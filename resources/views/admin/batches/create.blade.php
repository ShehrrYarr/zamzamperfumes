@extends('layouts.panel')

@section('title','Add Batch')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:860px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Add Batch</h1>
                <p class="muted">Barcode is auto-generated (00001, 00002...).</p>
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

        <form method="POST" action="{{ route('admin.batches.store') }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px;">Perfume</label>
                <select name="perfume_id" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65); color:white;">
                    <option value="">Select perfume</option>
                    @foreach($perfumes as $p)
                    <option value="{{ $p->id }}" {{ old('perfume_id')==$p->id ? 'selected' : '' }}>
                        {{ $p->name }}{{ $p->brand ? ' — '.$p->brand : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ">Batch No
                        (optional)</label>
                    <input name="batch_no" value="{{ old('batch_no') }}"
                        style="width:100%; padding:12px; border-radius:14px;">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ;">Quantity</label>
                    <input type="number" name="quantity" min="0" required value="{{ old('quantity', 0) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
                </div>
            </div>

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ">Cost Price
                        (optional)</label>
                    <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px;">Sell Price
                        (optional)</label>
                    <input type="number" step="0.01" min="0" name="sell_price" value="{{ old('sell_price') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid">
                </div>
            </div>

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px;">MFG Date
                        (optional)</label>
                    <input type="date" name="mfg_date" value="{{ old('mfg_date') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px;">EXP Date
                        (optional)</label>
                    <input type="date" name="exp_date" value="{{ old('exp_date') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid">
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Create Batch</button>
                <a class="btn btn-ghost" href="{{ route('admin.batches.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection