@extends('layouts.app')

@section('title', 'Profit & Loss Statement - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'profit-loss')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Profit &amp; Loss</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Income Statement (Profit &amp; Loss)</h1>
      <p class="text-muted small mb-0">Hospital operating revenue, direct medical costs, overhead, and net margin performance.</p>
    </div>
    <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Export PDF</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Financial Item</th><th class="text-end">Current Period (₱)</th><th class="text-end">Prior Period (₱)</th></tr>
        </thead>
        <tbody>
          <tr><td class="fw-semibold text-success">Hospital Operating Revenue</td><td class="text-end fw-bold text-success">₱18,450,000.00</td><td class="text-end">₱16,200,000.00</td></tr>
          <tr><td class="ps-4 text-muted">Inpatient Ward &amp; ICU Services</td><td class="text-end">₱11,200,000.00</td><td class="text-end">₱9,800,000.00</td></tr>
          <tr><td class="ps-4 text-muted">Outpatient &amp; ER Revenue</td><td class="text-end">₱4,250,000.00</td><td class="text-end">₱3,900,000.00</td></tr>
          <tr><td class="ps-4 text-muted">Pharmacy &amp; Diagnostics Sales</td><td class="text-end">₱3,000,000.00</td><td class="text-end">₱2,500,000.00</td></tr>
          <tr><td class="fw-semibold text-danger">Operating Expenses (OPEX)</td><td class="text-end fw-bold text-danger">-₱12,100,000.00</td><td class="text-end">-₱11,400,000.00</td></tr>
          <tr class="table-success fw-bold"><td>Net Operating Profit (EBITDA)</td><td class="text-end text-success">₱6,350,000.00</td><td class="text-end">₱4,800,000.00</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
