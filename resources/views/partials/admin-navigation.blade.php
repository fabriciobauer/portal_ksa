<nav class="nav flex-column admin-nav">
    @foreach ($navigationItems as $item)
        <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" data-admin-nav-link>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
