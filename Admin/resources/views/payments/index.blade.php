@extends('layouts.app')

@php
  use Illuminate\Support\Str;
  $pageTitle = 'Payment History';
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
    <p class="text-slate-300 text-sm">Comprehensive payment history and approval management</p>
  </div>
</div>

{{-- STATISTICS CARDS --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Payments</div>
    <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Approved</div>
    <div class="text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</div>
    <div class="text-xs text-slate-500 mt-1">₱{{ number_format($stats['total_amount'], 2) }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Pending</div>
    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
    <div class="text-xs text-slate-500 mt-1">₱{{ number_format($stats['pending_amount'], 2) }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Rejected</div>
    <div class="text-2xl font-bold text-rose-600">{{ $stats['rejected'] }}</div>
  </div>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
  <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
      <input type="text" name="q" value="{{ request('q') }}" 
             placeholder="Loan ref, borrower..." 
             class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
      <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Borrower</label>
      <select name="borrower_id" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="">All Borrowers</option>
        @foreach($borrowers as $borrower)
          <option value="{{ $borrower->id }}" {{ request('borrower_id') == $borrower->id ? 'selected' : '' }}>
            {{ $borrower->full_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Date From</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}" 
             class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Date To</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}" 
             class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm font-semibold">
        Filter
      </button>
      <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold">
        Clear
      </a>
    </div>
  </form>
</div>

{{-- STATUS FILTERS (Quick) --}}
<div class="mb-6 flex gap-2">
  <a href="{{ route('payments.index', ['status' => 'all']) }}" 
     class="px-4 py-2 rounded-lg {{ $status === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    All ({{ $stats['total'] }})
  </a>
  <a href="{{ route('payments.index', ['status' => 'pending']) }}" 
     class="px-4 py-2 rounded-lg {{ $status === 'pending' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Pending ({{ $stats['pending'] }})
  </a>
  <a href="{{ route('payments.index', ['status' => 'approved']) }}" 
     class="px-4 py-2 rounded-lg {{ $status === 'approved' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Approved ({{ $stats['approved'] }})
  </a>
  <a href="{{ route('payments.index', ['status' => 'rejected']) }}" 
     class="px-4 py-2 rounded-lg {{ $status === 'rejected' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Rejected ({{ $stats['rejected'] }})
  </a>
</div>

{{-- PAYMENTS TABLE --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
  @if($payments->count() > 0)
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Borrower</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Loan</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Repayment</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Amount</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Penalty</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Receipt</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          @foreach($payments as $payment)
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3 text-sm">
                <div>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</div>
                <div class="text-xs text-slate-500">{{ $payment->paid_at ? $payment->paid_at->format('h:i A') : '' }}</div>
              </td>
              <td class="px-4 py-3 text-sm">
                <div class="font-medium">{{ $payment->borrower->full_name ?? 'N/A' }}</div>
                <div class="text-xs text-slate-500">{{ $payment->borrower->email ?? '' }}</div>
              </td>
              <td class="px-4 py-3 text-sm">
                <div class="font-medium">{{ $payment->loan->reference ?? 'N/A' }}</div>
                <div class="text-xs text-slate-500">₱{{ number_format($payment->loan->principal_amount ?? 0, 2) }}</div>
              </td>
              <td class="px-4 py-3 text-sm">
                @if($payment->repayment)
                  <div class="text-xs">
                    Due: {{ \Carbon\Carbon::parse($payment->repayment->due_date)->format('M d, Y') }}
                  </div>
                  <div class="text-xs text-slate-500">
                    Due: ₱{{ number_format($payment->repayment->amount_due, 2) }} | 
                    Paid: ₱{{ number_format($payment->repayment->amount_paid, 2) }}
                  </div>
                @else
                  <span class="text-xs text-slate-400">Auto-assigned</span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm text-right font-semibold">
                ₱{{ number_format($payment->amount, 2) }}
              </td>
              <td class="px-4 py-3 text-sm text-right">
                @if($payment->penalty_amount > 0)
                  <span class="text-rose-600 font-medium">₱{{ number_format($payment->penalty_amount, 2) }}</span>
                @else
                  <span class="text-slate-400">₱0.00</span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm">
                @if($payment->receiptDocument)
                  <a href="{{ route('documents.view', $payment->receiptDocument->id) }}" 
                     target="_blank" 
                     class="text-blue-600 hover:text-blue-800 text-xs flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Receipt
                  </a>
                @else
                  <span class="text-slate-400 text-xs">No receipt</span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm text-center">
                @if($payment->status === 'pending')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Pending
                  </span>
                @elseif($payment->status === 'approved')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    Approved
                  </span>
                  @if($payment->approved_at)
                    <div class="text-xs text-slate-500 mt-1">
                      {{ $payment->approved_at->format('M d, Y') }}
                    </div>
                  @endif
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                    Rejected
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm text-center">
                @if($payment->status === 'pending')
                  <div class="flex gap-2 justify-center">
                    <form method="POST" action="{{ route('payments.approve', $payment) }}" class="inline">
                      @csrf
                      <button type="submit" 
                              class="px-3 py-1 bg-emerald-600 text-white text-xs rounded-lg hover:bg-emerald-700">
                        Approve
                      </button>
                    </form>
                    <button type="button" 
                            onclick="showRejectModal({{ $payment->id }})"
                            class="px-3 py-1 bg-rose-600 text-white text-xs rounded-lg hover:bg-rose-700">
                      Reject
                    </button>
                  </div>
                @elseif($payment->status === 'approved')
                  <div class="text-xs text-slate-500">
                    Approved by: {{ $payment->approvedBy->username ?? 'N/A' }}
                  </div>
                @else
                  <div class="text-xs text-slate-500">
                    {{ $payment->rejection_reason ? 'Reason: ' . Str::limit($payment->rejection_reason, 30) : 'Rejected' }}
                  </div>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t border-slate-200">
      {{ $payments->links() }}
    </div>
  @else
    <div class="p-12 text-center">
      <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-slate-900">No payments found</h3>
      <p class="mt-1 text-sm text-slate-500">
        @if($status === 'pending')
          There are no pending payments to review.
        @else
          No {{ $status }} payments found.
        @endif
      </p>
    </div>
  @endif
</div>

{{-- REJECT MODAL --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <h3 class="text-lg font-semibold mb-4">Reject Payment</h3>
    <form id="rejectForm" method="POST" action="">
      @csrf
      <div class="mb-4">
        <label for="rejection_reason" class="block text-sm font-medium text-slate-700 mb-2">
          Rejection Reason <span class="text-rose-600">*</span>
        </label>
        <textarea id="rejection_reason" 
                  name="rejection_reason" 
                  rows="4" 
                  required
                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                  placeholder="Please provide a reason for rejecting this payment..."></textarea>
      </div>
      <div class="flex gap-3 justify-end">
        <button type="button" 
                onclick="closeRejectModal()"
                class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200">
          Cancel
        </button>
        <button type="submit" 
                class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
          Reject Payment
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function showRejectModal(paymentId) {
  const form = document.getElementById('rejectForm');
  form.action = '{{ route("payments.reject", ":id") }}'.replace(':id', paymentId);
  document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
  document.getElementById('rejectModal').classList.add('hidden');
  document.getElementById('rejection_reason').value = '';
}

// Close modal on outside click
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
  if (e.target === this) {
    closeRejectModal();
  }
});
</script>

@endsection

