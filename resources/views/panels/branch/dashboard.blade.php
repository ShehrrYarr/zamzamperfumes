@extends('layouts.panel')

@section('title', 'Branch Dashboard')
@section('panel_name', 'Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Branch Dashboard</h1>
        <p class="muted">
            Welcome, {{ auth()->user()->name }} —
            Branch: {{ $branch?->name ?? '—' }} ({{ $branch?->code ?? '—' }})
        </p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">{{ $staffCount }}</h1>
        <p class="muted">Total Staff</p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">{{ $activeStaffCount }}</h1>
        <p class="muted">Active Staff</p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">Quick</h1>
        <p class="muted">Actions</p>
        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn" href="{{ route('branch.staff.index') }}">Manage Staff</a>
            <a class="btn btn-ghost" href="{{ route('branch.staff.create') }}">Add Staff</a>
            <a class="btn" href="{{ route('branch.pos') }}">Open POS</a>
        </div>
    </div>
    <div class="col-4 card">
        <h1 class="h1">Quick</h1>
        <p class="muted">Actions</p>
        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn" href="{{ route('branch.returns.index') }}">See Returns</a>
            <a class="nav-link {{ request()->routeIs('branch.qr') ? 'active' : '' }}" href="{{ route('branch.qr') }}">
                Attendance QR
            </a>
            
        </div>
    </div>
</div>
@endsection