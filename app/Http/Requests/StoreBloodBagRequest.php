<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBloodBagRequest extends FormRequest
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
        return [
            'donor_id' => ['required', 'integer', 'exists:donors,id'],
            'blood_bag_code' => ['required', 'string', 'max:80', 'unique:blood_bags,blood_bag_code'],
            'donation_date' => ['required', 'date', 'before_or_equal:today'],
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
