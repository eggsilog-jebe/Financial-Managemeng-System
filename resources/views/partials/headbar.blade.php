<header class="sticky top-0 z-30 flex h-14 sm:h-16 w-full items-center justify-between border-b border-slate-200/80 bg-white/90 px-6 lg:px-8 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/90 transition-colors">
  <!-- Left: Mobile Toggle & App Identity -->
  <div class="flex items-center gap-3">
    <button 
      @click="sidebarOpen = !sidebarOpen" 
      type="button" 
      class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
      aria-label="Toggle navigation drawer"
    >
      <i class="ph-bold ph-list text-xl"></i>
    </button>

    <div class="hidden sm:flex flex-col">
      <div class="flex items-center gap-2">
        <span class="text-sm font-bold tracking-tight text-slate-900 dark:text-white">
          Financial Management System
        </span>
        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">
          GAAP / IFRS
        </span>
      </div>
      <span class="text-[11px] text-slate-500 dark:text-slate-400">
        Hospital Clinical &amp; Transaction Ledger Core
      </span>
    </div>
  </div>

  <!-- Center: Global Search Bar with Ctrl+K shortcut -->
  <div class="hidden md:flex flex-1 max-w-md mx-4" x-data="{
    focused: false,
    query: '',
    results: [],
    init() {
      window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
          e.preventDefault();
          this.$refs.searchInput.focus();
        }
      });
    }
  }">
    <div class="relative w-full">
      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
        <i class="ph-bold ph-magnifying-glass text-base"></i>
      </div>
      <input 
        x-ref="searchInput"
        type="search" 
        x-model="query"
        @focus="focused = true"
        @blur="setTimeout(() => focused = false, 200)"
        placeholder="Quick search accounts, patient bills, vouchers..." 
        class="w-full rounded-xl border-0 bg-slate-100/80 py-2 pl-10 pr-12 text-xs sm:text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700 transition-all"
      >
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5">
        <kbd class="hidden sm:inline-flex items-center rounded border border-slate-300 bg-slate-50 px-1.5 font-mono text-[10px] font-medium text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
          Ctrl K
        </kbd>
      </div>
    </div>
  </div>

  <!-- Right: Shift Status, Workstation Indicator, Theme Toggle, Profile -->
  <div class="flex items-center gap-2 sm:gap-3">
    <!-- Workstation Security Indicator -->
    <div class="hidden xl:flex items-center gap-1.5 rounded-xl bg-slate-100 px-2.5 py-1.5 text-xs text-slate-600 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
      <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
      <span class="font-mono text-[11px] font-medium">TERMINAL-ACTIVE</span>
    </div>

    <!-- Theme Toggle Button (Light by default) -->
    <button 
      type="button" 
      @click="
        if (typeof window.setFmsTheme === 'function') {
          window.setFmsTheme(darkMode ? 'light' : 'dark');
        } else {
          darkMode = !darkMode;
          if (darkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('fms_theme', 'dark');
            localStorage.setItem('himsMainTheme', 'dark');
          } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('fms_theme', 'light');
            localStorage.setItem('himsMainTheme', 'light');
          }
        }
      " 
      class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white transition-colors"
      :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
      :aria-label="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
    >
      {{-- In Light Mode: Show Moon icon to switch to Dark Mode --}}
      <i class="ph-bold ph-moon text-lg block dark:hidden" aria-hidden="true"></i>
      {{-- In Dark Mode: Show Sun icon to switch to Light Mode --}}
      <i class="ph-bold ph-sun text-lg hidden dark:block" aria-hidden="true"></i>
    </button>

    <!-- Executive User Profile Menu (Alpine.js) -->
    <div 
      class="relative pl-2 border-l border-slate-200 dark:border-slate-800" 
      x-data="{
        userMenuOpen: false,
        userName: '{{ addslashes(auth()->user()->name ?? 'Executive') }}',
        userEmail: '{{ addslashes(auth()->user()->email ?? 'user@hospital.gov.ph') }}',
        userAvatar: '{{ auth()->user()->avatarUrl() ?? '' }}'
      }"
      @user-profile-updated.window="
        if ($event.detail.name) userName = $event.detail.name;
        if ($event.detail.email) userEmail = $event.detail.email;
        if ($event.detail.avatarUrl !== undefined) userAvatar = $event.detail.avatarUrl;
      "
    >
      <button 
        type="button" 
        @click="userMenuOpen = !userMenuOpen" 
        @click.outside="userMenuOpen = false"
        class="flex items-center gap-2 rounded-xl p-1 text-left transition-colors hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none"
        aria-expanded="false"
        :aria-expanded="userMenuOpen.toString()"
      >
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden shrink-0">
          <template x-if="userAvatar">
            <img :src="userAvatar" :alt="userName" class="h-full w-full object-cover">
          </template>
          <template x-if="!userAvatar">
            <i class="ph-bold ph-user text-sm"></i>
          </template>
        </span>
        <div class="hidden sm:flex flex-col text-left">
          <span class="text-xs font-semibold text-slate-900 dark:text-white max-w-[120px] truncate leading-tight" x-text="userName">
            {{ auth()->user()->name ?? 'Executive' }}
          </span>
          <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 leading-tight">
            {{ auth()->user()->role ?? 'Hospital Staff' }}
          </span>
        </div>
        <i class="ph-bold ph-caret-down text-xs text-slate-400 transition-transform" :class="{ 'rotate-180': userMenuOpen }"></i>
      </button>

      <!-- Dropdown Panel -->
      <div 
        x-show="userMenuOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-64 origin-top-right rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 z-50 divide-y divide-slate-100 dark:divide-slate-800"
      >
        <div class="px-3 py-2.5">
          <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="userName">{{ auth()->user()->name ?? 'Executive' }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="userEmail">{{ auth()->user()->email ?? 'user@hospital.gov.ph' }}</p>
          <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">
            <i class="ph-bold ph-shield-check"></i>
            <span>{{ auth()->user()->role ?? 'Finance' }}</span>
          </div>
        </div>

        <div class="py-1">
          <a href="{{ route('account.settings') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors">
            <i class="ph-bold ph-gear text-slate-400"></i>
            <span>Account Settings</span>
          </a>
        </div>

        <div class="pt-1">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30 transition-colors">
              <i class="ph-bold ph-sign-out text-base"></i>
              <span>Sign Out of Terminal</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>
