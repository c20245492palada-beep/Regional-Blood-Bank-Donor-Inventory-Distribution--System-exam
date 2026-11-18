<?php

namespace App\Support;

final class PhilippinePhoneNumber
{
    public static function normalize(string $phoneNumber): string
    {
        if (preg_match('/^09\d{9}$/', $phoneNumber) === 1) {
            return '+63'.substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }
}
