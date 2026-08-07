@extends('layouts.app')

@section('title', 'Bank Deposits - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'bank-deposits')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Bank Deposits</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Deposits Log</h1>
      <p class="text-muted small mb-0">Reconciliation records matching cashier drawer collections with confirmed bank deposits.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Deposit Ref</th><th>Bank Account</th><th>Deposit Date</th><th class="text-end">Amount Deposited (₱)</th><th>Verification</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">DEP-2026-302</span></td>
            <td>Metrobank Operating #1020</td>
            <td>2026-08-05</td>
            <td class="text-end text-success fw-bold">₱125,400.00</td>
            <td><span class="badge bg-success-subtle text-success">Verified by Bank</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
