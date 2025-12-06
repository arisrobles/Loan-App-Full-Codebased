@extends('layouts.app')

@section('title', 'Loan Back Office — Reports')
@section('page-title', 'Reports')

@push('head')
<style>
  body::before {
    content:"";position:fixed;inset:-20%;z-index:-1;
    background:
      radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.07), transparent 60%),
      radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.06), transparent 60%),
      radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.05), transparent 60%);
  }
  .stuck{position:sticky;top:0;z-index:30}
  .table-sticky thead th{position:sticky;top:0;background:#f8fafc;z-index:10}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
{{-- FILTER BAR --}}
<section class="px-4 md:px-6 py-4 bg-white border border-slate-200 rounded-2xl stuck shadow-sm">
  <div class="flex flex-wrap items-end gap-3">
    <div>
      <label class="text-xs text-slate-500">Period</label>
      <select id="period" class="mt-1 h-10 rounded-lg bg-slate-100 px-3">
        <option value="this_month">This Month</option>
        <option value="last_month">Last Month</option>
        <option value="qtd">Quarter-to-Date</option>
        <option value="ytd" selected>Year-to-Date</option>
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
      <label class="text-xs text-slate-500">Branch / Account</label>
      <input id="branch" type="text" placeholder="All" class="mt-1 h-10 w-full rounded-lg bg-slate-100 px-3" />
    </div>
    <div class="ml-auto flex items-center gap-2">
      <button id="bulkEntryBtn" class="px-3 h-10 rounded-lg bg-slate-100 hover:bg-slate-200">Bulk Entry</button>
      <button id="uploadCsvBtn" class="px-3 h-10 rounded-lg bg-slate-100 hover:bg-slate-200">Upload CSV</button>
      <button id="exportBtn" class="px-3 h-10 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Export</button>
    </div>
  </div>
</section>

{{-- REPORT TABS --}}
<section class="p-4 md:p-6 space-y-6">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-4 pt-4">
      <div class="flex flex-wrap gap-2 text-sm">
        <button data-tab="pnl" class="tab-btn px-3 py-2 rounded-lg bg-indigo-600 text-white">P&L</button>
        <button data-tab="bs" class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Balance Sheet</button>
        <button data-tab="cf" class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Cash Flow</button>
        <button data-tab="pr" class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Projected Revenue</button>
        <button data-tab="bank" class="tab-btn px-3 py-2 rounded-lg hover:bg-slate-100">Bank Statements</button>
      </div>
    </div>

    <div class="p-6">
      {{-- P&L --}}
      <div data-panel="pnl" class="tab-panel grid grid-cols-1 lg:grid-cols-5 gap-4">
        <article class="lg:col-span-3 bg-white rounded-xl p-6 border border-slate-100">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold">Profit & Loss Overview</h2>
            <span class="text-xs text-slate-500" id="periodLabel">YTD</span>
          </div>
          <canvas id="pnlChart" height="180"></canvas>
        </article>
        <article class="lg:col-span-2 bg-white rounded-xl p-6 border border-slate-100">
          <h3 class="text-sm font-semibold mb-3">Key Metrics</h3>
          <dl class="grid grid-cols-2 gap-3 text-sm">
            <div class="p-3 rounded-lg bg-slate-50 border"><dt>Revenue</dt><dd class="font-semibold text-emerald-600">₱1,240,000</dd></div>
            <div class="p-3 rounded-lg bg-slate-50 border"><dt>COGS</dt><dd class="font-semibold">₱420,000</dd></div>
            <div class="p-3 rounded-lg bg-slate-50 border"><dt>Operating Exp.</dt><dd class="font-semibold">₱310,000</dd></div>
            <div class="p-3 rounded-lg bg-slate-50 border"><dt>Net Income</dt><dd class="font-semibold text-emerald-600">₱510,000</dd></div>
          </dl>
        </article>
      </div>

      {{-- BALANCE SHEET --}}
      <div data-panel="bs" class="tab-panel hidden grid grid-cols-1 lg:grid-cols-2 gap-4">
        <article class="bg-white rounded-xl p-6 border border-slate-100">
          <h2 class="text-base font-semibold mb-2">Assets vs Liabilities</h2>
          <canvas id="bsChart" height="200"></canvas>
        </article>
        <article class="bg-white rounded-xl p-6 border border-slate-100">
          <h3 class="text-sm font-semibold">Equity Summary</h3>
          <ul class="mt-3 space-y-2 text-sm">
            <li class="p-3 rounded-lg bg-slate-50 border flex justify-between"><span>Total Assets</span><b>₱2,400,000</b></li>
            <li class="p-3 rounded-lg bg-slate-50 border flex justify-between"><span>Total Liabilities</span><b>₱1,550,000</b></li>
            <li class="p-3 rounded-lg bg-slate-50 border flex justify-between"><span>Owner's Equity</span><b class="text-emerald-600">₱850,000</b></li>
          </ul>
        </article>
      </div>

      {{-- CASH FLOW --}}
      <div data-panel="cf" class="tab-panel hidden">
        <h2 class="text-base font-semibold mb-3">Cash Flow Overview</h2>
        <canvas id="cfChart" height="200"></canvas>
      </div>

      {{-- PROJECTED REVENUE --}}
      <div data-panel="pr" class="tab-panel hidden">
        <h2 class="text-base font-semibold mb-3">Projected Revenue (Next 12 Months)</h2>
        <canvas id="prChart" height="200"></canvas>
      </div>

      {{-- BANK STATEMENTS --}}
      <div data-panel="bank" class="tab-panel hidden">
        <h2 class="text-base font-semibold mb-3">Bank Balance Trend</h2>
        <canvas id="bankChart" height="200"></canvas>
      </div>
    </div>
  </div>
</section>

{{-- BULK & CSV MODALS --}}
<div id="bulkModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50">
  <div class="bg-white w-[92%] max-w-2xl rounded-2xl p-6 shadow-2xl border border-slate-100">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Bulk Entry Transactions</h3>
      <button onclick="hideBulk()" class="rounded-lg p-2 hover:bg-slate-100">✕</button>
    </div>
    <textarea id="bulkText" class="mt-3 w-full h-48 rounded-xl bg-slate-50 border border-slate-200 p-3 font-mono text-sm" placeholder="2025-09-01, payment, 2500, Client payment, OR-1001"></textarea>
    <div class="mt-4 flex items-center justify-end gap-2">
      <button class="px-4 h-10 rounded-lg bg-slate-100 hover:bg-slate-200" onclick="hideBulk()">Cancel</button>
      <button class="px-4 h-10 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" onclick="parseBulk()">Add Rows</button>
    </div>
  </div>
</div>

<div id="csvModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50">
  <div class="bg-white w-[92%] max-w-md rounded-2xl p-6 shadow-2xl border border-slate-100">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Upload File (CSV)</h3>
      <button onclick="hideCsv()" class="rounded-lg p-2 hover:bg-slate-100">✕</button>
    </div>
    <input id="csvInput" type="file" accept=".csv" class="w-full rounded-lg bg-slate-50 border border-slate-200 p-2" />
    <div class="mt-4 flex items-center justify-end gap-2">
      <button class="px-4 h-10 rounded-lg bg-slate-100 hover:bg-slate-200" onclick="hideCsv()">Cancel</button>
      <button class="px-4 h-10 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" onclick="fakeCsvImport()">Upload</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const tabBtns=document.querySelectorAll('.tab-btn');
const panels=document.querySelectorAll('.tab-panel');
tabBtns.forEach(btn=>btn.addEventListener('click',()=>{
  const key=btn.dataset.tab;
  tabBtns.forEach(b=>b.classList.remove('bg-indigo-600','text-white'));
  btn.classList.add('bg-indigo-600','text-white');
  panels.forEach(p=>p.classList.toggle('hidden',p.dataset.panel!==key));
}));
const C=v=>'₱'+Number(v).toLocaleString();
new Chart(pnlChart,{type:'bar',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[
  {label:'Revenue',data:[180000,190000,210000,200000,230000,240000],backgroundColor:'#2563eb'},
  {label:'Expense',data:[120000,110000,130000,125000,135000,140000],backgroundColor:'#94a3b8'}]},
  options:{plugins:{legend:{position:'bottom'}},scales:{y:{ticks:{callback:v=>C(v)}}}}});
new Chart(bsChart,{type:'doughnut',data:{labels:['Assets','Liabilities'],datasets:[{data:[2400000,1550000],backgroundColor:['#22c55e','#f59e0b'],borderWidth:0}]},options:{plugins:{legend:{position:'bottom'}},cutout:'65%'}});
new Chart(cfChart,{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[{label:'Net Cash',data:[20000,35000,15000,40000,50000,30000],borderColor:'#2563eb',backgroundColor:'#2563eb22',fill:true}]}});
new Chart(prChart,{type:'bar',data:{labels:['Jul','Aug','Sep','Oct','Nov','Dec','Jan'],datasets:[{label:'Projected',data:[18000,19000,22000,26000,28000,30000,25000],backgroundColor:'#0ea5e9'}]}});
new Chart(bankChart,{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun'],datasets:[{label:'Bank Balance',data:[420000,440000,430000,470000,520000,538500],borderColor:'#22c55e',backgroundColor:'#22c55e22',fill:true}]}});

function showBulk(){bulkModal.classList.remove('hidden');bulkModal.classList.add('flex');}
function hideBulk(){bulkModal.classList.add('hidden');}
function showCsv(){csvModal.classList.remove('hidden');csvModal.classList.add('flex');}
function hideCsv(){csvModal.classList.add('hidden');}
function parseBulk(){
  const lines=(bulkText.value||'').split(/\n+/).filter(Boolean);
  if(!lines.length)return Swal.fire('Nothing to import','','info');
  hideBulk();Swal.fire('Imported '+lines.length+' rows','','success');
}
function fakeCsvImport(){
  if(!csvInput.files.length)return Swal.fire('Select a file','','info');
  hideCsv();Swal.fire('CSV Uploaded','','success');
}
bulkEntryBtn.onclick=showBulk;
uploadCsvBtn.onclick=showCsv;
exportBtn.onclick=()=>Swal.fire('Export queued','Your report will download shortly.','success');
</script>
@endpush
