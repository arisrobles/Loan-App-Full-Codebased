{{-- resources/views/borrowers/index.blade.php --}}
@extends('layouts.app')

@php
  use App\Models\Borrower;

  $pageTitle = 'Borrower Management';
  $statuses  = Borrower::STATUSES ?? ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('head')
<style>
  html,body {
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,#020617 0,#020617 220px,#f3f4f6 220px,#f9fafb 100%);
    color:#0f172a;
  }

  /* HERO similar to loans (dark navy) */
  .mx-header {
    background:linear-gradient(135deg,#020617,#0f172a);
    color:white;
    border-radius:1rem;
    border:1px solid rgba(148,163,184,.4);
    box-shadow:
      0 18px 48px -22px rgba(15,23,42,.9),
      0 0 0 1px rgba(15,23,42,.8) inset;
  }

  /* GENERIC BUTTONS (matching loans) */
  .btn {
    display:inline-flex;align-items:center;justify-content:center;
    font-weight:600;border-radius:.75rem;transition:.18s;
    padding:.55rem 1.05rem;font-size:.78rem;cursor:pointer;border:none;
    white-space:nowrap;
  }
  .btn svg {width:14px;height:14px;margin-right:.3rem;}

  .btn-brand {
    background:linear-gradient(90deg,#2563eb,#4f46e5);
    color:white;box-shadow:0 3px 12px rgba(37,99,235,.45);
  }
  .btn-brand:hover {transform:translateY(-1px);box-shadow:0 7px 20px rgba(37,99,235,.6);}

  .btn-outline {
    border:1px solid rgba(148,163,184,.6);
    background:transparent;color:#e5e7eb;
  }
  .btn-outline:hover {background:rgba(15,23,42,.7);}

  .btn-quiet {
    background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0;
  }
  .btn-quiet:hover {background:#e5edff;border-color:#6366f1;}

  /* METRIC CARDS similar to loan tiles */
  .metric-card {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:1rem;
    padding:1rem 1.1rem;
    box-shadow:0 10px 30px -18px rgba(15,23,42,.25);
  }
  .metric-card h6 {
    font-size:.68rem;text-transform:uppercase;color:#64748b;margin-bottom:.15rem;
    letter-spacing:.1em;font-weight:600;
  }
  .metric-card p {
    font-size:1.3rem;font-weight:700;color:#0f172a;
  }
  .metric-sub {
    font-size:.7rem;color:#94a3b8;margin-top:.15rem;
  }

  /* FILTER PANEL similar to loans filters */
  .filter-panel {
    background:white;
    border-radius:1.25rem;
    border:1px solid #e5e7eb;
    box-shadow:0 12px 32px -20px rgba(15,23,42,.25);
  }
  label {
    font-size:.7rem;font-weight:600;color:#475569;letter-spacing:.08em;
    text-transform:uppercase;
  }
  input,select {
    border-radius:.8rem;background:#f9fafb;border:1px solid #e2e8f0;
    height:2.5rem;width:100%;padding:.4rem .8rem;
    font-size:.82rem;transition:border .16s, box-shadow .16s, background .16s;
  }
  input:focus,select:focus {
    border-color:#2563eb;
    box-shadow:0 0 0 1px rgba(37,99,235,.7),0 0 0 4px rgba(37,99,235,.15);
    outline:none;background:white;
  }

  /* TABLE same spirit as loans table */
  .table-container {overflow:auto;border-radius:1.25rem 1.25rem 0 0;}
  table {width:100%;border-collapse:collapse;min-width:960px;}
  thead {
    background:linear-gradient(to right,#f8fafc,#e5edff);
    text-transform:uppercase;font-size:.68rem;color:#64748b;
    position:sticky;top:0;z-index:5;
  }
  th,td {padding:.75rem 1rem;text-align:left;}
  thead th {font-weight:700;border-bottom:1px solid #e2e8f0;}
  tbody tr:nth-child(even) {background:#fdfdfd;}
  tbody tr:hover td {
    background:#eef2ff;transition:.2s;
  }

  /* STATUS BADGES (compact like loans) */
  .badge {
    display:inline-flex;align-items:center;justify-content:center;
    padding:0.18rem .6rem;
    border-radius:9999px;font-size:.7rem;font-weight:600;
  }
  .badge[data-status=active]{background:#dcfce7;color:#166534;}
  .badge[data-status=inactive]{background:#e2e8f0;color:#475569;}
  .badge[data-status=delinquent]{background:#fef9c3;color:#92400e;}
  .badge[data-status=closed]{background:#e0f2fe;color:#0369a1;}
  .badge[data-status=blacklisted]{background:#fee2e2;color:#b91c1c;}

  /* PAGINATION FOOTER (same pattern as loans) */
  .pagination {display:flex;gap:.25rem;align-items:center;justify-content:center;font-size:.78rem;}
  .pagination span, .pagination a {
    padding:.35rem .66rem;border-radius:.55rem;
    font-weight:500;
  }
  .pagination a {background:white;border:1px solid #e2e8f0;color:#475569;}
  .pagination a:hover {background:#e5edff;}
  .pagination .active {background:#2563eb;color:white;border-color:#2563eb;}

  /* MODAL similar styling to loans detail style */
  .modal-backdrop {
    position:fixed;inset:0;background:rgba(15,23,42,.55);
    display:flex;align-items:flex-start;justify-content:center;
    z-index:50;padding-top:5vh;
  }
  .modal-panel {
    width:100%;max-width:540px;background:white;border-radius:1.4rem;
    box-shadow:0 24px 60px -24px rgba(15,23,42,.55);
    padding:1.5rem 1.75rem;position:relative;
    animation:modalIn .17s ease-out;
  }
  .modal-panel h3{font-size:.95rem;font-weight:700;margin-bottom:.4rem;color:#0f172a;}
  .modal-panel p.helper{font-size:.74rem;color:#64748b;margin-bottom:.9rem;}
  .modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;}
  .modal-grid-full{grid-column:1 / -1;}

  @keyframes modalIn {
    from {opacity:0;transform:translateY(10px) scale(.98);}
    to   {opacity:1;transform:translateY(0) scale(1);}
  }
</style>
@endsection

@section('content')

{{-- FLASH MESSAGE --}}
@if(session('success'))
  <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200 shadow-sm">
    {{ session('success') }}
  </div>
@endif

{{-- HERO (matching loans style) --}}
<div class="mx-header mb-7">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="space-y-1">
      <h1 class="text-xl font-semibold tracking-tight flex items-center gap-2">
        Borrower Portfolio
        <span class="inline-flex items-center justify-center text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-200 border border-emerald-400/40">
          Core Module
        </span>
      </h1>
      <p class="text-xs md:text-sm text-slate-200/90">
        Centralized record of all borrowers linked to your loan pipeline.
      </p>
      <p class="text-[11px] text-slate-300/85 flex items-center gap-2">
        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        {{ number_format($meta['total'] ?? 0) }} borrower{{ ($meta['total'] ?? 0) == 1 ? '' : 's' }} on file
      </p>
    </div>

    <div class="flex flex-wrap gap-2">
      <button class="btn btn-brand" onclick="openCreateModal()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Borrower
      </button>
      <button class="btn btn-outline" onclick="exportCsv()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7h8m-8 4h8m-5 4h5m-7 0l-4 4m4-4l-4-4" />
        </svg>
        Export CSV
      </button>
    </div>
  </div>
</div>

{{-- METRIC TILES (aligned with loan summary tiles) --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7">
  <div class="metric-card">
    <h6>Total Borrowers</h6>
    <p>{{ number_format($meta['total'] ?? 0) }}</p>
    <div class="metric-sub">Active + archived</div>
  </div>

  <div class="metric-card">
    <h6>Current Page</h6>
    <p>{{ $meta['page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}</p>
    <div class="metric-sub">Pagination state</div>
  </div>

  <div class="metric-card">
    <h6>Per Page</h6>
    <p>{{ $meta['per_page'] ?? 15 }}</p>
    <div class="metric-sub">Rows per view</div>
  </div>

  <div class="metric-card">
    <h6>Active Filters</h6>
    <p style="font-size:.8rem;font-weight:500;color:#1e293b;">
      {{ collect(request()->except(['page']))
          ->map(fn($v,$k)=>$k.':'.(is_array($v)?implode(',',$v):$v))
          ->join(' • ') ?: 'None' }}
    </p>
    <div class="metric-sub">Use the panel below to refine</div>
  </div>
</div>

{{-- FILTERS (similar structure to loans filters) --}}
<div class="filter-panel p-5 mb-8">
  <form action="{{ route('borrowers.index') }}" method="GET"
        class="grid md:grid-cols-12 gap-3 items-end">
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
    <div class="md:col-span-12 flex flex-wrap gap-2 mt-2">
      <button class="btn btn-brand">
        Apply Filters
      </button>
      <a href="{{ route('borrowers.index') }}" class="btn btn-quiet">
        Reset
      </a>
    </div>
  </form>
</div>

{{-- TABLE (aligned with loans table vibe) --}}
<div class="bg-white rounded-2xl shadow-[0_16px_40px_-20px_rgba(15,23,42,0.45)] border border-slate-100 overflow-hidden">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Borrower</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Status</th>
          <th>Archived</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse ($rows as $i => $row)
          <tr class="transition hover:bg-indigo-50/40">
            <td class="px-4 py-3 font-medium text-gray-700">
              {{ $rows->firstItem() + $i }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold flex items-center justify-center">
                  {{ strtoupper(mb_substr($row->full_name,0,1)) }}
                </div>
                <div>
                  <a href="{{ route('borrowers.show', $row) }}" class="font-semibold text-gray-900 hover:text-indigo-600 hover:underline">
                    {{ $row->full_name }}
                  </a>
                  @if($row->reference_no)
                    <div class="text-[11px] text-slate-500">
                      Ref: {{ $row->reference_no }}
                    </div>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              @if($row->email)
                <a href="mailto:{{ $row->email }}" class="text-indigo-600 hover:text-indigo-500 font-medium text-sm">
                  {{ $row->email }}
                </a>
              @else
                <span class="text-gray-400 italic text-xs">No email</span>
              @endif
            </td>
            <td class="px-4 py-3 text-sm">
              {{ $row->phone ?: '—' }}
            </td>
            <td class="px-4 py-3 text-gray-600 text-sm">
              {{ $row->address ?: '—' }}
            </td>
            <td class="px-4 py-3">
              <span class="badge" data-status="{{ $row->status ?? 'inactive' }}">
                {{ ucfirst($row->status ?? 'inactive') }}
              </span>
            </td>
            <td class="px-4 py-3">
              @if($row->is_archived)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-slate-200 text-slate-700">
                  Archived
                </span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                  Active
                </span>
              @endif
            </td>
            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
              {{-- EDIT BUTTON (opens modal) --}}
              <button
                type="button"
                class="btn btn-quiet text-[11px]"
                onclick="openEditModal(@js([
                    'id'             => $row->id,
                    'full_name'      => $row->full_name,
                    'email'          => $row->email,
                    'phone'          => $row->phone,
                    'address'        => $row->address,
                    'sex'            => $row->sex,
                    'occupation'     => $row->occupation,
                    'birthday'       => optional($row->birthday)->format('Y-m-d'),
                    'monthly_income' => $row->monthly_income,
                    'civil_status'   => $row->civil_status,
                    'reference_no'   => $row->reference_no,
                    'status'         => $row->status,
                    'is_archived'    => $row->is_archived,
                ]))"
              >
                Edit
              </button>

              {{-- Archive / Unarchive --}}
              @if(!$row->is_archived)
                <form action="{{ route('borrowers.archive', $row) }}" method="POST" class="inline">
                  @csrf
                  <button class="btn btn-quiet text-[11px] bg-slate-50 hover:bg-slate-200"
                          onclick="return confirm('Archive this borrower?')">
                    Archive
                  </button>
                </form>
              @else
                <form action="{{ route('borrowers.unarchive', $row) }}" method="POST" class="inline">
                  @csrf
                  <button class="btn btn-quiet text-[11px] bg-emerald-50 hover:bg-emerald-100">
                    Unarchive
                  </button>
                </form>
              @endif

              {{-- Soft delete --}}
              <form action="{{ route('borrowers.destroy', $row) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-quiet text-[11px] bg-red-50 hover:bg-red-100 text-red-700"
                        onclick="return confirm('Delete this borrower (soft delete)?')">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-10 text-gray-400 italic">
              No borrowers found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex flex-col md:flex-row justify-between items-center px-5 py-4 bg-slate-50 border-t border-gray-100 text-xs text-gray-600 gap-2">
    <div>
      @if ($rows->total())
        Showing 
        <span class="font-semibold">{{ $rows->firstItem() }}</span>–
        <span class="font-semibold">{{ $rows->lastItem() }}</span>
        of <span class="font-semibold">{{ $rows->total() }}</span>
      @else
        No results
      @endif
    </div>
    <div class="pagination">
      {{ $rows->onEachSide(1)->links() }}
    </div>
  </div>
</div>

{{-- CREATE MODAL --}}
<div id="createModal" class="modal-backdrop" style="display:none;">
  <div class="modal-panel">
    <div class="flex items-center justify-between mb-1">
      <h3>Add Borrower</h3>
      <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
    </div>
    <p class="helper">
      Capture KYC and contact details for a new borrower.
    </p>
    <form action="{{ route('borrowers.store') }}" method="POST" class="space-y-3">
      @csrf
      <div class="modal-grid">
        <div class="modal-grid-full">
          <label>Full Name</label>
          <input type="text" name="full_name" required>
        </div>
        <div>
          <label>Email</label>
          <input type="email" name="email">
        </div>
        <div>
          <label>Phone</label>
          <input type="text" name="phone">
        </div>
        <div class="modal-grid-full">
          <label>Address</label>
          <input type="text" name="address">
        </div>
        <div>
          <label>Sex</label>
          <select name="sex">
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Prefer not to say</option>
          </select>
        </div>
        <div>
          <label>Occupation</label>
          <input type="text" name="occupation">
        </div>
        <div>
          <label>Birthday</label>
          <input type="date" name="birthday">
        </div>
        <div>
          <label>Monthly Income</label>
          <input type="number" name="monthly_income" step="0.01" min="0">
        </div>
        <div>
          <label>Civil Status</label>
          <input type="text" name="civil_status">
        </div>
        <div>
          <label>Reference No.</label>
          <input type="text" name="reference_no">
        </div>
        <div class="modal-grid-full">
          <label>Status</label>
          <select name="status">
            @foreach($statuses as $s)
              <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn btn-quiet" onclick="closeCreateModal()">Cancel</button>
        <button type="submit" class="btn btn-brand">Save Borrower</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="modal-backdrop" style="display:none;">
  <div class="modal-panel">
    <div class="flex items-center justify-between mb-1">
      <h3>Edit Borrower</h3>
      <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
    </div>
    <p class="helper">
      Update borrower details. Changes will flow through linked loans and reports.
    </p>
    <form id="editForm" method="POST" class="space-y-3">
      @csrf
      @method('PUT')
      <div class="modal-grid">
        <div class="modal-grid-full">
          <label>Full Name</label>
          <input type="text" name="full_name" id="edit_full_name" required>
        </div>
        <div>
          <label>Email</label>
          <input type="email" name="email" id="edit_email">
        </div>
        <div>
          <label>Phone</label>
          <input type="text" name="phone" id="edit_phone">
        </div>
        <div class="modal-grid-full">
          <label>Address</label>
          <input type="text" name="address" id="edit_address">
        </div>
        <div>
          <label>Sex</label>
          <select name="sex" id="edit_sex">
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Prefer not to say</option>
          </select>
        </div>
        <div>
          <label>Occupation</label>
          <input type="text" name="occupation" id="edit_occupation">
        </div>
        <div>
          <label>Birthday</label>
          <input type="date" name="birthday" id="edit_birthday">
        </div>
        <div>
          <label>Monthly Income</label>
          <input type="number" name="monthly_income" step="0.01" min="0" id="edit_monthly_income">
        </div>
        <div>
          <label>Civil Status</label>
          <input type="text" name="civil_status" id="edit_civil_status">
        </div>
        <div>
          <label>Reference No.</label>
          <input type="text" name="reference_no" id="edit_reference_no">
        </div>
        <div class="modal-grid-full">
          <label>Status</label>
          <select name="status" id="edit_status">
            @foreach($statuses as $s)
              <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn btn-quiet" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-brand">Update Borrower</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
<script>
  function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
  }
  function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
  }

  function openEditModal(borrower) {
    const form = document.getElementById('editForm');
    form.action = "{{ url('/borrowers') }}/" + borrower.id;

    document.getElementById('edit_full_name').value       = borrower.full_name ?? '';
    document.getElementById('edit_email').value           = borrower.email ?? '';
    document.getElementById('edit_phone').value           = borrower.phone ?? '';
    document.getElementById('edit_address').value         = borrower.address ?? '';
    document.getElementById('edit_sex').value             = borrower.sex ?? '';
    document.getElementById('edit_occupation').value      = borrower.occupation ?? '';
    document.getElementById('edit_birthday').value        = borrower.birthday ?? '';
    document.getElementById('edit_monthly_income').value  = borrower.monthly_income ?? '';
    document.getElementById('edit_civil_status').value    = borrower.civil_status ?? '';
    document.getElementById('edit_reference_no').value    = borrower.reference_no ?? '';
    document.getElementById('edit_status').value          = borrower.status ?? '';

    document.getElementById('editModal').style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }

  // Export CSV with current filters
  function exportCsv() {
    const baseUrl = "{{ route('borrowers.export.csv') }}";
    const params = new URLSearchParams(window.location.search);
    const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
    window.location.href = url;
  }
</script>
@endsection