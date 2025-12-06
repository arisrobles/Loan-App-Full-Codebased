@extends('layouts.app')

@php
  $pageTitle = 'About MasterFunds';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
    <p class="text-slate-300 text-sm">System information and technical details</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- Main Content --}}
  <div class="lg:col-span-2 space-y-6">
    {{-- System Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-900 mb-4">System Information</h2>
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Application Name</div>
            <div class="text-slate-900 font-medium">MasterFunds Admin Panel</div>
          </div>
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Version</div>
            <div class="text-slate-900 font-medium">v1.0.0</div>
          </div>
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Framework</div>
            <div class="text-slate-900 font-medium">Laravel {{ app()->version() }}</div>
          </div>
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">PHP Version</div>
            <div class="text-slate-900 font-medium">{{ PHP_VERSION }}</div>
          </div>
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Database</div>
            <div class="text-slate-900 font-medium">MySQL</div>
          </div>
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Environment</div>
            <div class="text-slate-900 font-medium">{{ app()->environment() }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Features --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-900 mb-4">System Features</h2>
      <ul class="space-y-3">
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Loan Management</div>
            <div class="text-sm text-slate-600">Complete loan lifecycle management from application to closure</div>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Payment Processing</div>
            <div class="text-sm text-slate-600">Payment approval workflow, direct entry, and comprehensive history</div>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Financial Reports</div>
            <div class="text-sm text-slate-600">Profit & Loss, Balance Sheet, and Cash Flow reports</div>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Access Control</div>
            <div class="text-sm text-slate-600">Role-based permissions and user management</div>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Bank Integration</div>
            <div class="text-sm text-slate-600">Bank account management and transaction reconciliation</div>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <div>
            <div class="font-medium text-slate-900">Chart of Accounts</div>
            <div class="text-sm text-slate-600">Complete accounting structure management</div>
          </div>
        </li>
      </ul>
    </div>

    {{-- Company Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-900 mb-4">Company</h2>
      <div class="space-y-3">
        <div>
          <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Company Name</div>
          <div class="text-slate-900 font-medium">MasterFunds Inc.</div>
        </div>
        <div>
          <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Description</div>
          <div class="text-slate-700">A comprehensive loan management system designed for efficient lending operations and borrower management.</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div class="space-y-6">
    {{-- Quick Stats --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">System Statistics</h3>
      <div class="space-y-3">
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Total Loans</span>
          <span class="font-semibold text-slate-900">{{ \App\Models\Loan::count() }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Total Borrowers</span>
          <span class="font-semibold text-slate-900">{{ \App\Models\Borrower::count() }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Total Payments</span>
          <span class="font-semibold text-slate-900">{{ \App\Models\Payment::count() }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Active Users</span>
          <span class="font-semibold text-slate-900">{{ \App\Models\User::count() }}</span>
        </div>
      </div>
    </div>

    {{-- Quick Links --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Quick Links</h3>
      <div class="space-y-2">
        <a href="{{ route('help.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          Help & Support
        </a>
        <a href="{{ route('legal.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          Terms & Conditions
        </a>
        <a href="{{ route('admin.settings.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          Admin Settings
        </a>
        <a href="{{ route('dashboard.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          Dashboard
        </a>
      </div>
    </div>

    {{-- Credits --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Credits</h3>
      <p class="text-xs text-slate-600 leading-relaxed">
        MasterFunds Admin Panel v1.0.0
      </p>
      <p class="text-xs text-slate-500 mt-2">
        © {{ now()->year }} MasterFunds. All rights reserved.
      </p>
    </div>
  </div>
</div>

@endsection

