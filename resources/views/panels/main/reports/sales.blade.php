@extends('layouts.panel')

@section('title','Sales Report')
@section('panel_name','Main Shop')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
                <div>
                    <div class="h1">Sales Report</div>
                    <p class="muted">Main Shop sales with revenue, cost, profit + items dropdown + receipt.</p>
                </div>
                <a href="{{ route('main.dashboard') }}" class="btn btn-outline-secondary">← Back</a>
            </div>

            <hr>

            <form method="GET" class="row" id="salesFilterForm">
                <div class="col-md-2 mb-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="mb-1"><b>Status</b></label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="completed" @selected(request('status')==='completed' )>Completed</option>
                        <option value="returned" @selected(request('status')==='returned' )>Returned</option>
                        <option value="partial_return" @selected(request('status')==='partial_return' )>Partial Return
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="mb-1"><b>Sale Type</b></label>
                    <select name="sale_type" class="form-control">
                        <option value="">All</option>
                        <option value="customer" @selected(request('sale_type')==='customer' )>Customer</option>
                        <option value="internal_transfer" @selected(request('sale_type')==='internal_transfer' )>Internal Sale</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>Payment</b></label>
                    <select name="payment_method" class="form-control" id="paymentMethod">
                        <option value="">All</option>
                        <option value="counter" @selected(request('payment_method')==='counter' )>Counter</option>
                        <option value="bank" @selected(request('payment_method')==='bank' )>Bank</option>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>Bank</b></label>
                    <select name="bank_id" class="form-control" id="bankId">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" @selected((string)request('bank_id')===(string)$bank->id)>
                            {{ $bank->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <a class="btn btn-outline-secondary btn-block" href="{{ route('main.reports.sales') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Totals --}}
    <div class="col-12">
        <div class="card stat stat-blue">
            <div class="k">Sales Count</div>
            <div class="v">{{ number_format($totals->sales_count ?? 0) }}</div>
            <div class="hint">Filtered sales</div>
        </div>
    </div>
    <div class="col-12">
        <div class="card stat stat-blue">
            <div class="k">Quantity Count</div>
           <div class="v">  {{ number_format((float)($totals->qty_total ?? 0)) }}</div>
            <div class="hint">Sold Quantity</div>
        </div>
    </div>

    

    <div class="col-12">
        <div class="card stat stat-purple">
            <div class="k">Revenue</div>
            <div class="v">{{ number_format($totals->revenue_total ?? 0, 2) }}</div>
            <div class="hint">Grand totals</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Cost</div>
            <div class="v">{{ number_format($totals->cost_total ?? 0, 2) }}</div>
            <div class="hint">Items × cost price</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-green">
            <div class="k">Profit</div>
            <div class="v">{{ number_format($profit ?? 0, 2) }}</div>
            <div class="hint">Revenue - cost</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width:42px;"></th>
                            <th>#</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Payment</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Cost</th>
                            <th class="text-right">Profit</th>
                            <th>Status</th>
                            <th class="text-right" style="width:110px;">Qty</th>
                            <th style="width:110px;">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $s)
                        @php
                        $rev = (float)($s->grand_total ?? 0);
                        $cost = (float)($s->cost_total ?? 0);
                        $p = $rev - $cost;

                        $pay = strtoupper($s->payment_method ?? '-');
                        $bankName = $s->bank_name ?? null;

                        $collapseId = 'saleItems_'.$s->id;
                        @endphp

                        {{-- Main row --}}
                        <tr>
                            <td class="align-middle">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse"
                                    data-target="#{{ $collapseId }}" aria-expanded="false"
                                    aria-controls="{{ $collapseId }}">
                                    +
                                </button>
                            </td>
                            <td class="align-middle"><b>{{ $s->id }}</b></td>
                            <td class="align-middle">{{ optional($s->created_at)->format('Y-m-d') }}</td>
                            <td class="align-middle"><b>{{ $s->user->name ?? '-' }}</b></td>
                            <td class="align-middle">
                                <b>{{ $pay }}</b>
                                @if($pay === 'BANK' && $bankName)
                                <div style="font-size:12px;"><b>{{ $bankName }}</b></div>
                                @endif
                            </td>
                            <td class="text-right align-middle"><b>{{ number_format($rev,2) }}</b></td>
                            <td class="text-right align-middle">{{ number_format($cost,2) }}</td>
                            <td class="text-right align-middle"><b>{{ number_format($p,2) }}</b></td>
                            <td class="align-middle">{{ $s->status ?? '-' }}</td>
                            <td class="text-right"><b>{{ (int)($s->qty_total ?? 0) }}</b></td>
                            <td class="align-middle">
                                <a class="btn btn-sm btn-primary" target="_blank"
                                    href="{{ route('main.pos.receipt', $s->id) }}">
                                    View
                                </a>
                            </td>
                        </tr>

                        {{-- Dropdown/expand row --}}
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="10" style="background: rgba(0,0,0,0.02);">
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <b>Sale #{{ $s->id }} Items</b>
                                            <div class="text-muted" style="font-size:12px;">
                                                {{ $s->customer_name ? 'Customer: '.$s->customer_name : 'Walk-in
                                                Customer' }}
                                                @if($s->customer_phone) — {{ $s->customer_phone }} @endif
                                            </div>
                                        </div>

                                        <span class="badge badge-light">
                                            Lines: {{ $s->items->count() }}
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Barcode</th>
                                                    <th>Item</th>
                                                    <th class="text-right">Unit</th>
                                                    <th class="text-right">Qty</th>
                                                    <th class="text-right">Line Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($s->items as $it)
                                                @php
                                                $unit = (float)($it->unit_price ?? 0);
                                                $qty = (int)($it->quantity ?? 0);
                                                $line = (float)($it->line_total ?? ($unit * $qty));
                                                @endphp
                                                <tr>
                                                    <td><b>{{ $it->barcode }}</b></td>
                                                    <td>{{ $it->item_name }}</td>
                                                    <td class="text-right">{{ number_format($unit,2) }}</td>
                                                    <td class="text-right">{{ $qty }}</td>
                                                    <td class="text-right"><b>{{ number_format($line,2) }}</b></td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3">No items.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<!-- Popper (required for Bootstrap tooltips/collapse positioning) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
    integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
</script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
    integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous">
</script>
<script>
    (function(){
  var payment = document.getElementById('paymentMethod');
  var bank = document.getElementById('bankId');

  function syncBank(){
    if (!payment || !bank) return;

    if (payment.value === 'bank' || payment.value === '') {
      bank.disabled = (payment.value !== 'bank');
      if (payment.value !== 'bank') bank.value = '';
    } else {
      bank.value = '';
      bank.disabled = true;
    }
  }

  if(payment && bank){
    payment.addEventListener('change', syncBank);
    syncBank();
  }
})();
</script>
@endsection