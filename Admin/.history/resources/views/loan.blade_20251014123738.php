@extends('layouts.app')

@section('title','Loan Applications')
@section('page-title','Loan Applications')
@section('page-subtitle','Manage approvals, disbursements & repayments')

@section('toolbar')
  <select id="density" class="h-9 px-2 rounded border text-sm hidden md:block" onchange="setDensity(this.value)">
    <option value="comfortable">Comfortable</option>
    <option value="compact">Compact</option>
  </select>
  <button id="themeBtn" class="h-9 px-3 rounded border text-sm" type="button" onclick="toggleTheme()">Theme: Light</button>
@endsection

{{-- Optional: mark Loans as active in your layout’s sidebar --}}
@push('sidebar')
  <div class="px-2 text-xs uppercase tracking-wide text-white/70 mt-2 mb-1">Navigate</div>
  <div class="pl-2 space-y-1">
    <a href="{{ route('loans.index') }}"
       class="block px-3 py-2 rounded-lg {{ ($filters['status'] ?? '')==='' ? 'bg-white/10' : 'hover:bg-white/10' }}">Pipeline</a>
    <a href="{{ route('loans.index', array_merge(request()->except('page'), ['status'=>'active'])) }}"
       class="block px-3 py-2 rounded-lg {{ ($filters['status'] ?? '')==='active' ? 'bg-white/10' : 'hover:bg-white/10' }}">Active Loans</a>
    <a href="{{ route('loans.index', array_merge(request()->except('page'), ['status'=>'restructured'])) }}"
       class="block px-3 py-2 rounded-lg {{ ($filters['status'] ?? '')==='restructured' ? 'bg-white/10' : 'hover:bg-white/10' }}">Restructured</a>
    <a href="{{ route('loans.index', array_merge(request()->except('page'), ['status'=>'closed'])) }}"
       class="block px-3 py-2 rounded-lg {{ ($filters['status'] ?? '')==='closed' ? 'bg-white/10' : 'hover:bg-white/10' }}">Closed</a>
    <a href="{{ route('loans.index', array_merge(request()->except('page'), ['status'=>'rejected'])) }}"
       class="block px-3 py-2 rounded-lg {{ ($filters['status'] ?? '')==='rejected' ? 'bg-white/10' : 'hover:bg-white/10' }}">Rejected</a>
  </div>

  <div class="px-2 text-xs uppercase tracking-wide text-white/70 mt-4 mb-1">Other</div>
  <a href="#payments" class="block px-3 py-2 rounded-lg hover:bg-white/10">Payments</a>
  <a href="#reports" class="block px-3 py-2 rounded-lg hover:bg-white/10">Reports</a>
@endpush

@push('head')
<style>
  body::before{content:"";position:fixed;inset:-20%;z-index:-1;background:
    radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.08), transparent 60%),
    radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.08), transparent 60%),
    radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.06), transparent 60%)}
  .stuck{position:sticky;top:0;z-index:30}
  .table-sticky thead th{position:sticky;top:0;background:#f8fafc;z-index:10}
  .btn{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 12px;border-radius:10px;font-size:.875rem}
  .btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
  .btn-quiet{background:#f1f5f9}.btn-quiet:hover{background:#e2e8f0}
  .badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;font-size:12px;font-weight:600}
  .kbd{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;background:#f1f5f9;border:1px solid #e2e8f0;border-bottom-width:2px;padding:.1rem .35rem;border-radius:.375rem}
  .actions-cell{position:relative;overflow:visible}
  details[open] > summary::after{content:"";position:fixed;inset:0}
  /* Compact density helpers (layout toggles .compact on <html>) */
  .compact th,.compact td{padding:.5rem!important}
  .compact .h-10{height:2.25rem!important}
  .compact .h-9{height:2.125rem!important}
  .compact .px-4{padding-left:.75rem!important;padding-right:.75rem!important}
  .compact .px-3{padding-left:.5rem!important;padding-right:.5rem!important}
</style>
@endpush

@section('content')
  {{-- Sticky Filters / Actions Bar --}}
  <div class="stuck bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-3 flex flex-col md:flex-row md:items-center gap-3">
      <form id="filterForm" method="GET" action="{{ route('loans.index') }}" class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-2">
        <div class="md:col-span-2">
          <label class="sr-only" for="qInput">Search</label>
          <div class="relative">
            <input id="qInput" name="q" value="{{ $filters['q'] ?? '' }}"
                   class="h-10 pl-9 pr-3 rounded-lg bg-slate-100 text-sm outline-none w-full"
                   placeholder="Search borrower or reference… (Press Enter)"/>
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </div>
        </div>

        @php $st = $filters['status'] ?? ''; @endphp
        <select name="status" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" title="Status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <option value="new_application" {{ $st==='new_application'?'selected':'' }}>New Application</option>
          <option value="under_review"    {{ $st==='under_review'?'selected':'' }}>Under Review</option>
          <option value="approved"        {{ $st==='approved'?'selected':'' }}>Approved</option>
          <option value="for_release"     {{ $st==='for_release'?'selected':'' }}>For Release</option>
          <option value="disbursed"       {{ $st==='disbursed'?'selected':'' }}>Disbursed</option>
          <option value="active"          {{ $st==='active'?'selected':'' }}>Active</option>
          <option value="restructured"    {{ $st==='restructured'?'selected':'' }}>Restructured</option>
          <option value="closed"          {{ $st==='closed'?'selected':'' }}>Closed</option>
          <option value="rejected"        {{ $st==='rejected'?'selected':'' }}>Rejected</option>
        </select>

        @php $m = (int) ($filters['months'] ?? 6); @endphp
        <select name="months" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" title="Months" onchange="this.form.submit()">
          <option value="6"  {{ $m===6?'selected':'' }}>Last 6 months</option>
          <option value="12" {{ $m===12?'selected':'' }}>Last 12 months</option>
        </select>

        @php $pp = (int) ($filters['per_page'] ?? 10); @endphp
        <select name="per_page" class="h-10 px-2 rounded-lg bg-slate-100 text-sm" title="Rows per page" onchange="this.form.submit()">
          <option value="10" {{ $pp===10?'selected':'' }}>10 / page</option>
          <option value="25" {{ $pp===25?'selected':'' }}>25 / page</option>
          <option value="50" {{ $pp===50?'selected':'' }}>50 / page</option>
        </select>
      </form>
    </div>
  </div>

  <div class="max-w-screen-2xl mx-auto px-4 md:px-6 py-6 space-y-6">
    {{-- Flash --}}
    @if(session('success'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center justify-between shadow-soft">
        <span>{{ session('success') }}</span>
        <button class="text-emerald-700 text-sm" onclick="this.parentElement.remove()">Dismiss</button>
      </div>
    @endif
    @if($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-soft">
        {{ $errors->first() }}
      </div>
    @endif

    {{-- Summary Tiles --}}
    @php
      $tiles = [
        ['label'=>'New Application',  'value'=>($summary['new_application'] ?? 0), 'color' => 'bg-slate-100 text-slate-700'],
        ['label'=>'Under Review',     'value'=>($summary['under_review'] ?? 0),    'color' => 'bg-amber-100 text-amber-700'],
        ['label'=>'Approved',         'value'=>($summary['approved'] ?? 0),        'color' => 'bg-blue-100 text-blue-700'],
        ['label'=>'For Release',      'value'=>($summary['for_release'] ?? 0),     'color' => 'bg-indigo-100 text-indigo-700'],
        ['label'=>'Disbursed',        'value'=>($summary['disbursed'] ?? 0),       'color' => 'bg-emerald-100 text-emerald-700'],
        ['label'=>'Missed Repayments','value'=>($summary['missed'] ?? 0),          'color' => 'bg-rose-100 text-rose-700'],
      ];
    @endphp
    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
      @foreach($tiles as $tile)
        <div class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
          <div class="text-xs text-slate-500">{{ $tile['label'] }}</div>
          <div class="mt-2 flex items-center justify-between">
            <div class="text-2xl font-semibold">{{ $tile['value'] }}</div>
            <span class="badge {{ $tile['color'] }}">{{ ($tile['value'] ?? 0) > 9 ? 'High' : 'Normal' }}</span>
          </div>
        </div>
      @endforeach
    </section>

    {{-- Pipeline + Trends --}}
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
      <div class="bg-white rounded-2xl p-6 shadow-card border border-slate-100 xl:col-span-1">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base md:text-lg font-semibold">Loan Pipeline Stages</h2>
          <span class="text-xs text-slate-500">Tip: Use <span class="kbd">/</span> to focus search</span>
        </div>
        <ol class="grid sm:grid-cols-2 gap-4 text-sm">
          <li class="p-4 rounded-lg border border-slate-100 bg-slate-50">
            <div class="font-semibold">New Application</div>
            <p class="mt-1 text-slate-600">Application submitted — borrower provides form &amp; requirements.</p>
          </li>
          <li class="p-4 rounded-lg border border-slate-100 bg-slate-50">
            <div class="font-semibold">Under Review</div>
            <p class="mt-1 text-slate-600">Credit check, scoring, and verification.</p>
          </li>
          <li class="p-4 rounded-lg border border-slate-100 bg-slate-50">
            <div class="font-semibold">Approved</div>
            <p class="mt-1 text-slate-600">Awaiting final sign-off/guarantor confirmation.</p>
          </li>
          <li class="p-4 rounded-lg border border-slate-100 bg-slate-50">
            <div class="font-semibold">For Release</div>
            <p class="mt-1 text-slate-600">Ready for disbursement — funds scheduled for release.</p>
          </li>
          <li class="p-4 rounded-lg border border-slate-100 bg-slate-50">
            <div class="font-semibold">Disbursed</div>
            <p class="mt-1 text-slate-600">Funds transferred to borrower.</p>
          </li>
        </ol>
      </div>

      <div class="bg-white rounded-2xl p-6 shadow-card border border-slate-100 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base md:text-lg font-semibold">Pipeline Trends</h2>
          <div class="text-xs text-slate-500">Monthly stacked counts</div>
        </div>
        <canvas id="pipelineTrends" height="140"></canvas>
      </div>
    </section>

    {{-- Missed Repayments --}}
    <section class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base md:text-lg font-semibold">Missed Repayments</h2>
      </div>
      <ul class="space-y-3 text-sm">
        @forelse($missed as $r)
          @php $warn = (($r['days_overdue'] ?? 0) >= 30) ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50'; @endphp
          <li class="p-3 rounded-lg border {{ $warn }}">
            <div class="font-medium">{{ $r['borrower_name'] ?? 'Borrower' }} • ₱{{ number_format((float)($r['overdue_amount'] ?? 0),2) }} overdue</div>
            <div class="text-slate-600 text-xs">Due: {{ $r['due_date'] ?? '-' }} • Suggested penalty: ₱{{ number_format((float)($r['suggested_penalty'] ?? 0),2) }}</div>
            <form method="POST" action="{{ route('repayments.applyPenalty', ['repayment'=>($r['repayment_id'] ?? 0)]) }}" class="mt-2" onsubmit="return confirm('Apply suggested penalty now?');">
              @csrf
              <input type="hidden" name="override_amount" value="{{ $r['suggested_penalty'] ?? 0 }}">
              <button class="btn btn-primary text-xs" type="submit">Apply penalty</button>
            </form>
          </li>
        @empty
          <li class="text-slate-500">No missed repayments.</li>
        @endforelse
      </ul>
    </section>

    {{-- Loan Table --}}
    <section class="bg-white rounded-2xl p-6 shadow-card border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-base md:text-lg font-semibold">Loan List</h2>
          <p class="text-xs text-slate-500">Click Actions ▾ per loan to progress stages</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
          <span>Keyboard: <span class="kbd">/</span> search • <span class="kbd">Tab</span> jump • <span class="kbd">Enter</span> submit</span>
        </div>
      </div>

      {{-- Desktop --}}
      <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-100">
        <table class="w-full text-sm table-sticky">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="p-3 text-left">Borrower / Ref</th>
              <th class="p-3 text-left">Amount</th>
              <th class="p-3 text-left">Applied</th>
              <th class="p-3 text-left">Release</th>
              <th class="p-3 text-left">Status</th>
              <th class="p-3 text-left">Outstanding</th>
              <th class="p-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @php
              $statusLabels = [
                'new_application' => 'New Application',
                'under_review'    => 'Under Review',
                'approved'        => 'Approved',
                'for_release'     => 'For Release',
                'disbursed'       => 'Disbursed',
                'restructured'    => 'Restructured',
                'closed'          => 'Closed',
                'rejected'        => 'Rejected',
              ];
              $badgeClass = [
                'new_application' => 'bg-slate-100 text-slate-700',
                'under_review'    => 'bg-amber-100 text-amber-700',
                'approved'        => 'bg-blue-100 text-blue-700',
                'for_release'     => 'bg-indigo-100 text-indigo-700',
                'disbursed'       => 'bg-emerald-100 text-emerald-700',
                'restructured'    => 'bg-fuchsia-100 text-fuchsia-700',
                'closed'          => 'bg-slate-200 text-slate-700',
                'rejected'        => 'bg-rose-100 text-rose-700',
              ];
            @endphp
            @forelse($loans as $row)
              @php
                $principal   = (float) ($row->principal_amount ?? 0);
                $disbursed   = (float) ($row->total_disbursed ?? 0);
                $paid        = (float) ($row->total_paid ?? 0);
                $outstanding = number_format(max(0, $disbursed - $paid), 2, '.', '');
                $statusKey   = (string) ($row->status ?? 'new_application');
              @endphp
              <tr class="hover:bg-slate-50">
                <td class="p-3">
                  <div class="font-medium">{{ $row->borrower_name ?? 'Borrower' }}</div>
                  <div class="text-xs text-slate-500">{{ $row->reference ?? '-' }}</div>
                </td>
                <td class="p-3">₱{{ number_format($principal, 2) }}</td>
                <td class="p-3">{{ optional($row->application_date)->toDateString() }}</td>
                <td class="p-3">{{ $row->release_date ?: '-' }}</td>
                <td class="p-3">
                  <span class="badge {{ $badgeClass[$statusKey] ?? 'bg-slate-100' }}">
                    {{ $statusLabels[$statusKey] ?? $statusKey }}
                  </span>
                </td>
                <td class="p-3">₱{{ number_format((float)$outstanding, 2) }}</td>
                <td class="p-3 actions-cell">
                  <details class="relative">
                    <summary aria-label="Open actions menu" class="list-none cursor-pointer px-3 h-9 rounded border border-slate-200 hover:bg-slate-50 inline-flex items-center">Actions ▾</summary>
                    <div class="absolute z-20 mt-1 w-56 bg-white border border-slate-200 rounded-lg shadow-card p-2 space-y-1">
                      @if($statusKey !== 'approved')
                        <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}">
                          @csrf
                          <input type="hidden" name="to_status" value="approved">
                          <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50" type="submit">Approve</button>
                        </form>
                      @else
                        <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}" onsubmit="return confirm('Reject this application?');">
                          @csrf
                          <input type="hidden" name="to_status" value="rejected">
                          <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-rose-700" type="submit">Reject</button>
                        </form>
                      @endif

                      @if($statusKey === 'approved')
                        <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}">
                          @csrf
                          <input type="hidden" name="to_status" value="for_release">
                          <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50" type="submit">Mark For Release</button>
                        </form>
                      @endif

                      @if(in_array($statusKey, ['for_release','approved'], true))
                        <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}"
                              onsubmit="if(!this.release_date.value){ this.release_date.value=new Date().toISOString().slice(0,10); } return confirm('Disburse funds now?');">
                          @csrf
                          <input type="hidden" name="to_status" value="disbursed">
                          <div class="px-3 py-2">
                            <label class="text-xs text-slate-600">Release date</label>
                            <input type="date" name="release_date" class="mt-1 w-full border rounded px-2 h-9 text-sm">
                          </div>
                          <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-indigo-700" type="submit">Disburse</button>
                        </form>
                      @endif

                      @if($statusKey === 'disbursed')
                        <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}" onsubmit="return confirm('Close this loan?');">
                          @csrf
                          <input type="hidden" name="to_status" value="closed">
                          <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-emerald-700" type="submit">Close</button>
                        </form>
                      @endif
                    </div>
                  </details>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="p-8">
                  <div class="text-center">
                    <h3 class="font-semibold">No loans match your filters</h3>
                    <p class="text-slate-500 text-sm mt-1">Try clearing search or choosing a different status.</p>
                    <a href="{{ route('loans.index') }}" class="btn btn-quiet mt-3">Clear filters</a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Mobile cards --}}
      <div class="md:hidden space-y-3">
        @forelse($loans as $row)
          @php
            $principal   = (float) ($row->principal_amount ?? 0);
            $disbursed   = (float) ($row->total_disbursed ?? 0);
            $paid        = (float) ($row->total_paid ?? 0);
            $outstanding = number_format(max(0, $disbursed - $paid), 2, '.', '');
            $statusKey   = (string) ($row->status ?? 'new_application');
            $statusLabels = [
              'new_application' => 'New Application',
              'under_review'    => 'Under Review',
              'approved'        => 'Approved',
              'for_release'     => 'For Release',
              'disbursed'       => 'Disbursed',
              'restructured'    => 'Restructured',
              'closed'          => 'Closed',
              'rejected'        => 'Rejected',
            ];
            $badgeClass = [
              'new_application' => 'bg-slate-100 text-slate-700',
              'under_review'    => 'bg-amber-100 text-amber-700',
              'approved'        => 'bg-blue-100 text-blue-700',
              'for_release'     => 'bg-indigo-100 text-indigo-700',
              'disbursed'       => 'bg-emerald-100 text-emerald-700',
              'restructured'    => 'bg-fuchsia-100 text-fuchsia-700',
              'closed'          => 'bg-slate-200 text-slate-700',
              'rejected'        => 'bg-rose-100 text-rose-700',
            ];
          @endphp
          <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-4">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-semibold">{{ $row->borrower_name ?? 'Borrower' }}</div>
                <div class="text-xs text-slate-500">{{ $row->reference ?? '-' }}</div>
              </div>
              <span class="px-2 py-1 rounded text-xs {{ $badgeClass[$statusKey] ?? 'bg-slate-100' }}">
                {{ $statusLabels[$statusKey] ?? $statusKey }}
              </span>
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
              <div><dt class="text-slate-500">Amount</dt><dd class="font-medium">₱{{ number_format($principal,2) }}</dd></div>
              <div><dt class="text-slate-500">Outstanding</dt><dd class="font-medium">₱{{ number_format((float)$outstanding,2) }}</dd></div>
              <div><dt class="text-slate-500">Applied</dt><dd class="font-medium">{{ optional($row->application_date)->toDateString() }}</dd></div>
              <div><dt class="text-slate-500">Release</dt><dd class="font-medium">{{ $row->release_date ?: '-' }}</dd></div>
            </dl>
            <div class="mt-3 flex flex-wrap gap-2">
              @if($statusKey !== 'approved')
                <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}">
                  @csrf
                  <input type="hidden" name="to_status" value="approved">
                  <button class="btn btn-primary text-sm" type="submit">Approve</button>
                </form>
              @else
                <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}" onsubmit="return confirm('Reject this application?');">
                  @csrf
                  <input type="hidden" name="to_status" value="rejected">
                  <button class="btn bg-rose-600 text-white hover:bg-rose-500 text-sm" type="submit">Reject</button>
                </form>
              @endif

              @if($statusKey === 'approved')
                <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}">
                  @csrf
                  <input type="hidden" name="to_status" value="for_release">
                  <button class="btn bg-slate-800 text-white hover:bg-slate-700 text-sm" type="submit">For Release</button>
                </form>
              @endif

              @if(in_array($statusKey, ['for_release','approved'], true))
                <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}"
                      onsubmit="if(!this.release_date.value){ this.release_date.value=new Date().toISOString().slice(0,10); } return confirm('Disburse funds now?');">
                  @csrf
                  <input type="hidden" name="to_status" value="disbursed">
                  <input type="date" name="release_date" class="border rounded px-2 h-9 text-sm" />
                  <button class="btn bg-indigo-600 text-white hover:bg-indigo-500 text-sm" type="submit">Disburse</button>
                </form>
              @endif

              @if($statusKey === 'disbursed')
                <form method="POST" action="{{ route('loans.move', ['loan'=>$row->id]) }}" onsubmit="return confirm('Close this loan?');">
                  @csrf
                  <input type="hidden" name="to_status" value="closed">
                  <button class="btn bg-emerald-700 text-white hover:bg-emerald-600 text-sm" type="submit">Close</button>
                </form>
              @endif
            </div>
          </div>
        @empty
          <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-6 text-center text-slate-500">
            No loans match your filters. Try adjusting search or status.
          </div>
        @endforelse
      </div>

      @if(method_exists($loans,'hasPages') && $loans->hasPages())
        <div class="mt-4">
          {{ $loans->links() }}
        </div>
      @endif
    </section>

    {{-- Payment Section --}}
    <section id="payments" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white p-6 rounded-2xl shadow-card border border-slate-100 text-center">
        <h3 class="text-base md:text-lg font-semibold mb-4">Payment QR Code</h3>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Bank%20Payment"
             alt="QR Code" class="mx-auto mb-4 border p-2 rounded">
        <p class="text-slate-500 text-sm">Clients can scan this QR code to pay via mobile banking.</p>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
        <h3 class="text-base md:text-lg font-semibold mb-4">Bank Account Details</h3>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-slate-600">Bank Name</dt><dd class="font-medium">Bank of the Philippines</dd></div>
          <div class="flex justify-between"><dt class="text-slate-600">Account Name</dt><dd class="font-medium">Loan Company Inc.</dd></div>
          <div class="flex justify-between"><dt class="text-slate-600">Account Number</dt><dd class="font-medium">1234-5678-9012</dd></div>
        </dl>
        <p class="mt-3 text-slate-500 text-sm">Clients may deposit directly to this account and upload proof of payment.</p>
      </div>
    </section>
  </div>
@endsection

{{-- Chart data from backend with safe fallbacks --}}
@php
  $chartLabels  = $trends['labels']  ?? [];
  $chartSetsRaw = $trends['datasets'] ?? [];
@endphp
@push('scripts-head')
  <script>window.labels = @json($chartLabels); window.datasets = @json((object) $chartSetsRaw);</script>
@endpush

@push('scripts')
  {{-- Close <details> menus on outside click & search shortcut --}}
  <script>
    document.addEventListener('click', (e)=>{
      const open = document.querySelector('details[open]');
      if (open && !open.contains(e.target)) open.removeAttribute('open');
    }, true);

    (function(){
      const input = document.getElementById('qInput');
      const form  = document.getElementById('filterForm');
      if(!input || !form) return;
      let t = null;
      input.addEventListener('input', ()=>{
        clearTimeout(t);
        const val = (input.value || '').trim();
        t = setTimeout(()=>{ if (val.length === 0 || val.length >= 2) form.submit(); }, 400);
      });
      document.addEventListener('keydown', (e)=>{
        if (e.key === '/' && document.activeElement !== input) { e.preventDefault(); input.focus(); }
      });
    })();

    // Theme & Density (layout toggles classes on <html>)
    (function(){
      const root = document.documentElement;
      const themeBtn = document.getElementById('themeBtn');
      const densitySel = document.getElementById('density');
      const savedTheme = localStorage.getItem('theme') || 'light';
      const savedDensity = localStorage.getItem('density') || 'comfortable';
      function applyTheme(t){ root.classList.toggle('dark', t==='dark'); if(themeBtn) themeBtn.textContent = 'Theme: ' + (t==='dark' ? 'Dark' : 'Light'); }
      function applyDensity(d){ root.classList.toggle('compact', d==='compact'); if(densitySel) densitySel.value = d; }
      applyTheme(savedTheme); applyDensity(savedDensity);
      window.toggleTheme = function(){ const next = root.classList.contains('dark') ? 'light' : 'dark'; localStorage.setItem('theme', next); applyTheme(next); }
      window.setDensity  = function(v){ localStorage.setItem('density', v); applyDensity(v); }
    })();
  </script>

  {{-- Chart.js (expects Chart loaded in layout or add CDN here) --}}
  <script>
    (function(){
      const el = document.getElementById('pipelineTrends');
      const labels = window.labels || [];
      const datasetsMap = window.datasets || {};
      if(!el || !Array.isArray(labels) || !labels.length) return;

      if (window.__pipelineChart && typeof window.__pipelineChart.destroy === 'function') {
        window.__pipelineChart.destroy();
      }

      const ctx = el.getContext('2d');
      const palette = ['#94a3b8','#f59e0b','#3b82f6','#6366f1','#10b981'];
      const keys    = ['New','Under Review','Approved','For Release','Disbursed'];

      function safeSeries(k){
        const arr = (datasetsMap && datasetsMap[k]) ? datasetsMap[k] : [];
        if (!Array.isArray(arr) || arr.length !== labels.length) return Array(labels.length).fill(0);
        return arr;
      }

      window.__pipelineChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: keys.map((label, i)=>({
            label,
            data: safeSeries(label),
            backgroundColor: palette[i],
            borderColor: palette[i],
            borderWidth: 1,
            borderRadius: 8,
            maxBarThickness: 36,
            stack: 'pipeline'
          }))
        },
        options: {
          responsive: true,
          interaction: { intersect:false, mode:'index' },
          plugins: {
            legend: { position:'bottom', labels:{ boxWidth: 12, padding: 12 } },
            tooltip: {
              backgroundColor:'#0f172a', titleColor:'#fff', bodyColor:'#e5e7eb', padding:10,
              callbacks: { label: (ctx)=> ' ' + ctx.dataset.label + ': ' + ctx.parsed.y }
            }
          },
          scales: {
            x: { stacked:true, grid:{ display:false } },
            y: { stacked:true, grid:{ color:'rgba(148,163,184,.2)' }, ticks:{ precision:0, stepSize: 1 } }
          }
        }
      });
    })();
  </script>
@endpush
