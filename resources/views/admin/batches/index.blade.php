@extends('layouts.panel')

@section('title','Batches')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Batches (Main Shop Inventory)</h1>
                <p class="muted">Search by barcode. Auto barcode starts from 00001.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <a class="btn" href="{{ route('admin.batches.create') }}">+ Add Batch</a>
            </div>
        </div>

        {{-- Search --}}
        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input id="barcodeSearch" type="text" value="{{ $q ?? '' }}" placeholder="Search barcode… (e.g. 00012)"
                style="width:min(420px,100%); padding:12px 14px; border-radius:14px; border:1px solid "
                autocomplete="off">
            <div id="searchHint" class="muted" style="font-size:13px;"></div>
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
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Print</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Edit Qty</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Edit Sell Price</th>
                    </tr>
                </thead>

                <tbody id="batchesBody">
                    @forelse($batches as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">
                            {{ $b->barcode }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->perfume?->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->quantity }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->sell_price ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->cost_price ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <a class="btn btn-ghost" href="javascript:void(0)"
                                onclick="openBatchPrint('{{ route('admin.batches.print', $b->id) }}')">Print</a>
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            @if(auth()->user()->role === 'admin')
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.batches.edit_qty', $b->id) }}">
                                Edit
                            </a>
                            @else
                            <span class="muted">—</span>
                            @endif
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            @if(auth()->user()->role === 'admin')
                            <a class="btn btn-sm btn-warn" target="_blank" href="{{ route('admin.batches.edit_sell_price', $b->id) }}">
                                Edit Price
                            </a>
                            @else
                            <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding:12px; ">No batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function(){
    const input = document.getElementById('barcodeSearch');
    const body  = document.getElementById('batchesBody');
    const hint  = document.getElementById('searchHint');

    const baseUrl = @json(route('admin.batches.index'));

    let timer = null;
    let lastQuery = input.value || '';

    function escHtml(s){
        return String(s ?? '')
          .replaceAll('&','&amp;')
          .replaceAll('<','&lt;')
          .replaceAll('>','&gt;')
          .replaceAll('"','&quot;')
          .replaceAll("'","&#039;");
    }

    function renderRows(rows){
        if(!rows || rows.length === 0){
            body.innerHTML = `<tr><td colspan="8" style="padding:12px; color:rgba(255,255,255,0.65);">No batches found.</td></tr>`;
            return;
        }

        body.innerHTML = rows.map(r => {
            return `
              <tr>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:700;">${escHtml(r.barcode)}</td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">${escHtml(r.perfume)}</td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">${escHtml(r.quantity)}</td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">${escHtml(r.sell_price ?? '—')}</td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">${escHtml(r.cost_price ?? '—')}</td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    <a class="btn btn-ghost" href="javascript:void(0)" onclick="openBatchPrint('${escHtml(r.print_url)}')">Print</a>
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    @if(auth()->user()->role === 'admin')
                        <a class="btn btn-sm btn-primary" href="${escHtml(r.edit_url)}">Edit</a>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                    @if(auth()->user()->role === 'admin')
                    <a class="btn btn-sm btn-warn" target="_blank" href="${escHtml(r.edit_sell_price_url)}">Edit Price</a>
                    @else
                    <span class="muted">—</span>
                    @endif
                </td>
              </tr>
            `;
        }).join('');
    }

    async function fetchRows(q){
        hint.textContent = q ? 'Searching…' : '';
        const url = baseUrl + '?q=' + encodeURIComponent(q);

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if(!res.ok){
            hint.textContent = 'Search failed';
            return;
        }
        const data = await res.json();
        if(!data.ok){
            hint.textContent = 'Search failed';
            return;
        }
        renderRows(data.rows);
        hint.textContent = q ? (`Showing results for "${q}"`) : '';
    }

    input.addEventListener('input', () => {
        const q = (input.value || '').trim();
        if(q === lastQuery) return;

        clearTimeout(timer);
        timer = setTimeout(() => {
            lastQuery = q;
            fetchRows(q);
        }, 250);
    });
})();
</script>
@endsection