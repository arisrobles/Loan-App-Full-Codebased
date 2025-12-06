@extends('layouts.app')

@php
  $pageTitle = 'Edit Repayment';
@endphp

@section('content')

  <div class="mb-6 rounded-2xl bg-gradient-to-r from-sky-600 via-sky-500 to-sky-400 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-semibold">Edit Repayment Schedule</h1>
        <p class="text-sm text-sky-50">
          {{ $loan->reference ?? ('Loan #' . $loan->id) }} — {{ $loan->borrower_name ?? 'N/A' }}
        </p>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <form method="POST" action="{{ route('repayments.update', [$loan, $repayment]) }}" class="space-y-4">
      @method('PUT')
      @include('repayments._form', ['submitLabel' => 'Update Schedule'])
    </form>
  </div>

@endsection