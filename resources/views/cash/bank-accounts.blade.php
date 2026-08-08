@extends('layouts.app')

@section('title', 'Bank Accounts - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-accounts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Bank Accounts Master Register</h1>
      <p class="text-muted small mb-0">Master register of active commercial bank accounts, ledger balances, and branch details.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrows-counter-clockwise me-1"></i> Refresh Balances</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="ph ph-plus-circle me-1"></i> Add Bank Account</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Hospital Bank Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Accounts</h4>
        <span class="fs-xs text-muted">Metrobank, BDO, BPI &amp; Landbank</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Ledger Cash Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱7,840,000.00</h4>
        <span class="fs-xs text-muted">Total liquid cash across accounts</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Main Operating Account</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
        <span class="fs-xs text-muted">Metrobank Main Branch #1020</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Collections Account</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱2,140,000.00</h4>
        <span class="fs-xs text-muted">BDO Collections Branch #2384</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Bank Name, Account No, or Purpose...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Account Purposes</option>
            <option value="operating">Primary Operations &amp; Payroll</option>
            <option value="collections">Collections &amp; HMO Deposits</option>
            <option value="reserve">Emergency Capital Reserve</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Currencies</option>
            <option value="php">PHP (₱)</option>
            <option value="usd">USD ($)</option>
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
              <th>Bank Name &amp; Branch</th>
              <th>Account Number</th>
              <th>Account Purpose</th>
              <th>Currency</th>
              <th class="text-end">Ledger Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-bold text-dark">Metrobank - Main Branch</td>
              <td><span class="font-monospace text-primary fw-bold">1020-8841-99</span></td>
              <td>Primary Operations &amp; Payroll Payouts</td>
              <td><span class="badge bg-light text-dark border">PHP (₱)</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱4,850,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Account Transactions"><i class="ph ph-list"></i></button>
              </td>
            </tr>
            <tr>
              <td class="fw-bold text-dark">BDO Unibank - Medical City Branch</td>
              <td><span class="font-monospace text-primary fw-bold">0091-2384-12</span></td>
              <td>Collections &amp; HMO Deposits</td>
              <td><span class="badge bg-light text-dark border">PHP (₱)</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱2,140,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Account Transactions"><i class="ph ph-list"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Bank Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add Hospital Bank Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Bank Account added!'); bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank &amp; Branch Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" placeholder="e.g. BPI - Healthcare Center Branch" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm font-monospace" placeholder="0000-0000-00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Purpose <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" placeholder="e.g. Emergency Equipment Reserve" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Opening Balance (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
