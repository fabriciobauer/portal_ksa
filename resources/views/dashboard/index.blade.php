@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Resumo operacional da temporada atual.')

@section('content')
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="card p-4" style="background: linear-gradient(135deg,#17304f,#1d4f91)">
            <div class="text-xs text-white/60 font-semibold uppercase tracking-wide">Temporada atual</div>
            <div class="text-lg font-bold text-white mt-1 truncate">{{ $currentSeason?->name ?? '—' }}</div>
        </div>
        <div class="card p-4" style="background: linear-gradient(135deg,#1c7c54,#2f9e68)">
            <div class="text-xs text-white/60 font-semibold uppercase tracking-wide">Próximas etapas</div>
            <div class="text-3xl font-bold text-white mt-1">{{ $nextStages->count() }}</div>
        </div>
        <div class="card p-4" style="background: linear-gradient(135deg,#cc6a00,#f79009)">
            <div class="text-xs text-white/60 font-semibold uppercase tracking-wide">Categorias ativas</div>
            <div class="text-3xl font-bold text-white mt-1">{{ $categoryOccupancy->count() }}</div>
        </div>
        <div class="card p-4" style="background: linear-gradient(135deg,#b42318,#d92d20)">
            <div class="text-xs text-white/60 font-semibold uppercase tracking-wide">Alertas promoção</div>
            <div class="text-3xl font-bold text-white mt-1">{{ $promotionAlerts->count() }}</div>
        </div>
    </div>

    {{-- Próximas etapas --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="section-title mb-0">Próximas etapas</div>
            <a href="{{ route('stages.index') }}" class="btn-outline btn-sm">Ver todas</a>
        </div>
        @forelse ($nextStages as $stage)
            <a href="{{ route('stages.show', $stage) }}"
               class="flex items-center justify-between py-3 border-b border-ksa-border last:border-0 hover:bg-gray-50/60 -mx-4 px-4 transition">
                <div>
                    <div class="font-semibold text-sm">{{ $stage->name }}</div>
                    <div class="text-xs text-ksa-muted">{{ $stage->season->name }} · {{ $stage->stage_date->format('d/m/Y') }}</div>
                </div>
                <span class="badge-gray">{{ ucfirst($stage->status) }}</span>
            </a>
        @empty
            <p class="text-sm text-ksa-muted">Nenhuma etapa próxima cadastrada.</p>
        @endforelse
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        {{-- Líderes --}}
        <div class="card p-4">
            <div class="section-title">Líderes por categoria</div>
            <div class="space-y-2">
                @forelse ($leaders as $leader)
                    <div class="flex items-center justify-between py-2 border-b border-ksa-border last:border-0">
                        <div>
                            <div class="text-xs text-ksa-muted">{{ $leader->seasonCategory->category->name }}</div>
                            <div class="font-semibold text-sm">{{ $leader->pilot->displayName() }}</div>
                        </div>
                        <span class="font-bold text-ksa-navy text-sm">{{ number_format($leader->total_valid_points, 2, ',', '.') }} pts</span>
                    </div>
                @empty
                    <p class="text-sm text-ksa-muted">Sem líderes consolidados.</p>
                @endforelse
            </div>
        </div>

        {{-- Ocupação --}}
        <div class="card p-4">
            <div class="section-title">Ocupação por categoria</div>
            <div class="space-y-3">
                @forelse ($categoryOccupancy as $item)
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-sm">{{ $item['season_category']->category->name }}</div>
                            <div class="text-xs text-ksa-muted">{{ $item['confirmed'] }} confirmados{{ $item['waiting'] > 0 ? ' · '.$item['waiting'].' em espera' : '' }}</div>
                        </div>
                        <span class="badge-gray flex-shrink-0">Até {{ $item['operational_limit'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-ksa-muted">Nenhuma categoria ativa.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if($promotionAlerts->isNotEmpty())
        <div class="card p-4">
            <div class="section-title">Alertas de promoção</div>
            <div class="space-y-2">
                @foreach ($promotionAlerts as $alert)
                    <div class="flex items-center gap-2 py-2 border-b border-ksa-border last:border-0">
                        <span class="badge-orange">Promoção</span>
                        <div>
                            <div class="font-semibold text-sm">{{ $alert->pilot->displayName() }}</div>
                            <div class="text-xs text-ksa-muted">{{ $alert->seasonCategory->category->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
