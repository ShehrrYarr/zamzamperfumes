@extends('layouts.panel')

@section('title','Branch QR')
@section('panel_name','Branch Panel')

@section('content')
<div class="card">
    <div class="h1">Branch Attendance QR</div>
    <p class="muted">Staff will scan this QR to check-in / check-out.</p>

    <div style="margin-top:16px; display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
        <div class="card" style="padding:18px; max-width:360px;">
            {!! QrCode::size(280)->generate(route('staff.scan', $shop->qr_token)) !!}
            <div class="muted" style="margin-top:10px; font-size:12px;">
                {{ route('staff.scan', $shop->qr_token) }}
            </div>
        </div>

        <div style="flex:1; min-width:260px;">
            <div class="card" style="padding:18px;">
                <b>Shop:</b> {{ $shop->name }} <br>
                <b>Type:</b> {{ $shop->type }} <br>
                <b>QR Token:</b> <span class="muted">{{ \Illuminate\Support\Str::limit($shop->qr_token, 18, '...')
                    }}</span>
                <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:12px 0;">
                <p class="muted" style="margin:0;">
                    Print this and place at the entrance. Staff must be logged in to scan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection