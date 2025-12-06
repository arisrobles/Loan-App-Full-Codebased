@extends('layouts.app')

@php
  $pageTitle = 'Chart of Accounts';
@endphp

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Chart of Accounts</h1>

    <nav class="flex gap-3">
      <a href="{{ route('coa.export') }}"
         class="px-3 py-2 bg-gray-800 text-white rounded text-sm hover:bg-gray-700">
        Export CSV
      </a>

      <form action="{{ route('coa.import') }}" method="post" enctype="multipart/form-data" class="flex items-center gap-2">
        @csrf
        <input type="file" name="file" accept=".csv,text/csv" required
               class="border text-sm bg-white px-2 py-1 rounded">
        <label class="flex items-center gap-1 text-sm text-gray-600">
          <input type="checkbox" name="dry_run" value="1"> Dry run
        </label>
        <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
          Import
        </button>
      </form>
    </nav>
  </div>

  {{-- Flash + errors --}}
  @if(session('success'))
    <div class="mb-3 p-3 bg-green-50 border border-green-300 text-green-700 rounded">
      {{ session('success') }}
    </div>
  @endif
  @if(session('import_errors') && count(session('import_errors')))
    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded">
      <div class="font-medium mb-1">Import row issues</div>
      <ul class="list-disc ml-5">
        @foreach(session('import_errors') as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if($errors->any())
    <div class="mb-3 p-3 bg-red-50 border border-red-300 text-red-700 rounded">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Add form --}}
  <div class="mb-6 bg-white border rounded shadow-sm p-4">
    <h2 class="text-lg font-medium mb-3">Add New Account</h2>
    <form action="{{ route('coa.store') }}" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3">
      @csrf
      <input name="code" placeholder="Code" value="{{ old('code') }}" required class="border px-2 py-1 rounded">
      <input name="name" placeholder="Name" value="{{ old('name') }}" required class="border px-2 py-1 rounded">

      <select name="report" class="border px-2 py-1 rounded" required>
        <option value="">Report</option>
        <option @selected(old('report')==='Balance Sheets')>Balance Sheets</option>
        <option @selected(old('report')==='Profit and Losses')>Profit and Losses</option>
      </select>

      <select name="group_account" class="border px-2 py-1 rounded" required>
        <option value="">Group</option>
        @foreach(['Assets','Liabilities','Equity','Revenue (Income)','Expense (COGS)','Expenses'] as $opt)
          <option value="{{ $opt }}" @selected(old('group_account')===$opt)>{{ $opt }}</option>
        @endforeach
      </select>

      <select name="normal_balance" class="border px-2 py-1 rounded">
        <option value="">Normal (auto)</option>
        <option @selected(old('normal_balance')==='Debit')>Debit</option>
        <option @selected(old('normal_balance')==='Credit')>Credit</option>
      </select>

      <input name="description" placeholder="Description (optional)" value="{{ old('description') }}"
             class="border px-2 py-1 md:col-span-3 rounded">

      <div class="md:col-span-3 flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
        <label for="is_active" class="text-sm">Active</label>
      </div>

      <button class="md:col-span-3 px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Account</button>
    </form>
  </div>

  {{-- Table --}}
  <div class="overflow-x-auto bg-white border rounded shadow-sm">
    <table class="min-w-full border-collapse text-sm">
      <thead class="bg-gray-100">
        <tr class="text-left">
          <th class="px-3 py-2 border">Code</th>
          <th class="px-3 py-2 border">Name</th>
          <th class="px-3 py-2 border">Report</th>
          <th class="px-3 py-2 border">Group</th>
          <th class="px-3 py-2 border text-center">Normal</th>
          <th class="px-3 py-2 border text-center">Debit ↑</th>
          <th class="px-3 py-2 border text-center">Credit ↑</th>
          <th class="px-3 py-2 border text-center">Bank Balance</th>
          <th class="px-3 py-2 border text-center">MasterFunds Balance</th>
          <th class="px-3 py-2 border text-center">Active</th>
          <th class="px-3 py-2 border text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($accounts as $acc)
          @php
            // Try to pair with a balance row (adapt to your join logic)
            $match = $bankBalances->firstWhere('code', $acc->code);
            if (!$match && str_contains(strtolower($acc->name), 'bank')) {
              $match = $bankBalances->first();
            }
          @endphp
          <tr class="{{ $acc->is_active ? '' : 'bg-gray-50 text-gray-400' }}">
            <td class="px-3 py-2 border font-mono">{{ $acc->code }}</td>
            <td class="px-3 py-2 border">{{ $acc->name }}</td>
            <td class="px-3 py-2 border">{{ $acc->report }}</td>
            <td class="px-3 py-2 border">{{ $acc->group_account }}</td>
            <td class="px-3 py-2 border text-center">{{ $acc->normal_balance }}</td>
            <td class="px-3 py-2 border text-center">{{ $acc->debit_effect }}</td>
            <td class="px-3 py-2 border text-center">{{ $acc->credit_effect }}</td>

            <td class="px-3 py-2 border text-right font-mono text-green-700">
              {{ $match ? number_format($match->bank_balance ?? 0, 2) : '-' }}
            </td>
            <td class="px-3 py-2 border text-right font-mono text-blue-700">
              {{ $match ? number_format($match->masterfunds_balance ?? 0, 2) : '-' }}
            </td>

            <td class="px-3 py-2 border text-center">
              @if($acc->is_active)
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>
              @else
                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">Inactive</span>
              @endif
            </td>

            <td class="px-3 py-2 border text-center space-x-2">
              @if($acc->is_active)
                <form action="{{ route('coa.archive', $acc->id) }}" method="post" class="inline">
                  @csrf
                  <button class="text-xs text-yellow-700 hover:underline">Archive</button>
                </form>
              @else
                <form action="{{ route('coa.activate', $acc->id) }}" method="post" class="inline">
                  @csrf
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
@endsection
