@extends('user_navbar')
@section('content')

<style>
    .live-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 18px #0001;
        padding: 20px;
    }

    .filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 12px;
    }

    .filters .form-group {
        min-width: 220px;
    }

    .live-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .live-dot {
        width: 10px;
        height: 10px;
        background: #16a34a;
        border-radius: 50%;
        margin-right: 8px;
        animation: pulse 1.6s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1
        }

        50% {
            opacity: .4
        }

        100% {
            opacity: 1
        }
    }

    table.live-table {
        width: 100%;
        border-collapse: collapse;
    }

    table.live-table th,
    table.live-table td {
        border-bottom: 1px solid #eee;
        padding: 8px 10px;
        font-size: 14px;
        text-align: left;
        vertical-align: top;
    }

    table.live-table th {
        background: #f7f7fb;
    }

    .totals {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin: 8px 0 0;
    }

    .totals .card {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .muted {
        color: #6b7280;
        font-size: 12px;
    }

    .btn {
        border: 0;
        background: #0ea5e9;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn.secondary {
        background: #64748b;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="live-wrap">

                <div class="live-bar">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="live-dot"></span>
                        <h3 style="margin:0;">Live Purchases (Batches) — Today by default</h3>
                    </div>
                    <div class="muted">Auto-refreshes every 10s • Last refresh: <span id="lastRefresh">—</span></div>
                </div>

                <div class="filters">
                    <div class="form-group">
                        <label>From (YYYY-MM-DD)</label>
                        <input type="date" id="start_date" class="form-control" value="{{ $start }}">
                    </div>
                    <div class="form-group">
                        <label>To (YYYY-MM-DD)</label>
                        <input type="date" id="end_date" class="form-control" value="{{ $end }}">
                    </div>

                    <div class="form-group">
                        <label>Vendor (optional)</label>
                        <select id="vendor_id" class="form-control">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ (isset($vendorId) && $vendorId==$v->id) ? 'selected' : ''
                                }}>
                                {{ $v->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Group (optional)</label>
                        <select id="group_id" class="form-control">
                            <option value="">All Groups</option>
                            @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ (isset($groupId) && $groupId==$g->id) ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Company (optional)</label>
                        <select id="company_id" class="form-control">
                            <option value="">All Companies</option>
                            @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ (isset($companyId) && $companyId==$c->id) ? 'selected' : ''
                                }}>
                                {{ $c->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="display:flex; gap:8px;">
                        <button id="applyBtn" class="btn">Apply</button>
                        <button id="todayBtn" class="btn secondary">Today</button>
                        <button id="refreshBtn" class="btn secondary">Refresh Now</button>
                    </div>
                </div>

                <div class="totals">
                    <div class="card">
                        <div class="muted">Purchase Entries</div>
                        <div id="totalCount" style="font-size:18px; font-weight:700;">0</div>
                    </div>
                    <div class="card">
                        <div class="muted">Qty Purchased</div>
                        <div id="totalQty" style="font-size:18px; font-weight:700;">0</div>
                    </div>
                    <div class="card">
                        <div class="muted">Qty Remaining</div>
                        <div id="totalRemaining" style="font-size:18px; font-weight:700;">0</div>
                    </div>
                    <div class="card">
                        <div class="muted">Total Purchase (Rs.)</div>
                        <div id="totalPurchase" style="font-size:18px; font-weight:700;">0.00</div>
                    </div>
                </div>

                <div style="margin-top:12px; overflow:auto;">
                    <table class="live-table" id="liveTable">
                        <thead>
                            <tr>
                                <th>Batch #</th>
                                <th>Created At</th>
                                <th>Purchase Date</th>
                                <th>Accessory</th>
                                <th>Group</th>
                                <th>Company</th>
                                <th>Vendor</th>
                                <th>Qty</th>
                                <th>Remaining</th>
                                <th>Purchase (unit)</th>
                                <th>Selling (unit)</th>
                                <th>Line Total</th>
                                <th>Barcode</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Filled by JS --}}
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    (function(){
  const startEl = document.getElementById('start_date');
  const endEl   = document.getElementById('end_date');
  const vendEl  = document.getElementById('vendor_id');
  const groupEl = document.getElementById('group_id');
  const compEl  = document.getElementById('company_id');

  const lastRef = document.getElementById('lastRefresh');
  const applyBtn= document.getElementById('applyBtn');
  const todayBtn= document.getElementById('todayBtn');
  const refreshBtn = document.getElementById('refreshBtn');

  const tbody   = document.querySelector('#liveTable tbody');
  const totalCount = document.getElementById('totalCount');
  const totalQty = document.getElementById('totalQty');
  const totalRemaining = document.getElementById('totalRemaining');
  const totalPurchase = document.getElementById('totalPurchase');

  let timer = null;

  function fmt(n){ return (Math.round((+n + Number.EPSILON)*100)/100).toFixed(2); }

  function setToday(){
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth()+1).padStart(2,'0');
    const d = String(now.getDate()).padStart(2,'0');
    const today = `${y}-${m}-${d}`;
    startEl.value = today;
    endEl.value   = today;
  }

  function esc(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function renderRows(rows){
    tbody.innerHTML = '';
    rows.forEach(r => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${r.id}</td>
          <td>${r.created_at || '-'}</td>
          <td>${r.purchase_date || '-'}</td>
          <td>${esc(r.accessory || '-')}</td>
          <td>${esc(r.group || '-')}</td>
          <td>${esc(r.company || '-')}</td>
          <td>${esc(r.vendor || '-')}</td>
          <td><strong>${r.qty_purchased ?? 0}</strong></td>
          <td>${r.qty_remaining ?? 0}</td>
          <td>Rs. ${fmt(r.purchase_price ?? 0)}</td>
          <td>Rs. ${fmt(r.selling_price ?? 0)}</td>
          <td><strong>Rs. ${fmt(r.line_total ?? 0)}</strong></td>
          <td>${esc(r.barcode || '—')}</td>
          <td>${esc(r.user || '-')}</td>
        </tr>
      `);
    });
  }

  async function fetchData(){
    const params = new URLSearchParams({
      start_date: startEl.value,
      end_date: endEl.value,
    });
    if (vendEl.value)  params.append('vendor_id', vendEl.value);
    if (groupEl.value) params.append('group_id', groupEl.value);
    if (compEl.value)  params.append('company_id', compEl.value);

    const res = await fetch(`{{ route('batches.live.feed') }}?${params.toString()}`, {
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) {
      const t = await res.text();
      throw new Error(`HTTP ${res.status}: ${t.slice(0,200)}`);
    }

    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Failed to load');

    renderRows(json.data || []);
    totalCount.textContent = json.totals?.count ?? 0;
    totalQty.textContent = json.totals?.qty_sum ?? 0;
    totalRemaining.textContent = json.totals?.remaining_sum ?? 0;
    totalPurchase.textContent = (json.totals?.purchase_sum ?? 0).toFixed(2);
    lastRef.textContent = json.refreshed_at || new Date().toLocaleTimeString();
  }

  function startAuto(){
    if (timer) clearInterval(timer);
    timer = setInterval(() => fetchData().catch(console.error), 10000);
  }

  applyBtn.addEventListener('click', () => fetchData().catch(err => alert(err.message)));
  refreshBtn.addEventListener('click', () => fetchData().catch(err => alert(err.message)));
  todayBtn.addEventListener('click', () => { setToday(); fetchData().catch(err => alert(err.message)); });

  if (!startEl.value || !endEl.value) setToday();
  fetchData().catch(console.error).finally(startAuto);
})();
</script>

@endsection