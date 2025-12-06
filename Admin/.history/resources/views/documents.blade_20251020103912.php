@extends('layouts.app')

@php
  $pageTitle = 'Document Management';
  $types  = ['Contract','Invoice','Receipt','Report','Misc'];
  $statuses = ['active','archived','expired'];
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
  .badge[data-status=archived]{background:#f1f5f9;color:#475569;}
  .badge[data-status=expired]{background:#fee2e2;color:#991b1b;}

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
      <h2 class="text-xl font-semibold">{{ $pageTitle }}</h2>
      <p class="text-sm text-indigo-100">Manage contracts, invoices, and document records</p>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-brand" onclick="openUploadModal()">+ Upload Document</button>
      <button class="btn btn-outline" onclick="exportDocs()">Export CSV</button>
    </div>
  </div>
</div>

{{-- METRICS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
  <div class="metric-card"><h6>Total</h6><p>{{ number_format($meta['total'] ?? 0) }}</p></div>
  <div class="metric-card"><h6>Active</h6><p>{{ $meta['active'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Archived</h6><p>{{ $meta['archived'] ?? 0 }}</p></div>
  <div class="metric-card"><h6>Expired</h6><p>{{ $meta['expired'] ?? 0 }}</p></div>
</div>

{{-- FILTER PANEL --}}
<form action="{{ route('documents.index') }}" method="GET"
      class="filter-panel p-5 grid md:grid-cols-12 gap-3 items-end mb-8">
  <div class="md:col-span-4">
    <label>Search</label>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Title / Owner / Type">
  </div>
  <div class="md:col-span-3">
    <label>Type</label>
    <select name="type">
      <option value="">All</option>
      @foreach ($types as $t)
        <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
      @endforeach
    </select>
  </div>
  <div class="md:col-span-3">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      @foreach ($statuses as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
  </div>
  <div class="md:col-span-2">
    <button class="btn btn-brand w-full">Apply</button>
  </div>
</form>

{{-- TABLE --}}
<div class="bg-white rounded-2xl shadow-[0_8px_28px_-12px_rgba(15,23,42,0.1)] border border-gray-100 overflow-hidden">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Type</th>
          <th>Owner</th>
          <th>Uploaded</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse ($rows as $i => $doc)
          <tr class="transition hover:bg-indigo-50/40 hover:shadow-[0_4px_16px_-8px_rgba(99,102,241,0.25)]">
            <td class="px-4 py-3 font-medium text-gray-800">{{ $rows->firstItem() + $i }}</td>
            <td class="px-4 py-3 font-semibold text-gray-900">{{ $doc->title }}</td>
            <td class="px-4 py-3">{{ $doc->type }}</td>
            <td class="px-4 py-3">{{ $doc->owner ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600">{{ $doc->uploaded_at?->format('Y-m-d') ?? '—' }}</td>
            <td class="px-4 py-3"><span class="badge" data-status="{{ $doc->status }}">{{ ucfirst($doc->status) }}</span></td>
            <td class="px-4 py-3 flex gap-1">
              <button class="btn btn-quiet text-xs border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50" onclick="viewDoc({{ $doc->id }})">View</button>
              <button class="btn btn-quiet text-xs border border-gray-200 hover:border-amber-400 hover:bg-amber-50" onclick="downloadDoc({{ $doc->id }})">Download</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center py-10 text-gray-400 italic">No documents found.</td></tr>
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
function openUploadModal(){alert('TODO: open upload modal')}
function exportDocs(){alert('TODO: export CSV')}
function viewDoc(id){alert('TODO: open document view for ID '+id)}
function downloadDoc(id){alert('TODO: trigger file download for ID '+id)}
</script>
@endsection
