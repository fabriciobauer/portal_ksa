@extends('layouts.app')

@section('title', 'Categorias')
@section('subtitle', 'Gestão livre das categorias do campeonato.')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Categorias cadastradas</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('categories.create') }}" class="btn btn-primary">Nova categoria</a>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Peso alvo</th>
                        <th>Limite padrão por etapa</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $category->name }}</div>
                                <div class="small text-muted">{{ $category->slug }}</div>
                            </td>
                            <td>{{ $category->target_weight ? number_format($category->target_weight, 2, ',', '.') . ' kg' : '-' }}</td>
                            <td>{{ $category->default_pilot_limit }}</td>
                            <td><span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? 'Ativa' : 'Inativa' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhuma categoria cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $categories->links() }}</div>
    </div>
@endsection
