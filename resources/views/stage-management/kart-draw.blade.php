@extends('layouts.app')

@section('title', 'Sorteio de Karts')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('page_nav')
    <a href="#queue">Fila</a>
    <a href="#used-map">Posicoes usadas</a>
    <a href="#history">Historico</a>
@endsection

@section('content')
    @php($isLocked = $latestBatch?->status === 'locked')
    @php($canDraw = ! $isLocked && $queuePreview['confirmed_entries_count'] > 0 && empty($queuePreview['issues']))

    <div class="content-card p-4 mb-4" id="queue" data-section-id="queue">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
            <div>
                <div class="section-title mb-1">Configuracao da fila</div>
                <div class="text-muted small">
                    Pilotos confirmados nesta categoria/etapa: {{ $queuePreview['confirmed_entries_count'] }}
                </div>
                <div class="text-muted small">
                    Posicoes usadas no sorteio: 1 ate {{ max(1, $queuePreview['confirmed_entries_count']) }}
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('kart-draws.draw', $stageCategory) }}">
                    @csrf
                    <button class="btn btn-primary" @disabled(! $canDraw) data-submitting-label="Sorteando...">Sortear automaticamente</button>
                </form>
                @if($latestBatch)
                    @if($isLocked)
                        <form method="POST" action="{{ route('kart-draws.unlock', $latestBatch) }}">
                            @csrf
                            <button class="btn btn-outline-warning" data-submitting-label="Destravando...">Destravar sorteio</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('kart-draws.lock', $latestBatch) }}">
                            @csrf
                            <button class="btn btn-outline-dark" data-submitting-label="Travando...">Travar sorteio</button>
                        </form>
                    @endif
                    <a href="{{ route('kart-draws.csv', $latestBatch) }}" class="btn btn-outline-secondary">Exportar CSV</a>
                @endif
                <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn btn-outline-secondary">Voltar</a>
            </div>
        </div>

        @if($isLocked)
            <div class="alert alert-info">
                O ultimo sorteio desta categoria esta travado. A fila e as atribuicoes ficaram congeladas ate o destravamento.
            </div>
        @endif

        @if(! $isLocked && $queuePreview['confirmed_entries_count'] > 0 && ! $canDraw)
            <div class="alert alert-info">
                Configure as posicoes 1 ate {{ $queuePreview['confirmed_entries_count'] }} da fila para liberar o sorteio.
            </div>
        @endif

        @if($queuePreview['issues'])
            <div class="alert alert-warning">
                <div class="fw-semibold mb-2">Pendencias para o sorteio</div>
                <ul class="mb-0 ps-3">
                    @foreach ($queuePreview['issues'] as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('kart-draws.queue.save', $stageCategory) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Posicao da fila</th>
                            <th>Ativa</th>
                            <th>Usada neste sorteio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($queuePreview['rows'] as $index => $row)
                            <tr class="{{ $row['is_used'] ? 'table-warning' : '' }}">
                                <td>
                                    {{ $row['queue_position'] }}
                                    <input type="hidden" name="positions[{{ $index }}][queue_position]" value="{{ $row['queue_position'] }}">
                                    <input
                                        type="hidden"
                                        name="positions[{{ $index }}][kart_number]"
                                        value="{{ old("positions.{$index}.kart_number", $row['stored_kart_number'] ?? $row['kart_number']) }}"
                                    >
                                </td>
                                <td>
                                    <input type="hidden" name="positions[{{ $index }}][is_active]" value="0">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="positions[{{ $index }}][is_active]"
                                            value="1"
                                            class="form-check-input"
                                            id="queue_active_{{ $row['queue_position'] }}"
                                            @checked((bool) old("positions.{$index}.is_active", $row['is_active']))
                                            @disabled($isLocked)
                                        >
                                        <label class="form-check-label" for="queue_active_{{ $row['queue_position'] }}">Ativa</label>
                                    </div>
                                </td>
                                <td>
                                    @if($row['is_used'])
                                        <span class="badge text-bg-dark">Sim</span>
                                    @else
                                        <span class="badge text-bg-light">Nao</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                <div class="small text-muted">
                    O sorteio sempre usa somente as primeiras posicoes da fila, de 1 ate N, onde N e a quantidade de pilotos confirmados.
                </div>
                <button class="btn btn-outline-dark" @disabled($isLocked) data-submitting-label="Salvando fila...">Salvar fila</button>
            </div>
        </form>
    </div>

    <div class="content-card p-4 mb-4" id="used-map" data-section-id="used-map">
        <div class="section-title">Mapa das posicoes usadas</div>
        @if($queuePreview['confirmed_entries_count'] > 0)
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Posicao da fila</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($queuePreview['used_rows'] as $row)
                            <tr>
                                <td>{{ $row['queue_position'] }}</td>
                                <td>
                                    @if($row['is_active'] && $row['is_configured'])
                                        <span class="badge text-bg-success">Pronta</span>
                                    @else
                                        <span class="badge text-bg-warning">Pendente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-muted">Ainda nao ha pilotos confirmados nesta categoria/etapa.</div>
        @endif
    </div>

    <div class="content-card p-4" id="history" data-section-id="history">
        <div class="section-title">Historico de sorteios</div>
        @forelse ($stageCategory->drawBatches->sortByDesc('sequence') as $batch)
            <div class="border rounded-4 p-3 mb-3">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold">Sessao {{ $batch->session_key }} / tentativa {{ $batch->sequence }}</div>
                        <div class="small text-muted">
                            {{ optional($batch->drawn_at)->format('d/m/Y H:i') }}
                            | posicoes usadas {{ $batch->range_start }} a {{ $batch->range_end }}
                        </div>
                    </div>
                    <span class="badge {{ $batch->status === 'locked' ? 'text-bg-success' : 'text-bg-warning' }}">{{ strtoupper($batch->status) }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Piloto</th>
                                <th>Posicao da fila</th>
                                <th>Ordem do sorteio</th>
                                <th>Manual</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batch->draws as $draw)
                                <tr>
                                    <td>{{ $draw->stageCategoryEntry->pilot->displayName() }}</td>
                                    <td>{{ $draw->queue_position }}</td>
                                    <td>{{ $draw->draw_order }}</td>
                                    <td>{{ $draw->is_manual ? 'Sim' : 'Nao' }}</td>
                                    <td class="text-end">
                                        @if($batch->status !== 'locked')
                                            <form method="POST" action="{{ route('kart-draws.update', $draw) }}" class="d-flex gap-2 justify-content-end">
                                                @csrf
                                                @method('PATCH')
                                                <input
                                                    type="number"
                                                    name="queue_position"
                                                    value="{{ $draw->queue_position }}"
                                                    min="1"
                                                    max="{{ $batch->draws->count() }}"
                                                    class="form-control form-control-sm"
                                                    style="width: 110px;"
                                                >
                                                <button class="btn btn-sm btn-outline-dark" data-submitting-label="Trocando...">Trocar posicao</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-muted">Ainda nao houve sorteio para esta categoria.</div>
        @endforelse
    </div>
@endsection
