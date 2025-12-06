@extends('layouts.app')

@php
  $pageTitle = 'Help & Support';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
    <p class="text-slate-300 text-sm">Get help with using the MasterFunds Admin Panel</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- Main Content --}}
  <div class="lg:col-span-2 space-y-6">
    {{-- FAQ Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-900 mb-4">Frequently Asked Questions</h2>
      
      <div class="space-y-4">
        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I approve a payment?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Go to the Payments page, find the pending payment, review the receipt if available, and click the "Approve" button. The payment will be automatically applied to the loan and repayment schedule.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I create a new loan?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Navigate to Loans → Create New Loan. Fill in the borrower information, loan amount, interest rate, tenor, and other details. The system will automatically generate the repayment schedule.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I transition a loan status?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Go to the loan detail page, use the status transition dropdown to move the loan through its lifecycle: new_application → under_review → approved → for_release → disbursed → closed.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I record a direct payment?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Go to the loan detail page or repayment schedule, click "Record Payment", enter the amount and select the repayment period. Direct payments are automatically approved and immediately update the loan balance.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I manage user permissions?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Go to Admin Settings → General. You can assign roles to users and configure permissions for each role. Roles include Administrator, Manager, Staff, and Viewer with different access levels.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I generate financial reports?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Navigate to Reports in the sidebar. You can generate Profit & Loss statements, Balance Sheets, and Cash Flow reports. Reports are generated based on your Chart of Accounts configuration.
          </div>
        </details>

        <details class="group">
          <summary class="flex items-center justify-between cursor-pointer p-3 bg-slate-50 rounded-lg hover:bg-slate-100">
            <span class="font-medium text-slate-900">How do I send notifications to borrowers?</span>
            <svg class="w-5 h-5 text-slate-500 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </summary>
          <div class="p-4 text-sm text-slate-700">
            Go to Notifications → Send Notification. Select the borrower, choose a notification type, enter title and message, and optionally link it to a specific loan. Notifications are automatically sent when loan statuses change or payments are approved/rejected.
          </div>
        </details>
      </div>
    </div>

    {{-- Contact Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-900 mb-4">Contact Information</h2>
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <div>
            <div class="text-xs text-slate-500">Email</div>
            <div class="text-slate-900 font-medium">support@masterfunds.com</div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
          </svg>
          <div>
            <div class="text-xs text-slate-500">Phone</div>
            <div class="text-slate-900 font-medium">+1 (234) 567-8900</div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <div class="text-xs text-slate-500">Business Hours</div>
            <div class="text-slate-900 font-medium">Mon-Fri: 9:00 AM - 6:00 PM</div>
            <div class="text-slate-600 text-sm">Sat: 9:00 AM - 1:00 PM</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div class="space-y-6">
    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Quick Actions</h3>
      <div class="space-y-2">
        <a href="{{ route('loans.create') }}" class="block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold text-center">
          Create New Loan
        </a>
        <a href="{{ route('payments.index') }}" class="block px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold text-center">
          Review Payments
        </a>
        <a href="{{ route('borrowers.index') }}" class="block px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold text-center">
          Manage Borrowers
        </a>
        <a href="{{ route('reports.index') }}" class="block px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold text-center">
          View Reports
        </a>
      </div>
    </div>

    {{-- Documentation Links --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Documentation</h3>
      <div class="space-y-2">
        <a href="#" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          User Guide
        </a>
        <a href="#" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          API Documentation
        </a>
        <a href="{{ route('legal.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          Terms & Conditions
        </a>
        <a href="{{ route('about.index') }}" class="block text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
          About System
        </a>
      </div>
    </div>
  </div>
</div>

@endsection

