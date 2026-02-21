@extends('layouts.panel')

@section('title','Accounts')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Accounts</h1>
                <p class="muted">Branch: {{ $branch->name ?? '—' }} ({{ strtoupper($branch->code ?? '') }})</p>
            </div>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            {{ session('error') }}
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

        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:820px;">
                <thead>
                    <tr style="text-align:left; ">
                        <th style="padding:10px; border-bottom:1px   ">#</th>
                        <th style="padding:10px; border-bottom:1px   ">Account</th>
                        <th style="padding:10px; border-bottom:1px   ">Shop</th>
                        <th style="padding:10px; border-bottom:1px   ">Created</th>
                        <th style="padding:10px; border-bottom:1px   ">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $a)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:800;">
                            #{{ $a->id }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <b>{{ $a->name ?? ('Account #'.$a->id) }}</b>
                            @if(!empty($a->type))
                            <div class="muted" style="font-size:12px;">Type: {{ $a->type }}</div>
                            @endif
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $branch->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ optional($a->created_at)->format('Y-m-d H:i') }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            <a class="btn btn-ghost" href="{{ route('branch.accounts.show', $a->id) }}">
                                Open
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:12px; ">No accounts found for this
                            branch.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="muted" style="margin-top:10px; font-size:12px;">
            Note: Accounts are created by Admin. Branch can add debit/credit entries.
        </div>
    </div>
</div>
@endsection