@extends('layouts.panel')

@section('title', 'Main Shop Staff')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Main Shop Staff — {{ $shop->name }}</h1>
                <p class="muted">Manage staff accounts for the main shop.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.mainshop.show') }}">← Back</a>
                <a class="btn" href="{{ route('admin.mainshop.staff.create') }}">+ Add Staff</a>
            </div>
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
                    <tr style="text-align:left; color:rgba(255,255,255,0.7);">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Name</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Email</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Status</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $s->name }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $s->email }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $s->is_active ?
                            'Active' : 'Disabled' }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <form method="POST" action="{{ route('admin.staff.toggle', $s->id) }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">
                                    {{ $s->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding:12px; color:rgba(255,255,255,0.65);">No staff yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection