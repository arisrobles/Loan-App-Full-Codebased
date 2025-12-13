{{-- resources/views/loans/edit.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = 'Edit Loan — ' . $loan->reference;
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
        <h1 class="text-xl font-semibold">Edit Loan</h1>
        <p class="text-sm text-indigo-100">Update loan information for {{ $loan->reference }}</p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('loans.show', $loan) }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold">
          Back to Loan
        </a>
        <a href="{{ route('loans.index') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold">
          Back to Loans
        </a>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    <form action="{{ route('loans.update', $loan) }}" method="POST" class="space-y-6" id="loanForm">
      @csrf
      @method('PUT')

      {{-- Borrower Selection --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Borrower <span class="text-rose-500">*</span>
        </label>
        <select name="borrower_id" id="borrower_id" required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">Select a borrower...</option>
          @foreach($borrowers as $borrower)
            <option value="{{ $borrower->id }}" {{ old('borrower_id', $loan->borrower_id) == $borrower->id ? 'selected' : '' }}>
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
               value="{{ old('principal_amount', $loan->principal_amount) }}"
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
               value="{{ old('interest_rate', $loan->interest_rate * 100) }}"
               min="0" max="100" step="0.01"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-[11px] text-slate-500">Current: {{ number_format($loan->interest_rate * 100, 2) }}% per annum</p>
      </div>

      {{-- Tenor (Loan Term) --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Loan Term (Tenor in months) <span class="text-rose-500">*</span>
        </label>
        <input type="number" name="tenor" id="tenor"
               value="{{ old('tenor', $loan->repayments()->count()) }}"
               min="1" max="18" step="1" required
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-[11px] text-slate-500">Current: {{ $loan->repayments()->count() }} months | Range: 1-18 months (must be an integer)</p>
        <p class="mt-1 text-[10px] text-amber-600">
          ⚠️ Changing tenor or loan amount will regenerate the repayment schedule. This is only allowed if the loan has no approved payments.
        </p>
      </div>

      {{-- Application Date --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Application Date <span class="text-rose-500">*</span>
        </label>
        <input type="date" name="application_date" id="application_date"
               value="{{ old('application_date', $loan->application_date?->format('Y-m-d')) }}" required
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      {{-- Release Date --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Release Date
        </label>
        <input type="date" name="release_date" id="release_date"
               value="{{ old('release_date', $loan->release_date?->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      {{-- Penalty Settings --}}
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            Penalty Grace Days
          </label>
          <input type="number" name="penalty_grace_days" id="penalty_grace_days"
                 value="{{ old('penalty_grace_days', $loan->penalty_grace_days) }}"
                 min="0" step="1"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <p class="mt-1 text-[11px] text-slate-500">Current: {{ $loan->penalty_grace_days }} days</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">
            Penalty Daily Rate
          </label>
          <input type="number" name="penalty_daily_rate" id="penalty_daily_rate"
                 value="{{ old('penalty_daily_rate', $loan->penalty_daily_rate) }}"
                 min="0" max="1" step="0.000001"
                 class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <p class="mt-1 text-[11px] text-slate-500">Current: {{ number_format($loan->penalty_daily_rate, 6) }}</p>
        </div>
      </div>

      {{-- Remarks --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Remarks
        </label>
        <textarea name="remarks" id="remarks" rows="3"
                  class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('remarks', $loan->remarks) }}</textarea>
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
                   value="{{ old('application_latitude', $loan->application_latitude) }}"
                   step="0.00000001" min="-90" max="90"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-[11px] text-slate-500">Decimal format (e.g., 14.5995)</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Longitude
            </label>
            <input type="number" name="application_longitude" id="application_longitude"
                   value="{{ old('application_longitude', $loan->application_longitude) }}"
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
                 value="{{ old('application_location_address', $loan->application_location_address) }}"
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
                   value="{{ old('guarantor_full_name', $loan->guarantor?->full_name) }}"
                   maxlength="255"
                   class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">
              Guarantor Address
            </label>
            <input type="text" name="guarantor_address" id="guarantor_address"
                   value="{{ old('guarantor_address', $loan->guarantor?->address) }}"
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
              <option value="Single" {{ old('guarantor_civil_status', $loan->guarantor?->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
              <option value="Married" {{ old('guarantor_civil_status', $loan->guarantor?->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
              <option value="Divorced" {{ old('guarantor_civil_status', $loan->guarantor?->civil_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
              <option value="Widowed" {{ old('guarantor_civil_status', $loan->guarantor?->civil_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-500">Optional</p>
          </div>
        </div>
      </div>

      {{-- Read-only Information --}}
      <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Read-Only Information</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-600">Loan Reference:</span>
            <span class="font-semibold text-slate-900">{{ $loan->reference }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Status:</span>
            <span class="font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $loan->status)) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Maturity Date:</span>
            <span class="font-semibold text-slate-900">{{ $loan->maturity_date?->format('M d, Y') ?? 'N/A' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Total Disbursed:</span>
            <span class="font-semibold text-slate-900">₱{{ number_format($loan->total_disbursed, 2) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Total Paid:</span>
            <span class="font-semibold text-emerald-600">₱{{ number_format($loan->total_paid, 2) }}</span>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div class="pt-2 flex justify-end gap-2">
        <a href="{{ route('loans.show', $loan) }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Update Loan
        </button>
      </div>
    </form>
  </div>

@endsection

