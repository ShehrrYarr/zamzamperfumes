@extends('layouts.panel')

@section('title', 'Main Shop')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Main Shop</h1>
                <p class="muted">Only one main shop is allowed. It supplies branches and controls internal codes.</p>
            </div>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        @if(!$mainShop)
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.10); max-width:560px;">
            <h2 style="margin:0 0 10px; font-size:16px;">Create Main Shop</h2>

            <form method="POST" action="{{ route('admin.mainshop.store') }}" style="display:grid; gap:12px;">
                @csrf

                <div>
                    <label style="display:block; margin-bottom:6px;  ">Name</label>
                    <input name="name" required
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px;  ">Code</label>
                    <input name="code" required placeholder="MAIN-001"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px;  ">Address</label>
                    <input name="address"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px;  ">Main Shop Login
                        Email</label>
                    <input type="email" name="email" required
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
                </div>

                <button class="btn" type="submit">Create Main Shop</button>
            </form>
        </div>
        @else
        <div style="margin-top:14px; display:grid; gap:12px; max-width:720px;">
            <div class="card" style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.10);">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <div style="font-weight:700; font-size:16px;">{{ $mainShop->name }}</div>
                        <div class="muted">Code: {{ $mainShop->code }} • Status: {{ $mainShop->is_active ? 'Active' :
                            'Disabled' }}</div>
                        <div class="muted">Address: {{ $mainShop->address ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card" style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.10);">
                <div style="font-weight:700; margin-bottom:6px;">Main Shop Login</div>
                <div class="muted">Email: <span >{{ $mainLogin?->email ?? 'Not found'
                        }}</span></div>
                <div class="muted">Password: <span >{{ $mainLogin?->password_text ?? 'Not found'
                        }}</span></div>

                <form method="POST" action="{{ route('admin.mainshop.reset_login_password') }}"
                    style="margin-top:10px;">
                    @csrf
                    <button class="btn btn-ghost" type="submit"
                        onclick="return confirm('Reset main shop login password?');">
                        Reset Login Password
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection