@extends('layouts.app')

@php
  $pageTitle = 'Borrower Management';
  $statuses  = ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('head')
<style>
  html,body {
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
    color:#0f172a;
  }

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
  .btn-brand {
    background:linear-gradient(90deg,#6366f1,#a855f7);
    color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);
  }
  .btn-brand:hover {opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline {border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover {background:#f8fafc;}
  .btn-quiet {background:#f1f5f9;color:#1e293b;}
  .btn-quiet:hover {background:#e2e8f0;}

  /* METRIC CARDS */
  .metric-card {
    background:white;border:1px solid #f1f5f9;border-radius:1rem;
    padding:1.25rem;box-shadow:0 10px 30px -12px rgba(79,70,229,.15);
    position:relative;overflow:hidden;
  }
  .metric-card::after {
    content:"";position:absolute;inset:0;
    background:radial-gradient(80% 70% at 0% 0%,rgba(168,85,247,.05),transparent);
  }
  .metric-card h6 {
    font-size:.7rem;text-transform:uppercase;color:#94a3b8;margin-bottom:.25rem;
    letter-spacing:.04em;font-weight:600;position:relative;z-index:1;
  }
  .metric-card p {font-size:1.25rem;font-weight:700;z-index:1;position:relative;}

  /* FILTERS */
  .filter-panel {
    background:white;border-radius:1.25rem;
    box-shadow:0 10px 28px -16px rgba(99,102,241,.15);
    border:1px solid #eef2ff;
  }
  label {
    font-size:.75rem;font-weight:600;color:#475569;letter-spacing:.03em;
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
  .table-container {overflow:auto;border-radius:1rem;position:relative;}
  table {width:100%;border-collapse:collapse;min-width:950px;}
  thead {
    background:linear-gradient(to right,#f8fafc,#eef2ff);
    text-transform:uppercase;font-size:.7rem;color:#475569;
    position:sticky;top:0;z-index:10;
  }
  th,td {padding:.9rem 1rem;text-align:left;}
  thead th {font-weight:700;border-bottom:1px solid #e2e8f0;}
  tbody tr:nth-child(even) {background:#fdfdfd;}
  tbody tr:hover td {
    background:#f4f6ff;transition:.25s;box-shadow:inset 0 0 0 9999px rgba(99,102,241,.03);
  }
  tbody td strong {color:#0f172a;}

  /* BADGES */
  .badge {
    display:inline-flex;align-items:center;padding:0.25rem .7rem;
    border-radius:9999px;font-size:.75rem;font-weight:600;
  }
  .badge[data-status=active]{background:#dcfce7;color:#166534;}
  .badge[data-status=inactive]{background:#f1f5f9;color:#475569;}
  .badge[data-status=delinquent]{background:#fef9c3;color:#92400e;}
  .badge[data-status=closed]{background:#e0f2fe;color:#0369a1;}
  .badge[data-status=blacklisted]{background:#fee2e2;color:#991b1b;}

  /* PAGINATION */
  .pagination {display:flex;gap:.25rem;align-items:center;justify-content:center;}
  .pagination span, .pagination a {
    padding:.4rem .7rem;border-radius:.5rem;
    font-size:.8rem;font-weight:500;
  }
  .pagination a {background:white;border:1px solid #e2e8f0;color:#475569;}
  .pagination a:hover {background:#f8fafc;}
  .pagination .active {background:#6366f1;color:white;}
</style>
@endsection


@section('content')

{{-- HEADER --}}
<div class="mx-header rounded-2xl mb-8 shadow-md">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold">Borrower Management</h2>
      <p class="text-sm text-indigo-100">Profiles, agreements, and loan history</p>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-brand" onclick="openCreateModal()">+ Add Borrower</button>
      <button class="btn btn-outline" onclick="exportCsv()">Export CSV</button>
    </div>
  </div>
</div>

{{-- METRICS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
  <div class="metric-card"><h6>Total</h6><p>{{ number_format($meta['total'] ?? 0) }}</p></div>
  <div class="metric-card"><h6>Page</h6><p>{{ $meta['page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}</p></div>
  <div class="metric-card"><h6>Per Page</h6><p>{{ $meta['per_page'] ?? 15 }}</p></div>
  <div class="metric-card"><h6>Active Filters</h6>
    <p style="font-size:.85rem;color:#475569;">
      {{ collect(request()->except(['page']))->map(fn($v,$k)=>$k.':'.(is_array($v)?implode(',',$v):$v))->join(', ') ?: 'None' }}
    </p>
  </div>
</div>

{{-- FILTER PANEL --}}
<form action="{{ route('borrowers.index') }}" method="GET"
      class="filter-panel p-5 grid md:grid-cols-12 gap-3 items-end mb-8">
  <div class="md:col-span-4">
    <label>Search</label>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Name / Email / Phone / Ref / Address">
  </div>
  <div class="md:col-span-2">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      @foreach ($statuses as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
  </div>
  <div class="md:col-span-2">
    <label>Archived</label>
    <select name="archived">
      <option value="">All</option>
      <option value="0" @selected(request('archived')==='0')>No</option>
      <option value="1" @selected(request('archived')==='1')>Yes</option>
    </select>
  </div>
  <div class="md:col-span-2">
    <label>Income Min</label>
    <input type="number" name="min_income" step="0.01" value="{{ request('min_income') }}">
  </div>
  <div class="md:col-span-2">
    <label>Income Max</label>
    <input type="number" name="max_income" step="0.01" value="{{ request('max_income') }}">
  </div>
  <div class="md:col-span-12 flex gap-2 mt-2">
    <button class="btn btn-brand">Apply Filters</button>
    <a href="{{ route('borrowers.index') }}" class="btn btn-outline">Reset</a>
  </div>
</form>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 overflow-hidden">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Status</th>
          <th>Archived</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse ($rows as $i => $row)
          <tr class="transition hover:bg-indigo-50/40 hover:shadow-[0_4px_16px_-8px_rgba(99,102,241,0.25)]">
            <td class="px-4 py-3 font-medium text-gray-800">{{ $rows->firstItem() + $i }}</td>
            <td class="px-4 py-3 font-semibold text-gray-900">{{ $row->full_name }}</td>
            <td class="px-4 py-3">
              <a href="mailto:{{ $row->email }}" class="text-indigo-600 hover:text-indigo-500 font-medium">{{ $row->email }}</a>
            </td>
            <td class="px-4 py-3">{{ $row->phone }}</td>
            <td class="px-4 py-3 text-gray-600">{{ $row->address }}</td>
            <td class="px-4 py-3"><span class="badge" data-status="{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
            <td class="px-4 py-3">
              @if($row->is_archived)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Yes</span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">No</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <button class="btn btn-quiet text-xs border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50"
                      onclick="openEditModal({{ $row->id }})">Edit</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center py-10 text-gray-400 italic">No borrowers found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex justify-between items-center px-5 py-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-600">
    <div>
      Showing 
      <span class="font-semibold">{{ $rows->firstItem() }}</span>–
      <span class="font-semibold">{{ $rows->lastItem() }}</span>
      of <span class="font-semibold">{{ $rows->total() }}</span>
    </div>
    <div class="pagination">{{ $rows->onEachSide(1)->links() }}</div>
  </div>
</div>

@endsection

@section('scripts')
<script>
function openCreateModal(){alert('TODO: open create modal')}
function exportCsv(){alert('TODO: export CSV')}
</script>
@endsection
