@extends('layouts.app')

@section('title', $category->exists ? 'Editar categoria do formulário' : 'Nova categoria do formulário')
@section('subtitle', 'Controle das opções exibidas na inscrição pública de pilotos.')

@section('content')
    <form method="POST"
        action="{{ $category->exists ? route('admin.registration-categories.update', $category) : route('admin.registration-categories.store') }}"
        class="card p-4 space-y-4">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-3 gap-4">
            <div class="field md:col-span-2">
                <label for="name" class="form-label">Nome</label>
                <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" class="form-input" required>
            </div>
            <div class="field">
                <label for="sort_order" class="form-label">Ordem</label>
                <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-input text-center">
            </div>
        </div>

        <div class="field">
            <label for="notes" class="form-label">Observações</label>
            <textarea id="notes" name="notes" class="form-textarea" rows="3">{{ old('notes', $category->notes) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input id="is_active" type="checkbox" name="is_active" value="1" class="form-checkbox"
                @checked(old('is_active', $category->exists ? $category->is_active : true))>
            <span class="text-sm font-semibold">Categoria ativa</span>
        </label>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">{{ $category->exists ? 'Salvar categoria' : 'Criar categoria' }}</button>
            <a href="{{ route('admin.registration-categories.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
