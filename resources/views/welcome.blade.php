@extends('layouts.app')

@section('title', 'Financial Management System (TRANSACTION CORE)')
@section('module', 'finance')
@section('page', 'dashboard')

@section('content')
<div class="container-fluid p-4">
  <!-- Dashboard Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Financial Management System</h1>
      <p class="text-muted mb-0">Transaction Core Modules &amp; Executive Command Dashboard</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Summary</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Transaction Core Active');"><i class="ph ph-lightning me-1"></i> System Status: Optimal</button>
    </div>
  </div>

  <!-- Key KPI Metrics Row (Fixed Font Sizes & Zero Overlapping) -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase kpi-title-text">Total Ledger Balance</span>
          <span class="badge bg-success-subtle text-success"><i class="ph ph-trend-up me-1"></i> +12.4%</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark kpi-value-text">₱4,850,240.00</h3>
        <span class="fs-xs text-muted kpi-sub-text">Real-time General Ledger Balance</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase kpi-title-text">Accounts Receivable</span>
          <span class="badge bg-primary-subtle text-primary"><i class="ph ph-clock me-1"></i> Pending</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark kpi-value-text">₱1,230,500.00</h3>
        <span class="fs-xs text-muted kpi-sub-text">142 Active Patient &amp; HMO Invoices</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase kpi-title-text">Accounts Payable</span>
          <span class="badge bg-warning-subtle text-warning"><i class="ph ph-calendar me-1"></i> Scheduled</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark kpi-value-text">₱640,120.00</h3>
        <span class="fs-xs text-muted kpi-sub-text">38 Vendor Payments Due</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase kpi-title-text">Available Cash Pool</span>
          <span class="badge bg-info-subtle text-info"><i class="ph ph-shield-check me-1"></i> Optimal</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark kpi-value-text">₱2,980,000.00</h3>
        <span class="fs-xs text-muted kpi-sub-text">Liquid Across 4 Bank Accounts</span>
      </div>
    </div>
  </div>

  <!-- Modules Grid Header -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h5 mb-0 font-weight-bold">Transaction Core Systems &amp; Modules</h2>
    <span class="badge bg-light text-dark border">9 Modules Active</span>
  </div>

  <!-- Optimized Module Cards Grid (Title on Top, Badge Below to Prevent Horizontal Squeezing) -->
  <div class="row g-3">
    @php
      $modules = [
        [
          'title' => 'General Ledger', 
          'icon' => 'ph-book-open', 
          'badge' => 'GL Core', 
          'badge_color' => 'bg-primary-subtle text-primary',
          'route' => route('gl.chart-of-accounts')
        ],
        [
          'title' => 'Accounts Payable (AP)', 
          'icon' => 'ph-receipt', 
          'badge' => 'Liabilities', 
          'badge_color' => 'bg-warning-subtle text-warning',
          'route' => route('ap.vendors')
        ],
        [
          'title' => 'Accounts Receivable (AR)', 
          'icon' => 'ph-currency-circle-dollar', 
          'badge' => 'Receivables', 
          'badge_color' => 'bg-success-subtle text-success',
          'route' => route('ar.customers')
        ],
        [
          'title' => 'Disbursement Management', 
          'icon' => 'ph-arrows-out', 
          'badge' => 'Treasury', 
          'badge_color' => 'bg-danger-subtle text-danger',
          'route' => route('disbursement.payment-requests')
        ],
        [
          'title' => 'Collection Management', 
          'icon' => 'ph-vault', 
          'badge' => 'Collections', 
          'badge_color' => 'bg-info-subtle text-info',
          'route' => route('collection.receipts')
        ],
        [
          'title' => 'Budget Management', 
          'icon' => 'ph-calculator', 
          'badge' => 'Planning', 
          'badge_color' => 'bg-purple-subtle text-purple',
          'route' => route('budget.fiscal-planning')
        ],
        [
          'title' => 'Cash Management', 
          'icon' => 'ph-coins', 
          'badge' => 'Liquidity', 
          'badge_color' => 'bg-teal-subtle text-teal',
          'route' => route('cash.bank-accounts')
        ],
        [
          'title' => 'Financial Reporting', 
          'icon' => 'ph-chart-line-up', 
          'badge' => 'Analytics', 
          'badge_color' => 'bg-indigo-subtle text-indigo',
          'route' => route('reporting.balance-sheet')
        ],
        [
          'title' => 'Tax Management', 
          'icon' => 'ph-percent', 
          'badge' => 'Compliance', 
          'badge_color' => 'bg-dark-subtle text-dark',
          'route' => route('tax.tax-config')
        ],
      ];
    @endphp

    @foreach($modules as $mod)
    <div class="col-md-4">
      <a href="{{ $mod['route'] }}" class="card border-0 shadow-sm rounded-3 text-decoration-none module-card-item h-100">
        <div class="card-body p-3.5 d-flex align-items-center gap-3">
          <div class="module-icon-wrap rounded-3 bg-light border text-primary">
            <i class="ph {{ $mod['icon'] }} fs-4"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <h3 class="fw-bold text-dark mb-1 module-title-text text-truncate">{{ $mod['title'] }}</h3>
            <span class="badge {{ $mod['badge_color'] }} fs-xs px-2 py-0.5 fw-semibold">{{ $mod['badge'] }}</span>
          </div>
        </div>
      </a>
    </div>
    @endforeach
  </div>
</div>

<style>
.module-card-item {
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
  border: 1px solid rgba(0,0,0,0.07) !important;
}
.module-card-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06) !important;
  border-color: var(--color-primary, #00a86b) !important;
}
.module-title-text {
  font-size: 0.875rem !important;
  line-height: 1.25 !important;
}
.module-icon-wrap {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kpi-title-text {
  font-size: 0.725rem !important;
  letter-spacing: 0.03em;
}
.kpi-value-text {
  font-size: 1.35rem !important;
  line-height: 1.2;
}
.kpi-sub-text {
  font-size: 0.735rem !important;
}
</style>
@endsection
