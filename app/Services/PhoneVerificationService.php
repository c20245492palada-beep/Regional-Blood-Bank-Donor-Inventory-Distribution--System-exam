<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    private const CODE_LIFETIME_MINUTES = 10;

    private const MAX_ATTEMPTS = 5;

    private const RESEND_DELAY_SECONDS = 60;

    public function __construct(private SemaphoreSmsService $sms) {}

    public function isConfigured(): bool
    {
        return $this->sms->isConfigured();
    }

    public function sendPhoneVerificationCode(User $user): bool
    {
        if ($user->phone_verified_at !== null) {
            throw ValidationException::withMessages([
                'phone_number' => 'This phone number is already verified.',
            ]);
        }

        return $this->sendCode($user, 'phone_verification', 'Your phone verification code is');
    }

    public function sendPasswordResetCode(User $user): bool
    {
        if ($user->phone_verified_at === null) {
            throw ValidationException::withMessages([
                'phone_number' => 'Verify this phone number before using it for password recovery.',
            ]);
        }

        return $this->sendCode($user, 'password_reset', 'Your password reset code is');
    }

    public function verifyPhoneNumber(User $user, string $code): bool
    {
        if (! $this->consumeCode($user, 'phone_verification', $code)) {
            return false;
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        return true;
    }

    public function consumePasswordResetCode(User $user, string $code): bool
    {
        return $this->consumeCode($user, 'password_reset', $code);
    }

    private function sendCode(User $user, string $purpose, string $messagePrefix): bool
    {
        if (! is_string($user->phone_number) || $user->phone_number === '') {
            throw ValidationException::withMessages([
                'phone_number' => 'Add a mobile number to your account first.',
            ]);
        }

        if (! $this->sms->isConfigured()) {
            throw ValidationException::withMessages([
                'phone_number' => 'SMS recovery is not configured. Set SEMAPHORE_API_KEY before requesting a code.',
            ]);
        }

        $existingCode = DB::table('phone_verification_codes')
            ->where('user_id', $user->getKey())
            ->where('purpose', $purpose)
            ->first();

        if ($existingCode !== null && Carbon::parse($existingCode->created_at)->gt(now()->subSeconds(self::RESEND_DELAY_SECONDS))) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $this->sms->send(
            $user->phone_number,
            "{$messagePrefix} {$code}. It expires in ".self::CODE_LIFETIME_MINUTES.' minutes.',
        );

        DB::table('phone_verification_codes')->updateOrInsert(
            [
                'user_id' => $user->getKey(),
                'purpose' => $purpose,
            ],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::CODE_LIFETIME_MINUTES),
                'attempts' => 0,
                'created_at' => now(),
            ],
        );

        return true;
    }

    private function consumeCode(User $user, string $purpose, string $code): bool
    {
        return DB::transaction(function () use ($user, $purpose, $code): bool {
            $verificationCode = DB::table('phone_verification_codes')
                ->where('user_id', $user->getKey())
                ->where('purpose', $purpose)
                ->lockForUpdate()
                ->first();

            if ($verificationCode === null) {
                return false;
            }

            if ((int) $verificationCode->attempts >= self::MAX_ATTEMPTS || Carbon::parse($verificationCode->expires_at)->isPast()) {
                DB::table('phone_verification_codes')->where('id', $verificationCode->id)->delete();

                return false;
            }

            if (! Hash::check($code, $verificationCode->code_hash)) {
                $attempts = (int) $verificationCode->attempts + 1;

                if ($attempts >= self::MAX_ATTEMPTS) {
                    DB::table('phone_verification_codes')->where('id', $verificationCode->id)->delete();
                } else {
                    DB::table('phone_verification_codes')
                        ->where('id', $verificationCode->id)
                        ->update(['attempts' => $attempts]);
                }

                return false;
            }

            DB::table('phone_verification_codes')->where('id', $verificationCode->id)->delete();

            return true;
        });
    }
}
