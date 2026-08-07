@extends('layouts.app')

@section('title', 'Bank Reconciliation - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-reconciliation')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Reconciliation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Reconciliation</h1>
      <p class="text-muted small mb-0">Automated matching of bank statement feeds against internal general ledger accounts.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-file-arrow-up me-1"></i> Import Statement File</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Statement Date</th><th>Account</th><th class="text-end">Bank Balance (₱)</th><th class="text-end">GL Balance (₱)</th><th class="text-end">Difference (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>2026-07-31</td>
            <td>Metrobank #1020</td>
            <td class="text-end">₱4,850,000.00</td>
            <td class="text-end">₱4,850,000.00</td>
            <td class="text-end text-success font-monospace">₱0.00</td>
            <td><span class="badge bg-success-subtle text-success">Reconciled</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
