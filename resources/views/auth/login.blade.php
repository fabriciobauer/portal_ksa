@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="vstack gap-3">
        @csrf

        <div>
            <label for="email" class="form-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>

        <div>
            <label for="password" class="form-label">Senha</label>
            <input id="password" type="password" name="password" class="form-control" required>
        </div>

        <div class="form-check">
            <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input">
            <label for="remember" class="form-check-label">Lembrar acesso</label>
        </div>

        <button type="submit" class="btn btn-dark w-100">Entrar</button>
    </form>
@endsection
