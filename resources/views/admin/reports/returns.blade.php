@extends('layouts.panel')

@section('title','Returns Report')
@section('panel_name','Admin Panel')

@section('content')
<div style="padding:18px;">
    <div>
        <h2 style="margin:0;">Admin — Returns & Cost Report</h2>
        <div style="color:#6b7280;font-size:13px;">All shops returns + refund totals + return cost (by date)</div>
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
                    <label>Method</label>
                    <select name="method" class="form-control">
                        <option value="">All</option>
                        <option value="counter" @selected(request('method')==='counter' )>Counter</option>
                        <option value="bank" @selected(request('method')==='bank' )>Bank</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;">
                    <button class="btn btn-success">Filter</button>
                    <a class="btn btn-secondary" href="{{ route('admin.reports.returns') }}">Reset</a>
                </div>
            </form>

            @php
            $refund = (float)($totals->refund_total ?? 0);
            $cost = (float)($totals->return_cost_total ?? 0);
            @endphp

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
                <div class="alert alert-info" style="margin:0;">
                    <b>Returns:</b> {{ $totals->return_count ?? 0 }}
                </div>
                <div class="alert alert-danger" style="margin:0;">
                    <b>Total Refund:</b> {{ number_format($refund,2) }}
                </div>
                <div class="alert alert-warning" style="margin:0;">
                    <b>Total Return Cost:</b> {{ number_format($cost,2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body" style="overflow:auto;">
            <table class="table table-striped" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Sale #</th>
                        <th>Shop</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Method</th>
                        <th class="text-end">Refund</th>
                        <th class="text-end">Return Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $r)
                    <tr>
                        <td><b>#{{ $r->id }}</b></td>
                        <td>#{{ $r->sale_id }}</td>
                        <td>{{ strtoupper($r->shop?->type ?? '') }} — {{ $r->shop?->name }}</td>
                        <td>{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $r->user?->name ?? '—' }}</td>
                        <td><b>{{ strtoupper($r->method) }}</b></td>
                        <td class="text-end"><b>{{ number_format($r->refund_amount,2) }}</b></td>
                        <td class="text-end">{{ number_format($r->return_cost_total ?? 0,2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">No returns found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">{{ $returns->links() }}</div>
        </div>
    </div>
</div>
@endsection