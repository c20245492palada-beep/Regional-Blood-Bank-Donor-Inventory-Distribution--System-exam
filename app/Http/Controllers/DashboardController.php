<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BloodBag;
use App\Models\DistributionLog;
use App\Models\Donor;
use App\Models\HospitalRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'donors' => Donor::count(),
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
            ->selectRaw('donors.blood_type || donors.rh_factor as blood_group, COUNT(*) as total')
            ->groupByRaw('donors.blood_type, donors.rh_factor')
            ->orderByDesc('total')
            ->get();

        $appointments = Appointment::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('appointment_date')
            ->get();

        return view('dashboard', compact('stats', 'recentDistributions', 'stockByGroup', 'appointments'));
    }
}
