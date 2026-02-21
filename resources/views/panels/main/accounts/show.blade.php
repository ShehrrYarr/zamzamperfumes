@extends('layouts.panel')

@section('title','Account Ledger')
@section('panel_name','Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
            <div>
                <h1 class="h1">{{ $account->name }}</h1>
                <p class="muted">Main shop can add debit/credit entries for its own accounts.</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('main.accounts.index') }}">← Back</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger mt-3 mb-0">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="GET" class="row mt-3">
            <div class="col-md-3 mb-2">
                <label><b>From</b></label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
                <label><b>To</b></label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-md-4 mb-2 d-flex align-items-end">
                <button class="btn btn-success mr-2">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('main.accounts.show', $account->id) }}">Reset</a>
            </div>
        </form>

        <div class="row mt-2">
            <div class="col-md-3">
                <div class="card stat stat-green">
                    <div class="k">Credit</div>
                    <div class="v">{{ number_format($totals->credit ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat stat-amber">
                    <div class="k">Debit</div>
                    <div class="v">{{ number_format($totals->debit ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat stat-blue">
                    <div class="k">Balance</div>
                    <div class="v">{{ number_format($balance ?? 0, 2)}}</div>
                    <div class="hint">credit - debit</div>
                </div>
            </div>
        </div>

        <hr>

        <h5 class="mb-2"><b>Add Entry</b></h5>
        <form method="POST" action="{{ route('main.accounts.entries.store', $account->id) }}" class="row">
            @csrf
            <div class="col-md-2 mb-2">
                <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}"
                    class="form-control" required>
            </div>

            <div class="col-md-2 mb-2">
                <select name="type" class="form-control" required>
                    <option value="credit" @selected(old('type')==='credit' )>Credit</option>
                    <option value="debit" @selected(old('type')==='debit' )>Debit</option>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}"
                    class="form-control" placeholder="Amount" required>
            </div>

            <div class="col-md-4 mb-2">
                <input name="description" value="{{ old('description') }}" class="form-control"
                    placeholder="Description (optional)">
            </div>

            <div class="col-md-2 mb-2">
                <button class="btn btn-primary btn-block">Add</button>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>By</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                    <tr>
                        <td>{{ optional($e->entry_date)->format('Y-m-d') }}</td>
                        <td>{{ $e->description ?? '—' }}</td>
                        <td>{{ $e->user?->name ?? '—' }}</td>
                        <td class="text-right">{{ $e->debit > 0 ? number_format($e->debit,2) : '—' }}</td>
                        <td class="text-right">{{ $e->credit > 0 ? number_format($e->credit,2) : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $entries->links() }}</div>
    </div>
</div>
@endsection