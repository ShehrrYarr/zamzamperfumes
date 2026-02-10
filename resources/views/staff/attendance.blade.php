<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance</title>

    <style>
        :root {
            --bg1: #0b1220;
            --bg2: #020617;
            --text: rgba(255, 255, 255, .92);
            --muted: rgba(255, 255, 255, .70);
            --line: rgba(255, 255, 255, .12);
            --card: rgba(255, 255, 255, .07);
            --shadow: 0 18px 50px rgba(0, 0, 0, .45);
            --r: 18px;
            --good: #22c55e;
            --warn: #f59e0b;
            --bad: #ef4444;
            --blue: #38bdf8;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
            color: var(--text);
            background:
                radial-gradient(1100px 520px at 15% 0%, rgba(56, 189, 248, .18), transparent 60%),
                radial-gradient(1000px 520px at 95% 10%, rgba(34, 197, 94, .12), transparent 60%),
                linear-gradient(180deg, var(--bg1), var(--bg2));
            min-height: 100vh;
            padding: 16px;
        }

        .wrap {
            max-width: 520px;
            margin: 0 auto
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .chip {
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .05);
            font-size: 12px;
            color: var(--muted);
        }

        .btn {
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .06);
            color: var(--text);
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:active {
            transform: scale(.98)
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--r);
            box-shadow: var(--shadow);
            padding: 16px;
            animation: pop .25s ease both;
        }

        @keyframes pop {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.99)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .h1 {
            font-size: 18px;
            font-weight: 900;
            margin: 0 0 4px
        }

        .p {
            margin: 0;
            color: var(--muted);
            font-size: 13px
        }

        .status {
            margin-top: 12px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(2, 6, 23, .35);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
        }

        .b-good {
            border-color: rgba(34, 197, 94, .35);
            background: rgba(34, 197, 94, .12)
        }

        .b-warn {
            border-color: rgba(245, 158, 11, .35);
            background: rgba(245, 158, 11, .12)
        }

        .b-bad {
            border-color: rgba(239, 68, 68, .35);
            background: rgba(239, 68, 68, .12)
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 12px
        }

        .box {
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(2, 6, 23, .30);
            border-radius: 16px;
            padding: 12px;
        }

        .k {
            font-size: 12px;
            color: var(--muted)
        }

        .v {
            font-size: 14px;
            font-weight: 900;
            margin-top: 4px
        }

        .msg {
            margin-top: 12px;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid rgba(56, 189, 248, .28);
            background: rgba(56, 189, 248, .12);
            color: rgba(255, 255, 255, .92);
            font-size: 13px;
        }

        .hint {
            margin-top: 12px;
            font-size: 12px;
            color: var(--muted);
            text-align: center;
        }

        .session-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .session-pill {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .06);
            color: rgba(255, 255, 255, .85);
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="top">
            <a class="btn" href="{{ route('staff.dashboard') }}">← Back</a>
            <span class="chip">Shop: {{ $shop->name }}</span>
        </div>

        <div class="card">
            <div class="h1">Attendance</div>
            <p class="p">Scan the shop QR to check-in. Scan again to check-out. (Multiple sessions supported)</p>

            @php
            $badgeText = 'No Record';
            $badgeClass = 'b-warn';

            if($attendance){
            $open = $attendance->sessions?->firstWhere('check_out_at', null);
            if($attendance->status === 'absent'){
            $badgeText = 'Absent';
            $badgeClass = 'b-bad';
            } elseif($open){
            $badgeText = 'Checked In';
            $badgeClass = 'b-warn';
            } elseif(($attendance->worked_minutes ?? 0) > 0){
            $badgeText = 'Completed';
            $badgeClass = 'b-good';
            } else {
            $badgeText = 'Partial';
            $badgeClass = 'b-warn';
            }
            }
            @endphp

            <div class="status">
                <div>
                    <div class="k">Today</div>
                    <div class="v">{{ now()->format('Y-m-d') }}</div>
                </div>
                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
            </div>

            @php
            $minsTotal = (int)($attendance?->worked_minutes ?? 0);
            $hTotal = intdiv($minsTotal, 60);
            $mTotal = $minsTotal % 60;
            @endphp

            <div class="grid">
                <div class="box">
                    <div class="k">Worked Today</div>
                    <div class="v">{{ $minsTotal ? "{$hTotal}h {$mTotal}m" : '—' }}</div>
                </div>
                <div class="box">
                    <div class="k">Earned Today</div>
                    <div class="v">{{ $attendance ? number_format($attendance->earned_amount, 2) : '—' }}</div>
                </div>

                <div class="box">
                    <div class="k">Daily Salary</div>
                    <div class="v">{{ $attendance ? number_format($attendance->daily_salary_snapshot, 2) : '—' }}</div>
                </div>
                <div class="box">
                    <div class="k">Hourly Salary (10h/day)</div>
                    <div class="v">{{ $attendance ? number_format($attendance->hourly_salary_snapshot, 2) : '—' }}</div>
                </div>
            </div>

            @if($message)
            <div class="msg">{{ $message }}</div>
            @endif

            @php
            $sessions = $attendance?->sessions ?? collect();
            @endphp

            @if($sessions->count())
            <div class="card" style="margin-top:12px; padding:14px;">
                <div class="h1" style="font-size:16px;">Today Sessions</div>

                <div style="display:grid; gap:10px; margin-top:10px;">
                    @foreach($sessions as $i => $s)
                    @php
                    $mins = (int)($s->worked_minutes ?? 0);
                    $h = intdiv($mins, 60);
                    $m = $mins % 60;
                    $open = !$s->check_out_at;
                    @endphp

                    <div class="box">
                        <div class="session-row">
                            <div>
                                <div class="k">Session {{ $i+1 }}</div>
                                <div class="v" style="font-size:13px;">
                                    IN: {{ $s->check_in_at?->format('h:i A') ?? '—' }}
                                    &nbsp; | &nbsp;
                                    OUT: {{ $s->check_out_at?->format('h:i A') ?? '—' }}
                                </div>
                                <div class="k" style="margin-top:6px;">
                                    Worked: <b>{{ $mins ? "{$h}h {$m}m" : '—' }}</b>
                                </div>
                            </div>

                            <div>
                                <span class="session-pill" style="border-color: {{ $open ? 'rgba(245,158,11,.35)' : 'rgba(34,197,94,.35)' }};
                      background: {{ $open ? 'rgba(245,158,11,.12)' : 'rgba(34,197,94,.12)' }};">
                                    {{ $open ? 'OPEN' : 'CLOSED' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="hint">
                Tip: If you refresh immediately, it won’t double mark (protected).
            </div>
        </div>
    </div>
</body>

</html>