@extends('layouts.app')

@section('title', 'Tax Returns & Filing - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-returns')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Returns &amp; Filing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Returns &amp; Statutory Filings</h1>
      <p class="text-muted small mb-0">Monthly and quarterly statutory returns (BIR Form 2550M/Q, Form 1601EQ, Corporate Income Tax Form 1702).</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-calendar-check me-1"></i> BIR Tax Calendar</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#fileReturnModal"><i class="ph ph-file-arrow-up me-1"></i> File Statutory Return</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Filed Returns (This Year)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">8 Returns</h4>
        <span class="fs-xs text-muted">100% On-Time Filing Record</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Paid (YTD)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱1,425,000.00</h4>
        <span class="fs-xs text-muted">Remitted via eFPS Payment</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Tax Payable</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱215,000.00</h4>
        <span class="fs-xs text-muted">Form 2550Q Due Aug 25</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">eFPS Portal Connection</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-globe fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Connected</h4>
        <span class="fs-xs text-muted">BIR eFPS System Synced</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Form Code, Return Title, or Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All BIR Form Types</option>
            <option value="2550">BIR Form 2550Q (Quarterly VAT)</option>
            <option value="1601">BIR Form 1601EQ (Withholding Tax)</option>
            <option value="1702">BIR Form 1702 (Corporate Tax)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Filing Statuses</option>
            <option value="filed">Filed &amp; Paid</option>
            <option value="pending">Pending Payment</option>
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
              <th>Form Code</th>
              <th>Description / Return Name</th>
              <th>Tax Period</th>
              <th>Due Date</th>
              <th class="text-end">Tax Payable (₱)</th>
              <th>Filing Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">BIR FORM 2550Q</span></td>
              <td>
                <div class="fw-semibold text-dark">Quarterly Value Added Tax Return</div>
                <span class="fs-xs text-muted">eFPS Confirmation Ref: 9940129</span>
              </td>
              <td>Q2 2026</td>
              <td>2026-08-25</td>
              <td class="text-end text-danger fw-bold font-monospace">₱215,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Payment</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-success py-1 px-2 fs-xs me-1"><i class="ph ph-bank me-1"></i> Pay via eFPS</button>
                <button class="btn btn-sm btn-light border p-1" title="View Form PDF"><i class="ph ph-file-pdf"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">BIR FORM 1601EQ</span></td>
              <td>
                <div class="fw-semibold text-dark">Quarterly Remittance of Creditable Income Taxes (EWT)</div>
                <span class="fs-xs text-muted">eFPS Payment Confirmation Ref: 881024</span>
              </td>
              <td>Q1 2026</td>
              <td>2026-04-30</td>
              <td class="text-end font-monospace text-muted">₱340,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Filed &amp; Remitted</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View eFPS Receipt"><i class="ph ph-receipt"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Form PDF"><i class="ph ph-file-pdf"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: File Statutory Tax Return -->
<div class="modal fade" id="fileReturnModal" tabindex="-1" aria-labelledby="fileReturnModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="fileReturnModalLabel"><i class="ph ph-file-arrow-up me-2 text-primary"></i>Record Statutory Tax Return Filing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Statutory Return filed successfully!'); bootstrap.Modal.getInstance(document.getElementById('fileReturnModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR Form Code <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="2550Q">BIR Form 2550Q (Quarterly VAT Return)</option>
                <option value="1601EQ">BIR Form 1601EQ (Quarterly EWT Return)</option>
                <option value="1702">BIR Form 1702-EX (Corporate Income Tax)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Period Covered <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Q2 2026 (Apr - Jun)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Gross Taxable Base (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Net Tax Payable (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace text-danger fw-bold" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR eFPS Payment Reference No.</label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. eFPS-PAY-991204">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Upload BIR Filing Confirmation (PDF / Image)</label>
              <input type="file" class="form-control form-control-sm">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Statutory Return</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
