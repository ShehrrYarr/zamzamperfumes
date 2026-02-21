@extends('layouts.panel')

@section('title','Transfers')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <div>
                    <h1 class="h1">Transfer History</h1>
                    <p class="muted">Transfers received by <b>{{ $branch->name }}</b>.</p>
                </div>
                <a class="btn btn-primary" href="{{ route('branch.transfers.claim_form') }}">Claim Transfer</a>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('branch.transfers.index') }}" class="row mt-3">
                <div class="col-md-4 mb-2">
                    <label class="mb-1"><b>Status</b></label>
                    <select name="status" class="form-control">
                        @foreach(['all','pending','claimed','cancelled'] as $st)
                        <option value="{{ $st }}" {{ ($status ?? 'all' )===$st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Apply</button>
                </div>

                <div class="col-12 mt-1">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('branch.transfers.index') }}">Reset</a>
                </div>
            </form>

            @if(session('success'))
            <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger mt-3 mb-0">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger mt-3 mb-0">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>From</th>
                            <th>Items</th>
                            <th class="text-right">Total Qty</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $t)
                        @php
                        $items = $t->items ?? collect();
                        $totalQty = (int) $items->sum('quantity');
                        @endphp
                        <tr>
                            <td style="font-weight:900;">{{ $t->code }}</td>
                            <td>{{ $t->fromShop?->name ?? 'Main Shop' }}</td>

                            {{-- Items --}}
                            <td>
                                @if($items->count())
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    @foreach($items as $it)
                                    @php
                                    $batch = $it->batch;
                                    $perf = $batch?->perfume;
                                    @endphp
                                    <div
                                        style="padding:8px 10px;border-radius:12px;border:1px solid rgba(15,23,42,0.08);background:rgba(255,255,255,0.72);">
                                        <div
                                            style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;">
                                            <div>
                                                <b>{{ $perf?->name ?? '—' }}</b>
                                                @if($batch?->barcode)
                                                <span class="muted" style="font-size:12px;"> ({{ $batch->barcode
                                                    }})</span>
                                                @endif
                                            </div>
                                            <div style="font-weight:900;">Qty: {{ (int)($it->quantity ?? 0) }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <span class="muted">—</span>
                                @endif
                            </td>

                            <td class="text-right"><b>{{ number_format($totalQty) }}</b></td>

                            <td>{{ optional($t->created_at)->format('Y-m-d H:i') }}</td>

                            <td>
                                <b>{{ ucfirst($t->status) }}</b>
                                @if($t->status === 'claimed' && $t->claimed_at)
                                <div class="muted" style="font-size:11px;">
                                    claimed: {{ optional($t->claimed_at)->format('Y-m-d H:i') }}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No transfers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection