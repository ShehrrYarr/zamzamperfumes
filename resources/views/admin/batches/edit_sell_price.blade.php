@extends('layouts.panel')

@section('title','Edit Sell Price')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="padding:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1" style="margin:0;">Edit Sell Price (Global)</h1>
                <p class="muted" style="margin:6px 0 0;">
                    Barcode: <b>{{ $barcode }}</b>
                    <br>
                    This will update sell price for <b>all batches</b> with this barcode in Main + all Branches.
                </p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.batches.index', ['q' => $barcode]) }}">← Back</a>
        </div>

        @if($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; ">
            <b>Fix errors:</b>
            <ul style="margin:8px 0 0;">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="margin-top:16px; max-width:560px;">
            <form method="POST" action="{{ route('admin.batches.update_sell_price', $batch->id) }}">
                @csrf
                @method('PUT')

                <label class="mb-1"><b>New Sell Price</b></label>
                <input name="sell_price" type="number" step="0.01" min="0"
                    value="{{ old('sell_price', $batch->sell_price) }}" class="form-control"
                    style="padding:12px;border-radius:12px;margin-top:6px;" required>

                <div class="muted" style="margin-top:8px;font-size:13px;">
                    Current price shown is from this batch record, but update will apply to <b>all shops</b>.
                </div>

                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Update Price Everywhere</button>
                    <a class="btn btn-ghost" href="{{ route('admin.batches.index', ['q' => $barcode]) }}">Cancel</a>
                </div>
            </form>
        </div>

        <hr style="margin:18px 0; border:none; border-top:1px">

        <div>
            <h3 style="margin:0 0 8px;">Affected Rows</h3>
            <div class="muted" style="margin-bottom:12px;">
                These batch rows (same barcode) will be updated:
            </div>

            <div style="overflow:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:820px;">
                    <thead>
                        <tr style="text-align:left;">
                            <th style="padding:10px; border-bottom:1px  ">Shop</th>
                            <th style="padding:10px; border-bottom:1px  ">Type</th>
                            <th style="padding:10px; border-bottom:1px  ">Qty</th>
                            <th style="padding:10px; border-bottom:1px  ">Sell</th>
                            <th style="padding:10px; border-bottom:1px  ">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $t)
                        <tr>
                            <td style="padding:10px; border-bottom:1px  ">
                                <b>{{ $t->shop?->name ?? '—' }}</b>
                            </td>
                            <td style="padding:10px; border-bottom:1px  ">
                                {{ $t->shop?->type ?? '—' }}
                            </td>
                            <td style="padding:10px; border-bottom:1px  ">
                                {{ (int)$t->quantity }}
                            </td>
                            <td style="padding:10px; border-bottom:1px  ">
                                {{ $t->sell_price ?? '—' }}
                            </td>
                            <td style="padding:10px; border-bottom:1px  ">
                                {{ $t->cost_price ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection