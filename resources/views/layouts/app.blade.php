<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#17304f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="KSA Racing">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192.png') }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (class_exists(\App\Support\Analytics::class) && view()->exists('partials.analytics'))
        @include('partials.analytics')
    @endif
    @stack('head')
</head>
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $navItems = collect([
        ['label' => 'Dashboard',            'url' => route('dashboard'),                          'route' => 'dashboard',                     'icon' => 'home'],
        ['label' => 'Temporadas',           'url' => route('seasons.index'),                      'route' => 'seasons.*',                     'icon' => 'calendar'],
        ['label' => 'Categorias',           'url' => route('categories.index'),                   'route' => 'categories.*',                  'icon' => 'tag'],
        ['label' => 'Pilotos',              'url' => route('pilots.index'),                       'route' => 'pilots.*',                      'icon' => 'user'],
        ['label' => 'Inscrições',           'url' => route('admin.pilot-registrations.index'),    'route' => 'admin.pilot-registrations.*',    'icon' => 'clipboard'],
        ['label' => 'Insc. esportivas',     'url' => route('registrations.index'),                'route' => 'registrations.*',               'icon' => 'flag'],
        ['label' => 'Etapas',               'url' => route('stages.index'),                       'route' => 'stages.*|stage-management.*|kart-draws.*', 'icon' => 'racing'],
        ['label' => 'Configurações',        'url' => route('settings.edit'),                      'route' => 'settings.*',                    'icon' => 'gear',   'admin' => true],
        ['label' => 'Auditoria',            'url' => route('audit-logs.index'),                   'route' => 'audit-logs.*',                  'icon' => 'shield', 'admin' => true],
        ['label' => 'Meu perfil',           'url' => route('profile.edit'),                       'route' => 'profile.*',                     'icon' => 'person'],
    ])->filter(fn($i) => !($i['admin'] ?? false) || $isAdmin)->values();

    $pageTitle   = trim($__env->yieldContent('title', 'Painel'));
    $pageSubtitle = trim($__env->yieldContent('subtitle', ''));

    $isActive = fn(string $patterns): bool =>
        collect(explode('|', $patterns))->contains(fn($p) => request()->routeIs($p));
    $currentItem = $navItems->first(fn($i) => $isActive($i['route']));
    $sectionLabel = $currentItem['label'] ?? 'Painel';
@endphp
<body class="h-full bg-ksa-bg">

{{-- ── Mobile menu backdrop ─────────────────────────────────────────────── --}}
<div
    id="mobile-menu-backdrop"
    class="fixed inset-0 z-40 bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"
></div>

{{-- ── Mobile menu drawer (slide from right) ────────────────────────────── --}}
<div
    id="mobile-menu"
    class="fixed inset-y-0 right-0 z-50 w-72 bg-ksa-navy translate-x-full transition-transform duration-300 md:hidden flex flex-col safe-top"
>
    <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
        <div>
            <div class="font-condensed text-lg font-bold text-white tracking-wide">{{ config('app.name') }}</div>
            <div class="text-xs text-white/50">{{ $user->name }} · {{ $user->role?->label() ?? 'Usuário' }}</div>
        </div>
        <button
            id="menu-close"
            onclick="document.getElementById('mobile-menu-backdrop').click()"
            class="text-white/60 hover:text-white p-1"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        @foreach ($navItems as $item)
            <a
                href="{{ $item['url'] }}"
                data-menu-link
                class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-semibold transition
                    {{ $isActive($item['route']) ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
            >
                @include('partials.nav-icon', ['icon' => $item['icon']])
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="px-4 py-4 border-t border-white/10 safe-bottom">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-white/70 hover:text-white rounded-xl hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sair
            </button>
        </form>
    </div>
</div>

{{-- ── Desktop sidebar ──────────────────────────────────────────────────────── --}}
<div class="hidden md:fixed md:inset-y-0 md:left-0 md:z-30 md:flex md:w-56 md:flex-col bg-ksa-navy">
    <div class="flex flex-col flex-1 overflow-y-auto pt-5 pb-4">
        <div class="px-4 mb-6">
            <div class="font-condensed text-lg font-bold text-white tracking-wide">{{ config('app.name') }}</div>
            <div class="text-xs text-white/50 mt-0.5">Gestão do campeonato</div>
        </div>

        <nav class="flex-1 px-3 space-y-0.5">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold transition
                        {{ $isActive($item['route']) ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                >
                    @include('partials.nav-icon', ['icon' => $item['icon']])
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="px-3 pb-4 border-t border-white/10 pt-3">
        <div class="px-3 pb-2">
            <div class="text-xs font-semibold text-white">{{ $user->name }}</div>
            <div class="text-xs text-white/50">{{ $user->role?->label() ?? 'Usuário' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white/60 hover:text-white rounded-lg hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sair
            </button>
        </form>
    </div>
</div>

{{-- ── Main content ─────────────────────────────────────────────────────────── --}}
<div class="md:pl-56 flex flex-col min-h-screen pb-[env(safe-area-inset-bottom)]">

    {{-- Top bar (mobile) --}}
    <header class="sticky top-0 z-20 bg-ksa-surface border-b border-ksa-border safe-top md:hidden">
        <div class="flex items-center gap-3 px-4 h-14">
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-ksa-muted leading-none">{{ $sectionLabel }}</div>
                <h1 class="text-base font-bold text-ksa-text truncate leading-tight mt-0.5">{{ $pageTitle }}</h1>
            </div>
            <button
                id="menu-trigger"
                class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl text-ksa-muted hover:bg-gray-100 transition"
                aria-label="Menu"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- Top bar (desktop) --}}
    <header class="hidden md:flex items-center justify-between px-6 py-4 border-b border-ksa-border bg-ksa-surface">
        <div>
            <div class="text-xs font-semibold text-ksa-muted uppercase tracking-wider">{{ $sectionLabel }}</div>
            <h1 class="text-xl font-bold text-ksa-text mt-0.5">{{ $pageTitle }}</h1>
            @if($pageSubtitle)
                <p class="text-sm text-ksa-muted mt-0.5">{{ $pageSubtitle }}</p>
            @endif
        </div>
        @stack('header_actions')
    </header>

    {{-- Page nav tabs --}}
    @hasSection('page_nav')
        <div class="bg-ksa-surface border-b border-ksa-border px-4 md:px-6">
            <div class="page-nav scrollbar-hide py-1">
                @yield('page_nav')
            </div>
        </div>
    @endif

    {{-- Flash messages --}}
    <div class="px-4 md:px-6 pt-4">
        <x-flash />
    </div>

    {{-- Page content --}}
    <main class="flex-1 px-4 md:px-6 py-4 space-y-4">
        @yield('content')
    </main>
</div>

{{-- ── Bottom nav (mobile only) ─────────────────────────────────────────────── --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-ksa-surface border-t border-ksa-border safe-bottom">
    <div class="grid grid-cols-4 h-16">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 text-xs font-semibold transition
            {{ request()->routeIs('dashboard') ? 'text-ksa-navy' : 'text-ksa-muted' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Início</span>
        </a>

        {{-- Etapas --}}
        <a href="{{ route('stages.index') }}" class="flex flex-col items-center justify-center gap-1 text-xs font-semibold transition
            {{ request()->routeIs('stages.*', 'stage-management.*', 'kart-draws.*', 'classifications.*') ? 'text-ksa-navy' : 'text-ksa-muted' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
            <span>Etapas</span>
        </a>

        {{-- Pilotos --}}
        <a href="{{ route('pilots.index') }}" class="flex flex-col items-center justify-center gap-1 text-xs font-semibold transition
            {{ request()->routeIs('pilots.*') ? 'text-ksa-navy' : 'text-ksa-muted' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Pilotos</span>
        </a>

        {{-- Menu --}}
        <button
            id="menu-trigger"
            class="flex flex-col items-center justify-center gap-1 text-xs font-semibold text-ksa-muted transition"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Menu</span>
        </button>
    </div>
</nav>

@stack('scripts')
</body>
</html>
