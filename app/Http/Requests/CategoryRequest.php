<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'target_weight' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'default_pilot_limit' => ['required', 'integer', 'min:1', 'max:60'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
