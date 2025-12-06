{{-- resources/views/transactions/edit.blade.php --}}
@extends('layouts.app')

@php
  /** @var \App\Models\BankAccount $account */
  /** @var \App\Models\BankTransaction $transaction */
  $pageTitle = 'Edit Bank Transaction';
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
        <h1 class="text-xl font-semibold">Edit Transaction #{{ $transaction->id }}</h1>
        <p class="text-sm text-slate-200">
          Update details and reconciliation tags.
        </p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('transactions.show', [$account->id, $transaction->id]) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-xs font-semibold">
          View Details
        </a>
        <a href="{{ route('transactions.index', $account->id) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-xs font-semibold">
          Back to List
        </a>
      </div>
    </div>
  </div>

  {{-- Errors --}}
  @if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
      <strong class="font-semibold">Please fix the following:</strong>
      <ul class="mt-1 list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <form action="{{ route('transactions.update', [$account->id, $transaction->id]) }}"
          method="POST"
          class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Reference Code</label>
        <input type="text" name="ref_code"
               value="{{ old('ref_code', $transaction->ref_code) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Kind</label>
        <select name="kind" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
          <option value="bank"    @selected(old('kind', $transaction->kind)==='bank')>Bank</option>
          <option value="journal" @selected(old('kind', $transaction->kind)==='journal')>Journal</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Transaction Date</label>
        <input type="date" name="tx_date"
               value="{{ old('tx_date', optional($transaction->tx_date)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact / Counterparty</label>
        <input type="text" name="contact_display"
               value="{{ old('contact_display', $transaction->contact_display) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
        <input type="text" name="description"
               value="{{ old('description', $transaction->description) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Spent</label>
        <input type="number" step="0.01" min="0" name="spent"
               value="{{ old('spent', $transaction->spent) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Received</label>
        <input type="number" step="0.01" min="0" name="received"
               value="{{ old('received', $transaction->received) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Reconcile Status</label>
        <select name="reconcile_status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
          <option value="pending" @selected(old('reconcile_status', $transaction->reconcile_status)==='pending')>Pending</option>
          <option value="ok"      @selected(old('reconcile_status', $transaction->reconcile_status)==='ok')>OK</option>
          <option value="match"   @selected(old('reconcile_status', $transaction->reconcile_status)==='match')>Matched</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Workflow Status</label>
        <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
          <option value="pending"  @selected(old('status', $transaction->status)==='pending')>Pending</option>
          <option value="posted"   @selected(old('status', $transaction->status)==='posted')>Posted</option>
          <option value="excluded" @selected(old('status', $transaction->status)==='excluded')>Excluded</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Ledger Contact</label>
        <input type="text" name="ledger_contact"
               value="{{ old('ledger_contact', $transaction->ledger_contact) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Account Name</label>
        <input type="text" name="account_name"
               value="{{ old('account_name', $transaction->account_name) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Transaction Class</label>
        <input type="text" name="tx_class"
               value="{{ old('tx_class', $transaction->tx_class) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Source</label>
        <input type="text" name="source"
               value="{{ old('source', $transaction->source) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks</label>
        <input type="text" name="remarks"
               value="{{ old('remarks', $transaction->remarks) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div>
        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 mb-1">
          <input type="checkbox" name="is_transfer" value="1" class="rounded border-slate-300"
                 @checked(old('is_transfer', $transaction->is_transfer))>
          <span>Is Inter-Account Transfer</span>
        </label>
      </div>

      <div class="md:col-span-2 flex justify-between items-center pt-3">
        <div class="text-[11px] text-slate-500">
          Created: {{ $transaction->created_at }} · Updated: {{ $transaction->updated_at }}
        </div>
        <div class="flex gap-2">
          <a href="{{ route('transactions.index', $account->id) }}"
             class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
            Cancel
          </a>
          <button type="submit"
             class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
            Save Changes
          </button>
        </div>
      </div>
    </form>
  </div>
@endsection