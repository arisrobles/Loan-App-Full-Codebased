@extends('layouts.app')

@php
  $pageTitle = 'Support Message Details';
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
      <p class="text-slate-300 text-sm">View and respond to support message</p>
    </div>
    <a href="{{ route('support-messages.index') }}"
       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-semibold">
      ← Back to Messages
    </a>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- Main Content --}}
  <div class="lg:col-span-2 space-y-6">
    {{-- Message Details --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h2 class="text-xl font-semibold text-slate-900 mb-2">{{ $supportMessage->subject }}</h2>
          <div class="flex items-center gap-4 text-sm text-slate-600">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              @if($supportMessage->borrower)
                <a href="{{ route('borrowers.show', $supportMessage->borrower) }}" class="text-indigo-600 hover:underline font-medium">
                  {{ $supportMessage->borrower->full_name }}
                </a>
              @else
                <span class="text-slate-400">Unknown Borrower</span>
              @endif
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              {{ $supportMessage->created_at->format('M d, Y h:i A') }}
            </div>
          </div>
        </div>
        <div>
          @if($supportMessage->status === 'pending')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
              Pending
            </span>
          @elseif($supportMessage->status === 'in_progress')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
              In Progress
            </span>
          @elseif($supportMessage->status === 'resolved')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
              Resolved
            </span>
          @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">
              Closed
            </span>
          @endif
        </div>
      </div>

      <div class="border-t border-slate-200 pt-4">
        <h3 class="text-sm font-semibold text-slate-700 uppercase mb-2">Message</h3>
        <div class="bg-slate-50 rounded-lg p-4 text-slate-700 whitespace-pre-wrap">
          {{ $supportMessage->message }}
        </div>
      </div>
    </div>

    {{-- Admin Response Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">Admin Response</h3>
      
      @if($supportMessage->admin_response)
        <div class="mb-4 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
          <div class="flex items-start justify-between mb-2">
            <div class="text-sm font-semibold text-emerald-900">Response</div>
            <div class="text-xs text-emerald-700">
              @if($supportMessage->respondedBy)
                By: {{ $supportMessage->respondedBy->username }}
              @endif
              @if($supportMessage->responded_at)
                • {{ $supportMessage->responded_at->format('M d, Y h:i A') }}
              @endif
            </div>
          </div>
          <div class="text-slate-700 whitespace-pre-wrap">{{ $supportMessage->admin_response }}</div>
        </div>
      @else
        <div class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
          <p class="text-sm text-yellow-800">No response has been sent yet.</p>
        </div>
      @endif

      {{-- Response Form --}}
      <form method="POST" action="{{ route('support-messages.update', $supportMessage) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
          <label for="admin_response" class="block text-sm font-semibold text-slate-700 mb-2">
            Response Message
          </label>
          <textarea id="admin_response"
                    name="admin_response"
                    rows="6"
                    class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Type your response here...">{{ old('admin_response', $supportMessage->admin_response) }}</textarea>
          <p class="mt-1 text-xs text-slate-500">Your response will be sent to the borrower via notification.</p>
        </div>

        <div class="mb-4">
          <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
            Status
          </label>
          <select id="status"
                  name="status"
                  class="w-full border border-slate-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="pending" {{ $supportMessage->status === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ $supportMessage->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="resolved" {{ $supportMessage->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
            <option value="closed" {{ $supportMessage->status === 'closed' ? 'selected' : '' }}>Closed</option>
          </select>
        </div>

        <div class="flex gap-3">
          <button type="submit"
                  class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
            Save Response
          </button>
          <a href="{{ route('support-messages.index') }}"
             class="px-6 py-3 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-semibold">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Sidebar --}}
  <div class="space-y-6">
    {{-- Borrower Information --}}
    @if($supportMessage->borrower)
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Borrower Information</h3>
        <div class="space-y-3">
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Name</div>
            <a href="{{ route('borrowers.show', $supportMessage->borrower) }}" class="text-indigo-600 hover:underline font-medium">
              {{ $supportMessage->borrower->full_name }}
            </a>
          </div>
          @if($supportMessage->borrower->email)
            <div>
              <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Email</div>
              <div class="text-slate-700">{{ $supportMessage->borrower->email }}</div>
            </div>
          @endif
          @if($supportMessage->borrower->phone)
            <div>
              <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Phone</div>
              <div class="text-slate-700">{{ $supportMessage->borrower->phone }}</div>
            </div>
          @endif
        </div>
      </div>
    @endif

    {{-- Message Metadata --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
      <h3 class="text-sm font-semibold text-slate-900 mb-4">Message Details</h3>
      <div class="space-y-3 text-sm">
        <div>
          <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Submitted</div>
          <div class="text-slate-700">{{ $supportMessage->created_at->format('M d, Y h:i A') }}</div>
        </div>
        @if($supportMessage->responded_at)
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Responded</div>
            <div class="text-slate-700">{{ $supportMessage->responded_at->format('M d, Y h:i A') }}</div>
          </div>
        @endif
        @if($supportMessage->respondedBy)
          <div>
            <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Responded By</div>
            <div class="text-slate-700">{{ $supportMessage->respondedBy->username }}</div>
          </div>
        @endif
        <div>
          <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Message ID</div>
          <div class="text-slate-700 font-mono text-xs">#{{ $supportMessage->id }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
  <div class="fixed bottom-4 right-4 bg-emerald-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
  </div>
@endif

@push('scripts')
<script>
  // Join support message room for real-time updates
  document.addEventListener('DOMContentLoaded', function() {
    const messageId = {{ $supportMessage->id }};
    if (window.SocketAdmin && window.SocketAdmin.joinSupportMessageRoom) {
      window.SocketAdmin.joinSupportMessageRoom(messageId);
    }
  });
</script>
@endpush

@if($errors->any())
  <div class="fixed bottom-4 right-4 bg-rose-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <ul class="list-disc list-inside">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@endsection

