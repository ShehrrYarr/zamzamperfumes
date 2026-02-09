@extends('user_navbar')
@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <div class="content-header row">
            </div>

            {{-- Image Banner --}}
            <div class="mb-2">
                <img src="{{ asset('images/zaitoonbanner.jpg') }}" alt=" Banner" class="img-fluid shadow rounded"
                    style="width: 100%; max-height: 250px; object-fit: cover;">
            </div>

            <!-- Grouped multiple cards for statistics starts here -->
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon primary d-flex justify-content-center mr-3">
                                            <a href="/accessories"> <i
                                                    class="icon p-1 fa fa-mobile customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalAccessoryCount}}</h3>
                                            <p class="sub-heading">Available Perfumes</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                    </span> -->
                                    </div>
                                </div>

                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/all"> <i
                                                    class="icon p-1 fa fa-mobile customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalSoldAccessories}}</h3>
                                            <p class="sub-heading">Sold Perfumes</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/pending"> <i
                                                    class="icon p-1 fa fa-cart-plus customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalPendingSalesCount}}</h3>
                                            <p class="sub-heading">Pending Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/approved"> <i
                                                    class="icon p-1 fa fa-cart-plus customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalApprovedSalesCount}}</h3>
                                            <p class="sub-heading">Approved Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>



            @if($lowStockAccessories->count())
            <div id="lowStockBox"
                style="margin: 24px 0; padding: 20px; background: #fff7e6; border: 1px solid #ffd580; border-radius: 12px; position: relative;">

                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                    <h4 style="color:#b32d2e; margin-bottom:12px;">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Reminder
                        <small id="lowStockFilterBadge" style="margin-left:8px; color:#7a4b00;"></small>
                    </h4>

                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        {{-- Companies chips --}}
                        @if(isset($lowStockCompanies) && $lowStockCompanies->count())
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-weight:700; color:#a34624;">Companies:</span>
                            @foreach($lowStockCompanies as $c)
                            <button type="button" class="chip chip-company" data-type="company" data-id="{{ $c['id'] }}"
                                style="border:none; background:#ffe28e; color:#7a4b00; padding:6px 10px; border-radius:999px; cursor:pointer;">
                                {{ $c['name'] }} ({{ $c['count'] }})
                            </button>
                            @endforeach
                        </div>
                        @endif

                        {{-- Groups chips --}}
                        @if(isset($lowStockGroups) && $lowStockGroups->count())
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-weight:700; color:#a34624;">Groups:</span>
                            @foreach($lowStockGroups as $g)
                            <button type="button" class="chip chip-group" data-type="group" data-id="{{ $g['id'] }}"
                                style="border:none; background:#ffe28e; color:#7a4b00; padding:6px 10px; border-radius:999px; cursor:pointer;">
                                {{ $g['name'] }} ({{ $g['count'] }})
                            </button>
                            @endforeach
                        </div>
                        @endif

                        {{-- Clear filter --}}
                        <button type="button" id="clearLowStockFilter"
                            style="border:none; background:#ffd580; color:#7a4b00; padding:6px 10px; border-radius:999px; cursor:pointer; display:none;">
                            Clear filter
                        </button>
                    </div>

                    <button id="toggleStockBtn"
                        style="background:#ffe28e;border:none;color:#b32d2e;padding:5px 16px;border-radius:5px;font-weight:bold;cursor:pointer;">
                        Maximize
                    </button>
                </div>

                <div style="overflow:hidden;" id="lowStockTableWrapper">
                    <table class="low-stock-table" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>Perfume Name</th>
                                <th>Company</th>
                                <th>Group</th>
                                <th>Minimum Qty</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="low-stock-tbody"></tbody>
                    </table>
                </div>
            </div>
            @else
            <div style="margin: 24px 0; padding: 15px; background: #eafdea; border-radius: 12px; color:#267a23;">
                All perfumes are above their minimum quantity.
            </div>
            @endif

            @php
            $userId = auth()->id();
            @endphp
            @if (in_array($userId, [1, 2]))
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">Rs. {{
                                                number_format($totalAccessoryAmount) }}
                                            </h3>
                                            <p class="sub-heading">Total Perfumes Cost</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{number_format($totalSoldAmount)}}</h3>
                                            <p class="sub-heading">Total Sold Perfumes</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="danger"><i class="fa fa-long-arrow-down"></i> 2.0%</small>
                                                                                    </span> -->
                                    </div>
                                </div>

                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalReceivable) }}</h3>
                                            <p class="sub-heading">Total Receivable</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalPendingSales) }}</h3>
                                            <p class="sub-heading">Total Pending Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">




                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalApprovedSales) }}</h3>
                                            <p class="sub-heading">Total Approved Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                                                    </span> -->
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .low-stock-table th,
                .low-stock-table td {
                    text-align: center;
                    padding: 12px 10px;
                }

                .low-stock-table th {
                    background: #ffe2a7;
                    color: #a34624;
                    font-weight: bold;
                    font-size: 1.07em;
                }

                .low-stock-table td {
                    background: #fff7e6;
                    color: #2d2d2d;
                    font-size: 1.05em;
                }

                .low-stock-status {
                    color: #c51111;
                    font-weight: bold;
                }

                .low-stock-count {
                    color: #b32d2e;
                    font-weight: 600;
                }

                #lowStockTableWrapper {
                    overflow: hidden;
                    transition: max-height 0.5s cubic-bezier(.68, -0.55, .27, 1.55);
                }
            </style>







            @endif





        </div>
    </div>
</div>
</div>

{{-- <script>
    let lowStockAccessories = @json($lowStockAccessories);
    let showingAll = false;
    
    function renderLowStockTable(showAll = false) {
    let tbody = document.getElementById('low-stock-tbody');
    tbody.innerHTML = ''; // Clear
    
    let dataToShow = showAll ? lowStockAccessories : lowStockAccessories.slice(0, 5);
    
    dataToShow.forEach(item => {
    let row = document.createElement('tr');
    row.innerHTML = `
    <td>${item.name}</td>
    <td>${item.min_qty}</td>
    <td class="low-stock-count">${item.stock}</td>
    <td class="low-stock-status">Restock Needed!</td>
    `;
    tbody.appendChild(row);
    });
    }
    
    document.addEventListener('DOMContentLoaded', function () {
    renderLowStockTable();
    
    let btn = document.getElementById('toggleStockBtn');
    let wrapper = document.getElementById('lowStockTableWrapper');
    
    // Initial wrapper max-height for collapsed state
    let rowHeight = 38; // Estimate row height (px). Adjust if needed for your design.
    let collapsedHeight = rowHeight * 5 + 40; // 5 rows + header
    let expandedHeight = rowHeight * (lowStockAccessories.length) + 40; // all rows + header
    
    wrapper.style.maxHeight = collapsedHeight + 'px';
    
    if (!btn) return;
    
    btn.addEventListener('click', function () {
    showingAll = !showingAll;
    renderLowStockTable(showingAll);
    
    // Animate the height
    if (showingAll) {
    wrapper.style.maxHeight = expandedHeight + 'px';
    btn.textContent = 'Minimize';
    } else {
    wrapper.style.maxHeight = collapsedHeight + 'px';
    btn.textContent = 'Maximize';
    }
    });
    
    // Hide the button if 5 or fewer
    if (lowStockAccessories.length <= 5) { btn.style.display='none' ; } });


</script> --}}

<script>
    // Data from controller
  const LOW_STOCK = @json($lowStockAccessories); // [{id,name,stock,min_qty,company_id,company,group_id,group}]
  let showingAll = false;
  let activeFilter = null; // { type: 'company'|'group', id: number|null }

  const tbody   = document.getElementById('low-stock-tbody');
  const wrapper = document.getElementById('lowStockTableWrapper');
  const toggleBtn = document.getElementById('toggleStockBtn');
  const clearBtn  = document.getElementById('clearLowStockFilter');
  const filterBadge = document.getElementById('lowStockFilterBadge');

  function applyFilter(data) {
    if (!activeFilter) return data;
    if (activeFilter.type === 'company') {
      return data.filter(x => String(x.company_id) === String(activeFilter.id));
    }
    if (activeFilter.type === 'group') {
      return data.filter(x => String(x.group_id) === String(activeFilter.id));
    }
    return data;
  }

  function renderLowStockTable(showAll = false) {
    tbody.innerHTML = '';

    let data = applyFilter(LOW_STOCK);
    let dataToShow = showAll ? data : data.slice(0, 5);

    dataToShow.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${item.name}</td>
        <td>${item.company || '-'}</td>
        <td>${item.group || '-'}</td>
        <td>${item.min_qty}</td>
        <td class="low-stock-count">${item.stock}</td>
        <td class="low-stock-status">Restock Needed!</td>
      `;
      tbody.appendChild(tr);
    });

    // Empty state
    if (dataToShow.length === 0) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="6" style="text-align:center; padding:10px;">No items match this filter.</td>`;
      tbody.appendChild(tr);
    }

    // Filter badge + clear button visibility
    if (activeFilter) {
      clearBtn.style.display = '';
      const label = activeFilter.type === 'company' ? 'Company' : 'Group';
      const name = (data[0]?.[activeFilter.type] ?? '').toString();
      filterBadge.textContent = `(${label}: ${name})`;
    } else {
      clearBtn.style.display = 'none';
      filterBadge.textContent = '';
    }
  }

  // Expand/Collapse animation
  document.addEventListener('DOMContentLoaded', () => {
    renderLowStockTable(false);

    const rowHeight = 42; // tweak if needed
    const collapsedHeight = rowHeight * 5 + 44; // 5 rows + header
    // dynamic expanded height based on filtered set
    function recomputeExpandedHeight() {
      const count = applyFilter(LOW_STOCK).length || 1;
      return rowHeight * count + 44;
    }

    wrapper.style.maxHeight = collapsedHeight + 'px';

    toggleBtn.addEventListener('click', () => {
      showingAll = !showingAll;
      renderLowStockTable(showingAll);
      wrapper.style.maxHeight = (showingAll ? recomputeExpandedHeight() : collapsedHeight) + 'px';
      toggleBtn.textContent = showingAll ? 'Minimize' : 'Maximize';
    });

    // Chip clicks
    document.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        activeFilter = { type: chip.dataset.type, id: chip.dataset.id };
        showingAll = true; // auto-expand when filtering
        renderLowStockTable(true);
        wrapper.style.maxHeight = recomputeExpandedHeight() + 'px';
        toggleBtn.textContent = 'Minimize';
      });
    });

    // Clear filter
    clearBtn.addEventListener('click', () => {
      activeFilter = null;
      showingAll = false;
      renderLowStockTable(false);
      wrapper.style.maxHeight = collapsedHeight + 'px';
      toggleBtn.textContent = 'Maximize';
    });

    // Hide Maximize button if <= 5 items initially
    if (LOW_STOCK.length <= 5) { toggleBtn.style.display = 'none'; }
  });
</script>




@endsection