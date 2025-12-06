{{-- resources/views/transactions/show.blade.php --}}
@extends('layouts.app')

@php
  /** @var \App\Models\BankAccount $account */
  /** @var \App\Models\BankTransaction $transaction */
  $pageTitle = 'Bank Transaction Details';
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#e0f2fe 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')
  {{-- Header --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-sky-800 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <p class="text-[11px] uppercase tracking-[.18em] text-sky-300/80 mb-1">
          Bank Transactions · {{ $account->code }} — {{ $account->name }}
        </p>
        <h1 class="text-xl font-semibold">
          Transaction #{{ $transaction->id }}
        </h1>
        <p class="text-sm text-slate-200">
          {{ $transaction->description ?: 'No description provided.' }}
        </p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('transactions.edit', [$account->id, $transaction->id]) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-100 text-slate-900 text-xs font-semibold hover:bg-white">
          Edit
        </a>
        <a href="{{ route('transactions.index', $account->id) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-xs font-semibold">
          Back to List
        </a>
      </div>
    </div>
  </div>

  {{-- Detail Card --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Left: Key Info --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
      <h2 class="text-sm font-semibold text-slate-800 mb-3">Transaction Summary</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Date</div>
          <div class="font-semibold text-slate-800">
            {{ optional($transaction->tx_date)->format('Y-m-d') ?? '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Kind</div>
          <div class="font-semibold text-slate-800">
            {{ ucfirst($transaction->kind) }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Reference Code</div>
          <div class="font-mono text-slate-800">
            {{ $transaction->ref_code ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Contact / Counterparty</div>
          <div class="text-slate-800">
            {{ $transaction->contact_display ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Spent (Out)</div>
          <div class="font-semibold text-rose-600">
            ₱{{ number_format($transaction->spent ?? 0, 2) }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Received (In)</div>
          <div class="font-semibold text-emerald-600">
            ₱{{ number_format($transaction->received ?? 0, 2) }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Reconcile Status</div>
          <div class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
            @class([
              'bg-slate-100 text-slate-700'   => $transaction->reconcile_status === 'pending',
              'bg-emerald-100 text-emerald-700'=> $transaction->reconcile_status === 'ok',
              'bg-sky-100 text-sky-700'        => $transaction->reconcile_status === 'match',
              ])"
          >
            {{ ucfirst($transaction->reconcile_status ?? 'pending') }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Workflow Status</div>
          <div class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
            @class([
              'bg-slate-100 text-slate-700'   => $transaction->status === 'pending',
              'bg-emerald-100 text-emerald-700'=> $transaction->status === 'posted',
              'bg-rose-100 text-rose-700'      => $transaction->status === 'excluded',
              ])"
          >
            {{ ucfirst($transaction->status ?? 'pending') }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Ledger Contact</div>
          <div class="text-slate-800">
            {{ $transaction->ledger_contact ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Account Name</div>
          <div class="text-slate-800">
            {{ $transaction->account_name ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Transaction Class</div>
          <div class="text-slate-800">
            {{ $transaction->tx_class ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Source</div>
          <div class="text-slate-800">
            {{ $transaction->source ?: '—' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Is Transfer</div>
          <div class="text-slate-800">
            {{ $transaction->is_transfer ? 'Yes' : 'No' }}
          </div>
        </div>

        <div>
          <div class="text-[11px] text-slate-500 uppercase mb-1">Matched ID</div>
          <div class="text-slate-800">
            {{ $transaction->match_id ?: '—' }}
          </div>
        </div>

        <div class="md:col-span-2">
          <div class="text-[11px] text-slate-500 uppercase mb-1">Remarks</div>
          <div class="text-slate-800">
            {{ $transaction->remarks ?: '—' }}
          </div>
        </div>
      </div>
    </div>

    {{-- Right: Meta --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-sm">
      <h2 class="text-sm font-semibold text-slate-800 mb-3">Audit Trail</h2>
      <dl class="space-y-2 text-[13px] text-slate-700">
        <div>
          <dt class="text-slate-500 text-[11px] uppercase mb-0.5">Created At</dt>
          <dd class="font-mono">{{ $transaction->created_at }}</dd>
        </div>
        <div>
          <dt class="text-slate-500 text-[11px] uppercase mb-0.5">Updated At</dt>
          <dd class="font-mono">{{ $transaction->updated_at }}</dd>
        </div>
        <div>
          <dt class="text-slate-500 text-[11px] uppercase mb-0.5">Posted At</dt>
          <dd class="font-mono">{{ $transaction->posted_at ?? '—' }}</dd>
        </div>
      </dl>

      <div class="mt-4 border-t border-slate-100 pt-3 flex flex-col gap-2">
        @if($transaction->status !== 'posted')
          <form action="{{ route('transactions.post', [$account->id, $transaction->id]) }}"
                method="POST">
            @csrf
            @method('PATCH')
            <button class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
              Mark as Posted
            </button>
          </form>
        @endif

        @if($transaction->status !== 'excluded')
          <form action="{{ route('transactions.exclude', [$account->id, $transaction->id]) }}"
                method="POST">
            @csrf
            @method('PATCH')
            <button class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold hover:bg-rose-100">
              Exclude from Books
            </button>
          </form>
        @else
          <form action="{{ route('transactions.restore', [$account->id, $transaction->id]) }}"
                method="POST">
            @csrf
            @method('PATCH')
            <button class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-cyan-50 text-cyan-700 text-xs font-semibold hover:bg-cyan-100">
              Restore to Pending
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
@endsection