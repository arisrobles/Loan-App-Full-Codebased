@isset($account)
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- CODE --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Code <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="code"
      value="{{ old('code', $account->code) }}"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
      required
    >
    @error('code')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- NAME --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Name <span class="text-red-500">*</span>
    </label>
    <input
      type="text"
      name="name"
      value="{{ old('name', $account->name) }}"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
      required
    >
    @error('name')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- DESCRIPTION --}}
  <div class="md:col-span-2">
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Description
    </label>
    <textarea
      name="description"
      rows="2"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
    >{{ old('description', $account->description) }}</textarea>
    @error('description')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- REPORT --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Report <span class="text-red-500">*</span>
    </label>
    <select
      name="report"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
      required
    >
      <option value="">Select report</option>
      @foreach($reports as $report)
        <option
          value="{{ $report }}"
          @selected(old('report', $account->report) === $report)
        >
          {{ $report }}
        </option>
      @endforeach
    </select>
    @error('report')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- GROUP --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Group <span class="text-red-500">*</span>
    </label>
    <select
      name="group_account"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
      required
    >
      <option value="">Select group</option>
      @foreach($groups as $group)
        <option
          value="{{ $group }}"
          @selected(old('group_account', $account->group_account) === $group)
        >
          {{ $group }}
        </option>
      @endforeach
    </select>
    @error('group_account')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- NORMAL BALANCE --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Normal Balance
    </label>
    <select
      name="normal_balance"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
    >
      <option value="">(None)</option>
      @foreach($normalBalances as $nb)
        <option
          value="{{ $nb }}"
          @selected(old('normal_balance', $account->normal_balance) === $nb)
        >
          {{ $nb }}
        </option>
      @endforeach
    </select>
    @error('normal_balance')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- DEBIT EFFECT --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Debit Effect
    </label>
    <select
      name="debit_effect"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
    >
      <option value="">(None)</option>
      @foreach($effects as $eff)
        <option
          value="{{ $eff }}"
          @selected(old('debit_effect', $account->debit_effect) === $eff)
        >
          {{ $eff }}
        </option>
      @endforeach
    </select>
    @error('debit_effect')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- CREDIT EFFECT --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Credit Effect
    </label>
    <select
      name="credit_effect"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
    >
      <option value="">(None)</option>
      @foreach($effects as $eff)
        <option
          value="{{ $eff }}"
          @selected(old('credit_effect', $account->credit_effect) === $eff)
        >
          {{ $eff }}
        </option>
      @endforeach
    </select>
    @error('credit_effect')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- SORT ORDER --}}
  <div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">
      Sort Order
    </label>
    <input
      type="number"
      name="sort_order"
      value="{{ old('sort_order', $account->sort_order ?? 0) }}"
      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
      min="0"
    >
    @error('sort_order')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  {{-- ACTIVE TOGGLE --}}
  <div class="flex items-center gap-2 mt-1 md:col-span-2">
    <input
      type="checkbox"
      name="is_active"
      value="1"
      id="is_active"
      @checked(old('is_active', $account->is_active ?? true))
    >
    <label for="is_active" class="text-xs font-semibold text-slate-600">
      Active
    </label>
    @error('is_active')
      <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>
</div>
@endisset