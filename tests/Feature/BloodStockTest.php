<?php

namespace Tests\Feature;

use App\Models\BloodBag;
use App\Models\DistributionLog;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\HospitalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BloodStockTest extends TestCase
{
    use RefreshDatabase;

    public static function guestSubmissionEndpoints(): array
    {
        return [
            'donor registration' => ['donors.store'],
            'donation recording' => ['blood-bags.store'],
            'hospital request' => ['hospital-requests.store'],
            'blood distribution' => ['distribution-logs.store'],
        ];
    }

    public function test_authenticated_dashboard_displays_donor_and_donation_forms(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(route('donors.store'), false)
            ->assertSee(route('blood-bags.store'), false)
            ->assertSee(route('hospital-requests.store'), false)
            ->assertSee(route('distribution-logs.store'), false)
            ->assertSeeText('Register donor')
            ->assertSeeText('Record donation as blood bag')
            ->assertSeeText('Create hospital request')
            ->assertSeeText('Issue blood bag to a request');
    }

    public function test_authenticated_user_can_create_a_hospital_request_and_dashboard_counts_it(): void
    {
        $user = User::factory()->create();
        $requestDate = today()->toDateString();

        $response = $this->actingAs($user)
            ->post(route('hospital-requests.store'), [
                'hospital_name' => 'St. Mary Hospital',
                'request_date' => $requestDate,
                'blood_type' => 'ab',
                'rh_factor' => '+',
                'units_requested' => '2',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Hospital request recorded.');

        $this->assertDatabaseHas('hospitals', [
            'hospital_name' => 'St. Mary Hospital',
        ]);

        $hospitalRequest = HospitalRequest::query()->sole();
        $this->assertSame($requestDate, $hospitalRequest->request_date->toDateString());
        $this->assertSame('AB', $hospitalRequest->blood_type);
        $this->assertSame('+', $hospitalRequest->rh_factor);
        $this->assertSame(2, $hospitalRequest->units_requested);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('St. Mary Hospital')
            ->assertViewHas('stats', static fn (array $stats): bool => $stats['open_requests'] === 1);
    }

    public function test_hospital_request_rejects_a_future_request_date(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('hospital-requests.store'), [
                'hospital_name' => 'St. Mary Hospital',
                'request_date' => today()->addDay()->toDateString(),
                'blood_type' => 'O',
                'rh_factor' => '+',
                'units_requested' => 2,
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('request_date');

        $this->assertSame(
            'The request date cannot be in the future.',
            session('errors')->getBag('default')->first('request_date')
        );
        $this->assertDatabaseCount('hospital_requests', 0);
        $this->assertDatabaseCount('hospitals', 0);
    }

    public function test_distribution_updates_stock_request_and_dashboard_totals(): void
    {
        $user = User::factory()->create();
        $donor = $this->createDonor();
        $bag = BloodBag::create([
            'donor_id' => $donor->id,
            'blood_bag_code' => 'BAG-ISSUE-001',
            'donation_date' => today()->toDateString(),
        ]);
        $hospital = Hospital::create(['hospital_name' => 'City General']);
        $hospitalRequest = HospitalRequest::create([
            'hospital_id' => $hospital->id,
            'request_date' => today()->toDateString(),
            'blood_type' => 'O',
            'rh_factor' => '+',
            'units_requested' => 2,
        ]);

        $response = $this->actingAs($user)
            ->post(route('distribution-logs.store'), [
                'blood_bag_id' => $bag->id,
                'hospital_request_id' => $hospitalRequest->id,
                'processing_fee' => '37.50',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Blood bag BAG-ISSUE-001 issued successfully.');

        $this->assertDatabaseHas('blood_bags', [
            'id' => $bag->id,
            'status' => 'Issued',
        ]);
        $this->assertDatabaseHas('hospital_requests', [
            'id' => $hospitalRequest->id,
            'status' => 'Partial',
        ]);

        $distribution = DistributionLog::query()->sole();
        $this->assertSame(1, $distribution->units_issued);
        $this->assertSame('37.50', $distribution->processing_fee);
        $this->assertSame('Completed', $distribution->status);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', static fn (array $stats): bool => $stats['open_requests'] === 1
                && $stats['issued_units'] === 1
                && $stats['collection_total'] === 37.5)
            ->assertSeeText('BAG-ISSUE-001')
            ->assertSeeText('City General')
            ->assertSeeText('37.50');
    }

    public function test_distribution_rejects_an_incompatible_bag_without_changing_records(): void
    {
        $donor = $this->createDonor([
            'donor_national_id' => 'DONORAB001',
            'blood_type' => 'AB',
        ]);
        $bag = BloodBag::create([
            'donor_id' => $donor->id,
            'blood_bag_code' => 'BAG-INCOMPATIBLE',
            'donation_date' => today()->toDateString(),
        ]);
        $hospital = Hospital::create(['hospital_name' => 'City General']);
        $hospitalRequest = HospitalRequest::create([
            'hospital_id' => $hospital->id,
            'request_date' => today()->toDateString(),
            'blood_type' => 'O',
            'rh_factor' => '+',
            'units_requested' => 1,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('distribution-logs.store'), [
                'blood_bag_id' => $bag->id,
                'hospital_request_id' => $hospitalRequest->id,
                'processing_fee' => '10.00',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('blood_bag_id');

        $this->assertSame(
            'The selected blood bag is not compatible with the hospital request.',
            session('errors')->getBag('default')->first('blood_bag_id')
        );
        $this->assertDatabaseHas('blood_bags', [
            'id' => $bag->id,
            'status' => 'Available',
        ]);
        $this->assertDatabaseHas('hospital_requests', [
            'id' => $hospitalRequest->id,
            'status' => 'Pending',
        ]);
        $this->assertDatabaseCount('distribution_logs', 0);
    }

    public function test_authenticated_user_can_register_a_normalized_donor(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('donors.store'), [
                'donor_national_id' => 'ph-0001',
                'donor_name' => '<strong>Jamie Reyes</strong>',
                'blood_type' => 'ab',
                'rh_factor' => '+',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Donor registered successfully.');

        $this->assertDatabaseHas('donors', [
            'donor_national_id' => 'PH0001',
            'donor_name' => 'Jamie Reyes',
            'blood_type' => 'AB',
            'rh_factor' => '+',
        ]);
    }

    public function test_donor_registration_rejects_an_existing_normalized_id(): void
    {
        $this->createDonor(['donor_national_id' => 'DONOR001']);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('donors.store'), [
                'donor_national_id' => 'donor 001',
                'donor_name' => 'Another Donor',
                'blood_type' => 'O',
                'rh_factor' => '-',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('donor_national_id');

        $this->assertDatabaseCount('donors', 1);
    }

    public function test_donor_registration_rejects_an_invalid_blood_type(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('donors.store'), [
                'donor_national_id' => 'PH0002',
                'donor_name' => 'Jamie Reyes',
                'blood_type' => 'C',
                'rh_factor' => '+',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('blood_type');

        $this->assertSame(
            'Blood type must be A, B, AB, or O.',
            session('errors')->getBag('default')->first('blood_type')
        );
        $this->assertDatabaseCount('donors', 0);
    }

    public function test_authenticated_user_can_record_a_donation_and_see_the_available_bag(): void
    {
        $user = User::factory()->create();
        $donor = $this->createDonor();
        $donationDate = today()->subDays(3)->toDateString();

        $response = $this->actingAs($user)
            ->post(route('blood-bags.store'), [
                'donor_id' => $donor->id,
                'blood_bag_code' => 'BAG-TEST-001',
                'donation_date' => $donationDate,
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Donation recorded as blood bag BAG-TEST-001.');

        $this->assertDatabaseHas('blood_bags', [
            'donor_id' => $donor->id,
            'blood_bag_code' => 'BAG-TEST-001',
            'status' => 'Available',
        ]);

        $bag = BloodBag::query()->sole();
        $this->assertSame($donationDate, $bag->donation_date->toDateString());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('BAG-TEST-001')
            ->assertSeeText('Jamie Reyes')
            ->assertSeeText('O+')
            ->assertSee('<div class="stock-count">1</div>', false);
    }

    public function test_donation_rejects_a_duplicate_bag_code(): void
    {
        $donor = $this->createDonor();
        BloodBag::create([
            'donor_id' => $donor->id,
            'blood_bag_code' => 'BAG-EXISTING',
            'donation_date' => today()->toDateString(),
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('blood-bags.store'), [
                'donor_id' => $donor->id,
                'blood_bag_code' => 'BAG-EXISTING',
                'donation_date' => today()->toDateString(),
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('blood_bag_code');

        $this->assertSame(
            'This blood bag code is already registered.',
            session('errors')->getBag('default')->first('blood_bag_code')
        );
        $this->assertDatabaseCount('blood_bags', 1);
    }

    public function test_donation_rejects_an_unregistered_donor(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('blood-bags.store'), [
                'donor_id' => 999,
                'blood_bag_code' => 'BAG-UNKNOWN-DONOR',
                'donation_date' => today()->toDateString(),
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('donor_id');

        $this->assertSame(
            'Select a registered donor.',
            session('errors')->getBag('default')->first('donor_id')
        );
        $this->assertDatabaseCount('blood_bags', 0);
    }

    public function test_donation_rejects_an_invalid_date(): void
    {
        $donor = $this->createDonor();

        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('blood-bags.store'), [
                'donor_id' => $donor->id,
                'blood_bag_code' => 'BAG-INVALID-DATE',
                'donation_date' => '2026-02-31',
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('donation_date');

        $this->assertDatabaseCount('blood_bags', 0);
    }

    public function test_donation_rejects_a_future_date(): void
    {
        $donor = $this->createDonor();

        $response = $this->actingAs(User::factory()->create())
            ->from(route('dashboard'))
            ->post(route('blood-bags.store'), [
                'donor_id' => $donor->id,
                'blood_bag_code' => 'BAG-FUTURE-DATE',
                'donation_date' => today()->addDay()->toDateString(),
            ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('donation_date');

        $this->assertSame(
            'The donation date cannot be in the future.',
            session('errors')->getBag('default')->first('donation_date')
        );
        $this->assertDatabaseCount('blood_bags', 0);
    }

    #[DataProvider('guestSubmissionEndpoints')]
    public function test_guest_cannot_submit_inventory_or_distribution_forms(string $routeName): void
    {
        $this->post(route($routeName), [])
            ->assertRedirect(route('login'));
    }

    private function createDonor(array $attributes = []): Donor
    {
        return Donor::create(array_merge([
            'donor_national_id' => 'DONORTEST001',
            'donor_name' => 'Jamie Reyes',
            'blood_type' => 'O',
            'rh_factor' => '+',
        ], $attributes));
    }
}
