@extends('layouts.panel')

@section('title','Sales & Cost Report')
@section('panel_name','Admin Panel')

@section('content')
<div style="padding:18px;">
    <div>
        <h2 style="margin:0;">Admin — Sales & Cost Report</h2>
        <div style="color:#6b7280;font-size:13px;">All shops sales + total cost + profit (by date)</div>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body">
            <form method="GET" style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;align-items:end;">
                <div>
                    <label>Shop</label>
                    <select name="shop_id" class="form-control">
                        <option value="">All Shops</option>
                        @foreach($shops as $s)
                        <option value="{{ $s->id }}" @selected(request('shop_id')==$s->id)>
                            {{ strtoupper($s->type) }} — {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div>
                    <label>To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div>
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="completed" @selected(request('status')==='completed' )>Completed</option>
                        <option value="returned" @selected(request('status')==='returned' )>Returned</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;">
                    <button class="btn btn-success">Filter</button>
                    <a class="btn btn-secondary" href="{{ route('admin.reports.sales') }}">Reset</a>
                </div>
            </form>

            @php
            $revenue = (float)($totals->revenue_total ?? 0);
            $cost = (float)($totals->cost_total ?? 0);
            @endphp

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
                <div class="alert alert-info" style="margin:0;">
                    <b>Sales:</b> {{ $totals->sales_count ?? 0 }}
                </div>
                <div class="alert alert-success" style="margin:0;">
                    <b>Total Revenue:</b> {{ number_format($revenue,2) }}
                </div>
                <div class="alert alert-warning" style="margin:0;">
                    <b>Total Cost:</b> {{ number_format($cost,2) }}
                </div>
                <div class="alert {{ $profit >= 0 ? 'alert-primary' : 'alert-danger' }}" style="margin:0;">
                    <b>Profit:</b> {{ number_format($profit,2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body" style="overflow:auto;">
            <table class="table table-striped" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th>Sale #</th>
                        <th>Shop</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Status</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                    @php
                    $rev = (float)($s->grand_total ?? 0);
                    $cst = (float)($s->cost_total ?? 0);
                    $pf = $rev - $cst;
                    @endphp
                    <tr>
                        <td><b>#{{ $s->id }}</b></td>
                        <td>{{ strtoupper($s->shop?->type ?? '') }} — {{ $s->shop?->name }}</td>
                        <td>{{ optional($s->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $s->user?->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $s->status==='returned' ? 'danger' : 'success' }}">{{ $s->status
                                }}</span></td>
                        <td class="text-end"><b>{{ number_format($rev,2) }}</b></td>
                        <td class="text-end">{{ number_format($cst,2) }}</td>
                        <td class="text-end">
                            <b style="color:{{ $pf>=0 ? '#16a34a' : '#ef4444' }};">{{ number_format($pf,2) }}</b>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">No sales found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">{{ $sales->links() }}</div>
        </div>
    </div>
</div>
@endsection