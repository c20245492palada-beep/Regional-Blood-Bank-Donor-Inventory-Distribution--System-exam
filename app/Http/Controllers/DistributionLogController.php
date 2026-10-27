<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDistributionLogRequest;
use App\Http\Requests\UpdateDistributionLogRequest;
use App\Models\BloodBag;
use App\Models\DistributionLog;
use App\Models\HospitalRequest as HospitalRequestModel;
use App\Services\BloodBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistributionLogController extends Controller
{
    public function __construct(private readonly BloodBankService $bloodBankService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));
        $logs = DistributionLog::with([
            'bloodBag.donor',
            'hospitalRequest.hospital',
        ])->latest('issued_at')->paginate($perPage);

        return response()->json($logs);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'available_bags' => BloodBag::query()
                ->where('status', 'Available')
                ->with('donor:id,donor_national_id,donor_name,blood_type,rh_factor')
                ->orderBy('blood_bag_code')
                ->get(['id', 'donor_id', 'blood_bag_code', 'donation_date', 'status']),
            'open_requests' => HospitalRequestModel::query()
                ->whereIn('status', ['Pending', 'Partial'])
                ->with('hospital:id,hospital_name')
                ->orderBy('request_date')
                ->get([
                    'id',
                    'hospital_id',
                    'request_date',
                    'blood_type',
                    'rh_factor',
                    'units_requested',
                    'status',
                ]),
        ]);
    }

    public function store(StoreDistributionLogRequest $request): JsonResponse
    {
        $log = $this->bloodBankService->distribute($request->validated());

        return response()->json($log, 201);
    }

    public function update(UpdateDistributionLogRequest $request, int $distributionLog): JsonResponse
    {
        $log = $this->bloodBankService->updateDistributionLog(
            $distributionLog,
            $request->validated()
        );

        return response()->json($log);
    }
}
