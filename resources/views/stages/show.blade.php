@extends('layouts.app')

@section('title', $stage->name)
@section('subtitle', $stage->season->name.' · '.$stage->stage_date->format('d/m/Y'))

@section('content')
    {{-- Info da etapa --}}
    <div class="card p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-xs text-ksa-muted font-semibold uppercase tracking-wide">Briefing</div>
                <div class="font-bold mt-1">{{ $stage->briefing_time ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-ksa-muted font-semibold uppercase tracking-wide">Sorteio</div>
                <div class="font-bold mt-1">{{ $stage->draw_time ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-ksa-muted font-semibold uppercase tracking-wide">Local</div>
                <div class="font-bold mt-1 text-sm">{{ $stage->location ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-ksa-muted font-semibold uppercase tracking-wide">Traçado</div>
                <div class="font-bold mt-1 text-sm">{{ $stage->track_layout ?: '—' }}</div>
            </div>
        </div>
        @if(auth()->user()->isAdmin())
            <div class="mt-4 pt-4 border-t border-ksa-border">
                <a href="{{ route('stages.edit', $stage) }}" class="btn-outline btn-sm">Editar etapa</a>
            </div>
        @endif
    </div>

    {{-- Categorias --}}
    <div class="section-title px-0.5 mt-2">Categorias desta etapa</div>

    @forelse ($stage->stageCategories as $stageCategory)
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="font-bold">{{ $stageCategory->seasonCategory->category->name }}</div>
                    <div class="text-xs text-ksa-muted mt-0.5">{{ $stageCategory->entries->count() }} pilotos</div>
                </div>
                @php
                    $sc = match($stageCategory->status) {
                        'open'   => 'badge-green',
                        'closed' => 'badge-navy',
                        default  => 'badge-gray',
                    };
                @endphp
                <span class="{{ $sc }}">{{ ucfirst($stageCategory->status) }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn-primary btn-sm btn-block">
                    Gestão da etapa
                </a>
                <a href="{{ route('kart-draws.show', $stageCategory) }}" class="btn-outline btn-sm btn-block">
                    Sorteio
                </a>
                <a href="{{ route('classifications.stage', $stageCategory) }}" class="btn-ghost btn-sm btn-block col-span-1">
                    Classificação etapa
                </a>
                <a href="{{ route('classifications.championship', $stageCategory->seasonCategory) }}" class="btn-ghost btn-sm btn-block col-span-1">
                    Campeonato
                </a>
            </div>
        </div>
    @empty
        <div class="card p-8 text-center text-sm text-ksa-muted">Nenhuma categoria provisionada.</div>
    @endforelse
@endsection
