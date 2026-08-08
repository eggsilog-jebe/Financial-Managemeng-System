@extends('layouts.app')

@section('title', 'Budget Reallocations - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'reallocations')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Reallocations</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Reallocations &amp; Inter-Departmental Transfers</h1>
      <p class="text-muted small mb-0">Shift surplus funds between cost centers to cover emergency operational or equipment demands.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-text me-1"></i> Transfer Log PDF</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#requestTransferModal"><i class="ph ph-arrows-left-right me-1"></i> Request Transfer</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Transfer Requests</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Request</h4>
        <span class="fs-xs text-muted">Awaiting CFO approval: ₱150,000</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Approved Transfers (YTD)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Approved</h4>
        <span class="fs-xs text-muted">Total Reallocated: <strong class="text-success">₱450,000.00</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Budget Impact</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">Balanced internal transfers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Rejected Requests</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-x-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">0 Requests</h4>
        <span class="fs-xs text-muted">100% compliant justifications</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Transfer Ref, Source/Target Dept, or Reason...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Source Departments</option>
            <option value="opd">Outpatient Clinic</option>
            <option value="radiology">Radiology &amp; Imaging</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Transfer Statuses</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending CFO Review</option>
            <option value="rejected">Rejected</option>
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
              <th>Source Department (From)</th>
              <th>Destination Department (To)</th>
              <th class="text-end">Transfer Amount (₱)</th>
              <th>Operational Reason</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">REAL-2026-05</span></td>
              <td><span class="badge bg-light text-dark border">Radiology &amp; Imaging</span></td>
              <td><span class="badge bg-light text-dark border">Facilities &amp; Utilities</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱150,000.00</td>
              <td>Coverage for power generator fuel rate hike</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending CFO Review</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-success p-1 py-0 px-2 fs-xs me-1" onclick="alert('Transfer Approved!');"><i class="ph ph-check me-1"></i> Approve</button>
                <button class="btn btn-sm btn-light border p-1" title="View Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">REAL-2026-04</span></td>
              <td><span class="badge bg-light text-dark border">Outpatient Clinic</span></td>
              <td><span class="badge bg-light text-dark border">ICU &amp; Emergency</span></td>
              <td class="text-end text-success fw-bold font-monospace">₱50,000.00</td>
              <td>Emergency Ventilator Parts Acquisition</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Approved</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Transfer Audit"><i class="ph ph-file-check"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Request Inter-Departmental Transfer -->
<div class="modal fade" id="requestTransferModal" tabindex="-1" aria-labelledby="requestTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="requestTransferModalLabel"><i class="ph ph-arrows-left-right me-2 text-primary"></i>Request Inter-Departmental Budget Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Budget transfer request submitted to CFO!'); bootstrap.Modal.getInstance(document.getElementById('requestTransferModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Department (Surplus) <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="radiology">Radiology &amp; Imaging (Surplus: ₱450,000)</option>
                <option value="opd">Outpatient Clinic (Surplus: ₱200,000)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Destination Department (Deficit) <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="facilities">Facilities &amp; Power Utilities</option>
                <option value="er">Emergency Room &amp; ICU</option>
                <option value="pharmacy">Pharmacy &amp; Medical Supplies</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Effective Fiscal Period</label>
              <select class="form-select form-select-sm">
                <option value="q3">Q3 2026 (Immediate)</option>
                <option value="q4">Q4 2026</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Operational Justification &amp; Urgency <span class="text-danger">*</span></label>
              <textarea class="form-control form-control-sm" rows="3" placeholder="State reason for deficit and why surplus funds can be safely spared from source department..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-paper-plane-tilt me-1"></i> Submit Transfer Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
