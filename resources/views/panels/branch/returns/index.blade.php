@extends('layouts.panel')

@section('title','Add Bank')
@section('panel_name','Branch Panel')

@section('content')
<div style="padding:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Returns</h2>
            <div style="color:#6b7280;font-size:13px;">Full + Partial returns ({{ $shop->name }})</div>
        </div>
        <a href="{{ route('branch.dashboard') }}" class="btn btn-primary">← Back</a>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body">
            <form method="GET" style="display:grid;grid-template-columns: repeat(6, 1fr);gap:10px;align-items:end;">
                <div>
                    <label>Sale ID</label>
                    <input name="sale_id" value="{{ request('sale_id') }}" class="form-control" placeholder="e.g. 12">
                </div>
                <div>
                    <label>Method</label>
                    <select name="method" class="form-control">
                        <option value="">All</option>
                        <option value="counter" @selected(request('method')==='counter' )>Counter</option>
                        <option value="bank" @selected(request('method')==='bank' )>Bank</option>
                    </select>
                </div>
                <div>
                    <label>From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div>
                    <label>To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div>
                    <label>Cashier</label>
                    <input name="cashier" value="{{ request('cashier') }}" class="form-control" placeholder="name">
                </div>
                <div>
                    <label>Customer</label>
                    <input name="customer" value="{{ request('customer') }}" class="form-control"
                        placeholder="name / phone">
                </div>

                <div style="grid-column:1 / -1; display:flex; gap:10px;">
                    <button class="btn btn-success">Filter</button>
                    <a class="btn btn-secondary" href="{{ route('main.returns.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:12px;">
        <div class="card-body" style="overflow:auto;">
            <table class="table table-striped" style="min-width:1100px;">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Sale #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Method</th>
                        <th>Bank</th>
                        <th class="text-end">Refund</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $r)
                    <tr>
                        <td><b>#{{ $r->id }}</b></td>
                        <td>#{{ $r->sale_id }}</td>
                        <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            {{ $r->sale?->customer_name ?: 'Walk-in' }}
                            @if($r->sale?->customer_phone)
                            <div style="font-size:12px;color:#6b7280;">{{ $r->sale->customer_phone }}</div>
                            @endif
                        </td>
                        <td>{{ $r->user?->name }}</td>
                        <td><b>{{ strtoupper($r->method) }}</b></td>
                        <td>{{ $r->method === 'bank' ? ($r->bank?->name ?? '—') : '—' }}</td>
                        <td class="text-end"><b>{{ number_format($r->refund_amount,2) }}</b></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="toggleDetails({{ $r->id }})">
                                View Items ({{ $r->items->count() }})
                            </button>
                        </td>
                    </tr>

                    <tr id="details-{{ $r->id }}" style="display:none;background:#f8fafc;">
                        <td colspan="9">
                            <div style="padding:10px;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Barcode</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Unit</th>
                                            <th class="text-end">Line Refund</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($r->items as $it)
                                        <tr>
                                            <td>{{ $it->saleItem?->item_name ?? '—' }}</td>
                                            <td>{{ $it->saleItem?->barcode ?? '—' }}</td>
                                            <td class="text-end">{{ $it->quantity }}</td>
                                            <td class="text-end">{{ number_format($it->unit_price,2) }}</td>
                                            <td class="text-end"><b>{{ number_format($it->line_refund,2) }}</b></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">No returns found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:10px;">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDetails(id){
  const row = document.getElementById('details-' + id);
  if(!row) return;
  row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>
@endsection