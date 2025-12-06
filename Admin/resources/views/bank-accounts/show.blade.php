{{-- resources/views/bank-accounts/show.blade.php --}}
@extends('layouts.app')

@php
  /** @var \App\Models\BankAccount $account */
  $pageTitle = 'Bank Account: '.$account->code;

  $bankBalance      = $balance->bank_balance        ?? null;
  $masterfunds      = $balance->masterfunds_balance ?? null;
  $asOfDate         = $balance->as_of_date          ?? null;
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')

  @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- HEADER --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">{{ $account->code }}</h1>
        <p class="text-sm text-indigo-100">
          {{ $account->name }} &middot; {{ $account->timezone ?? 'Asia/Manila' }}
        </p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('bank-accounts.edit', $account) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-xs font-semibold">
          Edit
        </a>
        <a href="{{ route('bank-accounts.index') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold">
          Back to list
        </a>
      </div>
    </div>
  </div>

  {{-- METRICS --}}
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Code</div>
      <div class="text-base font-bold text-slate-800">{{ $account->code }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm md:col-span-2">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Name</div>
      <div class="text-base font-bold text-slate-800">{{ $account->name }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Timezone</div>
      <div class="text-base font-bold text-slate-800">{{ $account->timezone ?? 'Asia/Manila' }}</div>
    </div>
  </div>

  {{-- BALANCES + QUICK LINK --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 lg:col-span-2">
      <h2 class="text-sm font-semibold text-slate-800 mb-2">Bank vs Book Balances</h2>
      @if($balance)
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
            <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Bank Feed Balance</div>
            <div class="text-lg font-bold text-slate-900">
              ₱{{ number_format((float)$bankBalance, 2) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
              As of {{ $asOfDate }}
            </div>
          </div>
          <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
            <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Book Balance (MasterFunds)</div>
            <div class="text-lg font-bold text-slate-900">
              ₱{{ number_format((float)$masterfunds, 2) }}
            </div>
          </div>
        </div>
      @else
        <p class="text-xs text-slate-500">
          No balance snapshot found (v_bank_balances view may be empty or not configured).
        </p>
      @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
      <h2 class="text-sm font-semibold text-slate-800 mb-2">Quick Actions</h2>
      <div class="space-y-2 text-xs">
        <a href="{{ route('transactions.index', $account->id) }}"
           class="block px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 font-semibold hover:bg-indigo-100">
          View Bank Transactions
        </a>
        <a href="{{ route('bank-accounts.edit', $account) }}"
           class="block px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">
          Edit Account Details
        </a>
      </div>
    </div>
  </div>

  {{-- LAST 10 TRANSACTIONS --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-slate-800">Recent Transactions</h2>
      <a href="{{ route('transactions.index', $account->id) }}"
         class="text-[11px] text-indigo-600 hover:text-indigo-700 font-semibold">
        View all &rarr;
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-3 py-2 text-left">Date</th>
            <th class="px-3 py-2 text-left">Ref</th>
            <th class="px-3 py-2 text-left">Contact</th>
            <th class="px-3 py-2 text-left">Description</th>
            <th class="px-3 py-2 text-right">Spent</th>
            <th class="px-3 py-2 text-right">Received</th>
            <th class="px-3 py-2 text-left">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transactions as $tx)
            <tr class="border-b border-slate-100 hover:bg-slate-50/60">
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->tx_date }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->ref_code ?? '—' }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $tx->contact_display ?? '—' }}
              </td>
              <td class="px-3 py-2 text-slate-600">
                {{ $tx->description ?? '—' }}
              </td>
              <td class="px-3 py-2 text-right text-rose-600">
                @if($tx->spent)
                  ₱{{ number_format($tx->spent, 2) }}
                @else
                  —
                @endif
              </td>
              <td class="px-3 py-2 text-right text-emerald-600">
                @if($tx->received)
                  ₱{{ number_format($tx->received, 2) }}
                @else
                  —
                @endif
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ ucfirst($tx->status ?? 'pending') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-6 text-center text-slate-400 text-[11px] italic">
                No transactions yet for this bank account.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection