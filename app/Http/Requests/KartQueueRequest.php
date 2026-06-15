<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KartQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array', 'min:1'],
            'positions.*.queue_position' => ['required', 'integer', 'min:1', 'distinct'],
            'positions.*.kart_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'positions.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'positions' => 'fila de karts',
            'positions.*.queue_position' => 'posição da fila',
            'positions.*.kart_number' => 'número do kart',
            'positions.*.is_active' => 'posição ativa',
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $positions = collect($this->input('positions', []));

                $activeRows = $positions
                    ->filter(fn (array $row): bool => (bool) ($row['is_active'] ?? false));

                if ($activeRows->contains(fn (array $row): bool => blank($row['kart_number'] ?? null))) {
                    $validator->errors()->add('positions', 'Toda posição ativa da fila deve ter um kart definido.');
                }

                $duplicateKarts = $activeRows
                    ->pluck('kart_number')
                    ->filter(fn ($kartNumber) => $kartNumber !== null && $kartNumber !== '')
                    ->countBy()
                    ->filter(fn (int $count): bool => $count > 1)
                    ->keys();

                if ($duplicateKarts->isNotEmpty()) {
                    $validator->errors()->add(
                        'positions',
                        'Não é permitido repetir kart entre posições ativas da fila: '.$duplicateKarts->implode(', ').'.',
                    );
                }
            },
        ];
    }
}
