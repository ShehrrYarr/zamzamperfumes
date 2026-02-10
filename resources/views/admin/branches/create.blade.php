@extends('layouts.panel')

@section('title', 'Create Branch')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Create Branch</h1>
        <p class="muted">This will create a branch shop + a branch login user (role: branch_shop).</p>

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

        <form method="POST" action="{{ route('admin.branches.store') }}"
            style="margin-top:14px; display:grid; gap:12px; max-width:560px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch Name</label>
                <input name="name" value="{{ old('name') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch Code
                    (unique)</label>
                <input name="code" value="{{ old('code') }}" required placeholder="BR-001"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Address
                    (optional)</label>
                <input name="address" value="{{ old('address') }}"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Branch Login
                    Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Create Branch</button>
                <a class="btn btn-ghost" href="{{ route('admin.branches.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection