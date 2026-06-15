@extends('layouts.app')

@section('title', $pilot->exists ? 'Editar Piloto' : 'Novo Piloto')
@section('subtitle', 'Cadastro individual do piloto.')

@section('content')
    <form method="POST" enctype="multipart/form-data"
        action="{{ $pilot->exists ? route('pilots.update', $pilot) : route('pilots.store') }}"
        class="card p-4 space-y-4">
        @csrf
        @if($pilot->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Nome</label>
                <input type="text" name="name" value="{{ old('name', $pilot->name) }}" class="form-input" required>
            </div>
            <div class="field">
                <label class="form-label">Apelido</label>
                <input type="text" name="nickname" value="{{ old('nickname', $pilot->nickname) }}" class="form-input">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">CPF</label>
                <input type="text" name="cpf" value="{{ old('cpf', $pilot->cpf) }}" class="form-input" placeholder="000.000.000-00">
            </div>
            <div class="field">
                <label class="form-label">Data de nascimento</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', optional($pilot->birth_date)->format('Y-m-d')) }}" class="form-input">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Telefone</label>
                <input type="text" name="phone" value="{{ old('phone', $pilot->phone) }}" class="form-input">
            </div>
            <div class="field">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $pilot->email) }}" class="form-input">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="field">
                <label class="form-label">Cidade</label>
                <input type="text" name="city" value="{{ old('city', $pilot->city) }}" class="form-input">
            </div>
            <div class="field">
                <label class="form-label">Peso base (kg)</label>
                <input type="number" step="0.01" name="base_weight" value="{{ old('base_weight', $pilot->base_weight) }}" class="form-input">
            </div>
        </div>

        <div class="field">
            <label class="form-label">Foto</label>
            <input type="file" name="photo" accept="image/*" class="form-input">
            @if($pilot->photo_path)
                <p class="form-hint">Foto atual — envie nova para substituir.</p>
            @endif
        </div>

        <div class="field">
            <label class="form-label">Endereço</label>
            <input type="text" name="address" value="{{ old('address', $pilot->address) }}" class="form-input">
        </div>

        <div class="field">
            <label class="form-label">Observações</label>
            <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $pilot->notes) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="form-checkbox"
                @checked(old('is_active', $pilot->is_active ?? true))>
            <span class="text-sm font-semibold">Piloto ativo</span>
        </label>

        <div class="flex gap-2 pt-2 border-t border-ksa-border">
            <button class="btn-primary" data-submitting-label="Salvando...">Salvar piloto</button>
            <a href="{{ route('pilots.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
@endsection
