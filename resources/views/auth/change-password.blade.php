<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Change Password &mdash; Hospital Financial Management System (HIMS &bull; FMS)</title>
  <x-favicon />

  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Immediate Theme Boot (Default: Light, Synced with fms_theme & colorScheme) -->
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

  <!-- Tailwind CSS v4 & Alpine.js Asset Bundle -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body 
  x-data="{ 
    helpdeskOpen: false,
    darkMode: document.documentElement.classList.contains('dark')
  }" 
  class="h-full bg-slate-50 dark:bg-slate-950 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-emerald-500 selection:text-white"
>

  <div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Left Hero & Security Authority Column (Universal Standard) -->
    <x-auth-hero />

    <!-- Right Change Password Panel Column -->
    <div class="relative flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-16">
      
      <!-- Top Actions (Theme Toggle & Helpdesk trigger) -->
      <div class="flex items-center justify-end gap-3">
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
          class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white transition-colors cursor-pointer"
          :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <i class="ph-bold ph-moon text-base block dark:hidden"></i>
          <i class="ph-bold ph-sun text-base hidden dark:block"></i>
        </button>

        <button 
          type="button" 
          @click="helpdeskOpen = true" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <i class="ph-bold ph-headset text-sm text-emerald-600 dark:text-emerald-400"></i>
          <span>IT Helpdesk</span>
        </button>
      </div>

      <!-- Center Auth Form Container -->
      <div class="my-auto mx-auto w-full max-w-md py-6">
        
        <!-- Hospital Header Seal & Department Badge -->
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-200 dark:border-slate-800">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30">
            <i class="ph-bold ph-hospital text-lg"></i>
          </div>
          <div>
            <h2 class="text-xs font-bold text-slate-900 dark:text-white tracking-wide uppercase">Republic of the Philippines</h2>
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Department of Health &bull; Hospital Network</p>
          </div>
        </div>

        <!-- Top Badge -->
        <div class="mb-3">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-950/60 dark:text-amber-300 font-mono tracking-wider uppercase">
            <i class="ph-bold ph-shield-warning text-xs"></i>
            SECURITY CREDENTIAL UPDATE
          </span>
        </div>

        <!-- Title & Instructions -->
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Password Reset Required</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
            Your temporary credentials must be updated before accessing clinical billing and ledger modules.
          </p>
        </div>

        @if(session('warning'))
          <div class="mt-4 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300 flex items-center gap-2">
            <i class="ph-fill ph-warning text-base text-amber-600 dark:text-amber-400 shrink-0"></i>
            <span>{{ session('warning') }}</span>
          </div>
        @endif

        @if(session('error'))
          <div class="mt-4 rounded-xl border border-rose-200/80 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2">
            <i class="ph-fill ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}" id="form-change-password" class="mt-5 space-y-4">
          @csrf

          <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
              New Password <span class="text-rose-500">*</span>
            </label>
            <div class="relative" x-data="{ show: false }">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ph-bold ph-lock-key"></i>
              </div>
              <input :type="show ? 'text' : 'password'" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-10 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-600 @error('password') border-rose-500 focus:border-rose-500 @enderror"
                     id="password" name="password" required autocomplete="new-password"
                     placeholder="Min 8 characters, alpha-numeric">
              <button 
                type="button" 
                @click="show = !show" 
                :aria-label="show ? 'Hide password' : 'Show password'"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
              >
                <i class="ph-bold text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
              </button>
            </div>
            @error('password')
              <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</p>
            @enderror
            <span class="block text-[11px] text-slate-400 mt-1">Must contain mixed case, numbers, and at least 8 characters.</span>
          </div>

          <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
              Confirm New Password <span class="text-rose-500">*</span>
            </label>
            <div class="relative" x-data="{ show: false }">
              <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ph-bold ph-check-square-offset"></i>
              </div>
              <input :type="show ? 'text' : 'password'" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-10 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-600"
                     id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                     placeholder="Re-enter your new password">
              <button 
                type="button" 
                @click="show = !show" 
                :aria-label="show ? 'Hide password' : 'Show password'"
                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
              >
                <i class="ph-bold text-base" :class="show ? 'ph-eye-slash' : 'ph-eye'"></i>
              </button>
            </div>
          </div>

          <div class="pt-2">
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 px-4 text-xs font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-700 active:bg-emerald-800 transition-all cursor-pointer" id="btn-change-password">
              <i class="ph-bold ph-lock-key text-base"></i>
              <span>Set New Password &amp; Continue</span>
            </button>
          </div>
        </form>

        <div class="text-center mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
              <i class="ph-bold ph-sign-out"></i>
              <span>Log out instead</span>
            </button>
          </form>
        </div>

      </div>

      <!-- Bottom Mini Footer -->
      <div class="text-center text-xs text-slate-400 dark:text-slate-500">
        Hospital Financial Management System &bull; Secure Terminal Gateway
      </div>
    </div>

  </div>

  <!-- IT Helpdesk Support Directory Modal -->
  <x-auth-helpdesk-modal />

</body>
</html>
