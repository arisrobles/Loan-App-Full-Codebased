{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    .card { background:#fff;border-radius:12px;padding:16px;box-shadow:0 6px 18px rgba(8,15,35,0.06); }
    .small { font-size:0.85rem;color:#6b7280; }
  </style>
@endsection

@section('content')
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="text-sm text-slate-500">Overview of cash, loans, revenue and upcoming collections.</p>
      </div>
      <div class="text-right">
        <div class="text-xs small">Total Cash (book)</div>
        <div class="text-xl font-bold">₱{{ number_format($totalCash,2) }}</div>
      </div>
    </div>

    {{-- Top summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="card">
        <div class="small">Total Loans</div>
        <div class="text-lg font-semibold">{{ $totalLoans }}</div>
        <div class="small text-slate-500 mt-1">Disbursed: {{ $disbursedLoans }} — Open: {{ $openLoans }}</div>
      </div>

      <div class="card">
        <div class="small">Outstanding Principal</div>
        <div class="text-lg font-semibold">₱{{ number_format($totalPrincipalOutstanding,2) }}</div>
        <div class="small text-slate-500 mt-1">Estimated outstanding principal across loans</div>
      </div>

      <div class="card">
        <div class="small">Upcoming Collections (30d)</div>
        <div class="text-lg font-semibold">{{ $upcomingRepayments->count() }}</div>
        <div class="small text-slate-500 mt-1">Repayments due in next 30 days</div>
      </div>

      <div class="card">
        <div class="small">Bank Accounts</div>
        <div class="text-lg font-semibold">{{ count($bankLabels) }}</div>
        <div class="small text-slate-500 mt-1">Bank balances (book)</div>
      </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <div>
            <div class="text-sm font-semibold">Revenue (last 6 months)</div>
            <div class="small text-slate-500">Monthly aggregated revenue from P&L revenue accounts</div>
          </div>
        </div>
        <canvas id="revenueChart" height="120"></canvas>
      </div>

      <div class="card">
        <div class="text-sm font-semibold mb-2">Loans by Status</div>
        <canvas id="loanStatusChart" height="220"></canvas>
      </div>
    </div>

    {{-- Bank balances bar --}}
    <div class="card">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="text-sm font-semibold">Bank Balances (book)</div>
          <div class="small text-slate-500">Per bank account</div>
        </div>
      </div>
      <canvas id="bankBarChart" height="80"></canvas>
    </div>

    {{-- Tables: Top borrowers, Upcoming repayments, Recent tx --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="card">
        <div class="text-sm font-semibold mb-2">Top Borrowers</div>
        <table class="w-full text-sm">
          <thead class="text-slate-500 text-xs">
            <tr><th class="text-left">Borrower</th><th class="text-right">Disbursed</th></tr>
          </thead>
          <tbody>
            @foreach($topBorrowers as $b)
              <tr>
                <td class="py-1">{{ $b->full_name }}</td>
                <td class="py-1 text-right">₱{{ number_format($b->total_disbursed,2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="card">
        <div class="text-sm font-semibold mb-2">Upcoming Repayments (30d)</div>
        <table class="w-full text-sm">
          <thead class="text-slate-500 text-xs">
            <tr><th class="text-left">Loan</th><th class="text-left">Due</th><th class="text-right">Due Amt</th></tr>
          </thead>
          <tbody>
            @foreach($upcomingRepayments as $r)
              <tr>
                <td class="py-1">{{ $r->reference }} — {{ $r->borrower_name }}</td>
                <td class="py-1">{{ \Carbon\Carbon::parse($r->due_date)->format('Y-m-d') }}</td>
                <td class="py-1 text-right">₱{{ number_format($r->amount_due - $r->amount_paid,2) }}</td>
              </tr>
            @endforeach
            @if($upcomingRepayments->isEmpty())
              <tr><td colspan="3" class="py-2 text-slate-400 italic">No upcoming repayments in next 30 days.</td></tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="card">
        <div class="text-sm font-semibold mb-2">Recent Bank Transactions</div>
        <table class="w-full text-sm">
          <thead class="text-slate-500 text-xs">
            <tr><th class="text-left">Ref</th><th class="text-left">Date</th><th class="text-right">Net</th></tr>
          </thead>
          <tbody>
            @foreach($recentTx as $t)
              <tr>
                <td class="py-1 text-xs">{{ $t->ref_code ?? '—' }} @if($t->contact_display) <div class="small text-slate-400">{{ $t->contact_display }}</div> @endif</td>
                <td class="py-1">{{ $t->tx_date }}</td>
                <td class="py-1 text-right">₱{{ number_format((float)($t->received ?? 0) - (float)($t->spent ?? 0),2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
<script>
  const months = {!! json_encode($months) !!};
  const revenueSeries = {!! json_encode($revenueSeries) !!};

  // Revenue line chart
  new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Revenue',
        data: revenueSeries,
        tension: 0.25,
        fill: true,
        borderWidth: 2,
        pointRadius: 3,
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
      }
    }
  });

  // Bank balances bar
  const bankLabels = {!! json_encode($bankLabels) !!};
  const bankValues = {!! json_encode($bankValues) !!};
  new Chart(document.getElementById('bankBarChart'), {
    type: 'bar',
    data: { labels: bankLabels, datasets: [{ label: 'Balance', data: bankValues, barThickness: 28 }] },
    options: {
      indexAxis: 'x',
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { callback: v => '₱' + v } } }
    }
  });

  // Loan status pie
  const loanStatus = {!! json_encode($loanStatus) !!};
  const loanStatusLabels = Object.keys(loanStatus);
  const loanStatusValues = Object.values(loanStatus);
  new Chart(document.getElementById('loanStatusChart'), {
    type: 'pie',
    data: { labels: loanStatusLabels, datasets: [{ data: loanStatusValues }] },
    options: { plugins: { legend: { position: 'bottom' } } }
  });
</script>
@endsection