@extends('layouts.panel')

@section('title','Daily Report')
@section('panel_name','Main Shop')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
                <div>
                    <div class="h1">Daily Report</div>
                    <p class="muted">Batches added, sales, refunds, and net cash — all in one place.</p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn btn-outline-secondary" href="{{ route('main.dashboard') }}">← Back</a>
                    <a class="btn btn-primary" href="{{ route('main.reports.daily.pdf', ['date' => $date]) }}">
                        Download PDF
                    </a>
                </div>
            </div>

            <hr>

            <form method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>Date</b></label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">View</button>
                </div>
            </form>
        </div>
    </div>

    {{-- NET CASH --}}
    <div class="col-12">
        <div class="card stat stat-green">
            <div class="k">Net Cash (Payments Today)</div>
            <div class="v">{{ number_format($netTotals['total'] ?? 0, 2) }}</div>
            <div class="hint">
                Counter: <b>{{ number_format($netTotals['counter'] ?? 0, 2) }}</b>
                &nbsp; | &nbsp;
                Bank: <b>{{ number_format($netTotals['bank'] ?? 0, 2) }}</b>
            </div>
        </div>
    </div>

    {{-- REFUNDS --}}
    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Refunds Processed Today</div>
            <div class="v">{{ number_format($refundTotals['total'] ?? 0, 2) }}</div>
            <div class="hint">
                Counter: <b>{{ number_format($refundTotals['counter'] ?? 0, 2) }}</b>
                &nbsp; | &nbsp;
                Bank: <b>{{ number_format($refundTotals['bank'] ?? 0, 2) }}</b>
                <span style="display:block; font-size:12px; color:#6b7280;">
                    Refunds reduce the same payment method’s cash automatically.
                </span>
            </div>
        </div>
    </div>

    {{-- BATCHES ADDED --}}
    <div class="col-12">
        <div class="card stat stat-blue">
            <div class="k">Batches Added Today</div>
            <div class="v">{{ number_format($batchTotals->count ?? 0) }}</div>
            <div class="hint">
                Qty: <b>{{ number_format($batchTotals->qty ?? 0) }}</b>
                &nbsp; | &nbsp;
                Stock Cost: <b>{{ number_format($batchTotals->cost ?? 0, 2) }}</b>
            </div>
        </div>
    </div>

    {{-- SALES --}}
    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Sales Created Today (Customer Only)</div>
            <div class="v">{{ number_format($salesTotals->count ?? 0) }}</div>
            <div class="hint">
                Gross Sales: <b>{{ number_format($salesTotals->gross_sales ?? 0, 2) }}</b>
                <span style="display:block; font-size:12px; color:#6b7280;">
                    Note: Money totals come from Payments section (net), not from gross sales.
                </span>
            </div>
        </div>
    </div>

    {{-- TOP PERFUMES --}}
    <div class="col-12">
        <div class="card">
            <div class="card-h">
                <div class="h">Top Sold Perfumes (Qty)</div>
                <div class="sub">Aggregated from sale items of today’s sales</div>
            </div>
            <div class="card-b">
                @if(($topPerfumes ?? collect())->count() === 0)
                <div class="text-muted">No items sold today.</div>
                @else
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    @foreach($topPerfumes as $p)
                    <span class="badge">
                        {{ $p->name }} <b>({{ (int)$p->qty }})</b>
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PAYMENTS TABLE --}}
    <div class="col-12">
        <div class="card">
            <div class="card-h">
                <div class="h">Payments Today (Sales + Refunds)</div>
                <div class="sub">Refunds are negative amounts</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Time</th>
                            <th>Sale</th>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ optional($p->paid_at)->format('H:i') }}</td>
                            <td>#{{ $p->sale_id }}</td>
                            <td>{{ strtoupper($p->method ?? '-') }}</td>
                            <td class="text-right">
                                <b style="color:{{ (float)$p->amount < 0 ? '#dc2626' : '#16a34a' }}">
                                    {{ number_format((float)$p->amount, 2) }}
                                </b>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No payments today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection