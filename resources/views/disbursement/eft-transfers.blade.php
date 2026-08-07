@extends('layouts.app')

@section('title', 'EFT Transfers - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'eft-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">EFT Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Electronic Funds Transfer (EFT / Wire)</h1>
      <p class="text-muted small mb-0">Automated electronic bank disbursements for supplier payments and bi-weekly hospital staff payroll batches.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> New Bank Transfer Batch</button>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Bi-Weekly Staff Payroll Batches</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-users-three fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱410,000.00</h4>
        <span class="fs-xs text-muted">Direct Deposit to 180 Employees</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Supplier Digital Transfers</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱850,000.00</h4>
        <span class="fs-xs text-muted">PESONet &amp; InstaPay Wire</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Processing Success Rate</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Settled</h4>
        <span class="fs-xs text-info"><i class="ph ph-shield-check"></i> Zero Bounced Transfers</span>
      </div>
    </div>
  </div>

  <!-- EFT Transfers Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Transfer Ref</th>
              <th>Transfer Type</th>
              <th>Recipient Bank &amp; Account</th>
              <th>Particulars / Purpose</th>
              <th>Execution Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Settlement Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">EFT-2026-901</span></td>
              <td><span class="badge bg-primary-subtle text-primary">Direct Payroll Batch</span></td>
              <td>BDO Unibank (<span class="font-monospace fs-xs">**** 4819</span>)</td>
              <td>Bi-Weekly Medical Staff &amp; Nurse Payroll Direct Deposit</td>
              <td>2026-08-05</td>
              <td class="text-end fw-bold text-dark">₱410,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle"></i> Settled / Transferred</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Remittance File"><i class="ph ph-file-text"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">EFT-2026-902</span></td>
              <td><span class="badge bg-info-subtle text-info">PESONet Supplier Wire</span></td>
              <td>Bank of the Philippine Islands (<span class="font-monospace fs-xs">**** 9912</span>)</td>
              <td>Siemens Healthcare CT Maintenance Spare Parts Payment</td>
              <td>2026-08-06</td>
              <td class="text-end fw-bold text-dark">₱48,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle"></i> Settled / Transferred</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Remittance File"><i class="ph ph-file-text"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
