@extends('user_navbar')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Barcode Modal */
    .barcode-modal {
        display: none;
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translate(-50%, 0);
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px #0003;
        z-index: 9999;
        min-width: 300px;
    }
</style>

{{-- Delete Account --}}
<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete Batch?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="deleteMobile" action="{{ route('deletebatch') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-body">

                        <div class="mb-1">
                            <label for="name" class="form-label">Are you sure you want to delete this Entry?</label>
                            <input class="form-control" hidden    name="id" id="did" value="">
                            {{-- <input type="text" class="form-control" id="dname" name="name" readonly required> --}}
                        </div>

                        <div class="mb-1">
                            <label for="name" class="form-label">Enter Password to Delete This Batch</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                       

                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> No
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Yes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
{{-- End Delete Account --}}

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

                        <div class="mb-1">
                            <label for="barcode" class="form-label">Barcode (Optional)</label>
                            <input type="text" class="form-control" name="barcode" id="barcode"
                                placeholder="Enter barcode (leave empty to auto-generate)" value="{{ old('barcode') }}">
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
                <h5 class="modal-title" id="exampleModalLabel">Edit Barcode</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="editAccessory" action="{{ route('batch.update') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-body">
                        <div class="mb-1">
                            {{-- <label for="mobile_name" class="form-label">Edit Barecode</label> --}}
                            <input class="form-control" type="hidden" name="id" id="id" value="Update">
                            {{-- <input type="text" class="form-control" id="name" name="name" required> --}}
                        </div>
                        <div class="mb-1">
                            <label for="barcode" class="form-label">Barcode (Optional)</label>
                            <input type="text" class="form-control" name="barcode" id="oldbarcode"
                                placeholder="Enter barcode (leave empty to auto-generate)">
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
<div id="barcode-modal" class="barcode-modal print-area">
    <div id="barcode-modal-content"></div>
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

            <div class="ml-1">
                <form method="GET" action="{{ route('batches.index') }}" class="mb-3 d-flex align-items-center"
                    style="gap:8px; flex-wrap:wrap;">
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}"
                        style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}"
                        style="max-width: 180px;">

                    <select name="group_id" class="form-control" style="max-width: 220px;">
                        <option value="">All Groups</option>
                        @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (string)$g->id === request('group_id') ? 'selected' : '' }}>
                            {{ $g->name }}
                        </option>
                        @endforeach
                    </select>

                    <select name="company_id" class="form-control" style="max-width: 240px;">
                        <option value="">All Companies</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ (string)$c->id === request('company_id') ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('batches.index') }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>

            <div class="ml-1">
                <h2>Total Purchase Amount: {{ $totalPurchasePrice }}</h2>
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
                                    <th>Group</th>
                                    <th>Company</th>
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
                                    <td>{{ optional($batch->accessory->group)->name ?? '-' }}</td>
                                    <td>{{ optional($batch->accessory->company)->name ?? '-' }}</td>
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
                                    <td>
                                        <a href="" onclick="edit({{ $batch->id }})" data-toggle="modal"
                                            data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i> |
                                            <a href="#" onclick="remove({{ $batch->id }})" data-toggle="modal" data-target="#exampleModal2">
                                                <i style="color:red" class="feather icon-trash"></i>
                                            </a>
                                        </a>
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
    // Submit button loading state
    $(document).ready(function() {
        $('#storeMobile').on('submit', function() {
            $('#storeButton').html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        });
    });

    // Qty x purchase => pay amount auto fill
    const qtyInput = document.getElementById('qty_purchased');
    const priceInput = document.getElementById('purchase_price');
    const payAmountInput = document.getElementById('pay_amount');

    function calculatePayAmount() {
        const qty = Number(qtyInput?.value || 0);
        const price = Number(priceInput?.value || 0);
        const total = qty * price;
        if (!isFinite(total)) return;
        payAmountInput.value = total.toFixed(2);
    }

    if (qtyInput && priceInput && payAmountInput) {
        qtyInput.addEventListener('input', calculatePayAmount);
        priceInput.addEventListener('input', calculatePayAmount);
    }

    // DataTable
    $(document).ready(function () {
        $('#accessoryTable').DataTable({ order: [[0, 'desc']] });
    });

    // Select2 in modal
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

    // Hide barcode modal
    function hideBarcodeModal() {
        document.getElementById('barcode-modal').style.display = 'none';
    }

    // Fetch barcode details and show modal
    async function showBarcodeAjax(batchId) {
        try {
            const res = await fetch(`/batches/${batchId}/barcode`, { headers: { 'Accept': 'application/json' }});
            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to fetch barcode info');
            }

            const batch = data.batch || {};
            const accessoryName = (batch.accessory ?? batch.accessory_name ?? '').toString();

            const c = document.getElementById('barcode-modal-content');
            c.textContent = '';

            const h = document.createElement('h4');
            h.textContent = `Batch Barcode (${batch.barcode ?? ''})`;

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('id', 'barcode-svg');

            const fields = [
                ['Accessory', accessoryName],
                ['Selling Price', batch.selling_price ?? ''],
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

            const modalEl = document.getElementById('barcode-modal');
            modalEl.dataset.batchBarcode  = String(batch.barcode ?? '');
            modalEl.dataset.accessoryName = accessoryName;
            modalEl.dataset.sellingPrice  = String(batch.selling_price ?? '');

            document.getElementById('barcode-modal').style.display = 'block';

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

    // Print sticker (Barcode + Batch# + Selling Price + Accessory Name)
    function printBarcodeModal(labelCount = 1) {
        const modalEl = document.getElementById('barcode-modal');

        let batchNumber   = modalEl?.dataset?.batchBarcode || '';
        let accessoryName = modalEl?.dataset?.accessoryName || '';
        let sellingPrice  = modalEl?.dataset?.sellingPrice || '';

        // Format price
        let priceText = '';
        const p = Number(sellingPrice);
        if (sellingPrice !== '' && !Number.isNaN(p)) {
            priceText = 'Rs. ' + p.toFixed(2);
        } else if (sellingPrice) {
            priceText = 'Rs. ' + sellingPrice;
        }

        const esc = (s) => String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        const safeBatch = esc(batchNumber);
        const safeName  = esc(accessoryName);
        const safePrice = esc(priceText);

        const win = window.open('', '', 'width=900,height=700');
        win.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=50mm, height=25mm, initial-scale=1.0">
  <title>Print Barcodes</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
  <style>
    @page { size: 50mm 25mm; margin: 0; }
    html, body { width: 50mm; height: 25mm; margin: 0; padding: 0; }
    .barcode-box {
      width: 50mm; height: 25mm; page-break-after: always;
      display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
    }
    .barcode-box svg { width: 120px !important; height: 45px !important; display:block; }
    .barcode-label { font-size: 4.5mm; font-weight: 700; margin-top: 0.8mm; }
    .barcode-price { font-size: 3.6mm; font-weight: 700; margin-top: 0.6mm; }
    .barcode-name  { font-size: 3.6mm; font-weight: 500; margin-top: 0.6mm; line-height: 1.05; max-width: 46mm;
                     white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  </style>
</head>
<body>
  ${Array.from({ length: labelCount }).map((_, i) => `
    <div class="barcode-box">
      <svg id="barcode_${i}"></svg>
      <div class="barcode-label">${safeBatch}</div>
    
      <div class="barcode-name">${safeName}</div>
    </div>
  `).join('')}
  <script>
    window.onload = function() {
      for (let i = 0; i < ${labelCount}; i++) {
        JsBarcode("#barcode_" + i, "${batchNumber}", {
          format: "CODE128",
          width: 2,
          height: 40,
          displayValue: false
        });
      }
      setTimeout(() => window.print(), 300);
    };
  <\/script>
</body>
</html>`);
        win.document.close();
    }

    // <div class="barcode-price">${safePrice}</div>

    // Auto hide alerts
    setTimeout(() => document.getElementById('successMessage')?.remove(), 4000);
    setTimeout(() => document.getElementById('dangerMessage')?.remove(), 6000);

    // Expose
    window.showBarcodeAjax = showBarcodeAjax;
    window.hideBarcodeModal = hideBarcodeModal;
    window.printBarcodeModal = printBarcodeModal;

    function edit(value) {
    console.log(value);
    var id = value;
    $.ajax({
    type: "GET",
    url: '/batchedit/' + id,
    success: function (data) {
    $("#editAccessory").trigger("reset");
    console.log(data.result);
    
    $('#id').val(data.result.id);
    $('#oldbarcode').val(data.result.barcode);
    
    
    
    
    },
    error: function (error) {
    console.log('Error:', error);
    }
    });
    }

    function remove(value) {
    console.log(value);
    var id = value;
    $.ajax({
    type: "GET",
    url: '/batchedit/' + id,
    success: function (data) {
    $("#deleteMobile").trigger("reset");
    
    $('#did').val(data.result.id);
    // $('#dname').val(data.result.name);
    
    },
    error: function (error) {
    console.log('Error:', error);
    }
    });
    }
</script>

@endsection