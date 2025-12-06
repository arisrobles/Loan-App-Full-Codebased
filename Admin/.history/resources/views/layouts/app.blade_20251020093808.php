<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ $title ?? 'MasterFunds' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  @yield('head')
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <div class="flex-1 min-w-0 flex flex-col">
      {{-- Header --}}
      <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-200 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 flex items-center justify-between h-14">
          <h1 class="font-semibold truncate">{{ $pageTitle ?? 'Dashboard' }}</h1>
          <span class="text-xs text-gray-500">Asia/Manila</span>
        </div>
      </header>

      {{-- Main container --}}
      <main class="mx-auto max-w-7xl w-full p-4 md:p-6 flex-1">
        @yield('content')
      </main>
    </div>
  </div>

  @yield('scripts')
</body>
</html>
