@extends('layouts.panel')

@section('title', 'Edit Branch')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Edit Branch</h1>
                <p class="muted">Update branch info and (optionally) branch login email.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.branches.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.branches.update', $shop->id) }}"
            style="margin-top:14px; display:grid; gap:12px; max-width:560px;">
            @csrf
            @method('PUT')

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch Name</label>
                <input name="name" value="{{ old('name', $shop->name) }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch Code</label>
                <input name="code" value="{{ old('code', $shop->code) }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Address</label>
                <input name="address" value="{{ old('address', $shop->address) }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div
                style="margin-top:4px; padding:12px; border-radius:14px; background: rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.10);">
                <div style="font-weight:600; margin-bottom:6px;">Branch Login (role: branch_shop)</div>

                <div class="muted" style="margin-bottom:10px;">
                    Current Email: <span style="color:rgba(255,255,255,0.9)">{{ $branchLogin?->email ?? 'Not found'
                        }}</span>
                </div>

                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">
                    Update Login Email (optional)
                </label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="new-branch-login@email.com"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Save Changes</button>
                <a class="btn btn-ghost" href="{{ route('admin.branches.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection