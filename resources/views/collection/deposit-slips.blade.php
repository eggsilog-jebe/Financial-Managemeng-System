@extends('layouts.app')

@section('title', 'Deposit Slips - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'deposit-slips')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Deposit Slips</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Batch Deposit Slips</h1>
      <p class="text-muted small mb-0">Preparation of cash and check batch slips for armored car pickup and bank processing.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Slip Ref</th><th>Batch Date</th><th>Cash Amount (₱)</th><th>Check Amount (₱)</th><th class="text-end">Total Deposit (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">SLIP-2026-081</span></td>
            <td>2026-08-06</td>
            <td>₱35,000.00</td>
            <td>₱10,200.00</td>
            <td class="text-end fw-bold">₱45,200.00</td>
            <td><span class="badge bg-primary-subtle text-primary">Ready for Transport</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
