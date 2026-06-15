@extends('layouts.app')

@section('title', $pilot->displayName())
@section('subtitle', 'Ficha do piloto')

@section('content')
    <div class="card p-4">
        <div class="flex items-start gap-4 mb-4">
            @if($pilot->photo_path)
                <img src="{{ asset('storage/'.$pilot->photo_path) }}" alt="" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0">
            @else
                <div class="w-16 h-16 rounded-2xl bg-ksa-navy/10 flex items-center justify-center flex-shrink-0">
                    <span class="text-ksa-navy font-bold text-2xl">{{ substr($pilot->name, 0, 1) }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <h2 class="font-bold text-lg">{{ $pilot->name }}</h2>
                @if($pilot->nickname)<p class="text-ksa-muted text-sm">"{{ $pilot->nickname }}"</p>@endif
                <span class="{{ $pilot->is_active ? 'badge-green' : 'badge-gray' }} mt-1">{{ $pilot->is_active ? 'Ativo' : 'Inativo' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-ksa-border">
            @if($pilot->birth_date)
                <div><div class="text-xs text-ksa-muted">Nascimento</div><div class="font-semibold text-sm">{{ $pilot->birth_date->format('d/m/Y') }}</div></div>
            @endif
            @if($pilot->cpf)
                <div><div class="text-xs text-ksa-muted">CPF</div><div class="font-semibold text-sm">{{ $pilot->cpf }}</div></div>
            @endif
            @if($pilot->phone)
                <div><div class="text-xs text-ksa-muted">Telefone</div><div class="font-semibold text-sm">{{ $pilot->phone }}</div></div>
            @endif
            @if($pilot->email)
                <div><div class="text-xs text-ksa-muted">E-mail</div><div class="font-semibold text-sm">{{ $pilot->email }}</div></div>
            @endif
            @if($pilot->city)
                <div><div class="text-xs text-ksa-muted">Cidade</div><div class="font-semibold text-sm">{{ $pilot->city }}</div></div>
            @endif
            @if($pilot->base_weight)
                <div><div class="text-xs text-ksa-muted">Peso base</div><div class="font-semibold text-sm">{{ $pilot->base_weight }} kg</div></div>
            @endif
        </div>

        @if($pilot->notes)
            <div class="mt-4 pt-4 border-t border-ksa-border">
                <div class="text-xs text-ksa-muted mb-1">Observações</div>
                <p class="text-sm">{{ $pilot->notes }}</p>
            </div>
        @endif

        <div class="mt-4 pt-4 border-t border-ksa-border flex gap-2">
            <a href="{{ route('pilots.edit', $pilot) }}" class="btn-primary btn-sm">Editar</a>
            <a href="{{ route('pilots.index') }}" class="btn-ghost btn-sm">← Voltar</a>
        </div>
    </div>

    @if($pilot->registrations->isNotEmpty())
        <div class="card">
            <div class="p-4 border-b border-ksa-border">
                <div class="section-title mb-0">Inscrições esportivas</div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Temporada</th><th>Categoria</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($pilot->registrations as $reg)
                            <tr>
                                <td>{{ $reg->seasonCategory->season->name }}</td>
                                <td>{{ $reg->seasonCategory->category->name }}</td>
                                <td><span class="{{ $reg->status === 'confirmed' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($reg->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
