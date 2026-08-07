@extends('layouts.app')

@section('title', 'Financial Management System (TRANSACTION CORE)')
@section('module', 'finance')
@section('page', 'dashboard')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Financial Management System</h1>
      <p class="text-muted mb-0">Transaction Core Modules Overview</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export Report</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> New Transaction</button>
    </div>
  </div>

  <!-- Key Metrics Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">Total Ledger Balance</span>
            <span class="badge bg-success-subtle text-success"><i class="ph ph-trend-up"></i> +12.4%</span>
          </div>
          <h3 class="fw-bold mb-0">₱4,850,240.00</h3>
          <small class="text-muted">Updated live</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">Accounts Receivable</span>
            <span class="badge bg-primary-subtle text-primary">Pending</span>
          </div>
          <h3 class="fw-bold mb-0">₱1,230,500.00</h3>
          <small class="text-muted">142 Active Invoices</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">Accounts Payable</span>
            <span class="badge bg-warning-subtle text-warning">Scheduled</span>
          </div>
          <h3 class="fw-bold mb-0">₱640,120.00</h3>
          <small class="text-muted">38 Due Payments</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">Available Cash Pool</span>
            <span class="badge bg-info-subtle text-info">Optimal</span>
          </div>
          <h3 class="fw-bold mb-0">₱2,980,000.00</h3>
          <small class="text-muted">Across 6 Accounts</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Modules Grid -->
  <h2 class="h5 mb-3 font-weight-bold">Transaction Core Modules</h2>
  <div class="row g-3">
    @php
      $modules = [
        ['title' => 'General Ledger', 'desc' => 'Central repository for all accounting and journal entries.', 'icon' => 'ph-book-open', 'badge' => 'Core', 'route' => route('gl.chart-of-accounts')],
        ['title' => 'Accounts Payable (AP)', 'desc' => 'Manage vendor invoices, vouchers, and outgoing payments.', 'icon' => 'ph-receipt', 'badge' => 'Active', 'route' => route('ap.vendors')],
        ['title' => 'Accounts Receivable (AR)', 'desc' => 'Track customer billings, collections, and outstanding receivables.', 'icon' => 'ph-currency-circle-dollar', 'badge' => 'Active', 'route' => route('ar.customers')],
        ['title' => 'Disbursement Management', 'desc' => 'Authorize, approve, and execute disbursements seamlessly.', 'icon' => 'ph-arrows-out', 'badge' => 'Active', 'route' => route('disbursement.payment-requests')],
        ['title' => 'Collection Management', 'desc' => 'Payment processing, receipts, and cash inflow reconciliation.', 'icon' => 'ph-vault', 'badge' => 'Active', 'route' => route('collection.receipts')],
        ['title' => 'Budget Management', 'desc' => 'Fiscal planning, budget allocation, and variance analysis.', 'icon' => 'ph-calculator', 'badge' => 'Active', 'route' => route('budget.fiscal-planning')],
        ['title' => 'Cash Management', 'desc' => 'Bank reconciliation, liquidity planning, and cash flow tracking.', 'icon' => 'ph-coins', 'badge' => 'New', 'route' => route('cash.bank-accounts')],
        ['title' => 'Financial Reporting & Analytics', 'desc' => 'Balance sheets, P&L statements, and real-time BI insights.', 'icon' => 'ph-chart-line-up', 'badge' => 'New', 'route' => route('reporting.balance-sheet')],
        ['title' => 'Tax Management', 'desc' => 'Tax computation, withholding compliance, and tax filings.', 'icon' => 'ph-percent', 'badge' => 'New', 'route' => route('tax.tax-config')],
      ];
    @endphp

    @foreach($modules as $mod)
    <div class="col-md-4">
      <a href="{{ $mod['route'] }}" class="card h-100 border-0 shadow-sm rounded-3 text-decoration-none module-card-link">
        <div class="card-body d-flex flex-column">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
              <div class="p-2 rounded-3 bg-primary-subtle text-primary me-3">
                <i class="ph {{ $mod['icon'] }} fs-4"></i>
              </div>
              <div>
                <h3 class="h6 mb-0 font-weight-bold text-dark">{{ $mod['title'] }}</h3>
                <span class="badge bg-secondary-subtle text-secondary fs-xs">{{ $mod['badge'] }}</span>
              </div>
            </div>
            <i class="ph ph-arrow-right text-muted opacity-50 module-arrow-icon fs-5"></i>
          </div>
          <p class="text-muted small mb-0 mt-auto">{{ $mod['desc'] }}</p>
        </div>
      </a>
    </div>
    @endforeach
  </div>
</div>

<style>
.module-card-link {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  cursor: pointer;
}
.module-card-link:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
}
.module-card-link:hover .module-arrow-icon {
  color: var(--color-primary) !important;
  opacity: 1 !important;
  transform: translateX(3px);
  transition: all 0.2s ease;
}
</style>
@endsection
