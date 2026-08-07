@extends('layouts.app')

@section('title', 'Invoicing & Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'billing')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Itemized Invoicing &amp; Billing</h1>
      <p class="text-muted small mb-0">Compiled hospital bills integrating Room &amp; Board, Doctor Professional Fees (PF), Pharmacy medicines, Operating Room usage, and Diagnostic Radiology.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Generate Final Bill</button>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Billed This Month</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,250,000.00</h4>
        <span class="fs-xs text-muted">210 Patient Invoices</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Doctor Professional Fees (PF)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-user-stethoscope fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,120,000.00</h4>
        <span class="fs-xs text-muted">Attending Consultants</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pharmacy &amp; Supplies Billed</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-first-aid-kit fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,450,000.00</h4>
        <span class="fs-xs text-muted">Meds, IV &amp; Surgical Kits</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Uncollected Billing Amount</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,230,500.00</h4>
        <span class="fs-xs text-muted">Pending Patient Cash Settlement</span>
      </div>
    </div>
  </div>

  <!-- Invoice Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Invoice No</th>
              <th>Patient Name</th>
              <th>Primary Department</th>
              <th>Invoice Breakdown</th>
              <th>Billing Date</th>
              <th class="text-end">Total Amount (₱)</th>
              <th>Payment Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">INV-2026-4401</span></td>
              <td class="fw-semibold text-dark">Maria Santos</td>
              <td>Surgery &amp; Recovery Suite</td>
              <td>
                <div class="fs-xs text-muted">Room (₱25k) + PF (₱30k) + OR Sterilization (₱30.2k)</div>
              </td>
              <td>2026-08-05</td>
              <td class="text-end fw-bold text-dark">₱85,200.00</td>
              <td><span class="badge bg-danger-subtle text-danger">Unpaid (Pending Discharge)</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Print Itemized Invoice"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">INV-2026-4402</span></td>
              <td class="fw-semibold text-dark">David Miller</td>
              <td>Outpatient Radiology</td>
              <td>
                <div class="fs-xs text-muted">Chest CT Scan with Contrast + Consultation Fee</div>
              </td>
              <td>2026-08-06</td>
              <td class="text-end fw-bold text-dark">₱6,400.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Paid at Cashier</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Print Itemized Invoice"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
