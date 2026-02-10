@extends('layouts.panel')

@section('title', 'Staff Dashboard')
@section('panel_name', 'Staff Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Staff Dashboard</h1>
        <p class="muted">Welcome, {{ $user->name }}.</p>
    </div>

    <div class="col-6 card">
        <h1 class="h1">My Shop</h1>
        <p class="muted">
            {{ $shop?->name ?? '—' }} ({{ $shop?->code ?? '—' }})<br>
            Type: {{ $shop?->type ?? '—' }}
        </p>
    </div>

    <div class="col-6 card">
        <h1 class="h1">Account</h1>
        <p class="muted">
            Email: {{ $user->email }}<br>
            Status: {{ $user->is_active ? 'Active' : 'Disabled' }}
        </p>
    </div>

    <div class="col-12 card">
        <h1 class="h1">Next modules</h1>
        <p class="muted">
            Attendance (QR check-in/out) and POS will appear here once enabled by admin/manager.
        </p>
    </div>
</div>
@endsection