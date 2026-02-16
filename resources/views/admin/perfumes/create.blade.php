@extends('layouts.panel')

@section('title','Add Perfume')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:780px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Add Perfume</h1>
                <p class="muted">Create a perfume product.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.perfumes.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; ">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.perfumes.store') }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ">Name</label>
                    <input name="name" required value="{{ old('name') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ">Brand</label>
                    <input name="brand" value="{{ old('brand') }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
                </div>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; ">SKU (optional)</label>
                <input name="sku" value="{{ old('sku') }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; ">Description</label>
                <textarea name="description" rows="4"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid ">{{ old('description') }}</textarea>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Save</button>
                <a class="btn btn-ghost" href="{{ route('admin.perfumes.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection