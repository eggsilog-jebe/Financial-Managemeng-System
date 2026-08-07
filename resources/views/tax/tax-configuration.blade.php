@extends('layouts.app')

@section('title', 'Tax Configuration - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-config')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Rules &amp; Setup</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Rates &amp; Rules Setup</h1>
      <p class="text-muted small mb-0">System-wide tax code parameters for VAT (12%), EWT withholding tax, and hospital service tax exemptions.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add Tax Code</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Tax Code</th><th>Tax Name</th><th>Applicable Rate (%)</th><th>Type</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">VAT-12</span></td>
            <td>Value Added Tax (Standard)</td>
            <td class="fw-bold">12.00%</td>
            <td>Output / Input VAT</td>
            <td><span class="badge bg-success-subtle text-success">Active</span></td>
          </tr>
          <tr>
            <td><span class="font-monospace text-primary">EWT-MED</span></td>
            <td>Expanded Withholding Tax (Physician Professional Fees)</td>
            <td class="fw-bold">10.00%</td>
            <td>Withholding Tax</td>
            <td><span class="badge bg-success-subtle text-success">Active</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
