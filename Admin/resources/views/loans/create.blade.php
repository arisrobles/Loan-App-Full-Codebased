{{-- resources/views/loans/create.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = 'Create New Loan';
@endphp

@section('content')

  @if($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800 text-white shadow-lg">
    <div class="px-6 py-5 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Create New Loan</h1>
        <p class="text-sm text-indigo-100">Create a new loan application with automatic repayment schedule.</p>
      </div>
      <a href="{{ route('loans.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold">
        Back to loans
      </a>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    <form action="{{ route('loans.store') }}" method="POST" class="space-y-6" id="loanForm">
      @csrf

      {{-- Borrower Selection --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Borrower <span class="text-rose-500">*</span>
        </label>
        <select name="borrower_id" id="borrower_id" required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">Select a borrower...</option>
          @foreach($borrowers as $borrower)
            <option value="{{ $borrower->id }}" {{ old('borrower_id') == $borrower->id ? 'selected' : '' }}>
              {{ $borrower->full_name }}
              @if($borrower->email) ({{ $borrower->email }}) @endif
              @if($borrower->phone) - {{ $borrower->phone }} @endif
            </option>
          @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-500">Only active, non-blacklisted borrowers are shown</p>
      </div>

      {{-- Principal Amount --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Loan Amount (₱) <span class="text-rose-500">*</span>
        </label>
        <input type="number" name="principal_amount" id="principal_amount"
               value="{{ old('principal_amount') }}"
               min="3500" max="50000" step="0.01" required
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-[11px] text-slate-500">Minimum: ₱3,500 | Maximum: ₱50,000</p>
      </div>

      {{-- Interest Rate --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Interest Rate (% per annum)
        </label>
        <input type="number" name="interest_rate" id="interest_rate"
               value="{{ old('interest_rate', \App\Constants\LoanDefaults::INTEREST_RATE_PERCENT) }}"
               min="0" max="100" step="0.01"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-[11px] text-slate-500">Default: {{ \App\Constants\LoanDefaults::INTEREST_RATE_PERCENT }}% per annum</p>
      </div>

      {{-- Tenor --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Tenor (Months) <span class="text-rose-500">*</span>
        </label>
        <input type="number" name="tenor" id="tenor" required
               value="{{ old('tenor') }}"
               min="1" max="18" step="1"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-[11px] text-slate-500">Range: 1-18 months (must be an integer)</p>
      </div>

      {{-- Application Date --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Application Date <span class="text-rose-500">*</span>
        </label>
        <input type="date" name="application_date" id="application_date"
               value="{{ old('application_date', date('Y-m-d')) }}" required
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      {{-- Penalty Settings --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            Penalty Grace Days
          </label>
          <input type="number" name="penalty_grace_days" id="penalty_grace_days"
                 value="{{ old('penalty_grace_days', \App\Constants\LoanDefaults::PENALTY_GRACE_DAYS) }}"
                 min="0" step="1"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <p class="mt-1 text-[11px] text-slate-500">Default: {{ \App\Constants\LoanDefaults::PENALTY_GRACE_DAYS }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            Penalty Daily Rate
          </label>
          <input type="number" name="penalty_daily_rate" id="penalty_daily_rate"
                 value="{{ old('penalty_daily_rate', \App\Constants\LoanDefaults::PENALTY_DAILY_RATE) }}"
                 min="0" max="1" step="0.000001"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <p class="mt-1 text-[11px] text-slate-500">Default: {{ \App\Constants\LoanDefaults::PENALTY_DAILY_RATE }} (0.1% per day)</p>
        </div>
      </div>

      {{-- Remarks --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Remarks
        </label>
        <textarea name="remarks" id="remarks" rows="3"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('remarks') }}</textarea>
      </div>

      {{-- Location Information (Optional) --}}
      <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Application Location (Optional)</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Latitude
            </label>
            <input type="number" name="application_latitude" id="application_latitude"
                   value="{{ old('application_latitude') }}"
                   step="0.00000001" min="-90" max="90"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-[11px] text-slate-500">Decimal format (e.g., 14.5995)</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Longitude
            </label>
            <input type="number" name="application_longitude" id="application_longitude"
                   value="{{ old('application_longitude') }}"
                   step="0.00000001" min="-180" max="180"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-[11px] text-slate-500">Decimal format (e.g., 120.9842)</p>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            Location Address
          </label>
          <input type="text" name="application_location_address" id="application_location_address"
                 value="{{ old('application_location_address') }}"
                 maxlength="255"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <p class="mt-1 text-[11px] text-slate-500">Full address where application was submitted</p>
        </div>
      </div>

      {{-- Guarantor Information (Optional) --}}
      <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Guarantor Information (Optional)</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Guarantor Full Name
            </label>
            <input type="text" name="guarantor_full_name" id="guarantor_full_name"
                   value="{{ old('guarantor_full_name') }}"
                   maxlength="255"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Guarantor Address
            </label>
            <input type="text" name="guarantor_address" id="guarantor_address"
                   value="{{ old('guarantor_address') }}"
                   maxlength="255"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Guarantor Civil Status
            </label>
            <select name="guarantor_civil_status" id="guarantor_civil_status"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Select civil status...</option>
              <option value="Single" {{ old('guarantor_civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
              <option value="Married" {{ old('guarantor_civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
              <option value="Divorced" {{ old('guarantor_civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
              <option value="Widowed" {{ old('guarantor_civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-500">Optional</p>
          </div>
        </div>
      </div>

      {{-- Preview Section (calculated values) --}}
      <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Loan Preview</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-600">Monthly Payment (EMI):</span>
            <span class="font-semibold text-slate-900" id="preview_emi">₱0.00</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Total Amount:</span>
            <span class="font-semibold text-slate-900" id="preview_total">₱0.00</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Total Interest:</span>
            <span class="font-semibold text-slate-900" id="preview_interest">₱0.00</span>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="pt-2 flex justify-end gap-2">
        <a href="{{ route('loans.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Create Loan
        </button>
      </div>
    </form>
  </div>

@endsection

@section('scripts')
<script>
  // Calculate and preview EMI
  function calculatePreview() {
    const principal = parseFloat(document.getElementById('principal_amount')?.value || 0);
    const interestRate = parseFloat(document.getElementById('interest_rate')?.value || 24);
    const tenor = parseInt(document.getElementById('tenor')?.value || 0);

    if (principal >= 3500 && principal <= 50000 && tenor > 0) {
      const monthlyRate = interestRate / 12 / 100;
      let emi = 0;

      if (monthlyRate > 0) {
        const numerator = principal * monthlyRate * Math.pow(1 + monthlyRate, tenor);
        const denominator = Math.pow(1 + monthlyRate, tenor) - 1;
        emi = numerator / denominator;
      } else {
        emi = principal / tenor;
      }

      emi = Math.round(emi * 100) / 100;
      const totalAmount = emi * tenor;
      const totalInterest = totalAmount - principal;

      document.getElementById('preview_emi').textContent = '₱' + emi.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      document.getElementById('preview_total').textContent = '₱' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
      document.getElementById('preview_interest').textContent = '₱' + totalInterest.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
      document.getElementById('preview_emi').textContent = '₱0.00';
      document.getElementById('preview_total').textContent = '₱0.00';
      document.getElementById('preview_interest').textContent = '₱0.00';
    }
  }

  // Add event listeners
  document.getElementById('principal_amount')?.addEventListener('input', calculatePreview);
  document.getElementById('interest_rate')?.addEventListener('input', calculatePreview);
  document.getElementById('tenor')?.addEventListener('change', calculatePreview);

  // Calculate on page load
  calculatePreview();
</script>
@endsection

