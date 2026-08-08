@extends('layouts.app')

@section('title', 'Invoicing & Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'billing')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; Corporate Billing Invoices</h1>
      <p class="text-muted small mb-0">Generate, audit, and track medical billing statements for admitted inpatients, HMO guarantors, and PhilHealth claims.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Export Billing Log</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createInvoiceModal"><i class="ph ph-plus-circle me-1"></i> Create Patient Invoice</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Invoices Issued Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">28 Invoices</h4>
        <span class="fs-xs text-muted">Total Billing Value: <strong class="text-primary">₱342,000.00</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending HMO Claims</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,220,000.00</h4>
        <span class="fs-xs text-muted">Filed with Maxicare &amp; Intellicare</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">PhilHealth Coverage Claims</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-first-aid fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱650,500.00</h4>
        <span class="fs-xs text-muted">Statutory Benefit Deductions</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Paid &amp; Settled Invoices</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">91.4%</h4>
        <span class="fs-xs text-muted">Prompt Payment Settlement</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Invoice No, Patient Name, or HMO...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Payor Classes</option>
            <option value="hmo">HMO Guarantor</option>
            <option value="patient">Direct Patient Cash</option>
            <option value="philhealth">PhilHealth Statutory</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Invoice Statuses</option>
            <option value="pending">Pending Payment</option>
            <option value="paid">Paid &amp; Cleared</option>
            <option value="partially_paid">Partially Paid</option>
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
              <th>Invoice Ref</th>
              <th>Date</th>
              <th>Patient / Payor Name</th>
              <th>Payor Type</th>
              <th class="text-end">Total Billed (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">INV-2026-0881</span></td>
              <td>2026-08-08</td>
              <td>
                <div class="fw-semibold text-dark">Juan De La Cruz</div>
                <span class="fs-xs text-muted">Patient ID: PAT-88412 (Inpatient Surgery)</span>
              </td>
              <td><span class="badge bg-info-subtle text-info">Maxicare HMO</span></td>
              <td class="text-end font-monospace text-success fw-bold">₱45,800.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Claim</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print Statement"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Breakdown"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Patient Invoice -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createInvoiceModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Patient Billing Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Patient Billing Invoice created!'); bootstrap.Modal.getInstance(document.getElementById('createInvoiceModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Maria Clara Santos" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient ID / Room No. <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. PAT-99201 (Room 304)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payor Classification <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="hmo">HMO Corporate Guarantor</option>
                <option value="cash">Self-Pay Direct Cash</option>
                <option value="philhealth">PhilHealth Coverage</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Gross Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Itemized Medical Particulars</label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="Room charges, ICU monitoring, lab tests, pharmacy dispensing..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Billing Invoice</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
