@extends('layouts.panel')

@section('title', 'Add Main Staff')
@section('panel_name', 'Admin Panel')

@section('content')
<div class="grid">
    <div class="col-12 card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div>
                <h1 class="h1">Add Staff — {{ $shop->name }}</h1>
                <p class="muted">Creates a staff login for the main shop.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.mainshop.staff.index') }}">← Back</a>
        </div>

        @if ($errors->any())
        <div
            style="margin-top:14px; padding:12px; border-radius:14px; background: rgba(255,0,90,0.10); border:1px solid rgba(255,0,90,0.25);">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.mainshop.staff.store') }}"
            style="margin-top:14px; display:grid; gap:12px; max-width:560px;">
            @csrf

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Name</label>
                <input name="name" value="{{ old('name') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>

            <div>
                <label style="display:block; margin-bottom:6px; color:rgba(255,255,255,0.75);">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    style="width:100%; padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); color:white;">
            </div>
            <div class="card" style="margin-top:12px;">
                <div class="h1" style="font-size:16px;">Salary Settings</div>
                <p class="muted">Enter Monthly salary (recommended). Daily/Hourly will be auto-calculated.</p>
            
                <div class="grid" style="margin-top:10px;">
                    <div class="col-6">
                        <label class="muted">Monthly Salary</label>
                        <input type="number" step="0.01" name="monthly_salary"
                            value="{{ old('monthly_salary', $staff->monthly_salary ?? '') }}" class="form-control"
                            >
                    </div>
            
                    <div class="col-6">
                        <label class="muted">Work Hours / Day</label>
                        <input type="number" name="work_hours_per_day"
                            value="{{ old('work_hours_per_day', $staff->work_hours_per_day ?? 10) }}" class="form-control" min="1"
                            max="24">
                    </div>
            
                    <div class="col-6">
                        <label class="muted">Daily Salary (auto)</label>
                        <input type="number" step="0.01" name="daily_salary"
                            value="{{ old('daily_salary', $staff->daily_salary ?? '') }}" class="form-control" readonly>
                    </div>
            
                    <div class="col-6">
                        <label class="muted">Hourly Salary (auto)</label>
                        <input type="number" step="0.01" name="hourly_salary"
                            value="{{ old('hourly_salary', $staff->hourly_salary ?? '') }}" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn" type="submit">Create Staff</button>
                <a class="btn btn-ghost" href="{{ route('admin.mainshop.staff.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
    (function(){
    const monthly = document.querySelector('input[name="monthly_salary"]');
    const hours = document.querySelector('input[name="work_hours_per_day"]');
    const daily = document.querySelector('input[name="daily_salary"]');
    const hourly = document.querySelector('input[name="hourly_salary"]');

    function calc(){
      const m = parseFloat(monthly?.value || '0');
      const h = parseInt(hours?.value || '10', 10) || 10;

      if (m > 0) {
        const d = m / 30;
        const hr = d / h;
        daily.value = d.toFixed(2);
        hourly.value = hr.toFixed(2);
      } else {
        daily.value = '';
        hourly.value = '';
      }
    }

    monthly?.addEventListener('input', calc);
    hours?.addEventListener('input', calc);
    calc();
  })();
</script>
@endsection