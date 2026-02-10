@extends('layouts.panel')

@section('title','Salary Report')
@section('panel_name','Admin Panel')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
        <div>
            <div class="h1">Monthly Salary Report (All Shops)</div>
            <p class="muted">Month: {{ $month }} ({{ $start }} to {{ $end }})</p>
        </div>
        <a class="btn" href="{{ route('admin.dashboard') }}">← Back</a>
    </div>

    <div class="card" style="margin-top:12px;">
        <form method="GET" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;align-items:end;">
            <div>
                <label class="muted">Month</label>
                <input type="month" name="month" value="{{ request('month', $month) }}" class="form-control">
            </div>
            <div>
                <label class="muted">Shop</label>
                <select name="shop_id" class="form-control">
                    <option value="">All shops</option>
                    @foreach($shops as $s)
                    <option value="{{ $s->id }}" @selected(request('shop_id')==$s->id)>
                        {{ strtoupper($s->type) }} — {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="muted">Staff Name</label>
                <input name="staff" value="{{ request('staff') }}" class="form-control" placeholder="search staff">
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn">Filter</button>
                <a class="btn btn-ghost" href="{{ route('admin.reports.salaries') }}">Reset</a>
            </div>
        </form>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
        <div class="pill">Staff: <b>{{ $totals['staff_count'] }}</b></div>
        <div class="pill">Worked Hours: <b>{{ $totals['worked_hours_total'] }}</b></div>
        <div class="pill">Present Days: <b>{{ $totals['present_days_total'] }}</b></div>
        <div class="pill">Absent Days: <b>{{ $totals['absent_days_total'] }}</b></div>
        <div class="pill">Expected: <b>{{ number_format($totals['expected_total'],2) }}</b></div>
        <div class="pill">Earned: <b>{{ number_format($totals['earned_total'],2) }}</b></div>
        <div class="pill">Deduction: <b>{{ number_format($totals['deduction_total'],2) }}</b></div>
    </div>

    <div style="margin-top:12px; overflow:auto;">
        <table class="table table-striped" style="min-width:1200px;">
            <thead>
                <tr>
                    <th>Shop</th>
                    <th>Staff</th>
                    <th class="text-end">Present</th>
                    <th class="text-end">Absent</th>
                    <th class="text-end">Partial</th>
                    <th class="text-end">Worked (hrs)</th>
                    <th class="text-end">Daily</th>
                    <th class="text-end">Hourly</th>
                    <th class="text-end">Expected</th>
                    <th class="text-end">Earned</th>
                    <th class="text-end">Deduction</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr>
                    <td>{{ strtoupper($r->shop_type) }} — {{ $r->shop_name }}</td>
                    <td><b>{{ $r->staff_name }}</b></td>
                    <td class="text-end">{{ (int)$r->present_days }}</td>
                    <td class="text-end">{{ (int)$r->absent_days }}</td>
                    <td class="text-end">{{ (int)$r->partial_days }}</td>
                    <td class="text-end">{{ number_format($r->worked_hours,2) }}</td>
                    <td class="text-end">{{ number_format((float)$r->daily_rate,2) }}</td>
                    <td class="text-end">{{ number_format((float)$r->hourly_rate,2) }}</td>
                    <td class="text-end"><b>{{ number_format((float)$r->expected_monthly,2) }}</b></td>
                    <td class="text-end"><b>{{ number_format((float)$r->earned_total,2) }}</b></td>
                    <td class="text-end">
                        <b style="color: {{ ((float)$r->deduction) > 0 ? '#f59e0b' : '#22c55e' }};">
                            {{ number_format((float)$r->deduction,2) }}
                        </b>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">No staff found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection