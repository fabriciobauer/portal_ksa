@extends('layouts.app')

@section('title', 'Temporadas')
@section('subtitle', 'Gerenciamento das temporadas do campeonato.')

@section('content')
    <div class="card">
        <div class="flex items-center justify-between p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Temporadas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('seasons.create') }}" class="btn-primary btn-sm">+ Nova temporada</a>
            @endif
        </div>
        @forelse ($seasons as $season)
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div>
                    <div class="font-semibold text-sm">{{ $season->name }}</div>
                    <div class="text-xs text-ksa-muted">
                        {{ optional($season->start_date)->format('d/m/Y') }} – {{ optional($season->end_date)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    @if($season->is_current)<span class="badge-green">Atual</span>@endif
                    <span class="badge-gray">{{ ucfirst($season->status) }}</span>
                    <a href="{{ route('seasons.show', $season) }}" class="btn-outline btn-sm">Abrir</a>
                    <a href="{{ route('seasons.edit', $season) }}" class="btn-ghost btn-sm">Editar</a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhuma temporada cadastrada.</div>
        @endforelse
    </div>
@endsection
