<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationAndAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account_and_authenticates_the_user(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'phone_number' => '09171234567',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'phone_number' => '+639171234567',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'alex@example.com',
            'password' => 'safe-password-123',
        ]);
    }

    public function test_registration_requires_a_valid_mobile_number(): void
    {
        $this->from(route('register'))
            ->post(route('register.submit'), [
                'name' => 'Alex Rivera',
                'email' => 'alex@example.com',
                'password' => 'safe-password-123',
                'password_confirmation' => 'safe-password-123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('phone_number');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_requires_valid_credentials_and_logout_ends_the_session(): void
    {
        $user = User::factory()->create([
            'email' => 'alex@example.com',
            'password' => bcrypt('safe-password-123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'safe-password-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_shows_a_working_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'));
    }

    public function test_guest_can_request_a_password_reset_link_by_email(): void
    {
        $user = User::factory()->create();
        Notification::fake();

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Send Reset Link')
            ->assertSee('Email Address')
            ->assertDontSee('Mobile Number');

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If an account exists for that email address, a password reset link will be sent.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_reset_request_response(): void
    {
        Notification::fake();

        $this->post(route('password.email'), [
            'email' => 'unknown@example.com',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If an account exists for that email address, a password reset link will be sent.');

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_their_password_with_a_valid_email_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', ['token' => $token]);

        $this->get($resetUrl.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee('Email Address')
            ->assertSee('Reset Password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

        $this->from($resetUrl)->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'another-password-123',
            'password_confirmation' => 'another-password-123',
        ])->assertRedirect($resetUrl)
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_guest_cannot_open_dashboard_or_create_appointments(): void
    {
        $appointment = Appointment::factory()->create();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->post(route('appointments.store'), [
            'service' => 'screening',
            'appointment_date' => today()->addDay()->toDateString(),
            'payment_method' => 'Cash',
        ])->assertRedirect(route('login'));
        $this->post(route('appointments.cancel', $appointment))
            ->assertRedirect(route('login'));

        $this->assertModelExists($appointment);
    }

    public function test_authenticated_user_can_book_an_appointment_with_server_calculated_fee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('appointments.store'), [
            'service' => 'plasma',
            'appointment_date' => today()->addDay()->toDateString(),
            'payment_method' => 'GCash',
            'fee' => 1,
        ])->assertRedirect(route('dashboard'));

        $appointment = Appointment::query()->sole();

        $this->assertSame($user->id, $appointment->user_id);
        $this->assertSame('Plasma donation', $appointment->service);
        $this->assertSame('1200.00', $appointment->fee);
        $this->assertSame('GCash', $appointment->payment_method);
        $this->assertSame('Pending', $appointment->payment_status);
        $this->assertSame('Pending', $appointment->status);
    }

    public function test_invalid_appointment_service_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('appointments.store'), [
                'service' => 'free',
                'appointment_date' => today()->addDay()->toDateString(),
                'payment_method' => 'Cash',
            ])
            ->assertSessionHasErrors('service');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_past_appointment_date_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('appointments.store'), [
                'service' => 'plasma',
                'appointment_date' => today()->subDay()->toDateString(),
                'payment_method' => 'GCash',
            ])
            ->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_appointment_inputs_are_sanitized_before_validation_and_persistence(): void
    {
        $user = User::factory()->create();
        $appointmentDate = today()->addDay()->toDateString();

        $this->actingAs($user)->post(route('appointments.store'), [
            'service' => '<strong>plasma</strong>',
            'appointment_date' => '<strong>'.$appointmentDate.'</strong>',
            'payment_method' => '<em>GCash</em>',
        ])->assertRedirect(route('dashboard'));

        $appointment = Appointment::query()->sole();

        $this->assertSame('Plasma donation', $appointment->service);
        $this->assertSame($appointmentDate, $appointment->appointment_date->toDateString());
        $this->assertSame('GCash', $appointment->payment_method);
    }

    public function test_appointment_configuration_drives_the_form_validation_and_saved_values(): void
    {
        config([
            'appointments.services.special-screening' => [
                'name' => 'Special screening',
                'fee' => 1750,
            ],
            'appointments.payment_methods.BankTransfer' => 'Bank transfer at clinic',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('value="special-screening" data-fee="1750">Special screening</option>', false)
            ->assertSee('value="BankTransfer">Bank transfer at clinic</option>', false);

        $this->post(route('appointments.store'), [
            'service' => 'special-screening',
            'appointment_date' => today()->addDay()->toDateString(),
            'payment_method' => 'BankTransfer',
        ])->assertRedirect(route('dashboard'));

        $appointment = Appointment::query()->sole();

        $this->assertSame('Special screening', $appointment->service);
        $this->assertSame('1750.00', $appointment->fee);
        $this->assertSame('BankTransfer', $appointment->payment_method);
    }

    public function test_authenticated_user_can_cancel_their_pending_appointment(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('appointments.cancel', $appointment))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Appointment cancelled.');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_user_cannot_cancel_another_users_appointment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $appointment = Appointment::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->post(route('appointments.cancel', $appointment))
            ->assertNotFound();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Pending',
        ]);
    }

    public function test_only_pending_appointments_can_be_cancelled(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->for($user)->create([
            'status' => 'Completed',
        ]);

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('appointments.cancel', $appointment))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Only pending appointments can be cancelled.');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Completed',
        ]);
    }
}
