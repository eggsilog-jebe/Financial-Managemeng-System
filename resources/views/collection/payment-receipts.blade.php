@extends('layouts.app')

@section('title', 'Payment Receipts - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'receipts')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Receipts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Receipts</h1>
      <p class="text-muted small mb-0">Official receipts (OR) issued to patients or payors upon receipt of funds.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Issue Official Receipt</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>OR Number</th><th>Date</th><th>Payor / Patient</th><th>Payment Mode</th><th class="text-end">Amount Paid (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">OR-2026-9901</span></td>
            <td>2026-08-07</td>
            <td>David Miller</td>
            <td>Credit Card (Visa)</td>
            <td class="text-end text-success fw-bold">₱6,400.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
