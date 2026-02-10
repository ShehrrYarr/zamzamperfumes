@extends('layouts.panel')

@section('title','Batches Report')
@section('panel_name','Admin Panel')

@section('content')

<div style="padding:18px;">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
        <div>
            <h2 style="margin:0;">Admin — Batches Cost Report</h2>
            <div style="color:#6b7280;font-size:13px;">Added batches + inventory cost (by date)</div>
        </div>
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
                    <label>Search</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="barcode / batch code">
                </div>

                <div style="display:flex;gap:10px;">
                    <button class="btn btn-success">Filter</button>
                    <a class="btn btn-secondary" href="{{ route('admin.reports.batches') }}">Reset</a>
                </div>
            </form>

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
                <div class="alert alert-info" style="margin:0;">
                    <b>Batches:</b> {{ $totals->batch_count ?? 0 }}
                </div>
                <div class="alert alert-info" style="margin:0;">
                    <b>Total Qty:</b> {{ $totals->total_qty ?? 0 }}
                </div>
                <div class="alert alert-warning" style="margin:0;">
                    <b>Total Stock Cost:</b> {{ number_format($totals->total_stock_cost ?? 0, 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body" style="overflow:auto;">
            <table class="table table-striped" style="min-width:1100px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Shop</th>
                        <th>Barcode</th>
                        <th>Perfume</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Cost Price</th>
                        <th class="text-end">Stock Cost</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $b)
                    @php
                    $qty = (int)($b->quantity ?? 0);
                    $cost = (float)($b->cost_price ?? 0);
                    $stockCost = $qty * $cost;
                    @endphp
                    <tr>
                        <td>#{{ $b->id }}</td>
                        <td>{{ strtoupper($b->shop?->type ?? '') }} — {{ $b->shop?->name }}</td>
                        <td><b>{{ $b->barcode }}</b></td>
                        <td>{{ $b->perfume?->name ?? '—' }}</td>
                        <td class="text-end">{{ $qty }}</td>
                        <td class="text-end">{{ number_format($cost,2) }}</td>
                        <td class="text-end"><b>{{ number_format($stockCost,2) }}</b></td>
                        <td>{{ optional($b->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">No batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">{{ $batches->links() }}</div>
        </div>
    </div>
</div>
@endsection