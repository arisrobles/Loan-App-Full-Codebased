@extends('layouts.app')

@section('title', 'Bank Transactions — MasterFunds')
@section('page-title', 'Bank Transactions')

@section('toolbar')
  <select id="density" class="h-9 px-2 rounded border text-sm hidden md:block" onchange="setDensity(this.value)">
    <option value="comfortable">Comfortable</option>
    <option value="compact">Compact</option>
  </select>
  <button id="themeBtn" class="h-9 px-3 rounded border text-sm" type="button" onclick="toggleTheme()">Theme: Light</button>
@endsection

@push('head')
<style>
  body::before {
    content: "";
    position: fixed;
    inset: -20%;
    z-index: -1;
    background:
      radial-gradient(40% 30% at 10% 10%, rgba(59,130,246,.08), transparent 60%),
      radial-gradient(40% 30% at 90% 10%, rgba(16,185,129,.08), transparent 60%),
      radial-gradient(50% 35% at 50% 120%, rgba(29,78,216,.06), transparent 60%);
  }
  .table-sticky thead th { position: sticky; top: 0; background: #f8fafc; z-index: 10 }
  .btn { display: inline-flex; align-items: center; justify-content: center; height: 38px; padding: 0 12px; border-radius: 10px; font-size: .875rem }
  .btn-primary { background: #2563eb; color: #fff } .btn-primary:hover { background: #1d4ed8 }
  .btn-quiet { background: #f1f5f9 } .btn-quiet:hover { background: #e2e8f0 }
  .badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 8px; font-size: 12px; font-weight: 600 }
  .kbd { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: #f1f5f9; border: 1px solid #e2e8f0; border-bottom-width: 2px; padding: .1rem .35rem; border-radius: .375rem }
  details>summary::-webkit-details-marker { display: none }
  details[open]>summary::after { content: ""; position: fixed; inset: 0 }
  ::-webkit-scrollbar { height: 10px; width: 10px }
  ::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #cbd5e1, #94a3b8); border-radius: 9999px }
  ::-webkit-scrollbar-track { background: #eef2f7 }
  .dark section.bg-white { background: #0f172a !important; border-color: #1f2937 !important }
  .dark .badge { background: #0b1f3a; color: #93c5fd }
  .compact th, .compact td { padding: .5rem !important }
</style>
@endpush

@section('content')
  {{-- Filters --}}
  <div class="bg-white/90 backdrop-blur border border-slate-200 rounded-xl mb-6">
    <div class="px-4 md:px-6 py-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
      <div class="sm:col-span-2">
        <div class="relative">
          <input id="search" name="q" value="{{ $q ?? '' }}" class="h-10 w-full pl-9 pr-3 rounded-lg bg-slate-100 text-sm outline-none"
                 placeholder="Search contact / description / amount (Press / to focus)">
          <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
          </svg>
        </div>
      </div>
      <select class="h-10 rounded-lg bg-slate-100 text-sm">
        <option value="">All Types</option>
        <option>Money In</option>
        <option>Money Out</option>
        <option>Transfer</option>
      </select>
      <select class="h-10 rounded-lg bg-slate-100 text-sm">
        <option value="">All Status</option>
        <option>Pending</option>
        <option>Posted</option>
        <option>Excluded</option>
      </select>
      <div class="flex gap-2">
        <button class="btn btn-quiet w-full" onclick="window.location.href='{{ route('transactions.index',['accountId'=>$accountId]) }}'">Reset</button>
        <button id="newTxBtn" class="btn btn-primary w-full">New</button>
      </div>
    </div>
  </div>

  {{-- Summary --}}
  <section class="bg-white p-6 rounded-2xl shadow-card border border-slate-100">
    <h2 class="text-base md:text-lg font-semibold">Account Summary</h2>
    <p class="text-slate-600 text-sm mt-1">Bank ending balance and reconciliation overview.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
      <article class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
        <div class="text-sm text-slate-500">Bank Ending Balance</div>
        <div class="mt-1 text-xl font-semibold text-emerald-600">₱{{ number_format($bankEnding,2) }}</div>
      </article>
      <article class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
        <div class="text-sm text-slate-500">Posted</div>
        <div class="mt-1 text-xl font-semibold text-indigo-600">₱{{ number_format($postedBal,2) }}</div>
      </article>
      <article class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
        <div class="text-sm text-slate-500">Pending</div>
        <div class="mt-1 text-xl font-semibold text-amber-600">{{ number_format($counts['pending']) }}</div>
      </article>
      <article class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
        <div class="text-sm text-slate-500">Excluded</div>
        <div class="mt-1 text-xl font-semibold text-slate-700">{{ number_format($counts['excluded']) }}</div>
      </article>
    </div>
  </section>

  {{-- Table --}}
  <section class="bg-white p-6 rounded-2xl shadow-card border border-slate-100 mt-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base md:text-lg font-semibold">Bank Transactions</h2>
      <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
        <span>Shortcuts: <span class="kbd">/</span> search • <span class="kbd">↑↓</span> move • <span class="kbd">Enter</span> open</span>
      </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-100">
      <table class="min-w-[1000px] w-full text-sm table-sticky">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="p-3 text-left">#</th>
            <th class="p-3 text-left">Date</th>
            <th class="p-3 text-left">Contact</th>
            <th class="p-3 text-left">Description</th>
            <th class="p-3 text-right">Spent</th>
            <th class="p-3 text-right">Received</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Account</th>
            <th class="p-3 text-left">Class</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($transactions as $i=>$tx)
            @php $st=strtolower($tx->status??'pending'); @endphp
            <tr class="hover:bg-slate-50">
              <td class="p-3">{{ $transactions->firstItem()+$i }}</td>
              <td class="p-3">{{ \Carbon\Carbon::parse($tx->tx_date)->toDateString() }}</td>
              <td class="p-3 truncate max-w-[160px]">{{ $tx->contact_display }}</td>
              <td class="p-3 truncate max-w-[220px]">{{ $tx->description }}</td>
              <td class="p-3 text-right text-rose-600">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
              <td class="p-3 text-right text-emerald-600">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
              <td class="p-3">
                @if($st==='posted')
                  <span class="badge bg-emerald-100 text-emerald-700">Posted</span>
                @elseif($st==='excluded')
                  <span class="badge bg-slate-100 text-slate-700">Excluded</span>
                @else
                  <span class="badge bg-amber-100 text-amber-700">Pending</span>
                @endif
              </td>
              <td class="p-3 truncate max-w-[160px]">{{ $tx->account_name }}</td>
              <td class="p-3 truncate max-w-[160px]">{{ $tx->tx_class }}</td>
              <td class="p-3">
                <details class="relative">
                  <summary class="list-none cursor-pointer px-3 h-9 rounded border border-slate-200 hover:bg-slate-50 inline-flex items-center">Actions ▾</summary>
                  <div class="absolute z-20 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-card p-2">
                    @if($st==='pending')
                      <form method="POST" action="{{ route('transactions.post',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">@csrf @method('PATCH')
                        <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-emerald-700">Post</button>
                      </form>
                      <form method="POST" action="{{ route('transactions.exclude',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">@csrf @method('PATCH')
                        <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-amber-700">Exclude</button>
                      </form>
                    @elseif($st==='excluded')
                      <form method="POST" action="{{ route('transactions.restore',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}">@csrf @method('PATCH')
                        <button class="w-full text-left px-3 py-2 rounded hover:bg-slate-50 text-indigo-700">Restore</button>
                      </form>
                    @else
                      <div class="px-3 py-2 text-xs text-slate-500">Posted {{ optional($tx->posted_at)->diffForHumans() }}</div>
                    @endif
                  </div>
                </details>
              </td>
            </tr>
          @empty
            <tr><td colspan="10" class="p-8 text-center text-slate-500 text-sm">No transactions found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between text-sm text-slate-500 mt-4">
      <div>{{ number_format($transactions->total()) }} records total</div>
      <div>{{ $transactions->onEachSide(1)->links() }}</div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
(function(){
  const root=document.documentElement,themeBtn=document.getElementById('themeBtn'),
        densitySel=document.getElementById('density');
  const savedTheme=localStorage.getItem('theme')||'light';
  const savedDensity=localStorage.getItem('density')||'comfortable';
  function applyTheme(t){root.classList.toggle('dark',t==='dark');themeBtn.textContent='Theme: '+(t==='dark'?'Dark':'Light');}
  function applyDensity(d){root.classList.toggle('compact',d==='compact');densitySel.value=d;}
  applyTheme(savedTheme);applyDensity(savedDensity);
  window.toggleTheme=()=>{const next=root.classList.contains('dark')?'light':'dark';localStorage.setItem('theme',next);applyTheme(next);}
  window.setDensity=v=>{localStorage.setItem('density',v);applyDensity(v);}
  // keyboard shortcut for search
  const q=document.getElementById('search');
  document.addEventListener('keydown',e=>{if(e.key==='/'&&document.activeElement!==q){e.preventDefault();q.focus();q.select();}});
  // close open details menus
  document.addEventListener('click',e=>{const open=document.querySelector('details[open]');if(open&&!open.contains(e.target))open.removeAttribute('open');},true);
})();
</script>
@endpush
