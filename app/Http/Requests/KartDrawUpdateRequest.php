<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KartDrawUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'queue_position' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'queue_position' => 'posição da fila',
        ];
    }
}
