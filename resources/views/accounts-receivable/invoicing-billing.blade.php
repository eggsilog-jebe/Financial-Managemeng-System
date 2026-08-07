@extends('layouts.app')

@section('title', 'Invoicing & Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'billing')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Invoicing &amp; Billing</h1>
      <p class="text-muted small mb-0">Itemized billing statements for inpatient stays, surgeries, lab tests, and ER visits.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Generate Invoice</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Invoice No</th><th>Patient Name</th><th>Department</th><th>Invoice Date</th><th class="text-end">Total Amount (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">INV-2026-4401</span></td>
            <td>Maria Santos</td>
            <td>Surgery &amp; Recovery</td>
            <td>2026-08-05</td>
            <td class="text-end fw-semibold">₱85,200.00</td>
            <td><span class="badge bg-danger-subtle text-danger">Unpaid</span></td>
          </tr>
          <tr>
            <td><span class="font-monospace text-primary">INV-2026-4402</span></td>
            <td>David Miller</td>
            <td>Outpatient Radiology</td>
            <td>2026-08-06</td>
            <td class="text-end fw-semibold">₱6,400.00</td>
            <td><span class="badge bg-success-subtle text-success">Paid</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
