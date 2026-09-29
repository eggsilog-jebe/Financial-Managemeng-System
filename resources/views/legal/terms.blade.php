<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terms and Conditions &bull; Hospital Financial Management System (HFMS)</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">

  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Immediate Theme Boot -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('fms_theme');
      if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
      }
    })();
  </script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    @media print {
      body { background-color: #ffffff !important; color: #000000 !important; }
      .no-print { display: none !important; }
      .print-shadow-none { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
    }
  </style>
</head>
<body 
  x-data="{ 
    darkMode: document.documentElement.classList.contains('dark')
  }" 
  class="min-h-full bg-slate-50 dark:bg-slate-950 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-emerald-500 selection:text-white flex flex-col"
>

  <!-- Sticky Top Navigation Bar -->
  <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/90 no-print">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      
      <!-- Left Logo & Breadcrumb -->
      <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30 text-lg hover:bg-emerald-700 transition-colors">
          <i class="ph-bold ph-arrow-left"></i>
        </a>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-bold text-sm text-slate-900 dark:text-white">HFMS Compliance</span>
            <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-mono">DOH NETWORK</span>
          </div>
          <p class="text-[11px] text-slate-400">Terms &amp; Conditions of System Use</p>
        </div>
      </div>

      <!-- Right Actions -->
      <div class="flex items-center gap-2">
        <a href="{{ route('legal.privacy') }}" class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
          Privacy Policy
        </a>

        <button 
          type="button" 
          onclick="window.print()" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer"
        >
          <i class="ph-bold ph-printer text-sm"></i>
          <span>Print Document</span>
        </button>

        <!-- Dark Mode Toggle -->
        <button 
          type="button" 
          @click="
            darkMode = !darkMode;
            if (darkMode) {
              document.documentElement.classList.add('dark');
              document.documentElement.style.colorScheme = 'dark';
              localStorage.setItem('fms_theme', 'dark');
            } else {
              document.documentElement.classList.remove('dark');
              document.documentElement.style.colorScheme = 'light';
              localStorage.setItem('fms_theme', 'light');
            }
          " 
          class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white transition-colors cursor-pointer"
          aria-label="Toggle theme"
        >
          <i class="ph-bold ph-moon text-sm block dark:hidden"></i>
          <i class="ph-bold ph-sun text-sm hidden dark:block"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- Document Main Container -->
  <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <div class="rounded-3xl bg-white dark:bg-slate-900 p-6 sm:p-10 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-800 print-shadow-none">
      
      <!-- Formal Document Header -->
      <div class="border-b border-slate-200 dark:border-slate-800 pb-6 mb-8 text-center sm:text-left">
        <div class="flex items-center gap-2 mb-2">
          <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 ring-1 ring-emerald-600/20 font-mono">
            LEGAL ARCHITECTURE SPECIFICATION
          </span>
          <span class="text-xs text-slate-400">&bull; Effective Fiscal Year 2026</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
          Hospital Financial Management System (HFMS) Terms and Conditions
        </h1>
        <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
          Operational Governance, Commission on Audit (COA) Auditing Mandates, Acceptable Use Directives, and Statutory Penal Provisions under Republic Act No. 11463 and Philippine Financial Jurisprudence.
        </p>
      </div>

      <!-- Legal Body Content -->
      @include('legal.content-terms')

      <!-- Sign-off Seal -->
      <div class="mt-12 pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 dark:text-slate-500 gap-3">
        <div>
          Official System Publication &bull; Department of Health &bull; Republic of the Philippines
        </div>
        <div class="font-mono">
          Document Ref: HFMS-TC-GOV-2026.1
        </div>
      </div>

    </div>

  </main>

  <!-- Document Footer -->
  <footer class="border-t border-slate-200 bg-white py-6 dark:border-slate-800 dark:bg-slate-900 text-center text-xs text-slate-500 dark:text-slate-400 no-print">
    <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div>
        Hospital Financial Management System (HFMS) &bull; DOH Network Compliance
      </div>
      <div class="flex items-center gap-4">
        <a href="{{ route('login') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400">Back to Sign In</a>
        <span>&bull;</span>
        <a href="{{ route('legal.privacy') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400">Privacy Policy</a>
      </div>
    </div>
  </footer>

</body>
</html>
