@extends('layouts.app')

@section('title', 'Bank Reconciliation - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-reconciliation')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Reconciliation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Statement Reconciliation</h1>
      <p class="text-muted small mb-0">Automated and manual reconciliation matching hospital bank statements against cash ledger transactions.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-magic-wand me-1"></i> Auto-Match Transactions</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#uploadStatementModal"><i class="ph ph-file-arrow-up me-1"></i> Upload Bank Statement</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Bank Statement Ending Balance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
        <span class="fs-xs text-muted">Metrobank Main Branch #1020</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Hospital Cash Book Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-book fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
        <span class="fs-xs text-muted">GL Account 1010 Balance</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Reconciliation Variance</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">Zero Unmatched Discrepancy</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Statement Match Rate</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100.0%</h4>
        <span class="fs-xs text-muted">All July Transactions Cleared</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Select Bank Account</label>
          <select class="form-select form-select-sm bg-light">
            <option value="metrobank">Metrobank Operating #1020</option>
            <option value="bdo">BDO Collections #2384</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Statement Period</label>
          <select class="form-select form-select-sm bg-light">
            <option value="jul_2026">July 2026 Statement</option>
            <option value="jun_2026">June 2026 Statement</option>
          </select>
        </div>
        <div class="col-md-4 text-end pt-3">
          <button class="btn btn-sm btn-primary"><i class="ph ph-funnel me-1"></i> Load Matching Table</button>
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
              <th>Date</th>
              <th>Bank Statement Line Item</th>
              <th>System Voucher Reference</th>
              <th class="text-end">Bank Amount (₱)</th>
              <th class="text-end">Cash Book Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>2026-08-07</td>
              <td>
                <div class="fw-semibold text-dark">DEP-2026-302 Armored Deposit</div>
                <span class="fs-xs text-muted">Machine Teller Stamp #MB-STAMP-99210</span>
              </td>
              <td><span class="font-monospace text-primary">DEP-2026-302</span></td>
              <td class="text-end text-success font-monospace">+₱125,400.00</td>
              <td class="text-end text-success font-monospace">+₱125,400.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Matched</span></td>
              <td class="text-end"><button class="btn btn-sm btn-light border p-1" title="View Match Audit"><i class="ph ph-eye"></i></button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Upload Bank Statement -->
<div class="modal fade" id="uploadStatementModal" tabindex="-1" aria-labelledby="uploadStatementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="uploadStatementModalLabel"><i class="ph ph-file-arrow-up me-2 text-primary"></i>Upload Electronic Bank Statement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Bank statement uploaded!'); bootstrap.Modal.getInstance(document.getElementById('uploadStatementModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Hospital Bank Account <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="1">Metrobank Main Branch (#1020-8841-99)</option>
              <option value="2">BDO Unibank Collections (#0091-2384-12)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Statement Ending Balance (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Upload OFX / CSV / PDF Statement File <span class="text-danger">*</span></label>
            <input type="file" class="form-control form-control-sm" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Upload &amp; Run Auto-Match</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
