<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>MasterFunds – Loan Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    html {
      scroll-behavior: smooth;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp .7s ease-out forwards; }
  </style>
</head>

<body class="bg-[#070b15] text-white">

<!-- NAVBAR -->
<header class="bg-[#0d1324]/95 border-b border-blue-900/40 sticky top-0 z-30 backdrop-blur">
  <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
    <!-- Logo -->
    <div class="flex items-center gap-2">
      <div class="w-9 h-9 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold text-sm">
        MF
      </div>
      <div>
        <p class="text-base font-semibold leading-tight tracking-tight">MasterFunds</p>
        <p class="text-[11px] text-blue-300/70 -mt-0.5">Loan Admin Panel</p>
      </div>
    </div>

    <!-- Desktop Nav -->
    <nav class="hidden md:flex items-center gap-6 text-sm">
      <a href="#overview" class="hover:text-blue-300 transition-colors">Overview</a>
      <a href="#features" class="hover:text-blue-300 transition-colors">Features</a>
      <a href="#workflow" class="hover:text-blue-300 transition-colors">How It Works</a>

      <!-- LOGIN BUTTON (Laravel route helper) -->
      <a href="{{ route('login') }}"
         class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold text-xs hover:bg-blue-600 transition-colors shadow-sm shadow-blue-500/40">
        Login
      </a>
    </nav>

    <!-- Mobile Menu Button -->
    <button id="navToggle"
            class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-blue-800/60 text-blue-200"
            aria-label="Toggle navigation">
      <!-- Simple hamburger icon -->
      <span class="block w-4 h-[2px] bg-blue-200 mb-[5px]"></span>
      <span class="block w-4 h-[2px] bg-blue-200 mb-[5px]"></span>
      <span class="block w-4 h-[2px] bg-blue-200"></span>
    </button>
  </div>

  <!-- Mobile Nav -->
  <nav id="mobileNav" class="md:hidden hidden border-t border-blue-900/40 bg-[#0d1324]/98">
    <div class="max-w-6xl mx-auto px-6 py-3 flex flex-col gap-3 text-sm">
      <a href="#overview" class="hover:text-blue-300 transition-colors">Overview</a>
      <a href="#features" class="hover:text-blue-300 transition-colors">Features</a>
      <a href="#workflow" class="hover:text-blue-300 transition-colors">How It Works</a>
      <a href="{{ route('login') }}"
         class="inline-flex justify-center px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold text-xs hover:bg-blue-600 transition-colors shadow-sm shadow-blue-500/40">
        Login
      </a>
    </div>
  </nav>
</header>

<!-- HERO -->
<section class="py-20 px-6 bg-gradient-to-br from-[#0f1a33] via-[#0c172d] to-[#050712]">
  <div class="max-w-6xl mx-auto fade-up text-center md:text-left">
    <p class="text-xs uppercase tracking-[0.25em] mb-5 text-blue-300/70">
      Loan Management Dashboard
    </p>

    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6 tracking-tight">
      A Clean & Organized Way<br class="hidden md:block" />
      to Manage Your Lending Operations
    </h1>

    <p class="text-base md:text-lg text-blue-200/85 max-w-2xl mb-8 mx-auto md:mx-0">
      MasterFunds provides a simple, focused admin panel for lending teams—built to keep
      borrowers, loans, and collections clear and manageable without unnecessary clutter.
    </p>

    <div class="flex flex-wrap gap-4 justify-center md:justify-start">
      <a href="#overview"
         class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg shadow shadow-blue-500/40 hover:bg-blue-600 text-sm transition-colors">
        View System Overview
      </a>
    </div>
  </div>
</section>

<!-- OVERVIEW -->
<section id="overview" class="py-16 px-6">
  <div class="max-w-5xl mx-auto text-center">
    <h2 class="text-3xl font-bold mb-4 tracking-tight">
      A Modern Interface for Loan Management
    </h2>
    <p class="text-blue-200/80 max-w-3xl mx-auto text-sm leading-relaxed">
      MasterFunds is designed around daily lending workflows—encoding borrowers, managing loans,
      and checking collections. It removes distractions and focuses only on what the team needs
      to see to operate smoothly.
    </p>
  </div>
</section>

<!-- FEATURES -->
<section id="features" class="py-16 px-6 bg-[#0d1324] border-y border-blue-900/30">
  <div class="max-w-6xl mx-auto mb-10 text-center">
    <h2 class="text-3xl font-bold mb-2 tracking-tight">Core Features</h2>
    <p class="text-blue-200/70 max-w-2xl mx-auto text-sm leading-relaxed">
      The essentials for lending operations, organized into a straightforward admin panel.
    </p>
  </div>

  <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8">
    <div class="bg-[#0f1a33] border border-blue-900/40 rounded-xl p-6 hover:border-blue-400/60 transition-colors">
      <h3 class="text-lg font-semibold mb-2 text-blue-300">Borrower Management</h3>
      <p class="text-blue-200/70 text-sm leading-relaxed">
        Maintain a clear list of borrowers with their basic information and linked loan records,
        making it easy to review profiles when needed.
      </p>
    </div>

    <div class="bg-[#0f1a33] border border-blue-900/40 rounded-xl p-6 hover:border-blue-400/60 transition-colors">
      <h3 class="text-lg font-semibold mb-2 text-blue-300">Loan Overview</h3>
      <p class="text-blue-200/70 text-sm leading-relaxed">
        Keep all loan entries organized with terms and schedules, so staff can quickly see
        what has been released and what is active.
      </p>
    </div>

    <div class="bg-[#0f1a33] border border-blue-900/40 rounded-xl p-6 hover:border-blue-400/60 transition-colors">
      <h3 class="text-lg font-semibold mb-2 text-blue-300">Collection Tracking</h3>
      <p class="text-blue-200/70 text-sm leading-relaxed">
        Help the team monitor which accounts have payments scheduled or overdue, without
        overwhelming them with analytics-heavy dashboards.
      </p>
    </div>
  </div>
</section>

<!-- WORKFLOW -->
<section id="workflow" class="py-16 px-6">
  <div class="max-w-5xl mx-auto text-center md:text-left">
    <h2 class="text-3xl font-bold mb-4 tracking-tight">Simple Workflow</h2>
    <p class="text-blue-200/75 text-sm max-w-3xl mb-6 leading-relaxed">
      MasterFunds mirrors the real flow inside a lending office. It’s built so your team can
      move from borrower encoding to collections review without switching between multiple tools.
    </p>

    <ul class="space-y-3 text-blue-200/85 text-sm max-w-xl mx-auto md:mx-0">
      <li>• Add or update borrower details in a clean, searchable list.</li>
      <li>• Create and modify loan entries according to your actual terms.</li>
      <li>• Check daily collections and overdue items from a focused view.</li>
      <li>• Print or export essential records for audits or reporting.</li>
    </ul>
  </div>
</section>

<!-- AMAZING FOOTER -->
<footer class="bg-[#050712] border-t border-blue-900/40 pt-16 pb-8 mt-10 relative overflow-hidden">
  <!-- Subtle gradient glow at the top of footer -->
  <div class="pointer-events-none absolute inset-x-0 -top-24 h-24 bg-gradient-to-b from-blue-500/15 to-transparent"></div>

  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-12 relative">
    <!-- Column 1 -->
    <div>
      <div class="flex items-center gap-2 mb-4">
        <div class="w-9 h-9 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold">
          MF
        </div>
        <span class="font-semibold text-lg tracking-tight">MasterFunds</span>
      </div>
      <p class="text-blue-300/70 text-sm leading-relaxed">
        A focused loan admin panel interface, crafted for lending teams who want clarity and
        structure in their daily operations.
      </p>
    </div>

    <!-- Column 2 -->
    <div>
      <h3 class="font-semibold text-blue-200 mb-3 text-sm">Navigation</h3>
      <ul class="space-y-2 text-blue-300/65 text-sm">
        <li><a href="#overview" class="hover:text-blue-300 transition-colors">Overview</a></li>
        <li><a href="#features" class="hover:text-blue-300 transition-colors">Features</a></li>
        <li><a href="#workflow" class="hover:text-blue-300 transition-colors">Workflow</a></li>
      </ul>
    </div>

    <!-- Column 3 -->
    <div>
      <h3 class="font-semibold text-blue-200 mb-3 text-sm">Resources</h3>
      <ul class="space-y-2 text-blue-300/65 text-sm">
        <li><a href="#" class="hover:text-blue-300 transition-colors">Documentation</a></li>
        <li><a href="#" class="hover:text-blue-300 transition-colors">FAQ</a></li>
        <li><a href="#" class="hover:text-blue-300 transition-colors">System Notes</a></li>
      </ul>
    </div>

    <!-- Column 4 -->
    <div>
      <h3 class="font-semibold text-blue-200 mb-3 text-sm">Brand & Links</h3>
      <p class="text-blue-300/65 text-sm mb-3">
        Keep everything consistent with your lending brand—colors, logo, and naming can be aligned
        to your organization.
      </p>
      <div class="flex gap-4 text-lg text-blue-400/80">
        <a href="#" class="hover:text-blue-300 transition-colors" aria-label="Website">🌐</a>
        <a href="#" class="hover:text-blue-300 transition-colors" aria-label="Facebook">📘</a>
        <a href="#" class="hover:text-blue-300 transition-colors" aria-label="Twitter">🐦</a>
        <a href="#" class="hover:text-blue-300 transition-colors" aria-label="LinkedIn">💼</a>
      </div>
    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="mt-10 border-t border-blue-900/40 pt-6 text-center text-[11px] text-blue-300/60 relative">
    <div class="flex flex-col md:flex-row items-center justify-center gap-3 max-w-6xl mx-auto">
      <span>© 2025 MasterFunds Admin Panel. All rights reserved.</span>
      <span class="hidden md:inline text-blue-500/40">•</span>
      <div class="flex gap-4">
        <a href="#" class="hover:text-blue-300 transition-colors">Terms</a>
        <a href="#" class="hover:text-blue-300 transition-colors">Privacy</a>
        <a href="#" class="hover:text-blue-300 transition-colors">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<!-- Simple mobile nav toggle script -->
<script>
  const navToggle = document.getElementById('navToggle');
  const mobileNav = document.getElementById('mobileNav');

  if (navToggle && mobileNav) {
    navToggle.addEventListener('click', () => {
      mobileNav.classList.toggle('hidden');
    });
  }
</script>

</body>
</html>
r