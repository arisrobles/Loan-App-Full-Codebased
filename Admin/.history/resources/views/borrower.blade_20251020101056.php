@extends('layouts.app')

@php
  $pageTitle = 'Borrower Management';
  $statuses  = ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('head')
<style>
  /* Cards and Buttons */
  .btn { @apply inline-flex items-center justify-center font-semibold rounded-lg px-3 py-2 text-sm transition-colors; }
  .btn-brand { @apply bg-indigo-600 text-white hover:bg-indigo-500; }
  .btn-outline { @apply border border-gray-200 bg-white hover:bg-gray-50 text-gray-700; }
  .metric-card { @apply bg-white border border-gray-100 rounded-2xl shadow-sm p-4; }
  .metric-card h6 { @apply text-xs font-semibold text-gray-500 uppercase mb-1 tracking-wide; }
  .metric-card p { @apply text-lg font-semibold text-gray-800; }

  /* Table */
  table { @apply w-full border-collapse min-w-[900px]; }
  thead { @apply bg-gray-50 text-xs text-gray-600 uppercase; }
  th, td { @apply px-4 py-3 text-left; }
  tbody tr:nth-child(even) { background: #fafafa; }
  tbody tr:hover td { background: #f9fafb; transition: background .2s; }

  /* Badges */
  .badge { @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold; }
  .badge[data-status=active] { @apply bg-green-100 text-green-700; }
  .badge[data-status=inactive] { @apply bg-gray-100 text-gray-600; }
  .badge[data-status=delinquent] { @apply bg-yellow-100 text-yellow-700; }
  .badge[data-status=closed] { @apply bg-blue-100 text-blue-700; }
  .badge[data-status=blacklisted] { @apply bg-red-100 text-red-700; }
</style>
@endsection

@section('content')

{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
  <div>
    <h2 class="text-xl font-bold text-gray-800">Borrower Management</h2>
    <p class="text-sm text-gray-500">Profiles, agreements, and loan history</p>
  </div>
  <div class="flex gap-2">
    <button class="btn btn-brand" onclick="openCreateModal()">+ Add Borrower</button>
    <button class="btn btn-outline" onclick="exportCsv()">Export CSV</button>
  </div>
</div>

{{-- Metrics --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="metric-card"><h6>Total</h6><p>{{ number_format($meta['total'] ?? 0) }}</p></div>
  <div class="metric-card"><h6>Page</h6><p>{{ $meta['page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}</p></div>
  <div class="metric-card"><h6>Per Page</h6><p>{{ $meta['per_page'] ?? 15 }}</p></div>
  <div class="metric-card"><h6>Active Filters</h6>
    <p class="text-sm text-gray-600">
      {{ collect(request()->except(['page']))->map(fn($v,$k)=>$k.':'.(is_array($v)?implode(',',$v):$v))->join(', ') ?: 'None' }}
    </p>
  </div>
</div>

{{-- Filters --}}
<form action="{{ route('borrowers.index') }}" method="GET" 
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 grid md:grid-cols-12 gap-3 items-end mb-6">
  <div class="md:col-span-4">
    <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
    <input type="text" name="q" value="{{ request('q') }}"
           class="w-full rounded-lg border-gray-200 bg-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
           placeholder="Name / Email / Phone / Ref / Address">
  </div>
  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
    <select name="status" class="w-full rounded-lg border-gray-200 bg-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
      <option value="">All</option>
      @foreach ($statuses as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
  </div>
  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-gray-600 mb-1">Archived</label>
    <select name="archived" class="w-full rounded-lg border-gray-200 bg-gray-100 px-2 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
      <option value="">All</option>
      <option value="0" @selected(request('archived')==='0')>No</option>
      <option value="1" @selected(request('archived')==='1')>Yes</option>
    </select>
  </div>
  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-gray-600 mb-1">Income Min</label>
    <input type="number" name="min_income" step="0.01" value="{{ request('min_income') }}"
           class="w-full rounded-lg border-gray-200 bg-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
  </div>
  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-gray-600 mb-1">Income Max</label>
    <input type="number" name="max_income" step="0.01" value="{{ request('max_income') }}"
           class="w-full rounded-lg border-gray-200 bg-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
  </div>
  <div class="md:col-span-12 flex flex-wrap gap-2">
    <button class="btn btn-brand">Apply</button>
    <a href="{{ route('borrowers.index') }}" class="btn btn-outline">Reset</a>
  </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Archived</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($rows as $i => $row)
          <tr>
            <td>{{ $rows->firstItem() + $i }}</td>
            <td><strong>{{ $row->full_name }}</strong></td>
            <td><a href="mailto:{{ $row->email }}" class="text-indigo-600 hover:underline">{{ $row->email }}</a></td>
            <td>{{ $row->phone }}</td>
            <td>{{ $row->address }}</td>
            <td><span class="badge" data-status="{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
            <td>@if($row->is_archived)<span class="badge bg-gray-200 text-gray-700">Yes</span>@else<span class="badge bg-green-100 text-green-700">No</span>@endif</td>
            <td><button class="btn btn-quiet text-xs" onclick="openEditModal({{ $row->id }})">Edit</button></td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center py-6 text-gray-500">No borrowers found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex justify-between items-center p-4 text-sm text-gray-600">
    <div>Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}</div>
    <div>{{ $rows->onEachSide(1)->links() }}</div>
  </div>
</div>

@endsection

@section('scripts')
<script>
function openCreateModal(){alert('TODO: open create modal')}
function exportCsv(){alert('TODO: export CSV')}
</script>
@endsection
