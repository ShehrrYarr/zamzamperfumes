@extends('user_navbar')
@section('content')

<style>
    .pos-container {
        width: 100%;
        max-width: none;
        margin: 30px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 18px #0002;
        padding: 0;
        overflow: hidden;
    }

    @media (max-width: 900px) {
        .pos-main {
            flex-direction: column;
        }

        .pos-cart {
            min-width: 100%;
            border-left: none;
            border-top: 2px solid #eee;
        }
    }

    .pos-main {
        display: flex;
        gap: 0;
    }

    .pos-form,
    .pos-cart {
        padding: 32px 24px;
        min-width: 350px;
        flex: 1;
    }

    .pos-cart {
        border-left: 2px solid #eee;
        background: #f7f7fb;
    }

    .input-row {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        align-items: flex-start;
    }

    .input-row label {
        min-width: 150px;
        font-weight: bold;
        color: #333;
        padding-top: 6px;
    }

    .input-row input,
    .input-row select,
    .input-row textarea {
        flex: 1;
        border: 1px solid #ddd;
        border-radius: 7px;
        padding: 8px 10px;
        font-size: 1em;
    }

    .input-row input:focus,
    .input-row textarea:focus,
    .input-row select:focus {
        border-color: #0066f7;
        outline: none;
    }

    .input-row input[type="number"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .scan-section {
        margin-bottom: 24px;
    }

    .sale-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    .sale-table th,
    .sale-table td {
        padding: 7px 8px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    .sale-table th {
        background: #f2f2f7;
    }

    .cart-summary {
        font-size: 1.1em;
        margin: 20px 0 10px 0;
        text-align: right;
    }

    .pos-btn,
    .btn-scan {
        display: inline-block;
        background: #0066f7;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 9px 18px;
        margin: 3px 0;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: .2s;
    }

    .btn-scan {
        background: #f78000;
        margin-left: 6px;
    }

    .pos-btn:hover,
    .btn-scan:hover {
        filter: brightness(.95);
    }

    .discount-hint {
        font-size: 12px;
        color: #666;
        margin-top: -10px;
        margin-bottom: 12px;
        padding-left: 162px;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            @if (session('success'))
            <div class="alert alert-success" id="successMessage">
                {{ session('success') }}
            </div>
            @endif

            @if (session('danger'))
            <div class="alert alert-danger" id="dangerMessage" style="color: red;">
                {{ session('danger') }}
            </div>
            @endif

            <div class="pos-container">
                <div style="padding:24px 32px 8px 32px; border-bottom:2px solid #f0f0f0;">
                    <h2 style="margin:0;font-weight:700;color:#111;">Point of Sale</h2>
                </div>

                <div class="pos-main">
                    <!-- Left: Sale & Scan Form -->
                    <div class="pos-form">
                        {{-- This form is only for UI grouping; checkout is via JS fetch --}}
                        <form method="POST" action="{{ route('sales.store') }}">
                            @csrf

                            <div class="input-row">
                                <label for="vendor_id">Vendor (optional):</label>
                                <select name="vendor_id" id="vendor_id">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="input-row">
                                <label for="customer_name">Customer Name:</label>
                                <input type="text" name="customer_name" id="customer_name"
                                    placeholder="Walk-in (leave blank if not)">
                            </div>

                            <div class="input-row" id="customer_mobile_row" style="display: none;">
                                <label for="customer_mobile">Customer Mobile:</label>
                                <input type="text" name="customer_mobile" id="customer_mobile"
                                    placeholder="Enter Mobile Number">
                            </div>

                            {{-- Comment field --}}
                            <div class="input-row">
                                <label for="sale_comment">Comment:</label>
                                <textarea id="sale_comment" name="comment" rows="2"
                                    placeholder="Optional note about this sale (e.g., special request, delivery instruction)"></textarea>
                            </div>
                        </form>

                        <div class="scan-section">
                            <label for="barcode_search" style="font-weight: bold;">Scan or Enter Barcode:</label>
                            <div style="display:flex; gap:8px; margin-top:4px;">
                                <input type="text" id="barcode_search" name="barcode_search"
                                    placeholder="Scan or type batch barcode" autocomplete="off" style="flex:1;">
                                <button type="button" class="btn-scan" onclick="scanBarcode()">Scan/Add</button>
                            </div>

                            <div style="margin-top:12px;">
                                <span style="color:#888;font-size:.97em;">or select manually:</span>
                            </div>

                            <div style="margin-top:6px;">
                                <select id="manual_batch_select"
                                    style="width:100%; padding:6px; border-radius:6px; border:1px solid #ddd;">
                                    <option value="">Select Accessory Batch</option>
                                    @foreach($batches as $batch)
                                    <option value="{{ $batch->barcode }}">
                                        {{ $batch->barcode }} - {{ $batch->accessory->name }} (Remaining: {{
                                        $batch->qty_remaining }}) - {{ $batch->accessory->description }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-scan" onclick="addSelectedBatch()">Add</button>
                            </div>
                        </div>

                        {{-- Preload batches data --}}
                        <script>
                            window.batchData = {};
              @foreach($batches as $batch)
                window.batchData["{{ $batch->barcode }}"] = {
                  id: {{ $batch->id }},
                  barcode: "{{ $batch->barcode }}",
                  accessory: "{{ addslashes($batch->accessory->name) }}",
                  qty_remaining: {{ $batch->qty_remaining }},
                  price: {{ $batch->selling_price ?? 0 }}
                };
              @endforeach
                        </script>
                    </div>

                    <!-- Right: Cart (Sale Items) -->
                    <div class="pos-cart">
                        <h3 style="margin-top:0;">Sale Cart</h3>

                        <table class="sale-table" id="sale-cart-table">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Accessory</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- JS fills rows --}}
                            </tbody>
                        </table>

                        <div class="cart-summary" id="cart-summary">
                            Total: <span id="cart-total">0.00</span>
                        </div>

                        {{-- ✅ Discount Amount --}}
                        <div class="input-row">
                            <label for="cart_discount">Discount (Amount):</label>
                            <input type="number" id="cart_discount" min="0" step="0.01"
                                placeholder="Enter Discount Amount" oninput="handleDiscountMode('amount')">
                        </div>

                        {{-- ✅ Discount Percent --}}
                        <div class="input-row">
                            <label for="cart_discount_percent">Discount (%):</label>
                            <input type="number" id="cart_discount_percent" min="0" max="100" step="0.01"
                                placeholder="Enter Discount Percentage" oninput="handleDiscountMode('percent')">
                        </div>
                        <div class="discount-hint">
                            Use only one: either Discount Amount or Discount % (the other will lock automatically).
                        </div>

                        <button class="pos-btn" id="checkout-btn" onclick="checkoutSale()">Checkout & Print
                            Invoice</button>
                        <br>

                        {{-- Vendor extra fields --}}
                        <div id="vendor-extra-fields" style="display:none; margin-top:20px;">
                            <div>
                                <label for="vendor_payment">Amount Vendor Will Pay (optional):</label>
                                <input type="number" min="0" name="pay_amount" id="pay_amount" class="form-control"
                                    placeholder="Enter amount">
                            </div>
                            <div style="margin-top:10px;">
                                <label for="vendor_balance">Vendor Balance:</label>
                                <input type="text" id="vendor_balance" class="form-control" readonly>
                            </div>
                        </div>

                        {{-- Payments --}}
                        <div id="payment-section" style="margin-top:20px;">
                            <div class="mb-2">
                                <label class="d-block mb-1">Payment Method:</label>
                                <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                                    <label style="display:flex; gap:6px; align-items:center;">
                                        <input type="radio" name="payment_method" value="counter" checked>
                                        <span>Counter (Cash)</span>
                                    </label>

                                    <label style="display:flex; gap:6px; align-items:center;">
                                        <input type="radio" name="payment_method" value="bank">
                                        <span>Bank</span>
                                    </label>

                                    <div id="bank-select-wrap" style="display:none; min-width:260px;">
                                        <select id="bank_id" class="form-control">
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">
                                                {{ $bank->name }}{{ $bank->account_no ? ' — '.$bank->account_no : '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="bank-ref-wrap" style="display:none; min-width:220px;">
                                        <input type="text" id="bank_reference" class="form-control"
                                            placeholder="Reference / Slip # (optional)">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> {{-- /pos-cart --}}
                </div> {{-- /pos-main --}}
            </div> {{-- /pos-container --}}

            {{-- Daily Sales --}}
            <div class="pos-container">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Daily Sales</h4>
                        <div>
                            <h4>Total Selling Price: Rs. {{ number_format($totalSellingPrice, 2) }}</h4>
                            <h4>Total Paid Price: Rs. {{ number_format($totalPaidPrice, 2) }}</h4>

                            <div style="margin-top:8px;">
                                <span class="badge bg-secondary" style="font-size:0.95rem;">
                                    Counter: Rs. {{ number_format($counterTotal, 2) }}
                                </span>
                                <span class="badge bg-primary" style="font-size:0.95rem; margin-left:6px;">
                                    Bank: Rs. {{ number_format($bankTotal, 2) }}
                                </span>
                            </div>

                            @if(isset($bankBreakdown) && $bankBreakdown->count())
                            <div style="margin-top:6px; font-size:0.95rem; color:#333;">
                                <strong>By Bank:</strong>
                                @foreach($bankBreakdown as $bk)
                                <span class="badge bg-light text-dark" style="margin-left:6px;">
                                    {{ $bk['name'] }}: Rs. {{ number_format($bk['total'], 2) }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="loginTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Customer/Vendor</th>
                                    <th>Total</th>
                                    <th>Payments</th>
                                    <th>Items</th>
                                    <th>Comment</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                @php
                                $subtotal = $sale->items->sum('subtotal');
                                $discount = (float) ($sale->discount_amount ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
                                    <td>
                                        @if($sale->vendor)
                                        Vendor: {{ $sale->vendor->name }}
                                        @elseif($sale->customer_name)
                                        Customer: {{ $sale->customer_name }}
                                        @else
                                        Walk-in
                                        @endif

                                        @if(!empty($sale->comment))
                                        <div style="font-size: 12px; color:#666; margin-top:4px;">
                                            <strong>Note:</strong> {{ $sale->comment }}
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                                        @if($discount > 0)
                                        <div style="font-size: 12px; color: #666; margin-top: 4px; line-height: 1.2;">
                                            <div>Subtotal: Rs. {{ number_format($subtotal, 2) }}</div>
                                            <div>Discount: - Rs. {{ number_format($discount, 2) }}</div>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sale->payments->isEmpty())
                                        <span class="badge bg-light text-dark">No Payment</span>
                                        @else
                                        <ul style="list-style:none; margin:0; padding:0;">
                                            @foreach($sale->payments as $p)
                                            <li>
                                                @if($p->method === 'bank')
                                                <span class="badge bg-primary">Bank</span>
                                                <span>
                                                    {{ $p->bank->name ?? 'Bank' }}
                                                    — Rs. {{ number_format($p->amount, 2) }}
                                                    @if(!empty($p->reference_no))
                                                    (Ref: {{ $p->reference_no }})
                                                    @endif
                                                </span>
                                                @else
                                                <span class="badge bg-secondary">Counter</span>
                                                <span>Rs. {{ number_format($p->amount, 2) }}</span>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" class="sale-items-link"
                                            data-sale="{{ $sale->id }}">
                                            <ul style="list-style:none; margin:0; padding:0;">
                                                @foreach($sale->items as $item)
                                                <li>
                                                    {{ $item->batch->accessory->name ?? '-' }} x{{ $item->quantity }}
                                                    ({{ number_format($item->price_per_unit, 2) }} each
                                                    @if($discount > 0) — before discount @endif)
                                                </li>
                                                @endforeach
                                            </ul>
                                        </a>
                                    </td>
                                    <td>{{ $sale->comment }}</td>
                                    <td>
                                        @if($sale->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                            href="{{ route('sales.invoice', $sale->id) }}">
                                            Receipt
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(isset($todaysRefunds))
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-1 text-muted">Today’s Refund Value (Items)</h6>
                                    <h3 class="mb-0">Rs. {{ number_format($todaysRefunds['value_from_items'], 2) }}</h3>
                                    <small class="text-muted">
                                        {{ $todaysRefunds['returns'] }} return(s), {{ $todaysRefunds['lines'] }} item
                                        line(s)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-1 text-muted">Refunds Paid Out Today</h6>
                                    <h3 class="mb-0">Rs. {{ number_format($todaysRefunds['paid_out_total'], 2) }}</h3>
                                    @if(!empty($todaysRefunds['paid_by_method']) &&
                                    count($todaysRefunds['paid_by_method']))
                                    <small class="text-muted">
                                        @foreach($todaysRefunds['paid_by_method'] as $method => $amt)
                                        {{ ucfirst($method) }}: Rs. {{ number_format($amt, 2) }}@if(!$loop->last),
                                        @endif
                                        @endforeach
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="mb-1 text-muted">Net Effect (Value − Paid)</h6>
                                    <h3 class="mb-0">Rs. {{ number_format($todaysRefunds['net_effect'], 2) }}</h3>
                                    <small class="text-muted">Positive = credit notes created but not yet paid
                                        out</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Processing overlay --}}
<div id="loading-overlay" style="
  display:none;
  position:fixed;
  top:0; left:0; right:0; bottom:0;
  z-index:99999;
  background:rgba(255,255,255,0.5);
  backdrop-filter: blur(6px);
  justify-content:center;
  align-items:center;">
    <div style="background: #fff9; padding:28px 32px; border-radius:16px; box-shadow:0 4px 24px #0003;">
        <span style="font-size:1.4em; font-weight:600;">
            <i class="fa fa-spinner fa-spin"></i>
            Processing Sale, Please wait...
        </span>
    </div>
</div>

<script>
    // --- INIT --- //
  $(document).ready(function () {
    $('#manual_batch_select').select2({
      placeholder: "Select a Batch",
      allowClear: true,
      width: '100%'
    });

    $('#vendor_id').select2({
      placeholder: "Select a vendor",
      allowClear: true,
      width: '100%'
    });

    // Toggle bank fields
    document.addEventListener('change', function(e) {
      if (e.target && e.target.name === 'payment_method') {
        const isBank = e.target.value === 'bank';
        document.getElementById('bank-select-wrap').style.display = isBank ? '' : 'none';
        document.getElementById('bank-ref-wrap').style.display = isBank ? '' : 'none';
      }
    });

    // Vendor extra fields + balance fetch
    $('#vendor_id').on('change', function () {
      const vendorId = $(this).val();
      const extraFields = document.getElementById('vendor-extra-fields');
      const balanceInput = document.getElementById('vendor_balance');

      if (vendorId) {
        extraFields.style.display = '';
        balanceInput.value = 'Loading...';

        fetch(`/api/vendor-balance/${vendorId}`)
          .then(res => res.json())
          .then(data => { balanceInput.value = data.balance; })
          .catch(() => { balanceInput.value = 'Error loading balance'; });
      } else {
        extraFields.style.display = 'none';
        balanceInput.value = '';
      }
    });

    // Ensure discount fields start unlocked
    handleDiscountMode('amount');
  });

  // Enter triggers scan
  document.getElementById('barcode_search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      scanBarcode();
    }
  });

  // Mobile input normalization
  document.getElementById('customer_mobile').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (!this.value.startsWith('923')) {
      this.value = '923' + this.value.replace(/^923*/, '');
    }
    if (this.value.length > 12) {
      this.value = this.value.slice(0, 12);
    }
  });

  // Show/Hide mobile for walk-in
  document.getElementById('vendor_id').addEventListener('change', function () {
    const mobileRow = document.getElementById('customer_mobile_row');
    if (!this.value) {
      mobileRow.style.display = '';
    } else {
      mobileRow.style.display = 'none';
      document.getElementById('customer_mobile').value = '';
    }
  });
  document.getElementById('vendor_id').dispatchEvent(new Event('change'));

  // --- CART LOGIC --- //
  let cart = [];

  function scanBarcode() {
    const code = document.getElementById('barcode_search').value.trim();
    if (!code) return alert('Enter or scan a barcode!');

    const batch = window.batchData[code];
    if (!batch) return alert('Barcode not found in available batches!');

    const qty = prompt('Quantity to add from batch ' + code + ' (Max: ' + batch.qty_remaining + '):', 1);
    if (!qty || isNaN(qty) || qty <= 0 || qty > batch.qty_remaining) return alert('Invalid quantity!');

    const existing = cart.find(i => i.barcode === batch.barcode);
    if (existing) {
      existing.qty = Number(existing.qty) + Number(qty);
    } else {
      cart.push({
        barcode: batch.barcode,
        accessory: batch.accessory,
        qty: Number(qty),
        price: Number(batch.price)
      });
    }

    renderCart();
    document.getElementById('barcode_search').value = '';
  }

  function addSelectedBatch() {
    const select = document.getElementById('manual_batch_select');
    const code = select.value;
    if (!code) return alert('Select a batch to add!');

    const batch = window.batchData[code];
    if (!batch) return alert('Batch not found!');

    const qty = prompt('Quantity to add from batch ' + code + ' (Max: ' + batch.qty_remaining + '):', 1);
    if (!qty || isNaN(qty) || qty <= 0 || qty > batch.qty_remaining) return alert('Invalid quantity!');

    const existing = cart.find(i => i.barcode === batch.barcode);
    if (existing) {
      existing.qty = Number(existing.qty) + Number(qty);
    } else {
      cart.push({
        barcode: batch.barcode,
        accessory: batch.accessory,
        qty: Number(qty),
        price: Number(batch.price)
      });
    }

    renderCart();
  }

  function cartSubtotal() {
    return cart.reduce((t, item) => t + (Number(item.price) * Number(item.qty)), 0);
  }

  // ✅ Only one discount mode should be active
  function handleDiscountMode(mode) {
    const amountEl  = document.getElementById('cart_discount');
    const percentEl = document.getElementById('cart_discount_percent');

    const amount  = parseFloat(amountEl.value || '0') || 0;
    const percent = parseFloat(percentEl.value || '0') || 0;

    if (mode === 'amount') {
      if (amount > 0) {
        percentEl.value = '';
        percentEl.disabled = true;
      } else {
        percentEl.disabled = false;
      }
    } else {
      if (percent > 0) {
        amountEl.value = '';
        amountEl.disabled = true;
      } else {
        amountEl.disabled = false;
      }
    }

    renderCart();
  }

  function renderCart() {
    const tbody = document.querySelector('#sale-cart-table tbody');
    tbody.innerHTML = "";

    cart.forEach((item, i) => {
      const lineSubtotal = Number(item.price) * Number(item.qty);
      tbody.innerHTML += `<tr>
        <td>${item.barcode}</td>
        <td>${item.accessory}</td>
        <td><input type="number" value="${item.qty}" min="1" style="width:50px;" onchange="updateQuantity(${i}, this.value)"></td>
        <td><input type="number" value="${Number(item.price).toFixed(2)}" min="0" step="0.01" style="width:70px;" onchange="updatePrice(${i}, this.value)"></td>
        <td>${lineSubtotal.toFixed(2)}</td>
        <td><button type="button" onclick="removeCartItem(${i})" style="background:#f33;color:#fff;padding:4px 10px;border:none;border-radius:3px;">Remove</button></td>
      </tr>`;
    });

    const subtotal = cartSubtotal();

    const discountAmount  = parseFloat(document.getElementById('cart_discount').value) || 0;
    const discountPercent = parseFloat(document.getElementById('cart_discount_percent').value) || 0;

    let discount = 0;
    if (discountPercent > 0) {
      discount = subtotal * (discountPercent / 100);
    } else {
      discount = discountAmount;
    }
    if (discount > subtotal) discount = subtotal;

    const grandTotal = Math.max(subtotal - discount, 0);
    document.getElementById('cart-total').textContent = grandTotal.toFixed(2);
  }

  function updateQuantity(i, newQty) {
    const q = Number(newQty);
    if (isNaN(q) || q <= 0) return;
    cart[i].qty = q;
    renderCart();
  }

  function updatePrice(i, newPrice) {
    const p = Number(newPrice);
    if (isNaN(p) || p < 0) return;
    cart[i].price = p;
    renderCart();
  }

  function removeCartItem(i) {
    cart.splice(i, 1);
    renderCart();
  }

  // --- CHECKOUT --- //
  function checkoutSale() {
    if (!cart.length) return alert("Cart is empty!");

    // Lock UI
    document.getElementById('loading-overlay').style.display = 'flex';
    const btn = document.getElementById('checkout-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

    const vendor_id       = document.getElementById('vendor_id').value || null;
    const customer_name   = document.getElementById('customer_name').value || null;
    const customer_mobile = document.getElementById('customer_mobile') ? document.getElementById('customer_mobile').value : '';
    const comment         = (document.getElementById('sale_comment').value || '').trim() || null;

    // Discount inputs (only one allowed)
    const discount_amount  = parseFloat(document.getElementById('cart_discount').value) || 0;
    const discount_percent = parseFloat(document.getElementById('cart_discount_percent').value) || 0;

    if (discount_amount > 0 && discount_percent > 0) {
      alert('Use only ONE discount: either Discount Amount OR Discount %.');
      btn.disabled = false;
      btn.innerHTML = 'Checkout & Print Invoice';
      document.getElementById('loading-overlay').style.display = 'none';
      return;
    }

    // Compute net total
    const subtotal = cart.reduce((t, it) => t + (Number(it.price) * Number(it.qty)), 0);

    let discount = 0;
    if (discount_percent > 0) {
      discount = subtotal * (discount_percent / 100);
    } else {
      discount = discount_amount;
    }
    if (discount > subtotal) discount = subtotal;

    const netTotal = Math.max(subtotal - discount, 0);

    // Payment UI
    const pay_amount_el   = document.getElementById('pay_amount'); // only visible for vendor
    const raw_pay_amount  = pay_amount_el ? parseFloat(pay_amount_el.value || '0') : 0;

    let method = 'counter';
    const methodInput = document.querySelector('input[name="payment_method"]:checked');
    if (methodInput) method = methodInput.value;

    const bank_id_el   = document.getElementById('bank_id');
    const bank_ref_el  = document.getElementById('bank_reference');
    const bank_id      = bank_id_el ? bank_id_el.value : '';
    const reference_no = bank_ref_el ? bank_ref_el.value.trim() : '';

    // Build payments[]
    const payments = [];
    if (vendor_id) {
      // Vendor can pay partial now
      if (raw_pay_amount > 0) {
        if (method === 'bank' && !bank_id) {
          alert('Please select a bank for the bank payment.');
          btn.disabled = false;
          btn.innerHTML = 'Checkout & Print Invoice';
          document.getElementById('loading-overlay').style.display = 'none';
          return;
        }
        payments.push({
          method: method === 'bank' ? 'bank' : 'counter',
          bank_id: method === 'bank' ? Number(bank_id) : null,
          amount: Number(raw_pay_amount),
          reference_no: method === 'bank' ? (reference_no || null) : null
        });
      }
    } else {
      // Walk-in: always record a full payment for net total
      if (method === 'bank' && !bank_id) {
        alert('Please select a bank for the bank payment.');
        btn.disabled = false;
        btn.innerHTML = 'Checkout & Print Invoice';
        document.getElementById('loading-overlay').style.display = 'none';
        return;
      }
      payments.push({
        method: method === 'bank' ? 'bank' : 'counter',
        bank_id: method === 'bank' ? Number(bank_id) : null,
        amount: Number(netTotal),
        reference_no: method === 'bank' ? (reference_no || null) : null
      });
    }

    // Payload
    const payload = {
      vendor_id,
      customer_name,
      customer_mobile,

      // ✅ Send both; backend enforces only one > 0
      cart_discount: discount_amount,
      cart_discount_percent: discount_percent,

      comment,

      // legacy single-payment hints (controller uses only if payments[] missing)
      pay_amount: vendor_id ? Number(raw_pay_amount) : Number(netTotal),
      payment_method: method,
      bank_id: method === 'bank' ? (bank_id ? Number(bank_id) : null) : null,
      reference_no: method === 'bank' ? (reference_no || null) : null,

      payments,

      items: cart.map(i => ({
        barcode: i.barcode,
        qty: Number(i.qty),
        price: Number(i.price)
      }))
    };

    fetch('/pos/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify(payload)
    })
    .then(async res => {
      const contentType = res.headers.get("content-type") || "";
      if (contentType.includes("application/json")) return res.json();
      const text = await res.text();
      throw new Error("Server did not return JSON. Response was: " + text.substring(0, 400));
    })
    .then(data => {
      if (data.success) {
        window.open('/pos/invoice/' + data.invoice_number, '_blank');
        setTimeout(() => window.location.reload(), 700);
      } else {
        console.error(data);
        alert("Error: " + (data.message || 'Sale failed.'));
        btn.disabled = false;
        btn.innerHTML = 'Checkout & Print Invoice';
        document.getElementById('loading-overlay').style.display = 'none';
      }
    })
    .catch(error => {
      console.error(error);
      alert("Unexpected error: " + error.message);
      btn.disabled = false;
      btn.innerHTML = 'Checkout & Print Invoice';
      document.getElementById('loading-overlay').style.display = 'none';
    });
  }
</script>

@endsection