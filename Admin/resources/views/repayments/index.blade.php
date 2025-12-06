@extends('layouts.app')

@php
  $pageTitle = 'Repayments for ' . ($loan->reference ?? 'Loan');

  $totals = [
    'due'         => $repayments->sum('amount_due'),
    'paid'        => $repayments->sum('amount_paid'),
    'outstanding' => $repayments->sum(fn($r) => (float)$r->outstanding),
  ];
@endphp

@section('content')

  {{-- HEADER --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-indigo-500 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">
          Repayment Schedule — {{ $loan->reference }}
        </h1>
        <p class="text-sm text-emerald-100">
          Borrower: {{ $loan->borrower_name ?? 'N/A' }} &middot;
          Principal: ₱{{ number_format($loan->principal_amount, 2) }} &middot;
          Status: <span class="font-semibold">{{ ucfirst(str_replace('_',' ', $loan->status)) }}</span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('loans.index') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-emerald-800/70 hover:bg-emerald-900 text-xs font-semibold">
          Back to Loans
        </a>

        @if ($loan->status === \App\Models\Loan::ST_DISBURSED && $loan->is_active)
          <button onclick="document.getElementById('paymentForm').classList.toggle('hidden')"
                  class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
            💰 Record Payment
          </button>
        @endif

        @if (Route::has('repayments.create'))
          <a href="{{ route('repayments.create', $loan) }}"
             class="inline-flex items-center px-3 py-2 rounded-lg bg-white text-emerald-700 text-xs font-semibold hover:bg-emerald-50">
            + Add Repayment Row
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- PAYMENT FORM (only for disbursed loans) --}}
  @if ($loan->status === \App\Models\Loan::ST_DISBURSED && $loan->is_active)
    <div id="paymentForm" class="hidden mb-6 bg-white rounded-2xl border border-indigo-200 shadow-sm p-6">
      <h3 class="text-sm font-semibold text-slate-800 mb-4">Record Payment</h3>
      <form action="{{ route('payments.store', $loan) }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Amount (₱) <span class="text-rose-500">*</span></label>
            <input type="number" name="amount" step="0.01" min="0.01" required
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Date</label>
            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Apply to Repayment (Optional)</label>
            <select name="repayment_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
              <option value="">Auto (Oldest Unpaid)</option>
              @foreach($repayments as $rep)
                @if($rep->amount_paid < $rep->amount_due)
                  <option value="{{ $rep->id }}">
                    Due: {{ $rep->due_date->toDateString() }} - Outstanding: ₱{{ number_format($rep->outstanding, 2) }}
                  </option>
                @endif
              @endforeach
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('paymentForm').classList.add('hidden')"
                  class="px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
            Cancel
          </button>
          <button type="submit"
                  class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
            Record Payment
          </button>
        </div>
      </form>
    </div>
  @endif

  {{-- SUMMARY --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Total Due</div>
      <div class="text-xl font-bold text-slate-800">₱{{ number_format($totals['due'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Total Paid</div>
      <div class="text-xl font-bold text-emerald-600">₱{{ number_format($totals['paid'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Outstanding</div>
      <div class="text-xl font-bold {{ $totals['outstanding'] > 0 ? 'text-rose-600':'text-slate-800' }}">
        ₱{{ number_format($totals['outstanding'], 2) }}
      </div>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-3 py-2 text-left">#</th>
            <th class="px-3 py-2 text-left">Due Date</th>
            <th class="px-3 py-2 text-right">Amount Due</th>
            <th class="px-3 py-2 text-right">Amount Paid</th>
            <th class="px-3 py-2 text-right">Outstanding</th>
            <th class="px-3 py-2 text-left">Paid At</th>
            <th class="px-3 py-2 text-left">Note</th>
            <th class="px-3 py-2 text-left">Overdue</th>
            <th class="px-3 py-2 text-right">Penalty</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($repayments as $i => $repayment)
            @php
              $outstanding = (float)$repayment->outstanding;
              $days = $repayment->days_overdue;
              $penalty = (float)$repayment->computePenalty();
              $repaymentPayments = $paymentsByRepayment->get($repayment->id, collect());
            @endphp
            <tr class="border-b border-slate-100 hover:bg-slate-50/60" x-data="{ open: false }">
              <td class="px-3 py-2 text-slate-700">
                {{ $i + 1 }}
              </td>
              <td class="px-3 py-2 text-slate-800">
                {{ optional($repayment->due_date)->toDateString() ?? '—' }}
              </td>
              <td class="px-3 py-2 text-right text-slate-800">
                ₱{{ number_format($repayment->amount_due, 2) }}
              </td>
              <td class="px-3 py-2 text-right {{ $repayment->amount_paid > 0 ? 'text-emerald-600':'text-slate-800' }}">
                ₱{{ number_format($repayment->amount_paid, 2) }}
              </td>
              <td class="px-3 py-2 text-right {{ $outstanding > 0 ? 'text-rose-600':'text-slate-800' }}">
                ₱{{ number_format($outstanding, 2) }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ optional($repayment->paid_at)->format('Y-m-d H:i') ?? '—' }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $repayment->note ?? '—' }}
              </td>
              <td class="px-3 py-2">
                @if ($days > 0 && $outstanding > 0)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-semibold">
                    {{ $days }} days
                  </span>
                @else
                  <span class="text-[11px] text-slate-400">On time</span>
                @endif
              </td>
              <td class="px-3 py-2 text-right {{ $penalty > 0 ? 'text-rose-600':'text-slate-800' }}">
                ₱{{ number_format($penalty, 2) }}
              </td>
              <td class="px-3 py-2 text-right space-x-1">
                @if($repaymentPayments->count() > 0)
                  <button @click="open = !open" 
                          class="inline-flex items-center px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[11px] font-semibold hover:bg-indigo-100">
                    {{ $repaymentPayments->count() }} payment(s)
                    <svg class="w-3 h-3 ml-1" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                @endif
                @if (Route::has('repayments.edit'))
                  <a href="{{ route('repayments.edit', [$loan, $repayment]) }}"
                     class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                    Edit
                  </a>
                @endif

                @if (Route::has('repayments.destroy'))
                  <form action="{{ route('repayments.destroy', [$loan, $repayment]) }}"
                        method="POST"
                        class="inline-block"
                        onsubmit="return confirm('Delete this repayment row?');">
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex items-center px-2 py-1 rounded-lg border border-rose-200 text-[11px] text-rose-700 hover:bg-rose-50">
                      Delete
                    </button>
                  </form>
                @endif

                @if (Route::has('repayments.applyPenalty') && $penalty > 0)
                  <form action="{{ route('repayments.applyPenalty', $repayment) }}"
                        method="POST"
                        class="inline-block"
                        onsubmit="return confirm('Apply suggested penalty to this repayment?');">
                    @csrf
                    <button class="inline-flex items-center px-2 py-1 rounded-lg bg-rose-600 text-white text-[11px] hover:bg-rose-700">
                      Apply Penalty
                    </button>
                  </form>
                @endif
              </td>
            </tr>
            
            {{-- Payment History Row (Expandable) --}}
            @if($repaymentPayments->count() > 0)
              <tr x-show="open" x-collapse class="bg-slate-50/50">
                <td colspan="10" class="px-3 py-4">
                  <div class="ml-4">
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
              <td colspan="10" class="px-3 py-6 text-center text-slate-400 text-[11px] italic">
                No repayment rows have been scheduled for this loan yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
