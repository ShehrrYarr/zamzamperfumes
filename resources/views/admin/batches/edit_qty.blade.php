@extends('layouts.panel')

@section('title','Edit Batch Quantity')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:860px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Edit Quantity</h1>
                <p class="muted">
                    Barcode: <b>{{ $batch->barcode }}</b> —
                    Perfume: <b>{{ $batch->perfume?->name ?? '—' }}</b>
                </p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.batches.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.batches.update_qty', $batch->id) }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf
            @method('PATCH')

            <div class="card"
                style="background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); box-shadow:none;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div>
                        <div class="muted" style="font-size:12px;">Current Qty</div>
                        <div style="font-weight:800; font-size:18px;">{{ (int)$batch->quantity }}</div>
                    </div>
                    <div>
                        <div class="muted" style="font-size:12px;">Sell / Cost</div>
                        <div style="font-weight:800; font-size:18px;">
                            {{ $batch->sell_price ?? '—' }}
                            <span class="muted" style="font-weight:600;"> / {{ $batch->cost_price ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px;">
                    New Quantity
                </label>
                <input type="number" name="quantity" min="0" required
                    value="{{ old('quantity', (int)$batch->quantity) }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid">
                <div class="muted" style="margin-top:6px; font-size:12px;">
                    Set exact quantity. (0 allowed)
                </div>
            </div>

            

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit" onclick="return confirm('Update batch quantity?');">
                    Update Quantity
                </button>
                <a class="btn btn-ghost" href="{{ route('admin.batches.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection