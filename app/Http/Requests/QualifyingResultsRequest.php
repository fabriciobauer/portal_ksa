<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QualifyingResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'results' => ['required', 'array'],
            'results.*.entry_id' => ['required', 'integer', 'exists:stage_category_entries,id'],
            'results.*.lap_time' => ['nullable', 'string', 'max:20'],
            'results.*.kart_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'results.*.status' => ['required', Rule::in(['valid', 'no_time', 'absent', 'dsq'])],
            'results.*.notes' => ['nullable', 'string'],
        ];
    }
}
