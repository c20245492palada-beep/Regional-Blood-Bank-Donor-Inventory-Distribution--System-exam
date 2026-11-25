<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\BloodBankService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    protected function prepareForValidation(): void
    {
        $donorNationalId = $this->input('donor_national_id');

        $this->merge([
            'donor_national_id' => is_string($donorNationalId)
                ? BloodBankService::formatDonorId($donorNationalId)
                : $donorNationalId,
        ]);

        foreach (['donor_name', 'blood_type', 'rh_factor'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = trim(strip_tags($value));

                $this->merge([
                    $field => $field === 'donor_name' ? $value : strtoupper($value),
                ]);
            }
        }
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'donor_national_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('donors', 'donor_national_id'),
            ],
            'donor_name' => ['required', 'string', 'max:150'],
            'blood_type' => ['required', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_factor' => ['required', 'string', Rule::in(['+', '-'])],
        ];
    }

    public function messages(): array
    {
        return [
            'donor_national_id.unique' => 'This donor ID is already registered.',
            'blood_type.in' => 'Blood type must be A, B, AB, or O.',
            'rh_factor.in' => 'RH factor must be positive (+) or negative (-).',
        ];
    }
}
