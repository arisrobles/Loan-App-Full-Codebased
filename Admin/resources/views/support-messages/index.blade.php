@extends('layouts.app')

@php
  use Illuminate\Support\Str;
  $pageTitle = 'Support Messages';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
    <p class="text-slate-300 text-sm">View and respond to borrower support messages</p>
  </div>
</div>

{{-- STATISTICS --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Total Messages</div>
    <div class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Pending</div>
    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">In Progress</div>
    <div class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Resolved</div>
    <div class="text-2xl font-bold text-emerald-600">{{ $stats['resolved'] }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Closed</div>
    <div class="text-2xl font-bold text-slate-600">{{ $stats['closed'] }}</div>
  </div>
</div>

{{-- FILTERS --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
  <form method="GET" action="{{ route('support-messages.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
      <input type="text" name="q" value="{{ request('q') }}"
             placeholder="Subject, message, borrower..."
             class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
      <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
        <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
        <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Closed</option>
      </select>
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
    <div class="flex items-end gap-2">
      <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm font-semibold">
        Filter
      </button>
      <a href="{{ route('support-messages.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-semibold">
        Clear
      </a>
    </div>
  </form>
</div>

{{-- STATUS FILTERS (Quick) --}}
<div class="mb-6 flex gap-2">
  <a href="{{ route('support-messages.index', ['status' => 'all']) }}"
     class="px-4 py-2 rounded-lg {{ $status === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    All ({{ $stats['total'] }})
  </a>
  <a href="{{ route('support-messages.index', ['status' => 'pending']) }}"
     class="px-4 py-2 rounded-lg {{ $status === 'pending' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Pending ({{ $stats['pending'] }})
  </a>
  <a href="{{ route('support-messages.index', ['status' => 'in_progress']) }}"
     class="px-4 py-2 rounded-lg {{ $status === 'in_progress' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    In Progress ({{ $stats['in_progress'] }})
  </a>
  <a href="{{ route('support-messages.index', ['status' => 'resolved']) }}"
     class="px-4 py-2 rounded-lg {{ $status === 'resolved' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Resolved ({{ $stats['resolved'] }})
  </a>
  <a href="{{ route('support-messages.index', ['status' => 'closed']) }}"
     class="px-4 py-2 rounded-lg {{ $status === 'closed' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
    Closed ({{ $stats['closed'] }})
  </a>
</div>

{{-- MESSAGES TABLE --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
  @if($messages->count() > 0)
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Borrower</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Subject</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Message</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Response</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          @foreach($messages as $message)
            <tr class="hover:bg-slate-50 {{ $message->status === 'pending' ? 'bg-yellow-50' : '' }}">
              <td class="px-4 py-3 text-sm">
                <div>{{ $message->created_at->format('M d, Y') }}</div>
                <div class="text-xs text-slate-500">{{ $message->created_at->format('h:i A') }}</div>
              </td>
              <td class="px-4 py-3 text-sm">
                @if($message->borrower)
                  <a href="{{ route('borrowers.show', $message->borrower) }}" class="text-indigo-600 hover:underline font-medium">
                    {{ $message->borrower->full_name }}
                  </a>
                  <div class="text-xs text-slate-500">{{ $message->borrower->email }}</div>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                {{ $message->subject }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-700">
                {{ Str::limit($message->message, 80) }}
              </td>
              <td class="px-4 py-3 text-center">
                @if($message->status === 'pending')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Pending
                  </span>
                @elseif($message->status === 'in_progress')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    In Progress
                  </span>
                @elseif($message->status === 'resolved')
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    Resolved
                  </span>
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                    Closed
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                @if($message->admin_response)
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    ✓ Responded
                  </span>
                  @if($message->responded_at)
                    <div class="text-xs text-slate-500 mt-1">
                      {{ $message->responded_at->format('M d, Y') }}
                    </div>
                  @endif
                @else
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                    No Response
                  </span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                <a href="{{ route('support-messages.show', $message) }}"
                   class="px-3 py-1 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700">
                  View & Respond
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 py-3 border-t border-slate-200">
      {{ $messages->links() }}
    </div>
  @else
    <div class="p-12 text-center">
      <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-slate-900">No support messages found</h3>
      <p class="mt-1 text-sm text-slate-500">
        @if($status !== 'all')
          No {{ $status }} messages found.
        @else
          No support messages have been submitted yet.
        @endif
      </p>
    </div>
  @endif
</div>

@if(session('success'))
  <div class="fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div class="fixed bottom-4 right-4 bg-rose-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('error') }}
  </div>
@endif

@push('scripts')
<script>
  // Auto-refresh support messages list when new messages arrive via Socket.io
  document.addEventListener('DOMContentLoaded', function() {
    // The socket-admin.js will handle real-time updates
    // This page will auto-refresh when new messages arrive
  });
</script>
@endpush

@endsection

