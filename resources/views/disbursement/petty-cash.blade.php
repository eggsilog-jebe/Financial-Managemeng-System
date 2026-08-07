@extends('layouts.app')

@section('title', 'Petty Cash - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'petty-cash')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Petty Cash</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Petty Cash Fund</h1>
      <p class="text-muted small mb-0">On-site cash drawer management for immediate minor hospital operational expenses (courier fees, emergency ice packs, taxi vouchers).</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-arrows-clockwise me-1"></i> Audit Cash Fund</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Replenish Fund</button>
    </div>
  </div>

  <!-- Fund Status Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Fixed Fund Ceiling</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-wallet fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱50,000.00</h4>
        <span class="fs-xs text-muted">Imprest Fund Ceiling</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Cash Remaining in Drawer</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱34,250.00</h4>
        <span class="fs-xs text-success"><i class="ph ph-check-circle"></i> Healthy Drawer Level</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Disbursed Vouchers</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱15,750.00</h4>
        <span class="fs-xs text-muted">18 Vouchers Pending Replenishment</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Custodian</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-user-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Anna Reyes</h4>
        <span class="fs-xs text-muted">Main Discharge Desk Custodian</span>
      </div>
    </div>
  </div>

  <!-- Petty Cash Vouchers Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Petty Cash Vouchers (PCV Log)</h6>
        <button class="btn btn-sm btn-outline-primary"><i class="ph ph-plus me-1"></i> Issue PCV</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher #</th>
              <th>Date</th>
              <th>Claimant &amp; Department</th>
              <th>Expense Particulars</th>
              <th class="text-end">Amount (₱)</th>
              <th>Audit Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">PCV-2026-081</span></td>
              <td>2026-08-06</td>
              <td>
                <div class="fw-semibold text-dark">ER Desk Nurse</div>
                <div class="text-muted fs-xs">Emergency Room</div>
              </td>
              <td>Urgent Courier Fee for Reference Lab Specimen Transport</td>
              <td class="text-end fw-bold text-dark">₱85.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Receipt Attached</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Receipt Attachment"><i class="ph ph-file-image"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">PCV-2026-082</span></td>
              <td>2026-08-07</td>
              <td>
                <div class="fw-semibold text-dark">OR Head Nurse</div>
                <div class="text-muted fs-xs">Surgery &amp; Operating Room</div>
              </td>
              <td>Emergency Distilled Water for Sterilizer Reservoir</td>
              <td class="text-end fw-bold text-dark">₱240.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Receipt Attached</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Receipt Attachment"><i class="ph ph-file-image"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
