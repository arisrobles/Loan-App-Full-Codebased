<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ $title ?? 'MasterFunds' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Optional: nicer scrollbar (supported browsers only) */
    .thin-scrollbar::-webkit-scrollbar{height:10px;width:10px}
    .thin-scrollbar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen flex">
    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <div class="flex-1 min-w-0 flex flex-col">
      {{-- Sticky Header (2 rows: title row + optional toolbar) --}}
      <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4">
          {{-- Row 1: Title / Meta --}}
          <div class="h-14 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ $pageTitle ?? 'Dashboard' }}</div>
            </div>
            <div class="text-xs text-gray-500 shrink-0">Asia/Manila</div>
          </div>

          {{-- Row 2 (optional): Actions / Filters toolbar
               Usage in a child view:
               @section('toolbar')
                 <a class="btn-quiet" href="#">Export</a>
                 <button class="btn-primary">New Record</button>
               @endsection
          --}}
          @hasSection('toolbar')
            <div class="py-2 border-t border-gray-100">
              <!-- Responsive grid to prevent overlap: left area (breadcrumbs/search), right area (actions) -->
              <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-2">
                <div class="min-w-0">
                  @yield('breadcrumbs') {{-- optional --}}
                  @yield('subhead')     {{-- optional sub-title/search --}}
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap md:justify-end gap-2">
                    {{-- Any buttons/filters placed in @section('toolbar') will wrap instead of overlap --}}
                    @yield('toolbar')
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </header>

      {{-- Main --}}
      <main class="mx-auto max-w-7xl w-full p-4 md:p-6 flex-1 min-w-0 thin-scrollbar">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- Minimal utility button styles (Tailwind-only, no config needed) --}}
  <template id="btn-styles"></template>
  <style>
    .btn-base{ @apply inline-flex items-center justify-center gap-2 rounded-lg font-medium h-10 px-3 transition-colors; }
    .btn-primary{ @apply btn-base bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600; }
    .btn-quiet{ @apply btn-base bg-gray-100 text-gray-900 hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-300; }
    .btn-outline{ @apply btn-base border border-gray-200 bg-white hover:bg-gray-50; }
    /* Compact density helper you can toggle on pages that need tighter UI */
    .density-compact .btn-base{ @apply h-9 px-2.5 text-sm; }
  </style>
</body>
</html>
