@extends('layouts.panel')

@section('title','Edit Expense')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">

    <div class="col-12">
        <div class="card stat stat-green" style="padding:22px;">
            <div class="k">Edit Expense</div>
            <div class="v" style="font-size:26px;">
                {{ $expense->title }}
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card" style="padding:22px;">

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.expenses.update', $expense->id) }}">
                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label><b>Shop</b></label>
                        <select name="shop_id" class="form-control" required>
                            @foreach($shops as $s)
                            <option value="{{ $s->id }}" {{ $expense->shop_id == $s->id ? 'selected' : '' }}>
                                {{ $s->type === 'main' ? 'Main' : 'Branch' }} — {{ $s->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label><b>Date</b></label>
                        <input type="date" name="expense_date" class="form-control"
                            value="{{ optional($expense->expense_date)->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label><b>Amount</b></label>
                        <input type="number" step="0.01" name="amount" class="form-control"
                            value="{{ $expense->amount }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label><b>Category</b></label>
                        <input type="text" name="category" class="form-control" value="{{ $expense->category }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label><b>Title</b></label>
                        <input type="text" name="title" class="form-control" value="{{ $expense->title }}" required>
                    </div>

                    <div class="col-12 mb-3">
                        <label><b>Notes</b></label>
                        <textarea name="notes" rows="4" class="form-control">{{ $expense->notes }}</textarea>
                    </div>

                </div>

                <button class="btn btn-success">
                    Update Expense
                </button>

                <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                    Back
                </a>

            </form>
        </div>
    </div>

</div>
@endsection