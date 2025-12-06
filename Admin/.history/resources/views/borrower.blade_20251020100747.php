@section('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  html,body {
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,#f9fafb 0%,#f1f5f9 100%);
    color:#0f172a;
  }
  :root {--hdr:72px;}

  /* Header */
  header.elite-header {
    position:sticky;top:0;z-index:50;
    background:rgba(255,255,255,.8);
    backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(226,232,240,.6);
  }
  header.elite-header .title {
    font-weight:700;
    font-size:1.4rem;
  }
  header.elite-header .subtitle {
    font-size:.85rem;
    color:#64748b;
  }

  /* Buttons */
  .btn {
    display:inline-flex;align-items:center;justify-content:center;gap:.4rem;
    font-weight:600;
    border-radius:.75rem;
    transition:.15s;
    padding:.55rem 1rem;
    font-size:.875rem;
  }
  .btn-brand {background:#4f46e5;color:white;box-shadow:0 2px 8px rgba(79,70,229,.3);}
  .btn-brand:hover {background:#4338ca;}
  .btn-outline {background:white;border:1px solid #e2e8f0;color:#1e293b;}
  .btn-outline:hover {background:#f8fafc;}
  .btn-quiet {background:#f1f5f9;color:#1e293b;}
  .btn-quiet:hover {background:#e2e8f0;}

  /* Metrics */
  .metric {
    background:white;
    border-radius:1rem;
    padding:1rem 1.25rem;
    box-shadow:0 10px 30px -12px rgba(15,23,42,.1);
    border:1px solid #f1f5f9;
  }
  .metric h6 {
    font-size:.75rem;
    text-transform:uppercase;
    color:#94a3b8;
    letter-spacing:.05em;
  }
  .metric p {font-weight:600;font-size:1.15rem;}

  /* Filters */
  .filter-form {
    background:white;
    border:1px solid #f1f5f9;
    border-radius:1rem;
    box-shadow:0 6px 18px -10px rgba(15,23,42,.06);
    padding:1rem;
  }

  /* Table */
  .table-wrap {overflow:auto;border-radius:1rem;}
  table {
    width:100%;
    border-collapse:collapse;
    min-width:900px;
    background:white;
  }
  thead {
    background:#f8fafc;
    text-transform:uppercase;
    font-size:.75rem;
    color:#475569;
  }
  th,td {
    padding:.9rem 1rem;
    text-align:left;
  }
  tbody tr:nth-child(even) {background:#fafafa;}
  tbody tr:hover td {background:#f1f5f9;}
  .badge {
    display:inline-flex;align-items:center;
    padding:0.25rem 0.6rem;
    border-radius:9999px;
    font-size:.75rem;font-weight:600;
  }
  .badge[data-status=active]{background:#dcfce7;color:#166534;}
  .badge[data-status=inactive]{background:#f1f5f9;color:#475569;}
  .badge[data-status=delinquent]{background:#fef9c3;color:#92400e;}
  .badge[data-status=closed]{background:#e0f2fe;color:#0369a1;}
  .badge[data-status=blacklisted]{background:#fee2e2;color:#991b1b;}
</style>
@endsection

@section('content')

<header class="elite-header">
  <div class="container-safe py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
    <div>
      <div class="title">Borrower Management</div>
      <div class="subtitle">Profiles, agreements, and loan history</div>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-brand" onclick="openCreateModal()">+ Add Borrower</button>
      <button class="btn btn-outline" onclick="exportCsv()">Export CSV</button>
    </div>
  </div>
</header>

<main class="container-safe mt-8 space-y-8">

  {{-- METRICS --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="metric"><h6>Total</h6><p>{{ number_format($meta['total'] ?? 0) }}</p></div>
    <div class="metric"><h6>Page</h6><p>{{ $meta['page'] ?? 1 }} / {{ $meta['last_page'] ?? 1 }}</p></div>
    <div class="metric"><h6>Per Page</h6><p>{{ $meta['per_page'] ?? 15 }}</p></div>
    <div class="metric"><h6>Filters</h6>
      <p style="font-size:.8rem;color:#475569;">
        {{ collect(request()->except(['page']))->map(fn($v,$k)=>$k.':'.(is_array($v)?implode(',',$v):$v))->join(', ') ?: 'None' }}
      </p>
    </div>
  </div>

  {{-- FILTERS --}}
  <form action="{{ route('borrowers.index') }}" method="GET"
        class="filter-form grid md:grid-cols-12 gap-3 items-end">
    <div class="md:col-span-4">
      <label class="block text-xs font-medium mb-1 text-slate-600">Search</label>
      <input type="text" name="q" value="{{ request('q') }}"
             class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm outline-none focus:ring-2 focus:ring-brand-600"
             placeholder="Name / Email / Phone / Ref / Address">
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-medium mb-1 text-slate-600">Status</label>
      <select name="status" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
        <option value="">All</option>
        @foreach ($statuses as $s)
          <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-medium mb-1 text-slate-600">Archived</label>
      <select name="archived" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
        <option value="">All</option>
        <option value="0" @selected(request('archived')==='0')>No</option>
        <option value="1" @selected(request('archived')==='1')>Yes</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-medium mb-1 text-slate-600">Income Min</label>
      <input type="number" name="min_income" step="0.01" value="{{ request('min_income') }}"
             class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600">
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-medium mb-1 text-slate-600">Income Max</label>
      <input type="number" name="max_income" step="0.01" value="{{ request('max_income') }}"
             class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600">
    </div>
    <div class="md:col-span-12 flex flex-wrap gap-2">
      <button class="btn btn-brand">Apply</button>
      <a href="{{ route('borrowers.index') }}" class="btn btn-outline">Reset</a>
    </div>
  </form>

  {{-- TABLE --}}
  <div class="table-wrap shadow-md border border-slate-100 bg-white rounded-2xl">
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
          <td><a href="mailto:{{ $row->email }}" class="text-brand-700 hover:underline">{{ $row->email }}</a></td>
          <td>{{ $row->phone }}</td>
          <td>{{ $row->address }}</td>
          <td><span class="badge" data-status="{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
          <td>@if($row->is_archived)<span class="badge bg-slate-200 text-slate-700">Yes</span>
              @else<span class="badge bg-ok-100 text-emerald-700">No</span>@endif</td>
          <td><button class="btn btn-quiet text-xs" onclick="openEditModal({{ $row->id }})">Edit</button></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center py-6 text-slate-500">No borrowers found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="flex justify-between items-center mt-4 text-sm text-slate-600">
    <div>Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}</div>
    <div>{{ $rows->onEachSide(1)->links() }}</div>
  </div>
</main>

@endsection
