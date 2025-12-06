<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ $title ?? 'MasterFunds' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Tailwind custom tweaks */
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #f9fafb;
      color: #111827;
    }

    /* Scrollbar */
    .thin-scrollbar::-webkit-scrollbar {
      width: 8px; height: 8px;
    }
    .thin-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1; border-radius: 8px;
    }

    /* Button styles */
    .btn {
      @apply inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-all border border-transparent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2;
    }
    .btn-primary {
      @apply bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:ring-indigo-500;
    }
    .btn-outline {
      @apply bg-white border-gray-300 text-gray-700 hover:bg-gray-50 focus-visible:ring-gray-300;
    }
    .btn-quiet {
      @apply bg-gray-100 text-gray-900 hover:bg-gray-200 focus-visible:ring-gray-200;
    }
    .btn-sm {
      @apply px-3 py-1.5 text-sm rounded-md;
    }

    /* Header shadow & backdrop */
    header {
      backdrop-filter: blur(10px);
      background-color: rgba(255, 255, 255, 0.85);
    }

    /* Sidebar placeholder */
    .sidebar {
      @apply hidden md:flex md:flex-col md:w-64 bg-white border-r border-gray-200 shadow-sm;
    }

    /* Compact helper */
    .density-compact .btn {
      @apply h-9 text-sm px-2.5;
    }
  </style>
</head>

<body class="antialiased text-gray-900">
  <div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="sidebar">
      <div class="p-4 border-b border-gray-100 text-lg font-bold text-indigo-700">
        MasterFunds
      </div>
      <nav class="flex-1 overflow-y-auto thin-scrollbar p-4 space-y-1">
        <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 font-medium">🏦 Dashboard</a>
        <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 font-medium">💰 Transactions</a>
        <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 font-medium">📊 Reports</a>
        <a href="#" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 font-medium">⚙️ Settings</a>
      </nav>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0">

      {{-- Sticky Header --}}
      <header class="sticky top-0 z-40 border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          
          {{-- Title Row --}}
          <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-2 min-w-0">
              <h1 class="text-lg font-semibold text-gray-800 truncate">{{ $pageTitle ?? 'Dashboard' }}</h1>
            </div>
            <div class="hidden sm:flex items-center gap-3">
              <span class="text-xs text-gray-500">Asia/Manila</span>
              <button class="btn btn-outline btn-sm">Help</button>
              <button class="btn btn-primary btn-sm">New</button>
            </div>
          </div>

          {{-- Toolbar Row (optional) --}}
          @hasSection('toolbar')
          <div class="py-2 border-t border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center">
              <div class="min-w-0">
                @yield('breadcrumbs')
                @yield('subhead')
              </div>
              <div class="flex flex-wrap justify-start md:justify-end gap-2">
                @yield('toolbar')
              </div>
            </div>
          </div>
          @endhasSection

        </div>
      </header>

      {{-- Main content --}}
      <main class="flex-1 thin-scrollbar overflow-y-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
          @yield('content')
        </div>
      </main>
    </div>
  </div>
</body>
</html>
