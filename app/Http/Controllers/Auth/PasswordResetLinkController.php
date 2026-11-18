<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Support\PhilippinePhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request, PhoneVerificationService $codes): RedirectResponse
    {
        $phoneNumber = $request->input('phone_number');

        if (is_string($phoneNumber)) {
            $request->merge([
                'phone_number' => PhilippinePhoneNumber::normalize($phoneNumber),
            ]);
        }

        $validated = $request->validate([
            'phone_number' => ['required', 'regex:/^\+639\d{9}$/'],
        ]);

        if (! $codes->isConfigured()) {
            return back()->withErrors([
                'phone_number' => 'SMS password recovery is not configured. Set SEMAPHORE_API_KEY before requesting a code.',
            ]);
        }

        $user = User::query()
            ->where('phone_number', $validated['phone_number'])
            ->whereNotNull('phone_verified_at')
            ->first();

        if ($user !== null) {
            $codes->sendPasswordResetCode($user);
        }

        return redirect()->route('password.request')->with(
            'status',
            'If this number is registered and verified, a password reset code will be sent by SMS.',
        );
    }
}
