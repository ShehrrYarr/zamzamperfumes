@extends('layouts.panel')

@section('title','Perfumes')
@section('panel_name','Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Perfumes</h1>
                <p class="muted">Manage perfumes (Admin & Main Shop only).</p>
            </div>
            <a class="btn" href="{{ route('admin.perfumes.create') }}">+ Add Perfume</a>
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
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Brand</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">SKU</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perfumes as $p)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $p->name }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $p->brand ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $p->sku ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);"><a class="btn btn-ghost" href="{{ route('admin.perfumes.edit', $p->id) }}">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="padding:12px; color:rgba(255,255,255,0.65);">No perfumes yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection