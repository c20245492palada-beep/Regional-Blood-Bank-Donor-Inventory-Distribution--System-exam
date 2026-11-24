<?php

namespace App\Http\Requests;

use App\Support\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phoneNumber = $this->input('phone_number');

        if (is_string($phoneNumber)) {
            $this->merge([
                'phone_number' => PhilippinePhoneNumber::normalize($phoneNumber),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'regex:/^\+639\d{9}$/', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
