@extends('layouts.panel')

@section('title','Attendance QR')
@section('panel_name','QR Code')

@section('content')
<div class="card">
    <div class="h1">Attendance QR</div>
    <p class="muted">
        Staff will scan this QR to check-in / check-out.
        <br>
        <b>Shop:</b> {{ $shop->name }} ({{ strtoupper($shop->type) }})
    </p>

    <div style="margin-top:16px; display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
        <div class="card" style="padding:18px; max-width:360px;">
            @php
            $slot = (int) floor(now()->timestamp / 300); // 300 sec = 5 minutes
            $sig = hash_hmac('sha256', $shop->qr_token.'|'.$slot, config('app.key'));
            $url = route('staff.scan', ['token' => $shop->qr_token]) . '?slot='.$slot.'&sig='.$sig;
            @endphp
            
            {!! QrCode::size(280)->generate($url) !!}
            <div class="muted" style="margin-top:10px; font-size:12px; word-break:break-all;">
                {{ $url }}
            </div>
            <div class="muted" style="margin-top:10px; font-size:12px; word-break:break-all;">
                {{ route('staff.scan', $shop->qr_token) }}
            </div>
        </div>

        <div style="flex:1; min-width:260px;">
            <div class="card" style="padding:18px;">
                <div><b>Instructions</b></div>
                <ul style="margin:10px 0 0; color:rgba(255,255,255,.75);">
                    <li>Staff must be logged in on mobile.</li>
                    <li>First scan = check-in, second scan = check-out.</li>
                    <li>Print and place this QR at the shop entrance.</li>
                </ul>

                <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:12px 0;">

                <div class="muted" style="font-size:12px;">
                    Token: {{ \Illuminate\Support\Str::limit($shop->qr_token, 18, '...') }}
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // refresh slightly after 5 minutes to ensure next slot
  setTimeout(() => location.reload(), 305000);
</script>
@endsection