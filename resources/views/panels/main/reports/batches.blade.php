@extends('layouts.panel')

@section('title','Batch Report')
@section('panel_name','Main Shop')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
                <div>
                    <div class="h1">Batch Report</div>
                    <p class="muted">Main Shop batches with stock cost summary.</p>
                </div>
                <a href="{{ route('main.dashboard') }}" class="btn btn-outline-secondary">← Back</a>
            </div>

            <hr>

            <form method="GET" class="row">

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>Shop</b></label>
                    <select name="shop_id" class="form-control">
                        @foreach($shops as $s)
                        <option value="{{ $s->id }}" @selected((int)request('shop_id', $selectedShopId)===(int)$s->id)>
                            {{ strtoupper($s->type) }} — {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-4 mb-2">
                    <label class="mb-1"><b>Search (barcode / batch code)</b></label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="e.g. 00012 or BATCH-...">
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>

                <div class="col-12 mt-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('main.reports.batches') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Totals --}}
    <div class="col-12">
        <div class="card stat stat-blue">
            <div class="k">Batches</div>
            <div class="v">{{ number_format($totals->batch_count ?? 0) }}</div>
            <div class="hint">Total batches in filters</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Total Qty</div>
            <div class="v">{{ number_format($totals->total_qty ?? 0) }}</div>
            <div class="hint">All stock quantity</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Total Stock Cost</div>
            <div class="v">{{ number_format($totals->total_stock_cost ?? 0, 2) }}</div>
            <div class="hint">Qty × Cost Price</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Perfume</th>
                            <th>Barcode</th>
                            <th>Batch Code</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Cost Price</th>
                            <th class="text-right">Stock Cost</th>
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
                            <td>{{ $b->id }}</td>
                            <td><b>{{ $b->perfume->name ?? '-' }}</b></td>
                            <td>{{ $b->barcode ?? '-' }}</td>
                            <td>{{ $b->batch_code ?? '-' }}</td>
                            <td class="text-right">{{ number_format($qty) }}</td>
                            <td class="text-right">{{ number_format($cost,2) }}</td>
                            <td class="text-right"><b>{{ number_format($stockCost,2) }}</b></td>
                            <td>{{ optional($b->created_at)->format('Y-m-d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No batches found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $batches->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection