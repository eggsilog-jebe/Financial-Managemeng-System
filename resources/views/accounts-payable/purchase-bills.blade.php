@extends('layouts.app')

@section('title', 'Purchase Bills - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'purchase-bills')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Purchase Bills</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Purchase Bills</h1>
      <p class="text-muted small mb-0">Incoming recurring supply bills (medical gases, surgical gloves, lab re-agents).</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Log Bill</button>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Medical Gases &amp; Consumables</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-first-aid-kit fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱124,500.00</h4>
        <span class="fs-xs text-muted">Oxygen, Nitrogen &amp; ICU Gases</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Bio-Hazard &amp; Environmental Services</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-trash fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱88,000.00</h4>
        <span class="fs-xs text-muted">Waste Disposal &amp; Linen Laundry</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Facility Power &amp; Water Utilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-lightning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱645,000.00</h4>
        <span class="fs-xs text-muted">Substation &amp; Water Filtration</span>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Bill ID</th>
            <th>Supplier</th>
            <th>Supply Item</th>
            <th>Bill Date</th>
            <th class="text-end">Total Amount (₱)</th>
            <th>Payment Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">BILL-2026-801</span></td>
            <td>Linde Medical Gases</td>
            <td>Oxygen Cylinder Tank Refill Batch</td>
            <td>2026-08-04</td>
            <td class="text-end fw-semibold">₱54,000.00</td>
            <td><span class="badge bg-warning-subtle text-warning">Unpaid</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
