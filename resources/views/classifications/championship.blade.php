@extends('layouts.app')

@section('title', 'Classificação do Campeonato')
@section('subtitle', $seasonCategory->season->name.' / '.$seasonCategory->category->name)

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('classifications.championship.csv', $seasonCategory) }}" class="btn btn-outline-dark btn-sm">CSV</a>
            <a href="{{ route('classifications.championship.pdf', $seasonCategory) }}" class="btn btn-outline-dark btn-sm">PDF</a>
        </div>
    </div>

    <div class="content-card p-4">
        <div class="section-title">Ranking geral</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Pos.</th><th>Piloto</th><th>Pontos válidos</th><th>Descartados</th><th>Etapas</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($points as $standing)
                        <tr class="{{ $standing->final_position <= 5 ? 'table-warning' : '' }}">
                            <td>{{ $standing->final_position }}</td>
                            <td>
                                {{ $standing->pilot->displayName() }}
                                @if($standing->final_position <= 5)<span class="badge text-bg-warning">Top 5</span>@endif
                                @if($standing->promotion_eligible)<span class="badge text-bg-info">Promoção</span>@endif
                            </td>
                            <td>{{ $standing->total_valid_points }}</td>
                            <td>{{ $standing->discarded_points }}</td>
                            <td>
                                @foreach (($pointMatrix[$standing->pilot_id] ?? collect()) as $point)
                                    <span class="badge {{ $point->is_discarded ? 'text-bg-danger' : 'badge-soft' }}">
                                        E{{ $point->stage->stage_number }} {{ $point->valid_points }}
                                        @if($point->is_discarded) DESCARTADA @endif
                                    </span>
                                @endforeach
                            </td>
                            <td>{{ collect($standing->tiebreak_counters)->sortKeys()->map(fn($count, $pos) => $count.'x'.$pos.'º')->join(' / ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Sem classificação consolidada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
