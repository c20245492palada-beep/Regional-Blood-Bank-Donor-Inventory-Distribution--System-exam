<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Support\PhilippinePhoneNumber;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.reset-password');
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
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = User::query()
            ->where('phone_number', $validated['phone_number'])
            ->whereNotNull('phone_verified_at')
            ->first();

        if ($user === null || ! $codes->consumePasswordResetCode($user, $validated['code'])) {
            return back()->withErrors([
                'code' => 'The phone number or reset code is invalid or expired.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
    }
}
