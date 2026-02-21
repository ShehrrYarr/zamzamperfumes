@extends('layouts.panel')

@section('title','Account Ledger')
@section('panel_name','Branch Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h1 class="h1">Account Ledger</h1>
                <p class="muted">
                    Branch: {{ $branch->name ?? '—' }} —
                    Account: <b>{{ $account->name ?? ('Account #'.$account->id) }}</b>
                </p>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('branch.accounts.index') }}">← Back</a>
            </div>
        </div>

        @if(session('success'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(0,255,170,0.10); border:1px solid rgba(0,255,170,0.20);">
            {{ session('success') }}
        </div>
        @endif

        @if(session('warning'))
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(251,191,36,0.14); border:1px solid rgba(251,191,36,0.25);">
            {{ session('warning') }}
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

        {{-- KPI --}}
        <div style="margin-top:14px; display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
            <div class="card stat stat-blue" style="padding:14px; margin:0;">
                <div class="k">Total Credit</div>
                <div class="v">{{ number_format($totals->credit ?? 0, 2) }}</div>
                <div class="hint">Filtered range</div>
            </div>
            <div class="card stat stat-amber" style="padding:14px; margin:0;">
                <div class="k">Total Debit</div>
                <div class="v">{{ number_format($totals->debit ?? 0, 2) }}</div>
                <div class="hint">Filtered range</div>
            </div>
            <div class="card stat {{ ($balance ?? 0) >= 0 ? 'stat-green' : 'stat-purple' }}"
                style="padding:14px; margin:0;">
                <div class="k">Balance</div>
                <div class="v">{{ number_format($balance ?? 0, 2) }}</div>
                <div class="hint">Credit − Debit</div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('branch.accounts.show', $account->id) }}"
            style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div>
                <div class="muted" style="margin-bottom:6px;">From</div>
                <input type="date" name="from" value="{{ request('from') }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06);">
            </div>

            <div>
                <div class="muted" style="margin-bottom:6px;">To</div>
                <input type="date" name="to" value="{{ request('to') }}"
                    style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); ">
            </div>

            <button class="btn" type="submit">Apply</button>
            <a class="btn btn-ghost" href="{{ route('branch.accounts.show', $account->id) }}">Reset</a>
        </form>

        <hr style="border:none;border-top:1px    margin:16px 0;">

        {{-- Add Entry --}}
        <div class="card"
            style="padding:14px; margin:0; background: rgba(255,255,255,0.04); border:1px   ">
            <div style="font-weight:900; margin-bottom:10px;">Add Entry</div>

            <form method="POST" action="{{ route('branch.accounts.entries.store', $account->id) }}"
                style="display:grid; grid-template-columns: 1.2fr 1fr 1fr 2fr auto; gap:10px; align-items:end;">
                @csrf

                <div>
                    <div class="muted" style="margin-bottom:6px;">Date</div>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}"
                        class="form-control">
                </div>

                <div>
                    <div class="muted" style="margin-bottom:6px;">Debit</div>
                    <input type="number" step="0.01" min="0" name="debit" value="{{ old('debit', 0) }}"
                        class="form-control" placeholder="0.00">
                </div>

                <div>
                    <div class="muted" style="margin-bottom:6px;">Credit</div>
                    <input type="number" step="0.01" min="0" name="credit" value="{{ old('credit', 0) }}"
                        class="form-control" placeholder="0.00">
                </div>

                <div>
                    <div class="muted" style="margin-bottom:6px;">Description</div>
                    <input type="text" name="description" value="{{ old('description') }}" class="form-control"
                        placeholder="Optional note…">
                </div>

                <div>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>

            <div class="muted" style="margin-top:8px; font-size:12px;">
                Rule: enter **only one** value per entry (either Debit or Credit).
            </div>
        </div>

        {{-- Entries table --}}
        <div style="margin-top:14px; overflow:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:1000px;">
                <thead>
                    <tr style="text-align:left; ">
                        <th style="padding:10px; border-bottom:1px   ">Date</th>
                        <th style="padding:10px; border-bottom:1px   ">User</th>
                        <th style="padding:10px; border-bottom:1px   ">Debit</th>
                        <th style="padding:10px; border-bottom:1px   ">Credit</th>
                        <th style="padding:10px; border-bottom:1px   ">Description</th>
                        <th style="padding:10px; border-bottom:1px   ">Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $e->entry_date }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $e->user?->name ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ number_format((float)$e->debit, 2) }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ number_format((float)$e->credit, 2) }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ $e->description ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid rgba(255,255,255,0.06);">
                            @if($e->ref_type && $e->ref_id)
                            <span class="muted" style="font-size:12px;">
                                {{ $e->ref_type }} #{{ $e->ref_id }}
                            </span>
                            @else
                            <span class="muted" style="font-size:12px;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:12px; color:rgba(255,255,255,0.65);">No entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            {{ $entries->links() }}
        </div>

    </div>
</div>

<style>
    @media (max-width: 992px) {
        .grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection