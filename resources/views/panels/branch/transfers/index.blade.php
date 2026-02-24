@extends('layouts.panel')

@section('title','Transfers')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Branch Transfers</h1>
                <p class="muted">Sent and received transfers for {{ $branch->name }}.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('branch.transfers.create') }}">+ Create Transfer</a>
                <a class="btn btn-ghost" href="{{ route('branch.transfers.claim_form') }}">Claim Transfer</a>
            </div>
        </div>

        <form method="GET" action="{{ route('branch.transfers.index') }}"
            style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div>
                <div class="muted" style="margin-bottom:6px;">Direction</div>
                <select name="direction"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); ">
                    @foreach(['all' => 'All', 'sent' => 'Sent', 'received' => 'Received'] as $k => $lbl)
                    <option value="{{ $k }}" {{ ($direction ?? 'all' )===$k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">Status</div>
                <select name="status"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); ">
                    @foreach(['all','pending','claimed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ ($status ?? 'all' )===$st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">From</div>
                <input type="date" name="from" value="{{ $from ?? '' }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); ">
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">To</div>
                <input type="date" name="to" value="{{ $to ?? '' }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); ">
            </div>

            <button class="btn" type="submit">Apply</button>
            <a class="btn btn-ghost" href="{{ route('branch.transfers.index') }}">Reset</a>
        </form>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65);">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65);">
            {{ session('error') }}
        </div>
        @endif

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:1050px;">
                <thead>
                    <tr style="text-align:left; ">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Code</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">From</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">To</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Items</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Created</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(0, 0, 0, 0.06); font-weight:700;">
                            {{ $t->code }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0, 0, 0, 0.06);">
                            {{ $t->fromShop?->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(9, 9, 9, 0.06);">
                            {{ $t->toShop?->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(0, 0, 0, 0.06);">
                            <details>
                                <summary style="cursor:pointer; user-select:none;">
                                    View items ({{ $t->items->count() }})
                                </summary>
                                <div style="margin-top:8px;">
                                    @foreach($t->items as $it)
                                    <div class="muted" style="margin-bottom:6px;">
                                        <b>{{ $it->batch?->perfume?->name ?? '—' }}</b>
                                        — {{ $it->batch?->barcode ?? '—' }}
                                        — Qty: <b >{{ (int)$it->quantity }}</b>
                                    </div>
                                    @endforeach
                                </div>
                            </details>
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(5, 5, 5, 0.06);">
                            {{ optional($t->created_at)->format('Y-m-d H:i') }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(2, 2, 2, 0.06);">
                            {{ $t->status }}
                            @if($t->status === 'claimed' && $t->claimed_at)
                            <div class="muted" style="font-size:11px;">claimed: {{
                                optional($t->claimed_at)->format('Y-m-d H:i') }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; ">No transfers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:10px;">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection