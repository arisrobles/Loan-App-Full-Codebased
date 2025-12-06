@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('title', 'Settings • Role Permissions & Chart of Accounts')
@section('page-title', 'Role Permissions & Accounts')
@section('page-subtitle', 'Configure user role access and your chart of accounts')

@push('head')
  <style>
    body::before{content:"";position:fixed;inset:-20%;z-index:-1;background:
      radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.08), transparent 60%),
      radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.08), transparent 60%),
      radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.06), transparent 60%)}
    ::-webkit-scrollbar{height:10px;width:10px}
    ::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#cbd5e1,#94a3b8);border-radius:9999px}
    ::-webkit-scrollbar-track{background:#eef2f7}

    .table-sticky thead th { position: sticky; top: 0; background: #f8fafc; z-index: 10; }
    .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:9999px;font-size:11px;font-weight:600}
    .chip{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .5rem;border-radius:9999px;border:1px solid rgba(15,23,42,.08);font-size:.7rem}
    .kb{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#e2e8f0;border:1px solid #cbd5e1;border-bottom-width:2px;padding:.1rem .35rem;border-radius:.375rem}
    details > summary { cursor: pointer; }
    .row-hover tbody tr:hover { background: #f8fafc; }
    .toast { animation: fade 5s ease-out forwards; }
    @keyframes fade { 0%{opacity:1} 80%{opacity:1} 100%{opacity:0} }
    mark{ background:#fde68a; padding:.05rem .2rem; border-radius:.25rem }

    /* Dark mode polish (optional) */
    .dark section.bg-white{background:#0f172a!important;border-color:#1f2937!important}
    .dark .row-hover tbody tr:hover{background:#0b1a34}
    .dark .badge{background:#0b1f3a;color:#93c5fd}

    /* Compact density (optional) */
    .compact th,.compact td{padding:.5rem!important}
    .compact .perm-item{padding:.5rem!important}
    .compact .h-10{height:2.25rem!important}
    .compact .h-9{height:2.125rem!important}
    .compact .px-4{padding-left:.75rem!important;padding-right:.75rem!important}
    .compact .px-3{padding-left:.5rem!important;padding-right:.5rem!important}
  </style>
@endpush

@section('content')
  <div class="space-y-6">

    {{-- flash + errors --}}
    @if($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        {{ $errors->first() }}
      </div>
    @endif

    @if(session('success'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg toast">
        {{ session('success') }}
      </div>
    @endif

    @if(session('import_errors'))
      <div class="rounded-lg border border-cyan-200 bg-cyan-50 text-cyan-900 p-3 text-sm">
        <div class="font-semibold mb-1">Import warnings/errors:</div>
        <ul class="list-disc ml-5">
          @foreach(session('import_errors') as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Role Permissions --}}
    <section id="role-perms" class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
      <div class="p-6 border-b border-slate-100">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div>
            <h2 class="text-base md:text-lg font-semibold">Role Permissions</h2>
            <p class="text-sm text-slate-500 mt-1">Toggle which features a role can access.</p>
          </div>
          <div class="flex items-center gap-2 text-sm">
            <form method="GET" action="{{ route('admin.settings.index') }}" class="flex items-center gap-2">
              <label for="roleSelect" class="text-slate-600">Editing Role:</label>
              <select name="role" id="roleSelect" class="border rounded h-9 px-2 focus:ring-2 focus:ring-brand-600/30" onchange="this.form.submit()">
                @foreach($roles as $r)
                  <option value="{{ $r->slug }}" @selected(($selectedRole ?? $roles->first()->slug) === $r->slug)>{{ Str::headline($r->slug) }}</option>
                @endforeach
              </select>
            </form>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
          <div class="relative">
            <input id="permSearch" placeholder="Filter permissions…" class="h-9 pl-9 pr-3 rounded bg-slate-100 text-sm outline-none focus:ring-2 focus:ring-brand-600/30" />
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </div>
          <button type="button" class="h-9 px-3 rounded border hover:bg-slate-50 active:scale-95 transition" id="permCheckAll">Check all (filtered)</button>
          <button type="button" class="h-9 px-3 rounded border hover:bg-slate-50 active:scale-95 transition" id="permUncheckAll">Uncheck all (filtered)</button>
        </div>
      </div>

      <div class="p-6">
        <form method="POST" action="{{ route('settings.permissions.update') }}" class="space-y-4" id="permForm">
          @csrf
          <input type="hidden" name="role_slug" value="{{ $selectedRole }}"/>

          @php
            $grouped = method_exists($permissions, 'groupBy') ? $permissions->groupBy('group') : collect(['General' => $permissions]);
          @endphp

          <div class="space-y-6 max-h-[60vh] overflow-auto pr-1">
            @foreach($grouped as $groupName => $groupPerms)
              <div>
                <div class="sticky top-0 bg-white/95 backdrop-blur-xs py-2 px-1 -mx-1 border-b border-slate-100 z-10">
                  <div class="text-[11px] uppercase tracking-wide text-slate-500">{{ $groupName ?: 'General' }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 mt-2">
                  @foreach($groupPerms as $perm)
                    @php
                      $label = $perm->name ?? Str::headline($perm->key);
                      $checked = $matrix[$perm->key][$selectedRole] ?? false;
                    @endphp
                    <label class="perm-item group flex items-start gap-3 p-3 rounded-xl border hover:shadow-soft bg-white transition hover:border-slate-300 focus-within:ring-2 focus-within:ring-brand-600/20" data-text="{{ Str::lower($label.' '.($perm->description ?? '')) }}">
                      <input type="checkbox" name="permissions[{{ $perm->key }}]" value="1" @checked($checked) class="mt-1 h-4 w-4 border-slate-300 rounded focus:ring-brand-600">
                      <span>
                        <span class="font-medium">{{ $label }}</span>
                        @if(!empty($perm->description))
                          <span class="block text-xs text-slate-500">{{ $perm->description }}</span>
                        @endif
                      </span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="flex items-center gap-3">
            <button class="h-10 px-4 rounded-lg bg-brand-600 text-white hover:bg-brand-700 active:scale-[.98] shadow-card">Save Changes</button>
            <span class="text-xs text-slate-500">Unchecked permissions are saved as <code>allowed=0</code>.</span>
          </div>
        </form>
      </div>
    </section>

    {{-- Reports Matrix (preview/static) --}}
    <section id="reports" class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-base md:text-lg font-semibold">Reports</h2>
        <span class="chip bg-slate-50 text-slate-600">Preview</span>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full text-sm table-sticky row-hover">
          <thead>
            <tr class="text-left text-slate-600">
              <th class="p-3 w-[30%]">Report</th>
              @foreach($roles as $r)
                <th class="p-3 w-[16%]">{{ Str::headline($r->slug) }}</th>
              @endforeach
              <th class="p-3">Remarks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @php
              $reportRows = [
                ['Profit & Losses',       'Set of Report from - Master 1 , 2 ,3... and conso'],
                ['Balance Sheet',         'Set of Report from - Master 1 , 2 ,3... and conso'],
                ['Cashflow',              'Set of Report from - Master 1 , 2 ,3... and conso'],
                ['Personal Cash Flow',    'Set of Report from - Master 1 , 2 ,3... and conso'],
                ['Bank Statement',        'BS from each of the bank (originally) and dummy'],
              ];
            @endphp
            @foreach($reportRows as [$name, $remarks])
              <tr>
                <td class="p-3 font-medium">{{ $name }}</td>
                @foreach($roles as $r)
                  <td class="p-3">
                    @if($r->slug === 'admin')
                      <span class="badge bg-emerald-100 text-emerald-700">Yes</span>
                    @else
                      <span class="badge bg-slate-100 text-slate-700">No (Optional)</span>
                    @endif
                  </td>
                @endforeach
                <td class="p-3 text-slate-600">{{ $remarks }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    {{-- Chart of Accounts --}}
    <section id="coa" class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
      <div class="p-6 border-b border-slate-100">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <h2 class="text-base md:text-lg font-semibold">Chart of Accounts</h2>
            <p class="text-xs text-slate-500">Manage master accounts. Use the search, sort by columns, or export/import.</p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
              <input id="coaSearch" class="h-10 pl-9 pr-3 rounded-lg bg-slate-100 text-sm outline-none focus:ring-2 focus:ring-brand-600/30" placeholder="Search code / name / desc ( / to focus )" />
              <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <button id="exportCsvBtn" type="button" class="h-10 px-3 rounded-lg bg-slate-100 border hover:bg-slate-50 active:scale-95 transition">Export CSV (Client)</button>
            <a href="{{ route('coa.export') }}" class="h-10 px-3 rounded-lg bg-brand-600 text-white hover:bg-brand-700 active:scale-[.98]">Export CSV (Server)</a>
            <form method="POST" action="{{ route('coa.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
              @csrf
              <input type="file" name="file" accept=".csv" class="text-xs" required>
              <label class="text-xs flex items-center gap-1"><input type="checkbox" name="dry_run" value="1" class="scale-110"> Dry run</label>
              <button class="h-10 px-3 rounded-lg border hover:bg-slate-50 active:scale-95 transition" type="submit">Import</button>
            </form>
          </div>
        </div>
      </div>

      {{-- Quick Create --}}
      <form method="POST" action="{{ route('chart-of-accounts.store') }}" class="m-4 grid grid-cols-1 md:grid-cols-6 gap-3 bg-white p-4 rounded-xl border shadow-soft">
        @csrf
        <input name="code" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Code" required>
        <input name="name" class="border rounded p-2 md:col-span-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Name" required>
        <select name="report" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
          <option>Balance Sheets</option>
          <option>Profit and Losses</option>
        </select>
        <select name="group_account" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
          <option>Assets</option><option>Liabilities</option><option>Equity</option>
          <option>Revenue (Income)</option><option>Expense (COGS)</option><option>Expenses</option>
        </select>
        <select name="normal_balance" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20">
          <option value="">Normal Balance…</option>
          <option>Debit</option><option>Credit</option>
        </select>
        <input name="description" class="border rounded p-2 md:col-span-3 focus:ring-2 focus:ring-brand-600/20" placeholder="Description (optional)">
        <input type="number" name="sort_order" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Sort">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="focus:ring-brand-600"> Active</label>
        <button class="h-10 px-4 rounded-lg bg-brand-600 text-white hover:bg-brand-700 active:scale-[.98] md:col-span-2" type="submit">Add Account</button>
      </form>

      <div class="p-4">
        <div class="overflow-auto rounded-xl border border-slate-200 max-h-[70vh]">
          <table class="min-w-[1280px] w-full text-sm table-sticky row-hover" id="coaTable">
            <thead>
              <tr class="text-left text-slate-600 select-none">
                <th class="p-3 w-28 cursor-pointer" data-sort="0">Account Code ▾</th>
                <th class="p-3 w-64 cursor-pointer" data-sort="1">Account Name</th>
                <th class="p-3 w-80">Description</th>
                <th class="p-3 w-40 cursor-pointer" data-sort="3">Reports</th>
                <th class="p-3 w-36 cursor-pointer" data-sort="4">Group Accounts</th>
                <th class="p-3 w-28 cursor-pointer" data-sort="5">Normal Balance</th>
                <th class="p-3 w-24">Debit</th>
                <th class="p-3 w-24">Credit</th>
                <th class="p-3 w-40">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @foreach($coa as $acc)
                @php
                  $code    = $acc->code ?? '';
                  $name    = $acc->name ?? '';
                  $desc    = $acc->description ?? '';
                  $report  = $acc->report ?? ($acc->is_pl ?? false ? 'Profit and Losses' : 'Balance Sheets');
                  $group   = $acc->group_account ?? '';
                  $normal  = $acc->normal_balance ?? 'Debit';
                  $debitChange  = $acc->debit_effect  ?? ($normal === 'Debit' ? 'Increase' : 'Decrease');
                  $creditChange = $acc->credit_effect ?? ($normal === 'Debit' ? 'Decrease' : 'Increase');
                @endphp
                <tr>
                  <td class="p-3 font-mono">{{ $code }}</td>
                  <td class="p-3 font-medium">{{ $name }} @if(!$acc->is_active)<span class="ml-2 chip bg-slate-50 text-slate-600">Inactive</span>@endif</td>
                  <td class="p-3 text-slate-600">{{ $desc }}</td>
                  <td class="p-3">{{ $report }}</td>
                  <td class="p-3">{{ $group }}</td>
                  <td class="p-3">{{ $normal }}</td>
                  <td class="p-3">{{ $debitChange }}</td>
                  <td class="p-3">{{ $creditChange }}</td>
                  <td class="p-3">
                    <div class="flex flex-wrap gap-2 items-center">
                      @if($acc->is_active)
                        <form method="POST" action="{{ route('chart-of-accounts.update', $acc) }}" class="inline">
                          @csrf @method('PATCH')
                          <input type="hidden" name="is_active" value="0">
                          <button type="submit" class="text-xs px-2 py-1 border rounded hover:bg-slate-50 active:scale-95">Archive</button>
                        </form>
                      @else
                        <form method="POST" action="{{ route('chart-of-accounts.update', $acc) }}" class="inline">
                          @csrf @method('PATCH')
                          <input type="hidden" name="is_active" value="1">
                          <button type="submit" class="text-xs px-2 py-1 border rounded hover:bg-slate-50 active:scale-95">Activate</button>
                        </form>
                      @endif

                      <details class="inline-block" ontoggle="this.querySelector('summary')?.setAttribute('aria-expanded', this.open ? 'true' : 'false')">
                        <summary class="text-xs px-2 py-1 border rounded hover:bg-slate-50" role="button" aria-expanded="false">Edit</summary>
                        <div class="mt-2 p-3 border rounded bg-slate-50">
                          <form method="POST" action="{{ route('chart-of-accounts.update', $acc) }}" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            @csrf @method('PATCH')
                            <input name="code" value="{{ $acc->code }}" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Code" required>
                            <input name="name" value="{{ $acc->name }}" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Name" required>
                            <select name="report" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
                              <option @selected($acc->report==='Balance Sheets')>Balance Sheets</option>
                              <option @selected($acc->report==='Profit and Losses')>Profit and Losses</option>
                            </select>
                            <select name="group_account" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
                              @foreach(['Assets','Liabilities','Equity','Revenue (Income)','Expense (COGS)','Expenses'] as $opt)
                                <option @selected($acc->group_account===$opt)>{{ $opt }}</option>
                              @endforeach
                            </select>
                            <select name="normal_balance" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20">
                              <option value="">Normal Balance…</option>
                              <option @selected($acc->normal_balance==='Debit')>Debit</option>
                              <option @selected($acc->normal_balance==='Credit')>Credit</option>
                            </select>
                            <input name="description" value="{{ $acc->description }}" class="border rounded p-2 md:col-span-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Description">
                            <input type="number" name="sort_order" value="{{ $acc->sort_order }}" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" placeholder="Sort">
                            <label class="flex items-center gap-2 text-sm md:col-span-3"><input type="checkbox" name="is_active" value="1" @checked($acc->is_active) class="focus:ring-brand-600"> Active</label>
                            <div class="md:col-span-3 flex gap-2">
                              <button class="h-9 px-3 rounded bg-brand-600 text-white hover:bg-brand-700 active:scale-[.98]" type="submit">Save</button>
                            </div>
                          </form>
                        </div>
                      </details>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Merge tool --}}
        <form method="POST" action="{{ route('chart-of-accounts.merge') }}" class="mt-4 bg-white p-4 rounded-xl border shadow-soft flex flex-wrap gap-3 items-center">
          @csrf
          <div class="font-medium mr-2">Merge accounts:</div>
          <div>
            <label class="text-xs block" for="mergeSource">Source (will be archived)</label>
            <select id="mergeSource" name="source_id" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
              @foreach($coa as $a)
                <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="text-slate-400">→</div>
          <div>
            <label class="text-xs block" for="mergeTarget">Target (kept)</label>
            <select id="mergeTarget" name="target_id" class="border rounded p-2 focus:ring-2 focus:ring-brand-600/20" required>
              @foreach($coa as $a)
                <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
              @endforeach
            </select>
          </div>
          <button class="h-10 px-4 rounded-lg bg-brand-600 text-white hover:bg-brand-700 active:scale-[.98]" onclick="return confirm('Merge selected accounts? This will move lines and archive the source.')">Merge</button>
          <p class="text-xs text-slate-500 w-full">Only allowed when group & normal balance match. Journal lines/balances are moved to the target if those tables exist.</p>
        </form>

        <p class="text-xs text-slate-500 mt-3">
          Tip: “Normal Balance” indicates the side (Debit/Credit) that increases the account. The “Debit/Credit” columns show the direction of change for each entry.
        </p>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
  // Smooth in-page anchor scroll
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',e=>{
      const id=a.getAttribute('href');
      if(id.length>1){ e.preventDefault(); document.querySelector(id)?.scrollIntoView({behavior:'smooth',block:'start'}); }
    })
  });

  // Sidebar toggle helper for mobile (works with default layout sidebar id="sidebar")
  window.toggleSidebar = function(){
    var sb = document.getElementById('sidebar');
    if(!sb) return;
    var hidden = sb.classList.contains('-translate-x-full');
    sb.classList.toggle('-translate-x-full', !hidden);
    sb.classList.toggle('translate-x-0', hidden);
  }

  // Escape + highlight helpers
  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function highlightHtml(text, q){
    if(!q) return escapeHtml(text||'');
    const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')','ig');
    return escapeHtml(text||'').replace(re, '<mark>$1</mark>');
  }

  // ===== Permission search & bulk toggle =====
  (function(){
    const input = document.getElementById('permSearch');
    const items = Array.from(document.querySelectorAll('.perm-item'));
    const checkAll = document.getElementById('permCheckAll');
    const uncheckAll = document.getElementById('permUncheckAll');
    if (!input) return;

    const highlight = (el, q) => {
      const span = el.querySelector('span > span.font-medium');
      if (!span) return;
      const raw  = span.textContent || '';
      span.innerHTML = highlightHtml(raw, q);
    };

    function filter(){
      const q = (input.value || '').trim().toLowerCase();
      items.forEach(it=>{
        const hay = (it.getAttribute('data-text') || '').toLowerCase();
        const show = hay.includes(q);
        it.style.display = show ? '' : 'none';
        highlight(it, q);
      });
    }

    input.addEventListener('input', () => {
      window.clearTimeout(input._t);
      input._t = window.setTimeout(filter, 150);
    });

    if (checkAll) checkAll.addEventListener('click', (e) => {
      e.preventDefault();
      items.forEach(it=>{ if (it.style.display !== 'none') it.querySelector('input[type="checkbox"]').checked = true; });
    });
    if (uncheckAll) uncheckAll.addEventListener('click', (e) => {
      e.preventDefault();
      items.forEach(it=>{ if (it.style.display !== 'none') it.querySelector('input[type="checkbox"]').checked = false; });
    });
  })();

  // ===== COA search with safe highlight =====
  (function(){
    const input = document.getElementById('coaSearch');
    const table = document.getElementById('coaTable');
    if (!input || !table) return;

    const rows  = Array.from(table.querySelectorAll('tbody tr'));
    let timer = null;

    function stripMarks(td){
      td.querySelectorAll('mark').forEach(m=>{ const t=document.createTextNode(m.textContent); m.replaceWith(t); });
    }

    function filter(){
      const q = (input.value || '').trim().toLowerCase();
      rows.forEach(r=>{
        Array.from(r.querySelectorAll('td')).forEach(td=> stripMarks(td));
        const text = (r.innerText || '').toLowerCase();
        const show = text.includes(q);
        r.style.display = show ? '' : 'none';
        if (q && show) {
          r.querySelectorAll('td').forEach(td=>{
            td.innerHTML = highlightHtml(td.textContent || '', q);
          });
        }
      });
    }

    input.addEventListener('input', function(){
      clearTimeout(timer);
      timer = setTimeout(filter, 150);
    });

    document.addEventListener('keydown', function(e){
      if (e.key === '/' && document.activeElement !== input) {
        e.preventDefault();
        input.focus();
        input.select();
      }
    });
  })();

  // ===== COA CSV export (client-side) =====
  (function(){
    const btn = document.getElementById('exportCsvBtn');
    const table = document.getElementById('coaTable');
    if (!btn || !table) return;

    btn.addEventListener('click', function(e){
      e.preventDefault();
      const rows = table.querySelectorAll('tr');
      const data = [];
      rows.forEach((tr, idx)=>{
        const cells = tr.querySelectorAll(idx===0 ? 'th' : 'td');
        const row = [];
        cells.forEach(td=>{
          let text = (td.innerText || '').replace(/\s+/g,' ').trim();
          text = '"' + text.replace(/"/g,'""') + '"';
          row.push(text);
        });
        if (row.length) data.push(row.join(','));
      });
      const csv = data.join('\n');
      const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
      const url  = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'chart_of_accounts.csv';
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
    });
  })();

  // ===== COA column sort (numeric-aware) =====
  (function(){
    const table = document.getElementById('coaTable');
    if (!table) return;
    const headers = table.querySelectorAll('th[data-sort]');

    headers.forEach(h => {
      let asc = true;
      h.addEventListener('click', () => {
        const idx = parseInt(h.getAttribute('data-sort'), 10);
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows).filter(r => r.style.display !== 'none');

        rows.sort((a,b) => {
          const ta = (a.cells[idx]?.textContent || '').trim();
          const tb = (b.cells[idx]?.textContent || '').trim();
          const numeric = /^-?\d+(\.\d+)?$/;
          const na = numeric.test(ta) ? Number(ta) : null;
          const nb = numeric.test(tb) ? Number(tb) : null;
          const cmp = (na!==null && nb!==null)
            ? (na - nb)
            : ta.localeCompare(tb, undefined, {numeric:true, sensitivity:'base'});
          return asc ? cmp : -cmp;
        });

        rows.forEach(r => tbody.appendChild(r));
        asc = !asc;
        headers.forEach(x=> x.textContent = x.textContent.replace(/[▾▴]/g,''));
        h.textContent = h.textContent + (asc ? ' ▾' : ' ▴');
      });
    });
  })();

  // ===== Theme & Density toggles (if you add toggles to the header) =====
  (function(){
    const root = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const densitySel = document.getElementById('density');
    const savedTheme = localStorage.getItem('theme') || 'light';
    const savedDensity = localStorage.getItem('density') || 'comfortable';

    function applyTheme(t){
      root.classList.toggle('dark', t==='dark');
      if(themeBtn){ themeBtn.textContent = 'Theme: ' + (t==='dark' ? 'Dark' : 'Light'); }
    }
    function applyDensity(d){
      root.classList.toggle('compact', d==='compact');
      if(densitySel){ densitySel.value = d; }
    }
    applyTheme(savedTheme); applyDensity(savedDensity);
    window.toggleTheme = function(){
      const next = root.classList.contains('dark') ? 'light' : 'dark';
      localStorage.setItem('theme', next); applyTheme(next);
    }
    window.setDensity = function(v){ localStorage.setItem('density', v); applyDensity(v); }
  })();
</script>
@endpush
