@extends('layouts.app')

@section('title', $stage->name)
@section('subtitle', 'Categorias da etapa e atalhos para gestão operacional.')

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Temporada</div>
                <div class="fw-semibold">{{ $stage->season->name }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Data</div>
                <div class="fw-semibold">{{ $stage->stage_date->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Briefing</div>
                <div class="fw-semibold">{{ $stage->briefing_time ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Sorteio</div>
                <div class="fw-semibold">{{ $stage->draw_time ?: '-' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($stage->stageCategories as $stageCategory)
            <div class="col-lg-4 col-md-6">
                <div class="content-card p-4 h-100">
                    <div class="section-title">{{ $stageCategory->seasonCategory->category->name }}</div>
                    <div class="small text-muted mb-3">{{ $stageCategory->entries->count() }} pilotos nesta etapa</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn btn-primary">Gestão da etapa</a>
                        <a href="{{ route('kart-draws.show', $stageCategory) }}" class="btn btn-outline-dark">Sorteio de karts</a>
                        <a href="{{ route('classifications.stage', $stageCategory) }}" class="btn btn-outline-secondary">Classificação da etapa</a>
                        <a href="{{ route('classifications.championship', $stageCategory->seasonCategory) }}" class="btn btn-outline-secondary">Classificação do campeonato</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">Nenhuma categoria provisionada para esta etapa.</div>
        @endforelse
    </div>
@endsection
