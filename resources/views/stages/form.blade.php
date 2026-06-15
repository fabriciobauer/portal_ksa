@extends('layouts.app')

@section('title', $stage->exists ? 'Editar Etapa' : 'Nova Etapa')
@section('subtitle', 'Configure data, horários, local e status operacional.')

@section('content')
    <form method="POST"
        action="{{ $stage->exists ? route('stages.update', $stage) : route('stages.store') }}"
        class="card p-4 space-y-4">
        @csrf
        @if($stage->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Temporada</label>
                <select name="season_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" @selected(old('season_id', $stage->season_id) == $season->id)>{{ $season->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="form-label">Nome da etapa</label>
                <input type="text" name="name" value="{{ old('name', $stage->name) }}" class="form-input" required>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="field">
                <label class="form-label">Número</label>
                <input type="number" name="stage_number" value="{{ old('stage_number', $stage->stage_number) }}" class="form-input text-center" required>
            </div>
            <div class="field">
                <label class="form-label">Data</label>
                <input type="date" name="stage_date" value="{{ old('stage_date', optional($stage->stage_date)->format('Y-m-d')) }}" class="form-input" required>
            </div>
            <div class="field">
                <label class="form-label">Briefing</label>
                <input type="time" name="briefing_time" value="{{ old('briefing_time', $stage->briefing_time) }}" class="form-input">
            </div>
            <div class="field">
                <label class="form-label">Sorteio</label>
                <input type="time" name="draw_time" value="{{ old('draw_time', $stage->draw_time) }}" class="form-input">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Local</label>
                <input type="text" name="location" value="{{ old('location', $stage->location) }}" class="form-input">
            </div>
            <div class="field">
                <label class="form-label">Traçado</label>
                <input type="text" name="track_layout" value="{{ old('track_layout', $stage->track_layout) }}" class="form-input">
            </div>
        </div>

        <div class="field">
            <label class="form-label">Status</label>
            <select name="status" class="form-select md:w-48">
                @foreach(['planned' => 'Planejada', 'open' => 'Aberta', 'closed' => 'Fechada'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('status', $stage->status ?: 'planned') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label class="form-label">Observações</label>
            <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $stage->notes) }}</textarea>
        </div>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">Salvar etapa</button>
            @if($stage->exists)
                <a href="{{ route('stages.show', $stage) }}" class="btn-outline">Ver etapa</a>
            @endif
            <a href="{{ route('stages.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
