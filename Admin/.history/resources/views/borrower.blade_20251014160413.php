@extends('layouts.app')

@php
  $pageTitle = 'Borrower Management — Elite UI';
  $statuses  = ['active','inactive','delinquent','closed','blacklisted'];

  // Normalize pagination meta (works for paginator or plain collections)
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

  // Windowed pagination (no huge button lists)
  $window = 1; // neighbors each side
  $pages = collect(range(1, $last))
    ->filter(fn($p)=> $p === 1 || $p === $last || abs($p - $page) <= $window)
    ->values()
    ->all();
@endphp

@section('head')
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','-apple-system','Segoe UI','Roboto','Helvetica Neue','Arial'] },
          colors: {
            brand:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',600:'#4f46e5',700:'#4338ca',900:'#1e1b4b'},
            ink:{400:'#475569',500:'#0f172a'},
            ok:{100:'#dcfce7',600:'#16a34a'},
            warn:{100:'#fef9c3',600:'#ca8a04'},
            info:{100:'#dbeafe',600:'#2563eb'},
            danger:{100:'#fee2e2',600:'#dc2626'},
          },
          boxShadow:{
            card:'0 14px 42px -16px rgba(2,6,23,.18)',
            soft:'0 8px 24px -12px rgba(2,6,23,.12)',
            hdr:'0 10px 28px -18px rgba(2,6,23,.28)',
            ring:'0 0 0 6px rgba(79,70,229,.08)',
          },
          borderRadius:{ xl2:'1.1rem' }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    html,body{font-family:Inter,ui-sans-serif,system-ui,-apple-system}
    body::before{
      content:"";position:fixed;inset:-25%;z-index:-1;background:
        radial-gradient(36% 28% at 8% 10%, rgba(79,70,229,.07), transparent 60%),
        radial-gradient(40% 30% at 92% 12%, rgba(16,185,129,.06), transparent 60%),
        radial-gradient(50% 35% at 50% 110%, rgba(37,99,235,.05), transparent 60%);
      filter:saturate(1.05);
    }

    :root{ --hdr: 64px; }
    .container-safe{ max-width: 1320px; margin-inline: auto; }

    /* Header */
    .stuck{ position: sticky; top: 0; z-index: 40; backdrop-filter: blur(10px); }
    .stuck.is-stuck{ box-shadow: var(--hdr-shadow, 0 10px 24px -18px rgba(2,6,23,.35)); }

    /* Table */
    .table-wrap{ overflow:auto; -webkit-overflow-scrolling:touch; scroll-behavior:smooth; }
    .table-sticky thead th{ position: sticky; top: calc(var(--hdr) + 48px); background:#f8fafc; z-index: 30; }
    @media (max-width: 1023.98px){ .table-sticky thead th{ top: var(--hdr); } } /* when stat-ribbon wraps */
    .table-fixed{ table-layout: fixed; min-width: 980px; }
    .sticky-col{ position: sticky; left: 0; z-index: 31; background:#fff; }
    .table-wrap.scrolled .sticky-col{ box-shadow: 8px 0 14px -12px rgba(2,6,23,.28); }

    /* Cells */
    td,th,[data-menu]{ position: relative; }
    .cell-wrap{ white-space: normal; overflow-wrap: anywhere; word-break: break-word; hyphens: auto; }
    .td-stack{ padding-top:.6rem!important; vertical-align: top; }
    .td-stack .td-label{font-size:.68rem;line-height:1rem;color:#64748b;text-transform:uppercase;letter-spacing:.02em}
    .td-stack .td-block{display:grid;grid-template-rows:auto 1fr;row-gap:.2rem}
    tr:hover>td{ background: #fafafa; }

    /* Buttons & chips */
    .btn{display:inline-flex;align-items:center;gap:.5rem;min-height:40px;height:40px;padding:0 14px;border-radius:12px;font-weight:600;transition:transform .12s ease, box-shadow .12s ease}
    .btn:focus-visible{ outline: 2px solid #4f46e5; outline-offset: 2px; box-shadow: var(--btn-ring, 0 0 0 0 transparent); }
    .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e9eef5}
    .btn-brand{background:#4f46e5;color:#fff}.btn-brand:hover{background:#4338ca}
    .btn-outline{border:1px solid #e2e8f0;background:#fff}.btn-outline:hover{background:#f8fafc}
    .btn[disabled]{opacity:.6;pointer-events:none}
    .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:9999px;font-size:12px;font-weight:600}

    /* Density toggle */
    .density-compact table td,.density-compact table th{ padding: 8px 10px !important; }
    .density-compact .btn, .density-compact button { min-height: 34px; height: 34px; padding-inline: 10px; }

    /* Menu */
    [data-menu]{position:relative}
    [data-menu] .menu{
      display:none;position:absolute;right:0;top:100%;margin-top:.5rem;background:#fff;border:1px solid #e2e8f0;border-radius:.9rem;
      box-shadow:0 12px 38px rgba(2,6,23,.16);min-width:12rem;overflow:hidden; z-index: 60;
    }
    [data-menu].open .menu{display:block}
    [data-menu] .menu a,[data-menu] .menu button{ width:100%; text-align:left; }

    /* Column widths */
    .col-check{ width:48px; } .col-name{ min-width:260px; } .col-email{ min-width:240px; }
    .col-phone{ min-width:170px; } .col-addr{ min-width:300px; } .col-stat{ min-width:170px; }
    .col-arch{ min-width:140px; } .col-act{ min-width:260px; }

    /* Mobile card list */
    @media (max-width: 767.98px){
      .table-section{ display:none; }
      .cards-section{ display:block; }
    }
    @media (min-width: 768px){
      .table-section{ display:block; }
      .cards-section{ display:none; }
    }

    /* Scrollbars */
    .table-wrap::-webkit-scrollbar{ height: 10px; }
    .table-wrap::-webkit-scrollbar-thumb{ background:#cbd5e1; border-radius:8px; }

    /* Dark mode polish */
    .dark body::before{ filter: saturate(0.9) brightness(.9); }
    .dark .table-sticky thead th{ background:#0b1220; }
    .dark .table-wrap.scrolled .sticky-col{ box-shadow: 8px 0 14px -12px rgba(0,0,0,.8); }
    .dark .btn-outline{ border-color:#1f2937; background:#0b1220; }
  </style>
@endsection

@section('sidebar')
  <nav class="p-4 space-y-1 text-sm">
    <div class="px-2 text-[10px] uppercase tracking-wide text-white/70 mt-2 mb-1">Navigate</div>
    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</a>
    <a href="{{ route('loans.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Loans</a>
    <a href="{{ route('loans.index') }}?stage=new_application" class="block px-3 py-2 rounded-lg hover:bg-white/10">Applications</a>
    <a href="{{ route('repayments.applyPenalty', ['repayment'=>1]) }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Payments</a>
    <a href="{{ route('borrowers.index') }}" class="block px-3 py-2 rounded-lg bg-white/10">Borrowers</a>
    <a href="{{ url('/reports') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Reports</a>
    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded-lg hover:bg-white/10">Settings</a>
    <form action="{{ route('logout') }}" method="POST" class="px-3 pt-2">
      @csrf
      <button class="w-full text-left py-2 rounded-lg bg-red-600 hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Logout</button>
    </form>
  </nav>
@endsection

@section('content')
  {{-- Header --}}
  <header class="bg-white/70 dark:bg-slate-900/70 backdrop-blur border-b border-slate-200/80 dark:border-slate-800 stuck" id="hdr">
    <div class="container-safe px-4 md:px-6">
      <div class="h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-brand-600/10 grid place-items-center text-brand-700">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
          </div>
          <div>
            <h1 class="text-lg md:text-xl font-semibold">{{ __('Borrower Management') }}</h1>
            <p class="text-xs text-slate-500">Profiles, agreements, and loan history</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button class="btn btn-quiet" onclick="openCreateModal()" aria-label="Add Borrower">+ Add Borrower</button>
          <div data-menu>
            <button class="btn btn-quiet" onclick="toggleMenu(this)" aria-haspopup="menu" aria-expanded="false">Tools ▾</button>
            <div class="menu z-menu" role="menu">
              <button class="px-3 py-2 hover:bg-slate-50" role="menuitem" onclick="exportCsv()">Export CSV</button>
              <button class="px-3 py-2 hover:bg-slate-50" role="menuitem" onclick="toggleDensity()">Compact Density</button>
              <button class="px-3 py-2 hover:bg-slate-50" role="menuitem" onclick="toggleTheme()">Toggle Dark</button>
            </div>
          </div>
        </div>
      </div>

      {{-- Stat ribbon --}}
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-3 pb-3">
        <div class="rounded-xl border border-slate-200 bg-white/70 px-3 py-2 flex items-center justify-between">
          <div>
            <div class="text-[11px] uppercase text-slate-500">Total</div>
            <div class="text-[15px] font-semibold">{{ number_format((int)($meta['total'] ?? 0)) }}</div>
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
    <div class="bg-white/70 dark:bg-slate-900/70 border-t border-slate-200/80 dark:border-slate-800">
      <div class="container-safe px-4 md:px-6 py-3">
        <form action="{{ route('borrowers.index') }}" method="GET" class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
          <div class="lg:col-span-4">
            <label class="block text-xs font-medium mb-1">Search</label>
            <div class="relative">
              <input type="text" name="q" value="{{ request('q') }}"
                     class="w-full h-10 rounded-lg bg-slate-100 pl-9 pr-3 text-sm outline-none focus:ring-2 focus:ring-brand-600"
                     placeholder="Name / Email / Phone / Ref / Address" />
              <svg class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path d="M21 21l-4.3-4.3" stroke-width="2"/>
              </svg>
            </div>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium mb-1">Status</label>
            <select name="status" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
              <option value="">All</option>
              @foreach ($statuses as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium mb-1">Archived</label>
            <select name="archived" class="w-full h-10 rounded-lg bg-slate-100 px-2 text-sm focus:ring-2 focus:ring-brand-600">
              <option value="" @selected(request('archived')==='')>All</option>
              <option value="0" @selected(request('archived')==='0')>No</option>
              <option value="1" @selected(request('archived')==='1')>Yes</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium mb-1">Income Min</label>
            <input type="number" name="min_income" step="0.01" value="{{ request('min_income') }}"
                  class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600" />
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium mb-1">Income Max</label>
            <input type="number" name="max_income" step="0.01" value="{{ request('max_income') }}"
                  class="w-full h-10 rounded-lg bg-slate-100 px-3 text-sm focus:ring-2 focus:ring-brand-600" />
          </div>

          <div class="lg:col-span-12 flex flex-wrap gap-2">
            <button class="h-10 px-4 rounded-lg bg-brand-600 text-white hover:bg-brand-700 text-sm">Apply Filters</button>
            <a href="{{ route('borrowers.index') }}" class="h-10 px-4 rounded-lg btn-outline text-sm">Reset</a>
            <span class="text-xs text-slate-500 ml-auto">Tips: <kbd class="px-1 border rounded bg-slate-50">/</kbd> focus • <kbd class="px-1 border rounded bg-slate-50">Tab</kbd> jump</span>
          </div>
        </form>
      </div>
    </div>
  </header>

  {{-- Flash --}}
  <div class="container-safe p-4 md:p-6 space-y-3">
    @if (session('success'))
      <div class="rounded-xl bg-ok-100 border border-emerald-200 text-emerald-800 px-4 py-3 shadow-soft">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="rounded-xl bg-danger-100 border border-rose-200 text-rose-800 px-4 py-3 shadow-soft">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li class="cell-wrap">{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif
  </div>

  {{-- Bulk actions bar --}}
  <div class="container-safe">
    <div id="bulkBar" class="hidden bg-white rounded-xl border border-slate-200 shadow-soft px-4 py-3 -mt-2 mb-2 flex items-center justify-between">
      <div class="text-sm text-slate-700"><b id="bulkCount">0</b> selected</div>
      <div class="flex gap-2">
        <button class="btn btn-quiet" onclick="bulkArchive()">Archive</button>
        <button class="btn btn-quiet" onclick="bulkDelete()">Delete</button>
        <button class="btn btn-brand" onclick="bulkExport()">Export</button>
      </div>
    </div>
  </div>

  {{-- Main --}}
  <main class="pb-10">
    <div class="container-safe px-4 md:px-6 space-y-6">

      {{-- TABLE (md+) --}}
      <section class="table-section bg-white rounded-2xl shadow-card border border-slate-100 relative">
        <div class="table-wrap" id="tblWrap" role="region" aria-label="Borrowers table" tabindex="0">
          <table id="tbl" class="w-full text-[13.5px] table-sticky table-fixed">
            <colgroup>
              <col class="col-check" />
              <col class="col-name" />
              <col class="col-email" />
              <col class="col-phone" />
              <col class="col-addr" />
              <col class="col-stat" />
              <col class="col-arch" />
              <col class="col-act" />
            </colgroup>

            <thead class="select-none">
              <tr class="bg-slate-50 text-slate-600">
                <th class="p-3 w-10 sticky-col"><input type="checkbox" id="checkAll" aria-label="Select all rows"/></th>
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
              @forelse ($rows as $row)
                <tr class="align-top hover:bg-slate-50 focus-within:bg-slate-50">
                  <td class="p-3 sticky-col">
                    <input type="checkbox" class="rowCheck" value="{{ $row->id }}" aria-label="Select {{ $row->full_name }}">
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Name</span>
                      <span class="td-value font-medium cell-wrap">
                        <span class="inline-flex items-center gap-2 align-top">
                          <span class="h-8 w-8 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 grid place-items-center text-[11px] text-brand-900 ring-1 ring-white">
                            {{ strtoupper(substr($row->full_name,0,1)) }}
                          </span>
                          <span class="leading-5">{{ $row->full_name }}</span>
                        </span>
                      </span>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Email</span>
                      <a class="td-value cell-wrap text-brand-700 hover:underline" href="mailto:{{ $row->email }}">{{ $row->email }}</a>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Phone</span>
                      <a class="td-value cell-wrap" href="tel:{{ preg_replace('/\s+/','',$row->phone) }}">{{ $row->phone }}</a>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Address</span>
                      <span class="td-value cell-wrap">{{ $row->address }}</span>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Status</span>
                      <span class="td-value">
                        <form action="{{ route('borrowers.status', $row->id) }}" method="POST">
                          @csrf
                          <select name="status" class="border rounded px-2 py-1 text-xs focus:ring-2 focus:ring-brand-600 bg-white"
                                  onchange="this.form.submit()" title="Change status">
                            @foreach ($statuses as $s)
                              <option value="{{ $s }}" @selected($row->status===$s)>{{ ucfirst($s) }}</option>
                            @endforeach
                          </select>
                        </form>
                      </span>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Archived</span>
                      <span class="td-value">
                        @if ($row->is_archived)
                          <span class="badge bg-slate-200 text-slate-700">Archived</span>
                        @else
                          <span class="badge bg-ok-100 text-emerald-700">Active</span>
                        @endif
                      </span>
                    </div>
                  </td>
                  <td class="p-3 td-stack">
                    <div class="td-block">
                      <span class="td-label">Actions</span>
                      <span class="td-value">
                        <div class="flex flex-wrap gap-2">
                          <button class="bg-amber-500 hover:bg-amber-400 text-white px-3 py-1 rounded shadow-sm"
                            onclick="openEditModal(this)"
                            data-id="{{ $row->id }}"
                            data-name="{{ $row->full_name }}"
                            data-email="{{ $row->email }}"
                            data-phone="{{ $row->phone }}"
                            data-address="{{ $row->address }}">Edit</button>

                          @if (!$row->is_archived)
                            <form action="{{ route('borrowers.archive', $row->id) }}" method="POST" onsubmit="return confirm('Archive this borrower?')">
                              @csrf
                              <button class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded">Archive</button>
                            </form>
                          @else
                            <form action="{{ route('borrowers.unarchive', $row->id) }}" method="POST" onsubmit="return confirm('Unarchive this borrower?')">
                              @csrf
                              <button class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded">Unarchive</button>
                            </form>
                          @endif

                          <div data-menu>
                            <button type="button" class="px-3 py-1 rounded border hover:bg-slate-50" onclick="toggleMenu(this)" aria-haspopup="menu">More ▾</button>
                            <div class="menu z-menu" role="menu">
                              <form action="{{ route('borrowers.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Soft delete this borrower?')">
                                @csrf
                                <button class="w-full text-left px-3 py-2 hover:bg-slate-50" type="submit" role="menuitem">Soft Delete</button>
                              </form>
                              <form action="{{ route('borrowers.forceDestroy', $row->id) }}" method="POST" onsubmit="return confirm('Permanently delete this borrower? This cannot be undone.')">
                                @csrf
                                <button class="w-full text-left px-3 py-2 text-rose-700 hover:bg-rose-50" type="submit" role="menuitem">Force Delete</button>
                              </form>
                              <button class="w-full text-left px-3 py-2 hover:bg-slate-50" onclick="exportOne('{{ $row->id }}')" role="menuitem">Export Row</button>
                            </div>
                          </div>
                        </div>
                      </span>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="p-10">
                    <div class="text-center">
                      <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 grid place-items-center mb-2">
                        <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18M6 7v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                      </div>
                      <h3 class="font-semibold">No borrowers found</h3>
                      <p class="text-slate-500 text-sm">Try adjusting filters or add a new borrower.</p>
                      <button onclick="openCreateModal()" class="btn btn-brand mt-3">Add Borrower</button>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

      {{-- CARDS (mobile) --}}
      <section class="cards-section space-y-3">
        @forelse ($rows as $row)
          <article class="bg-white rounded-2xl shadow-card border border-slate-100 p-4">
            <div class="flex items-start gap-3">
              <div class="h-9 w-9 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 grid place-items-center text-[12px] text-brand-900">
                {{ strtoupper(substr($row->full_name,0,1)) }}
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                  <h3 class="font-semibold text-[15px] leading-5 cell-wrap">{{ $row->full_name }}</h3>
                  @if ($row->is_archived)
                    <span class="badge bg-slate-200 text-slate-700">Archived</span>
                  @else
                    <span class="badge bg-ok-100 text-emerald-700">Active</span>
                  @endif
                </div>
                <dl class="mt-2 grid grid-cols-1 gap-1 text-[13px] text-slate-600">
                  <div><dt class="inline text-slate-400">Email:</dt> <dd class="inline cell-wrap"><a class="hover:underline" href="mailto:{{ $row->email }}">{{ $row->email }}</a></dd></div>
                  <div><dt class="inline text-slate-400">Phone:</dt> <dd class="inline cell-wrap"><a href="tel:{{ preg_replace('/\s+/','',$row->phone) }}">{{ $row->phone }}</a></dd></div>
                  <div><dt class="inline text-slate-400">Address:</dt> <dd class="inline cell-wrap">{{ $row->address }}</dd></div>
                  <div>
                    <dt class="inline text-slate-400">Status:</dt>
                    <dd class="inline">
                      <form action="{{ route('borrowers.status', $row->id) }}" method="POST" class="inline">
                        @csrf
                        <select name="status" class="border rounded px-2 py-1 text-xs align-middle" onchange="this.form.submit()">
                          @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected($row->status===$s)>{{ ucfirst($s) }}</option>
                          @endforeach
                        </select>
                      </form>
                    </dd>
                  </div>
                </dl>

                <div class="mt-3 flex flex-wrap gap-2">
                  <button class="bg-amber-500 hover:bg-amber-400 text-white px-3 py-1 rounded"
                          onclick="openEditModal(this)"
                          data-id="{{ $row->id }}"
                          data-name="{{ $row->full_name }}"
                          data-email="{{ $row->email }}"
                          data-phone="{{ $row->phone }}"
                          data-address="{{ $row->address }}">Edit</button>

                  @if (!$row->is_archived)
                    <form action="{{ route('borrowers.archive', $row->id) }}" method="POST" onsubmit="return confirm('Archive this borrower?')">
                      @csrf
                      <button class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded">Archive</button>
                    </form>
                  @else
                    <form action="{{ route('borrowers.unarchive', $row->id) }}" method="POST" onsubmit="return confirm('Unarchive this borrower?')">
                      @csrf
                      <button class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-1 rounded">Unarchive</button>
                    </form>
                  @endif

                  <div data-menu>
                    <button type="button" class="px-3 py-1 rounded border" onclick="toggleMenu(this)">More ▾</button>
                    <div class="menu z-menu">
                      <form action="{{ route('borrowers.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Soft delete this borrower?')">
                        @csrf
                        <button class="w-full text-left px-3 py-2 hover:bg-slate-50" type="submit">Soft Delete</button>
                      </form>
                      <form action="{{ route('borrowers.forceDestroy', $row->id) }}" method="POST" onsubmit="return confirm('Permanently delete this borrower? This cannot be undone.')">
                        @csrf
                        <button class="w-full text-left px-3 py-2 text-rose-700 hover:bg-rose-50" type="submit">Force Delete</button>
                      </form>
                      <button class="w-full text-left px-3 py-2 hover:bg-slate-50" onclick="exportOne('{{ $row->id }}')">Export Row</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </article>
        @empty
          <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-8 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 grid place-items-center mb-2">
              <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18M6 7v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
            </div>
            <h3 class="font-semibold">No borrowers found</h3>
            <p class="text-slate-500 text-sm">Try adjusting filters or add a new borrower.</p>
            <button onclick="openCreateModal()" class="btn btn-brand mt-3">Add Borrower</button>
          </div>
        @endforelse
      </section>

      {{-- Pagination (windowed + ellipsis) --}}
      <div class="bg-white rounded-xl border border-slate-200 shadow-soft px-4 py-3 flex flex-wrap gap-2 items-center justify-between text-sm">
        <div class="text-slate-600">Page <b>{{ $page }}</b> of <b>{{ $last }}</b> • Total <b>{{ $meta['total'] ?? 0 }}</b></div>
        <div class="flex flex-wrap gap-1 items-center">
          <a class="px-3 py-1 rounded border {{ $page<=1 ? 'opacity-50 pointer-events-none' : '' }}" href="{{ url('/borrowers') }}?{{ $buildQuery(['page' => max($page-1,1)]) }}" aria-label="Previous page">Prev</a>

          @foreach ($pages as $i => $p)
            @if ($i>0 && $pages[$i-1] !== $p-1)
              <span class="px-2 text-slate-400 select-none">…</span>
            @endif
            <a class="px-3 py-1 rounded border {{ $p===$page ? 'bg-brand-600 text-white border-brand-600' : '' }}" href="{{ url('/borrowers') }}?{{ $buildQuery(['page' => $p]) }}">{{ $p }}</a>
          @endforeach

          <a class="px-3 py-1 rounded border {{ $page>=$last ? 'opacity-50 pointer-events-none' : '' }}" href="{{ url('/borrowers') }}?{{ $buildQuery(['page' => min($page+1,$last)]) }}" aria-label="Next page">Next</a>
        </div>
      </div>
    </div>
  </main>

  {{-- Create Modal --}}
  <div id="createModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50" role="dialog" aria-modal="true" aria-labelledby="createTitle">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl border border-slate-100">
      <div class="p-4 border-b flex items-center justify-between">
        <h2 id="createTitle" class="text-lg font-semibold">Add Borrower</h2>
        <button class="text-2xl leading-none px-2" onclick="closeCreateModal()" aria-label="Close">&times;</button>
      </div>
      <form action="{{ route('borrowers.store') }}" method="POST" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input name="full_name" required class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input name="email" type="email" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Phone</label>
          <input name="phone" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1">Address</label>
          <input name="address" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Sex</label>
          <select name="sex" class="w-full h-10 rounded-lg bg-slate-100 px-3">
            <option value="">Select…</option>
            <option>Male</option>
            <option>Female</option>
            <option>Prefer not to say</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Civil Status</label>
          <select name="civil_status" class="w-full h-10 rounded-lg bg-slate-100 px-3">
            <option value="">Select…</option>
            <option>Single</option>
            <option>Married</option>
            <option>Separated</option>
            <option>Widowed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Occupation</label>
          <input name="occupation" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Birthday</label>
          <input name="birthday" type="date" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Monthly Income (₱)</label>
          <input name="monthly_income" type="number" step="0.01" min="0" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Reference No.</label>
          <input name="reference_no" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select name="status" class="w-full h-10 rounded-lg bg-slate-100 px-3">
            @foreach ($statuses as $s)
              <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2 flex justify-end gap-2 pt-2">
          <button type="button" onclick="closeCreateModal()" class="px-4 h-10 rounded-lg btn-outline">Cancel</button>
          <button class="px-4 h-10 rounded-lg bg-brand-600 text-white">Save</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Edit Modal --}}
  <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50" role="dialog" aria-modal="true" aria-labelledby="editTitle">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl border border-slate-100">
      <div class="p-4 border-b flex items-center justify-between">
        <h2 id="editTitle" class="text-lg font-semibold">Edit Borrower</h2>
        <button class="text-2xl leading-none px-2" onclick="closeEditModal()" aria-label="Close">&times;</button>
      </div>
      <form id="editForm" action="#" method="POST" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        @csrf
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input name="full_name" id="edit_full_name" required class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input name="email" id="edit_email" type="email" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Phone</label>
          <input name="phone" id="edit_phone" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1">Address</label>
          <input name="address" id="edit_address" class="w-full h-10 rounded-lg bg-slate-100 px-3">
        </div>
        <div class="md:col-span-2 flex justify-end gap-2 pt-2">
          <button type="button" onclick="closeEditModal()" class="px-4 h-10 rounded-lg btn-outline">Cancel</button>
          <button class="px-4 h-10 rounded-lg bg-brand-600 text-white">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Toast --}}
  <div id="toast" class="fixed bottom-4 right-4 hidden max-w-sm bg-slate-900 text-white px-4 py-3 rounded-lg shadow-2xl"></div>
@endsection

@section('scripts')
  <script>
    // Header height -> CSS var (ensures sticky thead offset is exact)
    function setHeaderOffset(){
      const hdr = document.getElementById('hdr');
      const h = hdr ? Math.round(hdr.getBoundingClientRect().height) : 64;
      document.documentElement.style.setProperty('--hdr', `${h}px`);
      hdr?.classList.toggle('is-stuck', window.scrollY > 2);
    }
    ['load','resize','scroll'].forEach(evt=> window.addEventListener(evt, setHeaderOffset, {passive:true}));

    // Sticky-col divider shadow on horizontal scroll
    const tblWrap = document.getElementById('tblWrap');
    tblWrap?.addEventListener('scroll', ()=>{
      tblWrap.classList.toggle('scrolled', tblWrap.scrollLeft > 0);
    }, {passive:true});

    // Dropdown menu
    function toggleMenu(btn){
      const wrap = btn.closest('[data-menu]');
      const expanded = wrap.classList.toggle('open');
      btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      if(expanded){
        setTimeout(()=> wrap.querySelector('.menu button, .menu a')?.focus(), 0);
      }
    }
    document.addEventListener('click', (e)=>{
      document.querySelectorAll('[data-menu].open').forEach(m=>{
        if(!m.contains(e.target)) m.classList.remove('open');
      });
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        document.querySelectorAll('[data-menu].open').forEach(m=> m.classList.remove('open'));
        closeCreateModal(); closeEditModal();
      }
    });

    // Create Modal
    function openCreateModal(){
      const m=document.getElementById('createModal');
      m.classList.remove('hidden'); m.classList.add('flex'); document.body.classList.add('overflow-hidden');
      setTimeout(()=> m.querySelector('input[name="full_name"]')?.focus(), 0);
    }
    function closeCreateModal(){
      const m=document.getElementById('createModal');
      m.classList.add('hidden'); m.classList.remove('flex'); document.body.classList.remove('overflow-hidden');
    }

    // Edit Modal
    function openEditModal(btn){
      const id = btn.dataset.id;
      const form = document.getElementById('editForm');
      form.action = "{{ url('/borrowers') }}/" + id + "/update";
      document.getElementById('edit_full_name').value = btn.dataset.name || '';
      document.getElementById('edit_email').value     = btn.dataset.email || '';
      document.getElementById('edit_phone').value     = btn.dataset.phone || '';
      document.getElementById('edit_address').value   = btn.dataset.address || '';
      const m=document.getElementById('editModal');
      m.classList.remove('hidden'); m.classList.add('flex'); document.body.classList.add('overflow-hidden');
      setTimeout(()=> document.getElementById('edit_full_name')?.focus(), 0);
    }
    function closeEditModal(){
      const m=document.getElementById('editModal');
      m.classList.add('hidden'); m.classList.remove('flex'); document.body.classList.remove('overflow-hidden');
    }

    // Bulk checks
    const checkAll = document.getElementById('checkAll');
    const bulkBar  = document.getElementById('bulkBar');
    const bulkCount= document.getElementById('bulkCount');
    function updateBulk(){
      const checks = Array.from(document.querySelectorAll('.rowCheck'));
      const sel = checks.filter(c=>c.checked);
      bulkCount.textContent = sel.length;
      bulkBar.classList.toggle('hidden', sel.length===0);
    }
    checkAll?.addEventListener('change', ()=>{
      document.querySelectorAll('.rowCheck').forEach(cb=> cb.checked = checkAll.checked);
      updateBulk();
    });
    document.addEventListener('change', (e)=>{ if(e.target.classList.contains('rowCheck')) updateBulk(); });

    // Utilities / demo
    function showToast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'), 2200); }
    function exportCsv(){ showToast('Exporting filtered borrowers to CSV…'); }
    function exportOne(id){ showToast('Exporting borrower #'+id+'…'); }
    function bulkArchive(){ showToast('Archiving selected borrowers…'); }
    function bulkDelete(){ if(confirm('Delete selected borrowers?')) showToast('Deleting…'); }
    function bulkExport(){ showToast('Exporting selection…'); }

    // Theme/Density
    function toggleTheme(){
      document.documentElement.classList.toggle('dark');
      requestAnimationFrame(setHeaderOffset);
    }
    let compact=false;
    function toggleDensity(){ compact=!compact; document.documentElement.classList.toggle('density-compact', compact); }

    // Keyboard: focus search with '/'
    document.addEventListener('keydown', (e)=>{
      if(e.key==='/' && !/input|textarea|select/i.test(document.activeElement.tagName) && !e.metaKey && !e.ctrlKey){
        e.preventDefault();
        document.querySelector('input[name="q"]')?.focus();
      }
    });

    // Respect reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.documentElement.style.scrollBehavior = 'auto';
    }
  </script>
@endsection
