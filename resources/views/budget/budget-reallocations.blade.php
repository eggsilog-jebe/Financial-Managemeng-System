@extends('layouts.app')

@section('title', 'Budget Reallocations - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'reallocations')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Reallocations</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Reallocations &amp; Transfers</h1>
      <p class="text-muted small mb-0">Inter-departmental fund transfer requests to adjust for unexpected operational demands.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Request Transfer</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Transfer Ref</th><th>From Department</th><th>To Department</th><th class="text-end">Transfer Amount (₱)</th><th>Reason</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">REAL-2026-04</span></td>
            <td>Outpatient Clinic</td>
            <td>ICU &amp; Emergency</td>
            <td class="text-end fw-bold">₱50,000.00</td>
            <td>Emergency Ventilator Parts Acquisition</td>
            <td><span class="badge bg-success-subtle text-success">Approved</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
