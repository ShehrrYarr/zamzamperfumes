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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/panel.css') }}">
    
</head>

<body>
    <div class="app" data-sidebar="open">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            <div class="brand">
                <div class="logo">ZZP</div>
                <div class="brand-text">
                    <div class="brand-title">Zam Zam Perfume</div>
                    <div class="brand-sub">@yield('panel_name', 'Panel')</div>
                </div>
            </div>

            <nav class="nav">
                @php $role = auth()->user()->role ?? null; @endphp

                {{-- Dashboard link based on role --}}
                @if($role === 'admin')
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">Dashboard</a>

                <a class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"
                    href="{{ route('admin.branches.index') }}">Branches</a>

                <a class="nav-link {{ request()->routeIs('admin.mainshop.*') ? 'active' : '' }}"
                    href="{{ route('admin.mainshop.show') }}">Main Shop</a>

                <a class="nav-link {{ request()->routeIs('admin.mainshop.staff.*') ? 'active' : '' }}"
                    href="{{ route('admin.mainshop.staff.index') }}">Main Shop Staff</a>

                <a class="nav-link {{ request()->routeIs('admin.perfumes.*') ? 'active' : '' }}"
                    href="{{ route('admin.perfumes.index') }}">Perfumes</a>

                <a class="nav-link {{ request()->routeIs('admin.batches.*') ? 'active' : '' }}"
                    href="{{ route('admin.batches.index') }}">Batches</a>



                @elseif($role === 'main_shop')
                <a class="nav-link {{ request()->routeIs('main.dashboard') ? 'active' : '' }}"
                    href="{{ route('main.dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('main.branches.*') ? 'active' : '' }}"
                    href="{{ route('main.branches.index') }}">Branches</a>

                <a class="nav-link {{ request()->routeIs('main.perfumes.*') ? 'active' : '' }}"
                    href="{{ route('main.perfumes.index') }}">Perfumes</a>

                <a class="nav-link {{ request()->routeIs('main.batches.*') ? 'active' : '' }}"
                    href="{{ route('main.batches.index') }}">Batches</a>

                <a class="nav-link {{ request()->routeIs('main.transfers.*') ? 'active' : '' }}"
                    href="{{ route('main.transfers.index') }}">Transfers</a>

                <a class="nav-link {{ request()->routeIs('main.inventory.*') ? 'active' : '' }}"
                    href="{{ route('main.inventory.index') }}">Inventory</a>

                <a class="nav-link {{ request()->routeIs('main.banks.*') ? 'active' : '' }}"
                    href="{{ route('main.banks.index') }}">Banks</a>


                @elseif($role === 'branch_shop')
                <a class="nav-link {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}"
                    href="{{ route('branch.dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('branch.staff.*') ? 'active' : '' }}"
                    href="{{ route('branch.staff.index') }}">Staff</a>

                <a class="nav-link {{ request()->routeIs('branch.transfers.*') ? 'active' : '' }}"
                    href="{{ route('branch.transfers.claim_form') }}">Claim Transfer</a>

                <a class="nav-link {{ request()->routeIs('branch.inventory.*') ? 'active' : '' }}"
                    href="{{ route('branch.inventory.index') }}">Inventory</a>

                <a class="nav-link {{ request()->routeIs('branch.transfers.index') ? 'active' : '' }}"
                    href="{{ route('branch.transfers.index') }}">Transfer History</a>

                <a class="nav-link {{ request()->routeIs('branch.banks.*') ? 'active' : '' }}"
                    href="{{ route('branch.banks.index') }}">Banks</a>

                @else
                <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                    href="{{ route('staff.dashboard') }}">Dashboard</a>

                @endif
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
                    <div class="pill">Role: {{ auth()->user()->role ?? '-' }}</div>
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