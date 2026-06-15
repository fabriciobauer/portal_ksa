@extends('layouts.app')

@section('title', 'Pilotos')
@section('subtitle', 'Cadastro completo de pilotos com histórico preservado.')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Pilotos cadastrados</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('pilots.create') }}" class="btn btn-primary">Novo piloto</a>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Piloto</th>
                        <th>Cidade</th>
                        <th>Peso base</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pilots as $pilot)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $pilot->displayName() }}</div>
                                <div class="small text-muted">{{ $pilot->phone ?: 'Sem telefone' }}</div>
                            </td>
                            <td>{{ $pilot->city ?: '-' }}</td>
                            <td>{{ $pilot->base_weight ? number_format($pilot->base_weight, 2, ',', '.') . ' kg' : '-' }}</td>
                            <td><span class="badge {{ $pilot->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $pilot->is_active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('pilots.show', $pilot) }}" class="btn btn-sm btn-outline-dark">Histórico</a>
                                <a href="{{ route('pilots.edit', $pilot) }}" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhum piloto cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $pilots->links() }}</div>
    </div>
@endsection
