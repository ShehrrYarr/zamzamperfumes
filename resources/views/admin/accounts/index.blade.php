@extends('layouts.panel')

@section('title','Accounts')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
            <div>
                <h1 class="h1">Accounts</h1>
                <p class="muted">Create accounts for Main Shop & Branches.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('admin.accounts.create') }}">+ Create Account</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success mt-3 mb-0">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row mt-3">
            <div class="col-md-4 mb-2">
                <label><b>Shop</b></label>
                <select name="shop_id" class="form-control">
                    <option value="">All</option>
                    @foreach($shops as $s)
                    <option value="{{ $s->id }}" @selected((string)request('shop_id')===(string)$s->id)>
                        {{ strtoupper($s->type) }} — {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-2">
                <label><b>Active</b></label>
                <select name="active" class="form-control">
                    <option value="">All</option>
                    <option value="1" @selected(request('active')==='1' )>Active</option>
                    <option value="0" @selected(request('active')==='0' )>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 mb-2 d-flex align-items-end">
                <button class="btn btn-success mr-2">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.accounts.index') }}">Reset</a>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Shop</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $a)
                    <tr>
                        <td>{{ $a->id }}</td>
                        <td>{{ strtoupper($a->shop?->type ?? '') }} — {{ $a->shop?->name }}</td>
                        <td><b>{{ $a->name }}</b></td>
                        <td>{{ $a->code ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $a->is_active ? 'success' : 'secondary' }}">
                                {{ $a->is_active ? 'active' : 'inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.accounts.show', $a->id) }}">Open</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $accounts->links() }}</div>
    </div>
</div>
@endsection