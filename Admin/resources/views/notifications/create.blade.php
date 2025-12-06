@extends('layouts.app')

@php
  $pageTitle = 'Send Notification';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
    <p class="text-slate-300 text-sm">Send a custom notification to a borrower</p>
  </div>
</div>

{{-- FLASH MESSAGES --}}
@if($errors->any())
  <div class="mb-6 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
    <ul class="list-disc list-inside">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- FORM --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
  <form method="POST" action="{{ route('notifications.store') }}">
    @csrf

    <div class="space-y-6">
      {{-- Borrower Selection --}}
      <div>
        <label for="borrower_id" class="block text-sm font-semibold text-slate-700 mb-2">
          Borrower <span class="text-rose-600">*</span>
        </label>
        <select name="borrower_id" id="borrower_id" required
                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">Select a borrower...</option>
          @foreach($borrowers as $borrower)
            <option value="{{ $borrower->id }}" {{ old('borrower_id') == $borrower->id ? 'selected' : '' }}>
              {{ $borrower->full_name }} ({{ $borrower->email }})
            </option>
          @endforeach
        </select>
      </div>

      {{-- Loan Selection (Optional) --}}
      <div>
        <label for="loan_id" class="block text-sm font-semibold text-slate-700 mb-2">
          Loan (Optional)
        </label>
        <select name="loan_id" id="loan_id"
                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option value="">No specific loan</option>
          @foreach($loans as $loan)
            <option value="{{ $loan->id }}" {{ old('loan_id') == $loan->id ? 'selected' : '' }}>
              {{ $loan->reference }} - {{ $loan->borrower_name }} ({{ $loan->status }})
            </option>
          @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">If selected, the loan must belong to the selected borrower</p>
      </div>

      {{-- Type --}}
      <div>
        <label for="type" class="block text-sm font-semibold text-slate-700 mb-2">
          Type <span class="text-rose-600">*</span>
        </label>
        <select name="type" id="type" required
                class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          @foreach($types as $value => $label)
            <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Title --}}
      <div>
        <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
          Title <span class="text-rose-600">*</span>
        </label>
        <input type="text" name="title" id="title" required
               value="{{ old('title') }}"
               maxlength="255"
               placeholder="e.g., Payment Reminder"
               class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      {{-- Message --}}
      <div>
        <label for="message" class="block text-sm font-semibold text-slate-700 mb-2">
          Message <span class="text-rose-600">*</span>
        </label>
        <textarea name="message" id="message" required rows="5"
                  maxlength="1000"
                  placeholder="Enter notification message..."
                  class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('message') }}</textarea>
        <p class="text-xs text-slate-500 mt-1">Maximum 1000 characters</p>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex items-center justify-end gap-4">
      <a href="{{ route('notifications.index') }}"
         class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold">
        Cancel
      </a>
      <button type="submit"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
        Send Notification
      </button>
    </div>
  </form>
</div>

@endsection

