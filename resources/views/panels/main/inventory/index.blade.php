@extends('layouts.panel')

@section('title','Inventory')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Main Shop Inventory</h1>
                <p class="muted">Search by barcode or perfume name/brand/SKU. Print stickers anytime.</p>
            </div>

            <form method="GET" action="{{ route('main.inventory.index') }}"
                style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <input name="q" value="{{ $q }}" placeholder="Search barcode / perfume..."
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white; min-width:260px;">
                <label style="display:flex; gap:8px; align-items:center; color:rgba(255,255,255,0.8);">
                    <input type="checkbox" name="in_stock" value="1" {{ $onlyInStock ? 'checked' : '' }}>
                    In stock only
                </label>
                <button class="btn" type="submit">Filter</button>
            </form>
        </div>

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; color:rgba(255,255,255,0.7);">
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
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <a class="btn btn-ghost" href="javascript:void(0)"
                                onclick="openBatchPrint('{{ route('main.batches.print', $b->id) }}')">Print</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; color:rgba(255,255,255,0.65);">No inventory found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection