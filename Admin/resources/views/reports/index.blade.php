{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = 'Financial Reports';

  // Helper for money formatting with ( ) for negatives
  function mf_money($val) {
      $v = (float) ($val ?? 0);
      $formatted = number_format($v, 2);
      return $v < 0 ? '(' . str_replace('-', '', $formatted) . ')' : $formatted;
  }

  // Ensure variables exist (controller should provide them)
  $totalRevenue = $totalRevenue ?? 0;
  $totalCogs    = $totalCogs ?? 0;
  $totalExpense = $totalExpense ?? 0;
  $grossProfit  = $grossProfit ?? 0;
  $netProfit    = $netProfit ?? 0;

  $bsAssets     = $bsAssets ?? collect();
  $bsLiabs      = $bsLiabs ?? collect();
  $bsEquity     = $bsEquity ?? collect();
  $totalAssets  = $totalAssets ?? 0;
  $totalLiabs   = $totalLiabs ?? 0;
  $totalEquity  = $totalEquity ?? 0;
  $bsBalanced   = $bsBalanced ?? false;
  $bsRows       = $bsRows ?? collect();

  $cashIn       = $cashIn ?? 0;
  $cashOut      = $cashOut ?? 0;
  $cfNet        = $cfNet ?? 0;
  $cfByGroup    = $cfByGroup ?? [];

  // For P&L sections
  $revenues     = $revenues ?? collect();
  $cogs         = $cogs ?? collect();
  $expenses     = $expenses ?? collect();

  // other placeholders
  $otherIncomeLoss = $otherIncomeLoss ?? 0.00;
  $taxes = $taxes ?? 0.00;
  $netBeforeTax = ($netProfit ?? 0) + $taxes;
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#e0f2fe 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')

  {{-- HERO / TABS --}}
  <div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-800 text-white shadow-lg">
    <div class="px-6 py-5 flex flex-col gap-4">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-xl font-semibold">Financial Reports</h1>
          <p class="text-sm text-slate-200">
            Profit &amp; Loss, Balance Sheet, and Cash Flow views based on your posted transactions.
          </p>
          <p class="text-[11px] text-slate-300 mt-1">
            Period: {{ $from ?? '—' }} to {{ $to ?? '—' }}
          </p>
        </div>
        <div class="text-right">
          <div class="text-[11px] uppercase tracking-wide text-slate-300">Net Profit / (Loss)</div>
          <div class="text-2xl font-bold {{ ($netProfit ?? 0) >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
            ₱{{ mf_money($netProfit) }}
          </div>
        </div>
      </div>

      {{-- Tabs: P&L / Balance Sheet / Cash Flow --}}
      <div x-data="{ tab: 'pl' }" class="mt-1">
        <div class="inline-flex rounded-full bg-slate-900/60 p-1 text-xs font-semibold">
          <button
            @click="tab = 'pl'"
            :class="tab === 'pl' ? 'bg-white text-slate-900 shadow px-4 py-1.5 rounded-full' : 'text-slate-200 px-4 py-1.5 rounded-full hover:bg-slate-800/80'"
          >
            Profit &amp; Loss
          </button>
          <button
            @click="tab = 'bs'"
            :class="tab === 'bs' ? 'bg-white text-slate-900 shadow px-4 py-1.5 rounded-full' : 'text-slate-200 px-4 py-1.5 rounded-full hover:bg-slate-800/80'"
          >
            Balance Sheet
          </button>
          <button
            @click="tab = 'cf'"
            :class="tab === 'cf' ? 'bg-white text-slate-900 shadow px-4 py-1.5 rounded-full' : 'text-slate-200 px-4 py-1.5 rounded-full hover:bg-slate-800/80'"
          >
            Cash Flow
          </button>
        </div>

        {{-- FILTERS (same for all tabs) --}}
        <form action="{{ route('reports.index') }}" method="GET"
              class="mt-4 bg-white/10 border border-white/10 rounded-xl px-4 py-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
          <div class="md:col-span-3">
            <label class="block text-[11px] font-semibold text-slate-200 mb-1">From</label>
            <input type="date" name="from" value="{{ $from ?? '' }}"
                   class="w-full rounded-lg border border-slate-500/60 bg-slate-900/40 px-3 py-2 text-xs text-white">
          </div>
          <div class="md:col-span-3">
            <label class="block text-[11px] font-semibold text-slate-200 mb-1">To</label>
            <input type="date" name="to" value="{{ $to ?? '' }}"
                   class="w-full rounded-lg border border-slate-500/60 bg-slate-900/40 px-3 py-2 text-xs text-white">
          </div>
          <div class="md:col-span-4">
            <label class="block text-[11px] font-semibold text-slate-200 mb-1">Bank Account</label>
            <select name="bank_account_id"
                    class="w-full rounded-lg border border-slate-500/60 bg-slate-900/40 px-3 py-2 text-xs text-white">
              <option value="">All accounts</option>
              @foreach($bankAccounts as $ba)
                <option value="{{ $ba->id }}" @selected(($bankAccountId ?? '') == $ba->id)>
                  {{ $ba->code }} — {{ $ba->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="md:col-span-2 flex gap-2">
            <button class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-white text-slate-900 text-xs font-semibold hover:bg-slate-100">
              Apply
            </button>
            <a href="{{ route('reports.index') }}"
               class="mt-4 inline-flex items-center px-4 py-2 rounded-lg border border-slate-400/60 text-xs text-slate-100 hover:bg-slate-900/40">
              Reset
            </a>
          </div>
        </form>

        {{-- BODY TABS --}}
        <div class="mt-6 space-y-6">

          {{-- PROFIT & LOSS --}}
          <div x-show="tab === 'pl'" x-cloak>
            {{-- SUMMARY CARDS --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Total Revenue</div>
                <div class="text-xl font-bold text-emerald-600">₱{{ mf_money($totalRevenue) }}</div>
              </div>
              <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Cost of Revenue</div>
                <div class="text-xl font-bold text-rose-600">₱{{ mf_money($totalCogs) }}</div>
              </div>
              <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Operating Expenses</div>
                <div class="text-xl font-bold text-rose-600">₱{{ mf_money($totalExpense) }}</div>
              </div>
              <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Gross / Net</div>
                <div class="text-xs text-slate-600">
                  Gross Profit:
                  <span class="font-semibold {{ $grossProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    ₱{{ mf_money($grossProfit) }}
                  </span>
                </div>
                <div class="text-xs text-slate-600">
                  Net Profit:
                  <span class="font-semibold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    ₱{{ mf_money($netProfit) }}
                  </span>
                </div>
              </div>
            </div>

            {{-- P&L GRID (unchanged) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
              <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Profit and Losses</h2>
                <p class="text-[11px] text-slate-500">
                  Based on posted bank transactions mapped to Profit and Losses accounts in your Chart of Accounts.
                </p>
              </div>

              <div class="divide-y divide-slate-100 text-xs">

                {{-- Revenue --}}
                <div class="px-4 py-3">
                  <div class="flex items-center justify-between mb-1.5">
                    <div class="font-semibold text-slate-800">Revenue</div>
                    <div class="text-[11px] text-slate-500">(Revenue accounts)</div>
                  </div>

                  @if($revenues->isEmpty())
                    <p class="text-[11px] text-slate-400 italic">No revenue accounts for this period.</p>
                  @else
                    <table class="w-full text-xs">
                      <tbody>
                        @foreach($revenues as $row)
                          <tr class="border-b border-slate-100">
                            <td class="py-1 pr-2 text-slate-700">
                              <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                              <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                            </td>
                            <td class="py-1 px-2 text-right text-slate-700"></td>
                            <td class="py-1 pl-2 text-right font-semibold text-emerald-700">₱{{ mf_money($row->amount) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <td class="pt-1 text-[11px] font-semibold text-slate-600">Total Revenue</td>
                          <td></td>
                          <td class="pt-1 text-right font-bold text-emerald-700">₱{{ mf_money($totalRevenue) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  @endif
                </div>

                {{-- Cost of Revenue --}}
                <div class="px-4 py-3">
                  <div class="flex items-center justify-between mb-1.5">
                    <div class="font-semibold text-slate-800">Cost of Revenue</div>
                    <div class="text-[11px] text-slate-500">(COGS accounts)</div>
                  </div>

                  @if($cogs->isEmpty())
                    <p class="text-[11px] text-slate-400 italic">No cost-of-revenue accounts for this period.</p>
                  @else
                    <table class="w-full text-xs">
                      <tbody>
                        @foreach($cogs as $row)
                          <tr class="border-b border-slate-100">
                            <td class="py-1 pr-2 text-slate-700">
                              <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                              <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                            </td>
                            <td class="py-1 px-2 text-right text-slate-700"></td>
                            <td class="py-1 pl-2 text-right font-semibold text-rose-700">₱{{ mf_money($row->amount) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <td class="pt-1 text-[11px] font-semibold text-slate-600">Total Cost of Revenue</td>
                          <td></td>
                          <td class="pt-1 text-right font-bold text-rose-700">₱{{ mf_money($totalCogs) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  @endif
                </div>

                {{-- Gross Profit --}}
                <div class="px-4 py-3 bg-slate-50">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-[11px] font-semibold text-slate-700 uppercase">Gross Profit (Total Revenue − Total Direct Cost)</div>
                      <div class="text-[11px] text-slate-500">Reflects profit before operating expenses.</div>
                    </div>
                    <div class="text-sm font-bold {{ $grossProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">₱{{ mf_money($grossProfit) }}</div>
                  </div>
                </div>

                {{-- Operating Expenses --}}
                <div class="px-4 py-3">
                  <div class="flex items-center justify-between mb-1.5">
                    <div class="font-semibold text-slate-800">Operating Expenses</div>
                    <div class="text-[11px] text-slate-500">(Opex accounts)</div>
                  </div>

                  @if($expenses->isEmpty())
                    <p class="text-[11px] text-slate-400 italic">No operating expense accounts for this period.</p>
                  @else
                    <table class="w-full text-xs">
                      <tbody>
                        @foreach($expenses as $row)
                          <tr class="border-b border-slate-100">
                            <td class="py-1 pr-2 text-slate-700">
                              <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                              <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                            </td>
                            <td class="py-1 px-2 text-right text-slate-700"></td>
                            <td class="py-1 pl-2 text-right font-semibold text-rose-700">₱{{ mf_money($row->amount) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <td class="pt-1 text-[11px] font-semibold text-slate-600">Total Opex</td>
                          <td></td>
                          <td class="pt-1 text-right font-bold text-rose-700">₱{{ mf_money($totalExpense) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  @endif
                </div>

                {{-- Operating Profit --}}
                @php $operatingProfit = ($grossProfit ?? 0) - ($totalExpense ?? 0); @endphp
                <div class="px-4 py-3 bg-slate-50">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-[11px] font-semibold text-slate-700 uppercase">Operating Profit (Gross Profit − Opex)</div>
                    </div>
                    <div class="text-sm font-bold {{ $operatingProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">₱{{ mf_money($operatingProfit) }}</div>
                  </div>
                </div>

                {{-- Other / Taxes / Net --}}
                <div class="px-4 py-3">
                  <div class="flex items-center justify-between">
                    <div class="text-[11px] text-slate-500">Other Income / Expenses</div>
                    <div class="text-sm font-bold text-slate-800">₱{{ mf_money($otherIncomeLoss) }}</div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-slate-50">
                  <div class="flex items-center justify-between">
                    <div class="text-[11px] font-semibold text-slate-700 uppercase">Net Profit Before Tax</div>
                    <div class="text-sm font-bold {{ $netBeforeTax >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">₱{{ mf_money($netBeforeTax) }}</div>
                  </div>
                </div>

                <div class="px-4 py-4 bg-slate-900 text-white">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-[11px] font-semibold uppercase tracking-wide">Net Profit</div>
                      <div class="text-[11px] text-slate-300">Final profit available to the business after all expenses.</div>
                    </div>
                    <div class="text-xl font-bold {{ $netProfit >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">₱{{ mf_money($netProfit) }}</div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- BALANCE SHEET --}}
          <div x-show="tab === 'bs'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

              {{-- Left: Assets --}}
              <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                  <h2 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Balance Sheet — Assets</h2>
                  <p class="text-[11px] text-slate-500">Current & non-current assets (based on posted transactions mapped to Balance Sheets accounts).</p>
                </div>

                <div class="px-4 py-3 text-xs">
                  @if($bsAssets->isEmpty())
                    <p class="text-[11px] text-slate-400 italic">No assets accounts found for the selected period.</p>
                  @else
                    <table class="w-full text-xs">
                      <tbody>
                        @foreach($bsAssets as $row)
                          <tr class="border-b border-slate-100">
                            <td class="py-2 pr-2 text-slate-700">
                              <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                              <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                            </td>
                            <td class="py-2 pl-2 text-right font-semibold text-slate-800">₱{{ mf_money($row->amount) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <td class="pt-2 text-[11px] font-semibold text-slate-600">Total Assets</td>
                          <td class="pt-2 text-right font-bold text-slate-800">₱{{ mf_money($totalAssets) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  @endif
                </div>
              </div>

              {{-- Right: Liabilities & Equity --}}
              <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                  <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Balance Sheet — Liabilities</h2>
                    <p class="text-[11px] text-slate-500">Current & long-term liabilities.</p>
                  </div>
                  <div class="px-4 py-3 text-xs">
                    @if($bsLiabs->isEmpty())
                      <p class="text-[11px] text-slate-400 italic">No liabilities accounts for this period.</p>
                    @else
                      <table class="w-full text-xs">
                        <tbody>
                          @foreach($bsLiabs as $row)
                            <tr class="border-b border-slate-100">
                              <td class="py-2 pr-2 text-slate-700">
                                <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                                <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                              </td>
                              <td class="py-2 pl-2 text-right font-semibold text-slate-800">₱{{ mf_money($row->amount) }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                        <tfoot>
                          <tr>
                            <td class="pt-2 text-[11px] font-semibold text-slate-600">Total Liabilities</td>
                            <td class="pt-2 text-right font-bold text-slate-800">₱{{ mf_money($totalLiabs) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                    @endif
                  </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                  <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Balance Sheet — Equity</h2>
                    <p class="text-[11px] text-slate-500">Owner's equity and retained earnings.</p>
                  </div>
                  <div class="px-4 py-3 text-xs">
                    @if($bsEquity->isEmpty())
                      <p class="text-[11px] text-slate-400 italic">No equity accounts for this period.</p>
                    @else
                      <table class="w-full text-xs">
                        <tbody>
                          @foreach($bsEquity as $row)
                            <tr class="border-b border-slate-100">
                              <td class="py-2 pr-2 text-slate-700">
                                <span class="font-mono text-[10px] text-slate-400">{{ $row->code }}</span><br>
                                <span class="font-semibold text-slate-800">{{ $row->name }}</span>
                              </td>
                              <td class="py-2 pl-2 text-right font-semibold text-slate-800">₱{{ mf_money($row->amount) }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                        <tfoot>
                          <tr>
                            <td class="pt-2 text-[11px] font-semibold text-slate-600">Total Equity</td>
                            <td class="pt-2 text-right font-bold text-slate-800">₱{{ mf_money($totalEquity) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                    @endif
                  </div>
                </div>

                {{-- Balancing check --}}
                <div class="bg-white rounded-xl border border-slate-100 p-3 text-xs">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-semibold text-slate-700">Assets = Liabilities + Equity</div>
                      <div class="text-[11px] text-slate-500">Balancing check for the selected period</div>
                    </div>
                    <div class="text-sm font-bold {{ $bsBalanced ? 'text-emerald-700' : 'text-rose-700' }}">
                      @if($bsBalanced)
                        Balanced
                      @else
                        Not Balanced
                      @endif
                    </div>
                  </div>
                  <div class="mt-2 text-[11px] text-slate-600">
                    <div>Total Assets: <strong>₱{{ mf_money($totalAssets) }}</strong></div>
                    <div>Total Liabilities + Equity: <strong>₱{{ mf_money($totalLiabs + $totalEquity) }}</strong></div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- CASH FLOW --}}
          <div x-show="tab === 'cf'" x-cloak>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
              <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Cash Flow Summary</h2>
                <p class="text-[11px] text-slate-500">Cash inflows and outflows for the selected period (posted transactions only).</p>
              </div>

              <div class="px-4 py-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                  <div class="bg-white rounded-lg border p-3">
                    <div class="text-[11px] text-slate-500">Cash In</div>
                    <div class="text-xl font-bold text-emerald-700">₱{{ mf_money($cashIn) }}</div>
                  </div>
                  <div class="bg-white rounded-lg border p-3">
                    <div class="text-[11px] text-slate-500">Cash Out</div>
                    <div class="text-xl font-bold text-rose-700">₱{{ mf_money($cashOut) }}</div>
                  </div>
                  <div class="bg-white rounded-lg border p-3">
                    <div class="text-[11px] text-slate-500">Net Cash</div>
                    <div class="text-xl font-bold {{ $cfNet >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">₱{{ mf_money($cfNet) }}</div>
                  </div>
                </div>

                {{-- Breakdown by group --}}
                <div class="mb-3">
                  <div class="text-sm font-semibold text-slate-700 mb-2">Breakdown by Account Group</div>
                  @if(empty($cfByGroup))
                    <p class="text-[11px] text-slate-400 italic">No cash flow items for selected period.</p>
                  @else
                    <table class="w-full text-xs">
                      <thead class="text-slate-500 text-[11px]">
                        <tr>
                          <th class="text-left py-1">Group</th>
                          <th class="text-right py-1">In</th>
                          <th class="text-right py-1">Out</th>
                          <th class="text-right py-1">Net</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($cfByGroup as $group => $vals)
                          <tr class="border-b border-slate-100">
                            <td class="py-2">{{ $group }}</td>
                            <td class="py-2 text-right">₱{{ mf_money($vals['in']) }}</td>
                            <td class="py-2 text-right">₱{{ mf_money($vals['out']) }}</td>
                            <td class="py-2 text-right font-semibold {{ ($vals['net'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                              ₱{{ mf_money($vals['net']) }}
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @endif
                </div>

                <div class="text-[11px] text-slate-500">
                  Note: Cash flow here is a simple aggregation of `received` and `spent` from posted bank transactions within the period. For a full indirect cash flow statement you'll need opening/closing balances and non-cash adjustments (depreciation).
                </div>
              </div>
            </div>
          </div>

        </div>
      </div> {{-- x-data wrapper --}}
    </div>
  </div>

@endsection