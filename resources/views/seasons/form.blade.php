@extends('layouts.app')

@section('title', $season->exists ? 'Editar Temporada' : 'Nova Temporada')
@section('subtitle', 'Defina período, status e categorias participantes da temporada.')

@section('content')
    <form method="POST" action="{{ $season->exists ? route('seasons.update', $season) : route('seasons.store') }}" class="row g-4">
        @csrf
        @if($season->exists)
            @method('PUT')
        @endif

        <div class="col-lg-8">
            <div class="content-card p-4">
                <div class="section-title">Dados principais</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" value="{{ old('name', $season->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $season->slug) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['planned' => 'Planejada', 'active' => 'Ativa', 'finished' => 'Encerrada'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $season->status ?: 'planned') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data inicial</label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($season->start_date)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data final</label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($season->end_date)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $season->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_current" value="1" class="form-check-input" id="is_current" @checked(old('is_current', $season->is_current))>
                            <label class="form-check-label" for="is_current">Marcar como temporada atual</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card p-4">
                <div class="section-title">Categorias na temporada</div>
                <div class="vstack gap-3">
                    @foreach ($categories as $category)
                        @php
                            $selected = collect(old('category_ids', $selectedCategories->all()))->contains($category->id);
                            $seasonCategory = $season->seasonCategories->firstWhere('category_id', $category->id);
                        @endphp
                        <div class="border rounded-4 p-3">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="category_{{ $category->id }}" name="category_ids[]" value="{{ $category->id }}" @checked($selected)>
                                <label class="form-check-label fw-semibold" for="category_{{ $category->id }}">{{ $category->name }}</label>
                            </div>
                            <label class="form-label small">Limite operacional por etapa</label>
                            <input type="number" name="pilot_limit_overrides[{{ $category->id }}]" value="{{ old('pilot_limit_overrides.'.$category->id, $seasonCategory?->pilot_limit) }}" class="form-control form-control-sm" min="1" max="60">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary">Salvar temporada</button>
            <a href="{{ route('seasons.index') }}" class="btn btn-outline-dark">Voltar</a>
        </div>
    </form>
@endsection
