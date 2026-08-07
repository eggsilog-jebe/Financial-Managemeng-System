@extends('layouts.app')

@section('title', 'Patient & Customer Accounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'customers')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Patient Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Payor Accounts</h1>
      <p class="text-muted small mb-0">Master billing accounts for admitted inpatients, outpatient clients, commercial HMO guarantors, and PhilHealth coverage.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export Master List</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Create Billing Account</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Active Admitted Inpatients</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-user-list fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">142 Patients</h4>
        <span class="fs-xs text-muted">Ward &amp; ICU Admission</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Patient Receivables</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,850,500.00</h4>
        <span class="fs-xs text-muted">Pending Patient Cash Settlement</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Active HMO Claims Pool</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,220,000.00</h4>
        <span class="fs-xs text-muted">Maxicare, Intellicare &amp; Medicard</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">PhilHealth Claim Receivables</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱820,000.00</h4>
        <span class="fs-xs text-muted">National Government Scheme</span>
      </div>
    </div>
  </div>

  <!-- Accounts Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <ul class="nav nav-pills flex-grow-1">
          <li class="nav-item"><button class="nav-link active btn-sm py-1 px-3 me-1 fw-semibold">All Payor Accounts (142)</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Admitted Inpatients</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Commercial HMOs</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">PhilHealth Scheme</button></li>
        </ul>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search patient name, account #, HMO...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Account No</th>
              <th>Patient / Guarantor Name</th>
              <th>Payor Category</th>
              <th>Insurance Policy / Guarantee</th>
              <th>Approved Cap</th>
              <th class="text-end">Balance Due (₱)</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AR-PAT-881</span></td>
              <td>
                <div class="fw-semibold text-dark">Juan Dela Cruz</div>
                <div class="text-muted fs-xs">Patient #10429 — Room 402 (Inpatient Ward A)</div>
              </td>
              <td><span class="badge bg-primary-subtle text-primary">Inpatient Self-Pay</span></td>
              <td>Maxicare HMO (<span class="font-monospace fs-xs">POL-99210</span>)</td>
              <td>₱200,000.00</td>
              <td class="text-end fw-bold text-danger">₱24,500.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-bed"></i> Active Admission</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Patient Ledger"><i class="ph ph-file-text"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AR-HMO-004</span></td>
              <td>
                <div class="fw-semibold text-dark">PhilHealth Insurance Corp</div>
                <div class="text-muted fs-xs">Government Healthcare Coverage Guarantee</div>
              </td>
              <td><span class="badge bg-success-subtle text-success">Government Guarantor</span></td>
              <td>National Universal Coverage</td>
              <td>Unlimited Batch</td>
              <td class="text-end fw-bold text-danger">₱820,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active Payor</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View HMO Master Record"><i class="ph ph-file-text"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AR-PAT-884</span></td>
              <td>
                <div class="fw-semibold text-dark">Maria Santos</div>
                <div class="text-muted fs-xs">Patient #10435 — Surgical Recovery Suite</div>
              </td>
              <td><span class="badge bg-info-subtle text-info">Commercial HMO</span></td>
              <td>Intellicare HMO (<span class="font-monospace fs-xs">POL-44109</span>)</td>
              <td>₱150,000.00</td>
              <td class="text-end fw-bold text-dark">₱85,200.00</td>
              <td><span class="badge bg-primary-subtle text-primary">Pending Discharge</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Patient Ledger"><i class="ph ph-file-text"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
