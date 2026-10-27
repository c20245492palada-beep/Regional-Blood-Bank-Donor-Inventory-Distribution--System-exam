<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBloodBagRequest;
use App\Http\Requests\UpdateBloodBagRequest;
use App\Models\BloodBag;
use App\Models\Donor;
use App\Services\BloodBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BloodBagController extends Controller
{
    private BloodBankService $bloodBankService;

    public function __construct(BloodBankService $bloodBankService)
    {
        $this->bloodBankService = $bloodBankService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPageInput = $request->query('per_page', 15);
        $perPage = is_string($perPageInput) || is_int($perPageInput)
            ? filter_var(
                $perPageInput,
                FILTER_VALIDATE_INT,
                ['options' => ['default' => 15, 'min_range' => 1, 'max_range' => 100]]
            )
            : 15;

        $bags = BloodBag::with(['donor', 'distributionLog.hospitalRequest.hospital'])
            ->latest()
            ->paginate($perPage);

        return response()->json($bags);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'donors' => Donor::query()
                ->orderBy('donor_name')
                ->get(['id', 'donor_national_id', 'donor_name', 'blood_type', 'rh_factor']),
        ]);
    }

    public function store(StoreBloodBagRequest $request): JsonResponse
    {
        $bag = $this->bloodBankService->createBloodBag($request->validated());

        return response()->json($bag->load('donor'), 201);
    }

    public function update(UpdateBloodBagRequest $request, int $bloodBag): JsonResponse
    {
        $bag = $this->bloodBankService->updateBloodBag($bloodBag, $request->validated());

        return response()->json($bag);
    }
}
