@extends('layouts.panel')

@section('title','Expenses')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">

    <div class="col-12">
        <div class="card stat stat-purple" style="padding:22px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <div>
                    <div class="k">Branch</div>
                    <div class="v" style="font-size:28px;line-height:1.1;">Expenses</div>
                    <div class="hint">Add and view branch expenses with date filters.</div>
                </div>
                <a class="btn btn-primary" href="{{ route('branch.expenses.create') }}">+ Add Expense</a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card stat stat-amber">
            <div class="k">Total (Filtered)</div>
            <div class="v">Rs {{ number_format((float)($total ?? 0), 2) }}</div>
            <div class="hint">Sum of visible results</div>
        </div>
    </div>

    <div class="col-12">
        <div class="card" style="padding:18px;">
            <div class="h1" style="margin:0;">Filters</div>
            <p class="muted" style="margin:6px 0 0;">Search by title/category/notes.</p>

            <form class="row mt-3" method="GET" action="{{ route('branch.expenses.index') }}">
                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>From</b></label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="mb-1"><b>To</b></label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="mb-1"><b>Search</b></label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control"
                        placeholder="Title / Category / Notes">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Apply</button>
                </div>
                <div class="col-12">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('branch.expenses.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="card" style="padding:18px;">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Notes</th>
                            <th class="text-right">Amount</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $e)
                        <tr>
                            <td>{{ optional($e->expense_date)->format('Y-m-d') }}</td>
                            <td>{{ $e->category ?? '—' }}</td>
                            <td style="font-weight:700;">{{ $e->title }}</td>
                            <td class="muted">{{ $e->notes ? \Illuminate\Support\Str::limit($e->notes, 60) : '—' }}</td>
                            <td class="text-right" style="font-weight:900;">Rs {{ number_format((float)$e->amount, 2) }}
                            </td>
                            <td class="muted">{{ $e->user?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No expenses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>

</div>
@endsection