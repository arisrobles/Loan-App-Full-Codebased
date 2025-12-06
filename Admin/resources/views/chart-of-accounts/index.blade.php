@extends('layouts.app')

@php
  use App\Models\ChartOfAccount;
  $pageTitle = 'Chart of Accounts';
@endphp

@section('content')
<div class="mb-5 flex items-center justify-between">
  <div>
    <h1 class="text-xl font-semibold text-slate-800">Chart of Accounts</h1>
    <p class="text-sm text-slate-500">Manage GL codes used for loans, bank transactions, and reports.</p>
  </div>
  <a href="{{ route('chart-of-accounts.create') }}"
     class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
    + Add Account
  </a>
</div>

@if (session('success'))
  <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
    {{ session('success') }}
  </div>
@endif

{{-- FILTERS --}}
<form method="GET" action="{{ route('chart-of-accounts.index') }}"
      class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end bg-white rounded-xl border border-slate-200 px-4 py-3">
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
           placeholder="Code or Name"
           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
  </div>
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Report</label>
    <select name="report" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      <option value="">All</option>
      @foreach($reports as $report)
        <option value="{{ $report }}" @selected(($filters['report'] ?? '') === $report)>
          {{ $report }}
        </option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Group</label>
    <select name="group_account" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      <option value="">All</option>
      @foreach($groups as $group)
        <option value="{{ $group }}" @selected(($filters['group_account'] ?? '') === $group)>
          {{ $group }}
        </option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">Active?</label>
    <select name="is_active" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      <option value="">All</option>
      <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
      <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
    </select>
  </div>
  <div class="md:col-span-4 flex gap-2">
    <button class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
      Apply Filters
    </button>
    <a href="{{ route('chart-of-accounts.index') }}"
       class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white hover:bg-slate-50">
      Reset
    </a>
  </div>
</form>

{{-- TABLE --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
        <tr>
          <th class="px-3 py-2 text-left">Code</th>
          <th class="px-3 py-2 text-left">Name</th>
          <th class="px-3 py-2 text-left">Group</th>
          <th class="px-3 py-2 text-left">Report</th>
          <th class="px-3 py-2 text-left">Normal</th>
          <th class="px-3 py-2 text-left">Active</th>
          <th class="px-3 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $row)
          <tr class="border-b border-slate-100 hover:bg-slate-50/70">
            <td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $row->code }}</td>
            <td class="px-3 py-2 text-slate-800">{{ $row->name }}</td>
            <td class="px-3 py-2 text-slate-600">{{ $row->group_account }}</td>
            <td class="px-3 py-2 text-slate-600">{{ $row->report }}</td>
            <td class="px-3 py-2 text-slate-600">{{ $row->normal_balance ?? '—' }}</td>
            <td class="px-3 py-2">
              @if($row->is_active)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                  Active
                </span>
              @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                  Inactive
                </span>
              @endif
            </td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('chart-of-accounts.edit', $row) }}"
                 class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-slate-700 border border-slate-200 hover:bg-slate-50">
                Edit
              </a>
              <form action="{{ route('chart-of-accounts.destroy', $row) }}"
                    method="POST"
                    class="inline"
                    onsubmit="return confirm('Delete this account? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-red-600 border border-red-200 hover:bg-red-50">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-slate-400 italic">
              No accounts found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 text-xs text-slate-500">
    <div>
      Showing
      <span class="font-semibold text-slate-700">{{ $rows->firstItem() }}</span>–
      <span class="font-semibold text-slate-700">{{ $rows->lastItem() }}</span>
      of
      <span class="font-semibold text-slate-700">{{ $rows->total() }}</span>
    </div>
    <div class="text-sm">
      {{ $rows->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection