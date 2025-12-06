{{-- resources/views/borrowers/edit.blade.php --}}
@extends('layouts.app')

@php
  use App\Models\Borrower;
  $pageTitle = 'Edit Borrower';
  $statuses  = Borrower::STATUSES ?? ['active','inactive','delinquent','closed','blacklisted'];
@endphp

@section('content')
<div class="max-w-3xl mx-auto">
  <div class="mb-4 flex items-center justify-between">
    <a href="{{ route('borrowers.show', $borrower) }}" class="text-sm text-indigo-600 hover:underline">
      ← Back to Profile
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6">
    <h1 class="text-lg font-semibold text-slate-900 mb-4">Edit Borrower</h1>

    @if($errors->any())
      <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-800 text-sm border border-red-200">
        <ul class="list-disc ml-4">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('borrowers.update', $borrower) }}" method="POST" class="space-y-4">
      @csrf
      @method('PUT')

      <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
          <input
            type="text"
            name="full_name"
            value="{{ old('full_name', $borrower->full_name) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
          <input
            type="email"
            name="email"
            value="{{ old('email', $borrower->email) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
          <input
            type="text"
            name="phone"
            value="{{ old('phone', $borrower->phone) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
          <input
            type="text"
            name="address"
            value="{{ old('address', $borrower->address) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Sex</label>
          <select
            name="sex"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
            <option value="">Select</option>
            <option value="Male" @selected(old('sex', $borrower->sex) === 'Male')>Male</option>
            <option value="Female" @selected(old('sex', $borrower->sex) === 'Female')>Female</option>
            <option value="Prefer not to say" @selected(old('sex', $borrower->sex) === 'Prefer not to say')>
              Prefer not to say
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Occupation</label>
          <input
            type="text"
            name="occupation"
            value="{{ old('occupation', $borrower->occupation) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Birthday</label>
          <input
            type="date"
            name="birthday"
            value="{{ old('birthday', optional($borrower->birthday)->format('Y-m-d')) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Monthly Income</label>
          <input
            type="number"
            step="0.01"
            min="0"
            name="monthly_income"
            value="{{ old('monthly_income', $borrower->monthly_income) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Civil Status</label>
          <input
            type="text"
            name="civil_status"
            value="{{ old('civil_status', $borrower->civil_status) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Reference No.</label>
          <input
            type="text"
            name="reference_no"
            value="{{ old('reference_no', $borrower->reference_no) }}"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
          <select
            name="status"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
            <option value="">Select</option>
            @foreach($statuses as $s)
              <option value="{{ $s }}" @selected(old('status', $borrower->status) === $s)>
                {{ ucfirst($s) }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('borrowers.show', $borrower) }}" class="btn btn-quiet bg-slate-100 hover:bg-slate-200">
          Cancel
        </a>
        <button type="submit" class="btn btn-brand">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
@endsection