<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('Blood Bank Dashboard');
    }

    public function test_dashboard_shows_cancel_and_delete_actions_for_pending_appointments(): void
    {
        $user = User::factory()->create();
        Appointment::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('class="appointment-action appointment-action-cancel"', false);
        $response->assertSee('class="appointment-action appointment-action-delete"', false);
    }

    public function test_dashboard_does_not_show_actions_for_non_pending_appointments(): void
    {
        $user = User::factory()->create();
        Appointment::factory()->for($user)->create([
            'status' => 'Cancelled',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('class="appointment-action appointment-action-cancel"', false);
        $response->assertDontSee('class="appointment-action appointment-action-delete"', false);
    }
}
