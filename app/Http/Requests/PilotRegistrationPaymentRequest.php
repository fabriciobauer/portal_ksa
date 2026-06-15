<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PilotRegistrationPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_status' => $this->has('payment_status')
                ? filter_var($this->input('payment_status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', 'boolean'],
        ];
    }
}
