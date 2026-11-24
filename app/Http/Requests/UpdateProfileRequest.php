<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
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
        $user = $this->user();
        abort_unless($user instanceof User, 403);

        return [
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
        ];
    }
}
