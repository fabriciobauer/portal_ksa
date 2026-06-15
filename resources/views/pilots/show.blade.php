@extends('layouts.app')

@section('title', $pilot->displayName())
@section('subtitle', 'Histórico completo do piloto por temporada, categoria e classificação.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="content-card p-4">
                <div class="section-title">Dados do piloto</div>
                <dl class="row mb-0">
                    <dt class="col-5">Nome</dt>
                    <dd class="col-7">{{ $pilot->name }}</dd>
                    <dt class="col-5">Apelido</dt>
                    <dd class="col-7">{{ $pilot->nickname ?: '-' }}</dd>
                    <dt class="col-5">Cidade</dt>
                    <dd class="col-7">{{ $pilot->city ?: '-' }}</dd>
                    <dt class="col-5">Peso base</dt>
                    <dd class="col-7">{{ $pilot->base_weight ? number_format($pilot->base_weight, 2, ',', '.') . ' kg' : '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="content-card p-4 mb-4">
                <div class="section-title">Inscrições por temporada</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Temporada</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pilot->registrations as $registration)
                                <tr>
                                    <td>{{ $registration->seasonCategory->season->name }}</td>
                                    <td>{{ $registration->seasonCategory->category->name }}</td>
                                    <td>{{ strtoupper($registration->registration_type) }}</td>
                                    <td>{{ ucfirst($registration->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center">Sem inscrições registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card p-4 mb-4">
                <div class="section-title">Classificação geral</div>
                <div class="vstack gap-2">
                    @forelse ($pilot->championshipStandings as $standing)
                        <div class="border rounded-3 p-3 d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $standing->seasonCategory->category->name }}</div>
                                <div class="small text-muted">Posição final {{ $standing->final_position ?: '-' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">{{ number_format($standing->total_valid_points, 2, ',', '.') }} pts</div>
                                @if($standing->promotion_eligible)
                                    <span class="badge text-bg-warning">Elegível à promoção</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Ainda não há classificação consolidada para este piloto.</div>
                    @endforelse
                </div>
            </div>

            <div class="content-card p-4">
                <div class="section-title">Participações em etapas</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th>Categoria</th>
                                <th>Presença</th>
                                <th>Briefing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pilot->stageEntries as $entry)
                                <tr>
                                    <td>{{ $entry->stageCategory->stage->name }}</td>
                                    <td>{{ $entry->stageCategory->seasonCategory->category->name }}</td>
                                    <td>{{ ucfirst($entry->attendance_status) }}</td>
                                    <td>{{ ucfirst($entry->briefing_status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Sem participação em etapas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
