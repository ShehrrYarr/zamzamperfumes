@extends('layouts.panel')

@section('title', 'Main Shop Dashboard')
@section('panel_name', 'Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Main Shop Dashboard</h1>
        <p class="muted">
            {{ $mainShop?->name ?? '—' }} ({{ $mainShop?->code ?? '—' }})
        </p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">{{ $branchesCount }}</h1>
        <p class="muted">Total Branches</p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">{{ $mainStaffCount }}</h1>
        <p class="muted">Main Shop Staff</p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">Quick</h1>
        <p class="muted">Actions</p>
        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn" href="{{ route('main.branches.index') }}">View Branches</a>
        </div>
    </div>
</div>
@endsection