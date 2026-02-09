@extends('user_navbar')
@section('content')
<style>
    .vs-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
    }

    .vs-card .card-header {
        background: #fff;
        border-bottom: 0;
        padding-bottom: 0;
    }

    .metric {
        border-radius: 14px;
        padding: 16px;
        background: linear-gradient(180deg, #fff, #f9fafb);
        box-shadow: inset 0 1px 0 #fff, 0 1px 1px rgba(0, 0, 0, .04);
    }

    .metric .label {
        color: #6b7280;
        font-size: .875rem;
    }

    .metric .value {
        font-weight: 700;
        font-size: 1.25rem;
        color: #111827;
    }

    .table thead th {
        background: #f3f4f6;
        border-bottom: 0;
    }

    .chip {
        display: inline-block;
        padding: .25rem .5rem;
        border-radius: 9999px;
        font-size: .75rem;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 600;
    }

    .soft-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
    }

    .sticky-actions {
        position: sticky;
        top: 0;
        z-index: 9;
        background: #fff;
        padding: .5rem 0;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

<div class="container-xxl py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0 fw-bold">Vendor Sales History</h3>
            <div class="text-muted">
                Vendor:
                <span class="fw-semibold">{{ $vendor->name ?? 'Unknown Vendor' }}</span>
                @if(!empty($vendor->mobile_no))
                <span class="chip ms-2">+{{ $vendor->mobile_no }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                ← Back
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="vs-card card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label mb-1">Start date</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ old('start_date', $start_date) }}">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label mb-1">End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $end_date) }}">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ route('showVSHistory', $vendor->id) }}" class="btn btn-light w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Metrics --}}
    @php
    $fmt = fn($n) => number_format((float)$n, 2);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="metric">
                <div class="label">Total Sold (after discount)</div>
                <div class="value">Rs. {{ $fmt($totalSoldAmount) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="metric">
                <div class="label">Total Paid</div>
                <div class="value">Rs. {{ $fmt($totalPaidAmount) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="metric">
                <div class="label">Remaining</div>
                <div class="value {{ ($totalRemaining ?? 0) == 0 ? 'text-success' : '' }}">
                    @if(($totalRemaining ?? 0) == 0 && ($totalSoldAmount ?? 0) > 0)
                    PAID IN FULL
                    @else
                    Rs. {{ $fmt($totalRemaining) }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Accessory Breakdown --}}
    <div class="vs-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">By Accessory</h5>
            <div class="text-muted">What this vendor bought, aggregated by accessory</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Accessory</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Avg. Unit Price</th>
                            <th class="text-end">Gross (before cart discount)</th>
                            <th class="text-end">Last Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byAccessory as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['qty'] }}</td>
                            <td class="text-center">Rs. {{ $fmt($row['avg_price']) }}</td>
                            <td class="text-end">Rs. {{ $fmt($row['gross']) }}</td>
                            <td class="text-end">
                                {{ \Carbon\Carbon::parse($row['last_sold_at'])->format('d M Y, H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No items found for this vendor in the
                                selected range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Batch Breakdown --}}
    <div class="vs-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">By Batch</h5>
            <div class="text-muted">Which batches were sold to this vendor</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Accessory</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-end">Line Gross</th>
                            <th class="text-end">Purchased On</th>
                            <th class="text-center">Qty Remaining (Batch)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byBatch as $row)
                        <tr>
                            <td><span class="chip">{{ $row['barcode'] }}</span></td>
                            <td>{{ $row['accessory'] }}</td>
                            <td class="text-center">{{ $row['qty_sold'] }}</td>
                            <td class="text-center">Rs. {{ $fmt($row['unit_price']) }}</td>
                            <td class="text-end">Rs. {{ $fmt($row['line_gross']) }}</td>
                            <td class="text-end">
                                {{ \Carbon\Carbon::parse($row['purchase_date'])->format('d M Y') }}
                            </td>
                            <td class="text-center">{{ $row['qty_remaining'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No batches found for this vendor in the
                                selected range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Individual Sales (Invoices) --}}
    <div class="vs-card card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Invoices</h5>
            <div class="text-muted">
                Showing {{ $sales->count() }} {{ Str::plural('invoice', $sales->count()) }}
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Remaining</th>
                            <th>Added By</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        @php
                        $gross = $sale->items->sum('subtotal');
                        $discount = (float) ($sale->discount_amount ?? 0);
                        $net = max($gross - $discount, 0);
                        $paid = (float) ($sale->pay_amount ?? 0);
                        if ($paid < 0) $paid=0; if ($paid> $net) $paid = $net;
                            $remaining = max($net - $paid, 0);
                            @endphp
                            <tr>
                                <td>#{{ $sale->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
                                <td style="max-width:360px;">
                                    <ul class="mb-0 ps-3">
                                        @foreach($sale->items as $item)
                                        <li>
                                            {{ $item->batch->accessory->name ?? '-' }} ×{{ $item->quantity }}
                                            ({{ number_format($item->price_per_unit,2) }} each)
                                        </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-end">Rs. {{ $fmt($gross) }}</td>
                                <td class="text-end">Rs. {{ $fmt($discount) }}</td>
                                <td class="text-end fw-semibold">Rs. {{ $fmt($net) }}</td>
                                <td class="text-end {{ $paid > 0 ? 'text-success fw-semibold' : '' }}">Rs. {{
                                    $fmt($paid) }}</td>
                                <td class="text-end {{ $remaining == 0 ? 'text-success fw-semibold' : '' }}">
                                    @if($remaining == 0 && $net > 0) PAID IN FULL @else Rs. {{ $fmt($remaining) }}
                                    @endif
                                </td>
                                <td>{{ $sale->user->name ?? '-' }}</td>
                                <td>
                                    @if($sale->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                    @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" target="_blank"
                                        href="{{ url('/pos/invoice/'.$sale->id) }}">
                                        View Invoice
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No invoices found for this vendor
                                    in the selected range.</td>
                            </tr>
                            @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</div>
@endsection