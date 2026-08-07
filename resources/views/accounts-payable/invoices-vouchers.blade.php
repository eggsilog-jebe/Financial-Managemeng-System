@extends('layouts.app')

@section('title', 'Invoices & Vouchers - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'invoices')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Invoices &amp; Vouchers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Invoices &amp; AP Vouchers</h1>
      <p class="text-muted small mb-0">Process 3-way matching for vendor invoices, purchase orders, and receiving reports.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Create AP Voucher</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Voucher Ref</th>
            <th>Vendor Name</th>
            <th>PO Reference</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th class="text-end">Amount (₱)</th>
            <th>3-Way Match</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">APV-2026-091</span></td>
            <td>PharmaCorp Philippines</td>
            <td>PO-88210</td>
            <td>2026-08-01</td>
            <td>2026-08-31</td>
            <td class="text-end fw-semibold">₱145,000.00</td>
            <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Matched</span></td>
            <td><span class="badge bg-primary-subtle text-primary">Approved</span></td>
          </tr>
          <tr>
            <td><span class="font-monospace text-primary">APV-2026-092</span></td>
            <td>MedTech Diagnostics</td>
            <td>PO-88215</td>
            <td>2026-08-03</td>
            <td>2026-09-17</td>
            <td class="text-end fw-semibold">₱98,400.00</td>
            <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Matched</span></td>
            <td><span class="badge bg-warning-subtle text-warning">Pending Review</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
