<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPilotRegistrationInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicPilotRegistrationRequest extends FormRequest
{
    use NormalizesPilotRegistrationInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePilotRegistrationInput();
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', 'min:10', 'max:20'],
            'cpf' => ['required', 'digits:11'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'has_kart_experience' => ['required', 'boolean'],
            'has_championship_experience' => ['required', 'boolean'],
            'weight_kg' => ['required', 'numeric', 'min:1', 'max:999.99'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'pilot_registration_category_id' => [
                'required',
                'integer',
                Rule::exists('pilot_registration_categories', 'id')->where('is_active', true),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'nome completo',
            'whatsapp' => 'whatsapp',
            'cpf' => 'CPF',
            'email' => 'e-mail',
            'address' => 'endereço',
            'has_kart_experience' => 'já andou de kart antes',
            'has_championship_experience' => 'já disputou algum campeonato',
            'weight_kg' => 'peso',
            'age' => 'idade',
            'pilot_registration_category_id' => 'categoria',
        ];
    }
}
