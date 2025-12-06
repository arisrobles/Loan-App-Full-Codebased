@extends('layouts.app')

@php
  $pageTitle = 'Add Repayment';
@endphp

@section('content')

  <div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-emerald-400 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">Add Repayment Schedule</h1>
        <p class="text-sm text-emerald-50">
          {{ $loan->reference ?? ('Loan #' . $loan->id) }} — {{ $loan->borrower_name ?? 'N/A' }}
        </p>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <form method="POST" action="{{ route('repayments.store', $loan) }}" class="space-y-4">
      @include('repayments._form', ['submitLabel' => 'Create Schedule'])
    </form>
  </div>

@endsection