@extends('layouts.app')

@section('title', 'Fund Transfers - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'fund-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Fund Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Inter-Account Bank Fund Transfers</h1>
      <p class="text-muted small mb-0">Internal transfers between hospital commercial bank accounts, payroll funding, and treasury reserve reallocations.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Transfer Log PDF</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newTransferModal"><i class="ph ph-arrows-left-right me-1"></i> New Fund Transfer</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Inter-Account Transfers (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrows-left-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">8 Transfers</h4>
        <span class="fs-xs text-muted">Internal Bank Liquidity Transfers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Transfer Volume</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱3,450,000.00</h4>
        <span class="fs-xs text-muted">100% Balanced Internal Liquidity</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Approvals</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Pending</h4>
        <span class="fs-xs text-muted">Payroll Account Funding: ₱850K</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Bank Wire Fees</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱200.00</h4>
        <span class="fs-xs text-muted">InstaPay / PESONet Fee Expenses</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Transfer Ref, Source, or Target Bank...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Source Banks</option>
            <option value="bdo">BDO Collections #2384</option>
            <option value="metrobank">Metrobank Operating #1020</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Statuses</option>
            <option value="completed">Completed &amp; Posted</option>
            <option value="pending">Pending Treasury Approval</option>
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
              <th>Transfer Ref</th>
              <th>Source Account (From)</th>
              <th>Destination Account (To)</th>
              <th class="text-end">Transfer Amount (₱)</th>
              <th>Transfer Date</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">TRF-2026-088</span></td>
              <td>
                <div class="fw-semibold text-dark">BDO Collections</div>
                <span class="fs-xs font-monospace text-muted">#0091-2384-12</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">Metrobank Operating</div>
                <span class="fs-xs font-monospace text-muted">#1020-8841-99</span>
              </td>
              <td class="text-end text-success fw-bold font-monospace">₱500,000.00</td>
              <td>2026-08-07</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Completed</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Transfer Audit"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Bank Fund Transfer -->
<div class="modal fade" id="newTransferModal" tabindex="-1" aria-labelledby="newTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newTransferModalLabel"><i class="ph ph-arrows-left-right me-2 text-primary"></i>Execute Inter-Account Fund Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Fund Transfer executed!'); bootstrap.Modal.getInstance(document.getElementById('newTransferModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Bank Account (From) <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="bdo">BDO Collections (#0091-2384-12)</option>
                <option value="metrobank">Metrobank Operating (#1020-8841-99)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Destination Bank Account (To) <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="metrobank">Metrobank Operating (#1020-8841-99)</option>
                <option value="bpi">BPI Payroll Account (#0012-4412-00)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Method</label>
              <select class="form-select form-select-sm">
                <option value="internal">Internal Bank Clearing (Same Day)</option>
                <option value="pesonet">PESONet Electronic Transfer</option>
                <option value="instapay">InstaPay Real-Time</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Transfer Purpose / Treasury Notes <span class="text-danger">*</span></label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="e.g. Weekly payroll account replenishment..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-paper-plane-tilt me-1"></i> Execute Transfer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
