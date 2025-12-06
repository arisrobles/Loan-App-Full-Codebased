@extends('layouts.app')

@php
  $pageTitle = 'Bank Transactions';
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
  .btn{display:inline-flex;align-items:center;justify-content:center;font-weight:600;border-radius:.75rem;transition:.2s;padding:.6rem 1.1rem;font-size:.875rem;}
  .btn-brand{background:linear-gradient(90deg,#6366f1,#a855f7);color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);}
  .btn-brand:hover{opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline{border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover{background:#f8fafc;}
  .metric-card{background:white;border:1px solid #f1f5f9;border-radius:1rem;padding:1.25rem;box-shadow:0 10px 30px -12px rgba(79,70,229,.15);}
  .metric-card h6{font-size:.7rem;text-transform:uppercase;color:#94a3b8;margin-bottom:.25rem;letter-spacing:.04em;font-weight:600;}
  .metric-card p{font-size:1.25rem;font-weight:700;}
  table{width:100%;border-collapse:collapse;min-width:950px;}
  th,td{padding:.9rem 1rem;text-align:left;}
  thead{background:linear-gradient(to right,#f8fafc,#eef2ff);text-transform:uppercase;font-size:.7rem;color:#475569;}
  tbody tr:nth-child(even){background:#fdfdfd;}
  tbody tr:hover td{background:#f4f6ff;transition:.25s;}
</style>
@endsection

@section('content')

{{-- GRADIENT HEADER --}}
<div class="mx-header rounded-2xl mb-8 shadow-md">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold">Bank Transactions</h2>
      <p class="text-sm text-indigo-100">Account #{{ $accountId }} — Reconciliation & Posting</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('transactions.index',['accountId'=>$accountId]) }}" class="btn btn-outline">Refresh</a>
      <button class="btn btn-brand" id="importBtnTop">+ Import CSV</button>
    </div>
  </div>
</div>

{{-- METRICS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
  <div class="metric-card"><h6>Bank Ending Balance</h6><p class="text-emerald-600">₱{{ number_format($bankEnding,2) }}</p></div>
  <div class="metric-card"><h6>Posted Balance</h6><p class="text-indigo-600">₱{{ number_format($postedBal,2) }}</p></div>
  <div class="metric-card"><h6>Pending</h6><p class="text-amber-600">{{ number_format($counts['pending'] ?? 0) }}</p></div>
  <div class="metric-card"><h6>Excluded</h6><p>{{ number_format($counts['excluded'] ?? 0) }}</p></div>
</div>

{{-- FILTER PANEL --}}
<form method="GET" action="{{ route('transactions.index', ['accountId'=>$accountId]) }}"
      class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 p-5 grid md:grid-cols-12 gap-3 items-end mb-8">
  <div class="md:col-span-6">
    <label class="block text-sm font-semibold text-gray-600 mb-1">Search</label>
    <input name="q" type="text" value="{{ $q ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" placeholder="Search by contact, description, or amount">
  </div>
  <div class="md:col-span-6 flex gap-2 mt-2">
    <button class="btn btn-brand">Search</button>
    <a href="{{ route('transactions.index',['accountId'=>$accountId]) }}" class="btn btn-outline">Reset</a>
    <button type="button" id="newTxBtn" class="btn btn-outline">New Transaction</button>
    <button type="button" id="importBtn" class="btn btn-outline">Import CSV</button>
  </div>

  {{-- Tabs --}}
  <div class="md:col-span-12 flex flex-wrap gap-2 mt-4 border-t border-slate-200 pt-4 text-sm">
    <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'pending']) }}"
       class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='pending' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
      Pending
    </a>
    <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'posted']) }}"
       class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='posted' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
      Posted
    </a>
    <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'excluded']) }}"
       class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='excluded' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
      Excluded
    </a>
  </div>
</form>

{{-- RECONCILIATION TREND --}}
<div class="bg-white rounded-2xl p-6 mb-8 border border-gray-100 shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)]">
  <h3 class="text-base font-semibold mb-4">Reconciliation Trend</h3>
  <canvas id="reconTrend" height="140"></canvas>
</div>

{{-- TRANSACTIONS TABLE --}}
<div class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-slate-700">
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Contact</th>
          <th>Description</th>
          <th class="text-right">Spent</th>
          <th class="text-right">Received</th>
          <th>Status</th>
          <th>Account</th>
          <th>Class</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse ($transactions as $i => $tx)
          @php $st = strtolower($tx->status ?? 'pending'); @endphp
          <tr class="hover:bg-indigo-50/40 hover:shadow-[0_4px_16px_-8px_rgba(99,102,241,0.25)] transition">
            <td>{{ $transactions->firstItem() + $i }}</td>
            <td>{{ \Carbon\Carbon::parse($tx->tx_date)->toDateString() }}</td>
            <td class="truncate max-w-[180px]">{{ $tx->contact_display }}</td>
            <td class="truncate max-w-[220px]">{{ $tx->description }}</td>
            <td class="text-right text-rose-600">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
            <td class="text-right text-emerald-600">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
            <td>
              @if($st === 'posted')
                <span class="inline-block px-2 py-1 text-xs rounded bg-emerald-50 text-emerald-700 border border-emerald-100">Posted</span>
              @elseif($st === 'excluded')
                <span class="inline-block px-2 py-1 text-xs rounded bg-slate-100 text-slate-600 border border-slate-200">Excluded</span>
              @else
                <span class="inline-block px-2 py-1 text-xs rounded bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
              @endif
            </td>
            <td class="truncate max-w-[160px]">{{ $tx->account_name }}</td>
            <td class="truncate max-w-[160px]">{{ $tx->tx_class }}</td>
            <td class="text-right">
              @if($st==='pending')
                <form method="POST" action="{{ route('transactions.post',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                  <button class="text-xs text-brand-600 hover:underline">Post</button>
                </form>
                <form method="POST" action="{{ route('transactions.exclude',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                  <button class="text-xs text-slate-500 hover:underline ml-2">Exclude</button>
                </form>
              @elseif($st==='excluded')
                <form method="POST" action="{{ route('transactions.restore',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                  <button class="text-xs text-emerald-600 hover:underline">Restore</button>
                </form>
              @else
                <span class="text-xs text-slate-400">Posted {{ optional($tx->posted_at)->diffForHumans() }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="py-10 text-center text-gray-400 italic">No transactions found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex justify-between items-center px-5 py-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-600">
    <div>{{ number_format($transactions->total()) }} transactions total</div>
    <div class="pagination">{{ $transactions->onEachSide(1)->links() }}</div>
  </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const el=document.getElementById('reconTrend');
  if(!el)return;
  const ctx=el.getContext('2d');
  const labels=@json($trendLabels ?? []);
  const datasets=[
    {label:'Posted',data:@json($trendPosted ?? []),backgroundColor:'#6366f1'},
    {label:'Pending',data:@json($trendPending ?? []),backgroundColor:'#f59e0b'}
  ];
  new Chart(ctx,{type:'bar',data:{labels,datasets},options:{
    responsive:true,plugins:{legend:{position:'bottom'}},
    scales:{y:{ticks:{precision:0}}}
  }});
});
</script>
@endsection
