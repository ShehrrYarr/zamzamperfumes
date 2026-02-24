@extends('layouts.panel')

@section('title', 'Main Shop Dashboard')
@section('panel_name', 'Main Shop Panel')

@section('content')
<div class="grid">

    {{-- Header --}}
    <div class="col-12">
        <div class="card stat stat-blue" style="padding:22px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">
                <div>
                    <div class="k">Main Shop</div>
                    <div class="v" style="font-size:30px;line-height:1.1;">Dashboard</div>
                    <div class="hint" style="margin-top:6px;">
                        <b>{{ $mainShop?->name ?? '—' }}</b> ({{ $mainShop?->code ?? '—' }})
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <a href="{{ route('main.pos') }}" class="btn btn-primary">Open POS</a>
                    <a href="{{ route('main.inventory.index') }}" class="btn btn-outline-secondary">Inventory</a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Total Branches</div>
            <div class="v">{{ number_format($branchesCount ?? 0) }}</div>
            <div class="hint">Active branch shops</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-green">
            <div class="k">Main Shop Staff</div>
            <div class="v">{{ number_format($mainStaffCount ?? 0) }}</div>
            <div class="hint">Employees in main shop</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Quick Access</div>
            <div class="v" style="font-size:20px;">Reports</div>
            <div class="hint">Batches • Sales • Returns</div>
            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn btn-primary btn-sm" href="{{ route('main.reports.batches') }}">Batch</a>
                <a class="btn btn-primary btn-sm" href="{{ route('main.reports.sales') }}">Sales</a>
                <a class="btn btn-primary btn-sm" href="{{ route('main.reports.returns') }}">Returns</a>
                <a class="btn btn-primary btn-sm" href="{{ route('main.reports.daily') }}">Daily Report</a>
            </div>
        </div>
    </div>

    {{-- Action cards --}}
    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <div class="h1" style="margin:0;">Operations</div>
                    <p class="muted" style="margin:6px 0 0;">Daily actions for managing the shop.</p>
                </div>
                <div class="pill"><b>Quick</b></div>
            </div>

            <div style="margin-top:14px;" class="grid">
                <div class="col-12">
                    <a href="{{ route('main.branches.index') }}" class="card stat stat-purple"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Branches</div>
                        <div class="v" style="font-size:18px;">View Branches</div>
                        <div class="hint">Manage branch shops</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('main.transfers.index') }}" class="card stat stat-blue"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Transfers</div>
                        <div class="v" style="font-size:18px;">Transfer History</div>
                        <div class="hint">Track outgoing transfers</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('main.returns.index') }}" class="card stat stat-amber"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Returns</div>
                        <div class="v" style="font-size:18px;">See Returns</div>
                        <div class="hint">Full & partial returns</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('main.pos') }}" class="card stat stat-green"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">POS</div>
                        <div class="v" style="font-size:18px;">Open POS</div>
                        <div class="hint">Sell & print receipt</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <div class="h1" style="margin:0;">Staff & Attendance</div>
                    <p class="muted" style="margin:6px 0 0;">Attendance QR and monthly payroll.</p>
                </div>
                <div class="pill"><b>HR</b></div>
            </div>

            <div style="margin-top:14px;" class="grid">
                <div class="col-12">
                    <a href="{{ route('shop.qr') }}" class="card stat stat-blue"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Attendance</div>
                        <div class="v" style="font-size:18px;">Attendance QR</div>
                        <div class="hint">Scan for check-in/out</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('main.reports.salaries') }}" class="card stat stat-purple"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Payroll</div>
                        <div class="v" style="font-size:18px;">Salary Report</div>
                        <div class="hint">Deductions & totals</div>
                    </a>
                </div>

                <div class="col-12">
                    <div class="card"
                        style="padding:16px;background:rgba(255,255,255,.75);border:1px solid rgba(15,23,42,.08);">
                        <div style="font-weight:900;">Tip</div>
                        <div class="muted" style="margin-top:4px;">
                            Use the sales report filter to check payment type (Counter/Bank) and bank-wise totals.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection