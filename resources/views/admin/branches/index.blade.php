@extends('layouts.panel')

@section('title', 'Branches')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Branches</h1>
                <p class="muted">Create and manage branch shops and their logins.</p>
            </div>
            <a class="btn" href="{{ route('admin.branches.create') }}">+ Create Branch</a>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Name</th>
                        {{-- <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">email</th> --}}
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Code</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Status</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->name }}</td>
                        {{-- <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->user->email }}</td> --}}
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->code }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->is_active ? 'Active' : 'Disabled' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; gap:10px; flex-wrap:wrap;">
                            <a class="btn btn-ghost" href="{{ route('admin.branches.edit', $b->id) }}">Edit</a>
                        
                            <form method="POST" action="{{ route('admin.branches.toggle', $b->id) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">
                                    {{ $b->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.branches.reset_login_password', $b->id) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit" onclick="return confirm('Reset branch login password?');">
                                    Reset Login Password
                                </button>
                            </form>

                            <a class="btn btn-ghost" href="{{ route('admin.branches.staff.index', $b->id) }}">Staff</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding:12px; color:rgba(255,255,255,0.65);">No branches yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection