@extends('layouts.app')

@section('title', 'Purchase Bills - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'purchase-bills')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Purchase Bills</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Purchase &amp; Utility Bills</h1>
      <p class="text-muted small mb-0">Log and manage non-PO recurring operational bills (medical gases, biohazard waste disposal, linen laundry, power utilities).</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Log Utility Bill</button>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Medical Gases &amp; Consumables</span>
        <h4 class="fw-bold mb-0 text-dark">₱124,500.00</h4>
        <span class="fs-xs text-muted">Oxygen, Nitrogen &amp; ICU Gases</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Bio-Hazard &amp; Environmental Services</span>
        <h4 class="fw-bold mb-0 text-warning">₱88,000.00</h4>
        <span class="fs-xs text-muted">Waste Disposal &amp; Linen Laundry</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Facility Power &amp; Water Utilities</span>
        <h4 class="fw-bold mb-0 text-danger">₱645,000.00</h4>
        <span class="fs-xs text-muted">Substation &amp; Water Filtration</span>
      </div>
    </div>
  </div>

  <!-- Bills Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bill ID</th>
              <th>Supplier / Utility Provider</th>
              <th>Expense Particulars</th>
              <th>Hospital Department</th>
              <th>Bill Date</th>
              <th>Due Date</th>
              <th class="text-end">Total Amount (₱)</th>
              <th>Payment Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">BILL-2026-801</span></td>
              <td class="fw-semibold text-dark">Linde Medical Gases Philippines</td>
              <td>Oxygen Cylinder Tank Refill Batch (50 Tanks)</td>
              <td><span class="fs-xs text-muted">ICU &amp; Emergency Room</span></td>
              <td>2026-08-04</td>
              <td>2026-09-03</td>
              <td class="text-end fw-bold text-dark">₱54,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning">Unpaid</span></td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">BILL-2026-802</span></td>
              <td class="fw-semibold text-dark">CleanBio Waste Management Inc</td>
              <td>Sharps &amp; Biohazard Infectious Waste Treatment</td>
              <td><span class="fs-xs text-muted">Hospital Environment &amp; Safety</span></td>
              <td>2026-08-05</td>
              <td>2026-08-20</td>
              <td class="text-end fw-bold text-dark">₱38,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning">Unpaid</span></td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">BILL-2026-803</span></td>
              <td class="fw-semibold text-dark">Manila Electric Company (MERALCO)</td>
              <td>Hospital Main Facility Power Grid Tariff (July)</td>
              <td><span class="fs-xs text-muted">Facility Operations</span></td>
              <td>2026-08-01</td>
              <td>2026-08-15</td>
              <td class="text-end fw-bold text-success">₱645,000.00</td>
              <td><span class="badge bg-success-subtle text-success">Paid</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
