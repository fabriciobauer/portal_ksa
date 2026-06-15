<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StageCategoryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'pilot_id' => ['required', 'integer', 'exists:pilots,id'],
            'season_category_registration_id' => ['nullable', 'integer', 'exists:season_category_registrations,id'],
            'confirmation_status' => ['required', Rule::in(['confirmed', 'waiting', 'cancelled', 'absent'])],
            'attendance_status' => ['nullable', Rule::in(['pending', 'present', 'absent'])],
            'briefing_status' => ['nullable', Rule::in(['pending', 'present', 'late', 'absent'])],
            'briefing_penalty_grid_positions' => ['nullable', 'integer', 'min:0', 'max:99'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
