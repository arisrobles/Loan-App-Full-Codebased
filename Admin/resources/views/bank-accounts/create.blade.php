{{-- resources/views/bank-accounts/create.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = 'Add Bank Account';
@endphp

@section('content')

  @if($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800 text-white shadow-lg">
    <div class="px-6 py-5 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Add Bank Account</h1>
        <p class="text-sm text-indigo-100">Register a new masterfund bank account.</p>
      </div>
      <a href="{{ route('bank-accounts.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-xs font-semibold">
        Back to list
      </a>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-xl">
    <form action="{{ route('bank-accounts.store') }}" method="POST" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Code <span class="text-rose-500">*</span></label>
        <input type="text" name="code" value="{{ old('code') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Timezone</label>
        <input type="text" name="timezone" value="{{ old('timezone', 'Asia/Manila') }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <p class="mt-1 text-[11px] text-slate-500">Example: Asia/Manila</p>
      </div>

      <div class="pt-2 flex justify-end gap-2">
        <a href="{{ route('bank-accounts.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Cancel
        </a>
        <button class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Save
        </button>
      </div>
    </form>
  </div>

@endsection