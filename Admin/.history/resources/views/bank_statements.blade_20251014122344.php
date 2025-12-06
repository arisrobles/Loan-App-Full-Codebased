@extends('layouts.app')

@php
  $pageTitle = 'Bank Transactions';
@endphp

@section('head')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* === Global polish === */
    * { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    *::-webkit-scrollbar { height: 8px; width: 8px; }
    *::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }

    .container-page { max-width: 1200px; }

    /* === Buttons === */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border-radius:.625rem;font-weight:600;line-height:1;padding:.6rem .9rem;border:1px solid transparent;transition:transform .12s ease, background .12s ease, border .12s ease, box-shadow .12s ease;white-space:nowrap}
    .btn:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.25)}
    .btn:hover{transform:translateY(-1px)}
    .btn-primary{background:#2563eb;color:#fff}
    .btn-primary:hover{background:#1e40af}
    .btn-primary:active{transform:translateY(0)}
    .btn-outline{color:#0f172a;border-color:#cbd5e1;background:#fff}
    .btn-outline:hover{background:#f8fafc}
    .btn-quiet{color:#334155;background:#f1f5f9;border-color:#e2e8f0}
    .btn-quiet:hover{background:#e2e8f0}
    .btn-xs{padding:.35rem .6rem;font-size:.75rem;border-radius:.5rem}
    .btn-icon{padding:.45rem;width:2.1rem;height:2.1rem}

    /* === Chips & status === */
    .chip{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .6rem;border-radius:999px;border:1px solid #e2e8f0;background:#f8fafc;font-size:.8rem;white-space:nowrap}
    .chip-soft{background:#eef2ff;border-color:#c7d2fe;color:#1e3a8a}
    .status{font-size:.75rem;font-weight:700;border-radius:999px;padding:.25rem .55rem;border:1px solid;white-space:nowrap}
    .st-pending{color:#92400e;background:#fff7ed;border-color:#fed7aa}
    .st-posted{color:#065f46;background:#ecfdf5;border-color:#a7f3d0}
    .st-excluded{color:#1f2937;background:#f1f5f9;border-color:#e5e7eb}
    .tag{font-size:.7rem;color:#334155;background:#e2e8f0;border:1px solid #cbd5e1;border-radius:999px;padding:.15rem .45rem}

    /* === Inputs === */
    .input-wrap{position:relative}
    .input-ico{position:absolute;inset-inline-start:.75rem;inset-block-start:50%;transform:translateY(-50%);width:18px;height:18px;color:#94a3b8;pointer-events:none}
    .input-base{width:100%;border-radius:.625rem;border:1px solid #cbd5e1;background:#fff;padding:.6rem .9rem;padding-inline-start:2.5rem;transition:border .12s ease, box-shadow .12s ease}
    .input-plain{padding-inline-start:.9rem}
    .input-base:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
    .group-money{position:relative}
    .group-money .prefix{position:absolute;inset-block:0;inset-inline-start:.75rem;display:flex;align-items:center;color:#94a3b8}
    .group-money input{padding-inline-start:2rem}

    /* === Table === */
    .table-wrap thead th{position:sticky;top:0;background:#f8fafc;z-index:5;box-shadow:inset 0 -1px 0 #e2e8f0}
    .table-wrap tbody tr:hover{background:#f8fafc}
    .table-wrap tbody tr:nth-child(even){background:#fcfdff}
    .td-trunc{max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .td-num{font-variant-numeric:tabular-nums}

    /* === Cards / sections === */
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;box-shadow:0 1px 0 rgba(2,6,23,.04)}
    .kpi{border:1px solid #e2e8f0;background:linear-gradient(180deg,#ffffff,#f8fafc);border-radius:1rem;padding:.9rem 1rem}
    .kpi .label{color:#64748b;font-size:.8rem;font-weight:600}
    .kpi .value{font-weight:800;font-size:1.15rem;color:#0f172a}

    /* === Segmented nav === */
    .segmented a{padding:.4rem .8rem;border-radius:999px;border:1px solid transparent;white-space:nowrap}
    .segmented a.active{background:#e0edff;border-color:#bfdbfe;color:#1e40af}
    .segmented a:hover{background:#f1f5f9}

    /* === Action menu === */
    details.menu{position:relative;display:inline-block}
    details.menu>summary{list-style:none;cursor:pointer;border-radius:.5rem;border:1px solid #cbd5e1;background:#fff;width:2.1rem;height:2.1rem;display:flex;align-items:center;justify-content:center}
    details.menu[open]>summary{box-shadow:0 0 0 3px rgba(37,99,235,.15)}
    details.menu .menu-content{position:absolute;right:0;top:110%;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;min-width:160px;box-shadow:0 12px 24px rgba(15,23,42,.08);padding:.35rem;z-index:20}
    details.menu .menu-content form{display:block}
    details.menu .menu-item{width:100%;text-align:left}
    .nowrap{white-space:nowrap}

    /* === COMPACT MODE === */
    .compact .container-page{max-width:100%;padding-inline:.5rem!important}
    .compact header .container-page{padding-block:.5rem!important}
    .compact main .container-page{padding-block:.5rem!important}
    .compact .card{border-radius:.75rem}
    .compact .card.p-4{padding:.75rem!important}
    .compact .kpi{padding:.5rem .6rem!important}
    .compact .segmented a{padding:.25rem .5rem!important}
    .compact .table-wrap thead th{padding:.5rem!important}
    .compact table th,.compact table td{padding:.5rem .5rem!important}
    .compact .btn{padding:.45rem .65rem!important}
    .compact .btn-xs{padding:.3rem .5rem!important;font-size:.72rem}
    .compact .input-base{padding:.45rem .6rem!important}
    .compact .input-wrap .input-ico{inset-inline-start:.5rem}
    .compact .input-base:not(.input-plain){padding-inline-start:2rem!important}
    .compact .p-4{padding:.75rem!important}
    .compact .px-4{padding-left:.5rem!important;padding-right:.5rem!important}
    .compact .py-6{padding-top:.5rem!important;padding-bottom:.5rem!important}
    .compact .space-y-6> :not([hidden])~ :not([hidden]){margin-top:.75rem!important}

    /* ==== PRINT: exactly 5 cm margins ==== */
    @media print{
      @page{size:A4;margin:5cm}
      html,body{padding:0!important;background:#fff!important}
      aside,.btn,.menu,#newTxPanel,.segmented,
      .input-wrap,form[action*="transactions.index"]{display:none!important}
      header{box-shadow:none!important}
      .card,.kpi{border:0!important;box-shadow:none!important}
      .table-wrap thead th{position:static!important;box-shadow:none!important}
      .container-page{max-width:100%!important;padding:0!important;margin:0!important}
    }
  </style>
@endsection

@section('content')
  {{-- Header --}}
  <header class="bg-white shadow-sm mb-4">
    <div class="container-page mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3 min-w-0">
          <a href="{{ route('transactions.index', ['accountId' => $accountId]) }}"
             class="p-2 rounded-lg bg-blue-50 text-blue-700 font-semibold">BANK ACCOUNTS</a>
          <span class="text-slate-400">/</span>
          <div class="text-slate-900 font-semibold truncate">Account #{{ $accountId }}</div>
        </div>
        <div class="grid grid-cols-2 gap-3 w-full sm:w-auto">
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
      <div class="mt-1 text-right text-xs text-slate-500">Asia/Manila</div>
    </div>
  </header>

  {{-- Flash --}}
  <div class="container-page mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mt-4 rounded-lg bg-green-50 text-green-800 px-4 py-2 border border-green-200">{{ session('status') }}</div>
    @endif
    @if (session('error'))
      <div class="mt-4 rounded-lg bg-red-50 text-red-800 px-4 py-2 border-red-200">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="mt-4 rounded-lg bg-red-50 text-red-800 px-4 py-2 border-red-200">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif
  </div>

  {{-- Content --}}
  <main class="flex-1 overflow-y-auto">
    <div class="container-page mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- Controls --}}
      <div class="card p-4">
        <form id="filters" method="GET"
              action="{{ route('transactions.index', ['accountId' => $accountId]) }}"
              class="flex flex-wrap gap-3 items-center">
          {{-- Search --}}
          <div class="input-wrap grow basis-64 min-w-[240px]">
            <svg class="input-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input name="q" id="search" type="text" value="{{ $q ?? '' }}" placeholder="Search by contact, description, amount, class…" class="input-base"/>
          </div>
          <button type="button" id="clearSearchBtn" class="btn btn-outline">Clear</button>

          <div class="ml-auto flex items-center gap-2 flex-wrap">
            <button type="button" id="newTxBtn" class="btn btn-primary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
              New
            </button>
            <button type="button" id="importBtn" class="btn btn-outline">Import CSV</button>
          </div>

          <input type="hidden" name="tab" value="{{ $tab ?? 'pending' }}">
          <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
          <input type="hidden" name="date_from" value="{{ $from ?? '' }}">
          <input type="hidden" name="date_to" value="{{ $to ?? '' }}">
        </form>

        {{-- Hidden import form --}}
        <form id="importForm" method="POST" enctype="multipart/form-data"
              action="{{ route('transactions.import', ['accountId' => $accountId]) }}" class="hidden">
          @csrf
          <input type="file" name="file" id="csvFile" accept=".csv,text/csv" class="hidden"/>
          <input type="hidden" name="kind" value="auto"/>
          <input type="hidden" name="statement_end_date" id="stmtDateHidden"/>
          <input type="hidden" name="statement_ending_balance" id="stmtBalHidden"/>
        </form>

        {{-- Tabs / Types / Date --}}
        <div class="mt-4 pt-4 border-t border-slate-200 flex flex-wrap gap-3 items-center">
          @php
            $makeUrl = fn($tabVal,$typeVal=null) => route('transactions.index', array_filter([
              'accountId'=>$accountId,
              'tab'=>$tabVal,
              'type'=>$typeVal ?? ($type ?? 'all'),
              'q'=>$q ?? '',
              'date_from'=>$from ?? '',
              'date_to'=>$to ?? ''
            ]));
          @endphp

          <div class="segmented flex items-center gap-2 overflow-x-auto">
            <a href="{{ $makeUrl('pending') }}"  class="{{ $tab==='pending' ? 'active' : '' }}">Pending <span class="chip chip-soft ml-1">{{ number_format($counts['pending']) }}</span></a>
            <a href="{{ $makeUrl('posted') }}"   class="{{ $tab==='posted' ? 'active' : '' }}">Posted <span class="chip chip-soft ml-1">{{ number_format($counts['posted']) }}</span></a>
            <a href="{{ $makeUrl('excluded') }}" class="{{ $tab==='excluded' ? 'active' : '' }}">Excluded <span class="chip chip-soft ml-1">{{ number_format($counts['excluded']) }}</span></a>
          </div>

          <div class="segmented flex items-center gap-1">
            <a href="{{ $makeUrl($tab,'all') }}"       class="{{ $type==='all' ? 'active' : '' }}">All</a>
            <a href="{{ $makeUrl($tab,'in') }}"        class="{{ $type==='in' ? 'active' : '' }}">Money In</a>
            <a href="{{ $makeUrl($tab,'out') }}"       class="{{ $type==='out' ? 'active' : '' }}">Money Out</a>
            <a href="{{ $makeUrl($tab,'suggested') }}" class="{{ $type==='suggested' ? 'active' : '' }}">Suggested</a>
            <a href="{{ $makeUrl($tab,'transfer') }}"  class="{{ $type==='transfer' ? 'active' : '' }}">Transfer</a>
          </div>

          <form method="GET" action="{{ route('transactions.index', ['accountId' => $accountId]) }}" class="flex items-center gap-2 ml-auto flex-wrap">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <input type="hidden" name="tab" value="{{ $tab ?? 'pending' }}">
            <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
            <div class="input-wrap">
              <svg class="input-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <input type="date" name="date_from" value="{{ $from ?? '' }}" class="input-base"/>
            </div>
            <span class="text-slate-400">–</span>
            <div class="input-wrap">
              <svg class="input-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <input type="date" name="date_to" value="{{ $to ?? '' }}" class="input-base"/>
            </div>
            <button class="btn btn-outline">Apply</button>
            <a href="{{ route('transactions.index', ['accountId' => $accountId, 'tab' => $tab ?? 'pending', 'type' => $type ?? 'all', 'q' => $q ?? '']) }}" class="btn btn-quiet">Reset</a>
          </form>
        </div>
      </div>

      {{-- Manual create --}}
      <div id="newTxPanel" class="card p-4 hidden">
        <form method="POST" action="{{ route('transactions.store', ['accountId' => $accountId]) }}" class="space-y-6">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Kind <span class="text-red-500">*</span></span>
              <select name="kind" class="input-base input-plain" required>
                <option value="bank">Bank</option>
                <option value="journal">Journal</option>
              </select>
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Date <span class="text-red-500">*</span></span>
              <input type="date" name="tx_date" value="{{ old('tx_date', now()->toDateString()) }}" class="input-base input-plain" required>
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Ref Code</span>
              <input type="text" name="ref_code" value="{{ old('ref_code') }}" class="input-base input-plain" maxlength="32" placeholder="Optional">
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Transfer?</span>
              <select name="is_transfer" class="input-base input-plain">
                <option value="0">No</option>
                <option value="1">Yes</option>
              </select>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Contact (bank)</span>
              <input type="text" name="contact_display" class="input-base input-plain" maxlength="191" placeholder="e.g. BPI / 7-Eleven">
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Ledger Contact</span>
              <input type="text" name="ledger_contact" class="input-base input-plain" maxlength="191" placeholder="e.g. Accounts Receivable">
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Account</span>
              <input type="text" name="account_name" class="input-base input-plain" maxlength="191" placeholder="e.g. Utilities Expense">
            </label>
          </div>

          <label class="field">
            <span class="block text-sm font-semibold text-slate-600 mb-1">Description</span>
            <input type="text" name="description" class="input-base input-plain" maxlength="255" placeholder="What is this transaction for?">
          </label>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Spent</span>
              <div class="group-money">
                <span class="prefix">₱</span>
                <input id="new_spent" type="number" step="0.01" name="spent" class="input-base input-plain" placeholder="0.00" inputmode="decimal">
              </div>
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Received</span>
              <div class="group-money">
                <span class="prefix">₱</span>
                <input id="new_received" type="number" step="0.01" name="received" class="input-base input-plain" placeholder="0.00" inputmode="decimal">
              </div>
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Class</span>
              <input type="text" name="tx_class" class="input-base input-plain" maxlength="191" placeholder="e.g. Master Fund1">
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Remarks</span>
              <input type="text" name="remarks" class="input-base input-plain" maxlength="255" placeholder="Optional notes">
            </label>
            <label class="field">
              <span class="block text-sm font-semibold text-slate-600 mb-1">Source</span>
              <input type="text" name="source" class="input-base input-plain" maxlength="64" placeholder="e.g. Bank credit">
            </label>
          </div>

          <div class="flex items-center gap-2">
            <button class="btn btn-primary">Save</button>
            <button type="button" id="newTxCancel" class="btn btn-outline">Cancel</button>
          </div>
        </form>
      </div>

      {{-- Table --}}
      <div class="card overflow-hidden">
        <div class="overflow-x-auto table-wrap">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-600">
                <th class="px-4 py-3 font-medium">#</th>
                <th class="px-4 py-3 font-medium">Date</th>
                <th class="px-4 py-3 font-medium">Contact</th>
                <th class="px-4 py-3 font-medium">Description</th>
                <th class="px-4 py-3 font-medium text-right">Spent</th>
                <th class="px-4 py-3 font-medium text-right">Received</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Reconcile</th>
                <th class="px-4 py-3 font-medium">Account</th>
                <th class="px-4 py-3 font-medium">Class</th>
                <th class="px-4 py-3 font-medium text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse ($transactions as $i => $tx)
                @php
                  $st = strtolower($tx->status ?? 'pending');
                  $stCls = $st==='posted' ? 'st-posted' : ($st==='excluded' ? 'st-excluded' : 'st-pending');
                @endphp
                <tr>
                  <td class="px-4 py-3 font-medium">{{ $transactions->firstItem() + $i }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($tx->tx_date)->toDateString() }}</td>
                  <td class="px-4 py-3 td-trunc" title="{{ $tx->contact_display }}">{{ $tx->contact_display }}</td>
                  <td class="px-4 py-3 td-trunc" title="{{ $tx->description }}">{{ $tx->description }}</td>
                  <td class="px-4 py-3 text-right td-num">{{ $tx->spent !== null ? number_format($tx->spent, 2) : '—' }}</td>
                  <td class="px-4 py-3 text-right td-num">{{ $tx->received !== null ? number_format($tx->received, 2) : '—' }}</td>
                  <td class="px-4 py-3">
                    <span class="status {{ $stCls }}">{{ ucfirst($st) }}</span>
                    @if(!empty($tx->is_transfer))
                      <span class="tag ml-1">Transfer</span>
                    @endif
                    @if(!empty($tx->match_id))
                      <span class="tag ml-1">Suggested</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <form method="POST"
                          action="{{ route('transactions.reconcile', ['accountId' => $accountId, 'transactionId' => $tx->id]) }}"
                          class="inline-flex gap-2 items-center flex-wrap">
                      @csrf
                      @method('PATCH')
                      <select name="reconcile_status" class="rounded border-slate-300 text-sm px-2 py-1">
                        @foreach (['pending'=>'Pending','ok'=>'OK','match'=>'Match'] as $val=>$label)
                          <option value="{{ $val }}" @selected($tx->reconcile_status===$val)>{{ $label }}</option>
                        @endforeach
                      </select>
                      <input type="hidden" name="q" value="{{ $q ?? '' }}">
                      <input type="hidden" name="tab" value="{{ $tab ?? 'pending' }}">
                      <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
                      <input type="hidden" name="date_from" value="{{ $from ?? '' }}">
                      <input type="hidden" name="date_to" value="{{ $to ?? '' }}">
                      <button class="btn btn-xs btn-primary">Save</button>
                    </form>
                  </td>
                  <td class="px-4 py-3 td-trunc" title="{{ $tx->account_name }}">{{ $tx->account_name }}</td>
                  <td class="px-4 py-3 td-trunc" title="{{ $tx->tx_class }}">{{ $tx->tx_class }}</td>
                  <td class="px-4 py-3 text-right whitespace-nowrap">
                    {{-- Desktop actions --}}
                    <div class="hidden sm:inline-flex items-center gap-1">
                      @if(($tx->status ?? 'pending') === 'pending')
                        <form method="POST" action="{{ route('transactions.post', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">
                          @csrf @method('PATCH')
                          <button class="btn btn-xs btn-primary">Post</button>
                        </form>
                        <form method="POST" action="{{ route('transactions.exclude', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">
                          @csrf @method('PATCH')
                          <button class="btn btn-xs btn-outline">Exclude</button>
                        </form>
                      @elseif(($tx->status ?? '') === 'excluded')
                        <form method="POST" action="{{ route('transactions.restore', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">
                          @csrf @method('PATCH')
                          <button class="btn btn-xs btn-outline">Restore</button>
                        </form>
                      @else
                        <span class="text-slate-500 text-xs">Posted {{ optional($tx->posted_at)->diffForHumans() }}</span>
                      @endif
                    </div>

                    {{-- Mobile overflow menu --}}
                    <div class="sm:hidden inline-block">
                      <details class="menu">
                        <summary aria-label="Actions">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>
                          </svg>
                        </summary>
                        <div class="menu-content">
                          @if(($tx->status ?? 'pending') === 'pending')
                            <form method="POST" action="{{ route('transactions.post', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">
                              @csrf @method('PATCH')
                              <button class="btn btn-xs btn-primary w-full menu-item">Post</button>
                            </form>
                            <form method="POST" action="{{ route('transactions.exclude', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">
                              @csrf @method('PATCH')
                              <button class="btn btn-xs btn-outline w-full menu-item">Exclude</button>
                            </form>
                          @elseif(($tx->status ?? '') === 'excluded')
                            <form method="POST" action="{{ route('transactions.restore', ['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">
                              @csrf @method('PATCH')
                              <button class="btn btn-xs btn-outline w-full menu-item">Restore</button>
                            </form>
                          @else
                            <div class="px-3 py-2 text-xs text-slate-600">Posted {{ optional($tx->posted_at)->diffForHumans() }}</div>
                          @endif
                        </div>
                      </details>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="px-4 py-10">
                    <div class="border-2 border-dashed border-slate-300 bg-slate-50/60 rounded-xl p-8 text-center text-slate-600">
                      <div class="text-lg font-semibold mb-1">No transactions found</div>
                      <div class="text-sm">Try adjusting your search, date range, or type filters.</div>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="p-4 flex flex-wrap gap-3 items-center justify-between border-t bg-slate-50">
          <div class="text-sm text-slate-600">
            {{ number_format($transactions->total()) }} transactions
            @if(($q ?? '') !== '') • Search: <span class="font-medium">“{{ $q }}”</span>@endif
            @if(($from ?? '') || ($to ?? '')) • Date: <span class="font-medium">{{ $from ?: '…' }} – {{ $to ?: '…' }}</span>@endif
            @if(($type ?? 'all') !== 'all') • Type: <span class="font-medium">{{ ucfirst($type) }}</span>@endif
          </div>
          <div class="w-full sm:w-auto">
            <div class="inline-block">{{ $transactions->onEachSide(1)->links() }}</div>
          </div>
        </div>
      </div>

    </div>
  </main>
@endsection

@section('scripts')
  <script>
    // Clear search
    (function(){
      const clearBtn = document.getElementById('clearSearchBtn');
      const searchEl = document.getElementById('search');
      if (clearBtn && searchEl) {
        clearBtn.addEventListener('click', () => {
          searchEl.value = '';
          document.getElementById('filters').submit();
        });
      }
    })();

    // Import CSV + optional statement prompt
    (function(){
      const importBtn  = document.getElementById('importBtn');
      const csvFile    = document.getElementById('csvFile');
      const importForm = document.getElementById('importForm');
      if (importBtn && csvFile && importForm) {
        importBtn.addEventListener('click', () => {
          const endDate = prompt('Statement ending date (YYYY-MM-DD)? Leave blank to skip.');
          const endBal  = endDate ? prompt('Statement ending balance? e.g. 106.12') : '';
          if (endDate) document.getElementById('stmtDateHidden').value = endDate;
          if (endDate && endBal) document.getElementById('stmtBalHidden').value = endBal;
          csvFile.click();
        });
        csvFile.addEventListener('change', () => { if (csvFile.files.length) importForm.submit(); });
      }
    })();

    // Toggle "New Transaction" panel
    (function(){
      const newTxBtn    = document.getElementById('newTxBtn');
      const newTxPanel  = document.getElementById('newTxPanel');
      const newTxCancel = document.getElementById('newTxCancel');
      if (newTxBtn && newTxPanel) newTxBtn.addEventListener('click', () => newTxPanel.classList.toggle('hidden'));
      if (newTxCancel && newTxPanel) newTxCancel.addEventListener('click', () => newTxPanel.classList.add('hidden'));
    })();

    // Enforce either Spent or Received
    (function(){
      const spentInput    = document.getElementById('new_spent');
      const receivedInput = document.getElementById('new_received');
      function exclusive(e) {
        if (e.target === spentInput && spentInput.value) receivedInput.value = '';
        if (e.target === receivedInput && receivedInput.value) spentInput.value = '';
      }
      if (spentInput && receivedInput) {
        spentInput.addEventListener('input', exclusive);
        receivedInput.addEventListener('input', exclusive);
      }
    })();
  </script>
@endsection
