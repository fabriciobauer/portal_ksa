@extends('layouts.app')

@section('title', 'Meu Perfil')
@section('subtitle', 'Dados de acesso e senha.')

@section('content')
    <div class="grid md:grid-cols-2 gap-4">
        <div class="card p-4 space-y-4">
            <div class="section-title">Dados de acesso</div>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="field">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                </div>
                <div class="field">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                </div>
                <button class="btn-primary btn-sm" data-submitting-label="Salvando...">Salvar perfil</button>
            </form>
        </div>

        <div class="card p-4 space-y-4">
            <div class="section-title">Alterar senha</div>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-3">
                @csrf @method('PUT')
                <div class="field">
                    <label class="form-label">Senha atual</label>
                    <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
                </div>
                <div class="field">
                    <label class="form-label">Nova senha</label>
                    <input type="password" name="password" class="form-input" required autocomplete="new-password">
                </div>
                <div class="field">
                    <label class="form-label">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
                </div>
                <button class="btn-outline btn-sm" data-submitting-label="Atualizando...">Atualizar senha</button>
            </form>
        </div>
    </div>
@endsection
