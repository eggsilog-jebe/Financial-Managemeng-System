@extends('layouts.app')

@section('title', 'Credit Notes - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Credit Notes</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Credit Notes &amp; Discounts</h1>
      <p class="text-muted small mb-0">Billing adjustments, senior citizen/PWD discounts, and procedure cancellations.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Issue Credit Note</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Credit Note #</th><th>Invoice Ref</th><th>Patient Name</th><th>Reason</th><th class="text-end">Credit Amount (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">CN-2026-041</span></td>
            <td>INV-2026-4401</td>
            <td>Maria Santos</td>
            <td>Senior Citizen Mandatory Discount (20%)</td>
            <td class="text-end text-danger fw-semibold">-₱17,040.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
