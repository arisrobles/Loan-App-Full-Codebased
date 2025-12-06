@php
  // Helpers
  $isActive = fn($pattern) => request()->routeIs($pattern)
      ? 'bg-gray-900 text-white'
      : 'text-gray-200 hover:bg-gray-800 hover:text-white';

  $groupActive = fn(array $patterns) =>
      collect($patterns)->contains(fn($p) => request()->routeIs($p));
@endphp

<aside
  x-data="{
    openMobile:false,
    // open settings group when in admin settings OR chart-of-accounts
    openSettings:@json(request()->routeIs('chart-of-accounts.*') || request()->is('admin/settings*')),
    // open information group when in about, help, or legal pages
    openInformation:@json(request()->routeIs('about.*') || request()->routeIs('help.*') || request()->routeIs('legal.*'))
  }"
  class="relative"
>
  <div class="md:hidden p-2 bg-gray-900 text-white flex items-center justify-between">
    <span class="font-semibold">Menu</span>
    <button @click="openMobile=!openMobile" class="p-2 rounded hover:bg-gray-800" aria-label="Toggle menu">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <nav class="bg-gray-900 text-gray-100 w-64 shrink-0 h-screen sticky top-0 flex flex-col overflow-hidden"
       :class="{'hidden md:block':!openMobile, 'block':openMobile}">
    <div class="p-4 border-b border-gray-800 shrink-0">
      <a href="{{ route('dashboard.index') }}" class="flex items-center gap-2">
        <span class="inline-flex h-8 w-8 items-center justify-center rounded bg-indigo-600 font-bold">MF</span>
        <span class="font-semibold">MasterFunds</span>
      </a>
    </div>

    <ul class="px-2 py-3 space-y-1 text-sm flex-1 overflow-y-auto min-h-0 pb-4 sidebar-scroll">
      <li>
        <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('dashboard') }}">
          <x-icon d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" title="home"/>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="mt-2">
        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('reports.index') }}">
          <x-icon d="M9 17v-6a2 2 0 012-2h8m-6 8V9m6 8V9M5 7h4" title="reports"/>
          <span>Reports</span>
        </a>
      </li>

      <li>
        <a href="{{ route('investors.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('investors.index') }}">
          <x-icon d="M16 11V7a4 4 0 00-8 0v4M5 21h14a2 2 0 002-2v-5H3v5a2 2 0 002 2z" title="investors"/>
          <span>Investors</span>
        </a>
      </li>

      <li>
        <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('documents.index') }}">
          <x-icon d="M7 7V3h6l4 4v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7h2z" title="documents"/>
          <span>Documents</span>
        </a>
      </li>

      <li class="pt-3 pb-1 px-3 text-xs uppercase tracking-wide text-gray-400">Operations</li>

      <li>
        <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('loans.*') }}">
          <x-icon d="M3 10h18M7 15h10M5 7h14" title="loans"/>
          <span>Loans</span>
        </a>
      </li>



      <li>
        <a href="{{ route('borrowers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('borrowers.*') }}">
          <x-icon d="M17 20h5v-1a7 7 0 00-14 0v1h5" title="borrowers"/>
          <span>Borrowers</span>
        </a>
      </li>

      <li>
        <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('payments.*') }}">
          <x-icon d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0 10a8 8 0 100-16 8 8 0 000 16z" title="payments"/>
          <span>Payments</span>
          @if(\App\Models\Payment::where('status', 'pending')->count() > 0)
            <span class="ml-auto px-2 py-0.5 text-xs font-semibold bg-yellow-500 text-white rounded-full">
              {{ \App\Models\Payment::where('status', 'pending')->count() }}
            </span>
          @endif
        </a>
      </li>


      <li>
        <a href="{{ route('bank-accounts.index', 1) }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('transactions.*') }}">
          <x-icon d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0 10a8 8 0 100-16 8 8 0 000 16z" title="bank"/>
          <span>Bank Account</span>
        </a>
      </li>

      <li>
        <a href="{{ route('transactions.index', 1) }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('transactions.*') }}">
          <x-icon d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2zm0 10a8 8 0 100-16 8 8 0 000 16z" title="bank"/>
          <span>Bank Transactions</span>
        </a>
      </li>

      <li class="pt-3 pb-1 px-3 text-xs uppercase tracking-wide text-gray-400">Settings</li>

      <li x-data class="{{ $groupActive(['chart-of-accounts.*','admin.settings.*']) ? 'bg-gray-800 rounded' : '' }}">
        <button @click="openSettings=!openSettings"
                class="w-full flex items-center justify-between px-3 py-2 rounded text-gray-200 hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <x-icon d="M12 6V4m0 16v-2m8-6h-2M6 12H4m12.364-5.636l-1.414 1.414M7.05 16.95l-1.414 1.414M16.95 16.95l1.414 1.414M7.05 7.05L5.636 5.636" title="settings"/>
            <span>Admin Settings</span>
          </span>
          <svg xmlns="http://www.w3.org/2000/svg"
               :class="{'rotate-180':openSettings}"
               class="h-4 w-4 transform transition"
               viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                  clip-rule="evenodd" />
          </svg>
        </button>

        <ul x-show="openSettings" x-collapse class="mt-1 pb-1">
          <li>
            <a href="{{ route('admin.settings.index') }}"
               class="ml-10 mr-2 mt-1 block px-3 py-2 rounded {{ $isActive('admin.settings.index') }}">
              General
            </a>
          </li>
          <li>
            <a href="{{ route('chart-of-accounts.index') }}"
               class="ml-10 mr-2 mt-1 block px-3 py-2 rounded {{ $isActive('chart-of-accounts.*') }}">
              Chart of Accounts
            </a>
          </li>
        </ul>
      </li>

      <li class="pt-3 pb-1 px-3 text-xs uppercase tracking-wide text-gray-400">Information</li>

      <li x-data class="{{ $groupActive(['about.*', 'help.*', 'legal.*']) ? 'bg-gray-800 rounded' : '' }}">
        <button @click="openInformation=!openInformation"
                class="w-full flex items-center justify-between px-3 py-2 rounded text-gray-200 hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <x-icon d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" title="information"/>
            <span>Information</span>
          </span>
          <svg xmlns="http://www.w3.org/2000/svg"
               :class="{'rotate-180':openInformation}"
               class="h-4 w-4 transform transition"
               viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                  clip-rule="evenodd" />
          </svg>
        </button>

        <ul x-show="openInformation" x-collapse class="mt-1 pb-1">
          <li>
            <a href="{{ route('help.index') }}"
               class="ml-10 mr-2 mt-1 block px-3 py-2 rounded {{ $isActive('help.*') }}">
              Help & Support
            </a>
          </li>
          <li>
            <a href="{{ route('legal.index') }}"
               class="ml-10 mr-2 mt-1 block px-3 py-2 rounded {{ $isActive('legal.*') }}">
              Legal
            </a>
          </li>
          <li>
            <a href="{{ route('about.index') }}"
               class="ml-10 mr-2 mt-1 block px-3 py-2 rounded {{ $isActive('about.*') }}">
              About
            </a>
          </li>
        </ul>
      </li>

      <li class="pt-3 pb-1 px-3 text-xs uppercase tracking-wide text-gray-400">Session</li>

      @auth
        <li>
          <form action="{{ route('logout') }}" method="post" class="px-3 py-2">
            @csrf
            <button class="w-full text-left flex items-center gap-3 px-3 py-2 rounded text-gray-200 hover:bg-gray-800">
              <x-icon d="M17 16l4-4m0 0l-4-4m4 4H7" title="logout"/>
              <span>Logout</span>
            </button>
          </form>
        </li>
      @else
        <li>
          <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2 rounded {{ $isActive('login') }}">
            <x-icon d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" title="login"/>
            <span>Login</span>
          </a>
        </li>
      @endauth
    </ul>

    <div class="p-3 text-[11px] text-gray-500 border-t border-gray-800 shrink-0">
      © {{ now()->year }} MasterFunds
    </div>
  </nav>
</aside>

{{-- Alpine (collapse) --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
