<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHospitalRequestRequest;
use App\Services\BloodBankService;
use Illuminate\Http\RedirectResponse;

class HospitalRequestController extends Controller
{
    public function store(
        StoreHospitalRequestRequest $request,
        BloodBankService $bloodBankService
    ): RedirectResponse {
        $bloodBankService->createHospitalRequest($request->validated());

        return redirect()
            ->route('dashboard')
            ->with('status', 'Hospital request recorded.');
    }
}
