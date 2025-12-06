{{-- resources/views/bank-accounts/index.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = 'Bank Accounts';

  $totalAccounts = $meta['total'] ?? 0;
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

  {{-- FLASH --}}
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

  {{-- HEADER (dark blue / violet) --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">Bank Accounts</h1>
        <p class="text-sm text-indigo-100">
          Masterfund bank accounts and their linked transactions
        </p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('bank-accounts.create') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-xs font-semibold shadow">
          + Add Bank Account
        </a>
      </div>
    </div>
  </div>

  {{-- METRICS --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Total Accounts</div>
      <div class="text-xl font-bold text-slate-800">{{ number_format($totalAccounts) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Page</div>
      <div class="text-xl font-bold text-slate-800">
        {{ $meta['page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Per Page</div>
      <div class="text-xl font-bold text-slate-800">
        {{ $meta['per_page'] ?? 10 }}
      </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Active Filters</div>
      <div class="text-xs text-slate-600">
        {{ collect($meta['query'] ?? [])->map(fn($v,$k)=>$k.':'.(is_array($v)?implode(',',$v):$v))->join(', ') ?: 'None' }}
      </div>
    </div>
  </div>

  {{-- FILTERS --}}
  <div class="mb-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <form action="{{ route('bank-accounts.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-6">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Code / Name"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Per Page</label>
        <input type="number"
               name="per_page"
               value="{{ request('per_page', 10) }}"
               min="5" max="100"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>
      <div class="md:col-span-3 flex gap-2">
        <button class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Apply
        </button>
        <a href="{{ route('bank-accounts.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Reset
        </a>
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-3 py-2 text-left">#</th>
            <th class="px-3 py-2 text-left">Code</th>
            <th class="px-3 py-2 text-left">Name</th>
            <th class="px-3 py-2 text-left">Timezone</th>
            <th class="px-3 py-2 text-right">Transactions</th>
            <th class="px-3 py-2 text-right">Linked Loans</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($accounts as $i => $account)
            <tr class="border-b border-slate-100 hover:bg-slate-50/60">
              <td class="px-3 py-2 text-slate-700">
                {{ $accounts->firstItem() + $i }}
              </td>
              <td class="px-3 py-2 font-semibold text-slate-900">
                <a href="{{ route('bank-accounts.show', $account) }}" class="hover:underline">
                  {{ $account->code }}
                </a>
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $account->name }}
              </td>
              <td class="px-3 py-2 text-slate-600">
                {{ $account->timezone ?? 'Asia/Manila' }}
              </td>
              <td class="px-3 py-2 text-right text-slate-800">
                {{ number_format($account->transactions_count ?? 0) }}
              </td>
              <td class="px-3 py-2 text-right text-slate-800">
                {{ number_format($account->loans_count ?? 0) }}
              </td>
              <td class="px-3 py-2 text-right space-x-1">
                <a href="{{ route('bank-accounts.show', $account) }}"
                   class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                  View
                </a>
                <a href="{{ route('bank-accounts.edit', $account) }}"
                   class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                  Edit
                </a>
                @if($account->transactions_count == 0 && $account->loans_count == 0)
                  <form action="{{ route('bank-accounts.destroy', $account) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex items-center px-2 py-1 rounded-lg bg-rose-50 text-[11px] text-rose-700 hover:bg-rose-100"
                            onclick="return confirm('Delete this bank account?')">
                      Delete
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-6 text-center text-slate-400 text-[11px] italic">
                No bank accounts found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-5 py-4 bg-slate-50 border-t border-slate-100 text-xs text-slate-600 gap-2">
      <div>
        @if ($accounts->total())
          Showing
          <span class="font-semibold">{{ $accounts->firstItem() }}</span>–
          <span class="font-semibold">{{ $accounts->lastItem() }}</span>
          of
          <span class="font-semibold">{{ $accounts->total() }}</span>
        @else
          No results
        @endif
      </div>
      <div class="text-right">
        {{ $accounts->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

@endsection