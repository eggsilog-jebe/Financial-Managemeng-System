@extends('layouts.app')

@section('title', 'Statement of Cash Flows - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'cash-flow-statement')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Cash Flow Statement</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Cash Flows</h1>
      <p class="text-muted small mb-0">Analysis of cash generated and utilized across Operating, Investing, and Financing activities.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Cash Flow PDF Statement exported!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Cash Inflow</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">+₱4,200,000.00</h4>
        <span class="fs-xs text-muted">Patient &amp; HMO Collections</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Investing Cash Outflow</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-shopping-bag fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">-₱1,500,000.00</h4>
        <span class="fs-xs text-muted">MRI &amp; Lab Equipment CAPEX</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Financing Cash Outflow</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">-₱500,000.00</h4>
        <span class="fs-xs text-muted">Bank Loan Principal Amortization</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Cash Position Increase</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">+₱2,200,000.00</h4>
        <span class="fs-xs text-muted">Positive Net Cash Inflow</span>
      </div>
    </div>
  </div>

  <!-- Cash Flow Activity Breakdown Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cash Flow Activity Classification</th>
              <th class="text-end">Net Cash Provided / (Used) (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-light fw-bold text-success"><td colspan="2">1. Cash Flow from Operating Activities</td></tr>
            <tr><td class="ps-4">Collections from Patient Billings &amp; HMO Claims</td><td class="text-end font-monospace text-success">+₱16,400,000.00</td></tr>
            <tr><td class="ps-4 text-muted">Less: Cash Paid to Medical Suppliers &amp; Vendors</td><td class="text-end font-monospace text-danger">-₱7,200,000.00</td></tr>
            <tr><td class="ps-4 text-muted">Less: Cash Paid for Staff Payroll &amp; Overtime</td><td class="text-end font-monospace text-danger">-₱5,000,000.00</td></tr>
            <tr class="fw-bold"><td>Net Cash Provided by Operating Activities</td><td class="text-end text-success font-monospace">+₱4,200,000.00</td></tr>

            <tr class="table-light fw-bold text-danger"><td colspan="2">2. Cash Flow from Investing Activities</td></tr>
            <tr><td class="ps-4 text-muted">Purchase of Digital X-Ray &amp; ICU Ventilators</td><td class="text-end font-monospace text-danger">-₱1,500,000.00</td></tr>
            <tr class="fw-bold"><td>Net Cash Used in Investing Activities</td><td class="text-end text-danger font-monospace">-₱1,500,000.00</td></tr>

            <tr class="table-light fw-bold text-warning"><td colspan="2">3. Cash Flow from Financing Activities</td></tr>
            <tr><td class="ps-4 text-muted">Repayment of Commercial Bank Loan Principal</td><td class="text-end font-monospace text-danger">-₱500,000.00</td></tr>
            <tr class="fw-bold"><td>Net Cash Used in Financing Activities</td><td class="text-end text-dark font-monospace">-₱500,000.00</td></tr>
          </tbody>
          <tfoot class="table-primary fw-bold">
            <tr>
              <td class="fs-6">NET INCREASE IN HOSPITAL CASH &amp; EQUIVALENTS</td>
              <td class="text-end text-primary fs-5 font-monospace">+₱2,200,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
