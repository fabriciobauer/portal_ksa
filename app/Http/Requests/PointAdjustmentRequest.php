<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PointAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['adjustment', 'override', 'revert_override'])],
            'value' => ['nullable', 'numeric', 'min:-999', 'max:999'],
            'reason' => ['required', 'string'],
        ];
    }
}
