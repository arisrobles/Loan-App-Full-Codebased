@extends('layouts.app')

@php
  $pageTitle = 'Borrower Management — Elite UI';
  $statuses  = ['active','inactive','delinquent','closed','blacklisted'];

  $meta = $meta ?? [
    'page'      => method_exists($rows,'currentPage') ? $rows->currentPage() : 1,
    'last_page' => method_exists($rows,'lastPage') ? $rows->lastPage() : 1,
    'query'     => request()->query(),
    'per_page'  => method_exists($rows,'perPage') ? $rows->perPage() : 15,
    'total'     => method_exists($rows,'total') ? $rows->total() : (is_countable($rows) ? count($rows) : 0),
  ];
  $buildQuery = function(array $overrides = []) use ($meta) {
    return http_build_query(array_merge($meta['query'] ?? [], $overrides));
  };
  $page = (int) ($meta['page'] ?? 1);
  $last = (int) ($meta['last_page'] ?? 1);
  $window = 1;
  $pages = collect(range(1, $last))
    ->filter(fn($p)=> $p === 1 || $p === $last || abs($p - $page) <= $window)
    ->values()->all();
@endphp

@section('head')
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','-apple-system','Segoe UI','Roboto'] },
          colors: {
            brand:{600:'#4f46e5',700:'#4338ca'},
            ok:{100:'#dcfce7',600:'#16a34a'},
            warn:{100:'#fef9c3',600:'#ca8a04'},
            info:{100:'#dbeafe',600:'#2563eb'},
            danger:{100:'#fee2e2',600:'#dc2626'},
          },
          boxShadow:{
            card:'0 14px 42px -16px rgba(2,6,23,.18)',
            soft:'0 8px 24px -12px rgba(2,6,23,.12)',
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    html,body{font-family:Inter,ui-sans-serif,system-ui,-apple-system}
    :root{--hdr:64px}
    body::before{
      content:"";position:fixed;inset:-25%;z-index:-1;
      background:
        radial-gradient(36% 28% at 8% 10%, rgba(79,70,229,.07), transparent 60%),
        radial-gradient(40% 30% at 92% 12%, rgba(16,185,129,.06), transparent 60%),
        radial-gradient(50% 35% at 50% 110%, rgba(37,99,235,.05), transparent 60%);
    }
    .dark body::before{
      filter:saturate(.9) brightness(.9);
      background:
        radial-gradient(36% 28% at 8% 10%, rgba(79,70,229,.03), transparent 60%),
        radial-gradient(40% 30% at 92% 12%, rgba(16,185,129,.03), transparent 60%),
        radial-gradient(50% 35% at 50% 110%, rgba(37,99,235,.03), transparent 60%);
    }

    .container-safe{max-width:1320px;margin-inline:auto;padding-inline:1rem}
    @media(min-width:768px){.container-safe{padding-inline:1.5rem}}

    /* Header */
    .stuck{position:sticky;top:0;z-index:40;backdrop-filter:blur(10px)}
    .stuck.is-stuck{box-shadow:0 8px 16px -12px rgba(15,23,42,.3)}

    /* Table + hover */
    .table-wrap{overflow:auto;-webkit-overflow-scrolling:touch}
    .table-sticky thead th{position:sticky;top:calc(var(--hdr) + 48px);background:#f8fafc;z-index:30}
    tbody tr{transition:background .12s ease}
    tbody tr:hover>td{background:#f9fafb}
    .table-wrap::-webkit-scrollbar{height:10px}
    .table-wrap::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
    .table-section{margin-top:calc(var(--hdr) + 100px)}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-weight:600;
      border-radius:.75rem;transition:all .12s ease;min-height:40px;padding:0 .9rem}
    .btn-brand{background:#4f46e5;color:#fff}.btn-brand:hover{background:#4338ca}
    .btn-outline{border:1px solid #e2e8f0;background:#fff}.btn-outline:hover{background:#f8fafc}
    .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e2e8f0}
    .btn-xs{min-height:30px;height:30px;padding:0 10px;font-size:.75rem;border-radius:.5rem}

    /* Badges / statuses */
    .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:9999px;
      font-size:12px;font-weight:600;text-transform:capitalize}
    .badge[data-status=active]{background:#dcfce7;color:#166534}
    .badge[data-status=inactive]{background:#f1f5f9;color:#475569}
    .badge[data-status=delinquent]{background:#fef9c3;color:#92400e}
    .badge[data-status=closed]{background:#e0f2fe;color:#0369a1}
    .badge[data-status=blacklisted]{background:#fee2e2;color:#991b1b}
  </style>
@endsection

@section('content')
<header class="bg-white/70 dark:bg-slate-900/70 border-b border-slate-200/80 dark:border-slate-800 stuck" id="hdr">
  <div class="container-safe">
    <div class="h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-brand-600/10 grid place-items-center text-brand-700">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
        </div>
        <div>
          <h1 class="text-lg md:text-xl font-semibold">Borrower Management</h1>
          <p class="text-xs text-slate-500">Profiles, agreements, and loan history</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn btn-quiet" onclick="openCreateModal()">+ Add Borrower</button>
        <div data-menu>
          <button class="btn btn-quiet" onclick="toggleMenu(this)">Tools ▾</button>
          <div class="menu absolute right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-soft hidden">
            <button class="block w-full text-left px-3 py-2 hover:bg-slate-50" onclick="exportCsv()">Export CSV</button>
            <button class="block w-full text-left px-3 py-2 hover:bg-slate-50" onclick="toggleTheme()">Toggle Dark</button>
          </div>
        </div>
      </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pb-3">
      <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2 flex items-center justify-between">
        <div>
          <div class="text-[11px] uppercase text-slate-500">Total</div>
          <div class="text-[15px] font-semibold">{{ number_format($meta['total'] ?? 0) }}</div>
        </div>
        <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6"/></svg>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
        <div class="text-[11px] uppercase text-slate-500">Page</div>
        <div class="text-[15px] font-semibold">{{ $page }} / {{ $last }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
        <div class="text-[11px] uppercase text-slate-500">Showing</div>
        <div class="text-[15px] font-semibold">{{ $meta['per_page'] ?? 15 }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2">
        <div class="text-[11px] uppercase text-slate-500">Filters</div>
        <div class="text-[12px] text-slate-600 truncate">
          {{ collect(request()->except(['page']))->map(fn($v,$k)=> $k.':'.(is_array($v)?implode(',',$v):$v))->join(' • ') ?: 'None' }}
        </div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white/70 border-t border-slate-200/80">
    <div class="container-safe py-3">
      <form action="{{ route('borrowers.index') }}" method="GET" class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
        <div class="lg:col-span-4">
          <label class="block text-xs font-medium mb-1">Search</label>
          <input type="text" name="q" value="{{ request('q') }}"
                 class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm outline-none focus:ring-2 focus:ring-brand-600"
                 placeholder="Name / Email / Phone / Ref / Address">
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Status</label>
          <select name="status" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
            <option value="">All</option>
            @foreach ($statuses as $s)
              <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Archived</label>
          <select name="archived" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
            <option value="">All</option>
            <option value="0" @selected(request('archived')==='0')>No</option>
            <option value="1" @selected(request('archived')==='1')>Yes</option>
          </select>
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Income Min</label>
          <input type="number" name="min_income" step="0.01" value="{{ request('min_income') }}"
                 class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600">
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Income Max</label>
          <input type="number" name="max_income" step="0.01" value="{{ request('max_income') }}"
                 class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600">
        </div>
        <div class="lg:col-span-12 flex flex-wrap gap-2">
          <button class="h-10 px-4 rounded-lg bg-brand-600 text-white hover:bg-brand-700 text-sm">Apply</button>
          <a href="{{ route('borrowers.index') }}" class="h-10 px-4 rounded-lg btn-outline text-sm">Reset</a>
        </div>
      </form>
    </div>
  </div>
</header>

<main class="pb-10">
  <div class="container-safe px-4 md:px-6 space-y-6">
    <section class="table-section bg-white rounded-2xl shadow-card border border-slate-100">
      <div class="table-wrap" id="tblWrap">
        <table class="w-full text-[13.5px] table-sticky table-fixed">
          <thead class="text-slate-600 bg-slate-50">
            <tr>
              <th class="p-3">#</th>
              <th class="p-3 text-left">Name</th>
              <th class="p-3 text-left">Email</th>
              <th class="p-3 text-left">Phone</th>
              <th class="p-3 text-left">Address</th>
              <th class="p-3 text-left">Status</th>
              <th class="p-3 text-left">Archived</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($rows as $i => $row)
              <tr>
                <td class="p-3">{{ $rows->firstItem() + $i }}</td>
                <td class="p-3 font-medium">{{ $row->full_name }}</td>
                <td class="p-3"><a href="mailto:{{ $row->email }}" class="text-brand-700 hover:underline">{{ $row->email }}</a></td>
                <td class="p-3">{{ $row->phone }}</td>
                <td class="p-3">{{ $row->address }}</td>
                <td class="p-3"><span class="badge" data-status="{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                <td class="p-3">
                  @if ($row->is_archived)
                    <span class="badge bg-slate-200 text-slate-700">Archived</span>
                  @else
                    <span class="badge bg-ok-100 text-emerald-700">Active</span>
                  @endif
                </td>
                <td class="p-3">
                  <div class="flex flex-wrap gap-2">
                    <button class="btn btn-xs btn-quiet" onclick="openEditModal(this)"
                      data-id="{{ $row->id }}" data-name="{{ $row->full_name }}"
                      data-email="{{ $row->email }}" data-phone="{{ $row->phone }}"
                      data-address="{{ $row->address }}">Edit</button>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="p-10 text-center text-slate-500">No borrowers found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="p-4 border-t bg-slate-50">
        <div class="flex justify-between text-sm text-slate-600">
          <div>
            Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}
          </div>
          <div>{{ $rows->onEachSide(1)->links() }}</div>
        </div>
      </div>
    </section>
  </div>
</main>
@endsection

@section('scripts')
<script>
function setHeaderOffset(){
  const hdr=document.getElementById('hdr');
  const h=hdr?.offsetHeight||64;
  document.documentElement.style.setProperty('--hdr',h+'px');
  hdr?.classList.toggle('is-stuck',window.scrollY>4);
}
['load','resize','scroll'].forEach(evt=>window.addEventListener(evt,setHeaderOffset,{passive:true}));
function toggleMenu(btn){
  const wrap=btn.closest('[data-menu]');
  wrap.querySelector('.menu').classList.toggle('hidden');
}
function openCreateModal(){alert('TODO: open create modal')}
function exportCsv(){alert('TODO: export CSV')}
function toggleTheme(){document.documentElement.classList.toggle('dark')}
</script>
@endsection
