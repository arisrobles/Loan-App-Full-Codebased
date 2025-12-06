@extends('layouts.app')

@php
  $pageTitle = 'Borrower Management';
  $statuses  = ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('head')
<style>
  html,body {font-family:'Inter',sans-serif; background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%); color:#0f172a;}
  
  /* Header */
  .mx-header {
    background:linear-gradient(135deg,rgba(99,102,241,.85),rgba(168,85,247,.8));
    backdrop-filter:blur(14px);
    color:white;
    border-bottom:1px solid rgba(255,255,255,.2);
    box-shadow:0 10px 25px -10px rgba(99,102,241,.4);
  }

  /* Buttons */
  .btn {display:inline-flex;align-items:center;justify-content:center;font-weight:600;
        border-radius:.75rem;transition:.2s;padding:.6rem 1.1rem;font-size:.875rem;}
  .btn-brand {background:linear-gradient(90deg,#6366f1,#a855f7);color:white;box-shadow:0 3px 10px rgba(99,102,241,.3);}
  .btn-brand:hover {opacity:.9;box-shadow:0 6px 16px rgba(99,102,241,.35);}
  .btn-outline {border:1px solid #e2e8f0;background:white;color:#334155;}
  .btn-outline:hover {background:#f8fafc;}
  .btn-quiet {background:#f1f5f9;color:#1e293b;}
  .btn-quiet:hover {background:#e2e8f0;}

  /* Metrics */
  .metric-card {
    background:white;
    border:1px solid #f1f5f9;
    border-radius:1rem;
    padding:1.25rem;
    box-shadow:0 10px 30px -12px rgba(79,70,229,.15);
    position:relative;
    overflow:hidden;
  }
  .metric-card::after {
    content:"";
    position:absolute;inset:0;
    background:radial-gradient(80% 70% at 0% 0%,rgba(168,85,247,.05),transparent);
    z-index:0;
  }
  .metric-card h6 {font-size:.7rem;text-transform:uppercase;color:#94a3b8;margin-bottom:.25rem;letter-spacing:.04em;z-index:1;position:relative;}
  .metric-card p {font-size:1.25rem;font-weight:700;z-index:1;position:relative;}

  /* Filter form */
  .filter-panel {
    background:white;
    border-radius:1.25rem;
    box-shadow:0 10px 28px -16px rgba(99,102,241,.15);
    border:1px solid #eef2ff;
  }
  label {font-size:.75rem;font-weight:600;color:#475569;letter-spacing:.03em;}
  input,select {
    border-radius:.75rem;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    height:2.5rem;
    width:100%;
    padding:.4rem .75rem;
    font-size:.875rem;
    transition:border .2s, box-shadow .2s;
  }
  input:focus,select:focus {
    border-color:#6366f1;
    box-shadow:0 0 0 3px rgba(99,102,241,.2);
    outline:none;
  }

  /* Table */
  .table-wrap {overflow:auto;border-radius:1rem;}
  table {width:100%;border-collapse:collapse;min-width:950px;}
  thead {background:#f8fafc;text-transform:uppercase;font-size:.7rem;color:#475569;}
  th,td {padding:.9rem 1rem;text-align:left;}
  tbody tr:nth-child(even) {background:#fdfdfd;}
  tbody tr:hover td {background:#f4f6ff;transition:.25s;}
  tbody td strong {color:#0f172a;}
  .badge {display:inline-flex;align-items:center;padding:0.25rem .7rem;border-radius:9999px;font-size:.75rem;font-weight:600;}
  .badge[data-status=active]{background:#dcfce7;color:#166534;}
  .badge[data-status=inactive]{background:#f1f5f9;color:#475569;}
  .badge[data-status=delinquent]{background:#fef9c3;color:#92400e;}
  .badge[data-status=closed]{background:#e0f2fe;color:#0369a1;}
  .badge[data-status=blacklisted]{background:#fee2e2;color:#991b1b;}

  /* Pagination */
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

{{-- Header --}}
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

{{-- Metrics --}}
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

{{-- Filters --}}
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

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
  <div class="table-wrap">
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
          <tr><td colspan="8" class="text-center py-8 text-gray-500">No borrowers found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex justify-between items-center p-5 text-sm text-gray-600 bg-gray-50 border-t border-gray-100">
    <div>Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}</div>
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
