@extends('layouts.app')

@section('title', 'Configurações')
@section('subtitle', 'Parâmetros gerais do campeonato, pontuação, descarte e faixa de karts.')

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" class="content-card p-4">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="section-title">Campeonato</div>
                <label class="form-label">Nome</label>
                <input type="text" name="settings[championship][name]" value="{{ $values->get('championship.name') }}" class="form-control mb-3">
                <label class="form-label">Organização</label>
                <input type="text" name="settings[championship][organization]" value="{{ $values->get('championship.organization') }}" class="form-control">
            </div>
            <div class="col-lg-4">
                <div class="section-title">Karts e etapa</div>
                <label class="form-label">Faixa inicial</label>
                <input type="number" name="settings[karts][range_start]" value="{{ $values->get('karts.range_start') }}" class="form-control mb-3">
                <label class="form-label">Faixa final</label>
                <input type="number" name="settings[karts][range_end]" value="{{ $values->get('karts.range_end') }}" class="form-control mb-3">
                <label class="form-label">Briefing padrão</label>
                <input type="time" name="settings[stage][default_briefing_time]" value="{{ $values->get('stage.default_briefing_time') }}" class="form-control mb-3">
                <label class="form-label">Tolerância de pesagem</label>
                <input type="number" step="0.01" name="settings[stage][weigh_in_tolerance]" value="{{ $values->get('stage.weigh_in_tolerance') }}" class="form-control mb-3">
                <div class="form-check mb-3">
                    <input type="checkbox" name="settings[stage][allow_single_entries]" value="1" class="form-check-input" id="allow_single_entries" @checked($values->get('stage.allow_single_entries'))>
                    <label class="form-check-label" for="allow_single_entries">Permitir inscrições avulsas</label>
                </div>
                <label class="form-label">Registro de briefing</label>
                <select name="settings[stage][briefing_adjustment_mode]" class="form-select">
                    <option value="manual_penalty_positions" @selected($values->get('stage.briefing_adjustment_mode') === 'manual_penalty_positions')>Somente informativo</option>
                </select>
            </div>
            <div class="col-lg-4">
                <div class="section-title">Pontuação e ranking</div>
                <label class="form-label">Descartes</label>
                <input type="number" name="settings[scoring][discard_count]" value="{{ $values->get('scoring.discard_count') }}" class="form-control mb-3">
                <label class="form-label">Bônus sem kart reserva</label>
                <select name="settings[scoring][bonus_no_spare_kart_behavior]" class="form-select mb-3">
                    @foreach (['grant' => 'Conceder bônus', 'remove' => 'Remover bônus', 'manual' => 'Decidir manualmente'] as $value => $label)
                        <option value="{{ $value }}" @selected($values->get('scoring.bonus_no_spare_kart_behavior') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="form-label">Escopo do desempate</label>
                <select name="settings[standings][championship_tiebreak_scope]" class="form-select mb-3">
                    <option value="all_heats" @selected($values->get('standings.championship_tiebreak_scope') === 'all_heats')>Todas as baterias</option>
                    <option value="valid_heats_only" @selected($values->get('standings.championship_tiebreak_scope') === 'valid_heats_only')>Somente etapas válidas</option>
                </select>
                <label class="form-label">Ambiguidades</label>
                <select name="settings[standings][ambiguity_behavior]" class="form-select">
                    <option value="manual" @selected($values->get('standings.ambiguity_behavior') === 'manual')>Decisão manual do admin</option>
                </select>
            </div>
        </div>

        <hr class="my-4">
        <div class="section-title">Tabela de pontuação por bateria</div>
        <div class="row g-3">
            @foreach (($values->get('scoring.points_table') ?? []) as $position => $value)
                <div class="col-md-2 col-4">
                    <label class="form-label">{{ $position }}º lugar</label>
                    <input type="number" step="0.01" name="settings[scoring][points_table][{{ $position }}]" value="{{ $value }}" class="form-control">
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button class="btn btn-primary">Salvar configurações</button>
        </div>
    </form>
@endsection
