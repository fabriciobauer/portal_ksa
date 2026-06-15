@extends('layouts.app')

@section('title', 'Configurações')
@section('subtitle', 'Parâmetros gerais do campeonato, pontuação e karts.')

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="card p-4 space-y-4">
            <div class="section-title">Campeonato</div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                    <label class="form-label">Nome do campeonato</label>
                    <input type="text" name="settings[championship][name]" value="{{ $values->get('championship.name') }}" class="form-input">
                </div>
                <div class="field">
                    <label class="form-label">Organização</label>
                    <input type="text" name="settings[championship][organization]" value="{{ $values->get('championship.organization') }}" class="form-input">
                </div>
            </div>
        </div>

        <div class="card p-4 space-y-4">
            <div class="section-title">Karts e etapa</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="field">
                    <label class="form-label">Kart inicial</label>
                    <input type="number" name="settings[karts][range_start]" value="{{ $values->get('karts.range_start') }}" class="form-input text-center">
                </div>
                <div class="field">
                    <label class="form-label">Kart final</label>
                    <input type="number" name="settings[karts][range_end]" value="{{ $values->get('karts.range_end') }}" class="form-input text-center">
                </div>
                <div class="field">
                    <label class="form-label">Briefing padrão</label>
                    <input type="time" name="settings[stage][default_briefing_time]" value="{{ $values->get('stage.default_briefing_time') }}" class="form-input">
                </div>
                <div class="field">
                    <label class="form-label">Tolerância pesagem (kg)</label>
                    <input type="number" step="0.01" name="settings[stage][weigh_in_tolerance]" value="{{ $values->get('stage.weigh_in_tolerance') }}" class="form-input text-center">
                    <p class="form-hint">Kart+Piloto: 3 kg (reg.). Piloto só: 0.</p>
                </div>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="settings[stage][allow_single_entries]" value="1" class="form-checkbox" @checked($values->get('stage.allow_single_entries'))>
                <span class="text-sm font-semibold">Permitir inscrições avulsas</span>
            </label>
            <div class="field">
                <label class="form-label">Registro de briefing</label>
                <select name="settings[stage][briefing_adjustment_mode]" class="form-select md:w-64">
                    <option value="manual_penalty_positions" @selected($values->get('stage.briefing_adjustment_mode') === 'manual_penalty_positions')>Somente informativo</option>
                </select>
            </div>
        </div>

        <div class="card p-4 space-y-4">
            <div class="section-title">Pontuação e ranking</div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="field">
                    <label class="form-label">Descartes</label>
                    <input type="number" name="settings[scoring][discard_count]" value="{{ $values->get('scoring.discard_count') }}" class="form-input text-center">
                </div>
                <div class="field">
                    <label class="form-label">Bônus sem kart reserva</label>
                    <select name="settings[scoring][bonus_no_spare_kart_behavior]" class="form-select">
                        @foreach(['grant'=>'Conceder bônus','remove'=>'Remover bônus','manual'=>'Decidir manualmente'] as $v=>$l)
                            <option value="{{ $v }}" @selected($values->get('scoring.bonus_no_spare_kart_behavior')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label">Escopo desempate</label>
                    <select name="settings[standings][championship_tiebreak_scope]" class="form-select">
                        <option value="all_heats" @selected($values->get('standings.championship_tiebreak_scope')==='all_heats')>Todas as baterias</option>
                        <option value="valid_heats_only" @selected($values->get('standings.championship_tiebreak_scope')==='valid_heats_only')>Etapas válidas</option>
                    </select>
                </div>
                <div class="field md:col-span-1">
                    <label class="form-label">Ambiguidades</label>
                    <select name="settings[standings][ambiguity_behavior]" class="form-select">
                        <option value="manual" @selected($values->get('standings.ambiguity_behavior')==='manual')>Decisão manual</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-ksa-border">
                <div class="section-title">Tabela de pontuação por bateria</div>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                    @foreach (($values->get('scoring.points_table') ?? []) as $position => $value)
                        <div class="field">
                            <label class="form-label text-xs text-center">{{ $position }}º</label>
                            <input type="number" step="0.01" name="settings[scoring][points_table][{{ $position }}]"
                                value="{{ $value }}" class="form-input text-center font-bold">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <button class="btn-primary" data-submitting-label="Salvando...">Salvar configurações</button>
    </form>
@endsection
