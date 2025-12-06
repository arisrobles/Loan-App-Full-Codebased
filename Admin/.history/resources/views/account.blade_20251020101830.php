@extends('layouts.app')

@php
  $pageTitle = 'Chart of Accounts';
@endphp

@section('head')
<style>
  html,body {font-family:'Inter',sans-serif;background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);color:#0f172a;}

  /* HEADER */
  .mx-header {
    background:linear-gradient(135deg,rgba(99,102,241,.9),rgba(168,85,247,.85));
    backdrop-filter:blur(14px);
    color:white;
    border-bottom:1px solid rgba(255,255,255,.2);
    box-shadow:0 10px 25px -10px rgba(99,102,241,.4);
  }

  /* BUTTONS */
  .btn {
    display:inline-flex;align-items:center;justify-content:center;
    font-weight:600;border-radius:.75rem;transition:.2s;
    padding:.6rem 1.1rem;font-size:.875rem;
  }
  .btn-brand {background:linear-gradient(90deg,#6366f1,#a855f7);color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);}
  .btn-brand:hover {opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline {border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover {background:#f8fafc;}

  /* FORM CARD */
  .form-card {
    background:white;border-radius:1rem;border:1px solid #eef2ff;
    box-shadow:0 10px 28px -16px rgba(99,102,241,.15);
    padding:1.5rem;
  }
  input,select {
    border-radius:.75rem;background:#f8fafc;border:1px solid #e2e8f0;
    height:2.5rem;width:100%;padding:.4rem .75rem;
    font-size:.875rem;transition:border .2s, box-shadow .2s;
  }
  input:focus,select:focus {
    border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.2);outline:none;
  }

  /* TABLE */
  .table-container {overflow:auto;border-radius:1rem;}
  table {width:100%;border-collapse:collapse;min-width:950px;}
  thead {
    background:linear-gradient(to right,#f8fafc,#eef2ff);
    text-transform:uppercase;font-size:.7rem;color:#475569;
    position:sticky;top:0;z-index:10;
  }
  th,td {padding:.9rem 1rem;text-align:left;}
  thead th {font-weight:700;border-bottom:1px solid #e2e8f0;}
  tbody tr:nth-child(even) {background:#fdfdfd;}
  tbody tr:hover td {background:#f4f6ff;transition:.25s;}
  tbody td {border-bottom:1px solid #f1f5f9;}
  tbody td.font-mono {font-family:ui-monospace,monospace;}

  /* BADGES */
  .badge {
    display:inline-flex;align-items:center;padding:0.25rem .7rem;
    border-radius:9999px;font-size:.75rem;font-weight:600;
  }
  .badge.active {background:#dcfce7;color:#166534;}
  .badge.inactive {background:#f1f5f9;color:#475569;}

  /* ALERTS */
  .alert {
    border-radius:.75rem;padding:.75rem 1rem;margin-bottom:1rem;
    font-size:.875rem;font-weight:500;
  }
  .alert-success {background:#dcfce7;border:1px solid #86efac;color:#166534;}
  .alert-warning {background:#fef9c3;border:1px solid #fde68a;color:#92400e;}
  .alert-error {background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;}
</style>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mx-header rounded-2xl mb-8 shadow-md">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold">Chart of Accounts</h2>
      <p class="text-sm text-indigo-100">Financial structure overview and management</p>
    </div>
    <nav class="flex gap-3 flex-wrap">
      <a href="{{ route('coa.export') }}" class="btn btn-outline">Export CSV</a>
      <form action="{{ route('coa.import') }}" method="post" enctype="multipart/form-data" class="flex items-center gap-2 bg-white rounded-lg border border-gray-200 px-3 py-2">
        @csrf
        <input type="file" name="file" accept=".csv,text/csv" required class="text-xs">
        <label class="flex items-center gap-1 text-xs text-gray-600">
          <input type="checkbox" name="dry_run" value="1"> Dry run
        </label>
        <button class="btn btn-brand text-xs py-1.5">Import</button>
      </form>
    </nav>
  </div>
</div>

{{-- ALERTS --}}
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('import_errors') && count(session('import_errors')))
  <div class="alert alert-warning">
    <div class="font-medium mb-1">Import row issues</div>
    <ul class="list-disc ml-5">
      @foreach(session('import_errors') as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif
@if($errors->any())
  <div class="alert alert-error">
    <ul class="list-disc ml-5">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- ADD FORM --}}
<div class="form-card mb-8">
  <h2 class="text-lg font-medium mb-3">Add New Account</h2>
  <form action="{{ route('coa.store') }}" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    @csrf
    <input name="code" placeholder="Code" value="{{ old('code') }}" required>
    <input name="name" placeholder="Name" value="{{ old('name') }}" required>

    <select name="report" required>
      <option value="">Report</option>
      <option @selected(old('report')==='Balance Sheets')>Balance Sheets</option>
      <option @selected(old('report')==='Profit and Losses')>Profit and Losses</option>
    </select>

    <select name="group_account" required>
      <option value="">Group</option>
      @foreach(['Assets','Liabilities','Equity','Revenue (Income)','Expense (COGS)','Expenses'] as $opt)
        <option value="{{ $opt }}" @selected(old('group_account')===$opt)>{{ $opt }}</option>
      @endforeach
    </select>

    <select name="normal_balance">
      <option value="">Normal (auto)</option>
      <option @selected(old('normal_balance')==='Debit')>Debit</option>
      <option @selected(old('normal_balance')==='Credit')>Credit</option>
    </select>

    <input name="description" placeholder="Description (optional)" value="{{ old('description') }}" class="md:col-span-3">
    <div class="md:col-span-3 flex items-center gap-2">
      <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
      <label for="is_active" class="text-sm">Active</label>
    </div>
    <button class="md:col-span-3 btn btn-brand">Add Account</button>
  </form>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 overflow-hidden">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Report</th>
          <th>Group</th>
          <th class="text-center">Normal</th>
          <th class="text-center">Debit ↑</th>
          <th class="text-center">Credit ↑</th>
          <th class="text-center">Bank Balance</th>
          <th class="text-center">MasterFunds Balance</th>
          <th class="text-center">Active</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($accounts as $acc)
          @php
            $match = $bankBalances->firstWhere('code', $acc->code);
            if (!$match && str_contains(strtolower($acc->name), 'bank')) $match = $bankBalances->first();
          @endphp
          <tr class="{{ $acc->is_active ? '' : 'bg-gray-50 text-gray-400' }} hover:bg-indigo-50/40 transition">
            <td class="font-mono">{{ $acc->code }}</td>
            <td class="font-semibold">{{ $acc->name }}</td>
            <td>{{ $acc->report }}</td>
            <td>{{ $acc->group_account }}</td>
            <td class="text-center">{{ $acc->normal_balance }}</td>
            <td class="text-center">{{ $acc->debit_effect }}</td>
            <td class="text-center">{{ $acc->credit_effect }}</td>
            <td class="text-right font-mono text-green-700">{{ $match ? number_format($match->bank_balance ?? 0, 2) : '-' }}</td>
            <td class="text-right font-mono text-blue-700">{{ $match ? number_format($match->masterfunds_balance ?? 0, 2) : '-' }}</td>
            <td class="text-center">
              @if($acc->is_active)
                <span class="badge active">Active</span>
              @else
                <span class="badge inactive">Inactive</span>
              @endif
            </td>
            <td class="text-center space-x-2">
              @if($acc->is_active)
                <form action="{{ route('coa.archive', $acc->id) }}" method="post" class="inline">@csrf
                  <button class="text-xs text-yellow-700 hover:underline">Archive</button>
                </form>
              @else
                <form action="{{ route('coa.activate', $acc->id) }}" method="post" class="inline">@csrf
                  <button class="text-xs text-emerald-700 hover:underline">Activate</button>
                </form>
              @endif
              <form action="{{ route('coa.destroy', $acc->id) }}" method="post" class="inline"
                    onsubmit="return confirm('Delete permanently? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="text-xs text-red-700 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
