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
        $response->assertSee('Dashboard navigation', false);
        $response->assertSee('href="#dashboard"', false);
        $response->assertSee('href="#appointments"', false);
        $response->assertSee('href="#health-indicators"', false);
        $response->assertSee('href="#distribution-activity"', false);
        $response->assertSee('href="#blood-stock"', false);
        $response->assertSee('rel="stylesheet"', false);
        $response->assertSee('type="module"', false);
    }

    public function test_pending_appointments_show_a_cancel_action(): void
    {
        $user = User::factory()->create();
        $pendingAppointment = Appointment::factory()->for($user)->create();
        $cancelledAppointment = Appointment::factory()->for($user)->create([
            'status' => 'Cancelled',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('appointments.cancel', $pendingAppointment), false);
        $response->assertDontSee(route('appointments.cancel', $cancelledAppointment), false);
        $response->assertSeeText('Cancel');
    }
}
