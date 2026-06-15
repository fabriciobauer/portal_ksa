<?php

namespace App\Http\Requests;

use App\Models\SeasonCategoryRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && ! $this->has('pilot_ids') && $this->filled('pilot_id')) {
            $this->merge([
                'pilot_ids' => [$this->input('pilot_id')],
            ]);
        }
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return $this->storeRules();
        }

        return $this->updateRules();
    }

    public function attributes(): array
    {
        return [
            'pilot_id' => 'piloto',
            'pilot_ids' => 'pilotos',
            'pilot_ids.*' => 'piloto',
            'season_category_id' => 'temporada / categoria',
        ];
    }

    protected function storeRules(): array
    {
        return [
            'season_category_id' => ['required', 'integer', 'exists:season_categories,id'],
            'pilot_ids' => ['required', 'array', 'min:1'],
            'pilot_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:pilots,id',
                Rule::unique('season_category_registrations', 'pilot_id')
                    ->where(fn ($query) => $query->where('season_category_id', $this->integer('season_category_id'))),
            ],
            ...$this->sharedRules(),
        ];
    }

    protected function updateRules(): array
    {
        $registrationId = $this->route('registration')?->id;

        return [
            'season_category_id' => ['required', 'integer', 'exists:season_categories,id'],
            'pilot_id' => [
                'required',
                'integer',
                'exists:pilots,id',
                Rule::unique('season_category_registrations')
                    ->where(fn ($query) => $query->where('season_category_id', $this->integer('season_category_id')))
                    ->ignore($registrationId),
            ],
            ...$this->sharedRules(),
        ];
    }

    protected function sharedRules(): array
    {
        return [
            'registration_type' => ['required', Rule::in([
                SeasonCategoryRegistration::TYPE_ANNUAL,
                SeasonCategoryRegistration::TYPE_SINGLE,
            ])],
            'status' => ['nullable', Rule::in([
                SeasonCategoryRegistration::STATUS_CONFIRMED,
                SeasonCategoryRegistration::STATUS_WAITING,
                SeasonCategoryRegistration::STATUS_CANCELLED,
            ])],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
