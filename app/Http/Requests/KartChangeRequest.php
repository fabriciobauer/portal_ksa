<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KartChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'stage_category_entry_id' => ['required', 'integer', 'exists:stage_category_entries,id'],
            'previous_kart_number' => ['required', 'integer', 'min:1'],
            'new_kart_number' => ['required', 'integer', 'min:1', 'different:previous_kart_number'],
            'reason_type' => ['required', Rule::in(['regular', 'breakdown'])],
            'happened_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
