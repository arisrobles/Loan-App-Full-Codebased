@extends('layouts.app')

@section('title', 'Dashboard — MasterFunds')
@section('page-title', 'Dashboard')

@section('toolbar')
  <a href="{{ route('transactions.index', ['accountId'=>1]) }}" class="hidden sm:inline-flex items-center gap-2 px-3 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
    Quick Payments
  </a>
@endsection

@section('content')
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Active Loans</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">45</div>
      <div class="text-xs text-cyan-600 mt-1">3 late this week</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Borrowers</div>
      <div class="mt-2 text-2xl font-semibold text-indigo-600">90</div>
      <div class="text-xs text-slate-500 mt-1">2 new signups</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Collections (MTD)</div>
      <div class="mt-2 text-2xl font-semibold text-emerald-600">₱540,000</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Avg. Ticket</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">₱18,900</div>
    </article>
  </section>

  <section class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
      <h2 class="text-base md:text-lg font-semibold">Welcome</h2>
      <p class="mt-2 text-sm text-slate-600">This dashboard is using a shared layout with a sidebar and topbar.</p>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
      <h2 class="text-base md:text-lg font-semibold">Shortcuts</h2>
      <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ route('borrowers.index') }}" class="px-3 h-10 rounded-lg border hover:bg-slate-50 text-sm">Borrowers</a>
        <a href="{{ route('loans.index') }}" class="px-3 h-10 rounded-lg border hover:bg-slate-50 text-sm">Loans</a>
        <a href="{{ route('admin.settings.index') }}" class="px-3 h-10 rounded-lg border hover:bg-slate-50 text-sm">Settings</a>
      </div>
    </div>
  </section>
@endsection
