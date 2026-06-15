@extends('layouts.app')

@section('title', 'Categorias do formulário')
@section('subtitle', 'Opções exibidas no formulário público de inscrição de pilotos.')

@section('content')
    <div class="content-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="section-title mb-1">Categorias cadastradas</div>
                <div class="text-muted">Somente categorias ativas aparecem em `/inscreva-se`.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.pilot-registrations.index') }}" class="btn btn-outline-dark">Voltar às inscrições</a>
                <a href="{{ route('admin.registration-categories.create') }}" class="btn btn-primary">Nova categoria</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Ordem</th>
                        <th>Status</th>
                        <th>Uso</th>
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
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td>{{ $category->registrations_count }} inscrição(ões)</td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ route('admin.registration-categories.edit', $category) }}" class="btn btn-sm btn-primary">Editar</a>
                                    <form method="POST" action="{{ route('admin.registration-categories.status', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $category->is_active ? '0' : '1' }}">
                                        <button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $category->is_active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhuma categoria de inscrição cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
@endsection
