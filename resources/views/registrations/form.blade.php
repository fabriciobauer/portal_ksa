@extends('layouts.app')

@section('title', $registration->exists ? 'Editar Inscrição' : 'Nova Inscrição')
@section('subtitle', 'Controle anual ou avulso da temporada. O limite operacional de karts passa a valer na etapa.')

@section('content')
    @php
        $selectedPilotIds = collect((array) old('pilot_ids', $registration->exists ? [$registration->pilot_id] : []))
            ->map(fn ($pilotId) => (int) $pilotId)
            ->all();
    @endphp

    <form method="POST" action="{{ $registration->exists ? route('registrations.update', $registration) : route('registrations.store') }}" class="card p-4 space-y-4">
        @csrf
        @if($registration->exists)
            @method('PUT')
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                @if($registration->exists)
                    <label class="form-label">Piloto</label>
                    <select name="pilot_id" class="form-select" required>
                        <option value="">Selecione</option>
                        @foreach ($pilots as $pilot)
                            <option value="{{ $pilot->id }}" @selected(old('pilot_id', $registration->pilot_id) == $pilot->id)>{{ $pilot->displayName() }}</option>
                        @endforeach
                    </select>
                @else
                    <label class="form-label">Pilotos</label>
                    <select name="pilot_ids[]" class="form-select" size="{{ min(max($pilots->count(), 6), 12) }}" multiple required>
                        @foreach ($pilots as $pilot)
                            <option value="{{ $pilot->id }}" @selected(in_array($pilot->id, $selectedPilotIds, true))>{{ $pilot->displayName() }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Selecione um ou mais pilotos para inscrever todos de uma vez nesta categoria.</p>
                @endif
            </div>
            <div class="field">
                <label class="form-label">Temporada / categoria</label>
                <select name="season_category_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($seasonCategories as $seasonCategory)
                        <option value="{{ $seasonCategory->id }}" @selected(old('season_category_id', $registration->season_category_id) == $seasonCategory->id)>{{ $seasonCategory->season->name }} - {{ $seasonCategory->category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="field">
                <label class="form-label">Tipo</label>
                <select name="registration_type" class="form-select">
                    <option value="annual" @selected(old('registration_type', $registration->registration_type ?: 'annual') === 'annual')>Anual</option>
                    <option value="single" @selected(old('registration_type', $registration->registration_type) === 'single')>Avulsa</option>
                </select>
            </div>
            <div class="field">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach (['confirmed' => 'Confirmada', 'waiting' => 'Espera', 'cancelled' => 'Cancelada'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $registration->status ?: 'confirmed') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field md:col-span-2">
                <label class="form-label">Data da inscrição</label>
                <input type="date" name="registered_at" value="{{ old('registered_at', optional($registration->registered_at)->format('Y-m-d')) }}" class="form-input">
            </div>
        </div>

        <div class="field">
            <label class="form-label">Observações</label>
            <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $registration->notes) }}</textarea>
        </div>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">{{ $registration->exists ? 'Salvar inscrição' : 'Salvar inscrições' }}</button>
            @if($registration->exists)
                <button type="submit" form="cancel-registration" class="btn-danger btn-sm">Cancelar inscrição</button>
            @endif
            <a href="{{ route('registrations.index') }}" class="btn-ghost">Voltar</a>
        </div>
    </form>

    @if($registration->exists)
        <form id="cancel-registration" method="POST" action="{{ route('registrations.destroy', $registration) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
