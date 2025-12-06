@extends('layouts.app')

@php
  $pageTitle = 'Bank Transactions';
@endphp

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  :root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-400: #94a3b8;
    --gray-600: #475569;
    --gray-800: #1e293b;
    --radius: 12px;
  }

  /* === General Reset === */
  * {
    font-family: 'Inter', system-ui, sans-serif;
    scrollbar-width: thin;
    scrollbar-color: var(--gray-200) transparent;
  }
  *::-webkit-scrollbar { height: 8px; width: 8px; }
  *::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 999px; }

  body { background: var(--gray-50); color: var(--gray-800); }
  .container-page { max-width: 1200px; margin-inline: auto; }

  /* === Header === */
  header { background: white; border-bottom: 1px solid var(--gray-200); }
  header .page-path {
    display: flex; align-items: center; gap: .5rem; font-weight: 600;
  }
  header .page-path a { color: var(--primary); text-decoration: none; }
  header .kpi {
    display: flex; flex-direction: column; gap: 0.25rem;
    background: white; border: 1px solid var(--gray-200);
    border-radius: var(--radius); padding: 1rem 1.25rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }
  header .kpi .label { font-size: .8rem; color: var(--gray-600); text-transform: uppercase; }
  header .kpi .value { font-weight: 700; font-size: 1.1rem; color: var(--primary-dark); }

  /* === Buttons === */
  .btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    border-radius: 8px; font-weight: 600; line-height: 1;
    padding: .55rem .9rem; transition: all .15s ease;
    border: 1px solid transparent; cursor: pointer;
  }
  .btn svg { width: 18px; height: 18px; }
  .btn-primary { background: var(--primary); color: white; }
  .btn-primary:hover { background: var(--primary-dark); }
  .btn-outline { background: white; border-color: var(--gray-200); color: var(--gray-800); }
  .btn-outline:hover { background: var(--gray-100); }
  .btn-quiet { background: var(--gray-100); color: var(--gray-600); border: 1px solid var(--gray-200); }
  .btn-quiet:hover { background: var(--gray-200); }
  .btn-xs { padding: .35rem .65rem; font-size: .75rem; border-radius: 6px; }

  /* === Inputs === */
  .input-wrap { position: relative; width: 100%; }
  .input-ico {
    position: absolute; top: 50%; left: .75rem; transform: translateY(-50%);
    width: 18px; height: 18px; color: var(--gray-400);
  }
  .input-base {
    width: 100%; border-radius: 8px; border: 1px solid var(--gray-200);
    padding: .6rem .9rem .6rem 2.3rem; background: white;
    transition: border .12s ease, box-shadow .12s ease;
  }
  .input-base:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
  .input-plain { padding-left: .9rem; }

  /* === Card & Sections === */
  .card {
    background: white; border: 1px solid var(--gray-200);
    border-radius: var(--radius); box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }

  /* === Segmented Nav === */
  .segmented a {
    padding: .4rem .9rem; border-radius: 999px;
    font-weight: 500; color: var(--gray-600);
    text-decoration: none; transition: all .15s ease;
  }
  .segmented a.active { background: var(--primary); color: white; }
  .segmented a:hover { background: var(--gray-100); }

  /* === Table === */
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; font-size: .875rem; }
  thead th {
    background: var(--gray-100); text-align: left; font-weight: 600;
    color: var(--gray-600); padding: .75rem 1rem; border-bottom: 1px solid var(--gray-200);
  }
  tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--gray-100); vertical-align: middle; }
  tbody tr:hover { background: var(--gray-50); }

  /* === Status badges === */
  .status {
    display: inline-flex; align-items: center; padding: .25rem .55rem;
    font-size: .75rem; font-weight: 600; border-radius: 999px;
  }
  .st-pending { background: #fff7ed; color: #b45309; }
  .st-posted { background: #ecfdf5; color: #065f46; }
  .st-excluded { background: #f1f5f9; color: #475569; }

  /* === Pagination & footer === */
  .table-footer {
    padding: 1rem; background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
    display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;
  }
  .summary-text { font-size: .875rem; color: var(--gray-600); }

  /* === Compact responsive === */
  @media (max-width: 768px) {
    header .container-page { flex-direction: column; gap: 1rem; }
    .segmented { flex-wrap: wrap; }
  }
</style>
@endsection

@section('content')
<header class="shadow-sm mb-4">
  <div class="container-page px-4 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="page-path">
      <a href="{{ route('transactions.index', ['accountId' => $accountId]) }}">BANK ACCOUNTS</a>
      <span>/</span>
      <div>Account #{{ $accountId }}</div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="kpi">
        <div class="label">Bank</div>
        <div class="value">₱{{ number_format($bankEnding,2) }}</div>
      </div>
      <div class="kpi">
        <div class="label">Posted</div>
        <div class="value">₱{{ number_format($postedBal,2) }}</div>
      </div>
    </div>
  </div>
</header>

<main class="flex-1 overflow-y-auto">
  <div class="container-page px-4 space-y-6 pb-10">

    {{-- Controls --}}
    <div class="card p-4 space-y-4">
      <form id="filters" method="GET" action="{{ route('transactions.index', ['accountId' => $accountId]) }}" class="flex flex-wrap gap-3 items-center">
        <div class="input-wrap grow basis-64">
          <svg class="input-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input name="q" id="search" type="text" value="{{ $q ?? '' }}" placeholder="Search transactions..." class="input-base"/>
        </div>
        <button type="button" id="clearSearchBtn" class="btn btn-outline">Clear</button>
        <div class="ml-auto flex gap-2">
          <button type="button" id="newTxBtn" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg> New
          </button>
          <button type="button" id="importBtn" class="btn btn-outline">Import CSV</button>
        </div>
      </form>

      <div class="flex flex-wrap gap-2 items-center border-t border-gray-200 pt-4">
        <div class="segmented flex gap-1 overflow-x-auto">
          <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'pending']) }}" class="{{ $tab==='pending'?'active':'' }}">Pending</a>
          <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'posted']) }}" class="{{ $tab==='posted'?'active':'' }}">Posted</a>
          <a href="{{ route('transactions.index',['accountId'=>$accountId,'tab'=>'excluded']) }}" class="{{ $tab==='excluded'?'active':'' }}">Excluded</a>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Date</th><th>Contact</th><th>Description</th>
              <th class="text-right">Spent</th><th class="text-right">Received</th>
              <th>Status</th><th>Account</th><th>Class</th><th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($transactions as $i => $tx)
              @php
                $st = strtolower($tx->status ?? 'pending');
                $cls = $st==='posted'?'st-posted':($st==='excluded'?'st-excluded':'st-pending');
              @endphp
              <tr>
                <td>{{ $transactions->firstItem() + $i }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($tx->tx_date)->toDateString() }}</td>
                <td>{{ $tx->contact_display }}</td>
                <td>{{ $tx->description }}</td>
                <td class="text-right">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
                <td class="text-right">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
                <td><span class="status {{ $cls }}">{{ ucfirst($st) }}</span></td>
                <td>{{ $tx->account_name }}</td>
                <td>{{ $tx->tx_class }}</td>
                <td class="text-right">
                  @if($st === 'pending')
                    <form method="POST" action="{{ route('transactions.post',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                      <button class="btn btn-xs btn-primary">Post</button>
                    </form>
                    <form method="POST" action="{{ route('transactions.exclude',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                      <button class="btn btn-xs btn-outline">Exclude</button>
                    </form>
                  @elseif($st === 'excluded')
                    <form method="POST" action="{{ route('transactions.restore',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                      <button class="btn btn-xs btn-outline">Restore</button>
                    </form>
                  @else
                    <span class="text-xs text-gray-500">Posted {{ optional($tx->posted_at)->diffForHumans() }}</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="py-10 text-center text-gray-500">No transactions found</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="summary-text">
          {{ number_format($transactions->total()) }} total records
          @if(($q ?? '') !== '') • <span>Search: “{{ $q }}”</span>@endif
        </div>
        <div>{{ $transactions->onEachSide(1)->links() }}</div>
      </div>
    </div>
  </div>
</main>
@endsection
