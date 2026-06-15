<?php

namespace App\Http\Requests\Concerns;

trait NormalizesPilotRegistrationInput
{
    protected function normalizePilotRegistrationInput(): void
    {
        $this->merge([
            'full_name' => $this->normalizeText($this->input('full_name')),
            'whatsapp' => $this->normalizeDigits($this->input('whatsapp')),
            'cpf' => $this->normalizeDigits($this->input('cpf')),
            'email' => $this->normalizeEmail($this->input('email')),
            'address' => $this->normalizeText($this->input('address')),
            'notes' => $this->normalizeNullableText($this->input('notes')),
            'weight_kg' => $this->normalizeDecimal($this->input('weight_kg')),
            'age' => $this->normalizeInteger($this->input('age')),
        ]);
    }

    protected function normalizeText(mixed $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value)) ?: '';
    }

    protected function normalizeNullableText(mixed $value): ?string
    {
        $normalized = $this->normalizeText($value);

        return $normalized !== '' ? $normalized : null;
    }

    protected function normalizeDigits(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?: '';

        return $digits !== '' ? $digits : null;
    }

    protected function normalizeEmail(mixed $value): ?string
    {
        $email = mb_strtolower(trim((string) $value));

        return $email !== '' ? $email : null;
    }

    protected function normalizeDecimal(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $normalized) ?: '');

        return $normalized !== '' ? $normalized : null;
    }

    protected function normalizeInteger(mixed $value): ?int
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return (int) preg_replace('/\D+/', '', $normalized);
    }
}
