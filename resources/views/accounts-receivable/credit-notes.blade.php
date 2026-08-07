@extends('layouts.app')

@section('title', 'Credit Notes & Discounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Credit Notes</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Credit Notes &amp; Statutory Discounts</h1>
      <p class="text-muted small mb-0">Record and process Senior Citizen (20%), Persons with Disability (PWD 20%), PhilHealth benefits, and procedure cancellation credits.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Issue Credit Note</button>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Senior Citizen Discounts (RA 9994)</span>
        <h4 class="fw-bold mb-0 text-danger">₱174,000.00</h4>
        <span class="fs-xs text-muted">20% Statutory Reduction</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">PWD Statutory Discounts (RA 10754)</span>
        <h4 class="fw-bold mb-0 text-warning">₱68,500.00</h4>
        <span class="fs-xs text-muted">20% Mandatory Exemption</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Cancelled Procedure Refunds</span>
        <h4 class="fw-bold mb-0 text-info">₱32,000.00</h4>
        <span class="fs-xs text-muted">Radiology &amp; Surgery Reversals</span>
      </div>
    </div>
  </div>

  <!-- Credit Notes Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Credit Note #</th>
              <th>Invoice Ref</th>
              <th>Patient Name</th>
              <th>Discount / Credit Particulars</th>
              <th>Legal Basis / Reason</th>
              <th class="text-end">Credit Adjustment (₱)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">CN-2026-041</span></td>
              <td><span class="font-monospace text-primary">INV-2026-4401</span></td>
              <td class="fw-semibold text-dark">Maria Santos</td>
              <td>20% Mandatory Senior Citizen Discount on Medicines &amp; Room Rate</td>
              <td><span class="badge bg-light text-dark border">RA 9994 Compliance</span></td>
              <td class="text-end text-danger fw-bold">-₱17,040.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Applied</span></td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">CN-2026-042</span></td>
              <td><span class="font-monospace text-primary">INV-2026-4402</span></td>
              <td class="fw-semibold text-dark">David Miller</td>
              <td>Cancelled Outpatient Contrast CT Scan Refund</td>
              <td><span class="badge bg-light text-dark border">Procedure Cancellation</span></td>
              <td class="text-end text-danger fw-bold">-₱3,200.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Applied</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
