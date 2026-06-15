@extends('layouts.app')

@section('title', $pilot->exists ? 'Editar Piloto' : 'Novo Piloto')
@section('subtitle', 'Cadastro individual do piloto com foto opcional e dados complementares.')

@section('content')
    <form method="POST" enctype="multipart/form-data" action="{{ $pilot->exists ? route('pilots.update', $pilot) : route('pilots.store') }}" class="content-card p-4">
        @csrf
        @if($pilot->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ old('name', $pilot->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Apelido</label>
                <input type="text" name="nickname" value="{{ old('nickname', $pilot->nickname) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Foto</label>
                <input type="file" name="photo" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de nascimento</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', optional($pilot->birth_date)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="phone" value="{{ old('phone', $pilot->phone) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="city" value="{{ old('city', $pilot->city) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Peso base</label>
                <input type="number" step="0.01" name="base_weight" value="{{ old('base_weight', $pilot->base_weight) }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="pilot_active" @checked(old('is_active', $pilot->is_active ?? true))>
                    <label class="form-check-label" for="pilot_active">Piloto ativo</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Observações</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $pilot->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">Salvar piloto</button>
            @if($pilot->exists)
                <a href="{{ route('pilots.show', $pilot) }}" class="btn btn-outline-dark">Ver histórico</a>
            @endif
        </div>
    </form>
@endsection
