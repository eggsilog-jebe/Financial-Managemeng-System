@extends('layouts.app')

@section('title', 'Ledger Books - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'ledger-books')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">General Ledger Books</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">General Ledger Account Books</h1>
      <p class="text-muted small mb-0">Master general ledger books tracking debit/credit movement across Assets, Liabilities, Equity, Revenue, and Expenses.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print GL Books</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('GL Books exported!');"><i class="ph ph-file-arrow-down me-1"></i> Export Master GL</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active GL Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-book-open fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">48 Accounts</h4>
        <span class="fs-xs text-muted">Chart of Accounts Master Index</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Debit Movement</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱54,110,200.00</h4>
        <span class="fs-xs text-muted">Accumulated Debit Postings</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Credit Movement</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱54,110,200.00</h4>
        <span class="fs-xs text-muted">Accumulated Credit Postings</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Ledger Book Solvency</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Balanced</h4>
        <span class="fs-xs text-muted">Zero Accounting Discrepancy</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Account Code or Account Name...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Account Types</option>
            <option value="asset">1000 - Assets</option>
            <option value="liability">2000 - Liabilities</option>
            <option value="equity">3000 - Equity</option>
            <option value="revenue">4000 - Revenue</option>
            <option value="expense">5000 - Expenses</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Fiscal Periods</option>
            <option value="ytd">FY 2026 Year-To-Date</option>
            <option value="q2">Q2 2026</option>
          </select>
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-sm btn-light border w-100"><i class="ph ph-funnel me-1"></i> Filter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Account Code</th>
              <th>Account Name</th>
              <th>Account Type</th>
              <th class="text-end">Opening (₱)</th>
              <th class="text-end">Debit Total (₱)</th>
              <th class="text-end">Credit Total (₱)</th>
              <th class="text-end">Ending Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">1010</span></td>
              <td><div class="fw-bold text-dark">Metrobank Operating Account</div></td>
              <td><span class="badge bg-success-subtle text-success">Asset</span></td>
              <td class="text-end font-monospace">₱2,500,000.00</td>
              <td class="text-end text-success font-monospace">+₱8,450,000.00</td>
              <td class="text-end text-danger font-monospace">-₱6,100,000.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱4,850,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Account Movement Ledger"><i class="ph ph-list-numbers"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">1200</span></td>
              <td><div class="fw-bold text-dark">Accounts Receivable (AR - Patients &amp; HMOs)</div></td>
              <td><span class="badge bg-success-subtle text-success">Asset</span></td>
              <td class="text-end font-monospace">₱1,850,000.00</td>
              <td class="text-end text-success font-monospace">+₱7,620,000.00</td>
              <td class="text-end text-danger font-monospace">-₱6,399,800.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱3,070,200.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Account Movement Ledger"><i class="ph ph-list-numbers"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
