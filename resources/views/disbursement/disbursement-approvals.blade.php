@extends('layouts.app')

@section('title', 'Disbursement Approvals - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'disbursement-approval')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Disbursement Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Treasury Disbursement Approvals</h1>
      <p class="text-muted small mb-0">High-level treasury and CFO verification workflow ensuring funds are fully authorized before physical check printing or wire execution.</p>
    </div>
  </div>

  <!-- Approval Limit Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending Check Releases</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱98,400.00</h4>
        <span class="fs-xs text-muted">1 Physical Check Pending Sign-off</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending EFT Batches</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱410,000.00</h4>
        <span class="fs-xs text-muted">1 Payroll Batch Awaiting CFO Wire Release</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Released Today</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱168,000.00</h4>
        <span class="fs-xs text-success"><i class="ph ph-check-circle"></i> Funds Authorized</span>
      </div>
    </div>
  </div>

  <!-- Approvals Queue Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Disbursement Ref</th>
              <th>Payee Name</th>
              <th>Payment Method</th>
              <th>Source Bank Account</th>
              <th class="text-end">Amount (₱)</th>
              <th>Authorization Level</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">DISB-APP-201</span></td>
              <td class="fw-semibold text-dark">Medical Staff Payroll Direct Batch</td>
              <td><span class="badge bg-info-subtle text-info">EFT Direct Deposit</span></td>
              <td>Metrobank Payroll #8841</td>
              <td class="text-end fw-bold text-dark">₱410,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-shield-star"></i> CFO Authorization Needed</span></td>
              <td class="text-end">
                <button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Release Wire</button>
                <button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">DISB-APP-202</span></td>
              <td class="fw-semibold text-dark">MedTech Diagnostics Inc</td>
              <td><span class="badge bg-primary-subtle text-primary">Physical Crossed Check</span></td>
              <td>Metrobank Operating #1020</td>
              <td class="text-end fw-bold text-dark">₱98,400.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-shield-check"></i> Controller Sign-off</span></td>
              <td class="text-end">
                <button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Authorize Check</button>
                <button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
