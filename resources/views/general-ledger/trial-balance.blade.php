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
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Trial Balance</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Trial Balance</h1>
      <p class="text-muted small mb-0">Verification audit of equal debit and credit balances across all active general ledger accounts.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Trial Balance PDF Statement exported!');"><i class="ph ph-file-pdf me-1"></i> Export Trial Balance PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Debit Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱54,110,200.00</h4>
        <span class="fs-xs text-muted">Sum of all active Debit balances</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Balance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱54,110,200.00</h4>
        <span class="fs-xs text-muted">Sum of all active Credit balances</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Balance Discrepancy</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">100% Perfectly Balanced Trial</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Audit Verification</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Passed</h4>
        <span class="fs-xs text-muted">GAAP Compliant Accounting</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Trial Balance Date</label>
          <input type="date" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Display Option</label>
          <select class="form-select form-select-sm bg-light">
            <option value="non_zero">Hide Zero Balance Accounts</option>
            <option value="all">Show All Accounts</option>
          </select>
        </div>
        <div class="col-md-4 text-end pt-3">
          <button class="btn btn-sm btn-primary"><i class="ph ph-arrow-clockwise me-1"></i> Update Trial Balance</button>
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
              <th>Account Title</th>
              <th>Category</th>
              <th class="text-end">Debit Balance (₱)</th>
              <th class="text-end">Credit Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">1010</span></td>
              <td><div class="fw-bold text-dark">Metrobank Operating Cash</div></td>
              <td><span class="badge bg-success-subtle text-success">Asset</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱4,850,000.00</td>
              <td class="text-end font-monospace text-muted">-</td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">1200</span></td>
              <td><div class="fw-bold text-dark">Accounts Receivable (AR)</div></td>
              <td><span class="badge bg-success-subtle text-success">Asset</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱3,070,200.00</td>
              <td class="text-end font-monospace text-muted">-</td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">2100</span></td>
              <td><div class="fw-bold text-dark">Accounts Payable (AP)</div></td>
              <td><span class="badge bg-danger-subtle text-danger">Liability</span></td>
              <td class="text-end font-monospace text-muted">-</td>
              <td class="text-end text-danger fw-bold font-monospace">₱910,500.00</td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">3000</span></td>
              <td><div class="fw-bold text-dark">Hospital Retained Earnings</div></td>
              <td><span class="badge bg-primary-subtle text-primary">Equity</span></td>
              <td class="text-end font-monospace text-muted">-</td>
              <td class="text-end text-primary fw-bold font-monospace">₱53,199,700.00</td>
            </tr>
          </tbody>
          <tfoot class="table-dark font-monospace fw-bold">
            <tr>
              <td colspan="3" class="text-end fs-6">TOTAL TRIAL BALANCE:</td>
              <td class="text-end text-success fs-6">₱54,110,200.00</td>
              <td class="text-end text-info fs-6">₱54,110,200.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
