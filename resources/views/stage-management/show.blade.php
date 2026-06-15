@extends('layouts.app')

@section('title', 'Gestao da Etapa')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('page_nav')
    <a href="#overview">Visao geral</a>
    <a href="#participants">Participantes</a>
    <a href="#qualifying">Tomada</a>
    <a href="#race-1">Corrida 1</a>
    <a href="#race-2">Corrida 2</a>
    <a href="#penalties">Penalidades</a>
    <a href="#occurrences">Ocorrencias</a>
    <a href="#standings">Classificacao</a>
@endsection

@section('content')
<div class="content-card p-4 mb-4" id="overview" data-section-id="overview">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('kart-draws.show', $stageCategory) }}" class="btn btn-primary btn-sm">Sorteio</a>
        <a href="{{ route('classifications.stage', $stageCategory) }}" class="btn btn-outline-secondary btn-sm">Classificacao da etapa</a>
        <a href="{{ route('classifications.championship', $stageCategory->seasonCategory) }}" class="btn btn-outline-secondary btn-sm">Campeonato</a>
        <form method="POST" action="{{ route('stage-management.recalculate', $stageCategory) }}">
            @csrf
            <button class="btn btn-outline-dark btn-sm" data-submitting-label="Recalculando...">Recalcular</button>
        </form>
    </div>
</div>

<div class="content-card p-4 mb-4" id="participants" data-section-id="participants">
    <div class="section-title">Inscritos / confirmados</div>
    <div class="table-responsive mb-3">
        <table class="table">
            <thead><tr><th>Piloto</th><th>Confirmacao</th><th>Presenca</th><th>Briefing</th><th>Grid (info)</th></tr></thead>
            <tbody>
                @foreach ($stageCategory->entries as $entry)
                    <tr>
                        <td>{{ $entry->pilot->displayName() }}</td>
                        <td>{{ ucfirst($entry->confirmation_status) }}</td>
                        <td>{{ ucfirst($entry->attendance_status) }}</td>
                        <td>{{ ucfirst($entry->briefing_status) }}</td>
                        <td>{{ $entry->briefing_penalty_grid_positions }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('stage-management.entries.store', $stageCategory) }}" class="row g-2">
        @csrf
        <div class="col-md-4"><select name="pilot_id" class="form-select">@foreach($availablePilots as $pilot)<option value="{{ $pilot->id }}">{{ $pilot->displayName() }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="confirmation_status" class="form-select">@foreach(['confirmed'=>'Confirmado','waiting'=>'Espera','cancelled'=>'Cancelado','absent'=>'Ausente'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="attendance_status" class="form-select">@foreach(['pending'=>'Pendente','present'=>'Presente','absent'=>'Ausente'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="briefing_status" class="form-select">@foreach(['pending'=>'Pendente','present'=>'Presente','late'=>'Atrasado','absent'=>'Ausente'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-1"><input type="number" name="briefing_penalty_grid_positions" value="0" class="form-control"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100" data-submitting-label="Salvando...">Salvar</button></div>
    </form>
    <div class="form-text mt-2">Penalidades e observacoes cadastradas no sistema sao apenas informativas e nao alteram grid ou classificacao automaticamente.</div>
</div>

<div class="content-card p-4 mb-4" id="qualifying" data-section-id="qualifying">
    <div class="section-title">Tomada de tempo</div>
    <form method="POST" action="{{ route('stage-management.qualifying.save', $stageCategory) }}">
        @csrf
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Piloto</th><th>Tempo</th><th>Kart</th><th>Status</th><th>Grid auto</th></tr></thead>
                <tbody>
                    @foreach ($stageCategory->entries as $entry)
                        @php($result = $entry->qualifyingResult)
                        <tr>
                            <td>{{ $entry->pilot->displayName() }}<input type="hidden" name="results[{{ $loop->index }}][entry_id]" value="{{ $entry->id }}"></td>
                            <td><input type="text" name="results[{{ $loop->index }}][lap_time]" value="{{ $result?->formatted_lap_time }}" class="form-control" placeholder="0:00.000" inputmode="numeric" autocomplete="off" data-lap-time-input></td>
                            <td><input type="number" name="results[{{ $loop->index }}][kart_number]" value="{{ $result?->current_kart_number ?: optional($latestKartBatch?->draws?->firstWhere('stage_category_entry_id', $entry->id))->kart_number }}" class="form-control"></td>
                            <td><select name="results[{{ $loop->index }}][status]" class="form-select">@foreach(['valid'=>'Valido','no_time'=>'Sem tempo','absent'=>'Ausente','dsq'=>'Desclassificado'] as $value => $label)<option value="{{ $value }}" @selected(($result?->status ?: 'valid') === $value)>{{ $label }}</option>@endforeach</select></td>
                            <td>{{ $result?->auto_grid_position ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" data-submitting-label="Salvando tomada...">Salvar tomada</button>
    </form>
    <hr>
    <form method="POST" action="{{ route('stage-management.kart-changes.store', $stageCategory) }}" class="row g-2">
        @csrf
        <div class="col-md-3"><select name="stage_category_entry_id" class="form-select">@foreach($stageCategory->entries as $entry)<option value="{{ $entry->id }}">{{ $entry->pilot->displayName() }}</option>@endforeach</select></div>
        <div class="col-md-2"><input type="number" name="previous_kart_number" class="form-control" placeholder="Kart anterior"></div>
        <div class="col-md-2"><input type="number" name="new_kart_number" class="form-control" placeholder="Novo kart"></div>
        <div class="col-md-2"><select name="reason_type" class="form-select"><option value="regular">Troca</option><option value="breakdown">Quebra</option></select></div>
        <div class="col-md-2"><input type="date" name="happened_at" value="{{ now()->format('Y-m-d') }}" class="form-control"></div>
        <div class="col-md-1"><button class="btn btn-outline-dark w-100" data-submitting-label="Trocando...">Trocar</button></div>
    </form>
</div>

@foreach ([1 => 'Corrida 1', 2 => 'Corrida 2'] as $raceNumber => $raceLabel)
    <div class="content-card p-4 mb-4" id="race-{{ $raceNumber }}" data-section-id="race-{{ $raceNumber }}">
        <div class="section-title">{{ $raceLabel }}</div>
        <form method="POST" action="{{ route('stage-management.races.save', [$stageCategory, $raceNumber]) }}">
            @csrf
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Grid</th><th>Piloto</th><th>Kart</th><th>Final</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($stageCategory->entries as $entry)
                            @php
                                $result = optional($races->get($raceNumber))->results->firstWhere('stage_category_entry_id', $entry->id);
                                $grid = $raceNumber === 1 ? $raceOneGrid->firstWhere('entry.id', $entry->id) : $raceTwoGrid->firstWhere('entry.id', $entry->id);
                            @endphp
                            <tr>
                                <td><input type="hidden" name="results[{{ $loop->index }}][entry_id]" value="{{ $entry->id }}"><input type="number" name="results[{{ $loop->index }}][grid_position]" value="{{ $result?->grid_position ?: data_get($grid, 'grid_position') }}" class="form-control"></td>
                                <td>{{ $entry->pilot->displayName() }}</td>
                                <td><input type="number" name="results[{{ $loop->index }}][kart_number]" value="{{ $result?->kart_number ?: data_get($grid, 'kart_number') }}" class="form-control"></td>
                                <td><input type="number" name="results[{{ $loop->index }}][finish_position]" value="{{ $result?->finish_position }}" class="form-control"></td>
                                <td><select name="results[{{ $loop->index }}][status]" class="form-select">@foreach(['finished'=>'Finalizou','dnf'=>'Nao finalizou','dns'=>'Nao largou','dsq'=>'Desclassificado'] as $value => $label)<option value="{{ $value }}" @selected(($result?->status ?: 'finished') === $value)>{{ $label }}</option>@endforeach</select></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" data-submitting-label="Salvando resultado...">Salvar {{ $raceLabel }}</button>
        </form>
    </div>
@endforeach

<div class="row g-4" id="penalties" data-section-id="penalties">
    <div class="col-lg-12">
        <div class="content-card p-4 h-100">
            <div class="section-title">Penalidades informativas</div>
            <form method="POST" action="{{ route('stage-management.penalties.store', $stageCategory) }}" class="row g-2">
                @csrf
                <div class="col-md-6"><select name="stage_category_entry_id" class="form-select">@foreach($stageCategory->entries as $entry)<option value="{{ $entry->id }}">{{ $entry->pilot->displayName() }}</option>@endforeach</select></div>
                <div class="col-md-6"><select name="type" class="form-select"><option value="podium_attire">Podio sem vestimenta</option><option value="podium_absent">Podio sem permanecer</option><option value="stage_dsq">Desclassificacao da etapa</option><option value="race_dsq">Desclassificacao da bateria</option></select></div>
                <input type="hidden" name="penalty_scope" value="championship_only">
                <input type="hidden" name="points_delta" value="0">
                <div class="col-12"><input type="text" name="reason" class="form-control" placeholder="Justificativa"></div>
                <div class="col-12"><button class="btn btn-outline-dark" data-submitting-label="Registrando...">Registrar penalidade</button></div>
            </form>
            <div class="form-text mt-2">Use este cadastro apenas como historico da etapa. O grid e o resultado oficial devem ser informados diretamente nas corridas.</div>
        </div>
    </div>
</div>

<div class="content-card p-4 my-4" id="occurrences" data-section-id="occurrences">
    <div class="section-title">Ocorrencias informativas</div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('stage-management.occurrences.store', $stageCategory) }}" class="row g-2">
        @csrf
        <div class="col-md-3"><select name="stage_category_entry_id" class="form-select">@foreach($stageCategory->entries as $entry)<option value="{{ $entry->id }}">{{ $entry->pilot->displayName() }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="race_id" class="form-select"><option value="">Geral</option>@foreach($races as $race)<option value="{{ $race->id }}">{{ $race->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="type" class="form-select"><option value="warning">Advertencia</option><option value="time_penalty_5">Punicao 5s</option><option value="time_penalty_10">Punicao 10s</option><option value="black_flag">Bandeira preta</option><option value="inappropriate_conduct">Conduta inadequada</option></select></div>
        <div class="col-md-3"><input type="file" name="evidence" class="form-control"></div>
        <div class="col-12"><input type="text" name="description" class="form-control" placeholder="Descricao"></div>
        <div class="col-12"><button class="btn btn-outline-dark" data-submitting-label="Registrando...">Registrar ocorrencia</button></div>
    </form>
    <div class="form-text mt-2">As ocorrencias ficam registradas para consulta e auditoria, sem recalcular automaticamente grid ou pontuacao.</div>
</div>

<div class="content-card p-4" id="standings" data-section-id="standings">
    <div class="section-title">Classificacao da etapa</div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Pos.</th><th>Piloto</th><th>R1</th><th>R2</th><th>Bonus camp.</th><th>Bruto etapa</th><th>Campeonato</th><th></th></tr></thead>
            <tbody>
                @forelse($standings as $standing)
                    <tr>
                        <td>{{ $standing->stage_position }}</td>
                        <td>{{ $standing->stageCategoryEntry->pilot->displayName() }} @if($standing->is_technical_tie)<span class="badge text-bg-warning">Empate tecnico</span>@endif @if($standing->discard_blocked)<span class="badge text-bg-danger">Sem descarte</span>@endif</td>
                        <td>{{ $standing->race1_points }}</td><td>{{ $standing->race2_points }}</td><td>{{ $standing->completion_bonus }}</td><td>{{ $standing->gross_stage_points }}</td><td>{{ $standing->championship_points }}</td>
                        <td class="text-end">@if(auth()->user()->isAdmin())<button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#adj_{{ $standing->id }}">Ajustar</button>@endif</td>
                    </tr>
                    @if(auth()->user()->isAdmin())
                        <tr class="collapse" id="adj_{{ $standing->id }}"><td colspan="8">
                            <form method="POST" action="{{ route('stage-management.points.adjust', $standing) }}" class="row g-2 mb-2">
                                @csrf
                                <div class="col-md-3"><select name="type" class="form-select"><option value="adjustment">Ajuste</option><option value="override">Override</option><option value="revert_override">Desfazer override</option></select></div>
                                <div class="col-md-3"><input type="number" step="0.01" name="value" class="form-control" placeholder="Valor"></div>
                                <div class="col-md-4"><input type="text" name="reason" class="form-control" placeholder="Motivo"></div>
                                <div class="col-md-2"><button class="btn btn-primary w-100" data-submitting-label="Salvando...">Salvar</button></div>
                            </form>
                            @if($standing->is_technical_tie)
                                <form method="POST" action="{{ route('stage-management.points.resolve-tie', $standing) }}" class="row g-2">
                                    @csrf
                                    <div class="col-md-2"><input type="number" name="stage_position" value="{{ $standing->stage_position }}" class="form-control"></div>
                                    <div class="col-md-8"><input type="text" name="tie_break_notes" class="form-control" placeholder="Justificativa do desempate manual"></div>
                                    <div class="col-md-2"><button class="btn btn-outline-secondary w-100" data-submitting-label="Salvando...">Desempatar</button></div>
                                </form>
                            @endif
                        </td></tr>
                    @endif
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Classificacao pendente.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
