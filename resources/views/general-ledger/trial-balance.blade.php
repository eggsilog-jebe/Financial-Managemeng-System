@extends('layouts.app')

@section('title', 'Trial Balance - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'trial-balance')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('gl.journal-entries') }}">General Ledger</a></li>
          <li class="breadcrumb-item active">Trial Balance</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Statement of General Ledger Trial Balance</h1>
      <p class="text-muted fs-xs mb-0">Real-time audit verification hub asserting strict double-entry equality ($\sum \text{Debits} = \sum \text{Credits}$).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['General Ledger']" 
          description="Real-time double-entry trial balance invariance verifier." 
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Statement
      </button>
      <a href="{{ route('gl.trial-balance.export', ['as_of_date' => $asOfDate, 'hide_zero_balances' => $hideZeroBalances ? '1' : '0', 'category' => $selectedCategory]) }}" class="btn btn-primary btn-sm">
        <i class="ph ph-file-arrow-down me-1"></i> Export Trial Balance (CSV)
      </a>
    </div>
  </div>

  <!-- Real-Time Audit Status Banner -->
  <div class="p-3 mb-4 rounded-3 border d-flex align-items-center justify-content-between {{ $isBalanced ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger' }}">
    <div class="d-flex align-items-center gap-3">
      <i class="ph {{ $isBalanced ? 'ph-shield-check' : 'ph-warning-octagon' }} fs-2"></i>
      <div>
        <h5 class="fw-bold mb-0 {{ $isBalanced ? 'text-success' : 'text-danger' }}">
          {{ $isBalanced ? 'TRIAL BALANCE IS BALANCED & AUDIT VERIFIED' : 'OUT OF BALANCE - VARIANCE DETECTED' }}
        </h5>
        <span class="fs-xs">
          {{ $isBalanced ? 'Total debits exactly equal total credits across all active balance sheet and nominal accounts.' : 'A double-entry variance has been detected. Check unposted drafts or unbalanced journal lines.' }}
        </span>
      </div>
    </div>
    <div class="text-end font-monospace">
      <span class="fs-xs text-uppercase d-block">Variance Discrepancy</span>
      <span class="fs-4 fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }}">
        ₱{{ number_format(abs($discrepancy ?? 0), 2) }}
      </span>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Debit Balances</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format($totalDebitBalance ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Balances</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format($totalCreditBalance ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Accounts in Report</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-book-open fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($rows ?? []) }} Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Audit Verification</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $isBalanced ? 'GAAP Compliant' : 'Unbalanced' }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('gl.trial-balance') }}">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <!-- As Of Date -->
          <div class="d-flex align-items-center gap-2">
            <label for="tbDateInput" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-calendar me-1"></i> As-Of Date:</label>
            <input type="date" name="as_of_date" id="tbDateInput" class="form-control form-control-sm bg-light" value="{{ $asOfDate ?? date('Y-m-d') }}" onchange="this.form.submit()">
          </div>

          <!-- Category -->
          <div class="d-flex align-items-center gap-2">
            <label for="tbCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Category:</label>
            <select name="category" id="tbCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 170px;" onchange="this.form.submit()">
              <option value="" {{ empty($selectedCategory) ? 'selected' : '' }}>All Categories</option>
              <option value="ASSET" {{ ($selectedCategory ?? '') === 'ASSET' ? 'selected' : '' }}>Assets (1000s)</option>
              <option value="LIABILITY" {{ ($selectedCategory ?? '') === 'LIABILITY' ? 'selected' : '' }}>Liabilities (2000s)</option>
              <option value="EQUITY" {{ ($selectedCategory ?? '') === 'EQUITY' ? 'selected' : '' }}>Equity (3000s)</option>
              <option value="REVENUE" {{ ($selectedCategory ?? '') === 'REVENUE' ? 'selected' : '' }}>Revenue (4000s)</option>
              <option value="EXPENSE" {{ ($selectedCategory ?? '') === 'EXPENSE' ? 'selected' : '' }}>Expenses (5000s)</option>
            </select>
          </div>

          <!-- Hide Zero-Balance Toggle -->
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="hide_zero_balances" value="1" id="hideZeroToggle" {{ $hideZeroBalances ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label small fw-semibold" for="hideZeroToggle">Hide Zero-Balance Accounts</label>
          </div>

          <!-- Search Bar -->
          <div class="search-box ms-auto" style="width: 260px;">
            <i class="ph ph-magnifying-glass"></i>
            <input type="search" name="q" id="tbSearchInput" class="form-control form-control-sm" placeholder="Search account code, title..." value="{{ $search ?? '' }}">
          </div>
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="trialBalanceTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 120px;">Account Code</th>
              <th>Account Title</th>
              <th>Category</th>
              <th>Normal Balance</th>
              <th class="text-end" style="width: 180px;">Debit Balance (₱)</th>
              <th class="text-end" style="width: 180px;">Credit Balance (₱)</th>
              <th class="text-end" style="width: 100px;">Ledger</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $row)
            @php
              $debitVal = (float) $row['debit'];
              $creditVal = (float) $row['credit'];
              $catUpper = strtoupper((string) $row['category']);
              $badgeClass = match($catUpper) {
                'ASSET'     => 'bg-success-subtle text-success',
                'LIABILITY' => 'bg-danger-subtle text-danger',
                'EQUITY'    => 'bg-primary-subtle text-primary',
                'REVENUE'   => 'bg-info-subtle text-info',
                'EXPENSE'   => 'bg-warning-subtle text-warning',
                default     => 'bg-secondary-subtle text-secondary',
              };
            @endphp
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">{{ $row['code'] }}</span></td>
              <td><div class="fw-semibold text-dark">{{ $row['name'] }}</div></td>
              <td><span class="badge {{ $badgeClass }} fs-xs">{{ $catUpper }}</span></td>
              <td><span class="badge bg-light text-dark border font-monospace fs-xs">{{ $row['normal_balance'] }}</span></td>
              <td class="text-end font-monospace {{ $debitVal > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                {{ $debitVal > 0 ? '₱' . number_format($debitVal, 2) : '-' }}
              </td>
              <td class="text-end font-monospace {{ $creditVal > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                {{ $creditVal > 0 ? '₱' . number_format($creditVal, 2) : '-' }}
              </td>
              <td class="text-end">
                <a href="{{ route('gl.ledger-books', ['account_id' => $row['id'], 'end_date' => $asOfDate]) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Ledger Book">
                  <i class="ph ph-book-open"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="ph ph-scales fs-2 d-block mb-2 text-secondary"></i>
                No account balances found matching criteria.
              </td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="4" class="text-end">TRIAL BALANCE TOTALS:</td>
              <td class="text-end font-monospace text-success fs-5">₱{{ number_format($totalDebitBalance ?? 0, 2) }}</td>
              <td class="text-end font-monospace text-primary fs-5">₱{{ number_format($totalCreditBalance ?? 0, 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Report As of: {{ $asOfDate }} | BIR CAS Compliant</span>
      <span class="fw-bold fs-xs {{ $isBalanced ? 'text-success' : 'text-danger' }}">
        <i class="ph {{ $isBalanced ? 'ph-check-circle' : 'ph-x-circle' }} me-1"></i>
        {{ $isBalanced ? 'Double-Entry Invariance Satisfied (0.00 Variance)' : 'Double-Entry Invariance Broken' }}
      </span>
    </div>
  </div>
</div>
@endsection
