<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account_and_authenticates_the_user(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'alex@example.com',
            'password' => 'safe-password-123',
        ]);
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

    public function test_guest_cannot_open_dashboard_or_create_appointments(): void
    {
        $appointment = Appointment::factory()->create();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->post(route('appointments.store'), [
            'service' => 'screening',
            'appointment_date' => today()->addDay()->toDateString(),
            'payment_method' => 'Cash',
        ])->assertRedirect(route('login'));
        $this->post(route('appointments.cancel', $appointment))->assertRedirect(route('login'));
        $this->delete(route('appointments.destroy', $appointment))->assertRedirect(route('login'));

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

    public function test_authenticated_user_can_cancel_own_pending_appointment(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('appointments.cancel', $appointment))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_user_cannot_cancel_another_users_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('appointments.cancel', $appointment))
            ->assertNotFound();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Pending',
        ]);
    }

    public function test_user_cannot_cancel_an_appointment_that_is_not_pending(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->for($user)->create([
            'status' => 'Completed',
        ]);

        $this->actingAs($user)
            ->post(route('appointments.cancel', $appointment))
            ->assertStatus(409);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'Completed',
        ]);
    }

    public function test_authenticated_user_can_delete_own_appointment(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('appointments.destroy', $appointment))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('appointments.destroy', $appointment))
            ->assertNotFound();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
        ]);
    }
}
