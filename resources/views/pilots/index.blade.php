@extends('layouts.app')

@section('title', 'Pilotos')
@section('subtitle', 'Cadastro de pilotos do campeonato.')

@section('content')
    <div class="card">
        <div class="flex items-center justify-between p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Pilotos cadastrados</div>
            <a href="{{ route('pilots.create') }}" class="btn-primary btn-sm">+ Novo piloto</a>
        </div>

        @forelse ($pilots as $pilot)
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div class="flex items-center gap-3 min-w-0">
                    @if($pilot->photo_path)
                        <img src="{{ asset('storage/'.$pilot->photo_path) }}" alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-full bg-ksa-navy/10 flex items-center justify-center flex-shrink-0">
                            <span class="text-ksa-navy font-bold text-sm">{{ substr($pilot->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-semibold text-sm truncate">{{ $pilot->displayName() }}</div>
                        <div class="text-xs text-ksa-muted">{{ $pilot->city ?: '—' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    <span class="{{ $pilot->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $pilot->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                    <a href="{{ route('pilots.show', $pilot) }}" class="btn-outline btn-sm">Ver</a>
                    <a href="{{ route('pilots.edit', $pilot) }}" class="btn-ghost btn-sm">Editar</a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhum piloto cadastrado.</div>
        @endforelse

        @if($pilots->hasPages())
            <div class="p-4 border-t border-ksa-border">{{ $pilots->links() }}</div>
        @endif
    </div>
@endsection
