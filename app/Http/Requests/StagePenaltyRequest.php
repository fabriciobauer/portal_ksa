<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StagePenaltyRequest extends FormRequest
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
            'type' => ['required', 'string', 'max:40'],
            'penalty_scope' => ['required', Rule::in(['championship_only', 'stage_and_championship'])],
            'points_delta' => ['nullable', 'numeric', 'min:-999', 'max:999'],
            'capped_group' => ['nullable', 'string', 'max:40'],
            'is_disqualification' => ['nullable', 'boolean'],
            'affects_discard_block' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}
