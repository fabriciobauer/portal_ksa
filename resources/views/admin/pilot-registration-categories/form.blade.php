@extends('layouts.app')

@section('title', $category->exists ? 'Editar categoria do formulário' : 'Nova categoria do formulário')
@section('subtitle', 'Controle das opções exibidas na inscrição pública de pilotos.')

@section('content')
    <form method="POST" action="{{ $category->exists ? route('admin.registration-categories.update', $category) : route('admin.registration-categories.store') }}" class="content-card p-4">
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <label for="name" class="form-label">Nome</label>
                <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="sort_order" class="form-label">Ordem</label>
                <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control">
            </div>
            <div class="col-12">
                <label for="notes" class="form-label">Observações</label>
                <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $category->notes) }}</textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $category->exists ? $category->is_active : true))>
                    <label for="is_active" class="form-check-label">Categoria ativa</label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button class="btn btn-primary">{{ $category->exists ? 'Salvar categoria' : 'Criar categoria' }}</button>
            <a href="{{ route('admin.registration-categories.index') }}" class="btn btn-outline-dark">Cancelar</a>
        </div>
    </form>
@endsection
