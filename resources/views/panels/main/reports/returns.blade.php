@extends('layouts.panel')

@section('title','Return Report')
@section('panel_name','Main Shop')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
                <div>
                    <div class="h1">Return Report</div>
                    <p class="muted">Main Shop returns with refund and return cost.</p>
                </div>
                <a href="{{ route('main.dashboard') }}" class="btn btn-outline-secondary">← Back</a>
            </div>

            <hr>

            <form method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-4 mb-2">
                    <label class="mb-1"><b>Method</b></label>
                    <select name="method" class="form-control">
                        <option value="">All</option>
                        <option value="full" @selected(request('method')==='full' )>Full Return</option>
                        <option value="partial" @selected(request('method')==='partial' )>Partial Return</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>

                <div class="col-12 mt-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('main.reports.returns') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Totals --}}
    <div class="col-12">
        <div class="card stat stat-blue">
            <div class="k">Returns</div>
            <div class="v">{{ number_format($totals->return_count ?? 0) }}</div>
            <div class="hint">Filtered returns</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Refund Total</div>
            <div class="v">{{ number_format($totals->refund_total ?? 0, 2) }}</div>
            <div class="hint">Total refund amount</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Return Cost</div>
            <div class="v">{{ number_format($totals->return_cost_total ?? 0, 2) }}</div>
            <div class="hint">Qty × cost price</div>
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
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Sale #</th>
                            <th>Method</th>
                            <th class="text-right">Refund</th>
                            <th class="text-right">Return Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $r)
                        @php
                        $refund = (float)($r->refund_amount ?? 0);
                        $rcost = (float)($r->return_cost_total ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                            <td><b>{{ $r->user->name ?? '-' }}</b></td>
                            <td>{{ $r->sale_id ?? ($r->sale->id ?? '-') }}</td>
                            <td><b>{{ strtoupper($r->method ?? '-') }}</b></td>
                            <td class="text-right"><b>{{ number_format($refund,2) }}</b></td>
                            <td class="text-right">{{ number_format($rcost,2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No returns found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection