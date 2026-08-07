@extends('layouts.app')

@section('title', 'Petty Cash - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'petty-cash')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement</li>
          <li class="breadcrumb-item active">Petty Cash</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Petty Cash Management</h1>
      <p class="text-muted small mb-0">On-site cash fund management for immediate minor hospital operational expenses.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Replenish Fund</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Voucher #</th><th>Date</th><th>Claimant</th><th>Expense Particulars</th><th class="text-end">Amount (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">PCV-2026-081</span></td>
            <td>2026-08-06</td>
            <td>ER Desk Nurse</td>
            <td>Urgent Courier Fee for Lab Specimens</td>
            <td class="text-end fw-semibold">₱85.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
