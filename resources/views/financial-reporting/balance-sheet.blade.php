@extends('layouts.app')

@section('title', 'Balance Sheet - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'balance-sheet')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Balance Sheet</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Financial Position (Balance Sheet)</h1>
      <p class="text-muted small mb-0">Audited summary of total hospital assets, current &amp; long-term liabilities, and retained net equity.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Balance Sheet Audit PDF generated!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Assets</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱54,110,200.00</h4>
        <span class="fs-xs text-muted">Current &amp; Non-Current Assets</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱12,910,500.00</h4>
        <span class="fs-xs text-muted">AP &amp; Equipment Loans</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Hospital Net Equity</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱41,199,700.00</h4>
        <span class="fs-xs text-muted">Capital &amp; Retained Earnings</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">9.78x</h4>
        <span class="fs-xs text-muted">Strong Liquidity Solvency</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">As-Of Date Reporting</label>
          <input type="date" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Comparison Period</label>
          <select class="form-select form-select-sm bg-light">
            <option value="prior_year">Prior Fiscal Year (FY 2025)</option>
            <option value="prior_quarter">Prior Quarter (Q1 2026)</option>
            <option value="none">No Comparison</option>
          </select>
        </div>
        <div class="col-md-4 text-end pt-3">
          <button class="btn btn-sm btn-primary"><i class="ph ph-arrow-clockwise me-1"></i> Update Balance Sheet</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Dual Column Balance Sheet Layout -->
  <div class="row g-4">
    <!-- Assets Column -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-success"><i class="ph ph-vault me-2"></i>ASSETS</h5>
          <span class="fs-xs text-muted font-monospace">As of {{ date('M d, Y') }}</span>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light fs-xs text-uppercase">
              <tr><th>Asset Account Category</th><th class="text-end">Amount (₱)</th></tr>
            </thead>
            <tbody>
              <tr class="table-light fw-bold"><td colspan="2">1. Current Assets</td></tr>
              <tr><td class="ps-4">Cash &amp; Bank Equivalents</td><td class="text-end font-monospace">₱4,850,000.00</td></tr>
              <tr><td class="ps-4">Accounts Receivable (AR - Patients &amp; HMOs)</td><td class="text-end font-monospace">₱3,070,200.00</td></tr>
              <tr><td class="ps-4">Pharmacy &amp; Medical Supplies Inventory</td><td class="text-end font-monospace">₱990,000.00</td></tr>
              <tr class="fw-semibold"><td>Total Current Assets</td><td class="text-end text-success font-monospace">₱8,910,200.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">2. Non-Current Assets</td></tr>
              <tr><td class="ps-4">Medical Equipment &amp; MRI Scanners</td><td class="text-end font-monospace">₱28,500,000.00</td></tr>
              <tr><td class="ps-4">Hospital Building &amp; Infrastructure</td><td class="text-end font-monospace">₱18,200,000.00</td></tr>
              <tr><td class="ps-4 text-muted">Less: Accumulated Depreciation</td><td class="text-end font-monospace text-danger">-₱1,500,000.00</td></tr>
              <tr class="fw-semibold"><td>Total Non-Current Assets</td><td class="text-end text-success font-monospace">₱45,200,000.00</td></tr>
            </tbody>
            <tfoot class="table-success fw-bold">
              <tr>
                <td class="fs-6">TOTAL ASSETS</td>
                <td class="text-end text-success fs-6 font-monospace">₱54,110,200.00</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Liabilities & Equity Column -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-primary"><i class="ph ph-scales me-2"></i>LIABILITIES &amp; EQUITY</h5>
          <span class="fs-xs text-muted font-monospace">As of {{ date('M d, Y') }}</span>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light fs-xs text-uppercase">
              <tr><th>Liabilities &amp; Equity Category</th><th class="text-end">Amount (₱)</th></tr>
            </thead>
            <tbody>
              <tr class="table-light fw-bold"><td colspan="2">1. Current Liabilities</td></tr>
              <tr><td class="ps-4">Accounts Payable (AP Vendor Bills)</td><td class="text-end font-monospace">₱910,500.00</td></tr>
              <tr><td class="ps-4">Accrued Nurse &amp; Staff Payroll</td><td class="text-end font-monospace">₱0.00</td></tr>
              <tr class="fw-semibold"><td>Total Current Liabilities</td><td class="text-end text-danger font-monospace">₱910,500.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">2. Long-Term Liabilities</td></tr>
              <tr><td class="ps-4">Medical Equipment Term Loans</td><td class="text-end font-monospace">₱12,000,000.00</td></tr>
              <tr class="fw-semibold"><td>Total Liabilities</td><td class="text-end text-danger font-monospace">₱12,910,500.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">3. Hospital Net Equity</td></tr>
              <tr><td class="ps-4">Founding Capital Reserve</td><td class="text-end font-monospace">₱25,000,000.00</td></tr>
              <tr><td class="ps-4">Retained Earnings (Accumulated Net Profits)</td><td class="text-end font-monospace">₱16,199,700.00</td></tr>
              <tr class="fw-semibold"><td>Total Net Equity</td><td class="text-end text-primary font-monospace">₱41,199,700.00</td></tr>
            </tbody>
            <tfoot class="table-primary fw-bold">
              <tr>
                <td class="fs-6">TOTAL LIABILITIES &amp; EQUITY</td>
                <td class="text-end text-primary fs-6 font-monospace">₱54,110,200.00</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
