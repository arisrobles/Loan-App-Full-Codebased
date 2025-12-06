@extends('layouts.app')

@php
  $pageTitle = 'Loan Management';
  $statuses = [
    'new_application' => 'New Application',
    'under_review'    => 'Under Review',
    'approved'        => 'Approved',
    'for_release'     => 'For Release',
    'disbursed'       => 'Disbursed',
    'restructured'    => 'Restructured',
    'closed'          => 'Closed',
    'rejected'        => 'Rejected',
  ];
@endphp

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  html,body {
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
    color:#0f172a;
  }
  .mx-header {
    background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(168,85,247,.85));
    backdrop-filter:blur(14px);
    color:white;
    border-bottom:1px solid rgba(255,255,255,.2);
    box-shadow:0 10px 25px -10px rgba(99,102,241,.4);
  }
  .btn {display:inline-flex;align-items:center;justify-content:center;font-weight:600;border-radius:.75rem;transition:.2s;padding:.6rem 1.1rem;font-size:.875rem;}
  .btn-brand{background:linear-gradient(90deg,#6366f1,#a855f7);color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);}
  .btn-brand:hover{opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline{border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover{background:#f8fafc;}
  .btn-quiet{background:#f1f5f9;color:#1e293b;}
  .btn-quiet:hover{background:#e2e8f0;}

  .metric-card{background:white;border:1px solid #f1f5f9;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 30px -12px rgba(79,70,229,.15);position:relative;overflow:hidden;}
  .metric-card h6{font-size:.7rem;text-transform:uppercase;color:#94a3b8;margin-bottom:.25rem;letter-spacing:.04em;font-weight:600;}
  .metric-card p{font-size:1.25rem;font-weight:700;}
  .table-container{overflow:auto;border-radius:1rem;position:relative;}
  table{width:100%;border-collapse:collapse;min-width:950px;}
  thead{background:linear-gradient(to right,#f8fafc,#eef2ff);text-transform:uppercase;font-size:.7rem;color:#475569;position:sticky;top:0;z-index:10;}
  th,td{padding:.9rem 1rem;text-align:left;}
  thead th{font-weight:700;border-bottom:1px solid #e2e8f0;}
  tbody tr:nth-child(even){background:#fdfdfd;}
  tbody tr:hover td{background:#f4f6ff;transition:.25s;box-shadow:inset 0 0 0 9999px rgba(99,102,241,.03);}
  .badge{display:inline-flex;align-items:center;padding:0.25rem .7rem;border-radius:9999px;font-size:.75rem;font-weight:600;}
  .pagination{display:flex;gap:.25rem;align-items:center;justify-content:center;}
  .pagination a{padding:.4rem .7rem;border-radius:.5rem;font-size:.8rem;font-weight:500;background:white;border:1px solid #e2e8f0;color:#475569;}
  .pagination .active{background:#6366f1;color:white;}
</style>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mx-header rounded-2xl mb-8 shadow-md">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold">Loan Management</h2>
      <p class="text-sm text-indigo-100">Applications, disbursements, and repayments overview</p>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-brand" onclick="openCreateModal()">+ New Loan</button>
      <button class="btn btn-outline" onclick="exportCsv()">Export CSV</button>
    </div>
  </div>
</div>

{{-- METRICS --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5 mb-8">
  <div class="metric-card"><h6>Applications</h6><p>{{ $summary['new_application'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Under Review</h6><p>{{ $summary['under_review'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Approved</h6><p>{{ $summary['approved'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>For Release</h6><p>{{ $summary['for_release'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Disbursed</h6><p>{{ $summary['disbursed'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Missed</h6><p>{{ $summary['missed'] ?? 0 }}</p></div>
</div>

{{-- FILTER PANEL --}}
<form action="{{ route('loans.index') }}" method="GET"
      class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 p-5 grid md:grid-cols-12 gap-3 items-end mb-8">
  <div class="md:col-span-4">
    <label class="block text-sm font-semibold text-gray-600 mb-1">Search</label>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Borrower / Ref / Notes">
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
    <select name="status">
      <option value="">All</option>
      @foreach($statuses as $key=>$label)
        <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
      @endforeach
    </select>
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-gray-600 mb-1">Months</label>
    <select name="months">
      <option value="6" @selected(request('months')==6)>Last 6 months</option>
      <option value="12" @selected(request('months')==12)>Last 12 months</option>
    </select>
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-gray-600 mb-1">Rows per page</label>
    <select name="per_page">
      <option value="10" @selected(request('per_page')==10)>10</option>
      <option value="25" @selected(request('per_page')==25)>25</option>
      <option value="50" @selected(request('per_page')==50)>50</option>
    </select>
  </div>
  <div class="md:col-span-12 flex gap-2 mt-2">
    <button class="btn btn-brand">Apply Filters</button>
    <a href="{{ route('loans.index') }}" class="btn btn-outline">Reset</a>
  </div>
</form>

{{-- PIPELINE CHART --}}
<div class="bg-white rounded-2xl p-6 mb-8 border border-gray-100 shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)]">
  <h3 class="text-base font-semibold mb-4">Pipeline Trends</h3>
  <canvas id="pipelineTrends" height="160"></canvas>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 overflow-hidden">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Borrower</th>
          <th>Amount</th>
          <th>Applied</th>
          <th>Release</th>
          <th>Status</th>
          <th>Outstanding</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($loans as $i => $loan)
          @php
            $idx = $loans->firstItem() + $i;
            $status = $loan->status ?? 'new_application';
          @endphp
          <tr class="hover:bg-indigo-50/40 hover:shadow-[0_4px_16px_-8px_rgba(99,102,241,0.25)] transition">
            <td>{{ $idx }}</td>
            <td><strong>{{ $loan->borrower_name }}</strong></td>
            <td>₱{{ number_format($loan->principal_amount,2) }}</td>
            <td>{{ optional($loan->application_date)->toDateString() }}</td>
            <td>{{ $loan->release_date ?: '-' }}</td>
            <td><span class="badge" data-status="{{ $status }}">{{ ucfirst(str_replace('_',' ',$status)) }}</span></td>
            <td>₱{{ number_format(max(0,$loan->total_disbursed - $loan->total_paid),2) }}</td>
            <td>
              <details class="relative">
                <summary class="cursor-pointer px-3 py-1.5 rounded-md border border-gray-200 text-xs bg-gray-50 hover:bg-gray-100">Actions ▾</summary>
                <div class="absolute z-20 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-md p-2 space-y-1">
                  @if($status !== 'approved')
                    <form method="POST" action="{{ route('loans.move',['loan'=>$loan->id]) }}">
                      @csrf <input type="hidden" name="to_status" value="approved">
                      <button class="w-full text-left px-2 py-1 rounded hover:bg-gray-50" type="submit">Approve</button>
                    </form>
                  @endif
                  @if($status === 'approved')
                    <form method="POST" action="{{ route('loans.move',['loan'=>$loan->id]) }}">
                      @csrf <input type="hidden" name="to_status" value="for_release">
                      <button class="w-full text-left px-2 py-1 rounded hover:bg-gray-50" type="submit">Mark for Release</button>
                    </form>
                  @endif
                  @if(in_array($status,['for_release','approved']))
                    <form method="POST" action="{{ route('loans.move',['loan'=>$loan->id]) }}">
                      @csrf <input type="hidden" name="to_status" value="disbursed">
                      <div class="px-2 py-1">
                        <label class="text-xs text-gray-600">Release date</label>
                        <input type="date" name="release_date" class="mt-1 w-full border rounded px-2 h-7 text-xs">
                      </div>
                      <button class="w-full text-left px-2 py-1 rounded hover:bg-gray-50 text-indigo-600" type="submit">Disburse</button>
                    </form>
                  @endif
                  @if($status === 'disbursed')
                    <form method="POST" action="{{ route('loans.move',['loan'=>$loan->id]) }}">
                      @csrf <input type="hidden" name="to_status" value="closed">
                      <button class="w-full text-left px-2 py-1 rounded hover:bg-gray-50 text-emerald-600" type="submit">Close</button>
                    </form>
                  @endif
                  @if(!in_array($status,['rejected','closed']))
                    <form method="POST" action="{{ route('loans.move',['loan'=>$loan->id]) }}">
                      @csrf <input type="hidden" name="to_status" value="rejected">
                      <button class="w-full text-left px-2 py-1 rounded hover:bg-gray-50 text-rose-600" type="submit">Reject</button>
                    </form>
                  @endif
                </div>
              </details>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center py-10 text-gray-400 italic">No loans found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex justify-between items-center px-5 py-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-600">
    <div>
      Showing 
      <span class="font-semibold">{{ $loans->firstItem() }}</span>–
      <span class="font-semibold">{{ $loans->lastItem() }}</span>
      of <span class="font-semibold">{{ $loans->total() }}</span>
    </div>
    <div class="pagination">{{ $loans->onEachSide(1)->links() }}</div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function openCreateModal(){alert('TODO: open create modal')}
function exportCsv(){alert('TODO: export CSV')}

// Chart.js for pipeline trends
document.addEventListener('DOMContentLoaded',()=>{
  const el=document.getElementById('pipelineTrends');
  if(!el)return;
  const ctx=el.getContext('2d');
  const labels=@json($chartLabels);
  const datasetsMap=@json($chartDatasets);
  const palette=['#94a3b8','#f59e0b','#3b82f6','#6366f1','#10b981'];
  const datasets=Object.keys(datasetsMap).map((k,i)=>({
    label:k,data:datasetsMap[k],backgroundColor:palette[i%palette.length],
    borderRadius:8,stack:'pipeline'
  }));
  new Chart(ctx,{
    type:'bar',data:{labels,datasets},
    options:{
      responsive:true,interaction:{intersect:false,mode:'index'},
      plugins:{legend:{position:'bottom'},tooltip:{callbacks:{label:c=>` ${c.dataset.label}: ${c.parsed.y}`}}},
      scales:{x:{stacked:true},y:{stacked:true,ticks:{precision:0}}}
    }
  });
});
</script>
@endsection
