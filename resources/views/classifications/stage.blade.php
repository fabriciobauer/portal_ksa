@extends('layouts.app')

@section('title', 'Classificação da Etapa')
@section('subtitle', $stageCategory->stage->name.' / '.$stageCategory->seasonCategory->category->name)

@section('content')
    <div class="card p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('classifications.stage.csv', $stageCategory) }}" class="btn-outline btn-sm">📄 CSV</a>
            <a href="{{ route('classifications.stage.pdf', $stageCategory) }}" class="btn-outline btn-sm">🖨️ PDF</a>
            <a href="{{ route('stage-management.show', $stageCategory) }}" class="btn-ghost btn-sm">← Gestão</a>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Resultado oficial</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pos.</th>
                        <th>Piloto</th>
                        <th class="text-right">R1</th>
                        <th class="text-right">R2</th>
                        <th class="text-right">Bônus</th>
                        <th class="text-right">Etapa</th>
                        <th class="text-right">Camp.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($standings as $standing)
                        <tr class="{{ $standing->stage_position <= 5 ? 'data-table-highlight' : '' }}">
                            <td>
                                @if($standing->stage_position <= 3)
                                    <span class="text-xl">{{ ['🥇','🥈','🥉'][$standing->stage_position - 1] }}</span>
                                @else
                                    <span class="font-bold text-lg">{{ $standing->stage_position }}º</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-semibold">{{ $standing->stageCategoryEntry->pilot->displayName() }}</div>
                                @if($standing->stage_position <= 5)<span class="badge-yellow text-xs">Top 5</span>@endif
                                @if($standing->is_disqualified)<span class="badge-red text-xs">DSQ</span>@endif
                            </td>
                            <td class="text-right font-mono">{{ $standing->race1_points }}</td>
                            <td class="text-right font-mono">{{ $standing->race2_points }}</td>
                            <td class="text-right font-mono">{{ $standing->completion_bonus }}</td>
                            <td class="text-right font-bold">{{ $standing->gross_stage_points }}</td>
                            <td class="text-right font-bold text-ksa-navy">{{ $standing->championship_points }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-ksa-muted py-8">Sem classificação consolidada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
