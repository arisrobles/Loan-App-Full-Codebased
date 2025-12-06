{{-- resources/views/borrowers/show.blade.php --}}
@extends('layouts.app')

@php
  use App\Models\Borrower;
  $pageTitle = 'Borrower Profile';
  $statuses  = Borrower::STATUSES ?? ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  {{-- Back link --}}
  <div class="flex items-center justify-between">
    <a href="{{ route('borrowers.index') }}" class="text-sm text-indigo-600 hover:underline">
      ← Back to Borrowers
    </a>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  {{-- Card --}}
  <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
      <div>
        <h1 class="text-xl font-semibold text-slate-900">
          {{ $borrower->full_name }}
        </h1>
        <p class="text-sm text-slate-500">
          Reference: {{ $borrower->reference_no ?: '—' }}
        </p>
      </div>
      <div class="text-right space-y-1">
        <div>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
            @if($borrower->is_archived)
              bg-slate-200 text-slate-700
            @else
              bg-emerald-50 text-emerald-700
            @endif">
            {{ $borrower->is_archived ? 'Archived' : 'Active Record' }}
          </span>
        </div>
        <div>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
            @class([
              'bg-green-100 text-green-700' => $borrower->status === 'active',
              'bg-slate-100 text-slate-700' => $borrower->status === 'inactive',
              'bg-teal-100 text-teal-800' => $borrower->status === 'delinquent',
              'bg-sky-100 text-sky-700' => $borrower->status === 'closed',
              'bg-red-100 text-red-700' => $borrower->status === 'blacklisted',
            ])
          ">
            Status: {{ ucfirst($borrower->status ?? 'inactive') }}
          </span>
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 text-sm">
      <div>
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Contact</h2>
        <p class="text-slate-700">
          Email:
          @if($borrower->email)
            <a href="mailto:{{ $borrower->email }}" class="text-indigo-600 hover:underline">
              {{ $borrower->email }}
            </a>
          @else
            <span class="text-slate-400">—</span>
          @endif
        </p>
        <p class="text-slate-700">
          Phone: {{ $borrower->phone ?: '—' }}
        </p>
        <p class="text-slate-700">
          Address: {{ $borrower->address ?: '—' }}
        </p>
      </div>

      <div>
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Profile</h2>
        <p class="text-slate-700">Sex: {{ $borrower->sex ?: '—' }}</p>
        <p class="text-slate-700">Occupation: {{ $borrower->occupation ?: '—' }}</p>
        <p class="text-slate-700">
          Birthday:
          {{ $borrower->birthday?->format('M d, Y') ?? '—' }}
        </p>
        <p class="text-slate-700">
          Monthly Income:
          {{ $borrower->monthly_income !== null ? number_format($borrower->monthly_income, 2) : '—' }}
        </p>
        <p class="text-slate-700">
          Civil Status: {{ $borrower->civil_status ?: '—' }}
        </p>
      </div>
    </div>

    <div class="mt-6 text-xs text-slate-400 flex justify-between">
      <span>Created: {{ $borrower->created_at?->format('M d, Y h:ia') }}</span>
      <span>Updated: {{ $borrower->updated_at?->format('M d, Y h:ia') }}</span>
    </div>
  </div>

  {{-- Actions --}}
  <div class="bg-white rounded-2xl shadow border border-slate-100 p-4 space-y-3">
    <h3 class="text-sm font-semibold text-slate-800 mb-1">Actions</h3>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('borrowers.edit', $borrower) }}" class="btn btn-quiet bg-slate-100 hover:bg-slate-200">
        Edit
      </a>

      {{-- Status change --}}
      <form action="{{ route('borrowers.status', $borrower) }}" method="POST" class="flex items-center gap-2">
        @csrf
        @method('PATCH')
        <select name="status" class="border rounded-lg text-xs px-2 py-1 h-8">
          @foreach($statuses as $s)
            <option value="{{ $s }}" @selected($borrower->status === $s)>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
        <button class="btn btn-quiet text-xs bg-indigo-50 hover:bg-indigo-100">
          Update Status
        </button>
      </form>

      {{-- Archive --}}
      @if(!$borrower->is_archived)
        <form action="{{ route('borrowers.archive', $borrower) }}" method="POST">
          @csrf
          <button class="btn btn-quiet text-xs bg-slate-100 hover:bg-slate-200"
                  onclick="return confirm('Archive this borrower?')">
            Archive
          </button>
        </form>
      @else
        <form action="{{ route('borrowers.unarchive', $borrower) }}" method="POST">
          @csrf
          <button class="btn btn-quiet text-xs bg-emerald-50 hover:bg-emerald-100">
            Unarchive
          </button>
        </form>
      @endif

      {{-- Soft delete --}}
      <form action="{{ route('borrowers.destroy', $borrower) }}" method="POST">
        @csrf
        @method('DELETE')
        <button class="btn btn-quiet text-xs bg-red-50 hover:bg-red-100 text-red-700"
                onclick="return confirm('Soft delete this borrower?')">
          Delete (Soft)
        </button>
      </form>

      {{-- Force delete --}}
      <form action="{{ route('borrowers.forceDestroy', $borrower) }}" method="POST">
        @csrf
        @method('DELETE')
        <button class="btn btn-quiet text-xs bg-red-600 hover:bg-red-700 text-white"
                onclick="return confirm('Permanently delete this borrower? This cannot be undone.')">
          Force Delete
        </button>
      </form>
    </div>
  </div>

  {{-- Borrower Statistics --}}
  <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Loan Statistics</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="bg-slate-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Loans</div>
        <div class="text-2xl font-bold text-slate-900">{{ $totalLoans }}</div>
      </div>
      <div class="bg-blue-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Borrowed</div>
        <div class="text-2xl font-bold text-blue-700">₱{{ number_format($totalBorrowed, 2) }}</div>
      </div>
      <div class="bg-indigo-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Disbursed</div>
        <div class="text-2xl font-bold text-indigo-700">₱{{ number_format($totalDisbursed, 2) }}</div>
      </div>
      <div class="bg-emerald-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Paid</div>
        <div class="text-2xl font-bold text-emerald-700">₱{{ number_format($totalPaid, 2) }}</div>
      </div>
      <div class="bg-rose-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Penalties</div>
        <div class="text-2xl font-bold text-rose-700">₱{{ number_format($totalPenalties, 2) }}</div>
      </div>
      <div class="bg-amber-50 rounded-lg p-4">
        <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Outstanding Balance</div>
        <div class="text-2xl font-bold text-amber-700">₱{{ number_format($totalOutstanding, 2) }}</div>
      </div>
    </div>
  </div>

  {{-- Loan History --}}
  <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
      <h2 class="text-lg font-semibold text-slate-800">Loan History ({{ $totalLoans }})</h2>
    </div>
    @if($loans->count() > 0)
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
            <tr>
              <th class="px-4 py-3 text-left">Reference</th>
              <th class="px-4 py-3 text-right">Principal</th>
              <th class="px-4 py-3 text-right">Disbursed</th>
              <th class="px-4 py-3 text-right">Paid</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-left">Application Date</th>
              <th class="px-4 py-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($loans as $loan)
              @php
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
                $badgeClass = $statusColors[$loan->status] ?? 'bg-slate-100 text-slate-700';
              @endphp
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                  <a href="{{ route('loans.show', $loan) }}" class="text-indigo-600 hover:underline font-semibold">
                    {{ $loan->reference }}
                  </a>
                </td>
                <td class="px-4 py-3 text-right text-slate-800">
                  ₱{{ number_format($loan->principal_amount, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-slate-800">
                  ₱{{ number_format($loan->total_disbursed ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-emerald-600">
                  ₱{{ number_format($loan->total_paid ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                    {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-700">
                  {{ $loan->application_date?->format('M d, Y') ?? '—' }}
                </td>
                <td class="px-4 py-3">
                  <a href="{{ route('loans.show', $loan) }}" class="text-xs text-indigo-600 hover:underline">
                    View Details
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="px-6 py-8 text-center text-slate-500">
        <p>No loans found for this borrower.</p>
      </div>
    @endif
  </div>

  {{-- Payment History --}}
  <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-800">Payment History</h2>
          <p class="text-xs text-slate-500 mt-1">
            Approved: ₱{{ number_format($paymentStats['approved'], 2) }} |
            Pending: ₱{{ number_format($paymentStats['pending'], 2) }} |
            Rejected: ₱{{ number_format($paymentStats['rejected'], 2) }}
          </p>
        </div>
      </div>
    </div>
    @if($payments->count() > 0)
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
            <tr>
              <th class="px-4 py-3 text-left">Date</th>
              <th class="px-4 py-3 text-left">Loan Reference</th>
              <th class="px-4 py-3 text-right">Amount</th>
              <th class="px-4 py-3 text-right">Penalty</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-left">Receipt</th>
              <th class="px-4 py-3 text-left">Approved By</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($payments as $payment)
              @php
                $statusColors = [
                  'pending' => 'bg-yellow-100 text-yellow-800',
                  'approved' => 'bg-emerald-100 text-emerald-700',
                  'rejected' => 'bg-rose-100 text-rose-700',
                ];
                $badgeClass = $statusColors[$payment->status] ?? 'bg-slate-100 text-slate-700';
              @endphp
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-700">
                  {{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}
                </td>
                <td class="px-4 py-3">
                  @if($payment->loan)
                    <a href="{{ route('loans.show', $payment->loan) }}" class="text-indigo-600 hover:underline">
                      {{ $payment->loan->reference }}
                    </a>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-slate-800 font-semibold">
                  ₱{{ number_format($payment->amount, 2) }}
                </td>
                <td class="px-4 py-3 text-right text-rose-600">
                  ₱{{ number_format($payment->penalty_amount ?? 0, 2) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                    {{ ucfirst($payment->status) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  @if($payment->receiptDocument)
                    <a href="{{ route('documents.view', $payment->receiptDocument->id) }}"
                       target="_blank"
                       class="text-xs text-indigo-600 hover:underline">
                      View Receipt
                    </a>
                  @else
                    <span class="text-slate-400 text-xs">No receipt</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-slate-700 text-xs">
                  @if($payment->approvedBy)
                    {{ $payment->approvedBy->name }}
                  @elseif($payment->status === 'approved')
                    <span class="text-slate-400">System</span>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
        {{ $payments->links() }}
      </div>
    @else
      <div class="px-6 py-8 text-center text-slate-500">
        <p>No payments found for this borrower.</p>
      </div>
    @endif
  </div>
</div>
@endsection
