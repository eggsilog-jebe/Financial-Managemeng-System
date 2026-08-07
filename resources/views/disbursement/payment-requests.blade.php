@extends('layouts.app')

@section('title', 'Payment Requests - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'payment-requests')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Payment Requests</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Payment Requisitions</h1>
      <p class="text-muted small mb-0">Operational fund requisitions submitted by hospital department heads for emergency parts, medical supplies, and service fees.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export Requests</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Submit Payment Request</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Requisitions</span>
          <span class="badge bg-primary-subtle text-primary"><i class="ph ph-file-text"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">12 Requisitions</h4>
        <span class="fs-xs text-muted">Across 5 Hospital Units</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending Approval</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱88,500.00</h4>
        <span class="fs-xs text-muted">4 Requisitions Awaiting CFO</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Budget Verified</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Encumbered</h4>
        <span class="fs-xs text-success">Cost Center Funds Reserved</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Released Payments</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱410,000.00</h4>
        <span class="fs-xs text-muted">Disbursed This Month</span>
      </div>
    </div>
  </div>

  <!-- Requisitions Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <ul class="nav nav-pills flex-grow-1">
          <li class="nav-item"><button class="nav-link active btn-sm py-1 px-3 me-1 fw-semibold">All Requisitions (12)</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Surgery &amp; OR</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Emergency Room</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Biomedical Maintenance</button></li>
        </ul>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search req #, department, payee...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Req Ref</th>
              <th>Department Origin</th>
              <th>Payee / Vendor</th>
              <th>Purpose &amp; Particulars</th>
              <th>Budget Verification</th>
              <th class="text-end">Requested Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">REQ-2026-114</span></td>
              <td class="fw-semibold text-dark">Surgery &amp; Operating Room</td>
              <td>Surgical Supplies &amp; Implants Co.</td>
              <td>Emergency Sterilizer Maintenance Pack &amp; Autoclave Seals</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Encumbered (CC-104)</span></td>
              <td class="text-end fw-bold text-dark">₱18,500.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock"></i> Pending CFO Sign-off</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Requisition Document"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">REQ-2026-115</span></td>
              <td class="fw-semibold text-dark">Emergency Room (ER)</td>
              <td>Linde Medical Gases Philippines</td>
              <td>Urgent Portable Oxygen Cylinder Refills (20 Units)</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Encumbered (CC-102)</span></td>
              <td class="text-end fw-bold text-dark">₱22,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock"></i> Pending Dept Head</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Requisition Document"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">REQ-2026-116</span></td>
              <td class="fw-semibold text-dark">Biomedical Engineering</td>
              <td>Siemens Healthcare Philippines</td>
              <td>CT Scan X-Ray Tube Maintenance Replacement Parts</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Encumbered (CC-108)</span></td>
              <td class="text-end fw-bold text-dark">₱48,000.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-check-double"></i> Approved for EFT</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Requisition Document"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
