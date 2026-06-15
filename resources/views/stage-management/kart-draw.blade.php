@extends('layouts.app')

@section('title', 'Sorteio de Karts')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('page_nav')
    <a href="#queue">Fila</a>
    <a href="#used-map">Posições usadas</a>
    <a href="#history">Histórico</a>
@endsection

@section('content')

@php
    $isLocked = $latestBatch?->status === 'locked';
    $canDraw  = !$isLocked && $queuePreview['confirmed_entries_count'] > 0 && empty($queuePreview['issues']);
@endphp

{{-- ── Status banner ───────────────────────────────────────────────────────── --}}
@if($isLocked)
    <div class="alert-info">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span>Sorteio travado. Destrave para fazer alterações.</span>
    </div>
@elseif($queuePreview['issues'])
    <div class="alert-warning">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($queuePreview['issues'] as $issue)<li>{{ $issue }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- ── Ações principais ────────────────────────────────────────────────────── --}}
<div class="card p-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-sm font-semibold">Pilotos confirmados: <span class="text-ksa-navy font-bold">{{ $queuePreview['confirmed_entries_count'] }}</span></div>
            <div class="text-xs text-ksa-muted">Posições usadas: 1 até {{ max(1, $queuePreview['confirmed_entries_count']) }}</div>
        </div>
        <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn-ghost btn-sm">← Voltar</a>
    </div>

    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('kart-draws.draw', $stageCategory) }}">
            @csrf
            <button class="btn-primary {{ $canDraw ? '' : 'opacity-40 pointer-events-none' }}"
                data-submitting-label="Sorteando..." @disabled(!$canDraw)>
                🎲 Sortear automaticamente
            </button>
        </form>

        @if($latestBatch)
            @if($isLocked)
                <form method="POST" action="{{ route('kart-draws.unlock', $latestBatch) }}">
                    @csrf
                    <button class="btn-warning" data-submitting-label="Destravando...">🔓 Destravar</button>
                </form>
            @else
                <form method="POST" action="{{ route('kart-draws.lock', $latestBatch) }}">
                    @csrf
                    <button class="btn-outline" data-submitting-label="Travando...">🔒 Travar sorteio</button>
                </form>
            @endif
            <a href="{{ route('kart-draws.csv', $latestBatch) }}" class="btn-outline btn-sm">📄 CSV</a>
        @endif
    </div>
</div>

{{-- ── Configuração da fila ─────────────────────────────────────────────────── --}}
<div id="queue" data-section-id="queue" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Configuração da fila de karts</div>
    </div>

    <form method="POST" action="{{ route('kart-draws.queue.save', $stageCategory) }}" class="p-4">
        @csrf
        @method('PUT')

        <div class="space-y-2 mb-4">
            @foreach ($queuePreview['rows'] as $index => $row)
                <div class="flex items-center gap-3 rounded-xl border p-3 {{ $row['is_used'] ? 'border-ksa-orange/40 bg-orange-50/40' : 'border-ksa-border' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                        {{ $row['is_used'] ? 'bg-ksa-orange text-white' : 'bg-gray-100 text-ksa-muted' }}">
                        {{ $row['queue_position'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold">Kart {{ $row['kart_number'] }}</div>
                        @if($row['is_used'])
                            <div class="text-xs text-ksa-orange font-semibold">Usada neste sorteio</div>
                        @endif
                    </div>
                    <input type="hidden" name="positions[{{ $index }}][queue_position]" value="{{ $row['queue_position'] }}">
                    <input type="hidden" name="positions[{{ $index }}][kart_number]"
                        value="{{ old("positions.{$index}.kart_number", $row['stored_kart_number'] ?? $row['kart_number']) }}">
                    <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                        <input type="hidden" name="positions[{{ $index }}][is_active]" value="0">
                        <input type="checkbox" name="positions[{{ $index }}][is_active]" value="1"
                            class="form-checkbox"
                            id="queue_active_{{ $row['queue_position'] }}"
                            @checked((bool) old("positions.{$index}.is_active", $row['is_active']))
                            @disabled($isLocked)>
                        <span class="text-xs text-ksa-muted">Ativa</span>
                    </label>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-ksa-muted">O sorteio usa sempre as posições 1 até N (N = pilotos confirmados).</p>
            <button class="btn-outline btn-sm flex-shrink-0" @disabled($isLocked) data-submitting-label="Salvando...">
                Salvar fila
            </button>
        </div>
    </form>
</div>

{{-- ── Posições usadas ──────────────────────────────────────────────────────── --}}
<div id="used-map" data-section-id="used-map" class="card p-4">
    <div class="section-title">Posições usadas no sorteio</div>
    @if($queuePreview['confirmed_entries_count'] > 0)
        <div class="grid grid-cols-3 gap-2">
            @foreach ($queuePreview['used_rows'] as $row)
                <div class="rounded-xl border p-3 text-center {{ $row['is_active'] && $row['is_configured'] ? 'border-green-200 bg-green-50' : 'border-orange-200 bg-orange-50' }}">
                    <div class="text-lg font-bold">{{ $row['queue_position'] }}</div>
                    <div class="text-xs mt-0.5 {{ $row['is_active'] && $row['is_configured'] ? 'text-ksa-green' : 'text-orange-700' }}">
                        {{ $row['is_active'] && $row['is_configured'] ? 'Pronta' : 'Pendente' }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-ksa-muted">Ainda não há pilotos confirmados nesta categoria/etapa.</p>
    @endif
</div>

{{-- ── Histórico de sorteios ───────────────────────────────────────────────── --}}
<div id="history" data-section-id="history" class="card">
    <div class="p-4 border-b border-ksa-border">
        <div class="section-title mb-0">Histórico de sorteios</div>
    </div>
    <div class="p-4 space-y-4">
        @forelse ($stageCategory->drawBatches->sortByDesc('sequence') as $batch)
            <div class="border border-ksa-border rounded-xl overflow-hidden">
                <div class="flex items-center justify-between p-3 bg-gray-50 border-b border-ksa-border">
                    <div>
                        <div class="font-semibold text-sm">Tentativa {{ $batch->sequence }}</div>
                        <div class="text-xs text-ksa-muted">{{ optional($batch->drawn_at)->format('d/m/Y H:i') }} · pos. {{ $batch->range_start }}–{{ $batch->range_end }}</div>
                    </div>
                    <span class="{{ $batch->status === 'locked' ? 'badge-green' : 'badge-yellow' }}">
                        {{ strtoupper($batch->status) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Piloto</th>
                                <th class="text-center">Fila</th>
                                <th class="text-center">Kart</th>
                                <th class="text-center">Ordem</th>
                                @if($batch->status !== 'locked')<th></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batch->draws->sortBy('queue_position') as $draw)
                                <tr>
                                    <td class="font-semibold text-sm">{{ $draw->stageCategoryEntry->pilot->displayName() }}
                                        @if($draw->is_manual)<span class="badge-blue text-xs ml-1">Manual</span>@endif
                                    </td>
                                    <td class="text-center font-bold">{{ $draw->queue_position }}</td>
                                    <td class="text-center font-mono font-bold text-ksa-navy">{{ $draw->kart_number }}</td>
                                    <td class="text-center text-ksa-muted">{{ $draw->draw_order }}</td>
                                    @if($batch->status !== 'locked')
                                        <td class="text-right pr-3">
                                            <form method="POST" action="{{ route('kart-draws.update', $draw) }}" class="flex items-center gap-1.5 justify-end">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="queue_position"
                                                    value="{{ $draw->queue_position }}"
                                                    min="1" max="{{ $batch->draws->count() }}"
                                                    class="form-input w-16 text-center px-2 py-1.5 text-sm min-h-0 h-9">
                                                <button class="btn-outline btn-sm" data-submitting-label="...">Trocar</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-sm text-ksa-muted">Nenhum sorteio realizado ainda.</p>
        @endforelse
    </div>
</div>

@endsection
