@extends('layouts.app')

@section('title', 'Gestão da Etapa')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('page_nav')
    <a href="#overview">Visão geral</a>
    <a href="#participants">Participantes</a>
    <a href="#qualifying">Tomada</a>
    <a href="#race-1">Corrida 1</a>
    <a href="#race-2">Corrida 2</a>
    <a href="#weigh-ins">Pesagem</a>
    <a href="#penalties">Penalidades</a>
    <a href="#occurrences">Ocorrências</a>
    <a href="#standings">Classificação</a>
@endsection

@section('content')

{{-- ── Visão geral ─────────────────────────────────────────────────────────── --}}
<div id="overview" data-section-id="overview" class="card p-4">
    <div class="section-title">Ações rápidas</div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('kart-draws.show', $stageCategory) }}" class="btn-primary btn-sm">Sorteio</a>
        <a href="{{ route('classifications.stage', $stageCategory) }}" class="btn-outline btn-sm">Classificação etapa</a>
        <a href="{{ route('classifications.championship', $stageCategory->seasonCategory) }}" class="btn-outline btn-sm">Campeonato</a>
        <form method="POST" action="{{ route('stage-management.recalculate', $stageCategory) }}">
            @csrf
            <button class="btn-ghost btn-sm" data-submitting-label="Recalculando...">Recalcular</button>
        </form>
    </div>
</div>

{{-- ── Participantes ───────────────────────────────────────────────────────── --}}
<div id="participants" data-section-id="participants" class="card">
    <div class="flex items-center justify-between p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Participantes</div>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Piloto</th>
                    <th>Confirmação</th>
                    <th>Presença</th>
                    <th>Briefing</th>
                    <th class="text-right">Grid pen.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stageCategory->entries as $entry)
                    <tr>
                        <td class="font-semibold text-sm">{{ $entry->pilot->displayName() }}</td>
                        <td>
                            <span class="{{ match($entry->confirmation_status) { 'confirmed' => 'badge-green', 'cancelled','absent' => 'badge-red', default => 'badge-gray' } }}">
                                {{ ucfirst($entry->confirmation_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $entry->attendance_status === 'present' ? 'badge-green' : 'badge-gray' }}">
                                {{ ucfirst($entry->attendance_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ match($entry->briefing_status) { 'present' => 'badge-green', 'absent' => 'badge-red', 'late' => 'badge-yellow', default => 'badge-gray' } }}">
                                {{ ucfirst($entry->briefing_status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($entry->briefing_penalty_grid_positions)
                                <span class="badge-red">-{{ $entry->briefing_penalty_grid_positions }}</span>
                            @else
                                <span class="text-ksa-muted text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-ksa-border">
        <div class="section-title">Adicionar participante</div>
        <form method="POST" action="{{ route('stage-management.entries.store', $stageCategory) }}" class="space-y-3">
            @csrf
            <div class="field">
                <label class="form-label">Piloto</label>
                <select name="pilot_id" class="form-select">
                    @foreach($availablePilots as $pilot)
                        <option value="{{ $pilot->id }}">{{ $pilot->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="field">
                    <label class="form-label text-xs">Confirmação</label>
                    <select name="confirmation_status" class="form-select">
                        @foreach(['confirmed'=>'Confirmado','waiting'=>'Espera','cancelled'=>'Cancelado','absent'=>'Ausente'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label text-xs">Presença</label>
                    <select name="attendance_status" class="form-select">
                        @foreach(['pending'=>'Pendente','present'=>'Presente','absent'=>'Ausente'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label text-xs">Briefing</label>
                    <select name="briefing_status" class="form-select">
                        @foreach(['pending'=>'Pendente','present'=>'Presente','late'=>'Atrasado','absent'=>'Ausente'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label text-xs">Pen. grid</label>
                    <input type="number" name="briefing_penalty_grid_positions" value="0" min="0" class="form-input text-center">
                </div>
            </div>
            <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Adicionar</button>
        </form>
        <p class="form-hint mt-2">Penalidades de briefing são informativas e não alteram o grid automaticamente.</p>
    </div>
</div>

{{-- ── Tomada de tempo ─────────────────────────────────────────────────────── --}}
<div id="qualifying" data-section-id="qualifying" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Tomada de tempo</div>
    </div>
    <form method="POST" action="{{ route('stage-management.qualifying.save', $stageCategory) }}" class="p-4 space-y-3">
        @csrf
        @foreach ($stageCategory->entries as $entry)
            @php $qr = $entry->qualifyingResult; @endphp
            <div class="border border-ksa-border rounded-xl p-3">
                <div class="font-semibold text-sm mb-2">{{ $entry->pilot->displayName() }}</div>
                <input type="hidden" name="results[{{ $entry->id }}][entry_id]" value="{{ $entry->id }}">
                <div class="grid grid-cols-2 gap-2">
                    <div class="field">
                        <label class="form-label text-xs">Tempo (M:SS.mmm)</label>
                        <input type="text" name="results[{{ $entry->id }}][lap_time]"
                            value="{{ old("results.{$entry->id}.lap_time", $qr?->lap_time) }}"
                            class="form-input text-center font-mono" placeholder="1:23.456" data-lap-time-input>
                    </div>
                    <div class="field">
                        <label class="form-label text-xs">Status</label>
                        <select name="results[{{ $entry->id }}][status]" class="form-select">
                            @foreach(['valid'=>'Válido','no_time'=>'Sem tempo','absent'=>'Ausente','dsq'=>'DSQ'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old("results.{$entry->id}.status", $qr?->status ?? 'valid')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach
        <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Salvar tomada de tempo</button>
    </form>
</div>

{{-- ── Troca de kart (tomada) ──────────────────────────────────────────────── --}}
<div class="card p-4">
    <div class="section-title">Troca de kart na tomada</div>
    <form method="POST" action="{{ route('stage-management.kart-changes.store', $stageCategory) }}" class="space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div class="field col-span-2">
                <label class="form-label">Piloto</label>
                <select name="entry_id" class="form-select">
                    @foreach($stageCategory->entries as $entry)
                        <option value="{{ $entry->id }}">{{ $entry->pilot->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="form-label">Kart anterior</label>
                <input type="number" name="from_kart_number" class="form-input text-center" placeholder="—">
            </div>
            <div class="field">
                <label class="form-label">Novo kart</label>
                <input type="number" name="to_kart_number" class="form-input text-center" placeholder="—">
            </div>
        </div>
        <p class="form-hint">Piloto que troca de kart larga por último (regulamento art. 25).</p>
        <button class="btn-warning btn-sm" data-submitting-label="Registrando...">Registrar troca</button>
    </form>
</div>

{{-- ── Corrida 1 ───────────────────────────────────────────────────────────── --}}
<div id="race-1" data-section-id="race-1" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Corrida 1</div>
    </div>
    <form method="POST" action="{{ route('stage-management.races.save', [$stageCategory, 1]) }}" class="p-4 space-y-3">
        @csrf
        @foreach ($stageCategory->entries as $entry)
            @php $rr = $races[1]?->results->firstWhere('stage_category_entry_id', $entry->id); @endphp
            <div class="border border-ksa-border rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-semibold text-sm">{{ $entry->pilot->displayName() }}</div>
                    @if($rr?->kart_number)<span class="badge-gray text-xs">K{{ $rr->kart_number }}</span>@endif
                </div>
                <input type="hidden" name="results[{{ $entry->id }}][entry_id]" value="{{ $entry->id }}">
                <div class="grid grid-cols-2 gap-2">
                    <div class="field">
                        <label class="form-label text-xs">Posição chegada</label>
                        <input type="number" name="results[{{ $entry->id }}][finish_position]"
                            value="{{ old("results.{$entry->id}.finish_position", $rr?->finish_position) }}"
                            min="1" class="form-input text-center text-2xl font-bold" placeholder="—">
                    </div>
                    <div class="field">
                        <label class="form-label text-xs">Status</label>
                        <select name="results[{{ $entry->id }}][status]" class="form-select">
                            @foreach(['finished'=>'Finalizado','dnf'=>'DNF','dsq'=>'DSQ','dns'=>'DNS','absent'=>'Ausente'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old("results.{$entry->id}.status",$rr?->status??'finished')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach
        <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Salvar Corrida 1</button>
    </form>
</div>

{{-- ── Corrida 2 ───────────────────────────────────────────────────────────── --}}
<div id="race-2" data-section-id="race-2" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Corrida 2</div>
    </div>
    <form method="POST" action="{{ route('stage-management.races.save', [$stageCategory, 2]) }}" class="p-4 space-y-3">
        @csrf
        @foreach ($stageCategory->entries as $entry)
            @php $rr = $races[2]?->results->firstWhere('stage_category_entry_id', $entry->id); @endphp
            <div class="border border-ksa-border rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-semibold text-sm">{{ $entry->pilot->displayName() }}</div>
                    @if($rr?->kart_number)<span class="badge-gray text-xs">K{{ $rr->kart_number }}</span>@endif
                </div>
                <input type="hidden" name="results[{{ $entry->id }}][entry_id]" value="{{ $entry->id }}">
                <div class="grid grid-cols-2 gap-2">
                    <div class="field">
                        <label class="form-label text-xs">Posição chegada</label>
                        <input type="number" name="results[{{ $entry->id }}][finish_position]"
                            value="{{ old("results.{$entry->id}.finish_position", $rr?->finish_position) }}"
                            min="1" class="form-input text-center text-2xl font-bold" placeholder="—">
                    </div>
                    <div class="field">
                        <label class="form-label text-xs">Status</label>
                        <select name="results[{{ $entry->id }}][status]" class="form-select">
                            @foreach(['finished'=>'Finalizado','dnf'=>'DNF','dsq'=>'DSQ','dns'=>'DNS','absent'=>'Ausente'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old("results.{$entry->id}.status",$rr?->status??'finished')===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach
        <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Salvar Corrida 2</button>
    </form>
</div>

{{-- ── Pesagem ──────────────────────────────────────────────────────────────── --}}
<div id="weigh-ins" data-section-id="weigh-ins" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Pesagem</div>
    </div>
    <form method="POST" action="{{ route('stage-management.weigh-ins.save', $stageCategory) }}" class="p-4 space-y-3">
        @csrf
        @foreach ($stageCategory->entries as $entry)
            @php $wi = $entry->weighIns->sortByDesc('weighed_at')->first(); @endphp
            <div class="border border-ksa-border rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-semibold text-sm">{{ $entry->pilot->displayName() }}</div>
                    @if($wi)
                        <span class="{{ $wi->within_tolerance ? 'badge-green' : 'badge-red' }}">
                            {{ $wi->within_tolerance ? 'OK' : 'Fora do peso' }}
                        </span>
                    @endif
                </div>
                <input type="hidden" name="weigh_ins[{{ $entry->id }}][entry_id]" value="{{ $entry->id }}">
                <div class="grid grid-cols-2 gap-2">
                    <div class="field">
                        <label class="form-label text-xs">Peso conjunto (kg)</label>
                        <input type="number" step="0.01" name="weigh_ins[{{ $entry->id }}][combined_weight]"
                            value="{{ old("weigh_ins.{$entry->id}.combined_weight", $wi?->combined_weight) }}"
                            class="form-input text-center" placeholder="93.00">
                    </div>
                    <div class="field">
                        <label class="form-label text-xs">Kart nº</label>
                        <input type="number" name="weigh_ins[{{ $entry->id }}][kart_number]"
                            value="{{ old("weigh_ins.{$entry->id}.kart_number", $wi?->kart_number) }}"
                            class="form-input text-center" placeholder="—">
                    </div>
                </div>
                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                    <input type="checkbox" name="weigh_ins[{{ $entry->id }}][exception_no_spare_kart]" value="1"
                        class="form-checkbox"
                        @checked(old("weigh_ins.{$entry->id}.exception_no_spare_kart", $wi?->exception_no_spare_kart))>
                    <span class="text-xs text-ksa-muted">Sem kart reserva (bônus mantido)</span>
                </label>
            </div>
        @endforeach
        <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Salvar pesagens</button>
    </form>
</div>

{{-- ── Penalidades ──────────────────────────────────────────────────────────── --}}
<div id="penalties" data-section-id="penalties" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Penalidades</div>
    </div>

    @if($stageCategory->penalties->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Piloto</th><th>Tipo</th><th>Valor</th><th>Bateria</th></tr></thead>
                <tbody>
                    @foreach($stageCategory->penalties as $penalty)
                        <tr>
                            <td class="font-semibold text-sm">{{ $penalty->stageCategoryEntry->pilot->displayName() }}</td>
                            <td>{{ $penalty->type }}</td>
                            <td>{{ $penalty->value }}</td>
                            <td>{{ $penalty->race_number ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <form method="POST" action="{{ route('stage-management.penalties.store', $stageCategory) }}" class="p-4 space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div class="field col-span-2">
                <label class="form-label">Piloto</label>
                <select name="entry_id" class="form-select">
                    @foreach($stageCategory->entries as $entry)
                        <option value="{{ $entry->id }}">{{ $entry->pilot->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select">
                    <option value="time_penalty">Tempo (+s)</option>
                    <option value="championship_points">Pontos camp.</option>
                    <option value="grid_penalty">Grid</option>
                    <option value="disqualification">DSQ</option>
                    <option value="other">Outro</option>
                </select>
            </div>
            <div class="field">
                <label class="form-label">Valor</label>
                <input type="text" name="value" class="form-input" placeholder="ex: 5">
            </div>
            <div class="field">
                <label class="form-label">Bateria</label>
                <select name="race_number" class="form-select">
                    <option value="">Ambas</option>
                    <option value="1">Corrida 1</option>
                    <option value="2">Corrida 2</option>
                </select>
            </div>
            <div class="field col-span-2">
                <label class="form-label">Descrição</label>
                <input type="text" name="description" class="form-input" placeholder="Motivo">
            </div>
        </div>
        <button class="btn-danger btn-sm" data-submitting-label="Registrando...">Registrar penalidade</button>
    </form>
</div>

{{-- ── Ocorrências ──────────────────────────────────────────────────────────── --}}
<div id="occurrences" data-section-id="occurrences" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Ocorrências</div>
    </div>

    @if($stageCategory->occurrences->isNotEmpty())
        <div class="p-4 space-y-2 border-b border-ksa-border">
            @foreach($stageCategory->occurrences as $occ)
                <div class="rounded-xl border border-ksa-border p-3 text-sm">
                    <div class="font-semibold">{{ $occ->description }}</div>
                    <div class="text-xs text-ksa-muted mt-0.5">{{ optional($occ->occurred_at)->format('H:i') }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('stage-management.occurrences.store', $stageCategory) }}" class="p-4 space-y-3">
        @csrf
        <div class="field">
            <label class="form-label">Ocorrência</label>
            <textarea name="description" class="form-textarea" placeholder="Descreva a ocorrência" rows="3"></textarea>
        </div>
        <button class="btn-outline btn-sm" data-submitting-label="Registrando...">Registrar ocorrência</button>
    </form>
</div>

{{-- ── Classificação da etapa ──────────────────────────────────────────────── --}}
<div id="standings" data-section-id="standings" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Classificação da etapa</div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pos.</th>
                    <th>Piloto</th>
                    <th class="text-right">R1</th>
                    <th class="text-right">R2</th>
                    <th class="text-right">Bônus</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($standings as $standing)
                    <tr class="{{ $standing->stage_position <= 5 ? 'data-table-highlight' : '' }}">
                        <td class="font-bold text-lg">{{ $standing->stage_position }}</td>
                        <td>
                            <div class="font-semibold text-sm">{{ $standing->stageCategoryEntry->pilot->displayName() }}</div>
                            @if($standing->is_disqualified)<span class="badge-red text-xs">DSQ</span>@endif
                            @if($standing->is_technical_tie)<span class="badge-yellow text-xs">Empate</span>@endif
                        </td>
                        <td class="text-right font-mono">{{ $standing->race1_points }}</td>
                        <td class="text-right font-mono">{{ $standing->race2_points }}</td>
                        <td class="text-right font-mono">{{ $standing->completion_bonus }}</td>
                        <td class="text-right font-bold text-ksa-navy">{{ $standing->championship_points }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-ksa-muted py-8">Sem classificação. Recalcule após inserir resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
