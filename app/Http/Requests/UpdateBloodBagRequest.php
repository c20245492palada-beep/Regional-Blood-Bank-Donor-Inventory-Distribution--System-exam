<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBloodBagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'blood_bag_code' => is_string($this->blood_bag_code)
                ? trim(strip_tags($this->blood_bag_code))
                : $this->blood_bag_code,
        ]);
    }

    public function rules(): array
    {
        $bagId = $this->route('bloodBag');

        return [
            'donor_id' => ['sometimes', 'required', 'integer', 'exists:donors,id'],
            'blood_bag_code' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                Rule::unique('blood_bags', 'blood_bag_code')->ignore($bagId),
            ],
            'donation_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'donor_id.exists' => 'Select a registered donor.',
            'blood_bag_code.unique' => 'This blood bag code is already registered.',
            'donation_date.before_or_equal' => 'The donation date cannot be in the future.',
        ];
    }
}
