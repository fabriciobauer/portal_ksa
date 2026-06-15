@extends('layouts.app')

@section('title', 'Classificacao da Etapa')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('content')
    <div class="content-card p-4 mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('classifications.stage.csv', $stageCategory) }}" class="btn btn-outline-dark btn-sm">CSV</a>
            <a href="{{ route('classifications.stage.pdf', $stageCategory) }}" class="btn btn-outline-dark btn-sm">PDF</a>
            <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
        </div>
    </div>

    <div class="content-card p-4">
        <div class="section-title">Resultado oficial</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Pos.</th><th>Piloto</th><th>R1</th><th>R2</th><th>Bonus camp.</th><th>Bruto etapa</th><th>Campeonato</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($standings as $standing)
                        <tr class="{{ $standing->stage_position <= 5 ? 'table-warning' : '' }}">
                            <td>{{ $standing->stage_position }}</td>
                            <td>
                                {{ $standing->stageCategoryEntry->pilot->displayName() }}
                                @if($standing->stage_position <= 5)<span class="badge text-bg-warning">Top 5</span>@endif
                                @if($standing->is_disqualified)<span class="badge text-bg-danger">Desclassificado</span>@endif
                            </td>
                            <td>{{ $standing->race1_points }}</td>
                            <td>{{ $standing->race2_points }}</td>
                            <td>{{ $standing->completion_bonus }}</td>
                            <td>{{ $standing->gross_stage_points }}</td>
                            <td>{{ $standing->championship_points }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Sem classificacao consolidada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
