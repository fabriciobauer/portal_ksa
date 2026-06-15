@extends('layouts.app')

@section('title', $category->exists ? 'Editar Categoria' : 'Nova Categoria')
@section('subtitle', 'Configure nome, slug, peso alvo, limite operacional por etapa e observações.')

@section('content')
    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="content-card p-4">
        @csrf
        @if($category->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Peso alvo</label>
                <input type="number" step="0.01" name="target_weight" value="{{ old('target_weight', $category->target_weight) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Limite padrão por etapa</label>
                <input type="number" name="default_pilot_limit" value="{{ old('default_pilot_limit', $category->default_pilot_limit ?: 12) }}" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $category->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Categoria ativa</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Observações</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $category->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">Salvar categoria</button>
            @if($category->exists && auth()->user()->isAdmin())
                <button type="submit" form="delete-category" class="btn btn-outline-danger">Remover</button>
            @endif
        </div>
    </form>

    @if($category->exists && auth()->user()->isAdmin())
        <form id="delete-category" method="POST" action="{{ route('categories.destroy', $category) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
