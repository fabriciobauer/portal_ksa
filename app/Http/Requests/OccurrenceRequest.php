<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OccurrenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'stage_category_entry_id' => ['required', 'integer', 'exists:stage_category_entries,id'],
            'race_id' => ['nullable', 'integer', 'exists:races,id'],
            'type' => ['required', Rule::in(['warning', 'time_penalty_5', 'time_penalty_10', 'black_flag', 'inappropriate_conduct'])],
            'description' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'max:5120'],
        ];
    }
}
