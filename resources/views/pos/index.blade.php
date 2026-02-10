<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — {{ $shop->name }}</title>

    <style>
        :root {
            --bg1: #0f172a;
            --bg2: #020617;
            --card: rgba(255, 255, 255, .06);
            --border: rgba(255, 255, 255, .10);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --accent: #22c55e;
            --accent2: #38bdf8;
            --danger: #ef4444;
            --warning: #f59e0b;
            --radius: 18px;
            --shadow: 0 25px 60px rgba(0, 0, 0, .55);
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(56, 189, 248, .12), transparent),
                radial-gradient(1000px 500px at 100% 10%, rgba(34, 197, 94, .10), transparent),
                linear-gradient(180deg, var(--bg1), var(--bg2));
            color: var(--text);
        }

        /* animations */
        .fade {
            animation: fade .35s ease-out both
        }

        @keyframes fade {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(10px);
            background: rgba(2, 6, 23, .72);
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
            letter-spacing: .4px
        }

        .title p {
            margin: 2px 0 0;
            font-size: 12px;
            color: var(--muted)
        }

        .btn {
            padding: 10px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(255, 255, 255, .10), rgba(255, 255, 255, .03));
            color: var(--text);
            font-weight: 800;
            cursor: pointer;
            transition: .2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow)
        }

        .btn:active {
            transform: none
        }

        .btn-back {
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            color: #042f2e;
            border: none;
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent), #16a34a);
            color: #052e16;
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #fb7185, #ef4444);
            color: #3f0a0a;
            border: none;
        }

        .btn-ghost {
            background: transparent;
        }

        .content {
            max-width: 1440px;
            margin: auto;
            padding: 22px;
            width: 100%
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr .7fr;
            gap: 18px
        }

        .card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .03));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-h {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .card-h .h {
            font-size: 15px;
            font-weight: 900
        }

        .card-h .sub {
            font-size: 12px;
            color: var(--muted)
        }

        .card-b {
            padding: 18px
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center
        }

        .input,
        .select {
            padding: 12px 12px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(2, 6, 23, .55);
            color: var(--text);
            outline: none;
        }

        .input {
            min-width: 260px;
        }

        .select {
            min-width: 220px;
        }

        .input::placeholder {
            color: rgba(148, 163, 184, .75)
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(2, 6, 23, .45);
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .table-wrap {
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 14px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            text-align: left
        }

        th {
            font-size: 12px;
            color: rgba(226, 232, 240, .85);
            letter-spacing: .3px
        }

        td {
            font-size: 13px
        }

        .small {
            font-size: 12px;
            color: var(--muted)
        }

        .right {
            text-align: right
        }

        hr.sep {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, .10);
            margin: 12px 0
        }

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
            background: rgba(2, 6, 23, .35);
        }

        .kpi .box .v {
            font-weight: 900;
            font-size: 18px
        }

        .kpi .box .t {
            font-size: 12px;
            color: var(--muted)
        }

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

        .radio-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(2, 6, 23, .35);
        }

        .radio-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(226, 232, 240, .92);
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
        }

        .radio-row input {
            transform: translateY(1px);
        }

        .totals {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            background: rgba(2, 6, 23, .35);
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .totals .row strong {
            font-size: 14px
        }

        .totals .grand {
            border-top: 1px solid rgba(255, 255, 255, .10);
            margin-top: 8px;
            padding-top: 10px;
        }

        .totals .grand strong {
            font-size: 16px
        }

        .toast {
            margin-top: 12px;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid rgba(56, 189, 248, .25);
            background: rgba(56, 189, 248, .10);
            color: rgba(226, 232, 240, .95);
            display: none;
        }

        @media(max-width:980px) {
            .grid {
                grid-template-columns: 1fr
            }

            table {
                min-width: 640px
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
                            <div class="sub">Discount + payment selection (checkout next step).</div>
                        </div>
                    </div>

                    <div class="card-b">
                        {{-- Payment + Discount UI --}}
                        <div class="form-grid">
                            <div>
                                <div class="small" style="margin-bottom:6px;">Payment Method</div>
                                <div class="radio-row">
                                    <label>
                                        <input type="radio" name="pay_method" value="counter" checked>
                                        Counter
                                    </label>
                                    <label>
                                        <input type="radio" name="pay_method" value="bank">
                                        Bank
                                    </label>
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
                                <div class="small" style="margin-top:6px;">
                                    Flat = amount off. Percent = % off subtotal.
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
                                        <th class="right">Subtotal</th>
                                        <th class="right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr>
                                        <td colspan="4" class="small">Cart is empty.</td>
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
                            Next step will add: Checkout (save sale + payment), receipt in new tab, and stock deduction.
                        </div>
                    </div>
                </div>

            </div>

            {{-- Today Sales placeholder remains --}}
            <div style="height:18px;"></div>
            <div class="card fade">
                <div class="card-h">
                    <div>
                        <div class="h">Today’s Sales</div>
                        <div class="sub">Will be implemented after checkout.</div>
                    </div>
                </div>
                <div class="card-b">
                    <div class="small">Pending…</div>
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

  let cartState = []; // keep latest cart for totals

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
    if(!res.ok){
      let msg = 'Request failed';
      try{
        const t = await res.text();
        msg = t || msg;
      }catch(e){}
      throw new Error(msg);
    }
    return res.json();
  }

  function money(n){
    const v = Number(n || 0);
    return v.toFixed(2);
  }

  function calcSubtotal(cart){
    return (cart || []).reduce((sum, c) => sum + (Number(c.price||0) * Number(c.qty||0)), 0);
  }

  function calcDiscount(subtotal){
    const t = discountType.value;
    const v = Number(discountValue.value || 0);

    if (t === 'none' || v <= 0) return 0;

    if (t === 'flat') {
      return Math.max(0, Math.min(v, subtotal));
    }

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
        <td class="right">
          <button class="btn btn-accent" data-add="${it.batch_id}">Add</button>
        </td>
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
      const line = (Number(c.price||0) * Number(c.qty||0));
      return `
        <tr>
          <td>
            <b>${c.perfume}</b>
            <div class="small">${c.barcode}</div>
            <div class="small">Available: ${c.available}</div>
          </td>
          <td style="width:140px;">
            <input class="input" style="min-width:auto; width:110px; padding:10px 10px;"
                   type="number" min="1" max="${c.available}" value="${c.qty}" data-qty="${c.batch_id}">
          </td>
          <td class="right"><b>${money(line)}</b><div class="small">${money(c.price)} each</div></td>
          <td class="right">
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

  // barcode: exact lookup then auto-add first result
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

  // Discount changes refresh totals
  discountType.addEventListener('change', refreshTotals);
  discountValue.addEventListener('input', refreshTotals);

  // Payment method toggle
  function handlePayToggle(){
    const method = document.querySelector('input[name="pay_method"]:checked')?.value || 'counter';
    bankBox.style.display = (method === 'bank') ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="pay_method"]').forEach(r => r.addEventListener('change', handlePayToggle));
  handlePayToggle();

  // Load banks
  async function loadBanks(){
    try{
      const data = await httpGet(routes.banks);
      const banks = data.banks || [];
      if(banks.length === 0){
        bankSelect.innerHTML = `<option value="">No banks found (add from Banks page)</option>`;
        return;
      }
      bankSelect.innerHTML = `<option value="">Select bank</option>` + banks.map(b =>
        `<option value="${b.id}">${b.name}${b.account_number ? ' — ' + b.account_number : ''}</option>`
      ).join('');
    }catch(e){
      bankSelect.innerHTML = `<option value="">Failed to load banks</option>`;
    }
  }

  // initial
  loadCart();
  loadBanks();
  barcodeInput.focus();
})();
    </script>
</body>

</html>