@extends('layouts.panel')

@section('title','Edit Perfume')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:780px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Edit Perfume</h1>
                <p class="muted">Update perfume details.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.perfumes.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.perfumes.update', $perfume->id) }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf
            @method('PUT')

            <div class="grid">
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px; ">Name</label>
                    <input name="name" required value="{{ old('name', $perfume->name) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid">
                </div>
                <div class="col-6">
                    <label style="display:block; margin-bottom:6px;">Brand</label>
                    <input name="brand" value="{{ old('brand', $perfume->brand) }}"
                        style="width:100%; padding:12px; border-radius:14px; border:1px solid">
                </div>
            </div>

            <div>
                <label style="display:block; margin-bottom:6px;">SKU</label>
                <input name="sku" value="{{ old('sku', $perfume->sku) }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid ">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; ">Description</label>
                <textarea name="description" rows="4"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid ">{{ old('description', $perfume->description) }}</textarea>
            </div>

            <label style="display:flex; gap:10px; align-items:center; ">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $perfume->is_active) ? 'checked' :
                '' }}>
                Active
            </label>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn btn-ghost" href="{{ route('admin.perfumes.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection