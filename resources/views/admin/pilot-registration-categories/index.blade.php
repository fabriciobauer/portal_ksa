@extends('layouts.app')

@section('title', 'Categorias do formulário')
@section('subtitle', 'Opções exibidas no formulário público de inscrição de pilotos.')

@section('content')
    <div class="card">
        <div class="flex flex-wrap justify-between items-center gap-3 p-4 border-b border-ksa-border">
            <div>
                <div class="section-title mb-0">Categorias cadastradas</div>
                <div class="text-xs text-ksa-muted mt-0.5">Somente categorias ativas aparecem em <code class="bg-gray-100 px-1 rounded">/inscreva-se</code>.</div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.pilot-registrations.index') }}" class="btn-outline btn-sm">Voltar às inscrições</a>
                <a href="{{ route('admin.registration-categories.create') }}" class="btn-primary btn-sm">+ Nova categoria</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-center">Ordem</th>
                        <th>Status</th>
                        <th>Uso</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="font-semibold text-sm">{{ $category->name }}</div>
                                <div class="text-xs text-ksa-muted">{{ $category->slug }}</div>
                            </td>
                            <td class="text-center text-sm">{{ $category->sort_order }}</td>
                            <td>
                                <span class="{{ $category->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td class="text-sm">{{ $category->registrations_count }} inscrição(ões)</td>
                            <td class="text-right">
                                <div class="inline-flex gap-2 items-center">
                                    <a href="{{ route('admin.registration-categories.edit', $category) }}" class="btn-primary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('admin.registration-categories.status', $category) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $category->is_active ? '0' : '1' }}">
                                        <button type="submit" class="{{ $category->is_active ? 'btn-danger' : 'btn-success' }} btn-sm">
                                            {{ $category->is_active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-ksa-muted py-8">Nenhuma categoria de inscrição cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-ksa-border">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
