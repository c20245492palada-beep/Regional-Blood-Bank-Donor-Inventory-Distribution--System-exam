<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    protected function prepareForValidation(): void
    {
        foreach (['service', 'appointment_date', 'payment_method'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $this->merge([
                    $field => trim(strip_tags($value)),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'service' => ['required', 'string', Rule::in(array_keys(config('appointments.services')))],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_method' => ['required', 'string', Rule::in(array_keys(config('appointments.payment_methods')))],
        ];
    }
}
