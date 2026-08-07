@extends('layouts.app')

@section('title', 'Invoices & Vouchers - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'invoices')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Invoices &amp; Vouchers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable Vouchers (3-Way Matching)</h1>
      <p class="text-muted small mb-0">Automated 3-way verification matching Vendor Invoices against Purchase Orders (PO) and Warehouse Goods Received Notes (GRN).</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-funnel me-1"></i> Filter Mismatches</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Create AP Voucher</button>
    </div>
  </div>

  <!-- Summary Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Vouchers Pending</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">18 Vouchers</h4>
        <span class="fs-xs text-muted">Gross Value: ₱1,450,000.00</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">3-Way Matched (Ready)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">14 Vouchers</h4>
        <span class="fs-xs text-success"><i class="ph ph-check-circle"></i> PO &amp; Receiving Confirmed</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">3-Way Mismatched</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Vouchers</h4>
        <span class="fs-xs text-danger"><i class="ph ph-warning"></i> Quantity / Unit Cost Mismatch</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Net EWT Withheld</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,500.00</h4>
        <span class="fs-xs text-muted">1% / 2% BIR Form 2307</span>
      </div>
    </div>
  </div>

  <!-- Vouchers Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">AP Vouchers Register</h6>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search voucher #, vendor, PO...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref</th>
              <th>Vendor Name</th>
              <th>PO &amp; GRN Ref</th>
              <th>Invoice Date</th>
              <th>Due Date</th>
              <th class="text-end">Gross Amount (₱)</th>
              <th class="text-end">EWT (₱)</th>
              <th class="text-end">Net Payable (₱)</th>
              <th>3-Way Match</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">APV-2026-091</span></td>
              <td class="fw-semibold text-dark">PharmaCorp Philippines</td>
              <td>
                <div class="fs-xs"><span class="font-monospace text-primary">PO-88210</span> / <span class="font-monospace text-muted">GRN-4410</span></div>
              </td>
              <td>2026-08-01</td>
              <td>2026-08-31</td>
              <td class="text-end">₱145,000.00</td>
              <td class="text-end text-muted">-₱1,450.00</td>
              <td class="text-end fw-bold text-dark">₱143,550.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-double"></i> 3-Way Matched</span></td>
              <td><span class="badge bg-primary-subtle text-primary">Approved</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Matching Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">APV-2026-092</span></td>
              <td class="fw-semibold text-dark">MedTech Diagnostics</td>
              <td>
                <div class="fs-xs"><span class="font-monospace text-primary">PO-88215</span> / <span class="font-monospace text-muted">GRN-4419</span></div>
              </td>
              <td>2026-08-03</td>
              <td>2026-09-17</td>
              <td class="text-end">₱98,400.00</td>
              <td class="text-end text-muted">-₱1,968.00</td>
              <td class="text-end fw-bold text-dark">₱96,432.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-double"></i> 3-Way Matched</span></td>
              <td><span class="badge bg-warning-subtle text-warning">Pending Review</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Matching Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">APV-2026-093</span></td>
              <td class="fw-semibold text-dark">Surgical Supplies &amp; Implants</td>
              <td>
                <div class="fs-xs"><span class="font-monospace text-primary">PO-88220</span> / <span class="font-monospace text-danger">GRN-HOLD</span></div>
              </td>
              <td>2026-08-05</td>
              <td>2026-09-04</td>
              <td class="text-end">₱52,000.00</td>
              <td class="text-end text-muted">-₱520.00</td>
              <td class="text-end fw-bold text-dark">₱51,480.00</td>
              <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-warning"></i> Qty Mismatch</span></td>
              <td><span class="badge bg-danger-subtle text-danger">On Hold</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Mismatch Reason"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
