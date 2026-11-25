<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHospitalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    protected function prepareForValidation(): void
    {
        foreach (['hospital_name', 'request_date', 'blood_type', 'rh_factor', 'units_requested'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = trim(strip_tags($value));

                if (in_array($field, ['blood_type', 'rh_factor'], true)) {
                    $value = strtoupper($value);
                }

                $this->merge([$field => $value]);
            }
        }
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'hospital_name' => ['required', 'string', 'max:150'],
            'request_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'blood_type' => ['required', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_factor' => ['required', 'string', Rule::in(['+', '-'])],
            'units_requested' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_date.before_or_equal' => 'The request date cannot be in the future.',
            'blood_type.in' => 'Blood type must be A, B, AB, or O.',
            'rh_factor.in' => 'RH factor must be positive (+) or negative (-).',
            'units_requested.min' => 'At least one blood unit must be requested.',
        ];
    }
}
