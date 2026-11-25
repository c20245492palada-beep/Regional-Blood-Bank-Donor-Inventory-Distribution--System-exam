<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BloodBag;
use App\Models\DistributionLog;
use App\Models\Donor;
use App\Models\HospitalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'users' => User::count(),
            'available_bags' => BloodBag::where('status', 'Available')->count(),
            'open_requests' => HospitalRequest::query()
                ->whereIn('status', ['Pending', 'Partial'])
                ->count(),
            'issued_units' => (int) DistributionLog::query()
                ->where('status', 'Completed')
                ->sum('units_issued'),
            'collection_total' => (float) DistributionLog::query()
                ->where('status', 'Completed')
                ->sum('processing_fee'),
        ];

        $recentDistributions = DistributionLog::with([
            'bloodBag.donor',
            'hospitalRequest.hospital',
        ])
            ->latest('issued_at')
            ->take(5)
            ->get();

        $stockByGroup = BloodBag::query()
            ->join('donors', 'donors.id', '=', 'blood_bags.donor_id')
            ->where('blood_bags.status', 'Available')
            ->select('donors.blood_type', 'donors.rh_factor')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('donors.blood_type', 'donors.rh_factor')
            ->orderByDesc('total')
            ->get()
            ->map(static fn (object $stock): object => (object) [
                'blood_group' => $stock->blood_type.$stock->rh_factor,
                'total' => (int) $stock->total,
            ]);

        $appointments = Appointment::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('appointment_date')
            ->get();

        $donors = Donor::query()
            ->orderBy('donor_name')
            ->get(['id', 'donor_national_id', 'donor_name', 'blood_type', 'rh_factor']);

        $availableBags = BloodBag::query()
            ->where('status', 'Available')
            ->with('donor:id,donor_name,blood_type,rh_factor')
            ->latest('created_at')
            ->latest('id')
            ->take(10)
            ->get();

        $availableBagsForDistribution = BloodBag::query()
            ->where('status', 'Available')
            ->with('donor:id,donor_name,blood_type,rh_factor')
            ->orderBy('blood_bag_code')
            ->get(['id', 'donor_id', 'blood_bag_code']);

        $openHospitalRequests = HospitalRequest::query()
            ->whereIn('status', ['Pending', 'Partial'])
            ->with('hospital:id,hospital_name')
            ->orderBy('request_date')
            ->orderBy('id')
            ->get([
                'id',
                'hospital_id',
                'request_date',
                'blood_type',
                'rh_factor',
                'units_requested',
                'status',
            ]);

        $appointmentServices = config('appointments.services');
        $paymentMethods = config('appointments.payment_methods');

        return view('dashboard', compact(
            'stats',
            'recentDistributions',
            'stockByGroup',
            'donors',
            'availableBags',
            'availableBagsForDistribution',
            'openHospitalRequests',
            'appointments',
            'appointmentServices',
            'paymentMethods',
        ));
    }
}
