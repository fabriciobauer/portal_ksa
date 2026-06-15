<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WeighInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'stage_category_entry_id' => ['required', 'integer', 'exists:stage_category_entries,id'],
            'kart_number' => ['nullable', 'integer', 'min:1'],
            'combined_weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'exception_no_spare_kart' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
