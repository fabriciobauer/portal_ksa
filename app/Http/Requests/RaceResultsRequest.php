<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RaceResultsRequest extends FormRequest
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
            'results.*.grid_position' => ['nullable', 'integer', 'min:1'],
            'results.*.finish_position' => ['nullable', 'integer', 'min:1'],
            'results.*.kart_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'results.*.status' => ['required', Rule::in(['finished', 'dnf', 'dns', 'dsq'])],
            'results.*.notes' => ['nullable', 'string'],
        ];
    }
}
