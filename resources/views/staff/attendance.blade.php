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
            <p class="p">Scan the branch QR to check-in. Scan again to check-out.</p>

            @php
            $status = $attendance?->status ?? 'none';
            $badgeClass = 'b-warn';
            $badgeText = 'No Record';

            if($attendance){
            if($attendance->check_in_at && !$attendance->check_out_at){
            $badgeText = 'Checked In';
            $badgeClass = 'b-warn';
            } elseif($attendance->check_in_at && $attendance->check_out_at){
            $badgeText = 'Completed';
            $badgeClass = 'b-good';
            } elseif($attendance->status === 'absent'){
            $badgeText = 'Absent';
            $badgeClass = 'b-bad';
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

            <div class="grid">
                <div class="box">
                    <div class="k">Check-in</div>
                    <div class="v">{{ $attendance?->check_in_at ? $attendance->check_in_at->format('h:i A') : '—' }}
                    </div>
                </div>
                <div class="box">
                    <div class="k">Check-out</div>
                    <div class="v">{{ $attendance?->check_out_at ? $attendance->check_out_at->format('h:i A') : '—' }}
                    </div>
                </div>

                <div class="box">
                    <div class="k">Worked</div>
                    <div class="v">
                        @php
                        $mins = (int)($attendance?->worked_minutes ?? 0);
                        $h = intdiv($mins, 60);
                        $m = $mins % 60;
                        @endphp
                        {{ $mins ? "{$h}h {$m}m" : '—' }}
                    </div>
                </div>
                <div class="box">
                    <div class="k">Earned Today</div>
                    <div class="v">{{ $attendance ? number_format($attendance->earned_amount, 2) : '—' }}</div>
                </div>
            </div>

            @if($message)
            <div class="msg">{{ $message }}</div>
            @endif

            <div class="hint">
                If you refresh immediately, it won’t double mark (protected).
            </div>
        </div>
    </div>
</body>

</html>