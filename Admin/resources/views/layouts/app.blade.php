<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ $title ?? 'MasterFunds' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    html, body {font-family: 'Inter', sans-serif; background-color: #f9fafb; color: #0f172a;}
    .glass-header {
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(226,232,240,0.7);
      box-shadow: 0 4px 20px -10px rgba(15,23,42,0.1);
    }
    /* Custom scrollbar for sidebar */
    .sidebar-scroll::-webkit-scrollbar {
      width: 6px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
      background: #1f2937;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
      background: #4b5563;
      border-radius: 3px;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
      background: #6b7280;
    }
    /* Firefox scrollbar */
    .sidebar-scroll {
      scrollbar-width: thin;
      scrollbar-color: #4b5563 #1f2937;
    }
  </style>
  @yield('head')
  
  {{-- Socket.io for Real-time Updates --}}
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <script>
    // Socket.io configuration
    window.SOCKET_CONFIG = {
      url: '{{ env("SOCKET_URL", "http://localhost:8080") }}',
      // Admin will authenticate via a token endpoint
      getTokenUrl: '{{ route("admin.socket.token") }}'
    };
  </script>
</head>
<body>
  <div class="min-h-screen flex">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main Content --}}
    <div class="flex-1 min-w-0 flex flex-col bg-gray-50">
      
      {{-- Header --}}
      <header class="glass-header sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 h-14 flex items-center justify-between">
          <h1 class="font-semibold text-gray-800 truncate">{{ $pageTitle ?? 'Dashboard' }}</h1>
          <div class="flex items-center gap-4">
            {{-- Notifications --}}
            <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Notifications">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              @php
                $unreadCount = \App\Models\Notification::where('is_read', false)->count();
              @endphp
              @if($unreadCount > 0)
                <span class="absolute top-1 right-1 h-4 w-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                  {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
              @endif
            </a>

            {{-- Support Messages --}}
            <a href="{{ route('support-messages.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Support Messages">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              @php
                try {
                  $pendingCount = \App\Models\SupportMessage::where('status', 'pending')->count();
                } catch (\Exception $e) {
                  $pendingCount = 0;
                }
              @endphp
              @if($pendingCount > 0)
                <span class="absolute top-1 right-1 h-4 w-4 bg-yellow-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                  {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                </span>
              @endif
            </a>

            <span class="text-xs text-gray-500">Asia/Manila</span>
          </div>
        </div>
      </header>

      {{-- Page Content --}}
      <main class="mx-auto max-w-7xl w-full p-6 md:p-8 flex-1">
        @yield('content')
      </main>

    </div>
  </div>

  {{-- Socket.io Admin Script --}}
  <script src="{{ asset('js/socket-admin.js') }}"></script>
  
  @stack('scripts')
  @yield('scripts')
</body>
</html>
