@extends('layouts.public')

@section('title', 'KSA Racing')

@section('content')
    @php
        $primaryActionUrl = auth()->check() ? route('dashboard') : route('login');
        $primaryActionLabel = auth()->check() ? 'Ir para o painel' : 'Login da administração';
        $registrationUrl = \Illuminate\Support\Facades\Route::has('public.registrations.create')
            ? route('public.registrations.create')
            : '#';
    @endphp

    {{-- Hero --}}
    <div class="min-h-screen bg-ksa-navy text-white">
        <header class="max-w-6xl mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-8">
                <span class="text-sm font-semibold text-white/70 tracking-wide">{{ $siteHost }}</span>
                <a href="{{ $primaryActionUrl }}" class="text-sm font-semibold border border-white/40 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">{{ $primaryActionLabel }}</a>
            </div>

            <div class="grid xl:grid-cols-7 gap-6 items-stretch">
                {{-- Left hero panel --}}
                <div class="xl:col-span-4 bg-white/10 rounded-2xl p-6 flex flex-col gap-4">
                    @if (file_exists(public_path('images/ksa-logo.png')))
                        <img src="{{ asset('images/ksa-logo.png') }}" alt="KSA Racing" class="h-14 w-auto object-contain self-start">
                    @endif

                    <div class="bg-yellow-400/20 border border-yellow-400/40 rounded-xl px-4 py-2 text-sm text-yellow-200">
                        Site em construção: a home pública já está no ar com a classificação do campeonato por categoria.
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-ksa-orange mb-1">Campeonato KSA Racing</p>
                        <h1 class="text-2xl md:text-3xl font-bold leading-tight">Pontuação oficial por categoria e etapas</h1>
                        <p class="text-white/70 mt-2 text-sm">Clique em uma categoria para abrir a classificação geral e a pontuação acumulada em cada etapa da temporada.</p>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-auto">
                        <a href="{{ $registrationUrl }}" class="bg-ksa-orange hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl transition-colors">Inscreva-se</a>
                        <a href="#categorias" class="border border-white/40 hover:bg-white/10 text-white font-semibold px-6 py-3 rounded-xl transition-colors">Ver classificações</a>
                    </div>
                </div>

                {{-- Right stats panel --}}
                <div class="xl:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-white/50 mb-1">Temporada em destaque</p>
                        <h2 class="text-xl font-bold">{{ $season?->name ?? 'Temporada não publicada' }}</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach([
                            ['label' => 'Categorias', 'value' => $categories->count()],
                            ['label' => 'Pilotos ranqueados', 'value' => $rankedPilotCount],
                            ['label' => 'Etapas lançadas', 'value' => $season?->stages->count() ?? 0],
                            ['label' => 'Última atualização', 'value' => $latestPublishedStage?->stage_date?->format('d/m') ?? '--/--'],
                        ] as $stat)
                            <div class="bg-white/10 rounded-xl p-3">
                                <div class="text-xs text-white/50">{{ $stat['label'] }}</div>
                                <div class="text-xl font-bold mt-0.5">{{ $stat['value'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if ($latestPublishedStage)
                        <div class="border-t border-white/10 pt-4">
                            <p class="text-xs font-bold uppercase tracking-widest text-white/50 mb-1">Etapa mais recente</p>
                            <div class="font-bold">{{ $latestPublishedStage->name }}</div>
                            <div class="text-sm text-white/60 mt-0.5">
                                {{ $latestPublishedStage->stage_date->format('d/m/Y') }}
                                @if ($latestPublishedStage->location) · {{ $latestPublishedStage->location }}@endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </header>
    </div>

    {{-- Classifications --}}
    <main id="categorias" class="max-w-6xl mx-auto px-4 py-10">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-ksa-navy/50 mb-1">Classificações</p>
            <h2 class="text-2xl font-bold text-ksa-navy">Pontuação do campeonato por categoria</h2>
            <p class="text-ksa-muted text-sm mt-1">A classificação geral considera os pontos válidos do campeonato. A tabela por etapa mostra o que cada piloto somou em cada prova.</p>
        </div>

        @if ($categories->isEmpty())
            <div class="text-center text-ksa-muted py-16 text-sm">
                Ainda não há categorias publicadas. As pontuações aparecerão aqui assim que forem lançadas.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($categories as $index => $category)
                    @php $leader = $category['standings']->first(); @endphp
                    <details class="card overflow-hidden" {{ $index === 0 ? 'open' : '' }}>
                        <summary class="flex flex-wrap justify-between items-center gap-3 px-4 py-4 cursor-pointer hover:bg-gray-50/70 list-none select-none">
                            <div>
                                <div class="font-bold text-ksa-navy">{{ $category['name'] }}</div>
                                <div class="text-xs text-ksa-muted mt-0.5">{{ $category['pilot_count'] }} pilotos ranqueados · {{ $category['stage_count'] }} etapas</div>
                            </div>
                            <div class="text-sm text-ksa-muted">
                                @if ($leader)
                                    Líder: <span class="font-semibold text-ksa-navy">{{ $leader->pilot->displayName() }}</span>
                                    · {{ number_format((float) $leader->total_valid_points, 2, ',', '.') }} pts
                                @else
                                    Pontuação em apuração
                                @endif
                            </div>
                        </summary>

                        <div class="border-t border-ksa-border p-4 space-y-6">
                            {{-- Classificação geral --}}
                            <div>
                                <div class="section-title mb-3">Classificação geral</div>
                                @if ($category['standings']->isEmpty())
                                    <p class="text-sm text-ksa-muted">Nenhuma pontuação publicada nesta categoria.</p>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="data-table">
                                            <thead><tr><th>Pos.</th><th>Piloto</th><th>Pontos válidos</th><th>Descartes</th></tr></thead>
                                            <tbody>
                                                @foreach ($category['standings'] as $standing)
                                                    <tr>
                                                        <td class="font-semibold">{{ $standing->final_position ?? '-' }}</td>
                                                        <td>{{ $standing->pilot->displayName() }}</td>
                                                        <td class="font-semibold">{{ number_format((float) $standing->total_valid_points, 2, ',', '.') }}</td>
                                                        <td class="text-ksa-muted">{{ number_format((float) $standing->discarded_points, 2, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Pontuação por etapa --}}
                            @if ($category['standings']->isNotEmpty() && $category['stages']->isNotEmpty())
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="section-title mb-0">Pontuação por etapa</div>
                                        <div class="flex gap-2 text-xs">
                                            <span class="badge-gray">Descartada</span>
                                            <span class="badge-navy">Sem descarte</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Piloto</th>
                                                    @foreach ($category['stages'] as $stage)
                                                        <th class="text-center">
                                                            <div class="font-bold">E{{ $stage->stage_number }}</div>
                                                            <div class="text-xs text-ksa-muted font-normal">{{ $stage->stage_date->format('d/m') }}</div>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($category['stage_rows'] as $row)
                                                    <tr>
                                                        <td class="font-semibold">{{ $row['pilot']->displayName() }}</td>
                                                        @foreach ($row['stages'] as $stagePoint)
                                                            <td class="text-center">
                                                                @if ($stagePoint['valid_points'] !== null)
                                                                    <div class="font-bold text-sm">{{ number_format((float) $stagePoint['valid_points'], 2, ',', '.') }}</div>
                                                                    @if ($stagePoint['is_discarded'])
                                                                        <span class="badge-gray text-xs mt-1">Desc.</span>
                                                                    @elseif ($stagePoint['discard_blocked'])
                                                                        <span class="badge-navy text-xs mt-1">Bloq.</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-ksa-muted">-</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </main>
@endsection
