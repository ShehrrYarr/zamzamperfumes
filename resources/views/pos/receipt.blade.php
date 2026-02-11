<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Receipt #{{ $sale->id ?? '' }}</title>

    {{-- Bootstrap CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Roboto Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --paper-w: 83mm;
            --paper-h: 297mm;
            --black: #000;
        }

        body {
            margin: 0;
            background: #f2f3f5;
            color: var(--black);
            font-family: 'Roboto', Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        /* Center container (screen only) */
        .screen-wrap {
            max-width: 520px;
            margin: 18px auto;
            padding: 0 12px;
        }

        /* Paper */
        .paper {
            width: var(--paper-w);
            min-height: var(--paper-h);
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, .14);
            overflow: hidden;
            border: 1px solid #000;
        }

        .receipt {
            width: 100%;
            padding: 10px 10px 12px;
        }

        .brand {
            text-align: center;
            font-weight: 900;
            font-size: 18px;
            letter-spacing: .3px;
            line-height: 1.15;
            margin: 0;
            text-transform: uppercase;
        }

        .contacts {
            text-align: center;
            font-weight: 700;
            font-size: 11px;
            margin: 5px 0 0;
            line-height: 1.25;
        }

        .subhead {
            text-align: center;
            font-weight: 700;
            font-size: 11px;
            margin: 4px 0 0;
            line-height: 1.25;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .meta {
            font-size: 11px;
            line-height: 1.35;
            margin: 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 10px;
            align-items: start;
        }

        .meta-box {
            border: 1px solid #000;
            padding: 6px 7px;
            border-radius: 10px;
        }

        .meta-label {
            font-weight: 900;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
            line-height: 1.1;
        }

        .meta-value {
            font-weight: 700;
            margin-top: 2px;
            line-height: 1.2;
            word-break: break-word;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 3px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .35px;
        }

        tbody td {
            border-bottom: 1px solid #000;
            padding: 6px 3px;
            font-size: 11px;
            vertical-align: top;
            font-weight: 700;
        }

        /* Column widths tuned for 83mm */
        .col-item {
            width: 50%;
            text-align: left;
        }

        .col-qty {
            width: 12%;
            text-align: right;
        }

        .col-rate {
            width: 19%;
            text-align: right;
        }

        .col-amt {
            width: 19%;
            text-align: right;
        }

        .item-name {
            font-weight: 900;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .item-sub {
            font-weight: 700;
            font-size: 10px;
            line-height: 1.15;
            margin-top: 2px;
            overflow-wrap: anywhere;
        }

        .totals {
            border: 2px solid #000;
            border-radius: 12px;
            padding: 8px 9px;
            margin-top: 10px;
            font-size: 12px;
            font-weight: 900;
        }

        .totals .rowx {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 2px 0;
        }

        .totals .grand {
            border-top: 2px dashed #000;
            padding-top: 7px;
            margin-top: 6px;
            font-size: 14px;
            font-weight: 900;
        }

        .policy {
            border: 2px solid #000;
            border-radius: 12px;
            padding: 8px 9px;
            margin-top: 10px;
        }

        .policy-title {
            font-weight: 900;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin: 0 0 6px;
        }

        .policy ul {
            margin: 0;
            padding-left: 16px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
        }

        .policy li {
            margin: 2px 0;
        }

        .footer {
            text-align: center;
            font-weight: 900;
            font-size: 11px;
            margin-top: 10px;
            line-height: 1.3;
            padding-bottom: 4px;
        }

        /* Print controls (screen only) */
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
        }

        @page {
            size: 83mm 297mm;
            margin: 0;
        }

        @media print {
            body {
                background: #fff;
            }

            .screen-wrap {
                margin: 0;
                padding: 0;
                max-width: none;
            }

            .actions {
                display: none !important;
            }

            .paper {
                box-shadow: none;
                border-radius: 0;
                border: 0;
                width: var(--paper-w);
                min-height: var(--paper-h);
            }

            .receipt {
                padding: 8px 8px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="screen-wrap">
        <div class="paper">
            <div class="receipt">

                <p class="brand">Zam Zam Perfumes BWP</p>
                <p class="contacts">+92 300 9218003, +92 300 7852629, +92 302 5066267</p>
                <p class="subhead">OFFICIAL SALES RECEIPT</p>

                <div class="divider"></div>

                @php
                $cName = $sale->customer_name ?? null;
                $cPhone = $sale->customer_phone ?? null;
                $customerText = $cName ? $cName : 'Walk-in';
                if($cPhone) $customerText .= ' / '.$cPhone;

                $pay = strtoupper($sale->payment_method ?? '-');
                $bankName = $sale->bank->name ?? null;

                $subTotal = (float)($sale->sub_total ?? $sale->subtotal ?? $sale->total_before_discount ?? 0);
                $discount = (float)($sale->discount_amount ?? $sale->discount ?? 0);
                $grand = (float)($sale->grand_total ?? $sale->total ?? 0);

                $itemsList = $items ?? ($sale->items ?? []);
                @endphp

                <div class="meta">
                    <div class="meta-grid">
                        <div class="meta-box">
                            <div class="meta-label">Receipt #</div>
                            <div class="meta-value">{{ $sale->id ?? '-' }}</div>
                        </div>

                        <div class="meta-box">
                            <div class="meta-label">Date & Time</div>
                            <div class="meta-value">{{ optional($sale->created_at)->format('Y-m-d h:i A') ??
                                now()->format('Y-m-d h:i A') }}</div>
                        </div>

                        <div class="meta-box">
                            <div class="meta-label">Shop</div>
                            <div class="meta-value">{{ $sale->shop->name ?? '-' }}</div>
                        </div>

                        <div class="meta-box">
                            <div class="meta-label">Cashier</div>
                            <div class="meta-value">{{ $sale->user->name ?? '-' }}</div>
                        </div>

                        <div class="meta-box" style="grid-column: 1 / -1;">
                            <div class="meta-label">Customer</div>
                            <div class="meta-value">{{ $customerText }}</div>
                        </div>

                        <div class="meta-box" style="grid-column: 1 / -1;">
                            <div class="meta-label">Payment</div>
                            <div class="meta-value">{{ $pay }}{{ ($pay === 'BANK' && $bankName) ? ' - '.$bankName : '' ?? 'Counter'
                                }}</div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <table>
                    <thead>
                        <tr>
                            <th class="col-item">Item</th>
                            <th class="col-qty">Qty</th>
                            <th class="col-rate">Price</th>
                            <th class="col-amt">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemsList as $it)
                        @php
                        $name = $it->name ?? ($it->batch?->perfume?->name ?? 'Item');
                        $barcode = $it->batch?->barcode ?? ($it->barcode ?? null);

                        $qty = (int)($it->quantity ?? 1);
                        $price = (float)($it->unit_price ?? $it->price ?? 0);
                        $lineTotal = (float)($it->total ?? ($qty * $price));
                        @endphp
                        <tr>
                            <td class="col-item">
                                <div class="item-name">{{ $name }}</div>
                                @if($barcode)
                                <div class="item-sub">Barcode: {{ $barcode }}</div>
                                @endif
                            </td>
                            <td class="col-qty">{{ $qty }}</td>
                            <td class="col-rate">{{ number_format($price, 2) }}</td>
                            <td class="col-amt">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center" style="font-weight:900;">NO ITEMS</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="totals">
                    <div class="rowx">
                        <div>SUBTOTAL</div>
                        <div>{{ number_format($subTotal, 2) }}</div>
                    </div>
                    <div class="rowx">
                        <div>DISCOUNT</div>
                        <div>-{{ number_format($discount, 2) }}</div>
                    </div>
                    <div class="rowx grand">
                        <div>GRAND TOTAL</div>
                        <div>{{ number_format($grand, 2) }}</div>
                    </div>
                </div>

                <div class="policy">
                    <div class="policy-title">Return Policy</div>
                    <ul>
                        <li>Exchange only if seal is intact and product is unused.</li>
                        <li>Return/exchange within 24 hours with original receipt.</li>
                        <li>No return on opened bottles, testers, or used items.</li>
                        <li>Damaged-after-purchase items are not eligible for return.</li>
                        <li>Management decision will be final after verification.</li>
                    </ul>
                </div>

                <div class="divider"></div>

                <div class="footer">
                    THANK YOU FOR YOUR PURCHASE<br>
                    — HAVE A NICE DAY —
                </div>

            </div>
        </div>

        {{-- ✅ Print button (screen only) --}}
        <div class="actions">
            <button type="button" class="btn btn-dark btn-sm px-4" onclick="window.print()">Print</button>
            <button type="button" class="btn btn-outline-dark btn-sm px-4" onclick="window.close()">Close</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>