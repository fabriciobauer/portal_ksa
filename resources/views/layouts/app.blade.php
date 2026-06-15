<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (class_exists(\App\Support\Analytics::class) && view()->exists('partials.analytics'))
        @include('partials.analytics')
    @endif
    @stack('head')
</head>
@php
    $navigationItems = collect([
        [
            'label' => 'Dashboard',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Temporadas',
            'url' => route('seasons.index'),
            'active' => request()->routeIs('seasons.*'),
        ],
        [
            'label' => 'Categorias',
            'url' => route('categories.index'),
            'active' => request()->routeIs('categories.*'),
        ],
        [
            'label' => 'Pilotos',
            'url' => route('pilots.index'),
            'active' => request()->routeIs('pilots.*'),
        ],
        [
            'label' => 'Inscricoes de pilotos',
            'url' => route('admin.pilot-registrations.index'),
            'active' => request()->routeIs('admin.pilot-registrations.*') || request()->routeIs('admin.registration-categories.*'),
        ],
        [
            'label' => 'Inscricoes esportivas',
            'url' => route('registrations.index'),
            'active' => request()->routeIs('registrations.*'),
        ],
        [
            'label' => 'Etapas',
            'url' => route('stages.index'),
            'active' => request()->routeIs('stages.*') || request()->routeIs('stage-management.*') || request()->routeIs('kart-draws.*'),
        ],
        [
            'label' => 'Configuracoes',
            'url' => route('settings.edit'),
            'active' => request()->routeIs('settings.*'),
            'visible' => auth()->user()->isAdmin(),
        ],
        [
            'label' => 'Auditoria',
            'url' => route('audit-logs.index'),
            'active' => request()->routeIs('audit-logs.*'),
            'visible' => auth()->user()->isAdmin(),
        ],
        [
            'label' => 'Meu perfil',
            'url' => route('profile.edit'),
            'active' => request()->routeIs('profile.*'),
        ],
    ])->filter(fn (array $item) => $item['visible'] ?? true)->values();

    $currentSection = $navigationItems->firstWhere('active', true);
    $pageTitle = trim($__env->yieldContent('title', 'Painel'));
    $sectionLabel = $currentSection['label'] ?? 'Painel';
@endphp
<body>
    <div class="offcanvas offcanvas-start sidebar sidebar-offcanvas" tabindex="-1" id="adminNavigation" aria-labelledby="adminNavigationLabel">
        <div class="offcanvas-header border-bottom border-light border-opacity-10">
            <div>
                <div class="fw-bold fs-5" id="adminNavigationLabel">{{ config('app.name') }}</div>
                <div class="small text-white-50">Administracao do campeonato</div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body px-3 py-4">
            @include('partials.admin-navigation', ['navigationItems' => $navigationItems])
        </div>
    </div>

    <div class="container-fluid app-shell">
        <div class="row">
            <aside class="col-md-3 col-lg-2 px-3 py-4 sidebar d-none d-md-block">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="fw-bold fs-5">{{ config('app.name') }}</div>
                        <div class="small text-white-50">Administracao do campeonato</div>
                    </div>
                </div>

                @include('partials.admin-navigation', ['navigationItems' => $navigationItems])
            </aside>

            <main class="col-12 col-md-9 col-lg-10 px-3 px-lg-4 py-4">
                <div class="page-header mb-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="d-flex align-items-start gap-3">
                            <button
                                type="button"
                                class="btn btn-outline-dark btn-sm d-md-none page-menu-trigger"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#adminNavigation"
                                aria-controls="adminNavigation"
                            >
                                Menu
                            </button>

                            <div>
                                <div class="page-kicker">{{ $sectionLabel }}</div>
                                <h1 class="h3 mb-1">{{ $pageTitle }}</h1>
                                @hasSection('subtitle')
                                    <p class="text-muted mb-0">@yield('subtitle')</p>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="text-end">
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->role?->label() ?? 'Usuario' }}</div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark btn-sm" data-submitting-label="Saindo...">Sair</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="flash-stack mb-4">
                    <x-flash />
                </div>

                @hasSection('page_nav')
                    <div class="content-card page-nav-shell p-2 mb-4">
                        <div class="page-nav-links">
                            @yield('page_nav')
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
