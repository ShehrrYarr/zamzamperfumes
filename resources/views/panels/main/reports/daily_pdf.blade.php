<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daily Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .title {
            font-size: 18px;
            font-weight: 800;
        }

        .sub {
            color: #64748b;
            margin-top: 2px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .kpi {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .kpi .box {
            flex: 1;
            min-width: 160px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
        }

        .k {
            color: #64748b;
            font-weight: 700;
            font-size: 11px;
        }

        .v {
            font-size: 16px;
            font-weight: 800;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 6px;
            text-align: left;
        }

        th {
            background: #f8fafc;
            font-weight: 800;
            font-size: 11px;
            color: #334155;
        }

        .right {
            text-align: right;
        }

        .green {
            color: #16a34a;
            font-weight: 800;
        }

        .red {
            color: #dc2626;
            font-weight: 800;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            margin: 3px 6px 0 0;
        }

        .muted {
            color: #64748b;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <div class="title">Daily Report</div>
            <div class="sub">{{ $mainShop->name }} — Date: {{ $date }}</div>
        </div>
        <div class="sub">Generated: {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    <div class="card">
        <div class="kpi">
            <div class="box">
                <div class="k">Counter (Before Expenses)</div>
                <div class="v">{{ number_format($netTotals['counter_before_expense'] ?? 0, 2) }}</div>
                <div class="muted">From payments today (sales + refunds)</div>
            </div>

            <div class="box">
                <div class="k">Expenses (Counter)</div>
                <div class="v">{{ number_format($netTotals['expenses'] ?? 0, 2) }}</div>
                <div class="muted">Reduces counter cash</div>
            </div>

            <div class="box">
                <div class="k">Counter (After Expenses)</div>
                <div class="v">{{ number_format($netTotals['counter_after_expense'] ?? 0, 2) }}</div>
                <div class="muted">CounterBefore - Expenses</div>
            </div>

            <div class="box">
                <div class="k">Bank (Net)</div>
                <div class="v">{{ number_format($netTotals['bank'] ?? 0, 2) }}</div>
                <div class="muted">From payments today</div>
            </div>

            <div class="box">
                <div class="k">Total (After Expenses)</div>
                <div class="v">{{ number_format($netTotals['total_after_expense'] ?? 0, 2) }}</div>
                <div class="muted">CounterAfter + Bank</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="kpi">
            <div class="box">
                <div class="k">Refunds Today</div>
                <div class="v">{{ number_format($refundTotals['total'] ?? 0, 2) }}</div>
                <div class="muted">Counter: {{ number_format($refundTotals['counter'] ?? 0, 2) }} | Bank: {{
                    number_format($refundTotals['bank'] ?? 0, 2) }}</div>
            </div>
            <div class="box">
                <div class="k">Batches Added Today</div>
                <div class="v">{{ number_format($batchTotals->count ?? 0) }}</div>
                <div class="muted">Qty: {{ number_format($batchTotals->qty ?? 0) }} | Cost: {{
                    number_format($batchTotals->cost ?? 0, 2) }}</div>
            </div>
            <div class="box">
                <div class="k">Sales Created Today</div>
                <div class="v">{{ number_format($salesTotals->count ?? 0) }}</div>
                <div class="muted">Gross sales: {{ number_format($salesTotals->gross_sales ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="k" style="margin-bottom:6px;">Top Sold Perfumes</div>
        @if(($topPerfumes ?? collect())->count() === 0)
        <div class="muted">No items sold today.</div>
        @else
        @foreach($topPerfumes as $p)
        <span class="badge">{{ $p->name }} <b>({{ (int)$p->qty }})</b></span>
        @endforeach
        @endif
    </div>

    <div class="card">
        <div class="k" style="margin-bottom:6px;">Payments Today (Sales + Refunds)</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Sale</th>
                    <th>Method</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ optional($p->paid_at)->format('H:i') }}</td>
                    <td>#{{ $p->sale_id }}</td>
                    <td>{{ strtoupper($p->method ?? '-') }}</td>
                    <td class="right">
                        <span class="{{ (float)$p->amount < 0 ? 'red' : 'green' }}">
                            {{ number_format((float)$p->amount, 2) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No payments today.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="k" style="margin-bottom:6px;">Expenses Today</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Description</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($e->created_at)->format('H:i') }}</td>
                    <td>{{ $e->title ?? $e->note ?? $e->description ?? '—' }}</td>
                    <td class="right"><span class="red">{{ number_format((float)$e->amount, 2) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">No expenses today.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>

</html>