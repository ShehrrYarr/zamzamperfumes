@extends('layouts.panel')

@section('title','Claim Transfer')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card" style="max-width:720px;">
        <h1 class="h1">Claim Transfer</h1>
        <p class="muted">Enter the secret code given by Main Shop to add batch into your inventory.</p>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('branch.transfers.claim') }}"
            style="margin-top:14px; display:grid; gap:12px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Secret Code</label>
                <input name="code" required placeholder="e.g. A9K2P0X1QZ"
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <button class="btn" type="submit">Claim</button>
        </form>
    </div>
</div>
@endsection