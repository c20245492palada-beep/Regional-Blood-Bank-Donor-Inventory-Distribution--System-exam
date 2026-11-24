<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validated();

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $phoneNumber = array_key_exists('phone_number', $validated)
            ? $validated['phone_number']
            : $user->phone_number;

        $user->fill([
            ...$validated,
            'phone_number' => $phoneNumber,
        ])->save();

        return redirect()->route('settings.edit')->with('status', 'Account details updated.');
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validated();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('settings.edit')->with('status', 'Password changed.');
    }
}
