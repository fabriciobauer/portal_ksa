<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#17304f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="KSA Racing">
    <link rel="manifest" href="/manifest.json">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (class_exists(\App\Support\Analytics::class) && view()->exists('partials.analytics'))
        @include('partials.analytics')
    @endif
    @stack('head')
</head>
<body class="h-full bg-ksa-navy flex items-center justify-center p-4 safe-top safe-bottom">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <a href="{{ route('public.home') }}" class="inline-block">
                <img src="{{ asset('images/ksa-logo.png') }}" alt="KSA Racing" class="h-16 mx-auto">
            </a>
            <h1 class="text-xl font-bold text-white mt-4">{{ config('app.name') }}</h1>
            <p class="text-sm text-white/60 mt-1">Acesso administrativo do campeonato</p>
        </div>

        <div class="card p-6">
            <x-flash />
            @yield('content')
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('public.home') }}" class="text-sm text-white/50 hover:text-white/80 transition">
                ← Voltar ao site
            </a>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
