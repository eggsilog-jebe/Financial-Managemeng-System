@extends('layouts.app')

@section('title', 'Bank Deposits - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'bank-deposits')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Bank Deposits</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Deposits Log &amp; Verification</h1>
      <p class="text-muted small mb-0">Reconciliation records matching cashier drawer collections with confirmed bank deposits.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Export Deposit Audit</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#verifyDepositModal"><i class="ph ph-check-circle me-1"></i> Record Bank Validation</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Confirmed Deposits (Month)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,842,500.00</h4>
        <span class="fs-xs text-muted">Cleared by commercial banks</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Verification</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Deposit</h4>
        <span class="fs-xs text-muted">Awaiting teller receipt validation: ₱45,200</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Deposit Discrepancies</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">100% teller stamp alignment</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">GL Reconciliation Match</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100.0%</h4>
        <span class="fs-xs text-muted">Matched with General Ledger Cash Book</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Deposit Ref, Machine Stamp, or Bank...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Hospital Accounts</option>
            <option value="metrobank">Metrobank #1020 (Operating)</option>
            <option value="bdo">BDO Unibank #2384 (Collections)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Verification Statuses</option>
            <option value="verified">Verified by Bank</option>
            <option value="pending">Pending Teller Verification</option>
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
              <th>Deposit Ref</th>
              <th>Linked Batch Slip</th>
              <th>Bank Account</th>
              <th>Deposit Date</th>
              <th class="text-end">Amount Deposited (₱)</th>
              <th>Teller Stamp / Machine Ref</th>
              <th>Verification</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">DEP-2026-302</span></td>
              <td><span class="font-monospace text-muted">SLIP-2026-080</span></td>
              <td>
                <div class="fw-semibold text-dark">Metrobank Operating</div>
                <span class="fs-xs font-monospace text-muted">#1020-8841-99</span>
              </td>
              <td>2026-08-07 15:30</td>
              <td class="text-end text-success fw-bold font-monospace">₱125,400.00</td>
              <td><span class="font-monospace text-dark">MB-STAMP-99210</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Verified by Bank</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Machine Slip Image"><i class="ph ph-file-image"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="GL Audit Entry"><i class="ph ph-arrows-left-right"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">DEP-2026-301</span></td>
              <td><span class="font-monospace text-muted">SLIP-2026-079</span></td>
              <td>
                <div class="fw-semibold text-dark">BDO Collections</div>
                <span class="fs-xs font-monospace text-muted">#0091-2384-12</span>
              </td>
              <td>2026-08-06 16:15</td>
              <td class="text-end text-success fw-bold font-monospace">₱98,000.00</td>
              <td><span class="font-monospace text-dark">BDO-TRX-10294</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Verified by Bank</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Machine Slip Image"><i class="ph ph-file-image"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="GL Audit Entry"><i class="ph ph-arrows-left-right"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Record / Verify Bank Deposit -->
<div class="modal fade" id="verifyDepositModal" tabindex="-1" aria-labelledby="verifyDepositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="verifyDepositModalLabel"><i class="ph ph-check-circle me-2 text-primary"></i>Record &amp; Verify Bank Deposit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Bank deposit verified and posted!'); bootstrap.Modal.getInstance(document.getElementById('verifyDepositModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Select Batch Deposit Slip <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="SLIP-2026-081">SLIP-2026-081 (₱45,200.00 - Metrobank)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Deposit Date &amp; Time Stamp <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Bank Machine Teller Stamp Ref <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. MB-STAMP-99211" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Confirmed Deposited Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" value="45200.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Upload Bank Machine Deposit Slip Photo / PDF</label>
              <input type="file" class="form-control form-control-sm">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Verify &amp; Match Cash Book</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
