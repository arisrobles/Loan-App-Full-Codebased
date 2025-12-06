@extends('layouts.app')

@php
  /** @var \App\Models\ChartOfAccount $account */
  $pageTitle = 'Edit Account: ' . $account->code;
@endphp

@section('content')
<div class="mb-4 flex items-center justify-between">
  <div>
    <h1 class="text-xl font-semibold text-slate-800">
      Edit Account
      <span class="font-mono text-xs text-slate-500 ml-1">{{ $account->code }}</span>
    </h1>
    <p class="text-sm text-slate-500">{{ $account->name }}</p>
  </div>
  <a href="{{ route('chart-of-accounts.index') }}"
     class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white hover:bg-slate-50">
    Back to list
  </a>
</div>

@if ($errors->any())
  <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800">
    Please fix the errors below.
  </div>
@endif

<form action="{{ route('chart-of-accounts.update', $account) }}" method="POST"
      class="bg-white rounded-xl border border-slate-200 px-4 py-4 space-y-4">
  @csrf
  @method('PUT')

  @include('chart-of-accounts._form')

  <div class="flex justify-end gap-2 pt-2">
    <a href="{{ route('chart-of-accounts.index') }}"
       class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 bg-white hover:bg-slate-50">
      Cancel
    </a>
    <button type="submit"
       class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
      Update
    </button>
  </div>
</form>
@endsection