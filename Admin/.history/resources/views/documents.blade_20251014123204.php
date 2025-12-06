@extends('layouts.app')

@section('title', 'Borrower Document Management — MasterFunds')
@section('page-title', 'Document Management')

{{-- Optional toolbar (right side of the top bar) --}}
@section('toolbar')
  <select id="density" class="h-9 px-2 rounded border text-sm hidden md:block" onchange="setDensity(this.value)">
    <option value="comfortable">Comfortable</option>
    <option value="compact">Compact</option>
  </select>
  <button id="themeBtn" class="h-9 px-3 rounded border text-sm" type="button" onclick="toggleTheme()">Theme: Light</button>
@endsection

@push('head')
  <style>
    body::before{content:"";position:fixed;inset:-20%;z-index:-1;background:
      radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.08), transparent 60%),
      radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.08), transparent 60%),
      radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.06), transparent 60%)}

    .table-sticky thead th{position:sticky;top:0;background:#f8fafc;z-index:10}
    .btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 12px;border-radius:10px;font-size:.875rem}
    .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
    .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e2e8f0}
    .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;font-size:12px;font-weight:600}
    .kbd{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f1f5f9;border:1px solid #e2e8f0;border-bottom-width:2px;padding:.1rem .35rem;border-radius:.375rem}
    .actions-cell{ position:relative; overflow:visible }
    details > summary::-webkit-details-marker{display:none}
    details[open] > summary::after{ content:""; position:fixed; inset:0; }

    /* Scrollbars */
    ::-webkit-scrollbar{height:10px;width:10px}
    ::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#cbd5e1,#94a3b8);border-radius:9999px}
    ::-webkit-scrollbar-track{background:#eef2f7}

    /* Dark mode helpers (layout toggles .dark on <html>) */
    .dark section.bg-white{background:#0f172a!important;border-color:#1f2937!important}
    .dark .badge{background:#0b1f3a;color:#93c5fd}
    .dark input, .dark select, .dark textarea, .dark button{border-color:#1f2937}

    /* Compact density */
    .compact th,.compact td{padding:.5rem!important}
    .compact .h-10{height:2.25rem!important}
    .compact .h-9{height:2.125rem!important}
    .compact .px-4{padding-left:.75rem!important;padding-right:.75rem!important}
    .compact .px-3{padding-left:.5rem!important;padding-right:.5rem!important}
  </style>
@endpush

@section('content')
  {{-- Filters / Actions (sticky just below header) --}}
  <div class="bg-white/90 backdrop-blur border border-slate-200 rounded-xl mb-6">
    <div class="px-4 md:px-6 py-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
      <div class="sm:col-span-2">
        <label class="sr-only" for="q">Search</label>
        <div class="relative">
          <input id="q" class="h-10 w-full pl-9 pr-3 rounded-lg bg-slate-100 text-sm outline-none"
                 placeholder="Search borrower / filename / remarks (Press / to focus)"/>
          <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
          </svg>
        </div>
      </div>
      <select class="h-10 rounded-lg bg-slate-100 text-sm">
        <option value="">All Doc Types</option>
        <option>Government ID</option>
        <option>Proof of Address</option>
        <option>Income Proof</option>
        <option>Photo</option>
      </select>
      <select class="h-10 rounded-lg bg-slate-100 text-sm">
        <option value="">All Statuses</option>
        <option>Verified</option>
        <option>Pending</option>
        <option>Rejected</option>
      </select>
      <div class="flex gap-2">
        <button class="btn btn-quiet w-full">Reset</button>
        <button class="btn btn-primary w-full">Upload</button>
      </div>
    </div>
  </div>

  {{-- Intro --}}
  <section class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
    <h2 class="text-base md:text-lg font-semibold">Borrower Document Storage</h2>
    <p class="text-slate-600 text-sm mt-1">View and manage uploaded borrower documents such as IDs and proofs of address.</p>
  </section>

  {{-- Table --}}
  <section class="bg-white p-6 rounded-2xl shadow-card border border-slate-100 mt-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base md:text-lg font-semibold">Borrower Documents</h2>
      <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
        <span>Shortcuts: <span class="kbd">/</span> search • <span class="kbd">Tab</span> move • <span class="kbd">Enter</span> open</span>
      </div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-slate-100">
      <table class="min-w-[1000px] w-full text-sm table-sticky">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="p-3 text-left">Borrower Name</th>
            <th class="p-3 text-left">Document Type</th>
            <th class="p-3 text-left">File Name</th>
            <th class="p-3 text-left">Upload Date</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Remarks</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr class="hover:bg-slate-50">
            <td class="p-3 font-medium">Juan Dela Cruz</td>
            <td class="p-3">Government ID</td>
            <td class="p-3">juan_id_front.jpg</td>
            <td class="p-3">Aug 05, 2025</td>
            <td class="p-3"><span class="badge bg-emerald-100 text-emerald-700">Verified</span></td>
            <td class="p-3 text-slate-600">ID is clear and valid</td>
            <td class="p-3 actions-cell">
              <details class="relative">
                <summary class="list-none cursor-pointer px-3 h-9 rounded border border-slate-200 hover:bg-slate-50 inline-flex items-center">Actions ▾</summary>
                <div class="absolute z-20 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-card p-2">
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">View</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">Download</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-rose-700">Reject</button>
                </div>
              </details>
            </td>
          </tr>
          <tr class="hover:bg-slate-50">
            <td class="p-3 font-medium">Maria Santos</td>
            <td class="p-3">Proof of Address</td>
            <td class="p-3">maria_electric_bill.pdf</td>
            <td class="p-3">Aug 04, 2025</td>
            <td class="p-3"><span class="badge bg-amber-100 text-amber-700">Pending</span></td>
            <td class="p-3 text-slate-600">Awaiting verification</td>
            <td class="p-3 actions-cell">
              <details class="relative">
                <summary class="list-none cursor-pointer px-3 h-9 rounded border border-slate-200 hover:bg-slate-50 inline-flex items-center">Actions ▾</summary>
                <div class="absolute z-20 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-card p-2">
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">View</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">Download</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-emerald-700">Verify</button>
                </div>
              </details>
            </td>
          </tr>
          <tr class="hover:bg-slate-50">
            <td class="p-3 font-medium">Carlos Garcia</td>
            <td class="p-3">Government ID</td>
            <td class="p-3">carlos_passport.png</td>
            <td class="p-3">Aug 03, 2025</td>
            <td class="p-3"><span class="badge bg-rose-100 text-rose-700">Rejected</span></td>
            <td class="p-3 text-slate-600">Photo is blurry</td>
            <td class="p-3 actions-cell">
              <details class="relative">
                <summary class="list-none cursor-pointer px-3 h-9 rounded border border-slate-200 hover:bg-slate-50 inline-flex items-center">Actions ▾</summary>
                <div class="absolute z-20 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-card p-2">
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">View</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50">Download</button>
                  <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-amber-700">Request Reupload</button>
                </div>
              </details>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  {{-- Upload widget --}}
  <section class="bg-white p-6 rounded-2xl shadow-card border border-slate-100 mt-6">
    <h3 class="text-base md:text-lg font-semibold">Upload New Document</h3>
    <p class="text-slate-600 text-sm mt-1">Drag and drop files here, or click browse. Accepted: JPG, PNG, PDF (max 10MB).</p>
    <div id="dropzone" class="mt-4 border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:border-slate-400 transition">
      <input id="fileInput" type="file" class="hidden" multiple accept=".jpg,.jpeg,.png,.pdf" />
      <div class="space-y-2">
        <div class="text-3xl">📄</div>
        <div class="font-medium">Drop files to upload</div>
        <div class="text-sm text-slate-500">or</div>
        <button id="browseBtn" class="btn btn-primary">Browse Files</button>
      </div>
    </div>
    <ul id="queue" class="mt-4 space-y-2 text-sm"></ul>
  </section>
@endsection

@push('scripts')
<script>
  // Theme & Density — shared with layout via localStorage
  (function(){
    const root = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const densitySel = document.getElementById('density');
    const savedTheme = localStorage.getItem('theme') || 'light';
    const savedDensity = localStorage.getItem('density') || 'comfortable';
    function applyTheme(t){ root.classList.toggle('dark', t==='dark'); if(themeBtn) themeBtn.textContent = 'Theme: ' + (t==='dark' ? 'Dark' : 'Light'); }
    function applyDensity(d){ root.classList.toggle('compact', d==='compact'); if(densitySel) densitySel.value = d; }
    applyTheme(savedTheme); applyDensity(savedDensity);
    window.toggleTheme = function(){ const next = root.classList.contains('dark') ? 'light' : 'dark'; localStorage.setItem('theme', next); applyTheme(next); }
    window.setDensity = function(v){ localStorage.setItem('density', v); applyDensity(v); }
  })();

  // Search keyboard shortcut
  (function(){
    const q = document.getElementById('q');
    if(!q) return;
    document.addEventListener('keydown', function(e){
      if (e.key === '/' && document.activeElement !== q) { e.preventDefault(); q.focus(); q.select(); }
    });
  })();

  // Close any open <details> menus when clicking outside
  document.addEventListener('click', function(e){
    const open = document.querySelector('details[open]');
    if (!open) return;
    if (!open.contains(e.target)) open.removeAttribute('open');
  }, true);

  // Upload widget (demo)
  (function(){
    const dz = document.getElementById('dropzone');
    const input = document.getElementById('fileInput');
    const browse = document.getElementById('browseBtn');
    const queue = document.getElementById('queue');
    if(!dz || !input || !browse || !queue) return;
    browse.addEventListener('click', () => input.click());
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('border-brand-600'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('border-brand-600'));
    dz.addEventListener('drop', e => { e.preventDefault(); dz.classList.remove('border-brand-600'); handleFiles(e.dataTransfer.files); });
    input.addEventListener('change', () => handleFiles(input.files));
    function handleFiles(files){
      Array.from(files).forEach(f => {
        const li = document.createElement('li');
        li.className = 'p-3 rounded-lg border border-slate-200 flex items-center justify-between';
        li.innerHTML = `<span>${f.name} <span class="text-slate-500">(${Math.ceil(f.size/1024)} KB)</span></span>`+
                       `<span class="text-xs px-2 py-1 rounded bg-slate-100">Queued</span>`;
        queue.appendChild(li);
      });
    }
  })();
</script>
@endpush
