<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonorRequest;
use App\Services\BloodBankService;
use Illuminate\Http\RedirectResponse;

class DonorController extends Controller
{
    public function store(
        StoreDonorRequest $request,
        BloodBankService $bloodBankService
    ): RedirectResponse {
        $bloodBankService->createDonor($request->validated());

        return redirect()
            ->route('dashboard')
            ->with('status', 'Donor registered successfully.');
    }
}
