@extends('layouts.app')

@section('title', 'Etapas')
@section('subtitle', 'Planejamento e operação das etapas do campeonato.')

@section('content')
    <div class="card">
        <div class="flex items-center justify-between p-4 border-b border-ksa-border">
            <div class="section-title mb-0">Etapas cadastradas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('stages.create') }}" class="btn-primary btn-sm">+ Nova etapa</a>
            @endif
        </div>

        @forelse ($stages as $stage)
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-ksa-border last:border-0 hover:bg-gray-50/60">
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-sm">{{ $stage->stage_number }}ª — {{ $stage->name }}</div>
                    <div class="text-xs text-ksa-muted mt-0.5">
                        {{ $stage->stage_date->format('d/m/Y') }}
                        @if($stage->location) · {{ $stage->location }} @endif
                    </div>
                    <div class="text-xs text-ksa-muted">{{ $stage->season->name }}</div>
                </div>
                <div class="flex items-center gap-2 ml-3">
                    @php
                        $pillClass = match($stage->status) {
                            'open'   => 'badge-green',
                            'closed' => 'badge-navy',
                            default  => 'badge-gray',
                        };
                    @endphp
                    <span class="{{ $pillClass }}">{{ ucfirst($stage->status) }}</span>
                    <a href="{{ route('stages.show', $stage) }}" class="btn-outline btn-sm">Abrir</a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-ksa-muted">Nenhuma etapa cadastrada.</div>
        @endforelse

        @if($stages->hasPages())
            <div class="p-4 border-t border-ksa-border">
                {{ $stages->links() }}
            </div>
        @endif
    </div>
@endsection
