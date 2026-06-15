@extends('layouts.app')

@section('title', $season->name)
@section('subtitle', 'Categorias e etapas desta temporada.')

@section('content')
    <div class="card p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
            <div><div class="text-xs text-ksa-muted">Status</div><div class="font-semibold mt-0.5">{{ ucfirst($season->status) }}</div></div>
            <div>
                <div class="text-xs text-ksa-muted">Temporada atual</div>
                <div class="mt-0.5">
                    <span class="{{ $season->is_current ? 'badge-green' : 'badge-gray' }}">{{ $season->is_current ? 'Sim' : 'Não' }}</span>
                </div>
            </div>
            <div>
                <div class="text-xs text-ksa-muted">Período</div>
                <div class="font-semibold text-sm mt-0.5">
                    {{ optional($season->start_date)->format('d/m/Y') ?: '—' }} até {{ optional($season->end_date)->format('d/m/Y') ?: '—' }}
                </div>
            </div>
        </div>
        <div class="pt-4 border-t border-ksa-border flex gap-2">
            <a href="{{ route('seasons.edit', $season) }}" class="btn-primary btn-sm">Editar</a>
            <a href="{{ route('seasons.index') }}" class="btn-ghost btn-sm">← Voltar</a>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Categorias</div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Categoria</th><th class="text-right">Limite/etapa</th><th>Ativa</th></tr></thead>
                <tbody>
                    @forelse ($season->seasonCategories as $sc)
                        <tr>
                            <td class="font-semibold">{{ $sc->category->name }}</td>
                            <td class="text-right">{{ $sc->effectivePilotLimit() }}</td>
                            <td><span class="{{ $sc->is_active ? 'badge-green' : 'badge-gray' }}">{{ $sc->is_active ? 'Sim' : 'Não' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-ksa-muted py-6">Sem categorias vinculadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Etapas</div>
        </div>
        @forelse ($season->stages as $stage)
            <a href="{{ route('stages.show', $stage) }}"
               class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div>
                    <div class="font-semibold text-sm">{{ $stage->name }}</div>
                    <div class="text-xs text-ksa-muted">{{ $stage->stage_date->format('d/m/Y') }}</div>
                </div>
                <span class="{{ match($stage->status) { 'open'=>'badge-green','closed'=>'badge-navy',default=>'badge-gray' } }}">{{ ucfirst($stage->status) }}</span>
            </a>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhuma etapa nesta temporada.</div>
        @endforelse
    </div>
@endsection
