@extends('layouts.panel')

@section('title','Create Transfer')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card" style="max-width:1000px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <div>
                    <h1 class="h1">Create Transfer</h1>
                    <p class="muted">Transfer multiple batches at once. You’ll get one secret code for the whole
                        transfer.</p>
                </div>
                <a class="btn btn-outline-secondary" href="{{ route('main.transfers.index') }}">← Back</a>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger mt-3 mb-0">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('main.transfers.store') }}" class="mt-3" id="transferForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="mb-1"><b>Branch</b></label>
                        <select name="to_shop_id" class="form-control" required>
                            <option value="">Select branch</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('to_shop_id')==$b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                    <div>
                        <b>Transfer Items</b>
                        <div class="muted" style="font-size:12px;">Add one or more batches and quantities.</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">+ Add Batch</button>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:65%;">Batch (Main inventory)</th>
                                <th class="text-right" style="width:20%;">Qty</th>
                                <th class="text-right" style="width:15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex" style="gap:10px;flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Create Transfer Code</button>
                    <a class="btn btn-outline-secondary" href="{{ route('main.transfers.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function(){
  var batches = @json(
$batches->map(function($bt){
return [
'id' => $bt->id,
'label' =>
($bt->perfume->name ?? '—')
. ' — Barcode: '
. ($bt->barcode ?? '-')
. ' — Qty: '
. ($bt->quantity ?? 0)
];
})->values()
);

  var body = document.getElementById('itemsBody');
  var addBtn = document.getElementById('addRowBtn');
  var rowIndex = 0;

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, function(m){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
  }

  function buildOptions(){
    var html = '<option value="">Select batch</option>';
    for(var i=0;i<batches.length;i++){
      html += '<option value="'+batches[i].id+'">'+escapeHtml(batches[i].label)+'</option>';
    }
    return html;
  }

  function addRow(selectedId, qty){
    var idx = rowIndex++;
    var tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="items[${idx}][batch_id]" class="form-control" required>
          ${buildOptions()}
        </select>
      </td>
      <td>
        <input type="number" min="1" name="items[${idx}][quantity]" class="form-control text-right" value="${qty || 1}" required>
      </td>
      <td class="text-right">
        <button type="button" class="btn btn-outline-secondary btn-sm removeRow">Remove</button>
      </td>
    `;
    body.appendChild(tr);

    if(selectedId){
      tr.querySelector('select').value = selectedId;
    }

    tr.querySelector('.removeRow').addEventListener('click', function(){
      tr.remove();
      if(body.querySelectorAll('tr').length === 0){
        addRow(null, 1);
      }
    });
  }

  addBtn.addEventListener('click', function(){
    addRow(null, 1);
  });

  // Start with one row
  addRow(null, 1);
})();
</script>
@endsection