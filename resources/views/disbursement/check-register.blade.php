@extends('layouts.app')

@section('title', 'Check Register - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'check-register')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Check Register</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Check Register</h1>
      <p class="text-muted small mb-0">Official log of physical checks written, signed, issued, cleared, or voided across hospital bank accounts.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Check Register</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Issue Physical Check</button>
    </div>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Checks Issued</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">42 Checks</h4>
        <span class="fs-xs text-muted">Current Month</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Cleared by Bank</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,450,000.00</h4>
        <span class="fs-xs text-success"><i class="ph ph-check-circle"></i> 34 Cleared Checks</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Outstanding Checks</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱245,000.00</h4>
        <span class="fs-xs text-warning"><i class="ph ph-clock"></i> 8 Uncleared Checks</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Voided / Cancelled Checks</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-x-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">0 Stop Payments</span>
      </div>
    </div>
  </div>

  <!-- Check Register Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Issued Check Log</h6>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search check #, payee, bank...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Check No</th>
              <th>Issue Date</th>
              <th>Payee Name</th>
              <th>Bank Account</th>
              <th>Voucher Ref</th>
              <th class="text-end">Amount (₱)</th>
              <th>Check Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">CHK-004812</span></td>
              <td>2026-08-04</td>
              <td class="fw-semibold text-dark">PharmaCorp Philippines</td>
              <td><span class="fs-xs text-muted">Metrobank Operating #1020</span></td>
              <td><span class="font-monospace text-primary">APV-2026-091</span></td>
              <td class="text-end fw-bold text-dark">₱120,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Cleared by Bank</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Check Copy"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">CHK-004813</span></td>
              <td>2026-08-06</td>
              <td class="fw-semibold text-dark">MedTech Diagnostics Inc</td>
              <td><span class="fs-xs text-muted">Metrobank Operating #1020</span></td>
              <td><span class="font-monospace text-primary">APV-2026-092</span></td>
              <td class="text-end fw-bold text-dark">₱98,400.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock"></i> Outstanding (In Transit)</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Check Copy"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
