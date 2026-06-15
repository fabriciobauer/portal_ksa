@extends('layouts.app')

@section('title', 'Temporadas')
@section('subtitle', 'Cadastre temporadas futuras sem alterar código e vincule categorias por edição.')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Lista de temporadas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('seasons.create') }}" class="btn btn-primary">Nova temporada</a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Categorias</th>
                        <th>Etapas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seasons as $season)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $season->name }}</div>
                                <div class="small text-muted">{{ $season->slug }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $season->is_current ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $season->is_current ? 'Atual' : ucfirst($season->status) }}
                                </span>
                            </td>
                            <td>{{ $season->seasonCategories->pluck('category.name')->join(', ') ?: '-' }}</td>
                            <td>{{ $season->stages_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('seasons.show', $season) }}" class="btn btn-sm btn-outline-dark">Ver</a>
                                <a href="{{ route('seasons.edit', $season) }}" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhuma temporada cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $seasons->links() }}</div>
    </div>
@endsection
