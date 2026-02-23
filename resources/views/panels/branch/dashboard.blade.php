@extends('layouts.panel')

@section('title', 'Branch Dashboard')
@section('panel_name', 'Branch Panel')

@section('content')
<div class="grid">

    {{-- Header --}}
    <div class="col-12">
        <div class="card stat stat-blue" style="padding:22px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">
                <div>
                    <div class="k">Branch Panel</div>
                    <div class="v" style="font-size:30px;line-height:1.1;">Dashboard</div>
                    <div class="hint" style="margin-top:6px;">
                        Welcome, <b>{{ auth()->user()->name }}</b> —
                        <span style="opacity:.9;">{{ $branch?->name ?? '—' }}</span> ({{ $branch?->code ?? '—' }})
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <a href="{{ route('branch.pos') }}" class="btn btn-primary">Open POS</a>
                    <a href="{{ route('branch.inventory.index') }}" class="btn btn-outline-secondary">Inventory</a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Total Staff</div>
            <div class="v">{{ number_format($staffCount ?? 0) }}</div>
            <div class="hint">All branch employees</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-green">
            <div class="k">Active Staff</div>
            <div class="v">{{ number_format($activeStaffCount ?? 0) }}</div>
            <div class="hint">Currently active accounts</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Quick Access</div>
            <div class="v" style="font-size:20px;">Transfers</div>
            <div class="hint">Claim transfer & history</div>
            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn btn-primary btn-sm" href="{{ route('branch.transfers.claim_form') }}">Claim</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('branch.transfers.index') }}">History</a>
            </div>
        </div>
    </div>

    {{-- Operations --}}
    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <div class="h1" style="margin:0;">Operations</div>
                    <p class="muted" style="margin:6px 0 0;">Daily actions for the branch.</p>
                </div>
                <div class="pill"><b>Quick</b></div>
            </div>

            <div style="margin-top:14px;" class="grid">
                <div class="col-12">
                    <a href="{{ route('branch.pos') }}" class="card stat stat-blue"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">POS</div>
                        <div class="v" style="font-size:18px;">Open POS</div>
                        <div class="hint">Sell & print receipt</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('branch.returns.index') }}" class="card stat stat-amber"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Returns</div>
                        <div class="v" style="font-size:18px;">See Returns</div>
                        <div class="hint">Full & partial returns</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('branch.inventory.index') }}" class="card stat stat-green"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Inventory</div>
                        <div class="v" style="font-size:18px;">View Stock</div>
                        <div class="hint">Branch batch quantities</div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('branch.reports.sales') }}" class="card stat stat-green"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Reports</div>
                        <div class="v" style="font-size:18px;">Sales Report</div>
                        <div class="hint">View sales report</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('branch.banks.index') }}" class="card stat stat-purple"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Banks</div>
                        <div class="v" style="font-size:18px;">Manage Banks</div>
                        <div class="hint">Payments (Bank method)</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff & Attendance --}}
    <div class="col-12">
        <div class="card" style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                <div>
                    <div class="h1" style="margin:0;">Staff & Attendance</div>
                    <p class="muted" style="margin:6px 0 0;">Manage staff, attendance QR and payroll reports.</p>
                </div>
                <div class="pill"><b>HR</b></div>
            </div>

            <div style="margin-top:14px;" class="grid">
                <div class="col-12">
                    <a href="{{ route('branch.staff.index') }}" class="card stat stat-purple"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Staff</div>
                        <div class="v" style="font-size:18px;">Manage Staff</div>
                        <div class="hint">Enable/disable accounts</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('branch.staff.create') }}" class="card stat stat-green"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Staff</div>
                        <div class="v" style="font-size:18px;">Add Staff</div>
                        <div class="hint">Create new login</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('shop.qr') }}" class="card stat stat-blue"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Attendance</div>
                        <div class="v" style="font-size:18px;">Attendance QR</div>
                        <div class="hint">Scan for check-in/out</div>
                    </a>
                </div>

                <div class="col-12">
                    <a href="{{ route('branch.reports.salaries') }}" class="card stat stat-amber"
                        style="display:block;text-decoration:none;color:inherit;padding:16px;">
                        <div class="k">Payroll</div>
                        <div class="v" style="font-size:18px;">Salary Report</div>
                        <div class="hint">Monthly salary & deductions</div>
                    </a>
                </div>

                <div class="col-12">
                    <div class="card"
                        style="padding:16px;background:rgba(255,255,255,.75);border:1px solid rgba(15,23,42,.08);">
                        <div style="font-weight:900;">Tip</div>
                        <div class="muted" style="margin-top:4px;">
                            Use <b>Returns</b> page to track refunds and stock adjustments.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection