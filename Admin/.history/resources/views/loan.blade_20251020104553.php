@extends('layouts.app')

@section('title','Loan Applications')
@section('page-title','Loan Applications')
@section('page-subtitle','Manage approvals, disbursements & repayments')

@section('toolbar')
  <select id="density" class="h-9 px-2 rounded border text-sm hidden md:block" onchange="setDensity(this.value)">
    <option value="comfortable">Comfortable</option>
    <option value="compact">Compact</option>
  </select>
  <button id="themeBtn" class="h-9 px-3 rounded border text-sm" type="button" onclick="toggleTheme()">Theme: Light</button>
@endsection

@push('head')
{{-- ✅ Include Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  body::before{content:"";position:fixed;inset:-20%;z-index:-1;background:
    radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.08), transparent 60%),
    radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.08), transparent 60%),
    radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.06), transparent 60%)}
  .stuck{position:sticky;top:0;z-index:30}
  .table-sticky thead th{position:sticky;top:0;background:#f8fafc;z-index:10}
  .btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 12px;border-radius:10px;font-size:.875rem}
  .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
  .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e2e8f0}
  .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;font-size:12px;font-weight:600}
  .kbd{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f1f5f9;border:1px solid #e2e8f0;border-bottom-width:2px;padding:.1rem .35rem;border-radius:.375rem}
  .actions-cell{position:relative;overflow:visible}
  details[open] > summary::after{content:"";position:fixed;inset:0}
  .compact th,.compact td{padding:.5rem!important}
  .compact .h-10{height:2.25rem!important}
  .compact .h-9{height:2.125rem!important}
</style>
@endpush

@section('content')

{{-- Filters & Summary Sections --}}
<div class="stuck bg-white/90 backdrop-blur border-b border-slate-200">
  <div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-3 flex flex-col md:flex-row md:items-center gap-3">
    <form id="filterForm" method="GET" action="{{ route('loans.index') }}" class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-2">
      <div class="md:col-span-2 relative">
        <input id="qInput" name="q" value="{{ $filters['q'] ?? '' }}"
               class="h-10 pl-9 pr-3 rounded-lg bg-slate-100 text-sm outline-none w-full"
               placeholder="Search borrower or reference… (Press Enter)"/>
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      </div>

      <select name="status" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(['new_application'=>'New Application','under_review'=>'Under Review','approved'=>'Approved','for_release'=>'For Release','disbursed'=>'Disbursed','active'=>'Active','restructured'=>'Restructured','closed'=>'Closed','rejected'=>'Rejected'] as $key=>$label)
          <option value="{{ $key }}" @selected(($filters['status'] ?? '')===$key)>{{ $label }}</option>
        @endforeach
      </select>

      <select name="months" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" onchange="this.form.submit()">
        <option value="6"  @selected(($filters['months'] ?? 6)==6)>Last 6 months</option>
        <option value="12" @selected(($filters['months'] ?? 6)==12)>Last 12 months</option>
      </select>

      <select name="per_page" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" onchange="this.form.submit()">
        <option value="10" @selected(($filters['per_page'] ?? 10)==10)>10 / page</option>
        <option value="25" @selected(($filters['per_page'] ?? 10)==25)>25 / page</option>
        <option value="50" @selected(($filters['per_page'] ?? 10)==50)>50 / page</option>
      </select>
    </form>
  </div>
</div>

<div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 space-y-6">

  {{-- Summary --}}
  <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
    @foreach([
      ['label'=>'New Applications','value'=>$summary['new_application'] ?? 0],
      ['label'=>'Under Review','value'=>$summary['under_review'] ?? 0],
      ['label'=>'Approved','value'=>$summary['approved'] ?? 0],
      ['label'=>'For Release','value'=>$summary['for_release'] ?? 0],
      ['label'=>'Disbursed','value'=>$summary['disbursed'] ?? 0],
      ['label'=>'Missed','value'=>$summary['missed'] ?? 0],
    ] as $tile)
      <div class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
        <div class="text-xs text-slate-500">{{ $tile['label'] }}</div>
        <div class="mt-2 text-2xl font-semibold">{{ $tile['value'] }}</div>
      </div>
    @endforeach
  </section>

  {{-- Pipeline Graph --}}
  <section class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base md:text-lg font-semibold">Pipeline Trends</h2>
      <span class="text-xs text-slate-500">Monthly stacked counts</span>
    </div>
    <canvas id="pipelineTrends" height="140"></canvas>
  </section>

  {{-- Loan List --}}
  <section class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
    <h2 class="text-base md:text-lg font-semibold mb-3">Loan List</h2>
    <div class="overflow-x-auto rounded-xl border border-slate-100">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="p-3 text-left">Borrower</th>
            <th class="p-3 text-left">Amount</th>
            <th class="p-3 text-left">Applied</th>
            <th class="p-3 text-left">Release</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Outstanding</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($loans as $row)
            <tr class="hover:bg-slate-50">
              <td class="p-3 font-medium">{{ $row->borrower_name ?? 'Borrower' }}</td>
              <td class="p-3">₱{{ number_format((float)$row->principal_amount,2) }}</td>
              <td class="p-3">{{ optional($row->application_date)->format('Y-m-d') }}</td>
              <td class="p-3">{{ $row->release_date ?: '-' }}</td>
              <td class="p-3"><span class="badge bg-slate-100 text-slate-700">{{ ucfirst(str_replace('_',' ',$row->status ?? '')) }}</span></td>
              <td class="p-3">₱{{ number_format(max(0, (float)$row->total_disbursed - (float)$row->total_paid),2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

</div>
@endsection

{{-- Pass chart data from controller --}}
@php
  $chartLabels  = $trends['labels']  ?? ['Jan','Feb','Mar','Apr','May','Jun'];
  $chartSetsRaw = $trends['datasets'] ?? [
    'New' => [10,15,20,25,30,28],
    'Under Review' => [8,10,12,18,22,20],
    'Approved' => [5,7,9,10,12,14],
    'For Release' => [3,4,6,8,10,11],
    'Disbursed' => [2,3,5,7,9,10],
  ];
@endphp

@push('scripts')
<script>
  // Apply theme/density
  (function(){
    const root = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const densitySel = document.getElementById('density');
    const savedTheme = localStorage.getItem('theme') || 'light';
    const savedDensity = localStorage.getItem('density') || 'comfortable';
    function applyTheme(t){ root.classList.toggle('dark', t==='dark'); themeBtn.textContent = 'Theme: ' + (t==='dark'?'Dark':'Light'); }
    function applyDensity(d){ root.classList.toggle('compact', d==='compact'); if(densitySel) densitySel.value=d; }
    applyTheme(savedTheme); applyDensity(savedDensity);
    window.toggleTheme = ()=>{ const next=root.classList.contains('dark')?'light':'dark'; localStorage.setItem('theme',next); applyTheme(next); };
    window.setDensity = (v)=>{ localStorage.setItem('density',v); applyDensity(v); };
  })();

  // ✅ ChartJS Initialization
  document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('pipelineTrends');
    if(!ctx) return;

    const labels = @json($chartLabels);
    const datasetsMap = @json($chartSetsRaw);
    const colors = ['#94a3b8','#f59e0b','#3b82f6','#6366f1','#10b981'];
    const keys = Object.keys(datasetsMap);

    const datasets = keys.map((k,i)=>({
      label: k,
      data: datasetsMap[k],
      backgroundColor: colors[i % colors.length],
      borderRadius: 6,
      stack: 'pipeline'
    }));

    new Chart(ctx, {
      type: 'bar',
      data: { labels, datasets },
      options: {
        responsive:true,
        plugins:{
          legend:{ position:'bottom' },
          tooltip:{
            backgroundColor:'#0f172a',
            titleColor:'#fff',
            bodyColor:'#e5e7eb',
            callbacks:{ label:(ctx)=>` ${ctx.dataset.label}: ${ctx.parsed.y}` }
          }
        },
        scales:{
          x:{ stacked:true, grid:{display:false} },
          y:{ stacked:true, ticks:{ precision:0 }, grid:{ color:'rgba(148,163,184,.2)' } }
        }
      }
    });
  });
</script>
@endpush
