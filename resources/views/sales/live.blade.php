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

    .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 12px;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef9c3;
        color: #854d0e;
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

    .btn:disabled {
        opacity: .6;
        cursor: not-allowed;
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
                        <h3 style="margin:0;">Live Sales (Today by default)</h3>
                    </div>
                    <div class="muted">Auto-refreshes every 10s • Last refresh: <span id="lastRefresh">—</span></div>
                </div>

                {{-- Filters --}}
                <div class="filters">
                    <div class="form-group">
                        <label>From (YYYY-MM-DD)</label>
                        <input type="date" id="start_date" class="form-control" value="{{ $start }}">
                    </div>
                    <div class="form-group">
                        <label>To (YYYY-MM-DD)</label>
                        <input type="date" id="end_date" class="form-control" value="{{ $end }}">
                    </div>
                    <div class="form-group" style="min-width:260px;">
                        <label>Vendor (optional)</label>
                        <select id="vendor_id" class="form-control">
                            <option value="">All Vendors / Walk-ins</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ (isset($vendorId) && $vendorId==$v->id) ? 'selected' : ''
                                }}>
                                {{ $v->name }}
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
                        <div class="muted">Sales Count</div>
                        <div id="totalCount" style="font-size:18px; font-weight:700;">0</div>
                    </div>
                    <div class="card">
                        <div class="muted">Net Total (Rs.)</div>
                        <div id="totalNet" style="font-size:18px; font-weight:700;">0.00</div>
                    </div>
                    <div class="card">
                        <div class="muted">Profit (Rs.)</div>
                        <div id="totalProfit" style="font-size:18px; font-weight:700;">0.00</div>
                    </div>
                </div>
                <div style="margin-top:12px; overflow:auto;">
                    <table class="live-table" id="liveTable">
                        <thead>
                            <tr>
                                <th>Sale #</th>
                                <th>Date</th>
                                <th>Who</th>
                                <th>Total (Net)</th>
                                <th>Payments</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>By</th>
                                <th>Receipt</th>
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
  const lastRef = document.getElementById('lastRefresh');
  const applyBtn= document.getElementById('applyBtn');
  const todayBtn= document.getElementById('todayBtn');
  const refreshBtn = document.getElementById('refreshBtn');

  const tbody   = document.querySelector('#liveTable tbody');
  const totalCount = document.getElementById('totalCount');
  const totalNet   = document.getElementById('totalNet');
const totalProfit= document.getElementById('totalProfit');

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

  function badge(status){
    if (String(status).toLowerCase() === 'approved') {
      return `<span class="badge badge-success">Approved</span>`;
    }
    return `<span class="badge badge-pending">Pending</span>`;
  }

  function renderRows(rows){
    tbody.innerHTML = '';
    rows.forEach(r => {
      const payHtml = (r.payments || []).map(p => {
        const m = p.method === 'bank' ? 'Bank' : 'Counter';
        const bank = p.bank ? ` — ${p.bank}` : '';
        const ref  = p.ref ? ` (Ref: ${p.ref})` : '';
        return `<div>${m}${bank}: Rs. ${fmt(p.amount)}${ref}</div>`;
      }).join('');

      const itemsInfo = `${r.items_count} item(s)` + (r.comment ? `<div class="muted">Note: ${escapeHtml(r.comment).slice(0,120)}</div>` : '');

      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${r.id}</td>
          <td>${r.sale_date || '-'}</td>
          <td>${r.who || '-'}</td>
          <td><strong>Rs. ${fmt(r.total)}</strong></td>
          <td>${payHtml || '<span class="muted">—</span>'}</td>
          <td>${itemsInfo}</td>
          <td>${badge(r.status)}</td>
          <td>${r.user || '-'}</td>
          <td><a class="btn secondary" target="_blank" href="${r.invoice_url}">Receipt</a></td>
        </tr>
      `);
    });
  }

  function escapeHtml(s){
    return String(s)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#039;');
  }

  async function fetchData(){
    const params = new URLSearchParams({
      start_date: startEl.value,
      end_date: endEl.value,
    });
    if (vendEl.value) params.append('vendor_id', vendEl.value);

    const res = await fetch(`{{ route('sales.live.feed') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) {
      const t = await res.text();
      throw new Error(`HTTP ${res.status}: ${t.slice(0,200)}`);
    }
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Failed to load');

    renderRows(json.data || []);
    totalCount.textContent = json.totals?.count ?? 0;
    totalNet.textContent   = (json.totals?.net_sum ?? 0).toFixed(2);
    lastRef.textContent    = json.refreshed_at || new Date().toLocaleTimeString();
    if (totalProfit) {
    totalProfit.textContent = (json.totals?.profit_sum ?? 0).toFixed(2);
    }
  }

  function startAuto(){
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
      fetchData().catch(err => console.error(err));
    }, 10000); // 10s
  }

  // Wire up UI
  applyBtn.addEventListener('click', () => {
    fetchData().catch(err => alert(err.message));
  });
  refreshBtn.addEventListener('click', () => {
    fetchData().catch(err => alert(err.message));
  });
  todayBtn.addEventListener('click', () => {
    setToday();
    fetchData().catch(err => alert(err.message));
  });

  // Init: ensure defaults and load
  if (!startEl.value || !endEl.value) setToday();
  fetchData().catch(err => console.error(err)).finally(startAuto);
})();
</script>

@endsection