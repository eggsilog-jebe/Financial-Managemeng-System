@extends('layouts.app')

@section('title', 'AP Payment Approvals - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'ap-approvals')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payment Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AP Payment Approvals &amp; Authorizations</h1>
      <p class="text-muted small mb-0">Multi-level governance workflow for authorizing supplier disbursements prior to fund release.</p>
    </div>
  </div>

  <!-- Approval Tiers Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Tier 1: Dept Head (< ₱50k)</span>
        <h4 class="fw-bold mb-0 text-success">2 Pending</h4>
        <span class="fs-xs text-muted">Materials &amp; ER Approval</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Tier 2: Finance Officer (< ₱250k)</span>
        <h4 class="fw-bold mb-0 text-primary">1 Pending</h4>
        <span class="fs-xs text-muted">Controller Authorization</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Tier 3: CFO Final Release (> ₱250k)</span>
        <h4 class="fw-bold mb-0 text-warning">1 Pending</h4>
        <span class="fs-xs text-muted">Executive Board Sign-off</span>
      </div>
    </div>
  </div>

  <!-- Approvals Queue Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0">Disbursement Authorizations Queue</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Approval ID</th>
              <th>Vendor / Payee</th>
              <th>Voucher Ref</th>
              <th>Department Origin</th>
              <th class="text-end">Voucher Amount (₱)</th>
              <th>Approval Tier Required</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AP-APP-101</span></td>
              <td class="fw-semibold text-dark">PharmaCorp Philippines</td>
              <td><span class="font-monospace text-primary">APV-2026-091</span></td>
              <td>Pharmacy &amp; Therapeutics</td>
              <td class="text-end fw-bold text-dark">₱143,550.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-shield-check"></i> Tier 2: Finance Officer</span></td>
              <td class="text-end">
                <button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Authorize</button>
                <button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AP-APP-102</span></td>
              <td class="fw-semibold text-dark">MedTech Diagnostics Inc</td>
              <td><span class="font-monospace text-primary">APV-2026-092</span></td>
              <td>Laboratory &amp; Radiology</td>
              <td class="text-end fw-bold text-dark">₱310,500.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-shield-star"></i> Tier 3: CFO Release</span></td>
              <td class="text-end">
                <button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Authorize</button>
                <button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">AP-APP-103</span></td>
              <td class="fw-semibold text-dark">Surgical Supplies &amp; Implants</td>
              <td><span class="font-monospace text-primary">APV-2026-094</span></td>
              <td>Surgery &amp; Operating Room</td>
              <td class="text-end fw-bold text-dark">₱18,500.00</td>
              <td><span class="badge bg-info-subtle text-info"><i class="ph ph-user-check"></i> Tier 1: Dept Head</span></td>
              <td class="text-end">
                <button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Authorize</button>
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
