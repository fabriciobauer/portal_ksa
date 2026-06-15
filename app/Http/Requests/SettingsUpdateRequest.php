<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.karts.range_start' => ['required', 'integer', 'min:1'],
            'settings.karts.range_end' => ['required', 'integer', 'gt:settings.karts.range_start'],
            'settings.scoring.discard_count' => ['required', 'integer', 'min:0', 'max:20'],
            'settings.stage.default_briefing_time' => ['nullable', 'date_format:H:i'],
            'settings.stage.weigh_in_tolerance' => ['nullable', 'numeric', 'min:0', 'max:99.99'],
            'settings.stage.allow_single_entries' => ['nullable', 'boolean'],
            'settings.standings.championship_tiebreak_scope' => ['required', 'string'],
            'settings.standings.ambiguity_behavior' => ['required', 'string'],
            'settings.scoring.bonus_no_spare_kart_behavior' => ['required', 'string'],
            'settings.stage.briefing_adjustment_mode' => ['required', 'string'],
            'settings.championship.name' => ['required', 'string', 'max:255'],
            'settings.championship.organization' => ['nullable', 'string', 'max:255'],
            'settings.scoring.points_table' => ['required', 'array'],
        ];
    }
}
