@extends('user_navbar')
@section('content')

<!-- Modal for Sale Items and Return Form -->
<div class="modal fade" id="saleItemsModal" tabindex="-1" aria-labelledby="saleItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="" id="return-items-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleItemsModalLabel">Sale Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="saleItemsModalBody">
                    <!-- Sale items and return fields will be loaded here -->
                    <div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Return</button>
                    <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                        <i class="feather icon-x"></i> Close
                    </button>
                </div>
            </div>
        </form>
    </div>
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
                <form method="GET" action="{{ route('sales.all') }}" class="mb-3 d-flex align-items-center">
                    <input type="date" class="form-control mr-2" name="start_date" value="{{ request('start_date') }}"
                        style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control mr-2" name="end_date" value="{{ request('end_date') }}"
                        style="max-width: 180px;">
                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('sales.all') }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>
            @php
            $userId = auth()->id();
            @endphp
            @if (in_array($userId, [1, 2]))
            {{-- Expanded totals row: keeps your two totals, adds Profit + Transferred (Bank/Counter) --}}
            <div class="row ml-1 mb-2">
                <div class="col-12 col-md-3">
                    <h5>Total Selling Price: Rs. {{ number_format($totalSellingPrice, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5>Total Paid Price: Rs. {{ number_format($totalPaidPrice, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5>Total Profit: Rs. {{ number_format($totalProfit ?? 0, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5 class="mb-0">Transferred</h5>
                    <div class="small">
                        Bank: <strong>Rs. {{ number_format($totalTransferredBank ?? 0, 2) }}</strong>
                        &nbsp;|&nbsp;
                        Counter: <strong>Rs. {{ number_format($totalTransferredCounter ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1 ">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">All Sales</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="loginTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Customer/Vendor</th>
                                    <th>Total</th>
                                    <th>Paid Amount</th>
                                    <th>Items</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="sales-table-body">
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
                                    </td>
                                    <td>
                                        <strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                                        @if($discount > 0)
                                        <div style="font-size:12px; color:#666; line-height:1.2; margin-top:4px;">
                                            <div>Subtotal: Rs. {{ number_format($subtotal, 2) }}</div>
                                            <div>Discount: - Rs. {{ number_format($discount, 2) }}</div>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Pay Amount (unchanged logic) --}}
                                        @if($sale->vendor)
                                        Rs. {{ number_format($sale->pay_amount ?? 0, 2) }}
                                        @else
                                        Rs. {{ number_format($sale->total_amount, 2) }}
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
                                                    @if($discount > 0)
                                                    — before discount
                                                    @endif
                                                    )
                                                </li>
                                                @endforeach
                                            </ul>
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                            href="{{ route('sales.invoice', $sale->id) }}">
                                            Receipt
                                        </a>
                                        {{-- <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('sales.show',$sale->id) }}">View</a> --}}
                                    </td>
                                    <td>
                                        @if($sale->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
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
    document.addEventListener('DOMContentLoaded', function() {
    // Open modal and load sale items
    document.querySelectorAll('.sale-items-link').forEach(function(link) {
        link.addEventListener('click', function() {
            let saleId = this.getAttribute('data-sale');
            // Set form action dynamically
            document.getElementById('return-items-form').action = '/sales/' + saleId + '/return';

            // Show modal and loading state
            let modal = new bootstrap.Modal(document.getElementById('saleItemsModal'));
            document.getElementById('saleItemsModalBody').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
            modal.show();

            // Fetch sale items via AJAX
            fetch('/sales/' + saleId + '/items', {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let html = '<table class="table"><thead><tr><th>Item</th><th>Qty Sold</th><th>Return Qty</th></tr></thead><tbody>';
                        data.items.forEach(function(item) {
                            html += `<tr>
                                <td>${item.accessory}</td>
                                <td>${item.quantity}</td>
                                <td>
                                    <input type="number" min="0" max="${item.quantity}" class="form-control return-qty" name="return_qty[${item.id}]" value="0">
                                </td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        html += `<input type="hidden" name="sale_id" value="${saleId}">`;
                        document.getElementById('saleItemsModalBody').innerHTML = html;
                    } else {
                        document.getElementById('saleItemsModalBody').innerHTML = '<div class="text-danger">Could not load items.</div>';
                    }
                })
                .catch(() => {
                    document.getElementById('saleItemsModalBody').innerHTML = '<div class="text-danger">Error loading items.</div>';
                });
        });
    });

    // Handle return form submit (AJAX)
    document.getElementById('return-items-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = e.target;
        let actionUrl = form.action;

        // Prepare FormData (so it works with @csrf and arrays)
        let formData = new FormData(form);

        // Optionally, disable button to prevent double-submits
        let submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerText = "Processing...";

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit Return";

            if (!data) {
                alert("Unexpected server response.");
                return;
            }

            if (data.success) {
                alert(data.message || "Return processed successfully!");
                // Close modal
                let modalEl = document.getElementById('saleItemsModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                // Reload sales list
                location.reload();
            } else {
                alert(data.message || "Could not process return.");
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerText = "Submit Return";
            alert("Server error. Try again.");
        });
    });
});
</script>

@endsection