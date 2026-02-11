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
    <style>
        :root{
        --primary:#2f3542;
        --primary-light:#57606f;
        
        --bg:#f5f6fa;
        --bg-soft:#eef1f5;
        
        --card-bg:rgba(255,255,255,0.75);
        --card-border:rgba(0,0,0,0.06);
        
        --text:#1e272e;
        --muted:#6c757d;
        
        --shadow:0 8px 24px rgba(0,0,0,0.06);
        --radius:16px;
        }
        
        *{
        box-sizing:border-box;
        }
        
        body{
        margin:0;
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        }
        
        /* Layout */
        .app{
        display:flex;
        min-height:100vh;
        }
        
        /* Sidebar */
        .sidebar{
        width:260px;
        background: linear-gradient(180deg,#ffffff,#f8f9fb);
        border-right:1px solid var(--card-border);
        padding:20px 16px;
        display:flex;
        flex-direction:column;
        transition: all .25s ease;
        box-shadow: 4px 0 20px rgba(0,0,0,.03);
        }
        
        .brand{
        display:flex;
        align-items:center;
        gap:12px;
        margin-bottom:25px;
        }
        
        .logo{
        width:42px;
        height:42px;
        border-radius:12px;
        background:#2f3542;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        }
        
        .brand-title{
        font-weight:700;
        font-size:16px;
        }
        
        .brand-sub{
        font-size:12px;
        color:var(--muted);
        }
        
        /* Navigation */
        .nav{
        display:flex;
        flex-direction:column;
        gap:8px;
        }
        
        .nav-link{
        padding:10px 14px;
        border-radius:12px;
        color:var(--primary);
        font-weight:500;
        text-decoration:none;
        transition:.2s ease;
        }
        
        .nav-link:hover{
        background:var(--bg-soft);
        text-decoration:none;
        }
        
        .nav-link.active{
        background:#e9ecef;
        font-weight:600;
        }
        
        /* Sidebar footer */
        .sidebar-footer{
        margin-top:auto;
        border-top:1px solid var(--card-border);
        padding-top:15px;
        }
        
        .userbox{
        display:flex;
        align-items:center;
        gap:10px;
        }
        
        .avatar{
        width:38px;
        height:38px;
        border-radius:10px;
        background:#dfe4ea;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:600;
        }
        
        .user-name{
        font-weight:600;
        }
        
        .user-role{
        font-size:12px;
        color:var(--muted);
        }
        
        /* Main */
        .main{
        flex:1;
        display:flex;
        flex-direction:column;
        }
        
        /* Topbar */
        .topbar{
        background:#ffffff;
        border-bottom:1px solid var(--card-border);
        padding:14px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        box-shadow: 0 4px 12px rgba(0,0,0,.02);
        }
        
        .icon-btn{
        border:none;
        background:#f1f2f6;
        border-radius:10px;
        padding:8px 12px;
        cursor:pointer;
        transition:.2s;
        }
        
        .icon-btn:hover{
        background:#e9ecef;
        }
        
        .topbar-right{
        display:flex;
        gap:10px;
        }
        
        .pill{
        background:#f1f2f6;
        padding:6px 12px;
        border-radius:999px;
        font-size:12px;
        color:var(--primary-light);
        }
        
        /* Content */
        .content{
        padding:20px;
        }
        
        .page{
        animation: fadeUp .3s ease both;
        }
        
        @keyframes fadeUp{
        from { opacity:0; transform: translateY(6px); }
        to { opacity:1; transform: translateY(0); }
        }
        
        /* Glass Cards */
        .card{
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border:1px solid var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding:20px;
        }
        
        /* Grid */
        .grid{
        display:grid;
        grid-template-columns: repeat(12,1fr);
        gap:20px;
        }
        
        .col-12{ grid-column: span 12; }
        .col-6{ grid-column: span 6; }
        .col-4{ grid-column: span 4; }
        .col-3{ grid-column: span 3; }
        
        /* Buttons */
        .btn{
        border-radius:10px;
        }
        
        .btn-primary{
        background:#2f3542;
        border:none;
        }
        
        .btn-primary:hover{
        background:#1e272e;
        }
        
        /* Forms */
        .form-control{
        border-radius:10px;
        border:1px solid #dfe4ea;
        }
        
        .form-control:focus{
        border-color:#ced6e0;
        box-shadow:none;
        }
        
        /* Table */
        .table{
        background:#fff;
        border-radius:12px;
        overflow:hidden;
        }
        
        .table thead{
        background:#f1f2f6;
        }
        
        .table th{
        font-weight:600;
        }
        
        /* Sidebar collapse */
        .app[data-sidebar="collapsed"] .sidebar{
        width:80px;
        }
        
        .app[data-sidebar="collapsed"] .brand-text,
        .app[data-sidebar="collapsed"] .user-meta{
        display:none;
        }
        
        .app[data-sidebar="collapsed"] .nav-link{
        text-align:center;
        padding:10px 8px;
        font-size:12px;
        }
        
        /* Mobile */
        @media(max-width:992px){
        .sidebar{
        position:fixed;
        left:0;
        top:0;
        bottom:0;
        z-index:999;
        transform:translateX(-100%);
        }
        
        .app[data-sidebar="open"] .sidebar{
        transform:translateX(0);
        }
        
        .main{
        margin-left:0 !important;
        }
        
        .col-6,.col-4,.col-3{
        grid-column: span 12;
        }
        }
    </style>
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