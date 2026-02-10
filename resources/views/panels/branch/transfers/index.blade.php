@extends('layouts.panel')

@section('title','Transfers')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Transfer History</h1>
                <p class="muted">Transfers received by {{ $branch->name }}.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('branch.transfers.claim_form') }}">Claim Transfer</a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('branch.transfers.index') }}"
            style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div>
                <div class="muted" style="margin-bottom:6px;">Status</div>
                <select name="status"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); color:white;">
                    @foreach(['all','pending','claimed','cancelled'] as $st)
                    <option value="{{ $st }}" {{ ($status ?? 'all' )===$st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">From</div>
                <input type="date" name="from" value="{{ $from ?? '' }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">To</div>
                <input type="date" name="to" value="{{ $to ?? '' }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <button class="btn" type="submit">Apply</button>
            <a class="btn btn-ghost" href="{{ route('branch.transfers.index') }}">Reset</a>
        </form>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; color:rgba(255,255,255,0.7);">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Code</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">From</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Item</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Qty</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Created</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                    @php
                    $item = $t->items->first();
                    $batch = $item?->batch;
                    $perfume = $batch?->perfume;
                    @endphp
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">{{
                            $t->code }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $t->fromShop?->name ?? 'Main Shop' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $perfume?->name ?? '—' }}
                            @if($batch?->barcode)
                            <span class="muted"> ({{ $batch->barcode }})</span>
                            @endif
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $item?->quantity ??
                            '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{
                            optional($t->created_at)->format('Y-m-d H:i') }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $t->status }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; color:rgba(255,255,255,0.65);">No transfers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection