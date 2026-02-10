<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Return Receipt #{{ $sale->id }}</title>

    <style>
        body {
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #f3f4f6;
        }

        .paper {
            width: 80mm;
            max-width: 92mm;
            margin: 16px auto;
            background: #fff;
            color: #111827;
            padding: 10px 10px 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
            border-radius: 8px;
        }

        .center {
            text-align: center
        }

        .muted {
            color: #6b7280;
            font-size: 12px
        }

        .h1 {
            font-weight: 900;
            font-size: 16px;
            margin: 0
        }

        .badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 900;
            font-size: 12px;
        }

        .hr {
            border-top: 1px dashed #d1d5db;
            margin: 10px 0
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px
        }

        .item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 6px;
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 12px;
        }

        .item:last-child {
            border-bottom: none
        }

        .bold {
            font-weight: 900
        }

        .tiny {
            font-size: 11px;
            color: #6b7280
        }

        .btns {
            width: 80mm;
            max-width: 92mm;
            margin: 10px auto 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        button {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff
            }

            .btns {
                display: none
            }

            .paper {
                box-shadow: none;
                margin: 0 auto;
                border-radius: 0
            }
        }
    </style>
</head>

<body>

    <div class="paper">
        <div class="center">
            <div class="h1">{{ $shop->name }}</div>
            <div class="muted">Return Receipt</div>
            <div class="badge">RETURNED</div>
        </div>

        <div class="hr"></div>

        <div class="row"><span>Sale #</span><span class="bold">{{ $sale->id }}</span></div>
        <div class="row"><span>Date</span><span>{{ optional($sale->created_at)->format('Y-m-d') }}</span></div>
        <div class="row"><span>Time</span><span>{{ optional($sale->created_at)->format('H:i') }}</span></div>

        <div class="hr"></div>

        <div class="row"><span>Customer</span><span class="bold">{{ $sale->customer_name ?: 'Walk-in' }}</span></div>
        @if($sale->customer_phone)
        <div class="row"><span>Phone</span><span>{{ $sale->customer_phone }}</span></div>
        @endif

        <div class="hr"></div>

        @foreach($sale->items as $it)
        <div class="item">
            <div>
                <div class="bold">{{ $it->item_name }}</div>
                <div class="tiny">{{ $it->barcode }}</div>
                <div class="tiny">{{ number_format($it->unit_price,2) }} × {{ $it->quantity }}</div>
            </div>
            <div class="bold">{{ number_format($it->line_total,2) }}</div>
        </div>
        @endforeach

        <div class="hr"></div>

        <div class="row"><span>Refund Amount</span><span class="bold">{{ number_format($sale->grand_total,2) }}</span>
        </div>

        @php
        $refund = $sale->payments->sortByDesc('id')->first(); // last payment should be refund
        @endphp
        <div class="row"><span>Refund Method</span><span class="bold">{{ strtoupper($refund?->method ?? '—') }}</span>
        </div>
        @if(($refund?->method ?? '') === 'bank')
        <div class="row"><span>Bank</span><span>{{ $refund?->bank?->name ?? '—' }}</span></div>
        @endif

        <div class="hr"></div>
        <div class="center muted">
            Refund processed successfully.
        </div>
    </div>

    <div class="btns">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

</body>

</html>