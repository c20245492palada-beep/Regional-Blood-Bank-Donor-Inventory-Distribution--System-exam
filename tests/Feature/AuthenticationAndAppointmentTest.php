<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
            'phone_verified_at' => null,
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

    public function test_guest_can_request_a_password_reset_code(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
            'phone_verified_at' => now(),
        ]);
        config(['services.semaphore.key' => 'test-semaphore-key']);
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response([['status' => 'Queued']], 200),
        ]);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Send Reset Code');

        $this->post(route('password.email'), [
            'phone_number' => '09171234567',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If this number is registered and verified, a password reset code will be sent by SMS.');

        $sentCode = null;

        Http::assertSent(function (HttpRequest $request) use (&$sentCode): bool {
            if ($request->url() !== 'https://api.semaphore.co/api/v4/messages' || $request['number'] !== '09171234567') {
                return false;
            }

            preg_match('/password reset code is (\d{6})/', $request['message'], $matches);
            $sentCode = $matches[1] ?? null;

            return $sentCode !== null;
        });

        $this->assertIsString($sentCode);
        $this->assertDatabaseHas('phone_verification_codes', [
            'user_id' => $user->id,
            'purpose' => 'password_reset',
        ]);
    }

    public function test_unknown_phone_gets_the_same_reset_request_response_without_sending_sms(): void
    {
        config(['services.semaphore.key' => 'test-semaphore-key']);
        Http::fake();

        $this->post(route('password.email'), [
            'phone_number' => '+639171234567',
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If this number is registered and verified, a password reset code will be sent by SMS.');

        Http::assertNothingSent();
    }

    public function test_unverified_phone_gets_the_same_reset_request_response_without_sending_sms(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
            'phone_verified_at' => null,
        ]);
        config(['services.semaphore.key' => 'test-semaphore-key']);
        Http::fake();

        $this->post(route('password.email'), [
            'phone_number' => $user->phone_number,
        ])->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If this number is registered and verified, a password reset code will be sent by SMS.');

        Http::assertNothingSent();
    }

    public function test_user_can_reset_their_password_with_a_valid_code(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
            'phone_number' => '+639171234567',
            'phone_verified_at' => now(),
        ]);
        config(['services.semaphore.key' => 'test-semaphore-key']);
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response([['status' => 'Queued']], 200),
        ]);

        $this->post(route('password.email'), [
            'phone_number' => $user->phone_number,
        ])->assertRedirect(route('password.request'));

        $sentCode = null;

        Http::assertSent(function (HttpRequest $request) use (&$sentCode): bool {
            preg_match('/password reset code is (\d{6})/', $request['message'], $matches);
            $sentCode = $matches[1] ?? null;

            return $request->url() === 'https://api.semaphore.co/api/v4/messages' && $sentCode !== null;
        });

        $this->get(route('password.reset'))->assertOk()
            ->assertSee('Password Reset Code')
            ->assertSee('Change Password');

        $this->post(route('password.update'), [
            'phone_number' => '09171234567',
            'code' => $sentCode,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Your password has been reset.');

        $this->from(route('password.reset'))->post(route('password.update'), [
            'phone_number' => $user->phone_number,
            'code' => $sentCode,
            'password' => 'another-password-123',
            'password_confirmation' => 'another-password-123',
        ])->assertRedirect(route('password.reset'))
            ->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_invalid_password_reset_code_does_not_change_the_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
            'phone_number' => '+639171234567',
            'phone_verified_at' => now(),
        ]);

        $this->from(route('password.reset'))->post(route('password.update'), [
            'phone_number' => $user->phone_number,
            'code' => '000000',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('password.reset'))
            ->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_expired_and_exhausted_codes_cannot_reset_a_password(): void
    {
        $expiredUser = User::factory()->create([
            'password' => Hash::make('old-password-123'),
            'phone_number' => '+639171234567',
            'phone_verified_at' => now(),
        ]);
        DB::table('phone_verification_codes')->insert([
            'user_id' => $expiredUser->id,
            'purpose' => 'password_reset',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->subSecond(),
            'attempts' => 0,
            'created_at' => now()->subMinutes(11),
        ]);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'phone_number' => $expiredUser->phone_number,
                'code' => '123456',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHasErrors('code');

        $limitedUser = User::factory()->create([
            'password' => Hash::make('old-password-123'),
            'phone_number' => '+639181234567',
            'phone_verified_at' => now(),
        ]);
        DB::table('phone_verification_codes')->insert([
            'user_id' => $limitedUser->id,
            'purpose' => 'password_reset',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 4,
            'created_at' => now(),
        ]);

        $this->from(route('password.reset'))
            ->post(route('password.update'), [
                'phone_number' => $limitedUser->phone_number,
                'code' => '654321',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('phone_verification_codes', [
            'user_id' => $limitedUser->id,
            'purpose' => 'password_reset',
        ]);
        $this->assertTrue(Hash::check('old-password-123', $limitedUser->fresh()->password));
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
