@php
  $isDashboard = request()->routeIs('accounting.dashboard') || request()->routeIs('dashboard');
  $isGl = request()->routeIs('gl.*') || request()->routeIs('accounting.general-ledger.*') || request()->routeIs('accounting.period-close.*');
  $isAp = request()->routeIs('ap.*');
  $isAr = request()->routeIs('ar.*');
  $isDisbursement = request()->routeIs('disbursement.*');
  $isCollection = request()->routeIs('collection.*') || request()->routeIs('accounting.cashier.*');
  $isBudget = request()->routeIs('budget.*');
  $isCash = request()->routeIs('cash.*');
  $isReporting = request()->routeIs('reporting.*') || request()->routeIs('accounting.reports.*');
  $isUserSecurity = request()->routeIs('user-security.*') || request()->routeIs('accounting.audit-log');
@endphp

<!-- Mobile Backdrop -->
<div 
  x-show="sidebarOpen" 
  x-cloak
  @click="sidebarOpen = false" 
  x-transition.opacity.duration.200ms 
  class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
  aria-hidden="true"
></div>

<aside 
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-white border-r border-slate-200/80 dark:bg-slate-900 dark:border-slate-800 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 select-none shadow-sm lg:shadow-none"
  aria-label="Primary navigation"
>
  <!-- Brand Header (Clean Light Standard Web System) -->
  <div class="flex h-16 flex-shrink-0 items-center justify-between px-5 border-b border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900">
    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
      <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/20 group-hover:bg-emerald-700 transition-colors">
        <i class="ph-bold ph-first-aid text-lg"></i>
      </div>
      <div class="flex flex-col min-w-0">
        <div class="flex items-center gap-1.5">
          <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white">FMS</span>
          <span class="inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30">HIMS</span>
        </div>
        <span class="text-xs text-slate-500 dark:text-slate-400 truncate font-normal">Financial Core</span>
      </div>
    </a>
    <button 
      type="button" 
      @click="sidebarOpen = false" 
      class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-white dark:hover:bg-slate-800 lg:hidden transition-colors" 
      aria-label="Close navigation"
    >
      <i class="ph-bold ph-x text-base"></i>
    </button>
  </div>

  <!-- Navigation Links (Minimalist Major Modules, Zero Dropdowns inside Sidebar) -->
  <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1 custom-scrollbar" aria-label="Main menu">
    
    @can('access-general-ledger')
    <a 
      href="{{ route('accounting.dashboard') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isDashboard ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-squares-four text-lg shrink-0 {{ $isDashboard ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Dashboard</span>
      </div>
      @if($isDashboard)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 1. Accounts Payable (AP) -->
    @can('access-ap-procurement')
    <a 
      href="{{ route('ap.vendors') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isAp ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-receipt text-lg shrink-0 {{ $isAp ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Accounts Payable</span>
      </div>
      @if($isAp)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 2. Accounts Receivable (AR) -->
    @can('access-ar-billing')
    <a 
      href="{{ route('ar.billing') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isAr ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-currency-circle-dollar text-lg shrink-0 {{ $isAr ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Accounts Receivable</span>
      </div>
      @if($isAr)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 3. Disbursement Management -->
    @can('access-disbursements')
    <a 
      href="{{ route('disbursement.payment-requests') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isDisbursement ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-arrows-out text-lg shrink-0 {{ $isDisbursement ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Disbursement</span>
      </div>
      @if($isDisbursement)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 4. Collection & Treasury -->
    @can('access-cashier-pos')
    <a 
      href="{{ route('collection.cashier-desk') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isCollection ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-hand-coins text-lg shrink-0 {{ $isCollection ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Collection &amp; POS</span>
      </div>
      @if($isCollection)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 5. Budget Management -->
    @can('access-budget')
    <a 
      href="{{ route('budget.fiscal-planning') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isBudget ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-calculator text-lg shrink-0 {{ $isBudget ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Budget Management</span>
      </div>
      @if($isBudget)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 6. General Ledger (GL) -->
    @can('access-general-ledger')
    <a 
      href="{{ route('gl.journal-entries') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isGl ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-book-open-text text-lg shrink-0 {{ $isGl ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">General Ledger</span>
      </div>
      @if($isGl)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 7. Financial Reporting -->
    @can('access-financial-reports')
    <a 
      href="{{ route('reporting.balance-sheet') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isReporting ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-chart-line-up text-lg shrink-0 {{ $isReporting ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Financial Reporting</span>
      </div>
      @if($isReporting)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 8. Cash & Banking -->
    @can('access-cash-management')
    <a 
      href="{{ route('cash.bank-accounts') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isCash ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-coins text-lg shrink-0 {{ $isCash ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">Cash &amp; Banking</span>
      </div>
      @if($isCash)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
      @endif
    </a>
    @endcan

    <!-- 9. User & Security -->
    @can('access-user-management')
    <a 
      href="{{ route('user-security.users') }}" 
      class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isUserSecurity ? 'bg-emerald-50 text-emerald-800 font-semibold ring-1 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50' }}"
    >
      <div class="flex items-center gap-3 min-w-0">
        <i class="ph-bold ph-shield-check text-lg shrink-0 {{ $isUserSecurity ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}"></i>
        <span class="truncate">User &amp; Security</span>
      </div>
      <div class="flex items-center gap-1.5">
        @if(($pendingWorkstationsCount ?? 0) > 0)
          <span class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-950/60 dark:text-amber-300">{{ $pendingWorkstationsCount }}</span>
        @endif
        @if($isUserSecurity)
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 dark:bg-emerald-400 shrink-0"></span>
        @endif
      </div>
    </a>
    @endcan
  </nav>

  <!-- Sidebar Footer: System Status & Version Badge -->
  <div class="p-3 border-t border-slate-200/80 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-900/60">
    <div class="flex items-center justify-between px-2 py-1 text-[11px] text-slate-500 dark:text-slate-400">
      <div class="flex items-center gap-2">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="font-medium text-slate-600 dark:text-slate-300">Terminal Online</span>
      </div>
      <span class="font-mono text-[10px] text-slate-400">v2.4 CAS</span>
    </div>
  </div>
</aside>
