@extends('layouts.app')

@section('title', $season->name)
@section('subtitle', 'Detalhes da temporada, categorias vinculadas e etapas cadastradas.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="content-card p-4">
                <div class="section-title">Resumo</div>
                <dl class="row mb-0">
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">{{ ucfirst($season->status) }}</dd>
                    <dt class="col-5">Atual</dt>
                    <dd class="col-7">{{ $season->is_current ? 'Sim' : 'Não' }}</dd>
                    <dt class="col-5">Período</dt>
                    <dd class="col-7">{{ optional($season->start_date)->format('d/m/Y') ?: '-' }} até {{ optional($season->end_date)->format('d/m/Y') ?: '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="content-card p-4 mb-4">
                <div class="section-title">Categorias</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Limite por etapa</th>
                                <th>Ativa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($season->seasonCategories as $seasonCategory)
                                <tr>
                                    <td>{{ $seasonCategory->category->name }}</td>
                                    <td>{{ $seasonCategory->effectivePilotLimit() }}</td>
                                    <td>{{ $seasonCategory->is_active ? 'Sim' : 'Não' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center">Sem categorias vinculadas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card p-4">
                <div class="section-title">Etapas</div>
                <div class="vstack gap-2">
                    @forelse ($season->stages as $stage)
                        <a href="{{ route('stages.show', $stage) }}" class="text-decoration-none border rounded-3 p-3 d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $stage->name }}</div>
                                <div class="small text-muted">{{ $stage->stage_date->format('d/m/Y') }}</div>
                            </div>
                            <span class="badge text-bg-secondary">{{ ucfirst($stage->status) }}</span>
                        </a>
                    @empty
                        <div class="text-muted">Nenhuma etapa cadastrada nesta temporada.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
