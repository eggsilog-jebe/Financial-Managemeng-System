@extends('layouts.app')

@section('title', 'Payment Requests - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'payment-requests')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement</li>
          <li class="breadcrumb-item active">Payment Requests</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Requests</h1>
      <p class="text-muted small mb-0">Requisitions submitted by hospital departments for operational expenses.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> New Request</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Req Ref</th><th>Department</th><th>Payee</th><th>Purpose</th><th class="text-end">Requested (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">REQ-2026-114</span></td>
            <td>Surgery &amp; Operating Room</td>
            <td>Surgical Supplies Co.</td>
            <td>Emergency Sterilizer Maintenance Pack</td>
            <td class="text-end fw-semibold">₱18,500.00</td>
            <td><span class="badge bg-warning-subtle text-warning">Pending Approval</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
