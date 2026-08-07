@extends('layouts.app')

@section('title', 'Balance Sheet - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'balance-sheet')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Balance Sheet</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Financial Position (Balance Sheet)</h1>
      <p class="text-muted small mb-0">Summary of total hospital assets, liabilities, and retained net equity.</p>
    </div>
    <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Export PDF</button>
  </div>
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-light fw-bold">Assets</div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <tbody>
              <tr><td>Current Assets (Cash &amp; Receivables)</td><td class="text-end fw-semibold">₱8,910,200.00</td></tr>
              <tr><td>Non-Current Assets (Medical Equipment &amp; Buildings)</td><td class="text-end fw-semibold">₱45,200,000.00</td></tr>
              <tr class="table-success fw-bold"><td>Total Assets</td><td class="text-end text-success">₱54,110,200.00</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-light fw-bold">Liabilities &amp; Equity</div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <tbody>
              <tr><td>Current Liabilities (Accounts Payable)</td><td class="text-end fw-semibold">₱910,500.00</td></tr>
              <tr><td>Long-Term Equipment Loans</td><td class="text-end fw-semibold">₱12,000,000.00</td></tr>
              <tr><td>Hospital Capital &amp; Retained Earnings</td><td class="text-end fw-semibold">₱41,199,700.00</td></tr>
              <tr class="table-primary fw-bold"><td>Total Liabilities &amp; Equity</td><td class="text-end text-primary">₱54,110,200.00</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
