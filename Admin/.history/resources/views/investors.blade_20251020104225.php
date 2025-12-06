@extends('layouts.app')

@php
  $pageTitle = 'Investor Relations';
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

  /* SECTIONS */
  section {
    background:white;
    border:1px solid #eef2ff;
    border-radius:1.25rem;
    padding:1.5rem;
    box-shadow:0 10px 28px -16px rgba(99,102,241,.15);
    margin-bottom:2rem;
  }

  /* BADGE */
  .badge {
    display:inline-flex;align-items:center;
    padding:.25rem .7rem;border-radius:9999px;
    font-size:.75rem;font-weight:600;
  }
  .badge-blue{background:#dbeafe;color:#1e40af;}
  .badge-green{background:#dcfce7;color:#166534;}
  .badge-amber{background:#fef3c7;color:#92400e;}
  .badge-slate{background:#f1f5f9;color:#475569;}
</style>
@endsection


@section('content')

{{-- HEADER --}}
<div class="mx-header rounded-2xl mb-8 shadow-md">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-semibold">{{ $pageTitle }}</h2>
      <p class="text-sm text-indigo-100">Growth, metrics, and materials for partners</p>
    </div>
    <div class="flex gap-2">
      <a href="#contact" class="btn btn-brand">Contact Us</a>
      <a href="#dataroom" class="btn btn-outline">Data Room</a>
    </div>
  </div>
</div>

{{-- METRICS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
  <div class="metric-card"><h6>Active Borrowers</h6><p>12,480</p></div>
  <div class="metric-card"><h6>Loan Volume (TTM)</h6><p>₱1.86B</p></div>
  <div class="metric-card"><h6>Net Take Rate</h6><p>3.4%</p></div>
  <div class="metric-card"><h6>90+ DPD</h6><p>2.1%</p></div>
</div>

{{-- OVERVIEW --}}
<section>
  <div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-3">
      <span class="badge badge-blue">Pre-Seed / Seed</span>
      <h2 class="text-xl md:text-2xl font-semibold">Funding inclusive growth for emerging borrowers</h2>
      <p class="text-slate-600 text-sm leading-relaxed">
        MasterFunds is a lending operations platform that streamlines borrower onboarding,
        risk assessment, and repayments for non-bank lenders. We help partners scale responsibly
        with verified KYC, automated collections, and real-time risk signals.
      </p>
      <div class="flex flex-wrap gap-2 pt-2">
        <span class="badge badge-green">KYC Automation</span>
        <span class="badge badge-blue">Risk Scoring</span>
        <span class="badge badge-amber">Collections</span>
        <span class="badge badge-slate">Analytics</span>
      </div>
    </div>
    <!-- KPI right column -->
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
<section>
  <h3 class="text-lg font-semibold mb-3">Traction</h3>
  <p class="text-sm text-slate-600 mb-4">Recent growth in lending volume and risk performance.</p>
  <div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 p-4 rounded-xl border border-slate-100 bg-slate-50">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium">Monthly Disbursed (₱M)</span>
        <span class="text-xs text-slate-500">Last 12 months</span>
      </div>
      <svg viewBox="0 0 600 220" class="w-full h-48">
        <defs>
          <linearGradient id="grad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#93c5fd" stop-opacity="0.7"/>
            <stop offset="100%" stop-color="#93c5fd" stop-opacity="0"/>
          </linearGradient>
        </defs>
        <path d="M0,180 C60,160 120,150 180,120 C240,100 300,110 360,90 C420,80 480,60 540,70 L540,220 L0,220 Z" fill="url(#grad)"/>
        <path d="M0,180 C60,160 120,150 180,120 C240,100 300,110 360,90 C420,80 480,60 540,70" stroke="#2563eb" stroke-width="3" fill="none" stroke-linecap="round"/>
      </svg>
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
    </div>
  </div>
</section>

{{-- FINANCIAL SNAPSHOT --}}
<section>
  <h3 class="text-lg font-semibold mb-3">Financial Snapshot</h3>
  <p class="text-sm text-slate-600 mb-4">Key quarterly performance indicators.</p>
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
        <tr class="hover:bg-indigo-50/30">
          <td class="p-3 font-medium">Revenue</td>
          <td class="p-3 text-right">₱18.2M</td>
          <td class="p-3 text-right">₱15.7M</td>
          <td class="p-3 text-right text-emerald-700">+16.0%</td>
          <td class="p-3 text-right text-slate-600">Take rate stable</td>
        </tr>
        <tr class="hover:bg-indigo-50/30">
          <td class="p-3 font-medium">Operating Margin</td>
          <td class="p-3 text-right">21%</td>
          <td class="p-3 text-right">17%</td>
          <td class="p-3 text-right text-emerald-700">+400 bps</td>
          <td class="p-3 text-right text-slate-600">Scale effects</td>
        </tr>
        <tr class="hover:bg-indigo-50/30">
          <td class="p-3 font-medium">GMV</td>
          <td class="p-3 text-right">₱528M</td>
          <td class="p-3 text-right">₱462M</td>
          <td class="p-3 text-right text-emerald-700">+14.3%</td>
          <td class="p-3 text-right text-slate-600">+ partners</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

{{-- CONTACT --}}
<section id="contact">
  <h3 class="text-lg font-semibold mb-3">Talk to Us</h3>
  <p class="text-slate-600 text-sm mb-3">We’re happy to walk through the product, metrics, and roadmap.</p>
  <form class="grid md:grid-cols-2 gap-3 text-sm">
    <input class="h-10 px-3 rounded-lg border bg-white" placeholder="Full Name" required>
    <input type="email" class="h-10 px-3 rounded-lg border bg-white" placeholder="Work Email" required>
    <input class="h-10 px-3 rounded-lg border bg-white md:col-span-2" placeholder="Company / Fund" required>
    <textarea rows="4" class="px-3 py-2 rounded-lg border bg-white md:col-span-2" placeholder="Your message"></textarea>
    <div class="md:col-span-2 flex gap-2">
      <button class="btn btn-brand">Send</button>
      <button type="reset" class="btn btn-quiet">Reset</button>
    </div>
  </form>
</section>

@endsection
