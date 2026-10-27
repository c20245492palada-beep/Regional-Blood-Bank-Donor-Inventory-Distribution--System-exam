<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blood_bag_id' => ['required', 'integer', 'exists:blood_bags,id'],
            'hospital_request_id' => ['required', 'integer', 'exists:hospital_requests,id'],
            'units_issued' => ['sometimes', 'integer', 'in:1'],
            'processing_fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'blood_bag_id.exists' => 'Select a registered blood bag.',
            'hospital_request_id.exists' => 'Select a registered hospital request.',
            'units_issued.in' => 'Each blood bag represents exactly one issued unit.',
            'processing_fee.min' => 'The processing fee cannot be negative.',
        ];
    }
}
