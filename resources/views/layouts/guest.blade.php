<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (class_exists(\App\Support\Analytics::class) && view()->exists('partials.analytics'))
        @include('partials.analytics')
    @endif
    @stack('head')
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="content-card p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <a href="{{ route('public.home') }}" class="guest-logo-link mb-3 d-inline-flex">
                            <img src="{{ asset('images/ksa-logo.png') }}" alt="Logo KSA Racing" class="guest-logo">
                        </a>
                        <h1 class="h3 mb-1">{{ config('app.name') }}</h1>
                        <p class="text-muted mb-0">Acesso administrativo do campeonato</p>
                    </div>

                    <x-flash />

                    @yield('content')

                    <div class="text-center mt-4">
                        <a href="{{ route('public.home') }}" class="small text-decoration-none">Voltar ao site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
