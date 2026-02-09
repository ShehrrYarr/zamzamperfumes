<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <!-- Google Fonts: Poppins for English, Noto Nastaliq Urdu for Urdu -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap"
        rel="stylesheet">
    <style>
        html,
        body {
            background: #fff;
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .receipt {
            width: 72mm;
            margin: 16px auto;
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        @media print {

            html,
            body {
                background: #fff;
                margin: 0 !important;
                padding: 0 !important;
                width: 80mm !important;
                min-height: unset !important;
                display: block;
            }

            .receipt {
                width: 72mm !important;
                margin: 0 auto !important;
            }

            .no-print {
                display: none !important;
            }
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold !important;
        }

        .shop-logo {
            font-size: 20px;
            margin-bottom: 2px;
            letter-spacing: 1px;
        }

        .main-label {
            font-size: 14px;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4px;
        }

        .receipt-table th,
        .receipt-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 13px;
            word-break: break-word;
            text-align: center;
        }

        .receipt-table th {
            background: #fafafa;
        }

        .receipt-table td:first-child,
        .receipt-table th:first-child {
            text-align: left;
        }

        .totals {
            font-size: 14px;
            font-weight: bold;
        }

        .total-label {
            text-align: left;
        }

        .total-value {
            text-align: right;
            width: 70px;
        }

        .policy {
            font-size: 10.5px;
            line-height: 1.5;
            margin-bottom: 6px;
            margin-top: 6px;
        }

        .urdu {
            font-family: 'Noto Nastaliq Urdu', 'Noto Sans Arabic', serif;
            font-size: 13px;
            font-weight: bold;
            direction: rtl;
            text-align: right;
            letter-spacing: 0.2px;
            line-height: 1.65;
        }

        .address-note {
            font-size: 10px;
            text-align: center;
            margin-top: 4px;
        }

        /* Note / Comment box */
        .note-box {
            border: 1px dashed #000;
            border-radius: 4px;
            padding: 6px;
            background: #fff;
            margin: 6px 0;
        }

        .note-text {
            font-size: 12px;
            line-height: 1.35;
            white-space: pre-wrap;
            /* just in case */
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="center shop-logo bold">Zam Zam Perfumes</div>
        <div class="center main-label">Bahawalpur Branch</div>
        <div class="center" style="font-size:12px; margin-bottom: 2px;">
            <span class="bold">Ph: 0300-7852629</span>
        </div>
        <div class="divider"></div>

        <table style="width:100%;margin-bottom:0;">
            <tr>
                <td class="bold">Invoice#</td>
                <td>{{ $sale->id }}</td>
            </tr>
            <tr>
                <td class="bold">Date</td>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td class="bold">Sold By</td>
                <td>{{ $sale->user->name ?? '-' }}</td>
            </tr>
            @if($sale->vendor)
            <tr>
                <td class="bold">Vendor</td>
                <td>{{ $sale->vendor->name }}</td>
            </tr>
            <tr>
                <td class="bold">Mobile</td>
                <td>+{{ $sale->vendor->mobile_no ?? '-' }}</td>
            </tr>
            @elseif($sale->customer_name)
            <tr>
                <td class="bold">Customer</td>
                <td>{{ $sale->customer_name }}</td>
            </tr>
            <tr>
                <td class="bold">Mobile</td>
                <td>+{{ $sale->customer_mobile ?? '-' }}</td>
            </tr>
            @endif
        </table>

        {{-- NOTE / COMMENT SECTION --}}
        @if(!empty($sale->comment))
        <div class="note-box">
            <div class="bold" style="margin-bottom:2px;">Note / Comment</div>
            <div class="note-text">{!! nl2br(e($sale->comment)) !!}</div>
        </div>
        @endif

        <div class="divider"></div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="width:44%;">Item</th>
                    <th style="width:14%;">Qty</th>
                    <th style="width:21%;">Price</th>
                    <th style="width:21%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td style="text-align:left;">{{ $item->batch->accessory->name ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price_per_unit,0) }}</td>
                    <td>{{ number_format($item->subtotal,0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        @php
        // Subtotal BEFORE discount
        $grossTotal = $sale->items->sum('subtotal');
        $discount = (float) ($sale->discount_amount ?? 0);
        $netTotal = max($grossTotal - $discount, 0);

        // Only vendors can pay on invoice
        $isVendorSale = !empty($sale->vendor);
        $paid = $isVendorSale ? (float) ($sale->pay_amount ?? 0) : 0.0;
        if ($paid < 0) $paid=0.0; if ($paid> $netTotal) $paid = $netTotal;

            $remaining = $isVendorSale ? max($netTotal - $paid, 0) : 0.0;
            @endphp

            <table style="width:100%;">
                <tr>
                    <td class="totals total-label" colspan="3">SUBTOTAL</td>
                    <td class="totals total-value">Rs. {{ number_format($grossTotal, 0) }}</td>
                </tr>
                @if($discount > 0)
                <tr>
                    <td class="totals total-label" colspan="3">DISCOUNT</td>
                    <td class="totals total-value">- Rs. {{ number_format($discount, 0) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="totals total-label" colspan="3">TOTAL</td>
                    <td class="totals total-value">Rs. {{ number_format($netTotal, 0) }}</td>
                </tr>
                @if($isVendorSale)
                <tr>
                    <td class="totals total-label" colspan="3">PAID</td>
                    <td class="totals total-value">Rs. {{ number_format($paid, 0) }}</td>
                </tr>
                <tr>
                    <td class="totals total-label" colspan="3">REMAINING</td>
                    <td class="totals total-value">
                        @if($remaining == 0 && $netTotal > 0)
                        PAID IN FULL
                        @else
                        Rs. {{ number_format($remaining, 0) }}
                        @endif
                    </td>
                </tr>
                @endif
            </table>

            <div class="divider"></div>
            {{-- <div class="policy">
                <div class="bold center" style="font-size:11.5px; margin-bottom:2px;">Return & Exchange Policy:</div>
            </div> --}}
            {{-- <div class="urdu">
                موبائل اسیسری موقع پہ چیک کریں •<br>
                وارنٹی والی چیز کی کمپنی ذمہ دار ہوگی •<br>
                استعمال شدہ اور کھلی ہوئی چیز کی واپسی&nbsp;نہیں&nbsp;ہوگی •<br>
            </div> --}}
            <div class="divider"></div>
            <div class="address-note"><b>Dubai Chowk Bahawalpur</b></div>
            <div class="center bold" style="font-size:13px;">
                Thank you for shopping!
            </div>
            <div class="no-print center" style="margin-top:10px;">
                <button onclick="window.print()" style="padding:5px 16px;font-size:13px;">Print</button>
            </div>
    </div>
</body>

</html>