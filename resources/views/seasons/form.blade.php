@extends('layouts.app')

@section('title', $season->exists ? 'Editar Temporada' : 'Nova Temporada')
@section('subtitle', 'Defina período, status e categorias da temporada.')

@section('content')
    <form method="POST" action="{{ $season->exists ? route('seasons.update', $season) : route('seasons.store') }}" class="space-y-4">
        @csrf
        @if($season->exists) @method('PUT') @endif

        <div class="card p-4 space-y-4">
            <div class="section-title">Dados principais</div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="field">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" value="{{ old('name', $season->name) }}" class="form-input" required>
                </div>
                <div class="field">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $season->slug) }}" class="form-input" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="field">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['planned'=>'Planejada','active'=>'Ativa','finished'=>'Encerrada'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('status', $season->status ?: 'planned')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($season->start_date)->format('Y-m-d')) }}" class="form-input">
                </div>
                <div class="field">
                    <label class="form-label">Data final</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($season->end_date)->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>
            <div class="field">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-textarea" rows="3">{{ old('description', $season->description) }}</textarea>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="is_current" value="1" class="form-checkbox" @checked(old('is_current', $season->is_current))>
                <span class="text-sm font-semibold">Marcar como temporada atual</span>
            </label>
        </div>

        <div class="card p-4">
            <div class="section-title">Categorias na temporada</div>
            <div class="space-y-3">
                @foreach ($categories as $category)
                    @php
                        $selected = collect(old('category_ids', $selectedCategories->all()))->contains($category->id);
                        $seasonCategory = $season->seasonCategories->firstWhere('category_id', $category->id);
                    @endphp
                    <div class="border border-ksa-border rounded-xl p-3">
                        <label class="flex items-center gap-2.5 cursor-pointer mb-2">
                            <input type="checkbox" class="form-checkbox" name="category_ids[]" value="{{ $category->id }}" @checked($selected)>
                            <span class="font-semibold text-sm">{{ $category->name }}</span>
                        </label>
                        <div class="field">
                            <label class="form-label text-xs">Limite de pilotos por etapa</label>
                            <input type="number" name="pilot_limit_overrides[{{ $category->id }}]"
                                value="{{ old('pilot_limit_overrides.'.$category->id, $seasonCategory?->pilot_limit) }}"
                                class="form-input w-28 text-center" min="1" max="60" placeholder="Padrão">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <button class="btn-primary" data-submitting-label="Salvando...">Salvar temporada</button>
            <a href="{{ route('seasons.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
