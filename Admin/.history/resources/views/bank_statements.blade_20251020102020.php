@extends('layouts.app')

@php
  $pageTitle = 'Bank Transactions';
@endphp

@section('head')
<style>
  html,body{
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
    color:#0f172a;
  }

  /* ====== HEADER ====== */
  .mx-header{
    background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(168,85,247,.85));
    backdrop-filter:blur(14px);
    color:white;
    border-bottom:1px solid rgba(255,255,255,.2);
    box-shadow:0 10px 25px -10px rgba(99,102,241,.4);
  }
  .mx-header h2{font-weight:600;font-size:1.25rem;}

  /* ====== KPI CARDS ====== */
  .kpi{
    border:1px solid #e2e8f0;
    background:linear-gradient(180deg,#ffffff,#f8fafc);
    border-radius:1rem;
    padding:1rem;
    box-shadow:0 10px 24px -16px rgba(99,102,241,.15);
  }
  .kpi .label{color:#64748b;font-size:.8rem;font-weight:600}
  .kpi .value{font-weight:800;font-size:1.2rem;color:#1e293b}

  /* ====== BUTTONS ====== */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;
    gap:.4rem;font-weight:600;border-radius:.75rem;
    padding:.55rem 1rem;font-size:.875rem;transition:.2s;
    border:1px solid transparent;
  }
  .btn-primary{
    background:linear-gradient(90deg,#6366f1,#a855f7);
    color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);
  }
  .btn-primary:hover{opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline{border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover{background:#f8fafc;}
  .btn-xs{font-size:.75rem;padding:.35rem .65rem;border-radius:.5rem;}

  /* ====== FORM / INPUTS ====== */
  .input-base{
    border:1px solid #e2e8f0;background:#fff;border-radius:.75rem;
    padding:.55rem .75rem;font-size:.875rem;width:100%;
    transition:border .15s, box-shadow .15s;
  }
  .input-base:focus{
    border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15);outline:none;
  }

  /* ====== FILTER CARD ====== */
  .card{
    background:white;border-radius:1rem;
    border:1px solid #eef2ff;
    box-shadow:0 10px 28px -16px rgba(99,102,241,.15);
  }

  /* ====== TABS ====== */
  .segmented a{
    padding:.4rem .8rem;border-radius:999px;border:1px solid transparent;
    white-space:nowrap;font-weight:500;font-size:.85rem;
  }
  .segmented a.active{
    background:#e0edff;border-color:#bfdbfe;color:#1e40af;
  }
  .segmented a:hover{background:#f8fafc;}

  /* ====== TABLE ====== */
  .table-container{overflow:auto;border-radius:1rem;}
  table{width:100%;border-collapse:collapse;min-width:950px;}
  thead{
    background:linear-gradient(to right,#f8fafc,#eef2ff);
    text-transform:uppercase;font-size:.7rem;color:#475569;
    position:sticky;top:0;z-index:10;
  }
  th,td{padding:.9rem 1rem;text-align:left;}
  thead th{font-weight:700;border-bottom:1px solid #e2e8f0;}
  tbody tr:nth-child(even){background:#fdfdfd;}
  tbody tr:hover td{
    background:#f4f6ff;transition:.25s;
    box-shadow:inset 0 0 0 9999px rgba(99,102,241,.03);
  }
  .td-num{text-align:right;font-variant-numeric:tabular-nums;}

  /* ====== STATUS BADGES ====== */
  .status{
    font-size:.75rem;font-weight:700;border-radius:999px;
    padding:.25rem .55rem;border:1px solid;
    display:inline-block;text-transform:capitalize;
  }
  .st-pending{color:#92400e;background:#fff7ed;border-color:#fed7aa;}
  .st-posted{color:#065f46;background:#ecfdf5;border-color:#a7f3d0;}
  .st-excluded{color:#1f2937;background:#f1f5f9;border-color:#e5e7eb;}

  .tag{font-size:.7rem;color:#334155;background:#e2e8f0;border:1px solid #cbd5e1;border-radius:999px;padding:.15rem .45rem;margin-left:.3rem;}

  /* ====== ALERTS ====== */
  .alert{border-radius:.75rem;padding:.75rem 1rem;margin-bottom:1rem;font-size:.875rem;font-weight:500;}
  .alert-success{background:#dcfce7;border:1px solid #86efac;color:#166534;}
  .alert-error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;}
</style>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mx-header rounded-2xl mb-8">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2>Bank Transactions</h2>
      <p class="text-sm text-indigo-100">Manage, reconcile, and post account activity</p>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="kpi">
        <div class="label">Bank</div>
        <div class="value nowrap">₱{{ number_format($bankEnding,2) }}</div>
      </div>
      <div class="kpi">
        <div class="label">Posted</div>
        <div class="value nowrap">₱{{ number_format($postedBal,2) }}</div>
      </div>
    </div>
  </div>
</div>

<div class="container-page mx-auto px-4 sm:px-6 lg:px-8">
  {{-- Alerts --}}
  @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
  @if(session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-error">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- Filters --}}
  <div class="card p-5 mb-8">
    <form method="GET" action="{{ route('transactions.index', ['accountId' => $accountId]) }}" class="flex flex-wrap gap-3 items-center">
      <div class="flex-grow min-w-[240px]">
        <input name="q" type="text" value="{{ $q ?? '' }}" class="input-base" placeholder="Search by description, amount, or class...">
      </div>
      <button class="btn btn-outline">Search</button>
      <a href="{{ route('transactions.index', ['accountId'=>$accountId]) }}" class="btn btn-quiet">Reset</a>
      <button type="button" id="newTxBtn" class="btn btn-primary">+ New</button>
      <button type="button" id="importBtn" class="btn btn-outline">Import CSV</button>
    </form>

    <div class="mt-5 flex flex-wrap gap-3 items-center border-t border-slate-200 pt-4">
      <div class="segmented flex gap-2">
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'pending']) }}"  class="{{ $tab==='pending'?'active':'' }}">Pending</a>
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'posted']) }}"   class="{{ $tab==='posted'?'active':'' }}">Posted</a>
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'excluded']) }}" class="{{ $tab==='excluded'?'active':'' }}">Excluded</a>
      </div>
      <div class="segmented flex gap-2">
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>$tab,'type'=>'all']) }}" class="{{ $type==='all'?'active':'' }}">All</a>
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>$tab,'type'=>'in']) }}"  class="{{ $type==='in'?'active':'' }}">Money In</a>
        <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>$tab,'type'=>'out']) }}" class="{{ $type==='out'?'active':'' }}">Money Out</a>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card overflow-hidden">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Date</th><th>Contact</th><th>Description</th>
            <th class="text-right">Spent</th><th class="text-right">Received</th>
            <th>Status</th><th>Account</th><th>Class</th><th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($transactions as $i => $tx)
            @php $st = strtolower($tx->status ?? 'pending'); @endphp
            <tr>
              <td>{{ $transactions->firstItem() + $i }}</td>
              <td>{{ \Carbon\Carbon::parse($tx->tx_date)->toDateString() }}</td>
              <td>{{ $tx->contact_display }}</td>
              <td>{{ $tx->description }}</td>
              <td class="td-num text-rose-600">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
              <td class="td-num text-emerald-600">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
              <td>
                <span class="status st-{{ $st }}">{{ ucfirst($st) }}</span>
                @if($tx->is_transfer)<span class="tag">Transfer</span>@endif
              </td>
              <td>{{ $tx->account_name }}</td>
              <td>{{ $tx->tx_class }}</td>
              <td class="text-right">
                @if($st==='pending')
                  <form method="POST" action="{{ route('transactions.post',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="btn btn-xs btn-primary">Post</button>
                  </form>
                  <form method="POST" action="{{ route('transactions.exclude',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="btn btn-xs btn-outline">Exclude</button>
                  </form>
                @elseif($st==='excluded')
                  <form method="POST" action="{{ route('transactions.restore',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="btn btn-xs btn-outline">Restore</button>
                  </form>
                @else
                  <span class="text-xs text-slate-500">Posted {{ optional($tx->posted_at)->diffForHumans() }}</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center py-10 text-slate-500 italic">
                No transactions found
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4 border-t bg-gray-50 text-sm text-slate-600 flex justify-between items-center flex-wrap gap-3">
      <div>{{ number_format($transactions->total()) }} records total</div>
      <div>{{ $transactions->onEachSide(1)->links() }}</div>
    </div>
  </div>
</div>

@endsection
