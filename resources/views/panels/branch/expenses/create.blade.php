@extends('layouts.panel')

@section('title','Add Expense')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12">
        <div class="card" style="max-width:900px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <div>
                    <h1 class="h1">Add Expense</h1>
                    <p class="muted">Record any expense made by this branch.</p>
                </div>
                <a class="btn btn-outline-secondary" href="{{ route('branch.expenses.index') }}">← Back</a>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form class="mt-3" method="POST" action="{{ route('branch.expenses.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="mb-1"><b>Date</b></label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}"
                            class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="mb-1"><b>Category</b> <span class="muted">(optional)</span></label>
                        <input type="text" name="category" value="{{ old('category') }}" class="form-control"
                            placeholder="e.g. Rent, Bills, Fuel">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="mb-1"><b>Amount (Rs)</b></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}"
                            class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="mb-1"><b>Title</b></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required
                            placeholder="Short title e.g. Electricity bill">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="mb-1"><b>Notes</b> <span class="muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Any details...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Save Expense</button>
                    <a class="btn btn-outline-secondary" href="{{ route('branch.expenses.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection