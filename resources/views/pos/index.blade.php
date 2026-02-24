<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — {{ $shop->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        body.modal-open {
            overflow: hidden;
        }


        :root {
            /* Modern SaaS: neutral base + crisp accents */
            --bg1: #f6f7fb;
            --bg2: #eef1f7;

            --cardTop: rgba(255, 255, 255, .88);
            --cardBottom: rgba(255, 255, 255, .68);

            --border: rgba(15, 23, 42, .10);
            --border2: rgba(15, 23, 42, .06);

            --text: #0f172a;
            --muted: rgba(15, 23, 42, .60);

            --accent: #2563eb;
            /* blue */
            --accent2: #7c3aed;
            /* purple */
            --success: #16a34a;
            /* green */
            --danger: #dc2626;
            /* red */
            --warn: #f59e0b;
            /* amber */

            --radius: 18px;
            --shadow: 0 18px 45px rgba(15, 23, 42, .10);
            --shadow2: 0 10px 25px rgba(15, 23, 42, .08);

            /* Today sales alternate */
            --todayBg1: rgba(255, 255, 255, .92);
            --todayBg2: rgba(255, 255, 255, .74);
            --todayBorder: rgba(37, 99, 235, .14);
            --todayGlow: 0 18px 45px rgba(37, 99, 235, .10);

            /* Modal */
            --modalBg: rgba(15, 23, 42, .55);
            --panelBg: rgba(255, 255, 255, .86);

            /* Inputs */
            --inputBg: rgba(255, 255, 255, .92);
            --inputFocus: rgba(37, 99, 235, .24);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Roboto, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 600px at 15% 0%, rgba(37, 99, 235, .10), transparent 55%),
                radial-gradient(900px 500px at 100% 20%, rgba(124, 58, 237, .08), transparent 55%),
                linear-gradient(180deg, var(--bg1), var(--bg2));
        }

        /* animations */
        .fade {
            animation: fade .35s ease-out both;
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes pop {
            from {
                opacity: 0;
                transform: scale(.985) translateY(8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .pop {
            animation: pop .22s ease-out both;
        }

        /* topbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, .78);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .topbar-inner {
            max-width: 1440px;
            margin: auto;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .3px;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .title p {
            margin: 2px 0 0;
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }

        /* buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(255, 255, 255, .80));
            color: var(--text);
            font-weight: 900;
            cursor: pointer;
            transition: .18s ease;
            user-select: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow2);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-back {
            background: linear-gradient(135deg, rgba(37, 99, 235, 1), rgba(124, 58, 237, 1));
            color: #fff;
            border: none;
            box-shadow: 0 12px 28px rgba(37, 99, 235, .25);
        }

        .btn-accent {
            background: linear-gradient(135deg, rgba(22, 163, 74, 1), rgba(34, 197, 94, 1));
            color: #fff;
            border: none;
            box-shadow: 0 12px 28px rgba(22, 163, 74, .20);
        }

        .btn-danger {
            background: linear-gradient(135deg, rgba(220, 38, 38, 1), rgba(244, 63, 94, 1));
            color: #fff;
            border: none;
            box-shadow: 0 12px 28px rgba(220, 38, 38, .18);
        }

        .btn-warn {
            background: linear-gradient(135deg, rgba(245, 158, 11, 1), rgba(251, 191, 36, 1));
            color: #1f2937;
            border: none;
            box-shadow: 0 12px 28px rgba(245, 158, 11, .18);
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, .75);
        }

        /* layout */
        .content {
            max-width: 1440px;
            margin: auto;
            padding: 22px;
            width: 100%;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr .7fr;
            gap: 18px;
        }

        /* cards */
        .card {
            background: linear-gradient(180deg, var(--cardTop), var(--cardBottom));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-h {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, .55);
        }

        .card-h .h {
            font-size: 15px;
            font-weight: 950;
            color: var(--text);
        }

        .card-h .sub {
            font-size: 12px;
            color: var(--muted);
            font-weight: 650;
        }

        .card-b {
            padding: 18px;
        }

        /* Today Sales alternate style */
        .card-today {
            background:
                radial-gradient(900px 340px at 25% 0%, rgba(37, 99, 235, .10), transparent 65%),
                linear-gradient(180deg, var(--todayBg1), var(--todayBg2));
            border: 1px solid var(--todayBorder);
            box-shadow: var(--todayGlow);
        }

        .card-today .card-h {
            border-bottom: 1px solid rgba(37, 99, 235, .14);
        }

        .card-today .btn {
            border-color: rgba(37, 99, 235, .20);
        }

        .card-today .table-wrap {
            border-color: rgba(37, 99, 235, .16);
        }

        .card-today th {
            color: rgba(15, 23, 42, .80);
        }

        .card-today td,
        .card-today .small {
            color: rgba(15, 23, 42, .78);
        }

        /* controls */
        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .input,
        .select {
            padding: 12px 12px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: var(--inputBg);
            color: var(--text);
            outline: none;
            transition: box-shadow .16s ease, border-color .16s ease, transform .16s ease;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .06);
        }

        .input {
            min-width: 260px;
        }

        .select {
            min-width: 220px;
        }

        .input::placeholder {
            color: rgba(15, 23, 42, .45);
            font-weight: 600;
        }

        .input:focus,
        .select:focus {
            border-color: rgba(37, 99, 235, .35);
            box-shadow: 0 0 0 4px var(--inputFocus);
            transform: translateY(-1px);
        }

        /* badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .70);
            color: rgba(15, 23, 42, .65);
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 6px 14px rgba(15, 23, 42, .06);
        }

        /* tables */
        .table-wrap {
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255, 255, 255, .70);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th,
        td {
            padding: 11px 10px;
            border-bottom: 1px solid rgba(15, 23, 42, .06);
            text-align: left;
        }

        th {
            font-size: 12px;
            color: rgba(15, 23, 42, .72);
            letter-spacing: .25px;
            font-weight: 900;
            background: rgba(255, 255, 255, .55);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        td {
            font-size: 13px;
            color: rgba(15, 23, 42, .88);
        }

        tr:hover td {
            background: rgba(37, 99, 235, .045);
        }

        .small {
            font-size: 12px;
            color: rgba(15, 23, 42, .55);
            font-weight: 650;
        }

        .right {
            text-align: right;
        }

        hr.sep {
            border: none;
            border-top: 1px solid rgba(15, 23, 42, .08);
            margin: 12px 0;
        }

        /* KPI */
        .kpi {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .kpi .box {
            flex: 1;
            min-width: 160px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            background: rgba(255, 255, 255, .70);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }

        .kpi .box .v {
            font-weight: 950;
            font-size: 18px;
            color: var(--text);
        }

        .kpi .box .t {
            font-size: 12px;
            color: rgba(15, 23, 42, .55);
            font-weight: 750;
        }

        /* form grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media(max-width:520px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* radio rows */
        .radio-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255, 255, 255, .70);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }

        .radio-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(15, 23, 42, .80);
            font-weight: 900;
            font-size: 13px;
            cursor: pointer;
        }

        .radio-row input {
            transform: translateY(1px);
        }

        /* totals */
        .totals {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            background: rgba(255, 255, 255, .72);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .totals .row strong {
            font-size: 14px;
            font-weight: 950;
            color: var(--text);
        }

        .totals .grand {
            border-top: 1px solid rgba(15, 23, 42, .08);
            margin-top: 8px;
            padding-top: 10px;
        }

        .totals .grand strong {
            font-size: 16px;
        }

        /* toast */
        .toast {
            margin-top: 12px;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid rgba(37, 99, 235, .25);
            background: rgba(37, 99, 235, .08);
            color: rgba(15, 23, 42, .90);
            display: none;
            font-weight: 800;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }

        /* Modal */
        .modal {
            position: fixed;
            inset: 0;
            background: var(--modalBg);
            backdrop-filter: blur(10px);
            display: none;
            z-index: 1000;
        }

        .modal.show {
            display: flex;
        }

        .modal-inner {
            width: min(980px, calc(100% - 26px));
            margin: auto;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, .35);
            background:
                radial-gradient(900px 360px at 25% 0%, rgba(37, 99, 235, .10), transparent),
                radial-gradient(800px 300px at 100% 0%, rgba(124, 58, 237, .08), transparent),
                linear-gradient(180deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .74));
            box-shadow: 0 40px 120px rgba(15, 23, 42, .30);

            overflow: hidden;
            /* keep rounded corners */
            display: flex;
            /* header fixed + body scroll */
            flex-direction: column;
            max-height: min(92vh, 860px);
            /* prevent going beyond screen */
        }

        .modal-b {
            padding: 18px;
            overflow: auto;
            /* ✅ scroll here */
            -webkit-overflow-scrolling: touch;
            flex: 1;
            /* take remaining height */
        }

        .modal-h {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(15, 23, 42, .10);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, .65);
        }

        .modal-h .h {
            font-weight: 950;
            color: var(--text);
        }


        .modal-close {
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .85);
            color: var(--text);
            border-radius: 12px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: 950;
            transition: .18s ease;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }

        .modal-close:hover {
            transform: translateY(-1px);
        }

        .modal-kpi {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        @media(max-width:860px) {
            .modal-kpi {
                grid-template-columns: 1fr;
            }
        }

        .pill {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .78);
            font-size: 12px;
            font-weight: 950;
            color: rgba(15, 23, 42, .90);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }

        .pill span {
            color: rgba(15, 23, 42, .60);
            font-weight: 900;
        }

        .helper {
            border: 1px solid rgba(245, 158, 11, .25);
            background: rgba(245, 158, 11, .10);
            border-radius: 14px;
            padding: 10px 12px;
            color: rgba(15, 23, 42, .90);
            font-size: 12px;
            font-weight: 750;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }

        @media(max-width:980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            table {
                min-width: 640px;
            }
        }
    </style>
</head>

<body>
    <div class="fade">
        <div class="topbar">
            <div class="topbar-inner">
                <div class="title">
                    <h1>POS — {{ $shop->name }}</h1>
                    <p>{{ $mode === 'main' ? 'Main Shop POS' : 'Branch Shop POS' }}</p>
                </div>
                <a class="btn btn-back" href="{{ $backUrl }}">← Back to Dashboard</a>
            </div>
        </div>

        <div class="content">
            <div class="grid">

                {{-- ITEMS --}}
                <div class="card fade">
                    <div class="card-h">
                        <div>
                            <div class="h">Items</div>
                            <div class="sub">Scan barcode (Enter) or search manually.</div>
                        </div>
                        <span class="badge">Shop inventory only</span>
                    </div>

                    <div class="card-b">
                        <div class="controls">
                            <input id="barcodeInput" class="input" placeholder="Scan barcode here…" autocomplete="off">
                            <input id="searchInput" class="input" placeholder="Search perfume / brand / SKU / barcode…"
                                autocomplete="off">
                            <button id="searchBtn" class="btn">Search</button>
                            <button id="clearBtn" class="btn btn-ghost">Clear</button>
                        </div>

                        <div id="toast" class="toast"></div>

                        <div style="height:12px;"></div>

                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Perfume</th>
                                        <th>Available</th>
                                        <th>Price</th>
                                        <th class="right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr>
                                        <td colspan="5" class="small">Search items to view results.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="small" style="margin-top:10px;">
                            Tip: keep cursor in barcode box — scanner will type and press Enter automatically.
                        </div>
                    </div>
                </div>

                {{-- CART --}}
                <div class="card fade">
                    <div class="card-h">
                        <div>
                            <div class="h">Cart</div>
                            <div class="sub">Customer + discount + payment + checkout</div>
                        </div>
                    </div>

                    <div class="card-b">

                        {{-- Customer --}}
                        <div style="margin-bottom:12px;">
                            <div class="small" style="margin-bottom:6px;">Customer (optional)</div>
                            <div class="form-grid">
                                <input id="customerName" class="input" style="min-width:auto; width:100%;"
                                    placeholder="Customer name (optional)">
                                <input id="customerPhone" class="input" style="min-width:auto; width:100%;"
                                    placeholder="Customer phone (optional)">
                            </div>
                            <div class="small" style="margin-top:6px;">Leave empty for Walk-in customer.</div>
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
                            <button id="checkoutBtn" class="btn btn-accent" type="button">Complete Sale</button>
                            <button id="openReturnBtn" class="btn btn-warn" type="button">Return / Refund</button>
                        </div>

                        {{-- Payment + Discount UI --}}
                        <div class="form-grid">
                            <div>
                                <div class="small" style="margin-bottom:6px;">Payment Method</div>
                                <div class="radio-row">
                                    <label><input type="radio" name="pay_method" value="counter" checked>
                                        Counter</label>
                                    <label><input type="radio" name="pay_method" value="bank"> Bank</label>
                                </div>

                                <div id="bankBox" style="margin-top:10px; display:none;">
                                    <div class="small" style="margin-bottom:6px;">Select Bank</div>
                                    <select id="bankSelect" class="select" style="width:100%;">
                                        <option value="">Loading banks…</option>
                                    </select>
                                    <div class="small" style="margin-top:6px;">Banks are managed from the Banks page.
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="small" style="margin-bottom:6px;">Discount</div>
                                <div class="form-grid" style="grid-template-columns: 0.9fr 1.1fr;">
                                    <select id="discountType" class="select" style="width:100%; min-width:auto;">
                                        <option value="none">None</option>
                                        <option value="flat">Flat</option>
                                        <option value="percent">Percent %</option>
                                    </select>

                                    <input id="discountValue" class="input" style="min-width:auto; width:100%;"
                                        type="number" min="0" step="0.01" value="0" placeholder="0">
                                </div>
                                <div class="small" style="margin-top:6px;">Flat = amount off. Percent = % off subtotal.
                                </div>
                            </div>
                        </div>

                        <div style="height:12px;"></div>

                        {{-- Cart Table --}}
                        <div class="table-wrap">
                            <table style="min-width:560px;">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th class="right">Unit Price</th>
                                        <th class="right">Subtotal</th>
                                        <th class="right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr>
                                        <td colspan="5" class="small">Cart is empty.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="sep">

                        <div class="kpi">
                            <div class="box">
                                <div class="v" id="cartItemsCount">0</div>
                                <div class="t">Lines</div>
                            </div>
                            <div class="box">
                                <div class="v" id="cartSubtotal">0.00</div>
                                <div class="t">Subtotal</div>
                            </div>
                        </div>

                        <div style="height:12px;"></div>

                        <div class="totals">
                            <div class="row">
                                <span class="small">Discount</span>
                                <strong id="cartDiscount">0.00</strong>
                            </div>
                            <div class="row grand">
                                <span class="small">Grand Total</span>
                                <strong id="cartGrand">0.00</strong>
                            </div>
                        </div>

                        <div class="small" style="margin-top:10px;">
                            After checkout/return, receipt will open in a new tab.
                        </div>
                    </div>
                </div>

            </div>

            {{-- TODAY SALES (different colors) --}}
            <div style="height:18px;"></div>
            <div class="card card-today fade">
                <div class="card-h">
                    <div>
                        <div class="h">Today’s Sales</div>
                        <div class="sub">Summary for this shop only</div>
                    </div>
                    <button id="refreshTodayBtn" class="btn btn-ghost" type="button">Refresh</button>
                </div>
                <div class="card-b">
                    <div class="kpi" style="margin-bottom:12px;">
                        <div class="box">
                            <div class="v" id="todayCount">0</div>
                            <div class="t">Sales</div>
                        </div>
                        <div class="box">
                            <div class="v" id="todayGrand">0.00</div>
                            <div class="t">Total</div>
                        </div>
                        <div class="box">
                            <div class="v" id="todayCounter">0.00</div>
                            <div class="t">Counter</div>
                        </div>
                        <div class="box">
                            <div class="v" id="todayBank">0.00</div>
                            <div class="t">Bank</div>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table style="min-width:720px;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Time</th>
                                    <th>Customer</th>
                                    <th>Method</th>
                                    <th class="right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="todayBody">
                                <tr>
                                    <td colspan="5" class="small">Loading…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- PARTIAL RETURN MODAL --}}
    <div id="returnModal" class="modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-inner pop">
            <div class="modal-h">
                <div>
                    <div class="h">Return / Refund (Partial)</div>
                    <div class="small">Enter Sale ID → select quantities to return → process refund</div>
                </div>
                <button id="closeReturnModal" class="modal-close" type="button">✕</button>
            </div>

            <div class="modal-b">
                <div class="helper" style="margin-bottom:12px;">
                    Tip: You can only return up to the <b>returnable quantity</b> (sold − already returned).
                </div>

                <div class="controls" style="margin-bottom:12px;">
                    <input id="returnSaleIdInput" class="input" style="min-width:auto; width:220px;"
                        placeholder="Sale ID (e.g. 123)">
                    <button id="fetchSaleBtn" class="btn btn-warn" type="button">Fetch Sale</button>
                    <span id="returnStatus" class="small"></span>
                </div>

                <div id="saleMeta" style="display:none;">
                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
                        <span class="pill"><span>Sale</span> <b id="metaSaleId">—</b></span>
                        <span class="pill"><span>Date</span> <b id="metaDate">—</b></span>
                        <span class="pill"><span>Customer</span> <b id="metaCustomer">—</b></span>
                        <span class="pill"><span>Total</span> <b id="metaTotal">—</b></span>
                    </div>

                    <div class="form-grid" style="margin-bottom:12px;">
                        <div>
                            <div class="small" style="margin-bottom:6px;">Refund Method</div>
                            <div class="radio-row">
                                <label><input type="radio" name="refund_method" value="counter" checked> Counter</label>
                                <label><input type="radio" name="refund_method" value="bank"> Bank</label>
                            </div>
                        </div>

                        <div>
                            <div class="small" style="margin-bottom:6px;">Bank (if bank refund)</div>
                            <select id="refundBankSelect" class="select" style="width:100%;">
                                <option value="">Select bank</option>
                            </select>
                            <div class="small" style="margin-top:6px;">Uses same banks list.</div>
                        </div>
                    </div>

                    <div class="table-wrap" style="margin-bottom:12px;">
                        <table style="min-width:820px;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Barcode</th>
                                    <th>Sold</th>
                                    <th>Returned</th>
                                    <th>Returnable</th>
                                    <th>Unit</th>
                                    <th>Return Qty</th>
                                    <th class="right">Line Refund</th>
                                </tr>
                            </thead>
                            <tbody id="returnItemsBody">
                                <tr>
                                    <td colspan="8" class="small">Fetch a sale first.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-kpi">
                        <div class="totals" style="border-color:rgba(251,191,36,.25);">
                            <div class="row">
                                <span class="small">Selected Refund</span>
                                <strong id="refundTotal">0.00</strong>
                            </div>
                            <div class="row grand">
                                <span class="small">Items Selected</span>
                                <strong id="refundLines">0</strong>
                            </div>
                        </div>

                        <div class="helper">
                            <b>Note:</b> Partial returns do not delete the sale. We record a return entry and a negative
                            payment.
                        </div>

                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:10px;">
                            <button id="processPartialReturnBtn" class="btn btn-danger" type="button">Process
                                Refund</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
  const mode = @json($mode); // 'main' or 'branch'

  const routes = {
    items: mode === 'main' ? @json(route('main.pos.items')) : @json(route('branch.pos.items')),
    cart: mode === 'main' ? @json(route('main.pos.cart')) : @json(route('branch.pos.cart')),
    add:  mode === 'main' ? @json(route('main.pos.cart.add')) : @json(route('branch.pos.cart.add')),
    update: mode === 'main' ? @json(route('main.pos.cart.update')) : @json(route('branch.pos.cart.update')),
    remove: mode === 'main' ? @json(route('main.pos.cart.remove')) : @json(route('branch.pos.cart.remove')),
    banks: mode === 'main' ? @json(route('main.pos.banks')) : @json(route('branch.pos.banks')),
    checkout: mode === 'main' ? @json(route('main.pos.checkout')) : @json(route('branch.pos.checkout')),
    today: mode === 'main' ? @json(route('main.pos.today')) : @json(route('branch.pos.today')),

    // partial return endpoints
    sale: mode === 'main' ? @json(route('main.pos.sale', ['sale' => 0])) : @json(route('branch.pos.sale', ['sale' => 0])),
    return_partial: mode === 'main' ? @json(route('main.pos.return_partial')) : @json(route('branch.pos.return_partial')),
  };

  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const barcodeInput = document.getElementById('barcodeInput');
  const searchInput = document.getElementById('searchInput');
  const itemsBody = document.getElementById('itemsBody');
  const cartBody = document.getElementById('cartBody');
  const toast = document.getElementById('toast');

  const cartItemsCount = document.getElementById('cartItemsCount');
  const cartSubtotalEl = document.getElementById('cartSubtotal');
  const cartDiscountEl = document.getElementById('cartDiscount');
  const cartGrandEl = document.getElementById('cartGrand');

  const discountType = document.getElementById('discountType');
  const discountValue = document.getElementById('discountValue');

  const bankBox = document.getElementById('bankBox');
  const bankSelect = document.getElementById('bankSelect');

  const checkoutBtn = document.getElementById('checkoutBtn');
  const openReturnBtn = document.getElementById('openReturnBtn');
  const customerNameEl = document.getElementById('customerName');
  const customerPhoneEl = document.getElementById('customerPhone');

  const todayCountEl = document.getElementById('todayCount');
  const todayGrandEl = document.getElementById('todayGrand');
  const todayCounterEl = document.getElementById('todayCounter');
  const todayBankEl = document.getElementById('todayBank');
  const todayBody = document.getElementById('todayBody');
  const refreshTodayBtn = document.getElementById('refreshTodayBtn');

  // Modal elements
  const returnModal = document.getElementById('returnModal');
  const closeReturnModal = document.getElementById('closeReturnModal');
  const returnSaleIdInput = document.getElementById('returnSaleIdInput');
  const fetchSaleBtn = document.getElementById('fetchSaleBtn');
  const returnStatus = document.getElementById('returnStatus');

  const saleMeta = document.getElementById('saleMeta');
  const metaSaleId = document.getElementById('metaSaleId');
  const metaDate = document.getElementById('metaDate');
  const metaCustomer = document.getElementById('metaCustomer');
  const metaTotal = document.getElementById('metaTotal');

  const refundBankSelect = document.getElementById('refundBankSelect');
  const returnItemsBody = document.getElementById('returnItemsBody');
  const refundTotalEl = document.getElementById('refundTotal');
  const refundLinesEl = document.getElementById('refundLines');
  const processPartialReturnBtn = document.getElementById('processPartialReturnBtn');

  let cartState = [];
  let banksCache = [];
  let currentReturnSale = null; // {sale, items[]}

  function showToast(msg, type='info'){
    toast.style.display = 'block';
    toast.textContent = msg;
    toast.style.borderColor = type === 'error' ? 'rgba(239,68,68,.35)' : 'rgba(56,189,248,.25)';
    toast.style.background = type === 'error' ? 'rgba(239,68,68,.12)' : 'rgba(56,189,248,.10)';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(()=> toast.style.display='none', 2200);
  }

  async function httpGet(url){
    const res = await fetch(url, { headers: {'Accept':'application/json'} });
    if(!res.ok){
      const txt = await res.text();
      throw new Error(txt || ('HTTP '+res.status));
    }
    return res.json();
  }

  async function httpPost(url, body){
    const res = await fetch(url, {
      method:'POST',
      headers:{
        'Accept':'application/json',
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify(body)
    });

    // keep laravel validation message readable
    const contentType = res.headers.get('content-type') || '';
    if(!res.ok){
      if(contentType.includes('application/json')){
        const j = await res.json();
        throw new Error(j.message || 'Request failed');
      }
      const t = await res.text();
      throw new Error(t || ('HTTP '+res.status));
    }

    return res.json();
  }

  function money(n){ return Number(n || 0).toFixed(2); }

  function calcSubtotal(cart){
    return (cart || []).reduce((sum, c) => sum + (Number(c.price||0) * Number(c.qty||0)), 0);
  }

  function calcDiscount(subtotal){
    const t = discountType.value;
    const v = Number(discountValue.value || 0);
    if (t === 'none' || v <= 0) return 0;
    if (t === 'flat') return Math.max(0, Math.min(v, subtotal));
    if (t === 'percent') {
      const pct = Math.max(0, Math.min(v, 100));
      return Math.max(0, Math.min((subtotal * pct / 100), subtotal));
    }
    return 0;
  }

  function refreshTotals(){
    const subtotal = calcSubtotal(cartState);
    const disc = calcDiscount(subtotal);
    const grand = Math.max(0, subtotal - disc);
    cartSubtotalEl.textContent = money(subtotal);
    cartDiscountEl.textContent = money(disc);
    cartGrandEl.textContent = money(grand);
  }

  function renderItems(items){
    if(!items || items.length === 0){
      itemsBody.innerHTML = `<tr><td colspan="5" class="small">No items found.</td></tr>`;
      return;
    }
    itemsBody.innerHTML = items.map(it => `
      <tr>
        <td><b>${it.barcode}</b></td>
        <td>${it.perfume}${it.brand ? `<div class="small">${it.brand}</div>` : ''}</td>
        <td>${it.qty}</td>
        <td>${it.sell_price === null ? '<span class="small">—</span>' : money(it.sell_price)}</td>
        <td class="right"><button class="btn btn-accent" data-add="${it.batch_id}">Add</button></td>
      </tr>
    `).join('');

    itemsBody.querySelectorAll('[data-add]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const batchId = Number(btn.getAttribute('data-add'));
        try{
          const data = await httpPost(routes.add, { batch_id: batchId, qty: 1 });
          renderCart(data.cart);
          showToast('Added to cart');
        }catch(e){
          showToast('Cannot add: ' + e.message, 'error');
        }
      });
    });
  }

  function renderCart(cart){
    cartState = cart || [];

    if(!cartState || cartState.length === 0){
      cartBody.innerHTML = `<tr><td colspan="4" class="small">Cart is empty.</td></tr>`;
      cartItemsCount.textContent = '0';
      refreshTotals();
      return;
    }

    cartBody.innerHTML = cartState.map(c => {
    const unit = Number(c.price || 0);
    const qty = Number(c.qty || 0);
    const line = unit * qty;
    
    return `
    <tr>
        <td>
            <b>${c.perfume}</b>
            <div class="small">${c.barcode}</div>
            <div class="small">Available: ${c.available}</div>
        </td>
    
        <td style="width:140px;">
            <input class="input" style="min-width:auto; width:110px; padding:10px 10px;" type="number" min="1"
                max="${c.available}" value="${qty}" data-qty="${c.batch_id}">
        </td>
    
        <td class="right" style="width:160px;">
            <input class="input" style="min-width:auto; width:120px; padding:10px 10px; text-align:right;" type="number"
                min="0" step="0.01" value="${money(unit)}" data-price="${c.batch_id}">
        </td>
    
        <td class="right" style="width:160px;">
            <b>${money(line)}</b>
        </td>
    
        <td class="right" style="width:140px;">
            <button class="btn btn-danger" data-remove="${c.batch_id}">Remove</button>
        </td>
    </tr>
    `;
    }).join('');

    cartItemsCount.textContent = String(cartState.length);

    cartBody.querySelectorAll('[data-qty]').forEach(inp => {
      inp.addEventListener('change', async () => {
        const batchId = Number(inp.getAttribute('data-qty'));
        const qty = Number(inp.value);
        try{
          const data = await httpPost(routes.update, { batch_id: batchId, qty });
          renderCart(data.cart);
          showToast('Quantity updated');
        }catch(e){
          showToast('Update failed: ' + e.message, 'error');
          await loadCart();
        }
      });
    });

    cartBody.querySelectorAll('[data-price]').forEach(inp => {
    inp.addEventListener('change', async () => {
    const batchId = Number(inp.getAttribute('data-price'));
    const price = Number(inp.value);
    
    if (isNaN(price) || price < 0) { showToast('Invalid price', 'error' ); await loadCart(); return; } try{ const data=await
        httpPost(routes.update, { batch_id: batchId, price }); renderCart(data.cart); showToast('Price updated'); }catch(e){
        showToast('Price update failed: ' + e.message, ' error'); await loadCart(); } }); });


        
    cartBody.querySelectorAll('[data-remove]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const batchId = Number(btn.getAttribute('data-remove'));
        try{
          const data = await httpPost(routes.remove, { batch_id: batchId });
          renderCart(data.cart);
          showToast('Removed');
        }catch(e){
          showToast('Remove failed: ' + e.message, 'error');
        }
      });
    });

    refreshTotals();
  }

  async function loadCart(){
    const data = await httpGet(routes.cart);
    renderCart(data.cart);
  }

  async function searchItems(){
    const q = searchInput.value.trim();
    if(q.length === 0){
      itemsBody.innerHTML = `<tr><td colspan="5" class="small">Type in search box…</td></tr>`;
      return;
    }
    const data = await httpGet(routes.items + '?q=' + encodeURIComponent(q));
    renderItems(data.items);
  }

  barcodeInput.addEventListener('keydown', async (e) => {
    if(e.key !== 'Enter') return;
    e.preventDefault();
    const code = barcodeInput.value.trim();
    if(!code) return;

    try{
      const data = await httpGet(routes.items + '?barcode=' + encodeURIComponent(code));
      if(!data.items || data.items.length === 0){
        showToast('Barcode not found / out of stock', 'error');
        barcodeInput.select();
        return;
      }
      const batchId = data.items[0].batch_id;
      const addRes = await httpPost(routes.add, { batch_id: batchId, qty: 1 });
      renderCart(addRes.cart);
      showToast('Scanned & added');
      barcodeInput.value = '';
      barcodeInput.focus();
    }catch(err){
      showToast('Scan failed: ' + err.message, 'error');
    }
  });

  document.getElementById('searchBtn').addEventListener('click', searchItems);
  document.getElementById('clearBtn').addEventListener('click', () => {
    searchInput.value = '';
    itemsBody.innerHTML = `<tr><td colspan="5" class="small">Search items to view results.</td></tr>`;
    barcodeInput.focus();
  });

  let t = null;
  searchInput.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(searchItems, 350);
  });

  discountType.addEventListener('change', refreshTotals);
  discountValue.addEventListener('input', refreshTotals);

  function handlePayToggle(){
    const method = document.querySelector('input[name="pay_method"]:checked')?.value || 'counter';
    bankBox.style.display = (method === 'bank') ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="pay_method"]').forEach(r => r.addEventListener('change', handlePayToggle));
  handlePayToggle();

  async function loadBanks(){
    try{
      const data = await httpGet(routes.banks);
      banksCache = data.banks || [];

      if(banksCache.length === 0){
        bankSelect.innerHTML = `<option value="">No banks found (add from Banks page)</option>`;
        refundBankSelect.innerHTML = `<option value="">No banks found</option>`;
        return;
      }

      const opts = `<option value="">Select bank</option>` + banksCache.map(b =>
        `<option value="${b.id}">${b.name}${b.account_number ? ' — ' + b.account_number : ''}</option>`
      ).join('');

      bankSelect.innerHTML = opts;
      refundBankSelect.innerHTML = opts;
    }catch(e){
      bankSelect.innerHTML = `<option value="">Failed to load banks</option>`;
      refundBankSelect.innerHTML = `<option value="">Failed to load banks</option>`;
    }
  }

  async function loadTodaySales(){
    try{
      const data = await httpGet(routes.today);
      const s = data.summary || {};
      todayCountEl.textContent = String(s.count || 0);
      todayGrandEl.textContent = money(s.grand_total || 0);
      todayCounterEl.textContent = money(s.counter_total || 0);
      todayBankEl.textContent = money(s.bank_total || 0);

      const rows = data.sales || [];
      if(rows.length === 0){
        todayBody.innerHTML = `<tr><td colspan="5" class="small">No sales today.</td></tr>`;
        return;
      }

      todayBody.innerHTML = rows.map(r => `
        <tr>
          <td><b>#${r.id}</b></td>
          <td>${r.time || '—'}</td>
          <td>${r.customer || 'Walk-in'}</td>
          <td>${(r.method || '—').toUpperCase()}${r.bank ? `<div class="small">${r.bank}</div>` : ''}</td>
          <td class="right"><b>${money(r.total)}</b></td>
        </tr>
      `).join('');
    }catch(e){
      todayBody.innerHTML = `<tr><td colspan="5" class="small">Failed to load today sales.</td></tr>`;
    }
  }

  refreshTodayBtn.addEventListener('click', loadTodaySales);

  // Checkout
  checkoutBtn.addEventListener('click', async () => {
    if(!cartState || cartState.length === 0){
      showToast('Cart is empty', 'error');
      return;
    }

    const method = document.querySelector('input[name="pay_method"]:checked')?.value || 'counter';
    const bankId = bankSelect ? bankSelect.value : '';

    if(method === 'bank' && !bankId){
      showToast('Please select a bank', 'error');
      return;
    }

    const payload = {
      customer_name: (customerNameEl?.value || '').trim(),
      customer_phone: (customerPhoneEl?.value || '').trim(),
      discount_type: discountType.value,
      discount_value: Number(discountValue.value || 0),
      payment_method: method,
      bank_id: method === 'bank' ? Number(bankId) : null,
    };

    try{
      checkoutBtn.disabled = true;
      checkoutBtn.textContent = 'Processing…';

      const res = await httpPost(routes.checkout, payload);

      if(res.ok){
        showToast('Sale completed ✅');
        if(res.receipt_url){ window.open(res.receipt_url, '_blank'); }

        cartState = [];
        renderCart([]);
        customerNameEl.value = '';
        customerPhoneEl.value = '';
        discountType.value = 'none';
        discountValue.value = 0;
        refreshTotals();

        await loadTodaySales();
      }else{
        showToast(res.message || 'Checkout failed', 'error');
      }
    }catch(e){
      showToast('Checkout failed: ' + e.message, 'error');
    }finally{
      checkoutBtn.disabled = false;
      checkoutBtn.textContent = 'Complete Sale';
      barcodeInput.focus();
    }
  });

  /* -----------------------
     Partial Return Modal
  ------------------------ */

  function openModal(){
    document.body.classList.add('modal-open');
    returnStatus.textContent = '';
    saleMeta.style.display = 'none';
    returnItemsBody.innerHTML = `<tr><td colspan="8" class="small">Fetch a sale first.</td></tr>`;
    refundTotalEl.textContent = '0.00';
    refundLinesEl.textContent = '0';
    currentReturnSale = null;

    returnModal.classList.add('show');
    returnModal.setAttribute('aria-hidden','false');
    setTimeout(()=> returnSaleIdInput.focus(), 60);
  }

  function closeModal(){
    returnModal.classList.remove('show');
    returnModal.setAttribute('aria-hidden','true');
    document.body.classList.remove('modal-open');
    barcodeInput.focus();
  }

  openReturnBtn.addEventListener('click', openModal);
  closeReturnModal.addEventListener('click', closeModal);

  // click outside modal to close
  returnModal.addEventListener('click', (e) => {
    if(e.target === returnModal) closeModal();
  });

  // ESC to close
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape' && returnModal.classList.contains('show')) closeModal();
  });

  function getRefundMethod(){
    return document.querySelector('input[name="refund_method"]:checked')?.value || 'counter';
  }

  function computeRefundFromUI(){
    let total = 0;
    let lines = 0;

    const inputs = returnItemsBody.querySelectorAll('input[data-sale-item]');
    inputs.forEach(inp => {
      const qty = Number(inp.value || 0);
      const unit = Number(inp.getAttribute('data-unit') || 0);
      if(qty > 0){
        lines++;
        total += (qty * unit);
      }
      const lineCell = document.getElementById('lineRefund_' + inp.getAttribute('data-sale-item'));
      if(lineCell){
        lineCell.textContent = money(qty * unit);
      }
    });

    refundTotalEl.textContent = money(total);
    refundLinesEl.textContent = String(lines);
    return { total, lines };
  }

  function renderReturnItems(items){
    if(!items || items.length === 0){
      returnItemsBody.innerHTML = `<tr><td colspan="8" class="small">No items on this sale.</td></tr>`;
      return;
    }

    returnItemsBody.innerHTML = items.map(it => {
      const disabled = it.returnable_qty <= 0 ? 'disabled' : '';
      const max = it.returnable_qty;
      return `
        <tr>
          <td>
            <b>${it.name}</b>
            <div class="small">SaleItem #${it.sale_item_id}</div>
          </td>
          <td>${it.barcode}</td>
          <td>${it.sold_qty}</td>
          <td>${it.returned_qty}</td>
          <td><b>${it.returnable_qty}</b></td>
          <td>${money(it.unit_price)}</td>
          <td style="width:140px;">
            <input class="input" style="min-width:auto; width:110px; padding:10px 10px;"
              type="number" min="0" max="${max}" value="0"
              ${disabled}
              data-sale-item="${it.sale_item_id}"
              data-batch="${it.batch_id}"
              data-unit="${it.unit_price}">
          </td>
          <td class="right"><b id="lineRefund_${it.sale_item_id}">0.00</b></td>
        </tr>
      `;
    }).join('');

    // Listen changes
    returnItemsBody.querySelectorAll('input[data-sale-item]').forEach(inp => {
      inp.addEventListener('input', () => {
        let v = Number(inp.value || 0);
        const max = Number(inp.getAttribute('max') || 0);
        if(v < 0) v = 0;
        if(v > max) v = max;
        inp.value = String(v);
        computeRefundFromUI();
      });
    });

    computeRefundFromUI();
  }

  function fillSaleMeta(sale){
    saleMeta.style.display = 'block';
    metaSaleId.textContent = '#' + sale.id;
    metaDate.textContent = sale.created_at || '—';
    metaCustomer.textContent = sale.customer || 'Walk-in';
    metaTotal.textContent = money(sale.total || 0);
  }

  async function fetchSale(){
    const id = Number((returnSaleIdInput.value || '').trim());
    if(!id){
      returnStatus.textContent = 'Enter a valid Sale ID.';
      returnStatus.style.color = 'rgba(251,191,36,.95)';
      return;
    }

    try{
      returnStatus.textContent = 'Fetching…';
      returnStatus.style.color = 'rgba(148,163,184,.95)';

      const url = routes.sale.replace(/0$/, String(id));
      const data = await httpGet(url);

      if(!data.ok){
        returnStatus.textContent = data.message || 'Cannot fetch sale.';
        returnStatus.style.color = 'rgba(239,68,68,.95)';
        return;
      }

      currentReturnSale = data;

      fillSaleMeta(data.sale);
      renderReturnItems(data.items);

      // Prefer original payment method for refund, but allow change
      const pm = data.sale.payment_method || 'counter';
      document.querySelectorAll('input[name="refund_method"]').forEach(r => {
        r.checked = (r.value === pm);
      });

      if(pm === 'bank' && data.sale.bank_id){
        refundBankSelect.value = String(data.sale.bank_id);
      } else {
        refundBankSelect.value = '';
      }

      returnStatus.textContent = 'Sale loaded ✅';
      returnStatus.style.color = 'rgba(34,197,94,.95)';
    }catch(e){
      returnStatus.textContent = 'Fetch failed: ' + e.message;
      returnStatus.style.color = 'rgba(239,68,68,.95)';
      saleMeta.style.display = 'none';
      currentReturnSale = null;
    }
  }

  fetchSaleBtn.addEventListener('click', fetchSale);
  returnSaleIdInput.addEventListener('keydown', (e) => {
    if(e.key === 'Enter'){ e.preventDefault(); fetchSale(); }
  });

  // process partial return
  processPartialReturnBtn.addEventListener('click', async () => {
    if(!currentReturnSale || !currentReturnSale.sale){
      returnStatus.textContent = 'Fetch a sale first.';
      returnStatus.style.color = 'rgba(251,191,36,.95)';
      return;
    }

    const { total, lines } = computeRefundFromUI();
    if(lines === 0 || total <= 0){
      returnStatus.textContent = 'Select at least one item qty to return.';
      returnStatus.style.color = 'rgba(251,191,36,.95)';
      return;
    }

    const method = getRefundMethod();
    const bankId = refundBankSelect.value;

    if(method === 'bank' && !bankId){
      returnStatus.textContent = 'Please select a bank for bank refund.';
      returnStatus.style.color = 'rgba(251,191,36,.95)';
      return;
    }

    if(!confirm(`Process refund of ${money(total)} for Sale #${currentReturnSale.sale.id}?`)){
      return;
    }

    // build items payload
    const items = [];
    returnItemsBody.querySelectorAll('input[data-sale-item]').forEach(inp => {
      const qty = Number(inp.value || 0);
      if(qty > 0){
        items.push({
          sale_item_id: Number(inp.getAttribute('data-sale-item')),
          qty: qty
        });
      }
    });

    try{
      processPartialReturnBtn.disabled = true;
      processPartialReturnBtn.textContent = 'Processing…';

      const res = await httpPost(routes.return_partial, {
        sale_id: Number(currentReturnSale.sale.id),
        method: method,
        bank_id: method === 'bank' ? Number(bankId) : null,
        items: items
      });

      if(res.ok){
        returnStatus.textContent = 'Refund processed ✅';
        returnStatus.style.color = 'rgba(34,197,94,.95)';

        // Refresh today sales
        await loadTodaySales();

        // Re-fetch sale to update returnable qty immediately
        await fetchSale();

        // Optional: close modal after success
        // closeModal();
        showToast('Partial return processed ✅');
      }else{
        returnStatus.textContent = res.message || 'Refund failed.';
        returnStatus.style.color = 'rgba(239,68,68,.95)';
      }
    }catch(e){
      returnStatus.textContent = 'Refund failed: ' + e.message;
      returnStatus.style.color = 'rgba(239,68,68,.95)';
    }finally{
      processPartialReturnBtn.disabled = false;
      processPartialReturnBtn.textContent = 'Process Refund';
    }
  });

  /* -----------------------
     Init
  ------------------------ */

  // Load banks into both selects
  loadBanks();

  // Load cart and today
  loadCart();
  loadTodaySales();

  // focus barcode by default
  barcodeInput.focus();
})();
    </script>
</body>

</html>