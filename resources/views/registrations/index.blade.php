@extends('layouts.app')

@section('title', 'Inscrições esportivas')
@section('subtitle', 'Vínculo entre piloto, categoria e temporada.')

@section('content')
    <div class="card">
        <div class="flex items-center justify-between p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Inscrições registradas</div>
            <a href="{{ route('registrations.create') }}" class="btn-primary btn-sm">+ Nova</a>
        </div>

        @forelse ($registrations as $registration)
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div class="min-w-0">
                    <div class="font-semibold text-sm">{{ $registration->pilot->displayName() }}</div>
                    <div class="text-xs text-ksa-muted">
                        {{ $registration->seasonCategory->season->name }} · {{ $registration->seasonCategory->category->name }}
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    <span class="badge-gray uppercase text-xs">{{ $registration->registration_type }}</span>
                    <span class="{{ $registration->status === 'confirmed' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($registration->status) }}</span>
                    <a href="{{ route('registrations.edit', $registration) }}" class="btn-ghost btn-sm">Editar</a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhuma inscrição registrada.</div>
        @endforelse

        @if($registrations->hasPages())
            <div class="p-4 border-t border-ksa-border">{{ $registrations->links() }}</div>
        @endif
    </div>

    @if($seasonCategories->isNotEmpty())
        <div class="card p-4">
            <div class="section-title">Resumo por categoria</div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($seasonCategories as $seasonCategory)
                    <div class="border border-ksa-border rounded-xl p-3">
                        <div class="font-semibold text-sm">{{ $seasonCategory->category->name }}</div>
                        <div class="text-xs text-ksa-muted">{{ $seasonCategory->season->name }}</div>
                        <div class="mt-2 text-sm">{{ $seasonCategory->confirmedRegistrationsCount() }} confirmadas na temporada</div>
                        <div class="text-xs text-ksa-muted">Limite/etapa: {{ $seasonCategory->effectivePilotLimit() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
