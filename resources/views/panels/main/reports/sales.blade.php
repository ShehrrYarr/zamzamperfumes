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
                    <p class="muted">Main Shop sales with revenue, cost and profit.</p>
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
                            <th>#</th>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th>Payment</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Cost</th>
                            <th class="text-right">Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $s)
                        @php
                        $rev = (float)($s->grand_total ?? 0);
                        $cost = (float)($s->cost_total ?? 0);
                        $p = $rev - $cost;
                        $pay = strtoupper($s->payment_method ?? '-');
                        $bankName = $s->bank->name ?? null;
                        @endphp
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td>{{ optional($s->created_at)->format('Y-m-d') }}</td>
                            <td><b>{{ $s->user->name ?? '-' }}</b></td>
                            <td>
                                <b>{{ $pay }}</b>
                                @if($pay === 'BANK' && $bankName)
                                <div style="font-size:12px;"><b>{{ $bankName }}</b></div>
                                @endif
                            </td>
                            <td class="text-right"><b>{{ number_format($rev,2) }}</b></td>
                            <td class="text-right">{{ number_format($cost,2) }}</td>
                            <td class="text-right"><b>{{ number_format($p,2) }}</b></td>
                            <td>{{ $s->status ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No sales found.</td>
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

<script>
    (function(){
    var payment = document.getElementById('paymentMethod');
    var bank = document.getElementById('bankId');

    function syncBank(){
      // Only enable bank dropdown if payment method is bank, otherwise clear + disable
      if (payment.value === 'bank' || payment.value === '') {
        bank.disabled = (payment.value !== 'bank'); // disable unless bank
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