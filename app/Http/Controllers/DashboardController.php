<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BloodBag;
use App\Models\DistributionLog;
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
            'pending_requests' => HospitalRequest::where('status', 'Pending')->count(),
            'issued_units' => (int) DistributionLog::sum('units_issued'),
            'collection_total' => (float) DistributionLog::sum('processing_fee'),
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

        $appointmentServices = config('appointments.services');
        $paymentMethods = config('appointments.payment_methods');

        return view('dashboard', compact(
            'stats',
            'recentDistributions',
            'stockByGroup',
            'appointments',
            'appointmentServices',
            'paymentMethods',
        ));
    }
}
