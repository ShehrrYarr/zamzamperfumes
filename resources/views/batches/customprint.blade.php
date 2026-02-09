@extends('user_navbar')
@section('content')

{{-- Store Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Batch</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="storeMobile" action="{{ route('batches.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="accessory_id" class="form-label">Accessory</label>
                            <select class="form-control" name="accessory_id" id="accessorySelect1" required>
                                <option value="">Select Accessory</option>
                                @foreach ($accessories as $accessory)
                                <option value="{{ $accessory->id }}">{{ $accessory->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-control" name="vendor_id" id="vendorSelect1" required>
                                <option value="">Select Vendor</option>
                                @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->mobile_no }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date" required>
                        </div>

                        <div class="mb-1">
                            <label for="pay_amount" class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description" placeholder="Enter Description">
                        </div>

                        <div class="mb-1">
                            <label for="qty_purchased" class="form-label">Quantity Purchased</label>
                            <input type="number" class="form-control" id="qty_purchased" name="qty_purchased" min="1"
                                required>
                        </div>

                        <div class="mb-1">
                            <label for="purchase_price" class="form-label">Purchase Price (per unit)</label>
                            <input type="number" class="form-control" id="purchase_price" name="purchase_price"
                                step="0.01" min="0" required>
                        </div>
                        <div class="mb-1">
                            <label for="selling_price" class="form-label">Selling Price (per unit)</label>
                            <input type="number" class="form-control" name="selling_price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-1">
                            <label for="pay_amount" class="form-label">Pay amount</label>
                            <input type="number" id="pay_amount" class="form-control" name="pay_amount">
                        </div>



                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="storeButton">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
{{-- End Store Modal --}}

{{-- Edit Modal --}}

<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Accessory</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="editAccessory" action="{{ route('accessories.update') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="mobile_name" class="form-label">Accessory Name</label>
                            <input class="form-control" type="hidden" name="id" id="id" value="Update">
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>



                        <div class="mb-1">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" name="description" id="description">
                        </div>
                        <div class="mb-1">
                            <label for="password" class="form-label">Edit Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- End Edit Modal --}}

{{-- Barcode Modal --}}
<div id="barcode-modal" class="barcode-modal print-area"
    style="display:none; position:fixed; top:20%; left:50%; transform:translate(-50%, 0); background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 8px #0003; z-index:9999; min-width:300px;">
    <div id="barcode-modal-content"></div>

    {{-- Sticker size controls (Width × Height + Unit) --}}
    <div class="d-flex align-items-center justify-content-start mt-2" id="sticker-size-controls"
        style="gap:10px; flex-wrap:wrap;">
        <div>
            <label class="mb-0" style="font-weight:600;">Sticker Size</label>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="number" id="sticker_width" class="form-control" style="width:90px;" min="10" step="1"
                    value="50">
                <span>×</span>
                <input type="number" id="sticker_height" class="form-control" style="width:90px;" min="10" step="1"
                    value="25">
                <select id="sticker_unit" class="form-control" style="width:90px;">
                    <option value="mm" selected>mm</option>
                    <option value="in">inch</option>
                </select>
            </div>
            <small class="text-muted">Tip: 50×25 mm ≈ 2×1 inch</small>
        </div>
    </div>

    <button class="btn btn-primary btn-sm" onclick="printBarcodeModal()">
        <i class="fa fa-print"></i> Print
    </button>
    <button class="btn btn-secondary btn-sm" onclick="hideBarcodeModal()">
        <i class="fa fa-times"></i> Close
    </button>
</div>






<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
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
            <div class="ml-1">
                <form method="GET" action="{{ route('batches.index') }}" class="mb-3 d-flex align-items-center">
                    <input type="date" class="form-control mr-2" name="start_date" value="{{ request('start_date') }}"
                        style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control mr-2" name="end_date" value="{{ request('end_date') }}"
                        style="max-width: 180px;">
                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('batches.index') }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>
            <div class="ml-1">
                <h2>Total Purchase Amount: {{$totalPurchasePrice}}</h2>
            </div>

            <button type="button" class="btn btn-primary ml-1" data-toggle="modal" data-target="#exampleModal">
                <i class="bi bi-plus"></i> Add Batch
            </button>
            <a type="button" class="btn btn-primary ml-1" href="/batches/bulk">
                <i class="bi bi-plus"></i> Bulk Add Batches
            </a>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1 ">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Accessories</h4>

                    </div>
                    <div class="table-responsive">
                        <table id="accessoryTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Created By</th>

                                    <th>Accessory</th>
                                    <th>Vendor</th>
                                    <th>Qty Purchased</th>
                                    <th>Qty Remaining</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Description</th>
                                    <th>Purchase Date</th>
                                    <th>Barcode</th>
                                    <th>Print Barcode</th>
                                    <th>Actions</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batches as $batch)
                                <tr>
                                    <td>{{ $batch->created_at }}</td>
                                    <td>{{ $batch->user->name }}</td>
                                    <td>{{ $batch->accessory->name ?? '-' }}</td>
                                    <td>{{ $batch->vendor->name ?? '-' }}</td>
                                    <td>{{ $batch->qty_purchased }}</td>
                                    <td>{{ $batch->qty_remaining }}</td>
                                    <td>{{ $batch->purchase_price }}</td>
                                    <td>{{ $batch->selling_price }}</td>
                                    <td>{{ $batch->description }}</td>
                                    <td>{{ $batch->purchase_date }}</td>
                                    <td>{{ $batch->barcode }}</td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="showBarcodeAjax({{ $batch->id }})">
                                            <i class="fa fa-barcode"></i> View Barcode
                                        </button>
                                    </td>
                                    <td><a href="" onclick="edit({{ $batch->id }})" data-toggle="modal"
                                            data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
    $('#storeMobile').on('submit', function() {
    // Change button text to "Saving..."
    $('#storeButton').html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    });
    });

  // ===== Qty × Purchase Price => Pay Amount (single add modal) =====
  const qtyInput = document.getElementById('qty_purchased');
  const priceInput = document.getElementById('purchase_price');
  const payAmountInput = document.getElementById('pay_amount');

  function calculatePayAmount() {
    const qty   = Number(qtyInput?.value || 0);
    const price = Number(priceInput?.value || 0);
    const total = qty * price;
    if (!isFinite(total)) return;
    payAmountInput.value = total.toFixed(2);
  }

  if (qtyInput && priceInput && payAmountInput) {
    qtyInput.addEventListener('input', calculatePayAmount);
    priceInput.addEventListener('input', calculatePayAmount);
  }

  // ===== DataTable (batches list) =====
  $(document).ready(function () {
    $('#accessoryTable').DataTable({ order: [[0, 'desc']] });
  });

  // ===== Select2 for Add Batch modal selects =====
  $(document).ready(function () {
    $('#vendorSelect1').select2({
      placeholder: "Select a vendor",
      allowClear: true,
      width: '100%'
    });
    $('#accessorySelect1').select2({
      placeholder: "Select an Accessory",
      allowClear: true,
      width: '100%'
    });
  });

  // ===== Barcode Modal helpers =====
  function hideBarcodeModal() {
    document.getElementById('barcode-modal').style.display = 'none';
  }

  // Build modal content safely (no raw innerHTML with untrusted strings)
  async function showBarcodeAjax(batchId) {
    try {
      const res = await fetch(`/batches/${batchId}/barcode`, { headers: { 'Accept': 'application/json' }});
      const data = await res.json();

      if (!res.ok || !data.success) {
        throw new Error(data.message || 'Failed to fetch barcode info');
      }

      // Expect data.batch like:
      // { barcode, accessory, vendor, qty_purchased, qty_remaining, purchase_price, selling_price, purchase_date }
      const batch = data.batch || {};
      // Accessory name might be in "accessory" or "accessory_name" depending on your API
      const accessoryName = (batch.accessory ?? batch.accessory_name ?? '').toString();

      const c = document.getElementById('barcode-modal-content');
      c.textContent = ''; // clear

      const h = document.createElement('h4');
      h.textContent = `Batch Barcode (${batch.barcode ?? ''})`;

      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('id', 'barcode-svg');

      const fields = [
        ['Accessory', accessoryName],
        ['Vendor', batch.vendor ?? ''],
        ['Qty Purchased', batch.qty_purchased ?? ''],
        ['Qty Remaining', batch.qty_remaining ?? ''],
        ['Purchase Price', batch.purchase_price ?? ''],
        ['Purchase Date', batch.purchase_date ?? ''],
      ];

      c.appendChild(h);
      c.appendChild(svg);
      for (const [label, value] of fields) {
        const p = document.createElement('p');
        const strong = document.createElement('strong');
        strong.textContent = `${label}: `;
        p.appendChild(strong);
        p.appendChild(document.createTextNode(String(value)));
        c.appendChild(p);
      }

      // Save values for printing
      const modalEl = document.getElementById('barcode-modal');
      modalEl.dataset.batchBarcode = String(batch.barcode ?? '');
      modalEl.dataset.accessoryName = accessoryName;

      // Show modal
      document.getElementById('barcode-modal').style.display = 'block';

      // Render barcode
      JsBarcode("#barcode-svg", String(batch.barcode ?? ''), {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 40,
        displayValue: false
      });

    } catch (err) {
      console.error(err);
      alert(err.message || 'Could not fetch barcode info!');
    }
  }

  // --- Remember last sticker size (optional) ---
  (function rememberSize() {
    try {
      const w = localStorage.getItem('sticker_w');
      const h = localStorage.getItem('sticker_h');
      const u = localStorage.getItem('sticker_u');
      if (w && document.getElementById('sticker_width'))  document.getElementById('sticker_width').value  = w;
      if (h && document.getElementById('sticker_height')) document.getElementById('sticker_height').value = h;
      if (u && document.getElementById('sticker_unit'))   document.getElementById('sticker_unit').value   = u;
    } catch(e){}
  })();

  // Print: barcode + batch number + accessory name, auto-sized to chosen label dimensions (mm/inch)
  function printBarcodeModal(labelCount = 1) {
    const modalEl = document.getElementById('barcode-modal');
    let batchNumber   = modalEl?.dataset?.batchBarcode || '';
    let accessoryName = modalEl?.dataset?.accessoryName || '';

    // Fallback if dataset missing
    if (!accessoryName) {
      const p = Array.from(document.querySelectorAll('#barcode-modal-content p'))
        .find(el => el.textContent.trim().startsWith('Accessory:'));
      if (p) accessoryName = p.textContent.replace(/^Accessory:\s*/i, '').trim();
    }

    // Read size controls
    const wEl = document.getElementById('sticker_width');
    const hEl = document.getElementById('sticker_height');
    const uEl = document.getElementById('sticker_unit');

    const unit  = (uEl?.value === 'in') ? 'in' : 'mm';
    const width = Math.max(10, parseFloat(wEl?.value || '50'));
    const height= Math.max(10, parseFloat(hEl?.value || '25'));

    // Persist choice
    try {
      localStorage.setItem('sticker_w', String(width));
      localStorage.setItem('sticker_h', String(height));
      localStorage.setItem('sticker_u', unit);
    } catch(e){}

    // Compute sizes
    const shortSide = Math.min(width, height);
    const titleSize  = (unit === 'in') ? Math.max(0.11, shortSide * 0.09) : Math.max(2.8, shortSide * 0.09);
    const nameSize   = (unit === 'in') ? Math.max(0.09, shortSide * 0.075) : Math.max(2.2, shortSide * 0.075);
    const svgHeight  = (unit === 'in') ? Math.max(height * 0.60, 0.5) : Math.max(height * 0.60, 10);

    const esc = s => String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');

    const win = window.open('', '', 'width=900,height=700');

    win.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1, width=device-width">
  <title>Print Barcodes</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
  <style>
    @page { size: ${width}${unit} ${height}${unit}; margin: 0; }
    html, body {
      width: ${width}${unit};
      height: ${height}${unit};
      margin: 0; padding: 0;
    }
    .barcode-box {
      width: ${width}${unit};
      height: ${height}${unit};
      page-break-after: always;
      display:flex; flex-direction:column;
      align-items:center; justify-content:center; text-align:center;
      overflow:hidden;
    }
    .barcode-box svg {
      width: calc(${width}${unit} - ${unit === 'in' ? '0.20in' : '5mm'});
      height: ${svgHeight}${unit};
      display:block;
    }
    .barcode-label {
      font-weight: 700;
      margin-top: ${unit === 'in' ? '0.02in' : '0.5mm'};
      font-size: ${titleSize}${unit};
      line-height: 1.05;
      max-width: calc(${width}${unit} - ${unit === 'in' ? '0.20in' : '5mm'});
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .barcode-name {
      font-weight: 500;
      margin-top: ${unit === 'in' ? '0.01in' : '0.3mm'};
      font-size: ${nameSize}${unit};
      line-height: 1.05;
      max-width: calc(${width}${unit} - ${unit === 'in' ? '0.20in' : '5mm'});
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
  </style>
</head>
<body>
  ${Array.from({ length: labelCount }).map((_, i) => `
    <div class="barcode-box">
      <svg id="barcode_${i}"></svg>
      <div class="barcode-label">${esc(batchNumber)}</div>
      <div class="barcode-name">${esc(accessoryName)}</div>
    </div>
  `).join('')}
  <script>
    window.onload = function() {
      for (let i = 0; i < ${labelCount}; i++) {
        JsBarcode("#barcode_" + i, ${JSON.stringify(batchNumber)}, {
          format: "CODE128",
          width: ${unit === 'in' ? Math.max(0.01, width * 0.02).toFixed(3) : Math.max(1.2, width * 0.04).toFixed(2)},
          height: ${unit === 'in' ? (svgHeight * 96).toFixed(0) : (svgHeight * 3.78).toFixed(0)}, // use px internally; CSS height controls final render
          displayValue: false,
          margin: 0
        });
      }
      setTimeout(() => window.print(), 250);
    };
  <\/script>
</body>
</html>`);
    win.document.close();
  }

  // ===== Optional: auto-hide flash messages =====
  setTimeout(() => document.getElementById('successMessage')?.remove(), 4000);
  setTimeout(() => document.getElementById('dangerMessage')?.remove(), 6000);

  // Expose functions used by buttons
  window.showBarcodeAjax = showBarcodeAjax;
  window.hideBarcodeModal = hideBarcodeModal;
  window.printBarcodeModal = printBarcodeModal;
</script>


@endsection