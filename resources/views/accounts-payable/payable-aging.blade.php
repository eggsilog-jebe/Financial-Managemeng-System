@extends('layouts.app')

@section('title', 'Payable Aging - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'payable-aging')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payable Aging Report</h1>
      <p class="text-muted small mb-0">Categorized tracking of vendor liabilities by payment age brackets.</p>
    </div>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-3 p-3"><span class="text-muted small">Current (0-30 Days)</span><h4 class="fw-bold text-success mb-0">₱680,200.00</h4></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-3 p-3"><span class="text-muted small">31-60 Days</span><h4 class="fw-bold text-warning mb-0">₱185,000.00</h4></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-3 p-3"><span class="text-muted small">61-90 Days</span><h4 class="fw-bold text-danger mb-0">₱45,300.00</h4></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm rounded-3 p-3"><span class="text-muted small">Over 90 Days</span><h4 class="fw-bold text-dark mb-0">₱0.00</h4></div></div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Vendor</th><th class="text-end">Current (₱)</th><th class="text-end">31-60 Days (₱)</th><th class="text-end">61-90 Days (₱)</th><th class="text-end">Total Due (₱)</th></tr>
        </thead>
        <tbody>
          <tr><td class="fw-medium">PharmaCorp Philippines</td><td class="text-end text-success">₱320,000.00</td><td class="text-end text-warning">₱100,000.00</td><td class="text-end text-muted">₱0.00</td><td class="text-end fw-bold">₱420,000.00</td></tr>
          <tr><td class="fw-medium">MedTech Diagnostics</td><td class="text-end text-success">₱225,200.00</td><td class="text-end text-warning">₱40,000.00</td><td class="text-end text-danger">₱45,300.00</td><td class="text-end fw-bold">₱310,500.00</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
