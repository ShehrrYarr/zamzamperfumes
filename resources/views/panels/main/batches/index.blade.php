@extends('layouts.panel')

@section('title','Batches')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Batches (Main Shop Inventory)</h1>
                <p class="muted">Auto barcode starts from 00001.</p>
            </div>
            <a class="btn" href="{{ route('admin.batches.create') }}">+ Add Batch</a>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Barcode</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Perfume</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Qty</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Sell</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Cost</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Print Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">{{
                            $b->barcode }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->perfume?->name
                            ?? '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->quantity }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->sell_price ??
                            '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->cost_price ??
                            '—' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);"><a class="btn btn-ghost" href="javascript:void(0)"
                            onclick="openBatchPrint('{{ route('main.batches.print', $b->id) }}')">
                            Print
                        </a></td>
                       
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:12px; color:rgba(255,255,255,0.65);">No batches yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection