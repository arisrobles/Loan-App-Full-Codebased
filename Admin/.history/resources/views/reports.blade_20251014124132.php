@extends('layouts.app')

@section('title', 'Loan Back Office — Reports')
@section('page-title', 'Reports')

@push('head')
  {{-- Tailwind helpers & page-specific styles --}}
  <style>
    body::before{content:"";position:fixed;inset:-20%;z-index:-1;background:
      radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.07), transparent 60%),
      radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.06), transparent 60%),
      radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.05), transparent 60%)}
    .stuck{position:sticky;top:0;z-index:30}
    .table-sticky thead th{position:sticky;top:0;background:#f8fafc;z-index:10}
    .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;font-size:12px;font-weight:600}
    .dark section.bg-white{background:#0f172a!important;border-color:#1f2937!important}
    .dark .badge{background:#0b1f3a;color:#93c5fd}
  </style>

  {{-- Vendor (load once in layout ideally; safe to include here if not global) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

{{-- Optional: override/extend the sidebar block in your layout --}}
@push('sidebar')
  <div class="px-2 text-xs uppercase tracking-wide text-white/70 mt-2 mb-1">Navigate</div>
  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</a>
  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Loans</a>
  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Payments</a>
  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Borrowers</a>
  <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-lg bg-white/10 font-medium">Reports</a>
  <a href="#" class="block px-3 py-2 rounded-lg hover:bg-white/10">Settings</a>
@endpush

@section('content')
  {{-- Filters / Actions --}}
  <section class="px-4 md:px-6 py-4 bg-white border border-slate-200 rounded-2xl stuck">
    <div class="flex flex-wrap items-end gap-3">
      <div>
        <label class="text-xs text-slate-500">Period</label>
        <select id="period" class="mt-1 h-10 rounded-lg bg-slate-100 px-3">
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="qtd">Quarter-to-Date</option>
          <option value="ytd" selected>Year-to-Date</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500">From</label>
        <input id="fromDate" type="date" class="mt-1 h-10 rounded-lg bg-slate-100 px-3" />
      </div>
      <div>
        <label class="text-xs text-slate-500">To</label>
        <input id="toDate" type="date" class="mt-1 h-10 rounded-lg bg-slate-100 px-3" />
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-slate-500">Account / Branch</label>
        <input id="branch" type="text" placeholder="All" class="mt-1 h-10 w-full rounded-lg bg-slate-100 px-3" />
      </div>
      <div class="ml-auto flex items-center gap-2">
        <button id="bulkEntryBtn" class="px-3 h-10 rounded-lg bg-slate-100 hover:bg-slate-200">Bulk Entry</button>
        <button id="uploadCsvBtn" class="px-3 h-10 rounded-lg bg-slate-100 hover:bg-slate-200">Upload CSV</button>
        <button id="exportBtn" class="px-3 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700">Export Report — Standard</button>
      </div>
    </div>
    <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100">Profit &amp; Loss</span>
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100">Balance Sheet</span>
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100">Cash Flow</span>
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100">Projected Revenue</span>
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100">Bank Statements</span>
    </div>
  </section>

  {{-- Tabs --}}
  <section class="p-4 md:p-6 space-y-6">
    <div class="bg-white rounded-2xl shadow-card border border-slate-100">
      <div class="px-4 pt-4">
        <div class="flex flex-wrap gap-2 text-sm">
          <button data-tab="pnl" class="tab-btn px-3 py-2 rounded-lg bg-brand-600 text-white">Profit &amp; Loss</button>
          <button data-tab="bs"  class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Balance Sheet</button>
          <button data-tab="cf"  class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Cash Flow</button>
          <button data-tab="pr"  class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Projected Revenue</button>
          <button data-tab="bank"class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Bank Statements</button>
        </div>
      </div>

      <div class="p-4 md:p-6">
        {{-- P&L --}}
        <div data-panel="pnl" class="tab-panel grid grid-cols-1 lg:grid-cols-5 gap-4">
          <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
            <div class="flex items-center justify-between">
              <h2 class="text-base md:text-lg font-semibold">P&amp;L Overview</h2>
              <div class="text-xs text-slate-500" id="periodLabel">YTD</div>
            </div>
            <div class="mt-4"><canvas id="pnlChart" height="200"></canvas></div>
          </article>
          <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
            <h3 class="text-sm font-semibold">Key Metrics</h3>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Revenue</dt><dd id="kpiRevenue" class="mt-1 font-semibold">₱1,240,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">COGS</dt><dd id="kpiCogs" class="mt-1 font-semibold">₱420,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Operating Exp.</dt><dd id="kpiOpex" class="mt-1 font-semibold">₱310,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Net Income</dt><dd id="kpiNet" class="mt-1 font-semibold text-emerald-600">₱510,000</dd></div>
            </dl>
          </article>
          <article class="lg:col-span-5 bg-white rounded-xl p-4 border border-slate-100 table-sticky overflow-auto">
            <div class="flex items-center justify-between px-2"><h3 class="text-sm font-semibold">Breakdown</h3><div class="text-xs text-slate-500">Sample data</div></div>
            <table class="mt-2 w-full text-sm">
              <thead>
                <tr class="text-left text-slate-500">
                  <th class="py-2 px-2">Category</th>
                  <th class="py-2 px-2">Jan</th><th class="py-2 px-2">Feb</th><th class="py-2 px-2">Mar</th>
                  <th class="py-2 px-2">Apr</th><th class="py-2 px-2">May</th><th class="py-2 px-2">Jun</th>
                  <th class="py-2 px-2">Total</th>
                </tr>
              </thead>
              <tbody id="pnlTable"></tbody>
            </table>
          </article>
        </div>

        {{-- Balance Sheet --}}
        <div data-panel="bs" class="tab-panel hidden grid grid-cols-1 lg:grid-cols-5 gap-4">
          <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
            <div class="flex items-center justify-between"><h2 class="text-base md:text-lg font-semibold">Assets vs Liabilities</h2><div class="text-xs text-slate-500">Snapshot</div></div>
            <div class="mt-4"><canvas id="bsChart" height="200"></canvas></div>
          </article>
          <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
            <h3 class="text-sm font-semibold">Equity Summary</h3>
            <ul class="mt-3 space-y-2 text-sm">
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Total Assets</span><b id="bsAssets">₱2,400,000</b></li>
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Total Liabilities</span><b id="bsLiab">₱1,550,000</b></li>
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Owner's Equity</span><b id="bsEquity" class="text-emerald-600">₱850,000</b></li>
            </ul>
          </article>
          <article class="lg:col-span-5 bg-white rounded-xl p-4 border border-slate-100 table-sticky overflow-auto">
            <div class="flex items-center justify-between px-2"><h3 class="text-sm font-semibold">Balance Sheet Detail</h3><div class="text-xs text-slate-500">Sample accounts</div></div>
            <table class="mt-2 w-full text-sm">
              <thead>
                <tr class="text-left text-slate-500"><th class="py-2 px-2">Account</th><th class="py-2 px-2">Type</th><th class="py-2 px-2">Amount</th></tr>
              </thead>
              <tbody id="bsTable"></tbody>
            </table>
          </article>
        </div>

        {{-- Cash Flow --}}
        <div data-panel="cf" class="tab-panel hidden grid grid-cols-1 lg:grid-cols-5 gap-4">
          <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
            <div class="flex items-center justify-between"><h2 class="text-base md:text-lg font-semibold">Cash Flow (Direct)</h2><div class="text-xs text-slate-500">Monthly</div></div>
            <div class="mt-4"><canvas id="cfChart" height="200"></canvas></div>
          </article>
          <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
            <h3 class="text-sm font-semibold">Net Cash</h3>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Operating</dt><dd id="cfOp" class="mt-1 font-semibold">₱210,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Investing</dt><dd id="cfInv" class="mt-1 font-semibold">-₱60,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Financing</dt><dd id="cfFin" class="mt-1 font-semibold">₱40,000</dd></div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100"><dt class="text-slate-500">Change in Cash</dt><dd id="cfNet" class="mt-1 font-semibold text-emerald-600">₱190,000</dd></div>
            </dl>
          </article>
          <article class="lg:col-span-5 bg-white rounded-xl p-4 border border-slate-100 table-sticky overflow-auto">
            <div class="flex items-center justify-between px-2"><h3 class="text-sm font-semibold">Cash Flow Detail</h3><div class="text-xs text-slate-500">Sample transactions</div></div>
            <table class="mt-2 w-full text-sm">
              <thead>
                <tr class="text-left text-slate-500"><th class="py-2 px-2">Date</th><th class="py-2 px-2">Description</th><th class="py-2 px-2">Inflow</th><th class="py-2 px-2">Outflow</th></tr>
              </thead>
              <tbody id="cfTable"></tbody>
            </table>
          </article>
        </div>

        {{-- Projected Revenue --}}
        <div data-panel="pr" class="tab-panel hidden grid grid-cols-1 lg:grid-cols-5 gap-4">
          <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
            <div class="flex items-center justify-between"><h2 class="text-base md:text-lg font-semibold">Projection (Next 12 Months)</h2><div class="text-xs text-slate-500">Based on pipeline</div></div>
            <div class="mt-4"><canvas id="prChart" height="200"></canvas></div>
          </article>
          <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
            <h3 class="text-sm font-semibold">Assumptions</h3>
            <ul class="mt-3 text-sm list-disc pl-5 space-y-1 text-slate-600">
              <li>Win rate: 35%</li><li>Avg. ticket: ₱20,000</li><li>Seasonality factor applied</li>
            </ul>
            <div class="mt-4 text-xs text-slate-500">* Demo data for UI</div>
          </article>
        </div>

        {{-- Bank Statements --}}
        <div data-panel="bank" class="tab-panel hidden grid grid-cols-1 lg:grid-cols-5 gap-4">
          <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
            <div class="flex items-center justify-between"><h2 class="text-base md:text-lg font-semibold">Bank Balance</h2><div class="text-xs text-slate-500">Monthly</div></div>
            <div class="mt-4"><canvas id="bankChart" height="200"></canvas></div>
          </article>
          <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
            <h3 class="text-sm font-semibold">Reconciliation</h3>
            <ul class="mt-3 space-y-2 text-sm">
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Book Balance</span><b>₱540,000</b></li>
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Bank Balance</span><b>₱538,500</b></li>
              <li class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex justify-between"><span>Outstanding</span><b class="text-amber-600">₱1,500</b></li>
            </ul>
          </article>
          <article class="lg:col-span-5 bg-white rounded-xl p-4 border border-slate-100 table-sticky overflow-auto">
            <div class="flex items-center justify-between px-2"><h3 class="text-sm font-semibold">Statement Lines</h3><div class="text-xs text-slate-500">Imported CSV</div></div>
            <table class="mt-2 w-full text-sm">
              <thead>
                <tr class="text-left text-slate-500"><th class="py-2 px-2">Date</th><th class="py-2 px-2">Reference</th><th class="py-2 px-2">Description</th><th class="py-2 px-2">Amount</th><th class="py-2 px-2">Balance</th></tr>
              </thead>
              <tbody id="bankTable"></tbody>
            </table>
          </article>
        </div>
      </div>
    </div>
  </section>

  {{-- Bulk Entry Modal --}}
  <div id="bulkModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white w-[92%] max-w-2xl rounded-2xl p-6 shadow-2xl border border-slate-100">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Bulk Entry Transactions</h3>
        <button class="rounded-lg p-2 hover:bg-slate-100" onclick="hideBulk()" aria-label="Close">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <p class="text-sm text-slate-600">Paste rows below (Date, Type, Amount, Description, Reference). One transaction per line.</p>
      <textarea id="bulkText" class="mt-3 w-full h-48 rounded-xl bg-slate-50 border border-slate-200 p-3 font-mono text-sm" placeholder="2025-09-01, payment, 2500, Client payment, OR-1001&#10;2025-09-02, fee, -150, Late fee, LF-22"></textarea>
      <div class="mt-4 flex items-center justify-end gap-2">
        <button class="px-4 h-10 rounded-lg bg-slate-100 hover:bg-slate-200" onclick="hideBulk()">Cancel</button>
        <button class="px-4 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700" onclick="parseBulk()">Add Rows</button>
      </div>
    </div>
  </div>

  {{-- Upload CSV Modal --}}
  <div id="csvModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white w-[92%] max-w-md rounded-2xl p-6 shadow-2xl border border-slate-100">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-semibold">Upload File (CSV)</h3>
        <button class="rounded-lg p-2 hover:bg-slate-100" onclick="hideCsv()" aria-label="Close">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <input id="csvInput" type="file" accept=".csv" class="w-full rounded-lg bg-slate-50 border border-slate-200 p-2" />
      <div class="mt-4 flex items-center justify-end gap-2">
        <button class="px-4 h-10 rounded-lg bg-slate-100 hover:bg-slate-200" onclick="hideCsv()">Cancel</button>
        <button class="px-4 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700" onclick="fakeCsvImport()">Upload</button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // Tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const panels  = document.querySelectorAll('.tab-panel');
    tabBtns.forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const key = btn.dataset.tab;
        tabBtns.forEach(b=> b.classList.remove('bg-brand-600','text-white'));
        btn.classList.add('bg-brand-600','text-white');
        panels.forEach(p=> p.classList.toggle('hidden', p.dataset.panel!==key));
      });
    });

    // Charts (demo)
    const currency = v => '₱' + Number(v).toLocaleString();

    new Chart(document.getElementById('pnlChart'), {
      type: 'bar',
      data: { labels:['Jan','Feb','Mar','Apr','May','Jun'], datasets:[
        { label:'Revenue', data:[180000,190000,210000,200000,230000,240000], backgroundColor:'#2563eb' },
        { label:'Expense', data:[120000,110000,130000,125000,135000,140000], backgroundColor:'#94a3b8' }
      ]},
      options:{ plugins:{ legend:{ position:'bottom' }}, scales:{ y:{ ticks:{ callback:v=>currency(v) }, grid:{ color:'rgba(148,163,184,.2)'}}, x:{ grid:{ display:false }}}}
    });

    new Chart(document.getElementById('bsChart'), {
      type: 'doughnut',
      data: { labels:['Assets','Liabilities'], datasets:[{ data:[2400000,1550000], backgroundColor:['#22c55e','#f59e0b'], borderWidth:0 }] },
      options:{ plugins:{ legend:{ position:'bottom' }}, cutout:'65%' }
    });

    new Chart(document.getElementById('cfChart'), {
      type: 'line',
      data: { labels:['Jan','Feb','Mar','Apr','May','Jun'], datasets:[{ label:'Net Cash', data:[20000,35000,15000,40000,50000,30000], borderColor:'#2563eb', backgroundColor:'#2563eb22', fill:true, tension:0.35, pointRadius:3, pointBorderWidth:0 }] },
      options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ ticks:{ callback:v=>currency(v) }, grid:{ color:'rgba(148,163,184,.2)'}}, x:{ grid:{ display:false }}}}
    });

    new Chart(document.getElementById('prChart'), {
      type: 'bar',
      data: { labels:['Jul','Aug','Sep','Oct','Nov','Dec','Jan'], datasets:[{ label:'Projected', data:[18000,19000,22000,26000,28000,30000,25000], backgroundColor:'#0ea5e9' }] },
      options:{ plugins:{ legend:{ position:'bottom' }}, scales:{ y:{ ticks:{ callback:v=>currency(v) }}}}
    });

    new Chart(document.getElementById('bankChart'), {
      type: 'line',
      data: { labels:['Jan','Feb','Mar','Apr','May','Jun'], datasets:[{ label:'Bank Balance', data:[420000,440000,430000,470000,520000,538500], borderColor:'#22c55e', backgroundColor:'#22c55e22', fill:true, tension:0.35, pointRadius:3, pointBorderWidth:0 }] },
      options:{ plugins:{ legend:{ display:false }}, scales:{ y:{ ticks:{ callback:v=>currency(v) }}}}
    });

    // Sample tables
    const pnlRows = [
      { cat:'Revenue', m:[180000,190000,210000,200000,230000,240000] },
      { cat:'COGS', m:[60000,55000,70000,65000,72000,74000] },
      { cat:'Operating Expenses', m:[45000,43000,46000,47000,49000,52000] },
      { cat:'Other', m:[5000,4000,6000,3000,4000,3000] }
    ];
    const pnlT = document.getElementById('pnlTable');
    pnlRows.forEach(r=>{
      const total = r.m.reduce((a,b)=>a+b,0);
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="py-2 px-2 font-medium">${r.cat}</td>`+
        r.m.map(v=>`<td class="py-2 px-2">${currency(v)}</td>`).join('')+
        `<td class="py-2 px-2 font-semibold">${currency(total)}</td>`;
      pnlT.appendChild(tr);
    });

    const bsRows = [
      { a:'Cash and Cash Equivalents', t:'Asset', v:540000 },
      { a:'Accounts Receivable', t:'Asset', v:320000 },
      { a:'Loans Receivable', t:'Asset', v:1240000 },
      { a:'Property & Equipment', t:'Asset', v:300000 },
      { a:'Accounts Payable', t:'Liability', v:210000 },
      { a:'Notes Payable', t:'Liability', v:980000 },
      { a:'Accrued Expenses', t:'Liability', v:360000 }
    ];
    const bsT = document.getElementById('bsTable');
    bsRows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="py-2 px-2">${r.a}</td><td class="py-2 px-2">${r.t}</td><td class="py-2 px-2 font-medium">${currency(r.v)}</td>`;
      bsT.appendChild(tr);
    });

    const cfRows = [
      { d:'2025-06-01', desc:'Customer payment', in:15000, out:0 },
      { d:'2025-06-02', desc:'Utilities', in:0, out:2500 },
      { d:'2025-06-04', desc:'Loan disbursement', in:0, out:20000 },
      { d:'2025-06-10', desc:'Interest received', in:1200, out:0 }
    ];
    const cfT = document.getElementById('cfTable');
    cfRows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="py-2 px-2">${r.d}</td><td class="py-2 px-2">${r.desc}</td>
        <td class="py-2 px-2 text-emerald-600">${r.in?currency(r.in):''}</td>
        <td class="py-2 px-2 text-red-600">${r.out?'-'+currency(r.out):''}</td>`;
      cfT.appendChild(tr);
    });

    const bankRows = [
      { d:'2025-06-03', ref:'DEP-1001', desc:'Deposit', amt:25000, bal:470000 },
      { d:'2025-06-07', ref:'CHK-2004', desc:'Supplier check', amt:-12000, bal:458000 },
      { d:'2025-06-19', ref:'TRX-441', desc:'Transfer in', amt:8000, bal:466000 }
    ];
    const bankT = document.getElementById('bankTable');
    bankRows.forEach(r=>{
      const tr = document.createElement('tr');
      const amtFmt = r.amt < 0 ? `<span class='text-red-600'>-${currency(Math.abs(r.amt))}</span>` : `<span class='text-emerald-600'>${currency(r.amt)}</span>`;
      tr.innerHTML = `<td class="py-2 px-2">${r.d}</td><td class="py-2 px-2">${r.ref}</td><td class="py-2 px-2">${r.desc}</td><td class="py-2 px-2">${amtFmt}</td><td class="py-2 px-2 font-medium">${currency(r.bal)}</td>`;
      bankT.appendChild(tr);
    });

    // Bulk & CSV Modals
    const bulkModal = document.getElementById('bulkModal');
    const csvModal  = document.getElementById('csvModal');
    document.getElementById('bulkEntryBtn')?.addEventListener('click', ()=> showBulk());
    document.getElementById('uploadCsvBtn')?.addEventListener('click', ()=> showCsv());
    function showBulk(){ bulkModal.classList.remove('hidden'); bulkModal.classList.add('flex'); }
    function hideBulk(){ bulkModal.classList.add('hidden'); bulkModal.classList.remove('flex'); }
    function showCsv(){ csvModal.classList.remove('hidden'); csvModal.classList.add('flex'); }
    function hideCsv(){ csvModal.classList.add('hidden'); csvModal.classList.remove('flex'); }
    function parseBulk(){
      const lines = (document.getElementById('bulkText').value||'').split(/\n+/).map(l=>l.trim()).filter(Boolean);
      if(!lines.length){ Swal.fire({icon:'info',title:'Nothing to import',timer:1200,showConfirmButton:false}); return; }
      hideBulk();
      Swal.fire({ icon:'success', title:`Imported ${lines.length} rows`, timer:1400, showConfirmButton:false });
    }
    function fakeCsvImport(){
      const inp = document.getElementById('csvInput');
      if(!inp.files?.length){ Swal.fire({icon:'info',title:'Select a CSV file',timer:1200,showConfirmButton:false}); return; }
      hideCsv();
      Swal.fire({ icon:'success', title:'CSV uploaded (demo)', timer:1400, showConfirmButton:false });
    }

    // Period control (demo behavior)
    const periodSel = document.getElementById('period');
    const fromDate = document.getElementById('fromDate');
    const toDate   = document.getElementById('toDate');
    function setRange(kind){
      const now = new Date();
      if(kind==='this_month'){
        fromDate.valueAsDate = new Date(now.getFullYear(), now.getMonth(), 1);
        toDate.valueAsDate   = new Date(now.getFullYear(), now.getMonth()+1, 0);
      }else if(kind==='last_month'){
        fromDate.valueAsDate = new Date(now.getFullYear(), now.getMonth()-1, 1);
        toDate.valueAsDate   = new Date(now.getFullYear(), now.getMonth(), 0);
      }else if(kind==='qtd'){
        const q = Math.floor(now.getMonth()/3);
        fromDate.valueAsDate = new Date(now.getFullYear(), q*3, 1);
        toDate.valueAsDate   = now;
      }else if(kind==='ytd'){
        fromDate.valueAsDate = new Date(now.getFullYear(), 0, 1);
        toDate.valueAsDate   = now;
      }
      document.getElementById('periodLabel').textContent = periodSel.options[periodSel.selectedIndex].text;
    }
    setRange('ytd');
    periodSel.addEventListener('change', ()=>{
      const v = periodSel.value;
      if(v !== 'custom') setRange(v);
      document.getElementById('periodLabel').textContent = periodSel.options[periodSel.selectedIndex].text;
    });

    // Export button (stub)
    document.getElementById('exportBtn')?.addEventListener('click', ()=>{
      Swal.fire({icon:'success', title:'Export queued', text:'Your report will download shortly (demo).', timer:1500, showConfirmButton:false});
    });
  </script>
@endpush
