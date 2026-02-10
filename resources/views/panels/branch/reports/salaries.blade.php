@extends('layouts.panel')

@section('title','Salary Report')
@section('panel_name','Branch')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
        <div>
            <div class="h1">Monthly Salary Report</div>
            <p class="muted">Month: {{ $month }} ({{ $start }} to {{ $end }})</p>
        </div>
        <a class="btn" href="{{ route('branch.dashboard') }}">← Back</a>
    </div>

    <div class="card" style="margin-top:12px;">
        <form method="GET" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;align-items:end;">
            <div>
                <label class="muted">Month</label>
                <input type="month" name="month" value="{{ request('month', $month) }}" class="form-control">
            </div>
            <div>
                <label class="muted">Staff Name</label>
                <input name="staff" value="{{ request('staff') }}" class="form-control" placeholder="search staff">
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn">Filter</button>
                <a class="btn btn-ghost" href="{{ route('branch.reports.salaries') }}">Reset</a>
            </div>
        </form>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
        <div class="pill">Staff: <b>{{ $totals['staff_count'] }}</b></div>
        <div class="pill">Worked Hours: <b>{{ $totals['worked_hours_total'] }}</b></div>
        <div class="pill">Present: <b>{{ $totals['present_days_total'] }}</b></div>
        <div class="pill">Absent: <b>{{ $totals['absent_days_total'] }}</b></div>
        <div class="pill">Expected: <b>{{ number_format($totals['expected_total'],2) }}</b></div>
        <div class="pill">Earned: <b>{{ number_format($totals['earned_total'],2) }}</b></div>
        <div class="pill">Deduction: <b>{{ number_format($totals['deduction_total'],2) }}</b></div>
    </div>

    <div style="margin-top:12px; overflow:auto;">
        <table class="table table-striped" style="min-width:1100px;">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th class="text-end">Present</th>
                    <th class="text-end">Absent</th>
                    <th class="text-end">Partial</th>
                    <th class="text-end">Worked (hrs)</th>
                    <th class="text-end">Expected</th>
                    <th class="text-end">Earned</th>
                    <th class="text-end">Deduction</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr>
                    <td><b>{{ $r->staff_name }}</b></td>
                    <td class="text-end">{{ (int)$r->present_days }}</td>
                    <td class="text-end">{{ (int)$r->absent_days }}</td>
                    <td class="text-end">{{ (int)$r->partial_days }}</td>
                    <td class="text-end">{{ number_format($r->worked_hours,2) }}</td>
                    <td class="text-end"><b>{{ number_format((float)$r->expected_monthly,2) }}</b></td>
                    <td class="text-end"><b>{{ number_format((float)$r->earned_total,2) }}</b></td>
                    <td class="text-end"><b>{{ number_format((float)$r->deduction,2) }}</b></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No staff found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection