<header class="navbar-custom">
  <div class="navbar-left">
    <button class="menu-toggle" type="button" aria-label="Collapse sidebar" aria-controls="app-sidebar" aria-expanded="true"><i class="ph ph-list" aria-hidden="true"></i></button>
    <div class="app-identity"><p class="app-identity-title">Financial Management System</p><span class="app-identity-subtitle">Transaction Core Suite</span></div>
  </div>
  <div class="navbar-center">
    <div class="search-wrap">
      <label class="search-box" for="global-search"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><span class="visually-hidden">Search FMS</span><input id="global-search" type="search" placeholder="Search accounts, ledgers, transactions, and reports..." autocomplete="off" aria-controls="search-results" aria-expanded="false"><kbd aria-hidden="true">/</kbd></label>
      <div class="search-results" id="search-results" role="listbox" hidden></div>
    </div>
  </div>
  <div class="navbar-right d-flex align-items-center gap-2">
    <div class="d-none d-md-flex align-items-center gap-2 me-2">
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
        <i class="ph ph-user-circle me-1"></i> {{ auth()->user()->role ?? 'CFO' }}
      </span>
      <small class="text-muted fw-semibold">{{ auth()->user()->name ?? 'Executive Demo User' }}</small>
    </div>
    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Switch Demo Role">
      <i class="ph ph-arrows-clockwise me-1"></i> Switch Role
    </a>
    <a href="{{ route('logout.get') }}" class="btn btn-sm btn-outline-danger py-1 px-2" title="Logout">
      <i class="ph ph-sign-out"></i>
    </a>
  </div>
</header>
