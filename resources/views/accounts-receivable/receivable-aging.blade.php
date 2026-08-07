@extends('layouts.app')

@section('title', 'Receivable Aging - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'ar-aging')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Receivable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Receivable Aging Report</h1>
      <p class="text-muted small mb-0">Age analysis of uncollected patient bills and pending HMO claims.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Payor Name</th><th class="text-end">0-30 Days (₱)</th><th class="text-end">31-60 Days (₱)</th><th class="text-end">61-90 Days (₱)</th><th class="text-end">Total Due (₱)</th></tr>
        </thead>
        <tbody>
          <tr><td class="fw-medium">PhilHealth Insurance</td><td class="text-end text-success">₱450,000.00</td><td class="text-end text-warning">₱250,000.00</td><td class="text-end text-danger">₱120,000.00</td><td class="text-end fw-bold">₱820,000.00</td></tr>
          <tr><td class="fw-medium">Maxicare HMO</td><td class="text-end text-success">₱320,000.00</td><td class="text-end text-warning">₱80,000.00</td><td class="text-end text-muted">₱0.00</td><td class="text-end fw-bold">₱400,000.00</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
