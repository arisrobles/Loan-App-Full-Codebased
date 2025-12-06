@extends('layouts.app')

@php
  $pageTitle = 'Loan Details — ' . $loan->reference;

  $statusColors = [
    'new_application'  => 'bg-slate-100 text-slate-700',
    'under_review'     => 'bg-cyan-100 text-cyan-800',
    'approved'         => 'bg-emerald-100 text-emerald-700',
    'for_release'      => 'bg-sky-100 text-sky-800',
    'disbursed'        => 'bg-indigo-100 text-indigo-700',
    'closed'           => 'bg-slate-200 text-slate-800',
    'rejected'         => 'bg-rose-100 text-rose-700',
    'cancelled'        => 'bg-pink-100 text-pink-700',
    'restructured'     => 'bg-purple-100 text-purple-700',
  ];
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800 text-white shadow-xl">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold mb-2">Loan Details</h1>
      <p class="text-slate-200 text-sm">
        Reference: <span class="font-semibold">{{ $loan->reference }}</span> &middot;
        Borrower: <span class="font-semibold">{{ $loan->borrower_name ?? 'N/A' }}</span>
      </p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('loans.index') }}"
         class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold">
        ← Back to Loans
      </a>
      <a href="{{ route('loans.edit', $loan) }}"
         class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-semibold">
        Edit Loan
      </a>
      <a href="{{ route('repayments.index', $loan) }}"
         class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-semibold">
        View Repayment Schedule
      </a>
    </div>
  </div>
</div>

{{-- FLASH MESSAGES --}}
@if(session('success'))
  <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
    {{ session('success') }}
  </div>
@endif

@if($errors->any())
  <div class="mb-6 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
    <ul class="list-disc list-inside">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- LOAN INFORMATION CARD --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  {{-- Main Info --}}
  <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">Loan Information</h2>
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Status</div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$loan->status] ?? 'bg-slate-100 text-slate-700' }}">
          {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
        </span>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Principal Amount</div>
        <div class="text-lg font-bold text-slate-900">₱{{ number_format($loan->principal_amount, 2) }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Interest Rate</div>
        <div class="text-slate-800">{{ number_format($loan->interest_rate * 100, 2) }}% per annum</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Application Date</div>
        <div class="text-slate-800">{{ $loan->application_date?->format('M d, Y') ?? 'N/A' }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Release Date</div>
        <div class="text-slate-800">{{ $loan->release_date?->format('M d, Y') ?? 'N/A' }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Maturity Date</div>
        <div class="text-slate-800">{{ $loan->maturity_date?->format('M d, Y') ?? 'N/A' }}</div>
      </div>
      @if($loan->borrower)
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Borrower</div>
        <a href="{{ route('borrowers.show', $loan->borrower) }}" class="text-indigo-600 hover:underline">
          {{ $loan->borrower->full_name }}
        </a>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Contact</div>
        <div class="text-slate-800">{{ $loan->borrower->email ?? 'N/A' }}</div>
      </div>
      @endif
      @if($loan->remarks)
      <div class="col-span-2">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Remarks</div>
        <div class="text-slate-800">{{ $loan->remarks }}</div>
      </div>
      @endif
    </div>
  </div>

  {{-- Financial Summary --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">Financial Summary</h2>
    <div class="space-y-4">
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Disbursed</div>
        <div class="text-xl font-bold text-slate-900">₱{{ number_format($loan->total_disbursed ?? 0, 2) }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Paid</div>
        <div class="text-xl font-bold text-emerald-600">₱{{ number_format($totalPaid, 2) }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Penalties</div>
        <div class="text-xl font-bold text-rose-600">₱{{ number_format($totalPenalties, 2) }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Outstanding Balance</div>
        <div class="text-xl font-bold {{ $totalOutstanding > 0 ? 'text-rose-600' : 'text-slate-800' }}">
          ₱{{ number_format($totalOutstanding, 2) }}
        </div>
      </div>
      <div class="pt-4 border-t border-slate-200">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-2">Payment Statistics</div>
        <div class="space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-600">Approved:</span>
            <span class="font-semibold text-emerald-600">{{ $paymentStats['total_approved'] }} (₱{{ number_format($paymentStats['approved'], 2) }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Pending:</span>
            <span class="font-semibold text-yellow-600">{{ $paymentStats['total_pending'] }} (₱{{ number_format($paymentStats['pending'], 2) }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Rejected:</span>
            <span class="font-semibold text-rose-600">{{ $paymentStats['total_rejected'] }} (₱{{ number_format($paymentStats['rejected'], 2) }})</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- REPAYMENT SCHEDULE WITH PAYMENT HISTORY --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
    <h2 class="text-lg font-semibold text-slate-800">Repayment Schedule & Payment History</h2>
    <p class="text-xs text-slate-500 mt-1">Click on a repayment to view payment history</p>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-xs">
      <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
        <tr>
          <th class="px-4 py-3 text-left">#</th>
          <th class="px-4 py-3 text-left">Due Date</th>
          <th class="px-4 py-3 text-right">Amount Due</th>
          <th class="px-4 py-3 text-right">Amount Paid</th>
          <th class="px-4 py-3 text-right">Penalty</th>
          <th class="px-4 py-3 text-right">Outstanding</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-center">Payments</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($loan->repayments as $index => $repayment)
          @php
            $outstanding = (float)$repayment->outstanding;
            $days = $repayment->days_overdue;
            $isFullyPaid = $outstanding <= 0.01;
            $repaymentPayments = $paymentsByRepayment->get($repayment->id, collect());
          @endphp
          <tr class="hover:bg-slate-50/60" x-data="{ open: false }">
            <td class="px-4 py-3 text-slate-700">{{ $index + 1 }}</td>
            <td class="px-4 py-3 text-slate-800">
              {{ $repayment->due_date?->format('M d, Y') ?? 'N/A' }}
              @if($days > 0 && !$isFullyPaid)
                <div class="text-[10px] text-rose-600 mt-1">{{ $days }} days overdue</div>
              @endif
            </td>
            <td class="px-4 py-3 text-right text-slate-800">₱{{ number_format($repayment->amount_due, 2) }}</td>
            <td class="px-4 py-3 text-right text-emerald-600">₱{{ number_format($repayment->amount_paid, 2) }}</td>
            <td class="px-4 py-3 text-right text-rose-600">₱{{ number_format($repayment->penalty_applied, 2) }}</td>
            <td class="px-4 py-3 text-right {{ $outstanding > 0 ? 'text-rose-600 font-semibold' : 'text-slate-800' }}">
              ₱{{ number_format($outstanding, 2) }}
            </td>
            <td class="px-4 py-3">
              @if($isFullyPaid)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                  Paid
                </span>
              @elseif($repayment->amount_paid > 0)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">
                  Partial
                </span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                  Unpaid
                </span>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              @if($repaymentPayments->count() > 0)
                <button @click="open = !open" 
                        class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold hover:bg-indigo-100">
                  {{ $repaymentPayments->count() }} payment(s)
                  <svg class="w-3 h-3 ml-1" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              @else
                <span class="text-slate-400 text-[11px]">No payments</span>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              <a href="{{ route('repayments.index', $loan) }}" 
                 class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                View Details
              </a>
            </td>
          </tr>
          
          {{-- Payment History Row (Expandable) --}}
          @if($repaymentPayments->count() > 0)
            <tr x-show="open" x-collapse class="bg-slate-50/50">
              <td colspan="9" class="px-4 py-4">
                <div class="ml-8">
                  <h4 class="text-xs font-semibold text-slate-700 mb-3">Payment History for this Repayment</h4>
                  <div class="space-y-2">
                    @foreach($repaymentPayments as $payment)
                      <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200">
                        <div class="flex items-center gap-3">
                          @if($payment->status === 'approved')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">
                              ✓ Approved
                            </span>
                          @elseif($payment->status === 'pending')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-semibold bg-yellow-100 text-yellow-700">
                              ⏳ Pending
                            </span>
                          @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700">
                              ✗ Rejected
                            </span>
                          @endif
                          <div class="text-xs text-slate-700">
                            <div class="font-semibold">₱{{ number_format($payment->amount, 2) }}</div>
                            <div class="text-slate-500">{{ $payment->paid_at?->format('M d, Y h:i A') ?? 'N/A' }}</div>
                          </div>
                          @if($payment->penalty_amount > 0)
                            <div class="text-xs text-rose-600">
                              Penalty: ₱{{ number_format($payment->penalty_amount, 2) }}
                            </div>
                          @endif
                        </div>
                        <div class="flex items-center gap-2">
                          @if($payment->receiptDocument)
                            <a href="{{ route('documents.view', $payment->receiptDocument->id) }}" 
                               target="_blank"
                               class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-semibold hover:bg-blue-100">
                              View Receipt
                            </a>
                          @endif
                          @if($payment->approved_at && $payment->approvedBy)
                            <div class="text-[10px] text-slate-500">
                              Approved by {{ $payment->approvedBy->username ?? 'N/A' }}<br>
                              {{ $payment->approved_at->format('M d, Y') }}
                            </div>
                          @endif
                          @if($payment->rejection_reason)
                            <div class="text-[10px] text-rose-600 max-w-xs">
                              Reason: {{ $payment->rejection_reason }}
                            </div>
                          @endif
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </td>
            </tr>
          @endif
        @empty
          <tr>
            <td colspan="9" class="px-4 py-6 text-center text-slate-400 text-xs italic">
              No repayment schedule found for this loan.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ALL PAYMENTS TABLE --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
    <h2 class="text-lg font-semibold text-slate-800">All Payments</h2>
    <p class="text-xs text-slate-500 mt-1">Complete payment history for this loan</p>
  </div>

  @if($loan->payments->count() > 0)
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">Repayment Period</th>
            <th class="px-4 py-3 text-right">Amount</th>
            <th class="px-4 py-3 text-right">Penalty</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-left">Receipt</th>
            <th class="px-4 py-3 text-left">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($loan->payments as $payment)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3 text-slate-700">
                <div>{{ $payment->paid_at?->format('M d, Y') ?? 'N/A' }}</div>
                <div class="text-[10px] text-slate-500">{{ $payment->paid_at?->format('h:i A') ?? '' }}</div>
              </td>
              <td class="px-4 py-3 text-slate-700">
                @if($payment->repayment)
                  Due: {{ $payment->repayment->due_date?->format('M d, Y') ?? 'N/A' }}
                @else
                  <span class="text-slate-400">Auto-assigned</span>
                @endif
              </td>
              <td class="px-4 py-3 text-right font-semibold text-slate-900">₱{{ number_format($payment->amount, 2) }}</td>
              <td class="px-4 py-3 text-right text-rose-600">
                @if($payment->penalty_amount > 0)
                  ₱{{ number_format($payment->penalty_amount, 2) }}
                @else
                  <span class="text-slate-400">₱0.00</span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                @if($payment->status === 'approved')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                    Approved
                  </span>
                  @if($payment->approved_at)
                    <div class="text-[10px] text-slate-500 mt-1">{{ $payment->approved_at->format('M d, Y') }}</div>
                  @endif
                @elseif($payment->status === 'pending')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">
                    Pending
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">
                    Rejected
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                @if($payment->receiptDocument)
                  <a href="{{ route('documents.view', $payment->receiptDocument->id) }}" 
                     target="_blank"
                     class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-semibold hover:bg-blue-100">
                    View Receipt
                  </a>
                @else
                  <span class="text-slate-400 text-[10px]">No receipt</span>
                @endif
              </td>
              <td class="px-4 py-3 text-slate-700 text-[10px]">
                @if($payment->remarks)
                  {{ Str::limit($payment->remarks, 50) }}
                @elseif($payment->rejection_reason)
                  <span class="text-rose-600">Rejected: {{ Str::limit($payment->rejection_reason, 50) }}</span>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="p-12 text-center">
      <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-slate-900">No payments found</h3>
      <p class="mt-1 text-sm text-slate-500">No payments have been recorded for this loan yet.</p>
    </div>
  @endif
</div>

@endsection

@section('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection

