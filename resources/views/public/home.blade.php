@extends('layouts.public')

@section('title', 'KSA Racing')
@section('body_class', 'public-home')

@section('content')
    @php
        $primaryActionUrl = auth()->check() ? route('dashboard') : route('login');
        $primaryActionLabel = auth()->check() ? 'Ir para o painel' : 'Login da administração';
        $registrationUrl = \Illuminate\Support\Facades\Route::has('public.registrations.create')
            ? route('public.registrations.create')
            : '#';
    @endphp

    <div class="public-shell">
        <header class="public-hero">
            <div class="container py-4 py-lg-5">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                    <span class="hero-domain">{{ $siteHost }}</span>
                    <a href="{{ $primaryActionUrl }}" class="btn btn-outline-light px-4">{{ $primaryActionLabel }}</a>
                </div>

                <div class="row g-4 align-items-stretch">
                    <div class="col-xl-7">
                        <div class="hero-panel h-100">
                            <img src="{{ asset('images/ksa-logo.png') }}" alt="Logo KSA Racing" class="hero-logo">

                            <div class="construction-banner">
                                Site em construção: a home pública já está no ar com a classificação do campeonato por categoria.
                            </div>

                            <div class="hero-copy">
                                <p class="hero-kicker mb-2">Campeonato KSA Racing</p>
                                <h1 class="hero-title">
                                    Pontuação oficial por categoria e etapas
                                </h1>
                                <p class="hero-description mb-0">
                                    Clique em uma categoria para abrir a classificação geral e a pontuação acumulada em cada etapa da temporada.
                                </p>
                            </div>

                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a href="{{ $registrationUrl }}"
                                   class="btn btn-warning btn-lg px-4"
                                   data-ga-event="home_inscreva_se_click"
                                   data-ga-params='@json(["page_type" => "home", "section" => "hero", "source" => \App\Support\Analytics::source()])'>
                                    Inscreva-se
                                </a>
                                <a href="#categorias" class="btn btn-outline-light btn-lg px-4">Ver classificações</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="hero-sidecard h-100">
                            <div class="section-eyebrow">Temporada em destaque</div>
                            <h2 class="hero-side-title">{{ $season?->name ?? 'Temporada não publicada' }}</h2>

                            <div class="hero-stat-grid">
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Categorias</span>
                                    <strong>{{ $categories->count() }}</strong>
                                </div>
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Pilotos ranqueados</span>
                                    <strong>{{ $rankedPilotCount }}</strong>
                                </div>
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Etapas lançadas</span>
                                    <strong>{{ $season?->stages->count() ?? 0 }}</strong>
                                </div>
                                <div class="hero-stat-card">
                                    <span class="hero-stat-label">Última atualização</span>
                                    <strong>{{ $latestPublishedStage?->stage_date?->format('d/m') ?? '--/--' }}</strong>
                                </div>
                            </div>

                            @if ($latestPublishedStage)
                                <div class="latest-stage-card">
                                    <div class="section-eyebrow">Etapa mais recente</div>
                                    <div class="latest-stage-title">{{ $latestPublishedStage->name }}</div>
                                    <div class="latest-stage-meta">
                                        {{ $latestPublishedStage->stage_date->format('d/m/Y') }}
                                        @if ($latestPublishedStage->location)
                                            · {{ $latestPublishedStage->location }}
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="categorias" class="container py-5">
            <div class="section-header">
                <div>
                    <span class="section-eyebrow">Classificações</span>
                    <h2 class="section-title-public mb-0">Pontuação do campeonato por categoria</h2>
                </div>
                <p class="section-description mb-0">
                    A classificação geral considera os pontos válidos do campeonato. Dentro de cada categoria, a tabela por etapa mostra o que cada piloto somou em cada prova.
                </p>
            </div>

            @if ($categories->isEmpty())
                <div class="empty-public-state">
                    Ainda não há categorias publicadas para exibição. Assim que as pontuações forem lançadas, elas aparecerão aqui.
                </div>
            @else
                <div class="accordion public-accordion" id="publicCategoryAccordion">
                    @foreach ($categories as $index => $category)
                        @php
                            $leader = $category['standings']->first();
                        @endphp

                        <div class="accordion-item public-category-card">
                            <h2 class="accordion-header" id="heading-{{ $category['id'] }}">
                                <button
                                    class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $category['id'] }}"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-controls="collapse-{{ $category['id'] }}"
                                >
                                    <span class="category-headline">
                                        <span class="category-name">{{ $category['name'] }}</span>
                                        <span class="category-meta">
                                            {{ $category['pilot_count'] }} pilotos ranqueados · {{ $category['stage_count'] }} etapas cadastradas
                                        </span>
                                    </span>

                                    <span class="category-leader">
                                        @if ($leader)
                                            Líder: {{ $leader->pilot->displayName() }} · {{ number_format((float) $leader->total_valid_points, 2, ',', '.') }} pts
                                        @else
                                            Pontuação em apuração
                                        @endif
                                    </span>
                                </button>
                            </h2>

                            <div
                                id="collapse-{{ $category['id'] }}"
                                class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                aria-labelledby="heading-{{ $category['id'] }}"
                                data-bs-parent="#publicCategoryAccordion"
                            >
                                <div class="accordion-body">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="public-card p-0 overflow-hidden">
                                                <div class="card-header-public">
                                                    <div>
                                                        <div class="card-title-public">Classificação geral</div>
                                                        <div class="card-subtitle-public">
                                                            {{ $category['description'] ?: 'Ranking atualizado com base nas pontuações válidas da temporada.' }}
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($category['standings']->isEmpty())
                                                    <div class="p-4 text-muted">
                                                        Nenhuma pontuação publicada nesta categoria até o momento.
                                                    </div>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table public-table align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Pos.</th>
                                                                    <th>Piloto</th>
                                                                    <th>Pontos válidos</th>
                                                                    <th>Descartes</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($category['standings'] as $standing)
                                                                    <tr>
                                                                        <td class="fw-semibold">{{ $standing->final_position ?? '-' }}</td>
                                                                        <td>{{ $standing->pilot->displayName() }}</td>
                                                                        <td class="fw-semibold">{{ number_format((float) $standing->total_valid_points, 2, ',', '.') }}</td>
                                                                        <td>{{ number_format((float) $standing->discarded_points, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="public-card p-0 overflow-hidden">
                                                <div class="card-header-public stage-card-header">
                                                    <div>
                                                        <div class="card-title-public">Pontuação por etapa</div>
                                                        <div class="card-subtitle-public">
                                                            Clique em outra categoria para alternar o detalhamento. Etapas descartadas aparecem sinalizadas.
                                                        </div>
                                                    </div>
                                                    <div class="point-legend">
                                                        <span class="legend-pill">Descartada</span>
                                                        <span class="legend-pill legend-pill-blocked">Sem descarte</span>
                                                    </div>
                                                </div>

                                                @if ($category['standings']->isEmpty() || $category['stages']->isEmpty())
                                                    <div class="p-4 text-muted">
                                                        As etapas desta categoria ainda não possuem pontuação consolidada para exibição.
                                                    </div>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table public-table stage-points-table align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Piloto</th>
                                                                    @foreach ($category['stages'] as $stage)
                                                                        <th class="text-center">
                                                                            <div class="stage-heading">E{{ $stage->stage_number }}</div>
                                                                            <small>{{ $stage->stage_date->format('d/m') }}</small>
                                                                        </th>
                                                                    @endforeach
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($category['stage_rows'] as $row)
                                                                    <tr>
                                                                        <td class="fw-semibold">{{ $row['pilot']->displayName() }}</td>
                                                                        @foreach ($row['stages'] as $stagePoint)
                                                                            <td class="text-center">
                                                                                @if ($stagePoint['valid_points'] !== null)
                                                                                    <div class="stage-point-value">
                                                                                        {{ number_format((float) $stagePoint['valid_points'], 2, ',', '.') }}
                                                                                    </div>

                                                                                    @if ($stagePoint['is_discarded'])
                                                                                        <span class="legend-pill mt-2">Descartada</span>
                                                                                    @elseif ($stagePoint['discard_blocked'])
                                                                                        <span class="legend-pill legend-pill-blocked mt-2">Sem descarte</span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
@endsection
