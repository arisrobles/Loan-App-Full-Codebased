<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ $title ?? 'MasterFunds' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Smooth scrollbars */
    .thin-scrollbar::-webkit-scrollbar{height:10px;width:10px}
    .thin-scrollbar::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}

    /* === Utility Buttons === */
    .btn-base{
      display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
      font-weight:600;border-radius:.5rem;line-height:1;
      padding:.55rem .9rem;transition:all .15s ease;white-space:nowrap;
    }
    .btn-primary{
      background:#4f46e5;color:#fff;
      border:1px solid transparent;
    }
    .btn-primary:hover{background:#4338ca;}
    .btn-outline{
      background:#fff;color:#111827;border:1px solid #e5e7eb;
    }
    .btn-outline:hover{background:#f9fafb;}
    .btn-quiet{
      background:#f3f4f6;color:#1f2937;border:1px solid #e5e7eb;
    }
    .btn-quiet:hover{background:#e5e7eb;}
    .btn-sm{padding:.4rem .7rem;font-size:.85rem}
    .btn-icon{padding:.45rem;width:2.2rem;height:2.2rem}

    /* === Compact density toggle === */
    .density-compact .btn-base{padding:.4rem .7rem;font-size:.85rem;border-radius:.4rem}

    /* === Layout polish === */
    body{background:#f9fafb;color:#111827;font-family:Inter,system-ui,sans-serif;}
    header{backdrop-filter:blur(8px);}
    main{scroll-behavior:smooth;}
  </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
  <div class="min-h-screen flex">

    {{-- Sidebar --}}
    <x-sidebar />

    {{-- Main content --}}
    <div class="flex-1 min-w-0 flex flex-col">

      {{-- Sticky Header --}}
      <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4">

          {{-- Row 1: Title / Meta --}}
          <div class="h-14 flex items-center justify-between gap-3">
            <div class="min-w-0 flex items-center gap-2">
              <div class="font-semibold text-gray-800 truncate text-base">
                {{ $pageTitle ?? 'Dashboard' }}
              </div>
            </div>
            <div class="text-xs text-gray-500 shrink-0">Asia/Manila</div>
          </div>

          {{-- Row 2: Optional toolbar --}}
          @hasSection('toolbar')
            <div class="border-t border-gray-100 py-2">
              <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-2">
                {{-- Left zone: breadcrumbs / subtitle --}}
                <div class="min-w-0">
                  @yield('breadcrumbs')
                  @yield('subhead')
                </div>
                {{-- Right zone: buttons --}}
                <div class="min-w-0">
                  <div class="flex flex-wrap justify-start md:justify-end gap-2">
                    @yield('toolbar')
                  </div>
                </div>
              </div>
            </div>
          @endhasSection

        </div>
      </header>

      {{-- Main content --}}
      <main class="mx-auto max-w-7xl w-full p-4 md:p-6 flex-1 min-w-0 thin-scrollbar">
        @yield('content')
      </main>

    </div>
  </div>
</body>
</html>
