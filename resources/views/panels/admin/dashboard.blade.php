@extends('layouts.panel')

@section('title', 'Admin Dashboard')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">

    {{-- Header --}}
    <div class="col-12">
        <div class="card stat stat-blue" style="padding:22px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">
                <div>
                    <div class="k">Admin Panel</div>
                    <div class="v" style="font-size:30px;line-height:1.1;">Dashboard</div>
                    <div class="hint" style="margin-top:6px;">
                        Manage branches, main shop, staff, inventory, sales & returns.
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <div class="pill" style="border:0;background:rgba(255,255,255,.75);">
                        <b>Today:</b> {{ now()->format('d M Y') }}
                    </div>
                    <a href="{{ route('admin.branches.index') }}" class="btn btn-primary">Manage Branches</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Reports --}}
    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <div class="h1" style="margin:0;">Reports</div>
                    <p class="muted" style="margin:6px 0 0;">View complete system reports with filters and totals.</p>
                </div>
                <div class="pill"><b>Shortcuts</b></div>
            </div>

            <div style="margin-top:14px;" class="grid">
                <div class="col-12">
                    <a href="{{ route('admin.reports.batches') }}" class="card stat stat-purple"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Inventory</div>
                        <div class="v" style="font-size:20px;">Batch Report</div>
                        <div class="hint">Batches, quantities, costs</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('admin.reports.sales') }}" class="card stat stat-blue"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Revenue</div>
                        <div class="v" style="font-size:20px;">Sales Report</div>
                        <div class="hint">Revenue, cost & profit</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('admin.reports.returns') }}" class="card stat stat-amber"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Refunds</div>
                        <div class="v" style="font-size:20px;">Returns Report</div>
                        <div class="hint">Full & partial returns</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('admin.reports.salaries') }}" class="card stat stat-green"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Payroll</div>
                        <div class="v" style="font-size:20px;">Salary Report</div>
                        <div class="hint">Monthly salaries & deductions</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Quick Actions --}}
    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="h1" style="margin:0;">Quick Actions</div>
                    <p class="muted" style="margin:6px 0 0;">Common admin operations</p>
                </div>
            </div>

            <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;">
                <a class="btn btn-primary" href="{{ route('admin.branches.index') }}">Branches</a>
                <a class="btn btn-ghost" href="{{ route('admin.mainshop.show') }}">Main Shop</a>
                <a class="btn btn-ghost" href="{{ route('admin.mainshop.staff.index') }}">Main Shop Staff</a>
                <a class="btn btn-ghost" href="{{ route('admin.perfumes.index') }}">Perfumes</a>
                <a class="btn btn-ghost" href="{{ route('admin.batches.index') }}">Batches</a>
            </div>

            <div style="margin-top:14px;border-top:1px solid rgba(15,23,42,0.08);padding-top:14px;">
                <p class="muted" style="margin:0;">
                    Tip: Use reports for date-wise filtering & totals for each shop.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection