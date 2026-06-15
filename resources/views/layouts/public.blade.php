<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (class_exists(\App\Support\Analytics::class) && view()->exists('partials.analytics'))
        @include('partials.analytics')
    @endif
    @stack('head')
</head>
<body class="@yield('body_class', 'public-home')">
    @yield('content')
    @stack('scripts')
</body>
</html>
