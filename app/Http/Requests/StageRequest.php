<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $stageId = $this->route('stage')?->id;

        return [
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'name' => ['required', 'string', 'max:255'],
            'stage_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('stages')
                    ->where(fn ($query) => $query->where('season_id', $this->integer('season_id')))
                    ->ignore($stageId),
            ],
            'stage_date' => ['required', 'date'],
            'briefing_time' => ['nullable', 'date_format:H:i'],
            'draw_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'track_layout' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['planned', 'open', 'closed'])],
        ];
    }
}
