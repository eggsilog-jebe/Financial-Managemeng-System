@extends('layouts.app')

@section('title', 'Payable Aging - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'payable-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable Aging Analysis</h1>
      <p class="text-muted small mb-0">Categorized age tracking of unpaid hospital vendor liabilities to optimize early payment discounts and preserve supply chain relationships.</p>
    </div>
    <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Aging Report</button>
  </div>

  <!-- Aging Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Current (0-30 Days)</span>
        <h4 class="fw-bold text-success mb-1">₱680,200.00</h4>
        <span class="fs-xs text-muted">54.8% of Total AP</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">31-60 Days</span>
        <h4 class="fw-bold text-warning mb-1">₱185,000.00</h4>
        <span class="fs-xs text-muted">Approaching Grace Period</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">61-90 Days</span>
        <h4 class="fw-bold text-danger mb-1">₱45,300.00</h4>
        <span class="fs-xs text-danger"><i class="ph ph-warning"></i> High Overdue Priority</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Over 90 Days</span>
        <h4 class="fw-bold text-dark mb-1">₱0.00</h4>
        <span class="fs-xs text-success"><i class="ph ph-check"></i> Zero Critical Suspensions</span>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Vendor Name</th>
              <th>Category</th>
              <th class="text-end">Current (0-30) (₱)</th>
              <th class="text-end">31-60 Days (₱)</th>
              <th class="text-end">61-90 Days (₱)</th>
              <th class="text-end">Over 90 Days (₱)</th>
              <th class="text-end">Total Payable (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-semibold text-dark">PharmaCorp Philippines</td>
              <td><span class="badge bg-light text-dark border">Pharmaceuticals</span></td>
              <td class="text-end text-success fw-semibold">₱320,000.00</td>
              <td class="text-end text-warning fw-semibold">₱100,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱420,000.00</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">MedTech Diagnostics Inc</td>
              <td><span class="badge bg-light text-dark border">Medical Devices</span></td>
              <td class="text-end text-success fw-semibold">₱225,200.00</td>
              <td class="text-end text-warning fw-semibold">₱40,000.00</td>
              <td class="text-end text-danger fw-semibold">₱45,300.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱310,500.00</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">Linde Medical Gases Philippines</td>
              <td><span class="badge bg-light text-dark border">Medical Gases</span></td>
              <td class="text-end text-success fw-semibold">₱54,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱54,000.00</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">Surgical Supplies &amp; Implants Co.</td>
              <td><span class="badge bg-light text-dark border">Surgical Consumables</span></td>
              <td class="text-end text-success fw-semibold">₱18,500.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱18,500.00</td>
            </tr>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="2" class="text-end">Summary Totals:</td>
              <td class="text-end text-success">₱617,700.00</td>
              <td class="text-end text-warning">₱140,000.00</td>
              <td class="text-end text-danger">₱45,300.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-primary">₱803,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
