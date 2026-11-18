<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Support\PhilippinePhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $phoneNumber = $request->input('phone_number');

        if (is_string($phoneNumber)) {
            $request->merge([
                'phone_number' => PhilippinePhoneNumber::normalize($phoneNumber),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->getKey()),
            ],
            'phone_number' => [
                'nullable',
                'regex:/^\+639\d{9}$/',
                Rule::unique('users')->ignore($user->getKey()),
            ],
        ]);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $phoneNumber = array_key_exists('phone_number', $validated)
            ? $validated['phone_number']
            : $user->phone_number;
        $phoneNumberChanged = $user->phone_number !== $phoneNumber;

        if ($phoneNumberChanged) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        if ($phoneNumberChanged) {
            $user->phone_verified_at = null;

            DB::table('phone_verification_codes')
                ->where('user_id', $user->getKey())
                ->delete();
        }

        $user->fill([
            ...$validated,
            'phone_number' => $phoneNumber,
        ])->save();

        return redirect()->route('settings.edit')->with('status', 'Account details updated.');
    }

    public function sendPhoneVerificationCode(Request $request, PhoneVerificationService $codes): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        if (! $codes->isConfigured()) {
            return back()->withErrors([
                'phone_number' => 'SMS verification is not configured. Set SEMAPHORE_API_KEY before requesting a code.',
            ]);
        }

        if (! $codes->sendPhoneVerificationCode($user)) {
            return back()->withErrors([
                'phone_number' => 'Please wait before requesting another verification code.',
            ]);
        }

        return redirect()->route('settings.edit')->with('status', 'A verification code was sent to your phone.');
    }

    public function verifyPhoneNumber(Request $request, PhoneVerificationService $codes): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (! $codes->verifyPhoneNumber($user, $validated['code'])) {
            return back()->withErrors([
                'code' => 'The verification code is invalid or expired.',
            ]);
        }

        return redirect()->route('settings.edit')->with('status', 'Phone number verified for password recovery.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('settings.edit')->with('status', 'Password changed.');
    }
}
