@extends('layouts.app')

@section('title', 'Executive Financial Dashboard & Hospital Governance')
@section('module', 'finance')
@section('page', 'dashboard')

@section('content')
<div class="container-fluid p-4">

  <!-- Executive Context Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <h1 class="h3 mb-0 font-weight-bold text-dark">Executive Financial Overview</h1>
        <span class="badge bg-primary text-white fs-xs px-2 py-1">PH PUBLIC HOSPITAL CORE</span>
      </div>
      <p class="text-muted mb-0">
        Hospital Financial Management System &bull; Active Role: 
        <strong class="text-dark">{{ $currentUser->name ?? $userRole }}</strong> 
        <span class="badge bg-secondary-subtle text-secondary ms-1">({{ $userRole }})</span>
      </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <!-- Last Login Telemetry Badge -->
      @if($currentUser?->last_login_at)
        <span class="badge bg-white text-muted border shadow-sm p-2 d-flex align-items-center gap-1">
          <i class="ph ph-clock text-primary fs-6"></i>
          <span class="fs-xs">Last Login: <strong>{{ $currentUser->last_login_at->diffForHumans() }}</strong> ({{ $currentUser->last_login_ip ?? '127.0.0.1' }})</span>
        </span>
      @endif

      <x-integration-badge 
          type="internal" 
          :internalModules="['Executive KPIs', 'General Ledger', 'Malasakit Center', 'COA 2021-014 Intact Recon', 'Departmental Budgets', 'Accounts Receivable']"
          :tables="['journal_entries', 'budget_allocations', 'guarantee_letters', 'philhealth_claims', 'payments', 'bank_deposits']"
          glImpact="Live synchronization across GAA fiscal budgets, PhilHealth UHC claims, and daily treasury deposits"
          description="Consolidated executive command overview visualizing cross-department financial health, subsidy distribution, and statutory audit integrity."
      />
    </div>
  </div>

  <!-- Role-Tailored Operational Quick Action Bar -->
  <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
        <i class="ph ph-lightning text-warning fs-5"></i>
        <span>Quick Financial Operations — <span class="text-primary">{{ $currentUser->name ?? $userRole }}</span> Command Panel</span>
      </h6>
      <span class="fs-xs text-muted">Role-Adaptive Shortcuts</span>
    </div>

    <div class="row g-2 pt-1">
      @if(in_array($userRole, ['CFO', 'FinanceManager', 'SuperAdmin'], true))
        <div class="col-md-2 col-6">
          <a href="{{ route('accounting.reports.index') }}" class="btn btn-outline-primary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-chart-line-up fs-5 text-primary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Financial Hub</strong>
              <span class="fs-xxs text-muted">P&amp;L / Balance Sheet</span>
            </div>
          </a>
        </div>
        <div class="col-md-2 col-6">
          <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-outline-secondary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-book-open fs-5 text-secondary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">General Ledger</strong>
              <span class="fs-xxs text-muted">Journal Entries</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('ar.malasakit.index') }}" class="btn btn-outline-purple w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2" style="border-color: #8b5cf6; color: #6d28d9;">
            <i class="ph ph-hand-coins fs-5 text-purple" style="color: #8b5cf6;"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Malasakit Center</strong>
              <span class="fs-xxs text-muted">RA 11463 GL Assistance</span>
            </div>
          </a>
        </div>
        <div class="col-md-2 col-6">
          <a href="{{ url('/budget') }}" class="btn btn-outline-info w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-scales fs-5 text-info"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Fiscal Budgets</strong>
              <span class="fs-xxs text-muted">GAA Allocations</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-12">
          <a href="{{ route('accounting.audit-log') }}" class="btn btn-outline-dark w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-shield-check fs-5 text-dark"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Audit Trail Hub</strong>
              <span class="fs-xxs text-muted">Immutable Ledger Logs</span>
            </div>
          </a>
        </div>
      @elseif($userRole === 'Auditor')
        <div class="col-md-3 col-6">
          <a href="{{ route('accounting.audit-log') }}" class="btn btn-outline-dark w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-shield-check fs-5 text-dark"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Audit Trail System</strong>
              <span class="fs-xxs text-muted">Immutable Activity Logs</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-outline-secondary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-book-open fs-5 text-secondary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Journal Browser</strong>
              <span class="fs-xxs text-muted">Verify Postings</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('accounting.reports.index') }}" class="btn btn-outline-primary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-scales fs-5 text-primary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Trial Balance</strong>
              <span class="fs-xxs text-muted">Verify Invariance</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('ar.malasakit.index') }}" class="btn btn-outline-info w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-file-text fs-5 text-info"></i>
            <div>
              <strong class="d-block fs-xs text-dark">GL Subsidies Audit</strong>
              <span class="fs-xxs text-muted">Review MAIP / PCSO</span>
            </div>
          </a>
        </div>
      @elseif($userRole === 'BillingClerk')
        <div class="col-md-4 col-12">
          <a href="{{ route('ar.malasakit.index') }}" class="btn btn-outline-purple w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2" style="border-color: #8b5cf6; color: #6d28d9;">
            <i class="ph ph-hand-coins fs-5 text-purple" style="color: #8b5cf6;"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Malasakit Desk</strong>
              <span class="fs-xxs text-muted">Register GLs &amp; Simulate Bills</span>
            </div>
          </a>
        </div>
        <div class="col-md-4 col-6">
          <a href="{{ url('/billing/invoices') }}" class="btn btn-outline-primary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-receipt fs-5 text-primary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Patient Invoices</strong>
              <span class="fs-xxs text-muted">Billing &amp; NBB Processing</span>
            </div>
          </a>
        </div>
        <div class="col-md-4 col-6">
          <a href="{{ route('collection.cashier-desk') }}" class="btn btn-outline-secondary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-hand-coins fs-5 text-secondary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Cashier Counter</strong>
              <span class="fs-xxs text-muted">Patient Co-Pay Reception</span>
            </div>
          </a>
        </div>
      @elseif($userRole === 'Cashier')
        <div class="col-md-4 col-12">
          <a href="{{ route('collection.cashier-desk') }}" class="btn btn-primary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-hand-coins fs-5 text-white"></i>
            <div>
              <strong class="d-block fs-xs text-white">Cashier Counter</strong>
              <span class="fs-xxs text-white-50">Issue Official Receipts (OR)</span>
            </div>
          </a>
        </div>
        <div class="col-md-4 col-6">
          <a href="{{ url('/collection/deposits') }}" class="btn btn-outline-success w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-bank fs-5 text-success"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Bank Deposit Slip</strong>
              <span class="fs-xxs text-muted">COA Daily Intact Remittance</span>
            </div>
          </a>
        </div>
        <div class="col-md-4 col-6">
          <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-outline-secondary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-receipt fs-5 text-secondary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Receipts Journal</strong>
              <span class="fs-xxs text-muted">Review Shift Batches</span>
            </div>
          </a>
        </div>
      @else
        <!-- Staff Accountant Default -->
        <div class="col-md-3 col-6">
          <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-outline-secondary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-plus-circle fs-5 text-secondary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Journal Browser</strong>
              <span class="fs-xxs text-muted">Post &amp; Reverse Entries</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('accounting.reports.index') }}" class="btn btn-outline-primary w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-scales fs-5 text-primary"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Trial Balance</strong>
              <span class="fs-xxs text-muted">Verify GAAP Balance</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('collection.cashier-desk') }}" class="btn btn-outline-info w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2">
            <i class="ph ph-receipt fs-5 text-info"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Cashier Counter</strong>
              <span class="fs-xxs text-muted">Collections Verification</span>
            </div>
          </a>
        </div>
        <div class="col-md-3 col-6">
          <a href="{{ route('ar.malasakit.index') }}" class="btn btn-outline-purple w-100 text-start p-2 rounded-2 d-flex align-items-center gap-2" style="border-color: #8b5cf6; color: #6d28d9;">
            <i class="ph ph-hand-coins fs-5 text-purple" style="color: #8b5cf6;"></i>
            <div>
              <strong class="d-block fs-xs text-dark">Malasakit Assistance</strong>
              <span class="fs-xxs text-muted">GL Accounting</span>
            </div>
          </a>
        </div>
      @endif
    </div>
  </div>

  <!-- Primary Financial Metric Cards -->
  <div class="row g-3 mb-4">
    <!-- Total Revenue -->
    <div class="col-md-3 col-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Hospital Revenue</span>
          <span class="badge bg-success-subtle text-success"><i class="ph ph-arrow-up-right me-1"></i> Live P&amp;L</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($totalRevenue, 2) }}</h3>
        <span class="fs-xs text-muted">Clinical, Diagnostic &amp; Pharmacy Revenue</span>
      </div>
    </div>

    <!-- Cash on Hand (Drawer/Vault - Account 1010/1011) -->
    <div class="col-md-2 col-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Cash on Hand</span>
          <span class="badge bg-primary-subtle text-primary"><i class="ph ph-vault me-1"></i> Vault</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($cashOnHand, 2) }}</h3>
        <span class="fs-xs text-muted">Cashier Drawers &amp; Undeposited Cash</span>
      </div>
    </div>

    <!-- Cash in Bank (LBP Depository - Account 1020) -->
    <div class="col-md-2 col-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Cash in Bank</span>
          <span class="badge bg-info-subtle text-info"><i class="ph ph-bank me-1"></i> LBP AGDB</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($cashInBank, 2) }}</h3>
        <span class="fs-xs text-muted">Land Bank Operating Accounts</span>
      </div>
    </div>

    <!-- Outstanding AR -->
    <div class="col-md-2 col-6">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Outstanding AR</span>
          <span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Receivables</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($outstandingAR, 2) }}</h3>
        <span class="fs-xs text-muted">Open Patient &amp; HMO Balances</span>
      </div>
    </div>

    <!-- Outstanding AP -->
    <div class="col-md-3 col-12">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Outstanding AP</span>
          <span class="badge bg-danger-subtle text-danger"><i class="ph ph-warning-circle me-1"></i> Payables</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($outstandingAP, 2) }}</h3>
        <span class="fs-xs text-muted">Medical Suppliers &amp; Drug Vendors</span>
      </div>
    </div>
  </div>

  <!-- PHASE 4 WIDGET: Public Hospital Fund-Source Utilization & UHC Distribution -->
  <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="ph ph-hand-coins text-purple me-2" style="color: #8b5cf6;"></i>
            Public Hospital Fund Sources &amp; Universal Healthcare Co-Pay Coverage
          </h5>
          <span class="badge bg-success-subtle text-success border border-success-subtle fs-xs">
            <i class="ph ph-shield-check me-1"></i> RA 11223 &bull; RA 11463
          </span>
        </div>
        <p class="text-muted fs-xs mb-0 mt-1">
          Analysis of funding streams: PhilHealth Case Rates vs. Malasakit Center Subsidies vs. Direct Patient Out-of-Pocket
        </p>
      </div>

      <div class="text-end">
        <span class="fs-xs text-muted d-block">Public Protection Ratio:</span>
        <span class="badge bg-primary text-white fs-6 px-2 py-1">
          {{ $socialCoveragePct }}% Socialized Coverage
        </span>
      </div>
    </div>

    <div class="card-body px-4 pt-2 pb-4">
      <!-- Composite Stacked Progress Bar -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1 fs-xs">
          <span class="text-muted">Funding Stream Distribution (Total: <strong>₱{{ number_format($totalFundSources, 2) }}</strong>)</span>
          <span class="fw-semibold text-dark">{{ 100 - $directCopaySharePct }}% Government Subsidized &bull; {{ $directCopaySharePct }}% Patient Co-Pay</span>
        </div>
        <div class="progress" style="height: 14px; border-radius: 8px; overflow: hidden;">
          <div class="progress-bar bg-success" role="progressbar" style="width: {{ $philhealthSharePct }}%;" title="PhilHealth: {{ $philhealthSharePct }}%"></div>
          <div class="progress-bar" role="progressbar" style="width: {{ $malasakitSharePct }}%; background-color: #8b5cf6;" title="Malasakit Subsidies: {{ $malasakitSharePct }}%"></div>
          <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $directCopaySharePct }}%;" title="Direct Co-Pay: {{ $directCopaySharePct }}%"></div>
        </div>
      </div>

      <!-- 3 Breakdown Metric Cards -->
      <div class="row g-3">
        <!-- 1. PhilHealth Reimbursements -->
        <div class="col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="fw-bold text-dark fs-xs text-uppercase d-flex align-items-center gap-1">
                <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #10b981;"></span>
                PhilHealth UHC Reimbursements
              </span>
              <span class="badge bg-success text-white fs-xs">{{ $philhealthSharePct }}% Share</span>
            </div>
            <h4 class="fw-bold text-dark mb-1">₱{{ number_format($philhealthTotal, 2) }}</h4>
            <div class="fs-xs text-muted d-flex justify-content-between">
              <span>Claims Processed: <strong>{{ $philhealthCount }}</strong></span>
              <span>UHC Case Rate Pool</span>
            </div>
          </div>
        </div>

        <!-- 2. Malasakit Medical Assistance (MAIP / PCSO / DSWD) -->
        <div class="col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="fw-bold text-dark fs-xs text-uppercase d-flex align-items-center gap-1">
                <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #8b5cf6;"></span>
                Malasakit Center Subsidies
              </span>
              <span class="badge text-white fs-xs" style="background-color: #8b5cf6;">{{ $malasakitSharePct }}% Share</span>
            </div>
            <h4 class="fw-bold text-dark mb-1">₱{{ number_format($malasakitGlUtilized, 2) }}</h4>
            <div class="fs-xs text-muted d-flex justify-content-between">
              <span>Active GLs: <strong>{{ $malasakitCount }}</strong></span>
              <span>Authorized: <strong>₱{{ number_format($malasakitGlTotal, 2) }}</strong></span>
            </div>
          </div>
        </div>

        <!-- 3. Direct Patient Co-Pay / Out-of-Pocket -->
        <div class="col-md-4">
          <div class="p-3 rounded-3 border bg-light h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="fw-bold text-dark fs-xs text-uppercase d-flex align-items-center gap-1">
                <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #f59e0b;"></span>
                Direct Patient Co-Pay
              </span>
              <span class="badge bg-warning text-dark fs-xs">{{ $directCopaySharePct }}% Share</span>
            </div>
            <h4 class="fw-bold text-dark mb-1">₱{{ number_format($directCopayTotal, 2) }}</h4>
            <div class="fs-xs text-muted d-flex justify-content-between">
              <span>Cashier Receipts: <strong>{{ $directCopayCount }}</strong></span>
              <span>Out-of-Pocket Share</span>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 fs-xs text-muted">
        <div>
          <i class="ph ph-info me-1 text-primary"></i>
          <strong>Universal Healthcare (RA 11223) &amp; Malasakit Centers Act (RA 11463):</strong> Hospital policy actively prioritizes No Balance Billing (NBB) for indigent patients in basic wards.
        </div>
        <a href="{{ route('ar.malasakit.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none fw-semibold">
          Manage Malasakit Guarantee Letters &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- PHASE 4 WIDGET: Departmental Budget Exhaustion & Fiscal Burn-Rate Indicators -->
  <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="ph ph-chart-donut text-primary me-2"></i>
            Departmental Fiscal Budget &amp; Expenditure Burn Rate (FY 2026)
          </h5>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-xs">
            GAA Appropriation Monitor
          </span>
        </div>
        <p class="text-muted fs-xs mb-0 mt-1">
          Real-time expenditure tracking against hospital departmental appropriations to prevent year-end fund exhaustion
        </p>
      </div>

      <div class="text-end">
        <span class="fs-xs text-muted d-block">Overall Fiscal Burn Rate:</span>
        <span class="badge {{ $overallBurnRate >= 80 ? 'bg-danger' : 'bg-primary' }} text-white fs-6 px-2 py-1">
          {{ $overallBurnRate }}% Spent
        </span>
      </div>
    </div>

    <div class="card-body px-4 pt-2 pb-4">
      <!-- Critical Exhaustion Alert Banner (if any department >= 85%) -->
      @if($criticalBudgets->count() > 0)
        <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger rounded-3 p-3 mb-3 d-flex align-items-start gap-3">
          <i class="ph ph-warning-octagon fs-3 flex-shrink-0 mt-1"></i>
          <div class="flex-grow-1">
            <strong class="d-block fs-6 mb-1">
              Fiscal Warning: {{ $criticalBudgets->count() }} Department(s) Approaching Budget Exhaustion (&gt;= 85% Burn Rate)
            </strong>
            <p class="fs-xs mb-2 text-dark">
              The following cost centers have depleted over 85% of their allocated appropriations for FY 2026. CFO review required for budget reallocation or procurement moratorium:
            </p>
            <div class="d-flex flex-wrap gap-2">
              @foreach($criticalBudgets as $crit)
                <span class="badge bg-white text-danger border border-danger-subtle p-2 fs-xs">
                  <strong>{{ $crit->department }}</strong>: 
                  {{ number_format($crit->burn_rate, 1) }}% Spent 
                  (₱{{ number_format((float) $crit->spent_amount, 2) }} / ₱{{ number_format((float) $crit->allocated_amount, 2) }}) &bull; 
                  Remaining: ₱{{ number_format((float) $crit->remaining_balance, 2) }}
                </span>
              @endforeach
            </div>
          </div>
          <a href="{{ url('/budget') }}" class="btn btn-danger btn-sm flex-shrink-0">
            Reallocate Funds
          </a>
        </div>
      @endif

      <!-- Summary KPI Ribbon -->
      <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
          <div class="p-2 border rounded-2 bg-light">
            <span class="fs-xxs text-muted text-uppercase d-block">Total GAA Allocated</span>
            <strong class="text-dark fs-6">₱{{ number_format($totalBudgetAllocated, 2) }}</strong>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-2 border rounded-2 bg-light">
            <span class="fs-xxs text-muted text-uppercase d-block">Total Liquidated / Spent</span>
            <strong class="text-dark fs-6 text-danger">₱{{ number_format($totalBudgetSpent, 2) }}</strong>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-2 border rounded-2 bg-light">
            <span class="fs-xxs text-muted text-uppercase d-block">Remaining Balance</span>
            <strong class="text-dark fs-6 text-success">₱{{ number_format($totalBudgetRemaining, 2) }}</strong>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="p-2 border rounded-2 bg-light">
            <span class="fs-xxs text-muted text-uppercase d-block">Department Cost Centers</span>
            <strong class="text-dark fs-6">{{ $budgetAllocations->count() }} Active Budgets</strong>
          </div>
        </div>
      </div>

      <!-- Department Burn Rate Progress Bars Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr class="fs-xs text-muted text-uppercase">
              <th>Department / Cost Center</th>
              <th>Category</th>
              <th class="text-end">Allocated</th>
              <th class="text-end">Spent Amount</th>
              <th class="text-end">Remaining</th>
              <th style="width: 220px;">Expenditure Burn Rate</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($budgetAllocations as $alloc)
              @php
                $burn = $alloc->burn_rate;
                $barColor = $burn >= 85 ? 'bg-danger' : ($burn >= 65 ? 'bg-warning' : 'bg-success');
              @endphp
              <tr>
                <td>
                  <strong class="d-block text-dark">{{ $alloc->department }}</strong>
                  <span class="fs-xxs text-muted font-monospace">{{ $alloc->department_code ?? 'DEPT' }} &bull; Head: {{ $alloc->department_head ?? 'N/A' }}</span>
                </td>
                <td><span class="badge bg-secondary-subtle text-secondary fs-xxs">{{ $alloc->category ?? 'Clinical' }}</span></td>
                <td class="text-end font-monospace fw-semibold">₱{{ number_format((float) $alloc->allocated_amount, 2) }}</td>
                <td class="text-end font-monospace text-danger">₱{{ number_format((float) $alloc->spent_amount, 2) }}</td>
                <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) $alloc->remaining_balance, 2) }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height: 8px;">
                      <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ min(100, $burn) }}%;"></div>
                    </div>
                    <span class="fs-xs font-monospace fw-bold {{ $burn >= 85 ? 'text-danger' : 'text-dark' }}">{{ number_format($burn, 1) }}%</span>
                  </div>
                </td>
                <td class="text-center">
                  @if($burn >= 85)
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-xxs">CRITICAL</span>
                  @elseif($burn >= 65)
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-xxs">MODERATE</span>
                  @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-xxs">HEALTHY</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-3 text-muted">No departmental budget allocations configured.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Row: COA Circular 2021-014 Daily Intact Deposit & Security Telemetry -->
  <div class="row g-3 mb-4">
    <!-- PHASE 4 WIDGET: Daily Collection & Intact Deposit Reconciliation (COA Circular No. 2021-014) -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
          <div>
            <h5 class="fw-bold mb-0 text-dark">
              <i class="ph ph-bank text-primary me-2"></i>
              Treasury Daily Collection &amp; Deposit
            </h5>
            <span class="fs-xs text-muted">COA Circular No. 2021-014 Compliance Engine</span>
          </div>
          <span class="badge {{ $isCoaIntactCompliant ? 'bg-success' : 'bg-warning text-dark' }} fs-xs">
            {{ $isCoaIntactCompliant ? 'INTACT: COMPLIANT' : 'REMITTANCE DUE' }}
          </span>
        </div>

        <div class="card-body px-4 pt-2 pb-4">
          <p class="fs-xs text-muted mb-3">
            Commission on Audit (COA) Circular 2021-014 mandates that all government hospital cash collections be deposited <strong>intact daily</strong> or on the next banking day with Authorized Government Depository Banks (AGDBs).
          </p>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="p-3 border rounded-2 bg-light">
                <span class="fs-xxs text-muted text-uppercase d-block">Cash Collections Today</span>
                <h4 class="fw-bold text-dark mb-0">₱{{ number_format($totalCashCollections, 2) }}</h4>
                <span class="fs-xxs text-muted">Cashier POS Counters</span>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded-2 bg-light">
                <span class="fs-xxs text-muted text-uppercase d-block">Deposited to Land Bank</span>
                <h4 class="fw-bold text-success mb-0">₱{{ number_format($totalDeposited, 2) }}</h4>
                <span class="fs-xxs text-muted">Validated LBP Deposits</span>
              </div>
            </div>
          </div>

          <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between mb-3">
            <div>
              <span class="fs-xs fw-semibold text-dark d-block">Undeposited Cash in Drawer / Safe</span>
              <span class="fs-xxs text-muted">Account 1011 &bull; Retained overnight or in-transit float</span>
            </div>
            <h5 class="fw-bold text-dark mb-0 font-monospace">₱{{ number_format($undepositedCollections, 2) }}</h5>
          </div>

          <div class="d-flex align-items-center justify-content-between fs-xs text-muted">
            <span>Primary AGDB: <strong>Land Bank of the Philippines (LBP)</strong></span>
            <span class="badge bg-light text-dark border">Depository: LBP-DEPO-1020-01</span>
          </div>
        </div>
      </div>
    </div>

    <!-- PHASE 4 WIDGET: Security & Immutable Audit Trail Telemetry (for CFO & Auditor) -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
          <div>
            <h5 class="fw-bold mb-0 text-dark">
              <i class="ph ph-shield-check text-success me-2"></i>
              Security &amp; Audit Trail Telemetry
            </h5>
            <span class="fs-xs text-muted">Continuous CAS Integrity &amp; Access Oversight</span>
          </div>
          <a href="{{ route('accounting.audit-log') }}" class="btn btn-sm btn-outline-dark fs-xs">
            Open Audit Hub &rarr;
          </a>
        </div>

        <div class="card-body px-4 pt-2 pb-4">
          <!-- Security KPI Mini Cards -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="p-2 border rounded-2 bg-light d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 {{ $failedLoginsToday > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                  <i class="ph ph-shield-warning fs-5"></i>
                </span>
                <div>
                  <span class="fs-xxs text-muted text-uppercase d-block">Failed Logins Today</span>
                  <strong class="fs-6 {{ $failedLoginsToday > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $failedLoginsToday }} {{ $failedLoginsToday === 0 ? '(Clean)' : 'Alerts' }}
                  </strong>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2 border rounded-2 bg-light d-flex align-items-center gap-2">
                <span class="p-2 rounded-2 bg-primary-subtle text-primary">
                  <i class="ph ph-database fs-5"></i>
                </span>
                <div>
                  <span class="fs-xxs text-muted text-uppercase d-block">Mutations Logged</span>
                  <strong class="fs-6 text-dark">{{ $mutationsToday }} Today</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- 5 Latest Activity Logs Snippet -->
          <span class="fs-xs fw-semibold text-muted text-uppercase d-block mb-2">Recent Security &amp; Ledger Events:</span>
          <div class="list-group list-group-flush fs-xs">
            @forelse($recentAuditLogs as $log)
              <div class="list-group-item px-0 py-2 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge {{ $log->event === 'failed_login' ? 'bg-danger' : ($log->event === 'login' ? 'bg-primary' : 'bg-secondary') }} fs-xxs">
                    {{ strtoupper($log->event) }}
                  </span>
                  <div>
                    <strong class="d-block text-dark">{{ $log->user_name ?? 'System' }}</strong>
                    <span class="fs-xxs text-muted">{{ $log->auditable_type ? class_basename($log->auditable_type) : 'Authentication Session' }} &bull; {{ $log->ip_address }}</span>
                  </div>
                </div>
                <span class="text-muted fs-xxs">{{ $log->created_at->diffForHumans() }}</span>
              </div>
            @empty
              <div class="text-center py-3 text-muted fs-xs">No recent audit logs recorded.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent General Ledger Transactions & Double-Entry Invariance -->
  <div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="fw-bold mb-0 text-dark">
          <i class="ph ph-clock-counter-clockwise text-primary me-2"></i>
          Recent Financial Journal Postings &amp; GAAP Invariance
        </h5>
        <span class="fs-xs text-muted">Auditable General Ledger Vouchers (SHA-256 Hash Verified)</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $isBalanced ? 'bg-success' : 'bg-danger' }}">
          {{ $isBalanced ? 'DOUBLE-ENTRY IN BALANCE (Debits == Credits)' : 'DISCREPANCY DETECTED' }}
        </span>
        <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-sm btn-outline-secondary">View All Entries</a>
      </div>
    </div>

    <div class="table-responsive p-3">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="fs-xs text-muted text-uppercase">
            <th>Reference #</th>
            <th>Posting Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Debits / Credits</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentJournals as $je)
            <tr>
              <td><span class="badge bg-light text-dark font-monospace border">{{ $je->reference_number }}</span></td>
              <td>{{ $je->entry_date->format('M d, Y') }}</td>
              <td><span class="text-dark fw-medium">{{ $je->description }}</span></td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $je->type }}</span></td>
              <td>
                <span class="badge {{ $je->status === 'POSTED' ? 'bg-success-subtle text-success' : ($je->status === 'REVERSED' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                  {{ $je->status }}
                </span>
              </td>
              <td class="text-end font-monospace fw-semibold">
                ₱{{ number_format((float) $je->lines->sum('debit'), 2) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No journal transactions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
