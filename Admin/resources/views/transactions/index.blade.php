{{-- resources/views/transactions/index.blade.php --}}
@extends('layouts.app')

@php
    $pageTitle = 'Bank Transactions — ' . ($account->name ?? 'Account #' . $account->id);

    // Simple metrics for current page
    $pageSpent    = $rows->sum('spent');
    $pageReceived = $rows->sum('received');
    $pageNet      = $pageReceived - $pageSpent;
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#e5edff 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')

  {{-- HEADER / HERO --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">Bank Transactions</h1>
        <p class="text-sm text-slate-200">
          {{ $account->code }} — {{ $account->name }} (ID: {{ $account->id }})
        </p>
        <p class="text-[11px] text-slate-300 mt-1">
          Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }} records
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button type="button"
                onclick="document.getElementById('importCsvForm').classList.toggle('hidden')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/40 bg-slate-900/40 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
          Import CSV
        </button>
        <button type="button"
                onclick="document.getElementById('createTxForm').classList.toggle('hidden')"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">
          + Add Transaction
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
      {{ session('error') }}
    </div>
  @endif

  {{-- TOP METRICS (current page only) --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Transactions (page)</div>
      <div class="text-2xl font-bold text-slate-900">{{ number_format($rows->count()) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Spent (page)</div>
      <div class="text-2xl font-bold text-rose-600">
        ₱{{ number_format($pageSpent, 2) }}
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Received (page)</div>
      <div class="text-2xl font-bold text-emerald-600">
        ₱{{ number_format($pageReceived, 2) }}
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Net (page)</div>
      <div class="text-2xl font-bold {{ $pageNet >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
        ₱{{ number_format($pageNet, 2) }}
      </div>
    </div>
  </div>

  {{-- IMPORT CSV (collapsible) --}}
  <div id="importCsvForm" class="mb-4 bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 hidden">
    <form method="POST"
          action="{{ route('transactions.import', $account->id) }}"
          enctype="multipart/form-data"
          class="flex flex-wrap items-end gap-3">
      @csrf
      <div class="flex-1 min-w-[200px]">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">CSV File</label>
        <input type="file" name="file"
               class="block w-full text-xs text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white">
        <p class="text-[10px] text-slate-400 mt-1">
          Expected columns: <code>tx_date, description, contact_display, spent, received, ref_code</code>
        </p>
        @error('file')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <button class="px-3 py-2 rounded-lg bg-slate-900 text-white text-xs font-semibold">
          Upload & Import
        </button>
      </div>
    </form>
  </div>

  {{-- CREATE TRANSACTION (collapsible simple form) --}}
  <div id="createTxForm" class="mb-4 bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 hidden">
    <form method="POST"
          action="{{ route('transactions.store', $account->id) }}"
          class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
      @csrf

      <div class="md:col-span-2">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Date</label>
        <input type="date" name="tx_date" value="{{ old('tx_date', now()->toDateString()) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm" required>
      </div>

      <div class="md:col-span-2">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kind</label>
        <select name="kind" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
          <option value="bank" @selected(old('kind') === 'bank')>Bank</option>
          <option value="journal" @selected(old('kind') === 'journal')>Journal</option>
        </select>
      </div>

      {{-- Chart of Account --}}
      <div class="md:col-span-3">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Account (COA)</label>
        <select name="account_id" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm" required>
          <option value="">Select account…</option>
          @foreach($accounts as $coa)
            <option value="{{ $coa->id }}" @selected(old('account_id') == $coa->id)>
              {{ $coa->code }} — {{ $coa->name }}
            </option>
          @endforeach
        </select>
        @error('account_id')
          <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="md:col-span-3">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Description</label>
        <input type="text" name="description" value="{{ old('description') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
      </div>

      <div class="md:col-span-2">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Contact</label>
        <input type="text" name="contact_display" value="{{ old('contact_display') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
      </div>

      <div class="md:col-span-1">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Spent</label>
        <input type="number" step="0.01" name="spent" value="{{ old('spent') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
      </div>

      <div class="md:col-span-1">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Received</label>
        <input type="number" step="0.01" name="received" value="{{ old('received') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
      </div>

      <div class="md:col-span-1">
        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Ref</label>
        <input type="text" name="ref_code" value="{{ old('ref_code') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
      </div>

      <div class="md:col-span-12 flex gap-2 mt-1">
        <button class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-xs font-semibold">
          Save Transaction
        </button>
        <button type="button"
                onclick="document.getElementById('createTxForm').classList.add('hidden')"
                class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700">
          Cancel
        </button>
      </div>
    </form>
  </div>

  {{-- FILTERS --}}
  <form method="GET"
        class="mb-5 bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[180px]">
      <label class="block text-[11px] font-semibold text-slate-500 mb-1">Search</label>
      <input type="text" name="q" value="{{ request('q') }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm"
             placeholder="Contact / description / ref / account">
    </div>

    <div>
      <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kind</label>
      <select name="kind" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
        <option value="">All</option>
        <option value="bank" @selected(request('kind') === 'bank')>Bank</option>
        <option value="journal" @selected(request('kind') === 'journal')>Journal</option>
      </select>
    </div>

    <div>
      <label class="block text-[11px] font-semibold text-slate-500 mb-1">Reconcile</label>
      <select name="reconcile_status" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
        <option value="">All</option>
        <option value="pending" @selected(request('reconcile_status') === 'pending')>Pending</option>
        <option value="ok" @selected(request('reconcile_status') === 'ok')>OK</option>
        <option value="match" @selected(request('reconcile_status') === 'match')>Match</option>
      </select>
    </div>

    <div>
      <label class="block text-[11px] font-semibold text-slate-500 mb-1">Workflow Status</label>
      <select name="status" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
        <option value="">All</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        <option value="posted" @selected(request('status') === 'posted')>Posted</option>
        <option value="excluded" @selected(request('status') === 'excluded')>Excluded</option>
      </select>
    </div>

    <div class="flex items-center gap-2">
      <button class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm font-semibold">
        Apply
      </button>
      <a href="{{ route('transactions.index', $account->id) }}"
         class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm text-slate-700">
        Reset
      </a>
    </div>
  </form>

  {{-- TABLE --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-3 py-2 text-left">ID</th>
            <th class="px-3 py-2 text-left">Ref</th>
            <th class="px-3 py-2 text-left">Kind</th>
            <th class="px-3 py-2 text-left">Tx Date</th>
            <th class="px-3 py-2 text-left">Contact</th>
            <th class="px-3 py-2 text-left">Description</th>
            <th class="px-3 py-2 text-right">Spent</th>
            <th class="px-3 py-2 text-right">Received</th>
            <th class="px-3 py-2 text-left">Reconcile</th>
            <th class="px-3 py-2 text-left">Account (COA)</th>
            <th class="px-3 py-2 text-left">Remarks</th>
            <th class="px-3 py-2 text-left">Class</th>
            <th class="px-3 py-2 text-left">Source</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Posted At</th>
            <th class="px-3 py-2 text-left">Transfer?</th>
            <th class="px-3 py-2 text-left">Bank Text</th>
            <th class="px-3 py-2 text-left">Match ID</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $tx)
            @php
              $isPosted   = $tx->status === 'posted';
              $isExcluded = $tx->status === 'excluded';
              $isPending  = $tx->status === 'pending';
            @endphp
            <tr class="border-b border-slate-100 hover:bg-slate-50/60">
              <td class="px-3 py-2 text-slate-700">{{ $tx->id }}</td>
              <td class="px-3 py-2 font-mono text-[11px] text-slate-700">{{ $tx->ref_code }}</td>
              <td class="px-3 py-2 text-slate-700">{{ $tx->kind }}</td>
              <td class="px-3 py-2 text-slate-700">
                {{ \Carbon\Carbon::parse($tx->tx_date)->format('Y-m-d') }}
              </td>
              <td class="px-3 py-2 text-slate-800">{{ $tx->contact_display }}</td>
              <td class="px-3 py-2 text-slate-800">{{ $tx->description }}</td>
              <td class="px-3 py-2 text-right text-rose-600">
                {{ $tx->spent ? '₱' . number_format($tx->spent, 2) : '—' }}
              </td>
              <td class="px-3 py-2 text-right text-emerald-600">
                {{ $tx->received ? '₱' . number_format($tx->received, 2) : '—' }}
              </td>
              <td class="px-3 py-2 text-slate-700">{{ $tx->reconcile_status }}</td>

              {{-- COA column --}}
              <td class="px-3 py-2 text-slate-700">
                @if($tx->account)
                  {{ $tx->account->code }} — {{ $tx->account->name }}
                @else
                  {{ $tx->account_name ?? '—' }}
                @endif
              </td>

              <td class="px-3 py-2 text-slate-700">{{ $tx->remarks }}</td>
              <td class="px-3 py-2 text-slate-700">{{ $tx->tx_class }}</td>
              <td class="px-3 py-2 text-slate-700">{{ $tx->source }}</td>
              <td class="px-3 py-2 text-slate-700">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                  @if($isPosted)
                    bg-emerald-50 text-emerald-700
                  @elseif($isExcluded)
                    bg-slate-100 text-slate-600
                  @else
                    bg-cyan-50 text-cyan-700
                  @endif
                ">
                  {{ ucfirst($tx->status) }}
                </span>
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->posted_at ? \Carbon\Carbon::parse($tx->posted_at)->format('Y-m-d H:i') : '—' }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->is_transfer ? 'Yes' : 'No' }}
              </td>
              <td class="px-3 py-2 text-slate-700">{{ $tx->bank_text }}</td>
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->match_id ?? '—' }}
              </td>
              <td class="px-3 py-2 text-right">
                <div class="inline-flex gap-1">
                  {{-- Mark Posted --}}
                  @if(! $isPosted)
                    <form method="POST"
                          action="{{ route('transactions.post', [$account->id, $tx->id]) }}"
                          onsubmit="return confirm('Mark this transaction as POSTED?');">
                      @csrf
                      @method('PATCH')
                      <button class="px-2 py-1 rounded-lg border border-emerald-200 text-[11px] text-emerald-700 hover:bg-emerald-50">
                        Post
                      </button>
                    </form>
                  @endif

                  {{-- Exclude --}}
                  @if(! $isExcluded)
                    <form method="POST"
                          action="{{ route('transactions.exclude', [$account->id, $tx->id]) }}"
                          onsubmit="return confirm('Exclude this transaction from books?');">
                      @csrf
                      @method('PATCH')
                      <button class="px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                        Exclude
                      </button>
                    </form>
                  @endif

                  {{-- Restore to Pending --}}
                  @if(! $isPending)
                    <form method="POST"
                          action="{{ route('transactions.restore', [$account->id, $tx->id]) }}"
                          onsubmit="return confirm('Restore this transaction to PENDING?');">
                      @csrf
                      @method('PATCH')
                      <button class="px-2 py-1 rounded-lg border border-cyan-200 text-[11px] text-cyan-700 hover:bg-cyan-50">
                        Pending
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="19" class="px-3 py-6 text-center text-slate-400 text-xs italic">
                No transactions found for this bank account.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="flex justify-between items-center px-4 py-3 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-600">
      <div>
        Showing
        <span class="font-semibold">{{ $rows->firstItem() }}</span>–
        <span class="font-semibold">{{ $rows->lastItem() }}</span>
        of <span class="font-semibold">{{ $rows->total() }}</span>
      </div>
      <div class="text-xs">
        {{ $rows->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

@endsection