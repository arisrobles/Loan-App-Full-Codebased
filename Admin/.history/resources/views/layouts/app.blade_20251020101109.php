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
  </style>
  @yield('head')
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
          <span class="text-xs text-gray-500">Asia/Manila</span>
        </div>
      </header>

      {{-- Page Content --}}
      <main class="mx-auto max-w-7xl w-full p-6 md:p-8 flex-1">
        @yield('content')
      </main>

    </div>
  </div>

  @yield('scripts')
</body>
</html>
