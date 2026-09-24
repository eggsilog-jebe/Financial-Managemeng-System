<header class="navbar-custom">
  <div class="navbar-left">
    <button class="menu-toggle" type="button" aria-label="Toggle navigation menu" aria-controls="app-sidebar" aria-expanded="false"><i class="ph ph-list" aria-hidden="true"></i></button>
    <div class="app-identity"><p class="app-identity-title text-truncate">Financial Management System</p><span class="app-identity-subtitle text-truncate">Transaction Core Suite</span></div>
  </div>
  <div class="navbar-center">
    <div class="search-wrap">
      <label class="search-box" for="global-search"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><span class="visually-hidden">Search FMS</span><input id="global-search" type="search" placeholder="Search accounts, ledgers, transactions, and reports..." autocomplete="off" aria-controls="search-results" aria-expanded="false"><kbd aria-hidden="true">/</kbd></label>
      <div class="search-results" id="search-results" role="listbox" hidden></div>
    </div>
  </div>
  <div class="navbar-right d-flex align-items-center gap-2">
    <div class="d-none d-md-flex align-items-center gap-2">
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
        <i class="ph ph-user-circle me-1"></i> {{ auth()->user()->role ?? 'CFO' }}
      </span>
      <small class="text-muted fw-semibold text-truncate" style="max-width: 160px;">{{ auth()->user()->name ?? 'Executive User' }}</small>
    </div>
  </div>
</header>
