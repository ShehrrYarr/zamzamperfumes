@extends('user_navbar')
@section('content')
<style>
    .vr-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, .06)
    }

    .vr-card .card-header {
        background: #fff;
        border-bottom: 0;
        padding-bottom: 0
    }

    .metric {
        border-radius: 14px;
        padding: 16px;
        background: linear-gradient(180deg, #fff, #f9fafb);
        box-shadow: inset 0 1px 0 #fff, 0 1px 1px rgba(0, 0, 0, .04)
    }

    .metric .label {
        color: #6b7280;
        font-size: .875rem
    }

    .metric .value {
        font-weight: 700;
        font-size: 1.25rem;
        color: #111827
    }

    .table thead th {
        background: #f3f4f6;
        border-bottom: 0
    }

    .chip {
        display: inline-block;
        padding: .25rem .5rem;
        border-radius: 9999px;
        font-size: .75rem;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 600
    }

    .soft-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent)
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
            <h3 class="mb-0 fw-bold">Vendor Receive History</h3>
            <div class="text-muted">
                Vendor:
                <span class="fw-semibold">{{ $vendor->name ?? 'Unknown Vendor' }}</span>
                @if(!empty($vendor->mobile_no))
                <span class="chip ms-2">+{{ $vendor->mobile_no }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">← Back</a>
            <button onclick="window.print()" class="btn btn-primary">Print</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="vr-card card mb-4">
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
                    <a href="{{ route('showVRHistory', $vendor->id) }}" class="btn btn-light w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Metrics --}}
    @php $fmt = fn($n) => number_format((float)$n, 2); @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="metric">
                <div class="label">Total Batches</div>
                <div class="value">{{ $batches->count() }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="metric">
                <div class="label">Qty Purchased</div>
                <div class="value">{{ number_format($totalQtyPurchased) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="metric">
                <div class="label">Qty Remaining</div>
                <div class="value">{{ number_format($totalQtyRemaining) }}</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="metric">
                <div class="label">Total Purchase Cost</div>
                <div class="value">Rs. {{ $fmt($totalPurchaseCost) }}</div>
            </div>
        </div>
    </div>

    {{-- Accessory Breakdown --}}
    <div class="vr-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">By Accessory</h5>
            <div class="text-muted">Aggregated purchases from this vendor</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Accessory</th>
                            <th class="text-center">Batches</th>
                            <th class="text-center">Qty Purchased</th>
                            <th class="text-center">Qty Remaining</th>
                            <th class="text-center">Avg. Purchase</th>
                            <th class="text-center">Avg. Selling</th>
                            <th class="text-end">Total Cost</th>
                            <th class="text-end">Last Purchase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byAccessory as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['batches_count'] }}</td>
                            <td class="text-center">{{ number_format($row['qty_purchased']) }}</td>
                            <td class="text-center">{{ number_format($row['qty_remaining']) }}</td>
                            <td class="text-center">Rs. {{ $fmt($row['avg_purchase']) }}</td>
                            <td class="text-center">Rs. {{ $fmt($row['avg_selling']) }}</td>
                            <td class="text-end">Rs. {{ $fmt($row['total_cost']) }}</td>
                            <td class="text-end">{{ \Carbon\Carbon::parse($row['last_purchase'])->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No accessories found for this vendor in
                                the selected range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Batches Table --}}
    <div class="vr-card card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Batches</h5>
            <div class="text-muted">Detailed list of batches received from this vendor</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Accessory</th>
                            <th class="text-center">Qty Purchased</th>
                            <th class="text-center">Qty Remaining</th>
                            <th class="text-center">Purchase Price</th>
                            <th class="text-center">Selling Price</th>
                            <th class="text-end">Line Cost</th>
                            <th class="text-end">Purchase Date</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $b)
                        @php
                        $lineCost = (float)$b->purchase_price * (int)$b->qty_purchased;
                        @endphp
                        <tr>
                            <td><span class="chip">{{ $b->barcode }}</span></td>
                            <td>{{ optional($b->accessory)->name }}</td>
                            <td class="text-center">{{ number_format($b->qty_purchased) }}</td>
                            <td class="text-center">{{ number_format($b->qty_remaining) }}</td>
                            <td class="text-center">Rs. {{ $fmt($b->purchase_price) }}</td>
                            <td class="text-center">
                                @if(!is_null($b->selling_price))
                                Rs. {{ $fmt($b->selling_price) }}
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">Rs. {{ $fmt($lineCost) }}</td>
                            <td class="text-end">{{ \Carbon\Carbon::parse($b->purchase_date)->format('d M Y') }}</td>
                            <td>{{ optional($b->user)->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No batches found for this vendor in the
                                selected range.</td>
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