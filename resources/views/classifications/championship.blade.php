@extends('layouts.app')

@section('title', 'Campeonato')
@section('subtitle', $seasonCategory->season->name.' / '.$seasonCategory->category->name)

@section('content')
    <div class="card p-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('classifications.championship.csv', $seasonCategory) }}" class="btn-outline btn-sm">📄 CSV</a>
            <a href="{{ route('classifications.championship.pdf', $seasonCategory) }}" class="btn-outline btn-sm">🖨️ PDF</a>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Ranking geral</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pos.</th>
                        <th>Piloto</th>
                        <th class="text-right">Pts válidos</th>
                        <th class="text-right">Descartado</th>
                        <th>Etapas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($points as $standing)
                        <tr class="{{ $standing->final_position <= 5 ? 'data-table-highlight' : '' }}">
                            <td>
                                @if($standing->final_position <= 3)
                                    <span class="text-xl">{{ ['🥇','🥈','🥉'][$standing->final_position - 1] }}</span>
                                @else
                                    <span class="font-bold text-lg">{{ $standing->final_position }}º</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-semibold">{{ $standing->pilot->displayName() }}</div>
                                @if($standing->final_position <= 5)<span class="badge-yellow text-xs">Top 5</span>@endif
                                @if($standing->promotion_eligible)<span class="badge-blue text-xs">Promoção</span>@endif
                            </td>
                            <td class="text-right font-bold text-ksa-navy text-lg">{{ $standing->total_valid_points }}</td>
                            <td class="text-right text-ksa-muted">{{ $standing->discarded_points ?: '—' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach (($pointMatrix[$standing->pilot_id] ?? collect()) as $point)
                                        <span class="badge text-xs {{ $point->is_discarded ? 'bg-red-100 text-ksa-red line-through' : 'badge-navy' }}">
                                            E{{ $point->stage->stage_number }} {{ $point->valid_points }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-ksa-muted py-8">Sem classificação consolidada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
