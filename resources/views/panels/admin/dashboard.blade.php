@extends('layouts.panel')

@section('title', 'Admin Dashboard')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Admin Dashboard</h1>
        <p class="muted">From here you will manage branches, main shop, and system settings.</p>
    </div>

    <div class="col-4 card">
        <h1 class="h1">Quick</h1>
        <p class="muted">Actions</p>
        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a class="btn" href="{{ route('admin.reports.batches') }}">Batch Report</a>
            <a class="btn btn-ghost" href="{{ route('admin.reports.sales') }}">Sales Report</a>
            <a class="btn" href="{{ route('admin.reports.returns') }}">Returns Report</a>
        </div>
    </div>
</div>
@endsection