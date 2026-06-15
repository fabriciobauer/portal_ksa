@extends('layouts.app')

@section('title', $stage->exists ? 'Editar Etapa' : 'Nova Etapa')
@section('subtitle', 'Configure data, horários, local, traçado e status operacional.')

@section('content')
    <form method="POST" action="{{ $stage->exists ? route('stages.update', $stage) : route('stages.store') }}" class="content-card p-4">
        @csrf
        @if($stage->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Temporada</label>
                <select name="season_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" @selected(old('season_id', $stage->season_id) == $season->id)>{{ $season->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nome da etapa</label>
                <input type="text" name="name" value="{{ old('name', $stage->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Número</label>
                <input type="number" name="stage_number" value="{{ old('stage_number', $stage->stage_number) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data</label>
                <input type="date" name="stage_date" value="{{ old('stage_date', optional($stage->stage_date)->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Briefing</label>
                <input type="time" name="briefing_time" value="{{ old('briefing_time', $stage->briefing_time) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sorteio</label>
                <input type="time" name="draw_time" value="{{ old('draw_time', $stage->draw_time) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Local</label>
                <input type="text" name="location" value="{{ old('location', $stage->location) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Traçado</label>
                <input type="text" name="track_layout" value="{{ old('track_layout', $stage->track_layout) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach (['planned' => 'Planejada', 'open' => 'Aberta', 'closed' => 'Fechada'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $stage->status ?: 'planned') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Observações</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $stage->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">Salvar etapa</button>
            @if($stage->exists)
                <a href="{{ route('stages.show', $stage) }}" class="btn btn-outline-dark">Abrir etapa</a>
            @endif
        </div>
    </form>
@endsection
