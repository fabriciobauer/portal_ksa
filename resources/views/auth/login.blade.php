@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <div class="field">
            <label for="email" class="form-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="form-input" required autofocus autocomplete="email">
        </div>

        <div class="field">
            <label for="password" class="form-label">Senha</label>
            <input id="password" type="password" name="password"
                class="form-input" required autocomplete="current-password">
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="form-checkbox">
            <span class="text-sm text-ksa-text">Lembrar acesso</span>
        </label>

        <button type="submit" class="btn-primary btn-block mt-2" data-submitting-label="Entrando...">
            Entrar
        </button>
    </form>
@endsection
