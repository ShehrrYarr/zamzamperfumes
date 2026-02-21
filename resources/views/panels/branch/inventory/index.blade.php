@extends('layouts.panel')

@section('title','Inventory')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Inventory — {{ $branch->name }}</h1>
                <p class="muted">Your branch batches (read-only).</p>
            </div>
        </div>

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Barcode</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Perfume</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Qty</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Sell</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Cost</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">
                            {{ $b->barcode }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->perfume?->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->quantity }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->sell_price ??
                            '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->cost_price ??
                            '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <a class="btn btn-ghost" href="javascript:void(0)"
                                onclick="openBatchPrint('{{ route('branch.batches.print', $b->id) }}')">
                                Print
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; ">No batches in inventory yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection