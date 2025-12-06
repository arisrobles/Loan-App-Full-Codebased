@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Due Date <span class="text-red-500">*</span>
    </label>
    <input type="date"
           name="due_date"
           value="{{ old('due_date', optional($repayment->due_date)->format('Y-m-d')) }}"
           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
           required>
    @error('due_date')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Amount Due <span class="text-red-500">*</span>
    </label>
    <input type="number"
           step="0.01"
           min="0"
           name="amount_due"
           value="{{ old('amount_due', $repayment->amount_due) }}"
           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
           required>
    @error('amount_due')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Amount Paid
    </label>
    <input type="number"
           step="0.01"
           min="0"
           name="amount_paid"
           value="{{ old('amount_paid', $repayment->amount_paid) }}"
           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    @error('amount_paid')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Remarks
    </label>
    <textarea name="remarks"
              rows="3"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('remarks', $repayment->remarks ?? $repayment->note ?? '') }}</textarea>
    @error('remarks')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>
</div>

<div class="mt-4 flex gap-2">
  <button class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
    {{ $submitLabel ?? 'Save' }}
  </button>
  <a href="{{ route('repayments.index', $loan) }}"
     class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
    Cancel
  </a>
</div>