@extends('layouts.panel')

@section('title','Create Transfer')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:900px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Create Transfer</h1>
                <p class="muted">Select branch, batch, and quantity. You’ll get a secret code.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('main.transfers.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('main.transfers.store') }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch</label>
                <select name="to_shop_id" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); color:white;">
                    <option value="">Select branch</option>
                    @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ old('to_shop_id')==$b->id ? 'selected' : '' }}>
                        {{ $b->name }} ({{ $b->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Batch (Main
                    inventory)</label>
                <select name="batch_id" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); color:white;">
                    <option value="">Select batch</option>
                    @foreach($batches as $bt)
                    <option value="{{ $bt->id }}" {{ old('batch_id')==$bt->id ? 'selected' : '' }}>
                        {{ $bt->perfume?->name ?? '—' }} — Barcode: {{ $bt->barcode }} — Qty: {{ $bt->quantity }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Quantity to
                    transfer</label>
                <input type="number" min="1" name="quantity" required value="{{ old('quantity', 1) }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Create Transfer Code</button>
                <a class="btn btn-ghost" href="{{ route('main.transfers.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection