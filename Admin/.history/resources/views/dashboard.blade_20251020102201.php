@extends('layouts.app')

@section('title', 'Bank Transactions — MasterFunds')
@section('page-title', 'Bank Transactions')

@section('toolbar')
  <a href="{{ route('transactions.index', ['accountId'=>1]) }}" class="hidden sm:inline-flex items-center gap-2 px-3 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700">
    Quick Payments
  </a>
@endsection

@section('content')
  {{-- Header KPIs --}}
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Bank Ending Balance</div>
      <div class="mt-2 text-2xl font-semibold text-emerald-600">₱{{ number_format($bankEnding,2) }}</div>
      <div class="text-xs text-slate-500 mt-1">as of {{ now()->format('M d, Y') }}</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Posted Balance</div>
      <div class="mt-2 text-2xl font-semibold text-indigo-600">₱{{ number_format($postedBal,2) }}</div>
      <div class="text-xs text-slate-500 mt-1">Reconciled total</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Pending Transactions</div>
      <div class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($counts['pending']) }}</div>
      <div class="text-xs text-slate-500 mt-1">Awaiting posting</div>
    </article>
    <article class="bg-white rounded-2xl p-4 shadow-card border border-slate-100">
      <div class="text-sm text-slate-500">Excluded</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($counts['excluded']) }}</div>
      <div class="text-xs text-slate-500 mt-1">Skipped or manual</div>
    </article>
  </section>

  {{-- Filters --}}
  <section class="bg-white rounded-2xl p-5 shadow-card border border-slate-100 mb-6">
    <form method="GET" action="{{ route('transactions.index', ['accountId'=>$accountId]) }}" class="flex flex-wrap gap-3 items-center">
      <div class="flex-grow min-w-[240px]">
        <input name="q" type="text" value="{{ $q ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" placeholder="Search by contact, description, or amount">
      </div>
      <button class="px-3 h-10 rounded-lg bg-brand-600 text-white hover:bg-brand-700 text-sm">Search</button>
      <a href="{{ route('transactions.index', ['accountId'=>$accountId]) }}" class="px-3 h-10 rounded-lg border text-sm hover:bg-slate-50">Reset</a>
      <button type="button" id="newTxBtn" class="px-3 h-10 rounded-lg border text-sm hover:bg-slate-50">New Transaction</button>
      <button type="button" id="importBtn" class="px-3 h-10 rounded-lg border text-sm hover:bg-slate-50">Import CSV</button>
    </form>

    <div class="flex flex-wrap gap-2 mt-4 border-t border-slate-200 pt-4 text-sm">
      <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'pending']) }}"
         class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='pending' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
        Pending
      </a>
      <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'posted']) }}"
         class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='posted' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
        Posted
      </a>
      <a href="{{ route('transactions.index', ['accountId'=>$accountId,'tab'=>'excluded']) }}"
         class="px-3 py-1.5 rounded-lg border text-slate-700 hover:bg-slate-50 {{ $tab==='excluded' ? 'bg-slate-100 border-slate-300' : 'border-slate-200' }}">
        Excluded
      </a>
    </div>
  </section>

  {{-- Transactions Table --}}
  <section class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-slate-700">
        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
          <tr>
            <th class="px-4 py-3 text-left font-medium">#</th>
            <th class="px-4 py-3 text-left font-medium">Date</th>
            <th class="px-4 py-3 text-left font-medium">Contact</th>
            <th class="px-4 py-3 text-left font-medium">Description</th>
            <th class="px-4 py-3 text-right font-medium">Spent</th>
            <th class="px-4 py-3 text-right font-medium">Received</th>
            <th class="px-4 py-3 text-left font-medium">Status</th>
            <th class="px-4 py-3 text-left font-medium">Account</th>
            <th class="px-4 py-3 text-left font-medium">Class</th>
            <th class="px-4 py-3 text-right font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($transactions as $i => $tx)
            @php $st = strtolower($tx->status ?? 'pending'); @endphp
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3">{{ $transactions->firstItem() + $i }}</td>
              <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($tx->tx_date)->toDateString() }}</td>
              <td class="px-4 py-3 truncate max-w-[180px]">{{ $tx->contact_display }}</td>
              <td class="px-4 py-3 truncate max-w-[220px]">{{ $tx->description }}</td>
              <td class="px-4 py-3 text-right text-rose-600">{{ $tx->spent ? number_format($tx->spent,2) : '—' }}</td>
              <td class="px-4 py-3 text-right text-emerald-600">{{ $tx->received ? number_format($tx->received,2) : '—' }}</td>
              <td class="px-4 py-3">
                @if($st === 'posted')
                  <span class="inline-block px-2 py-1 text-xs rounded bg-emerald-50 text-emerald-700 border border-emerald-100">Posted</span>
                @elseif($st === 'excluded')
                  <span class="inline-block px-2 py-1 text-xs rounded bg-slate-100 text-slate-600 border border-slate-200">Excluded</span>
                @else
                  <span class="inline-block px-2 py-1 text-xs rounded bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                @endif
              </td>
              <td class="px-4 py-3 truncate max-w-[160px]">{{ $tx->account_name }}</td>
              <td class="px-4 py-3 truncate max-w-[160px]">{{ $tx->tx_class }}</td>
              <td class="px-4 py-3 text-right">
                @if($st==='pending')
                  <form method="POST" action="{{ route('transactions.post',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="text-xs text-brand-600 hover:underline">Post</button>
                  </form>
                  <form method="POST" action="{{ route('transactions.exclude',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="text-xs text-slate-500 hover:underline ml-2">Exclude</button>
                  </form>
                @elseif($st==='excluded')
                  <form method="POST" action="{{ route('transactions.restore',['accountId'=>$accountId,'transactionId'=>$tx->id]) }}" class="inline">@csrf @method('PATCH')
                    <button class="text-xs text-emerald-600 hover:underline">Restore</button>
                  </form>
                @else
                  <span class="text-xs text-slate-400">Posted {{ optional($tx->posted_at)->diffForHumans() }}</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="py-10 text-center text-slate-500 text-sm">No transactions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-slate-100 bg-slate-50 flex flex-wrap items-center justify-between text-sm text-slate-600">
      <div>{{ number_format($transactions->total()) }} transactions total</div>
      <div>{{ $transactions->onEachSide(1)->links() }}</div>
    </div>
  </section>
@endsection
