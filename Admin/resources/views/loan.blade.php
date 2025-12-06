@extends('layouts.app')

@php
  $pageTitle = 'Loans Overview';

  $statusOptions = [
    ''                 => 'All statuses',
    'new_application'  => 'New Application',
    'under_review'     => 'Under Review',
    'approved'         => 'Approved',
    'for_release'      => 'For Release',
    'disbursed'        => 'Disbursed',
    'closed'           => 'Closed',
    'rejected'         => 'Rejected',
    'cancelled'        => 'Cancelled',
    'restructured'     => 'Restructured',
  ];

  $statusColors = [
    'new_application'  => 'bg-slate-100 text-slate-700',
    'under_review'     => 'bg-cyan-100 text-cyan-800',
    'approved'         => 'bg-emerald-100 text-emerald-700',
    'for_release'      => 'bg-sky-100 text-sky-800',
    'disbursed'        => 'bg-indigo-100 text-indigo-700',
    'closed'           => 'bg-slate-200 text-slate-800',
    'rejected'         => 'bg-rose-100 text-rose-700',
    'cancelled'        => 'bg-pink-100 text-pink-700',
    'restructured'     => 'bg-purple-100 text-purple-700',
  ];
@endphp

@section('head')
  <style>
    html,body {
      font-family:'Inter',sans-serif;
      background:linear-gradient(180deg,#f9fafb 0%,#eef2ff 100%);
      color:#0f172a;
    }
  </style>
@endsection

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-xl font-semibold">Loan Pipeline</h1>
      <p class="text-sm text-slate-200">
        Applications, approvals, releases, and active disbursements
      </p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('loans.create') }}"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold shadow">
        + Create Loan
      </a>
      <a href="#filters"
         class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-xs font-semibold shadow">
        Filter Loans
      </a>
    </div>
  </div>
</div>

  {{-- METRIC TILES --}}
  <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">New Applications</div>
      <div class="text-xl font-bold text-slate-800">{{ number_format($summary['new_application'] ?? 0) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Under Review</div>
      <div class="text-xl font-bold text-cyan-600">{{ number_format($summary['under_review'] ?? 0) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Approved</div>
      <div class="text-xl font-bold text-emerald-600">{{ number_format($summary['approved'] ?? 0) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">For Release</div>
      <div class="text-xl font-bold text-sky-600">{{ number_format($summary['for_release'] ?? 0) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Disbursed</div>
      <div class="text-xl font-bold text-indigo-600">{{ number_format($summary['disbursed'] ?? 0) }}</div>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
      <div class="text-[11px] font-semibold text-slate-500 uppercase mb-1">Loans with Missed Due</div>
      <div class="text-xl font-bold {{ ($summary['missed'] ?? 0) > 0 ? 'text-rose-600' : 'text-slate-800' }}">
        {{ number_format($summary['missed'] ?? 0) }}
      </div>
    </div>
  </div>

  {{-- CHART + MISSED PANEL --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
    {{-- Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-800">Loan Applications by Month</h2>
          <p class="text-[11px] text-slate-500">
            Last {{ $filters['months'] ?? 6 }} month(s) by status
          </p>
        </div>
      </div>
      <div class="h-64">
        <canvas id="loanStatusChart"
                data-labels='@json($chartLabels)'
                data-datasets='@json($chartDatasets)'></canvas>
      </div>
    </div>

    {{-- Missed repayments --}}
    <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-4">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-semibold text-slate-800">Missed Repayments (Top 5)</h2>
          <p class="text-[11px] text-slate-500">
            Overdue schedules with suggested penalties
          </p>
        </div>
      </div>

      @if (count($missed))
        <ul class="space-y-3 text-xs">
          @foreach ($missed as $m)
            <li class="border border-rose-50 rounded-xl px-3 py-2.5 bg-rose-50/40">
              <div class="flex items-center justify-between mb-1">
                <span class="font-semibold text-slate-800">
                  {{ $m['reference'] ?? 'Loan #' . ($m['repayment_id'] ?? '') }}
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-semibold">
                  {{ $m['days_overdue'] }}d overdue
                </span>
              </div>
              <div class="text-[11px] text-slate-600 mb-1">
                Borrower: <span class="font-medium">{{ $m['borrower_name'] ?? 'N/A' }}</span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-slate-600">
                <span>Overdue: <span class="font-semibold text-rose-600">₱{{ number_format((float)$m['overdue_amount'], 2) }}</span></span>
                <span>Suggested Penalty: <span class="font-semibold text-rose-600">₱{{ number_format((float)$m['suggested_penalty'], 2) }}</span></span>
              </div>
              <div class="mt-1 text-[11px] text-slate-500">
                Due: <span class="font-medium">{{ $m['due_date'] }}</span>
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-[11px] text-slate-400 italic mt-2">
          No missed repayments detected based on current data.
        </p>
      @endif
    </div>
  </div>

  {{-- FILTERS --}}
  <div id="filters" class="mb-6 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <form action="{{ route('loans.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
      {{-- Search --}}
      <div class="md:col-span-4">
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Search
        </label>
        <input type="text"
               name="q"
               value="{{ $filters['q'] ?? '' }}"
               placeholder="Reference / Borrower / Remarks"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      {{-- Status --}}
      <div class="md:col-span-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Status
        </label>
        <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
          @foreach ($statusOptions as $value => $label)
            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Months --}}
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Chart Months
        </label>
        <input type="number"
               name="months"
               min="1" max="24"
               value="{{ $filters['months'] ?? 6 }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      {{-- Per page --}}
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">
          Per Page
        </label>
        <input type="number"
               name="per_page"
               min="5" max="100"
               value="{{ $filters['per_page'] ?? 10 }}"
               class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
      </div>

      <div class="md:col-span-1 flex gap-2">
        <button class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
          Apply
        </button>
        <a href="{{ route('loans.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Reset
        </a>
      </div>
    </form>
  </div>

  {{-- LOANS TABLE --}}
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide border-b border-slate-100">
          <tr>
            <th class="px-3 py-2 text-left">#</th>
            <th class="px-3 py-2 text-left">Reference</th>
            <th class="px-3 py-2 text-left">Borrower</th>
            <th class="px-3 py-2 text-right">Principal</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Application</th>
            <th class="px-3 py-2 text-left">Release</th>
            <th class="px-3 py-2 text-right">Disbursed</th>
            <th class="px-3 py-2 text-right">Paid</th>
            <th class="px-3 py-2 text-right">Penalties</th>
            <th class="px-3 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($loans as $i => $loan)
            @php
              $status = $loan->status;
              $badgeClass = $statusColors[$status] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <tr class="border-b border-slate-100 hover:bg-slate-50/60">
              <td class="px-3 py-2 text-slate-700">
                {{ $loans->firstItem() + $i }}
              </td>
              <td class="px-3 py-2 font-semibold text-slate-900">
                <a href="{{ route('loans.show', $loan) }}" class="text-indigo-600 hover:underline">
                {{ $loan->reference }}
                </a>
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $loan->borrower_name ?? 'N/A' }}
              </td>
              <td class="px-3 py-2 text-right text-slate-800">
                ₱{{ number_format($loan->principal_amount, 2) }}
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                  {{ ucfirst(str_replace('_',' ', $status)) }}
                </span>
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $loan->application_date ?? '—' }}
              </td>
              <td class="px-3 py-2 text-slate-700">
                {{ $loan->release_date ?? '—' }}
              </td>
              <td class="px-3 py-2 text-right text-slate-800">
                ₱{{ number_format($loan->total_disbursed ?? 0, 2) }}
              </td>
              <td class="px-3 py-2 text-right text-emerald-600">
                ₱{{ number_format($loan->total_paid ?? 0, 2) }}
              </td>
              <td class="px-3 py-2 text-right text-rose-600">
                ₱{{ number_format($loan->total_penalties ?? 0, 2) }}
              </td>
              <td class="px-3 py-2 text-right space-y-1">
                {{-- Repayments --}}
                @if (Route::has('repayments.index'))
                  <a href="{{ route('repayments.index', $loan) }}"
                     class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-100">
                    Repayments
                  </a>
                @endif>

                {{-- Status Transition --}}
                <form method="POST" action="{{ route('loans.move', $loan) }}" class="inline-flex items-center gap-1 mt-1">
                  @csrf
                  <select name="to_status"
                          class="border border-slate-200 rounded-md px-1 py-1 text-[11px] bg-white">
                    <option value="">Move to…</option>
                    <option value="{{ \App\Models\Loan::ST_REVIEW }}">Under Review</option>
                    <option value="{{ \App\Models\Loan::ST_APPROVED }}">Approved</option>
                    <option value="{{ \App\Models\Loan::ST_FOR_RELEASE }}">For Release</option>
                    <option value="{{ \App\Models\Loan::ST_DISBURSED }}">Disbursed</option>
                    <option value="{{ \App\Models\Loan::ST_CLOSED }}">Closed</option>
                    <option value="{{ \App\Models\Loan::ST_REJECTED }}">Rejected</option>
                    <option value="{{ \App\Models\Loan::ST_CANCELLED }}">Cancelled</option>
                    <option value="{{ \App\Models\Loan::ST_RESTRUCTURED }}">Restructured</option>
                  </select>

                  <input type="date"
                         name="release_date"
                         class="border border-slate-200 rounded-md px-1 py-1 text-[11px] bg-white"
                         placeholder="Release date">

                  <button class="inline-flex items-center px-2 py-1 rounded-md bg-slate-900 text-white text-[11px] hover:bg-slate-800">
                    Move
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="px-3 py-6 text-center text-slate-400 text-[11px] italic">
                No loans found for the current filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION FOOTER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-5 py-4 bg-slate-50 border-t border-slate-100 text-xs text-slate-600 gap-2">
      <div>
        @if ($loans->total())
          Showing
          <span class="font-semibold">{{ $loans->firstItem() }}</span>–
          <span class="font-semibold">{{ $loans->lastItem() }}</span>
          of
          <span class="font-semibold">{{ $loans->total() }}</span>
        @else
          No results
        @endif
      </div>
      <div class="text-right">
        {{ $loans->onEachSide(1)->links() }}
      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    (function () {
      const ctx = document.getElementById('loanStatusChart');
      if (!ctx) return;

      // Chart data from server (via data attributes)
      const labels = JSON.parse(ctx.dataset.labels || '[]');
      const datasetsRaw = JSON.parse(ctx.dataset.datasets || '{}');

      const colors = [
        '#4f46e5','#22c55e','#f97316','#0ea5e9','#f43f5e',
        '#a855f7','#64748b'
      ];

      const datasets = Object.keys(datasetsRaw).map((name, idx) => ({
        label: name,
        data: datasetsRaw[name],
        borderColor: colors[idx % colors.length],
        backgroundColor: colors[idx % colors.length],
        borderWidth: 2,
        fill: false,
        tension: 0.3,
        pointRadius: 3,
        pointHoverRadius: 4,
      }));

      new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: { boxWidth: 12, boxHeight: 12 }
            },
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { font: { size: 10 } }
            },
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, font: { size: 10 } }
            }
          }
        }
      });
    })();
  </script>
@endsection
