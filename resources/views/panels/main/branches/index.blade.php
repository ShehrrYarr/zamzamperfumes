@extends('layouts.panel')

@section('title', 'Branches')
@section('panel_name', 'Main Shop Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <h1 class="h1">Branches</h1>
        <p class="muted">Read-only view of all branches and their status.</p>

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left; color:rgba(255,255,255,0.7);">
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Name</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Code</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Address</th>
                        <th style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.10);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $b)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->name }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->code }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">{{ $b->address ?? '—'
                            }}</td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $b->is_active ? 'Active' : 'Disabled' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding:12px; color:rgba(255,255,255,0.65);">No branches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection