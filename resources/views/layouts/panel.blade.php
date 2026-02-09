<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/panel.css') }}">
</head>

<body>
    <div class="app" data-sidebar="open">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo">JF</div>
                <div class="brand-text">
                    <div class="brand-title">JM Fragrances</div>
                    <div class="brand-sub">@yield('panel_name', 'Panel')</div>
                </div>
            </div>

            <nav class="nav">
                <a class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}" href="#">
                    <span>Dashboard</span>
                </a>

                {{-- Later we’ll add: Branches, Staff, Inventory, POS, Attendance --}}
            </nav>

            <div class="sidebar-footer">
                <div class="userbox">
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                    <div class="user-meta">
                        <div class="user-name">{{ auth()->user()->name ?? '' }}</div>
                        <div class="user-role">{{ auth()->user()->role ?? '' }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost w-100" type="submit">Logout</button>
                </form>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <button class="icon-btn" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
                    ☰
                </button>

                <div class="topbar-right">
                    <div class="pill">Shop: {{ auth()->user()->shop_id ?? '—' }}</div>
                    <div class="pill">Role: {{ auth()->user()->role }}</div>
                </div>
            </header>

            <section class="content">
                <div class="page" id="page">
                    @yield('content')
                </div>
            </section>
        </main>
    </div>

    <script src="{{ asset('assets/panel.js') }}"></script>
</body>

</html>