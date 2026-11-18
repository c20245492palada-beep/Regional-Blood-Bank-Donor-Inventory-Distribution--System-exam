<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_settings_from_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('settings.edit'));

        $this->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Profile information')
            ->assertSee('Phone verification')
            ->assertSee('pattern="(?:\+639[0-9]{9}|09[0-9]{9})"', false)
            ->assertSee('Change password');
    }

    public function test_authenticated_user_can_update_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone_number' => null,
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone_number' => '09302324013',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Account details updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone_number' => '+639302324013',
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'email' => 'other@example.com',
        ]);
    }

    public function test_user_cannot_change_their_email_to_another_users_email(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => $user->name,
                'email' => 'taken@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'user@example.com',
        ]);
    }

    public function test_user_cannot_use_another_users_phone_number(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
        ]);
        User::factory()->create([
            'phone_number' => '+639181234567',
        ]);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => '09181234567',
            ])
            ->assertSessionHasErrors('phone_number');

        $this->assertSame('+639171234567', $user->fresh()->phone_number);
    }

    public function test_changing_a_phone_number_clears_its_verified_status(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => '+639181234567',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone_number' => '+639181234567',
            'phone_verified_at' => null,
        ]);
    }

    public function test_changing_a_phone_number_requires_the_current_password(): void
    {
        $user = User::factory()->create([
            'phone_number' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => '09302324013',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertNull($user->fresh()->phone_number);
    }

    public function test_user_can_verify_their_phone_with_a_valid_sms_code(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+639171234567',
            'phone_verified_at' => null,
        ]);
        config(['services.semaphore.key' => 'test-semaphore-key']);
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response([['status' => 'Queued']], 200),
        ]);

        $this->actingAs($user)
            ->post(route('settings.phone.send-code'))
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'A verification code was sent to your phone.');

        $sentCode = null;

        Http::assertSent(function (HttpRequest $request) use (&$sentCode): bool {
            preg_match('/phone verification code is (\d{6})/', $request['message'], $matches);
            $sentCode = $matches[1] ?? null;

            return $request->url() === 'https://api.semaphore.co/api/v4/messages' && $sentCode !== null;
        });

        $this->from(route('settings.edit'))
            ->post(route('settings.phone.verify'), ['code' => '000000'])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);

        $this->post(route('settings.phone.verify'), ['code' => $sentCode])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Phone number verified for password recovery.');

        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertDatabaseMissing('phone_verification_codes', [
            'user_id' => $user->id,
            'purpose' => 'phone_verification',
        ]);
    }

    public function test_authenticated_user_can_change_password_with_their_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password-123'),
        ]);

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'current-password-123',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status', 'Password changed.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_change_fails_with_an_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password-123'),
        ]);

        $this->actingAs($user)
            ->put(route('settings.password.update'), [
                'current_password' => 'wrong-password-123',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password-123', $user->fresh()->password));
    }

    public function test_guest_cannot_access_or_update_account_settings(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'password' => Hash::make('current-password-123'),
        ]);

        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->patch(route('settings.profile.update'), [
            'name' => 'Changed Name',
            'email' => $user->email,
        ])->assertRedirect(route('login'));
        $this->put(route('settings.password.update'), [
            'current_password' => 'current-password-123',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Original Name',
        ]);
        $this->assertTrue(Hash::check('current-password-123', $user->fresh()->password));
    }
}
