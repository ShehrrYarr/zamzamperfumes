@extends('layouts.panel')

@section('title','Create Transfer')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
  <div class="col-12 card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
      <div>
        <h1 class="h1">Create Branch Transfer</h1>
        <p class="muted">Create a secret code to transfer batches to another branch.</p>
      </div>
      <a class="btn btn-ghost" href="{{ route('branch.transfers.index') }}">← Back</a>
    </div>

    @if(session('success'))
      <div style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
        {{ session('error') }}
      </div>
    @endif

    @if ($errors->any())
      <div style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
        <ul style="margin:0; padding-left:18px;">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('branch.transfers.store') }}" style="margin-top:14px;">
      @csrf

      <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
        <div style="min-width:260px;">
          <div class="muted" style="margin-bottom:6px;">To Branch</div>
          <select name="to_shop_id" required
            style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(15,20,35,0.65);  width:100%;">
            <option value="">Select branch</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
          </select>
        </div>

        <button class="btn" type="submit">Create Transfer Code</button>
      </div>

      <div style="height:12px;"></div>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:900px;">
          <thead>
            <tr style="text-align:left; ">
              <th style="padding:10px; border-bottom:1px  ">Select</th>
              <th style="padding:10px; border-bottom:1px  ">Barcode</th>
              <th style="padding:10px; border-bottom:1px  ">Perfume</th>
              <th style="padding:10px; border-bottom:1px  ">Available</th>
              <th style="padding:10px; border-bottom:1px  ">Qty to Transfer</th>
              <th style="padding:10px; border-bottom:1px  ">Cost</th>
              <th style="padding:10px; border-bottom:1px  ">Sell</th>
            </tr>
          </thead>
          <tbody>
            @foreach($batches as $i => $bt)
              <tr>
                <td style="padding:10px; border-bottom:1px  ">
                  <input type="checkbox" class="pick" data-row="{{ $i }}" style="transform:scale(1.1);">
                </td>
                <td style="padding:10px; border-bottom:1px   font-weight:700;">
                  {{ $bt->barcode }}
                </td>
                <td style="padding:10px; border-bottom:1px  ">
                  {{ $bt->perfume?->name ?? '—' }}
                </td>
                <td style="padding:10px; border-bottom:1px  ">
                  {{ (int)$bt->quantity }}
                </td>
                <td style="padding:10px; border-bottom:1px  ">
                  <input type="hidden" name="items[{{ $i }}][batch_id]" value="{{ $bt->id }}" disabled>
                  <input type="number" name="items[{{ $i }}][quantity]"
                    min="1" max="{{ (int)$bt->quantity }}"
                    value="1"
                    style="padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06);  "
                    disabled>
                </td>
                <td style="padding:10px; border-bottom:1px  ">
                  {{ number_format((float)$bt->cost_price, 2) }}
                </td>
                <td style="padding:10px; border-bottom:1px  ">
                  {{ number_format((float)$bt->sell_price, 2) }}
                </td>
              </tr>
            @endforeach
            @if($batches->count() === 0)
              <tr><td colspan="7" style="padding:12px;">No stock available.</td></tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="muted" style="margin-top:10px;">
        Tip: tick batches you want to transfer, then set quantities.
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    document.querySelectorAll('.pick').forEach(cb => {
      cb.addEventListener('change', () => {
        const row = cb.getAttribute('data-row');
        const idInput = document.querySelector(`input[name="items[${row}][batch_id]"]`);
        const qtyInput = document.querySelector(`input[name="items[${row}][quantity]"]`);
        if(!idInput || !qtyInput) return;

        const on = cb.checked;
        idInput.disabled = !on;
        qtyInput.disabled = !on;
        if(on){
          if(!qtyInput.value || Number(qtyInput.value) < 1) qtyInput.value = 1;
        } else {
          qtyInput.value = 1;
        }
      });
    });
  })();
</script>
@endsection