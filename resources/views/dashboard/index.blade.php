@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Resumo operacional do campeonato e alertas da temporada atual.')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card p-4" style="background: linear-gradient(135deg, #17304f, #1d4f91);">
                <div class="small text-white-50">Temporada atual</div>
                <div class="fs-4 fw-bold">{{ $currentSeason?->name ?? 'Não definida' }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card p-4" style="background: linear-gradient(135deg, #1c7c54, #2f9e68);">
                <div class="small text-white-50">Próximas etapas</div>
                <div class="fs-4 fw-bold">{{ $nextStages->count() }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card p-4" style="background: linear-gradient(135deg, #cc6a00, #f79009);">
                <div class="small text-white-50">Categorias ativas</div>
                <div class="fs-4 fw-bold">{{ $categoryOccupancy->count() }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card p-4" style="background: linear-gradient(135deg, #b42318, #d92d20);">
                <div class="small text-white-50">Alertas de promoção</div>
                <div class="fs-4 fw-bold">{{ $promotionAlerts->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="content-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Próximas etapas</div>
                    <a href="{{ route('stages.index') }}" class="btn btn-sm btn-outline-dark">Ver etapas</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th>Temporada</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nextStages as $stage)
                                <tr>
                                    <td>{{ $stage->name }}</td>
                                    <td>{{ $stage->season->name }}</td>
                                    <td>{{ $stage->stage_date->format('d/m/Y') }}</td>
                                    <td><span class="badge text-bg-secondary">{{ ucfirst($stage->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nenhuma próxima etapa cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card p-4">
                <div class="section-title">Líderes por categoria</div>
                <div class="row g-3">
                    @forelse ($leaders as $leader)
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="small text-muted">{{ $leader->seasonCategory->category->name }}</div>
                                <div class="fw-semibold">{{ $leader->pilot->displayName() }}</div>
                                <div>{{ number_format($leader->total_valid_points, 2, ',', '.') }} pts</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">Ainda não há líderes consolidados.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="content-card p-4 mb-4">
                <div class="section-title">Ocupação por categoria</div>
                <div class="vstack gap-3">
                    @forelse ($categoryOccupancy as $item)
                        <div class="border rounded-4 p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold">{{ $item['season_category']->category->name }}</div>
                                    <div class="small text-muted">{{ $item['season_category']->season->name }}</div>
                                </div>
                                <span class="badge text-bg-secondary">
                                    Até {{ $item['operational_limit'] }} karts/etapa
                                </span>
                            </div>
                            <div class="small text-muted mt-2">{{ $item['confirmed'] }} inscrições confirmadas na temporada</div>
                            @if($item['waiting'] > 0)
                                <div class="small text-muted">{{ $item['waiting'] }} inscrições em espera</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">Nenhuma categoria ativa vinculada à temporada atual.</div>
                    @endforelse
                </div>
            </div>

            <div class="content-card p-4 mb-4">
                <div class="section-title">Última etapa realizada</div>
                @if ($latestStage)
                    <div class="fw-semibold">{{ $latestStage->name }}</div>
                    <div class="text-muted">{{ $latestStage->season->name }}</div>
                    <div class="mt-2">{{ $latestStage->stage_date->format('d/m/Y') }}</div>
                @else
                    <div class="text-muted">Nenhuma etapa fechada até o momento.</div>
                @endif
            </div>

            <div class="content-card p-4">
                <div class="section-title">Alertas de promoção</div>
                <div class="vstack gap-2">
                    @forelse ($promotionAlerts as $alert)
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold">{{ $alert->pilot->displayName() }}</div>
                            <div class="small text-muted">{{ $alert->seasonCategory->category->name }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Nenhum piloto elegível no momento.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
