<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PilotRegistrationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $this->input('name'))) ?: '',
            'notes' => ($notes = preg_replace('/\s+/u', ' ', trim((string) $this->input('notes'))) ?: '') !== '' ? $notes : null,
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'sort_order' => 'ordem',
            'notes' => 'observações',
            'is_active' => 'status',
        ];
    }
}
