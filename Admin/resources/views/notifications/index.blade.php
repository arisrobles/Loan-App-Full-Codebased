@extends('layouts.app')

@php
  $pageTitle = 'Notifications';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
      <p class="text-slate-300 text-sm">Manage and send notifications to borrowers</p>
    </div>
    <a href="{{ route('notifications.create') }}"
       class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
      + Send Notification
    </a>
  </div>
</div>

{{-- STATISTICS --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Notifications</div>
    <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Unread</div>
    <div class="text-2xl font-bold text-yellow-600">{{ $stats['unread'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Read</div>
    <div class="text-2xl font-bold text-emerald-600">{{ $stats['read'] }}</div>
  </div>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
  <form method="GET" action="{{ route('notifications.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
      <input type="text" name="q" value="{{ request('q') }}"
             placeholder="Search notifications..."
             class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Borrower</label>
      <select name="borrower_id" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="">All Borrowers</option>
        @foreach($borrowers as $borrower)
          <option value="{{ $borrower->id }}" {{ request('borrower_id') == $borrower->id ? 'selected' : '' }}>
            {{ $borrower->full_name }}
          </option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
      <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="">All Types</option>
        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
        <option value="reminder" {{ request('type') == 'reminder' ? 'selected' : '' }}>Reminder</option>
        <option value="approval" {{ request('type') == 'approval' ? 'selected' : '' }}>Approval</option>
        <option value="payment_received" {{ request('type') == 'payment_received' ? 'selected' : '' }}>Payment Received</option>
        <option value="payment_due" {{ request('type') == 'payment_due' ? 'selected' : '' }}>Payment Due</option>
        <option value="loan_status_change" {{ request('type') == 'loan_status_change' ? 'selected' : '' }}>Loan Status Change</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
      <select name="is_read" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Unread</option>
        <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Read</option>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm font-semibold">
        Filter
      </button>
      <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold">
        Clear
      </a>
    </div>
  </form>
</div>

{{-- FLASH MESSAGES --}}
@if(session('success'))
  <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
    {{ session('success') }}
  </div>
@endif

{{-- NOTIFICATIONS TABLE --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
  @if($notifications->count() > 0)
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Borrower</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Title</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Message</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Loan</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($notifications as $notification)
            @php
              // Determine navigation URL based on notification type and related entities
              $navUrl = null;
              if ($notification->loan_id) {
                // Payment-related notifications go to payments page filtered by loan
                if (in_array($notification->type, ['payment_received', 'payment_due'])) {
                  $navUrl = route('payments.index', ['loan_id' => $notification->loan_id]);
                } else {
                  // Other loan-related notifications go to loan details
                  $navUrl = route('loans.show', $notification->loan);
                }
              } elseif ($notification->borrower_id && !$notification->loan_id) {
                // Notifications without loan go to borrower page
                $navUrl = route('borrowers.show', $notification->borrower);
              }
            @endphp
            <tr class="hover:bg-slate-100 transition-colors {{ !$notification->is_read ? 'bg-yellow-50' : '' }} {{ $navUrl ? 'cursor-pointer' : '' }}"
                @if($navUrl) onclick="window.location.href='{{ $navUrl }}'" title="Click to view details" @endif>
              <td class="px-4 py-3 text-sm text-slate-700">
                {{ $notification->created_at->format('M d, Y h:i A') }}
              </td>
              <td class="px-4 py-3 text-sm">
                @if($notification->borrower)
                  <a href="{{ route('borrowers.show', $notification->borrower) }}" 
                     class="text-indigo-600 hover:underline"
                     onclick="event.stopPropagation()">
                    {{ $notification->borrower->full_name }}
                  </a>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                  {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                <div class="flex items-center gap-2">
                  {{ $notification->title }}
                  @if($navUrl)
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-slate-700">
                {{ Str::limit($notification->message, 50) }}
              </td>
              <td class="px-4 py-3 text-sm">
                @if($notification->loan)
                  <a href="{{ route('loans.show', $notification->loan) }}" 
                     class="text-indigo-600 hover:underline"
                     onclick="event.stopPropagation()">
                    {{ $notification->loan->reference }}
                  </a>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                @if($notification->is_read)
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                    Read
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                    Unread
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                <div class="flex items-center justify-center gap-2">
                  @if($notification->is_read)
                    <form method="POST" action="{{ route('notifications.unread', $notification) }}" class="inline">
                      @csrf
                      <button type="submit" class="text-xs text-slate-600 hover:text-slate-900" title="Mark as unread">
                        Mark Unread
                      </button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                      @csrf
                      <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800" title="Mark as read">
                        Mark Read
                      </button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="inline"
                        onsubmit="return confirm('Delete this notification?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800" title="Delete">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
      {{ $notifications->links() }}
    </div>
  @else
    <div class="px-6 py-12 text-center text-slate-500">
      <p class="text-lg mb-2">No notifications found</p>
      <p class="text-sm">Try adjusting your filters or <a href="{{ route('notifications.create') }}" class="text-indigo-600 hover:underline">send a new notification</a></p>
    </div>
  @endif
</div>

{{-- MARK ALL AS READ --}}
@if($stats['unread'] > 0)
  <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
      @csrf
      @if(request('borrower_id'))
        <input type="hidden" name="borrower_id" value="{{ request('borrower_id') }}">
      @endif
      <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm font-semibold">
        Mark All as Read ({{ $stats['unread'] }} unread)
      </button>
    </form>
  </div>
@endif

@endsection

