@extends('layouts.app')

@section('title', 'Withholding Tax - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'withholding-tax')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Withholding Tax</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Withholding Tax Certificates (BIR Form 2307 / 2306)</h1>
      <p class="text-muted small mb-0">Creditable withholding tax certificates generated for consultant physicians, medical suppliers, and contractors.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Export BIR E-Submission</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueCertModal"><i class="ph ph-plus-circle me-1"></i> Issue 2307 Certificate</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Form 2307 Issued (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-file-text fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">42 Certificates</h4>
        <span class="fs-xs text-muted">Consultant Doctors &amp; Suppliers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Withheld (EWT)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-scissors fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱384,500.00</h4>
        <span class="fs-xs text-muted">Ready for BIR 1601EQ Remittance</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Gross Income Base</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱3,845,000.00</h4>
        <span class="fs-xs text-muted">Professional &amp; Supplier Income Base</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Remittance Status</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Due Aug 10</h4>
        <span class="fs-xs text-muted">Monthly EWT 1601EQ Filing Deadline</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Cert No, Payee Name, or TIN Number...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Form Types</option>
            <option value="2307">Form 2307 (Creditable Withholding)</option>
            <option value="2306">Form 2306 (Final Withholding VAT)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Payee Types</option>
            <option value="doctor">Medical Consultants &amp; Physicians</option>
            <option value="supplier">Pharmaceutical &amp; Equipment Suppliers</option>
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
              <th>Cert Number</th>
              <th>Payee / Doctor Name</th>
              <th>TIN Number</th>
              <th>ATC Code</th>
              <th class="text-end">Gross Income (₱)</th>
              <th class="text-end">Tax Withheld (₱)</th>
              <th>Form Type</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">C2307-2026-881</span></td>
              <td>
                <div class="fw-semibold text-dark">Dr. Roberto Gomez</div>
                <span class="fs-xs text-muted">Visiting Cardiology Consultant</span>
              </td>
              <td><span class="font-monospace text-muted">102-391-441-000</span></td>
              <td><span class="badge bg-light text-dark border font-monospace">WI010 (10%)</span></td>
              <td class="text-end font-monospace fw-semibold">₱120,000.00</td>
              <td class="text-end text-danger fw-bold font-monospace">₱12,000.00</td>
              <td><span class="badge bg-primary-subtle text-primary">BIR Form 2307</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print Official 2307 PDF"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Email Cert to Doctor"><i class="ph ph-envelope"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">C2307-2026-880</span></td>
              <td>
                <div class="fw-semibold text-dark">Metro Pharma Distributors Corp</div>
                <span class="fs-xs text-muted">Medical Consumables Supplier</span>
              </td>
              <td><span class="font-monospace text-muted">008-992-101-000</span></td>
              <td><span class="badge bg-light text-dark border font-monospace">WC158 (1%)</span></td>
              <td class="text-end font-monospace fw-semibold">₱450,000.00</td>
              <td class="text-end text-danger fw-bold font-monospace">₱4,500.00</td>
              <td><span class="badge bg-primary-subtle text-primary">BIR Form 2307</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print Official 2307 PDF"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Email Cert to Supplier"><i class="ph ph-envelope"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Form 2307 Certificate -->
<div class="modal fade" id="issueCertModal" tabindex="-1" aria-labelledby="issueCertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueCertModalLabel"><i class="ph ph-file-text me-2 text-primary"></i>Issue BIR Form 2307 Withholding Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('BIR Form 2307 Certificate generated!'); bootstrap.Modal.getInstance(document.getElementById('issueCertModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee Name (Doctor / Supplier) <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Dr. Alejandro Santos" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Taxpayer Identification Number (TIN) <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="000-000-000-000" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Alphanumeric Tax Code (ATC) <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="WI010">WI010 - Professional Fees (10%)</option>
                <option value="WI011">WI011 - Professional Fees (15%)</option>
                <option value="WC158">WC158 - Purchase of Medical Goods (1%)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Gross Income Payment (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Calculated Tax Withheld (₱)</label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace bg-light text-danger fw-bold" placeholder="0.00" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Quarter / Period</label>
              <select class="form-select form-select-sm">
                <option value="q3">Q3 2026 (Jul - Sep)</option>
                <option value="q2">Q2 2026 (Apr - Jun)</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-printer me-1"></i> Generate &amp; Sign 2307 PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
