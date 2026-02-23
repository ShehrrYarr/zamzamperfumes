@extends('layouts.panel')

@section('title','Sales Report')
@section('panel_name','Branch Panel')

@section('content')
<div style="padding:18px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
        <div>
            <h2 style="margin:0; font-weight:800;">Branch — Sales & Cost Report</h2>
            <div style="color:#6b7280;font-size:13px;">Branch sales + quantity + cost + profit (filtered)</div>
        </div>
        <a href="{{ route('branch.dashboard') }}" class="btn btn-outline-secondary">← Back</a>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="GET" class="row" style="row-gap:10px;">
                <div class="col-md-2">
                    <label class="mb-1"><b>Sale Type</b></label>
                    <select name="sale_type" class="form-control">
                        <option value="">All</option>
                        <option value="customer" @selected(request('sale_type')==='customer' )>Customer</option>
                        <option value="internal_transfer" @selected(request('sale_type')==='internal_transfer' )>
                            Internal Sale</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>Payment</b></label>
                    <select name="payment_method" class="form-control">
                        <option value="">All</option>
                        <option value="counter" @selected(request('payment_method')==='counter' )>Counter</option>
                        <option value="bank" @selected(request('payment_method')==='bank' )>Bank</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>Bank</b></label>
                    <select name="bank_id" class="form-control">
                        <option value="">All</option>
                        @foreach($banks as $b)
                        <option value="{{ $b->id }}" @selected((string)request('bank_id')===(string)$b->id)>
                            {{ $b->name }}
                        </option>
                        @endforeach
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
                        <option value="">Default (No Returned)</option>
                        <option value="completed" @selected(request('status')==='completed' )>Completed</option>
                        <option value="partial_return" @selected(request('status')==='partial_return' )>Partial Return
                        </option>
                        <option value="returned" @selected(request('status')==='returned' )>Returned</option>
                    </select>
                </div>

                <div class="col-md-12 d-flex align-items-end" style="gap:10px;">
                    <button class="btn btn-success" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route('branch.reports.sales') }}">Reset</a>
                </div>
            </form>

            @php
            $revenue = (float)($totals->revenue_total ?? 0);
            $cost = (float)($totals->cost_total ?? 0);
            $profitVal = (float)($profit ?? 0);
            $qtyTotal = (float)($totals->qty_total ?? 0);
            @endphp

            <div class="row mt-3" style="row-gap:10px;">
                <div class="col-md-3">
                    <div class="alert alert-info mb-0">
                        <b>Sales:</b> {{ $totals->sales_count ?? 0 }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-dark mb-0">
                        <b>Qty Sold:</b> {{ number_format($qtyTotal) }}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-success mb-0">
                        <b>Revenue:</b> {{ number_format($revenue,2) }}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="alert alert-warning mb-0">
                        <b>Cost:</b> {{ number_format($cost,2) }}
                    </div>
                </div>
                <div class="col-md-2">
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
            <table class="table table-hover mb-0" style="min-width:1350px;">
                <thead class="thead-light">
                    <tr>
                        <th style="width:110px;">Sale #</th>
                        <th style="width:170px;">Date</th>
                        <th style="min-width:170px;">Cashier</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:150px;">Sale Type</th>
                        <th style="width:160px;">Payment</th>
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
                    $qty = (int)($s->qty_total ?? 0);
                    $collapseId = 'saleItems_'.$s->id;

                    $st = $s->sale_type ?? 'customer';
                    if($st === null) $st = 'customer';

                    $pm = $s->payment_method ?? '-';
                    $bank = $s->bank_name ?? null;

                    $receiptUrl = route('branch.pos.receipt', $s->id);
                    @endphp

                    <tr data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false"
                        aria-controls="{{ $collapseId }}" style="cursor:pointer;">
                        <td><b>#{{ $s->id }}</b></td>
                        <td>{{ optional($s->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $s->user?->name ?? '—' }}</td>
                        <td>
                            <span
                                class="badge badge-{{ ($s->status==='returned') ? 'danger' : (($s->status==='partial_return') ? 'warning' : 'success') }}">
                                {{ $s->status ?? '-' }}
                            </span>
                        </td>
                        <td><b>{{ strtoupper($st) }}</b></td>
                        <td>
                            <b>{{ strtoupper($pm) }}</b>
                            @if($bank)<div class="text-muted" style="font-size:12px;">{{ $bank }}</div>@endif
                        </td>
                        <td class="text-right"><b>{{ $qty }}</b></td>
                        <td class="text-right"><b>{{ number_format($rev,2) }}</b></td>
                        <td class="text-right">{{ number_format($cst,2) }}</td>
                        <td class="text-right">
                            <b style="color:{{ $pf>=0 ? '#16a34a' : '#ef4444' }};">{{ number_format($pf,2) }}</b>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ $receiptUrl }}"
                                onclick="event.stopPropagation();">
                                Open
                            </a>
                        </td>
                    </tr>

                    <tr class="collapse" id="{{ $collapseId }}">
                        <td colspan="11" style="background:#f8fafc;">
                            <div style="padding:12px;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center"
                                    style="gap:10px;">
                                    <div>
                                        <b>Sale Items</b>
                                        <span class="text-muted" style="font-size:12px;">— #{{ $s->id }}</span>
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
                        <td colspan="11" class="text-center py-4">No sales found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">{{ $sales->links() }}</div>
        </div>
    </div>
</div>
@endsection