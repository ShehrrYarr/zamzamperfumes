@extends('layouts.panel')

@section('title','Sales & Cost Report')
@section('panel_name','Admin Panel')

@section('content')
<div style="padding:18px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
        <div>
            <h2 style="margin:0; font-weight:800;">Admin — Sales & Cost Report</h2>
            <div style="color:#6b7280;font-size:13px;">All shops sales + total cost + profit (by date)</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="GET" class="row" style="row-gap:10px;">
                <div class="col-md-4">
                    <label class="mb-1"><b>Shop</b></label>
                    <select name="shop_id" class="form-control">
                        <option value="">All Shops</option>
                        @foreach($shops as $s)
                        <option value="{{ $s->id }}" @selected((string)request('shop_id')===(string)$s->id)>
                            {{ strtoupper($s->type) }} — {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                                    <label class="mb-1"><b>Sale Type</b></label>
                                    <select name="sale_type" class="form-control">
                                        <option value="">All</option>
                                        <option value="customer" @selected(request('sale_type')==='customer' )>Customer</option>
                                        <option value="internal_transfer" @selected(request('sale_type')==='internal_transfer' )>Internal Sale</option>
                                    </select>
                                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>Status</b></label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="completed" @selected(request('status')==='completed' )>Completed</option>
                        <option value="returned" @selected(request('status')==='returned' )>Returned</option>
                        <option value="partial_return" @selected(request('status')==='partial_return' )>Partial Return
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end" style="gap:10px;">
                    <button class="btn btn-success btn-block" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary btn-block" href="{{ route('admin.reports.sales') }}">Reset</a>
                </div>
            </form>

            @php
            $revenue = (float)($totals->revenue_total ?? 0);
            $cost = (float)($totals->cost_total ?? 0);
            $profitVal = (float)($profit ?? 0);
            @endphp

            <div class="row mt-3" style="row-gap:10px;">
                <div class="col-md-3">
                    <div class="alert alert-info mb-0">
                        <b>Sales:</b> {{ $totals->sales_count ?? 0 }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-dark mb-0">
                        <b>Qty Sold:</b> {{ number_format((float)($totals->qty_total ?? 0)) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success mb-0">
                        <b>Total Revenue:</b> {{ number_format($revenue,2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning mb-0">
                        <b>Total Cost:</b> {{ number_format($cost,2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert {{ $profitVal >= 0 ? 'alert-primary' : 'alert-danger' }} mb-0">
                        <b>Profit:</b> {{ number_format($profitVal,2) }}
                    </div>
                </div>
            </div>

            <div class="mt-2" style="color:#6b7280;font-size:12px;">
                Tip: Click a row to expand and see items.
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body" style="overflow:auto;">
            <table class="table table-hover mb-0" style="min-width:1250px;">
                <thead class="thead-light">
                    <tr>
                        <th style="width:110px;">Sale #</th>
                        <th style="min-width:240px;">Shop</th>
                        <th style="width:170px;">Date</th>
                        <th style="min-width:170px;">Cashier</th>
                        <th style="width:130px;">Status</th>
                        <th class="text-right" style="width:110px;">Qty</th>
                        <th class="text-right" style="width:140px;">Revenue</th>
                        <th class="text-right" style="width:140px;">Cost</th>
                        <th class="text-right" style="width:140px;">Profit</th>
                        <th style="width:130px;">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                    @php
                    $rev = (float)($s->grand_total ?? 0);
                    $cst = (float)($s->cost_total ?? 0);
                    $pf = $rev - $cst;

                    $collapseId = 'saleItems_'.$s->id;

                    // Receipt link based on shop type
                    $receiptUrl = null;
                    $type = $s->shop?->type;
                    if ($type === 'main') $receiptUrl = route('main.pos.receipt', $s->id);
                    if ($type === 'branch') $receiptUrl = route('branch.pos.receipt', $s->id);
                    @endphp

                    {{-- Main clickable row --}}
                    <tr data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false"
                        aria-controls="{{ $collapseId }}" style="cursor:pointer;">
                        <td><b>#{{ $s->id }}</b></td>
                        <td>{{ strtoupper($s->shop?->type ?? '') }} — {{ $s->shop?->name ?? '—' }}</td>
                        <td>{{ optional($s->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $s->user?->name ?? '—' }}</td>
                        <td>
                            <span
                                class="badge badge-{{ ($s->status==='returned') ? 'danger' : (($s->status==='partial_return') ? 'warning' : 'success') }}">
                                {{ $s->status ?? '-' }}
                            </span>
                        </td>
                        <td class="text-right"><b>{{ (int)($s->qty_total ?? 0) }}</b></td>
                        <td class="text-right"><b>{{ number_format($rev,2) }}</b></td>
                        <td class="text-right">{{ number_format($cst,2) }}</td>
                        <td class="text-right">
                            <b style="color:{{ $pf>=0 ? '#16a34a' : '#ef4444' }};">{{ number_format($pf,2) }}</b>
                        </td>
                        <td>
                            @if($receiptUrl)
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ $receiptUrl }}"
                                onclick="event.stopPropagation();">
                                Open
                            </a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Collapsible items row --}}
                    <tr class="collapse" id="{{ $collapseId }}">
                        <td colspan="9" style="background:#f8fafc;">
                            <div style="padding:12px;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center"
                                    style="gap:10px;">
                                    <div>
                                        <b>Sale Items</b>
                                        <span class="text-muted" style="font-size:12px;">
                                            — #{{ $s->id }}
                                        </span>
                                    </div>

                                    <div class="text-muted" style="font-size:12px;">
                                        Subtotal: <b>{{ number_format((float)($s->subtotal ?? 0), 2) }}</b>
                                        &nbsp; | &nbsp; Discount: <b>{{ number_format((float)($s->discount_amount ?? 0),
                                            2) }}</b>
                                        &nbsp; | &nbsp; Grand: <b>{{ number_format((float)($s->grand_total ?? 0), 2)
                                            }}</b>
                                    </div>
                                </div>

                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0" style="background:white;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:170px;">Barcode</th>
                                                <th>Item</th>
                                                <th class="text-right" style="width:110px;">Unit</th>
                                                <th class="text-right" style="width:90px;">Qty</th>
                                                <th class="text-right" style="width:130px;">Line Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($s->items as $it)
                                            <tr>
                                                <td><b>{{ $it->barcode }}</b></td>
                                                <td>{{ $it->item_name }}</td>
                                                <td class="text-right">{{ number_format((float)$it->unit_price, 2) }}
                                                </td>
                                                <td class="text-right">{{ (int)$it->quantity }}</td>
                                                <td class="text-right"><b>{{ number_format((float)$it->line_total, 2)
                                                        }}</b></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">No items found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">No sales found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
</div>

{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<!-- Popper (required for Bootstrap tooltips/collapse positioning) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
    integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
</script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
    integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous">
</script>
<script> --}}
@endsection