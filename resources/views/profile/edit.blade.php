@extends('layouts.app')

@section('title', 'Meu Perfil')
@section('subtitle', 'Atualize seus dados de acesso e senha.')

@section('content')
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="content-card p-4">
                <div class="section-title">Dados de acesso</div>
                <form method="POST" action="{{ route('profile.update') }}" class="vstack gap-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>

                    <button class="btn btn-primary align-self-start">Salvar perfil</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card p-4">
                <div class="section-title">Alterar senha</div>
                <form method="POST" action="{{ route('profile.password') }}" class="vstack gap-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="form-label">Senha atual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button class="btn btn-outline-dark align-self-start">Atualizar senha</button>
                </form>
            </div>
        </div>
    </div>
@endsection
