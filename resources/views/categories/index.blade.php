@extends('layouts.app')

@section('title', 'Categorias')
@section('subtitle', 'Gestão das categorias do campeonato.')

@section('content')
    <div class="card">
        <div class="flex items-center justify-between p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Categorias cadastradas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('categories.create') }}" class="btn-primary btn-sm">+ Nova</a>
            @endif
        </div>
        @forelse ($categories as $category)
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div>
                    <div class="font-semibold text-sm">{{ $category->name }}</div>
                    <div class="text-xs text-ksa-muted">
                        {{ $category->slug }}@if($category->target_weight) · {{ number_format($category->target_weight, 0) }} kg@endif · Limite: {{ $category->default_pilot_limit }}
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    <span class="{{ $category->is_active ? 'badge-green' : 'badge-gray' }}">{{ $category->is_active ? 'Ativa' : 'Inativa' }}</span>
                    <a href="{{ route('categories.edit', $category) }}" class="btn-ghost btn-sm">Editar</a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhuma categoria cadastrada.</div>
        @endforelse
        @if($categories->hasPages())
            <div class="p-4 border-t border-ksa-border">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
