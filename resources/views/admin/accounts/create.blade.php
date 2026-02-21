@extends('layouts.panel')

@section('title','Create Account')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:820px;">
        <h1 class="h1">Create Account</h1>
        <p class="muted">Create an account for any shop (main/branch).</p>

        @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.accounts.store') }}" class="mt-3">
            @csrf

            <div class="form-group">
                <label><b>Shop</b></label>
                <select name="shop_id" class="form-control" required>
                    <option value="">Select</option>
                    @foreach($shops as $s)
                    <option value="{{ $s->id }}" @selected((string)old('shop_id')===(string)$s->id)>
                        {{ strtoupper($s->type) }} — {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><b>Account Name</b></label>
                <input name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label><b>Code (optional)</b></label>
                <input name="code" class="form-control" value="{{ old('code') }}">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="act" checked>
                <label class="form-check-label" for="act"><b>Active</b></label>
            </div>

            <button class="btn btn-primary">Create</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.accounts.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection