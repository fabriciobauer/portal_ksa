@extends('layouts.app')

@section('title', $category->exists ? 'Editar Categoria' : 'Nova Categoria')
@section('subtitle', 'Configure nome, slug, peso alvo e limite operacional.')

@section('content')
    <form method="POST"
        action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}"
        class="card p-4 space-y-4">
        @csrf
        @if($category->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-input" required>
            </div>
            <div class="field">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-input" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Peso alvo (kg)</label>
                <input type="number" step="0.01" name="target_weight" value="{{ old('target_weight', $category->target_weight) }}" class="form-input">
            </div>
            <div class="field">
                <label class="form-label">Limite padrão/etapa</label>
                <input type="number" name="default_pilot_limit" value="{{ old('default_pilot_limit', $category->default_pilot_limit ?: 12) }}" class="form-input text-center" required>
            </div>
        </div>

        <div class="field">
            <label class="form-label">Descrição</label>
            <textarea name="description" class="form-textarea" rows="2">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="field">
            <label class="form-label">Observações</label>
            <textarea name="notes" class="form-textarea" rows="2">{{ old('notes', $category->notes) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="form-checkbox" @checked(old('is_active', $category->is_active ?? true))>
            <span class="text-sm font-semibold">Categoria ativa</span>
        </label>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">Salvar categoria</button>
            @if($category->exists && auth()->user()->isAdmin())
                <button type="submit" form="delete-category" class="btn-danger btn-sm">Remover</button>
            @endif
            <a href="{{ route('categories.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>

    @if($category->exists && auth()->user()->isAdmin())
        <form id="delete-category" method="POST" action="{{ route('categories.destroy', $category) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif
@endsection
