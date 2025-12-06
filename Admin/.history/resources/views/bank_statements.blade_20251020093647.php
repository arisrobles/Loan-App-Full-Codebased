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
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-500: #6b7280;
    --gray-700: #374151;
    --gray-900: #111827;
  }

  body { background: var(--gray-50); color: var(--gray-900); font-family: 'Inter', system-ui, sans-serif; }

  .container-page { max-width: 1200px; margin-inline: auto; }

  /* === Header === */
  header {
    background: white;
    border-bottom: 1px solid var(--gray-200);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }

  /* === KPI cards === */
  .kpi {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: .75rem;
    padding: .85rem 1rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }
  .kpi .label { font-size: .75rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; }
  .kpi .value { font-size: 1.15rem; font-weight: 700; color: var(--primary-dark); }

  /* === Buttons === */
  .btn {
    display:inline-flex;align-items:center;justify-content:center;gap:.4rem;
    font-weight:600;border-radius:.5rem;
    padding:.55rem .9rem;line-height:1;white-space:nowrap;
    transition: all .15s ease;
    border: 1px solid transparent;
  }
  .btn-primary { background: var(--primary); color: white; }
  .btn-primary:hover { background: var(--primary-dark); }
  .btn-outline { background: white; border-color: var(--gray-200); color: var(--gray-900); }
  .btn-outline:hover { background: var(--gray-100); }
  .btn-quiet { background: var(--gray-100); color: var(--gray-700); border: 1px solid var(--gray-200); }
  .btn-quiet:hover { background: var(--gray-200); }
  .btn-xs { padding:.35rem .6rem; font-size:.75rem; }

  /* === Inputs === */
  .input-wrap { position: relative; width: 100%; }
  .input-ico {
    position: absolute; top: 50%; left: .75rem; transform: translateY(-50%);
    width: 18px; height: 18px; color: var(--gray-400);
  }
  .input-base {
    width: 100%; border: 1px solid var(--gray-300);
    border-radius: .5rem; padding: .55rem .9rem .55rem 2.4rem;
    background: white; transition: border .12s ease, box-shadow .12s ease;
  }
  .input-base:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
  }
  .input-plain { padding-left: .9rem; }

  /* === Segmented Tabs === */
  .segmented a {
    display: inline-flex; align-items: center;
    padding: .4rem .9rem; border-radius: 999px;
    color: var(--gray-600); font-weight: 500; text-decoration: none;
    border: 1px solid transparent; transition: all .15s ease;
  }
  .segmented a:hover { background: var(--gray-100); }
  .segmented a.active { background: var(--primary); color: white; border-color: var(--primary); }

  /* === Table === */
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; font-size: .875rem; }
  thead th {
    background: var(--gray-100);
    color: var(--gray-700); font-weight: 600;
    text-align: left; padding: .75rem 1rem;
    border-bottom: 1px solid var(--gray-200);
  }
  tbody td {
    padding: .75rem 1rem; border-bottom: 1px solid var(--gray-100);
  }
  tbody tr:hover { background: var(--gray-50); transition: background .15s ease; }

  /* === Status & Tags === */
  .status {
    display: inline-block; padding: .25rem .55rem;
    border-radius: 999px; font-weight: 600; font-size: .75rem;
  }
  .st-pending { background: #fff7ed; color: #b45309; }
  .st-posted { background: #ecfdf5; color: #065f46; }
  .st-excluded { background: #f3f4f6; color: #374151; }
  .tag {
    font-size: .7rem; color: var(--gray-700);
    background: var(--gray-200); border-radius: 999px;
    padding: .15rem .4rem; margin-left: .25rem;
  }

  /* === Pagination Footer === */
  .table-footer {
    background: var(--gray-50); border-top: 1px solid var(--gray-200);
    padding: 1rem; display: flex; flex-wrap: wrap;
    justify-content: space-between; align-items: center;
  }
  .summary-text { color: var(--gray-600); font-size: .85rem; }

  /* === Card === */
  .card {
    background: white; border: 1px solid var(--gray-200);
    border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }

  /* === Smooth spacing === */
  .section-gap > * + * { margin-top: 1.5rem; }
</style>
@endsection


@section('content')
<header class="mb-6">
  <div class="container-page px-4 py-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="flex items-center gap-3">
      <a href="{{ route('transactions.index', ['accountId' => $accountId]) }}"
         class="p-2 rounded-md bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100">BANK ACCOUNTS</a>
      <span class="text-gray-400">/</span>
      <div class="font-semibold text-gray-800 truncate">Account #{{ $accountId }}</div>
    </div>
    <div class="grid grid-cols-2 gap-3 w-full sm:w-auto">
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

<main class="container-page px-4 space-y-6">

  {{-- Filters --}}
  <div class="card p-4 space-y-4">
    <form id="filters" method="GET"
          action="{{ route('transactions.index', ['accountId' => $accountId]) }}"
          class="flex flex-wrap gap-3 items-center">
      <div class="input-wrap grow basis-64 min-w-[240px]">
        <svg class="input-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input name="q" id="search" type="text" value="{{ $q ?? '' }}" placeholder="Search transactions..." class="input-base"/>
      </div>
      <button type="button" id="clearSearchBtn" class="btn btn-outline">Clear</button>
      <div class="ml-auto flex gap-2">
        <button type="button" id="newTxBtn" class="btn btn-primary">+ New</button>
        <button type="button" id="importBtn" class="btn btn-outline">Import CSV</button>
      </div>
      <input type="hidden" name="tab" value="{{ $tab ?? 'pending' }}">
      <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
      <input type="hidden" name="date_from" value="{{ $from ?? '' }}">
      <input type="hidden" name="date_to" value="{{ $to ?? '' }}">
    </form>

    <div class="border-t border-gray-200 pt-4 flex flex-wrap gap-3 items-center justify-between">
      <div class="segmented flex flex-wrap gap-2">
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'pending']) }}" class="{{ $tab==='pending'?'active':'' }}">Pending</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'posted']) }}" class="{{ $tab==='posted'?'active':'' }}">Posted</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'excluded']) }}" class="{{ $tab==='excluded'?'active':'' }}">Excluded</a>
      </div>

      <div class="segmented flex flex-wrap gap-1">
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>$tab,'type'=>'all']) }}" class="{{ $type==='all'?'active':'' }}">All</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>$tab,'type'=>'in']) }}" class="{{ $type==='in'?'active':'' }}">Money In</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>$tab,'type'=>'out']) }}" class="{{ $type==='out'?'active':'' }}">Money Out</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>$tab,'type'=>'suggested']) }}" class="{{ $type==='suggested'?'active':'' }}">Suggested</a>
        <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>$tab,'type'=>'transfer']) }}" class="{{ $type==='transfer'?'active':'' }}">Transfer</a>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card overflow-hidden">
    <div class="table-wrap">
      <table>
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
        <tbody>
          @forelse ($transactions as $i => $tx)
            @php
              $st = strtolower($tx->status ?? 'pending');
              $stCls = $st==='posted' ? 'st-posted' : ($st==='excluded' ? 'st-excluded' : 'st-pending');
            @endphp
            <tr>
              <td>{{ $transactions->firstItem() + $i }}</td>
              <td>{{ \Illuminate\Support\Carbon::parse($tx->tx_date)->toDateString() }}</td>
              <td>{{ $tx->contact_display }}</td>
              <td>{{ $tx->description }}</td>
              <td class="text-right">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
              <td class="text-right">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
              <td><span class="status {{ $stCls }}">{{ ucfirst($st) }}</span></td>
              <td>{{ $tx->account_name }}</td>
              <td>{{ $tx->tx_class }}</td>
              <td class="text-right">
                @if($st === 'pending')
                  <form method="POST" action="{{ route('transactions.post', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="btn btn-xs btn-primary">Post</button>
                  </form>
                  <form method="POST" action="{{ route('transactions.exclude', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="btn btn-xs btn-outline">Exclude</button>
                  </form>
                @elseif($st === 'excluded')
                  <form method="POST" action="{{ route('transactions.restore', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
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
        {{ number_format($transactions->total()) }} transactions
        @if(($q ?? '') !== '') • Search: “{{ $q }}” @endif
      </div>
      <div>{{ $transactions->onEachSide(1)->links() }}</div>
    </div>
  </div>
</main>
@endsection
