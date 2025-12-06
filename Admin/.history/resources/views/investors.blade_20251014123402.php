@extends('layouts.app')

@section('title','MasterFunds — Investor Relations')
@section('page-title','Investor Relations')
@section('page-subtitle','Growth, metrics, and materials for partners')

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
  .btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 12px;border-radius:10px;font-size:.875rem}
  .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
  .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e2e8f0}
  .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;font-size:12px;font-weight:600}
  .kbd{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;background:#f1f5f9;border:1px solid #e2e8f0;border-bottom-width:2px;padding:.1rem .35rem;border-radius:.375rem}
  /* Dark / Compact helpers (layout toggles classes on <html>) */
  .dark section.bg-white{background:#0f172a!important;border-color:#1f2937!important}
  .compact .h-10{height:2.25rem!important}.compact .h-9{height:2.125rem!important}
</style>
@endpush

@section('content')
  <div class="space-y-6">

    {{-- OVERVIEW --}}
    <section id="overview" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <div class="grid lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 space-y-3">
          <span class="badge bg-blue-50 text-blue-700">Pre-Seed / Seed</span>
          <h2 class="text-xl md:text-2xl font-semibold">Funding inclusive growth for emerging borrowers</h2>
          <p class="text-slate-600 text-sm leading-relaxed">
            MasterFunds is a lending operations platform that streamlines borrower onboarding, risk assessment,
            and repayments for non-bank lenders. We help partners scale responsibly with verified KYC, automated
            collections, and real-time risk signals.
          </p>
          <div class="flex flex-wrap gap-2 pt-2">
            <span class="badge bg-emerald-100 text-emerald-700">KYC Automation</span>
            <span class="badge bg-indigo-100 text-indigo-700">Risk Scoring</span>
            <span class="badge bg-amber-100 text-amber-700">Collections</span>
            <span class="badge bg-cyan-100 text-cyan-700">Analytics</span>
          </div>
          <div class="flex gap-2 pt-3">
            <a href="#dataroom" class="btn btn-primary">View Data Room</a>
            <a href="#contact" class="btn btn-quiet">Request Deck</a>
          </div>
        </div>
        <!-- KPIs -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 gap-3">
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500">Active Borrowers</div>
            <div class="text-2xl font-semibold">12,480</div>
            <div class="text-emerald-600 text-xs">▲ 9.8% MoM</div>
          </div>
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500">Loan Volume (TTM)</div>
            <div class="text-2xl font-semibold">₱1.86B</div>
            <div class="text-emerald-600 text-xs">▲ 15.4% QoQ</div>
          </div>
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500">Net Take Rate</div>
            <div class="text-2xl font-semibold">3.4%</div>
            <div class="text-slate-500 text-xs">Stable</div>
          </div>
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500">90+ DPD</div>
            <div class="text-2xl font-semibold">2.1%</div>
            <div class="text-emerald-600 text-xs">▼ -30 bps</div>
          </div>
        </div>
      </div>
    </section>

    {{-- TRACTION --}}
    <section id="traction" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base md:text-lg font-semibold">Traction</h3>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
          <span>Shortcuts: <span class="kbd">G</span><span class="kbd">T</span> go to Traction</span>
        </div>
      </div>
      <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 p-4 rounded-xl border border-slate-100 bg-slate-50">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium">Monthly Disbursed (₱M)</span>
            <span class="text-xs text-slate-500">Last 12 months</span>
          </div>
          <!-- simple inline area chart -->
          <svg viewBox="0 0 600 220" class="w-full h-48">
            <defs>
              <linearGradient id="grad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#93c5fd" stop-opacity="0.7"/>
                <stop offset="100%" stop-color="#93c5fd" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <path d="M0,180 C60,160 120,150 180,120 C240,100 300,110 360,90 C420,80 480,60 540,70 L540,220 L0,220 Z" fill="url(#grad)"/>
            <path d="M0,180 C60,160 120,150 180,120 C240,100 300,110 360,90 C420,80 480,60 540,70" stroke="#2563eb" stroke-width="3" fill="none" stroke-linecap="round"/>
            <g fill="#2563eb"><circle cx="180" cy="120" r="4"/><circle cx="360" cy="90" r="4"/><circle cx="540" cy="70" r="4"/></g>
          </svg>
          <div class="flex gap-6 text-xs text-slate-600 mt-2">
            <div>Peak: <span class="font-semibold">₱210M</span></div>
            <div>Avg: <span class="font-semibold">₱155M</span></div>
            <div>Growth: <span class="font-semibold text-emerald-700">+74% YoY</span></div>
          </div>
        </div>
        <div class="space-y-3">
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Unit Economics</div>
            <ul class="text-sm space-y-1">
              <li class="flex justify-between"><span>CAC (weighted)</span><span class="font-medium">₱370</span></li>
              <li class="flex justify-between"><span>LTV (12M)</span><span class="font-medium">₱3,950</span></li>
              <li class="flex justify-between"><span>LTV/CAC</span><span class="font-medium">10.7×</span></li>
              <li class="flex justify-between"><span>Payback</span><span class="font-medium">2.8 mo</span></li>
            </ul>
          </div>
          <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="text-xs text-slate-500 mb-1">Partner Mix</div>
            <ul class="text-sm space-y-1">
              <li class="flex justify-between"><span>Top 3 partners</span><span class="font-medium">41%</span></li>
              <li class="flex justify-between"><span>SME lenders</span><span class="font-medium">52%</span></li>
              <li class="flex justify-between"><span>NGOs / MFIs</span><span class="font-medium">23%</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    {{-- FINANCIALS --}}
    <section id="financials" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base md:text-lg font-semibold">Financial Snapshot</h3>
        <span class="text-xs text-slate-500">Last updated: <span class="font-medium">Aug 2025</span></span>
      </div>
      <div class="overflow-x-auto rounded-xl border border-slate-100">
        <table class="min-w-[880px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="p-3 text-left">Metric</th>
              <th class="p-3 text-right">Q2 2025</th>
              <th class="p-3 text-right">Q1 2025</th>
              <th class="p-3 text-right">Δ QoQ</th>
              <th class="p-3 text-right">Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr class="hover:bg-slate-50">
              <td class="p-3 font-medium">Revenue</td>
              <td class="p-3 text-right">₱18.2M</td>
              <td class="p-3 text-right">₱15.7M</td>
              <td class="p-3 text-right text-emerald-700">+16.0%</td>
              <td class="p-3 text-right text-slate-600">Take rate stable</td>
            </tr>
            <tr class="hover:bg-slate-50">
              <td class="p-3 font-medium">Operating Margin</td>
              <td class="p-3 text-right">21%</td>
              <td class="p-3 text-right">17%</td>
              <td class="p-3 text-right text-emerald-700">+400 bps</td>
              <td class="p-3 text-right text-slate-600">Scale effects</td>
            </tr>
            <tr class="hover:bg-slate-50">
              <td class="p-3 font-medium">GMV</td>
              <td class="p-3 text-right">₱528M</td>
              <td class="p-3 text-right">₱462M</td>
              <td class="p-3 text-right text-emerald-700">+14.3%</td>
              <td class="p-3 text-right text-slate-600">+ partners</td>
            </tr>
            <tr class="hover:bg-slate-50">
              <td class="p-3 font-medium">Collections Success</td>
              <td class="p-3 text-right">97.2%</td>
              <td class="p-3 text-right">96.6%</td>
              <td class="p-3 text-right text-emerald-700">+60 bps</td>
              <td class="p-3 text-right text-slate-600">Automation</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    {{-- TEAM --}}
    <section id="team" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <h3 class="text-base md:text-lg font-semibold mb-4">Leadership</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
          ['img'=>1,  'name'=>'Alex Reyes','role'=>'CEO & Co-Founder','tags'=>['Strategy','Risk'],
           'desc'=>'Ex-lending ops at fintech unicorn; built credit models for 1M+ borrowers.'],
          ['img'=>5,  'name'=>'Jamie Cruz','role'=>'CTO & Co-Founder','tags'=>['Infra','Data'],
           'desc'=>'Led core payments at top PH e-commerce; 12+ yrs full-stack + data.'],
          ['img'=>8,  'name'=>'Rina Santos','role'=>'COO','tags'=>['Ops','Quality'],
           'desc'=>'Scaled field collections to 300+ agents; Six Sigma Black Belt.'],
          ['img'=>11, 'name'=>'Mark Lim','role'=>'CFO','tags'=>['Finance','Risk'],
           'desc'=>'Ex-IB; structured debt lines with local banks and DFIs.'],
        ] as $m)
        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
          <div class="flex items-center gap-3">
            <img class="w-12 h-12 rounded-full" src="https://i.pravatar.cc/80?img={{ $m['img'] }}" alt="{{ $m['name'] }}"/>
            <div>
              <div class="font-medium">{{ $m['name'] }}</div>
              <div class="text-xs text-slate-500">{{ $m['role'] }}</div>
            </div>
          </div>
          <p class="text-sm text-slate-600 mt-3">{{ $m['desc'] }}</p>
          <div class="mt-3 flex gap-2">
            @foreach ($m['tags'] as $t)
              <span class="badge bg-slate-200 text-slate-700">{{ $t }}</span>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </section>

    {{-- ROADMAP --}}
    <section id="roadmap" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <h3 class="text-base md:text-lg font-semibold mb-4">Milestones</h3>
      <ol class="relative border-s border-slate-200 pl-6 space-y-6">
        <li>
          <span class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>
          <div class="text-sm"><span class="font-semibold">Q2 2025 —</span> Launched automated KYC with ID + liveness</div>
        </li>
        <li>
          <span class="absolute -left-[9px] w-4 h-4 rounded-full bg-blue-500 ring-4 ring-blue-100"></span>
          <div class="text-sm"><span class="font-semibold">Q3 2025 —</span> Collections orchestration v2 (SMS/Auto-debit)</div>
        </li>
        <li>
          <span class="absolute -left-[9px] w-4 h-4 rounded-full bg-amber-500 ring-4 ring-amber-100"></span>
          <div class="text-sm"><span class="font-semibold">Q4 2025 —</span> Risk score API for partners (beta)</div>
        </li>
      </ol>
    </section>

    {{-- UPDATES --}}
    <section id="updates" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base md:text-lg font-semibold">Investor Updates</h3>
        <button class="btn btn-quiet">Subscribe</button>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
        @foreach ([
          ['date'=>'Aug 2025','title'=>'Signed 2 new SME lenders','text'=>'Adds ₱80M/mo potential GMV; early cohorts trending above plan.'],
          ['date'=>'Jul 2025','title'=>'Collections uplift +60 bps','text'=>'SMS sequencing + intelligent retries improved success to 97.2%.'],
          ['date'=>'Jun 2025','title'=>'Risk score beta','text'=>'Model trained on 300k+ loans; 90+ DPD reduced by 30 bps.'],
        ] as $u)
        <article class="p-4 rounded-xl border border-slate-100 bg-slate-50">
          <div class="text-xs text-slate-500">{{ $u['date'] }}</div>
          <h4 class="font-medium mt-1">{{ $u['title'] }}</h4>
          <p class="text-slate-600 mt-1">{{ $u['text'] }}</p>
        </article>
        @endforeach
      </div>
    </section>

    {{-- DATA ROOM --}}
    <section id="dataroom" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <div class="grid md:grid-cols-2 gap-6 items-center">
        <div>
          <h3 class="text-base md:text-lg font-semibold">Data Room & Materials</h3>
          <p class="text-slate-600 text-sm mt-1">Request access to our investor data room, including our deck, product demo, financial model, and security docs.</p>
          <ul class="text-sm mt-3 space-y-1 list-disc pl-5 text-slate-700">
            <li>Investor Deck (PDF)</li>
            <li>Financial Model (XLSX)</li>
            <li>Product Demo (Video)</li>
            <li>Security & Compliance</li>
          </ul>
        </div>
        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
          <div class="grid sm:grid-cols-2 gap-2">
            <button class="btn btn-primary">Request Access</button>
            <button class="btn btn-quiet">Download One-Pager</button>
            <button class="btn btn-quiet">View Product Demo</button>
            <a href="#contact" class="btn btn-quiet">Book a Call</a>
          </div>
          <p class="text-xs text-slate-500 mt-3">We aim to respond within 24 hours.</p>
        </div>
      </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <h3 class="text-base md:text-lg font-semibold mb-2">FAQ</h3>
      <div class="divide-y divide-slate-100 text-sm">
        <details class="py-3 group">
          <summary class="cursor-pointer font-medium flex items-center justify-between">
            What is your business model? <span class="text-slate-400 group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="text-slate-600 mt-2">SaaS + usage-based take rate on processed loan volume. Partners pay platform fees and optional add-ons (KYC, SMS, auto-debit).</p>
        </details>
        <details class="py-3 group">
          <summary class="cursor-pointer font-medium flex items-center justify-between">
            How do you manage credit risk? <span class="text-slate-400 group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="text-slate-600 mt-2">We provide risk score APIs that combine partner data, repayment histories, and device signals. Partners retain underwriting control; we reduce fraud and late-stage delinquencies.</p>
        </details>
        <details class="py-3 group">
          <summary class="cursor-pointer font-medium flex items-center justify-between">
            What are you fundraising for? <span class="text-slate-400 group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="text-slate-600 mt-2">Team expansion (engineering & risk), go-to-market, and regulatory compliance in new markets.</p>
        </details>
      </div>
    </section>

    {{-- CONTACT --}}
    <section id="contact" class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
      <h3 class="text-base md:text-lg font-semibold">Talk to Us</h3>
      <p class="text-slate-600 text-sm mt-1">We’re happy to walk through the product, metrics, and roadmap.</p>
      <form class="mt-4 grid md:grid-cols-2 gap-3 text-sm">
        <input class="h-10 px-3 rounded-lg border bg-white" placeholder="Full Name" required>
        <input type="email" class="h-10 px-3 rounded-lg border bg-white" placeholder="Work Email" required>
        <input class="h-10 px-3 rounded-lg border bg-white md:col-span-2" placeholder="Company / Fund" required>
        <textarea rows="4" class="px-3 py-2 rounded-lg border bg-white md:col-span-2" placeholder="Your message"></textarea>
        <div class="md:col-span-2 flex gap-2">
          <button class="btn btn-primary">Send</button>
          <button type="reset" class="btn btn-quiet">Reset</button>
        </div>
      </form>
    </section>

  </div>
@endsection

@push('scripts')
<script>
  // Theme & Density shared with layout
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

  // Keyboard section jumps
  (function(){
    const map = { 'G T': '#traction', 'G F': '#financials', 'G O': '#overview' };
    let seq = [];
    document.addEventListener('keydown', (e)=>{
      const k = e.key.toUpperCase();
      if (!/^[A-Z]$/.test(k)) return;
      seq.push(k); if (seq.length>2) seq.shift();
      const key = seq.join(' ');
      if (map[key]) { e.preventDefault(); location.hash = map[key]; }
    });
  })();
</script>
@endpush
