@extends('layouts.panel')

@section('title', 'Add Staff')
@section('panel_name', 'Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Add Staff</h1>
                <p class="muted">Creates a staff login for your branch.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('branch.staff.index') }}">← Back</a>
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

        <form method="POST" action="{{ route('branch.staff.store') }}"
            style="margin-top:14px; display:grid; gap:12px; max-width:560px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Name</label>
                <input name="name" value="{{ old('name') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Create Staff</button>
                <a class="btn btn-ghost" href="{{ route('branch.staff.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection