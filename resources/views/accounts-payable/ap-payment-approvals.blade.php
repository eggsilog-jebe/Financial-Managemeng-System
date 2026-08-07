@extends('layouts.app')

@section('title', 'AP Payment Approvals - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'ap-approvals')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">AP Payment Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AP Payment Approvals</h1>
      <p class="text-muted small mb-0">Multi-level authorization workflow for approving vendor disbursements.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Approval ID</th><th>Vendor</th><th>Voucher Ref</th><th class="text-end">Amount (₱)</th><th>Approval Level</th><th>Action</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">AP-APP-102</span></td>
            <td>PharmaCorp Philippines</td>
            <td>APV-2026-091</td>
            <td class="text-end fw-semibold">₱145,000.00</td>
            <td><span class="badge bg-info-subtle text-info">CFO Authorization</span></td>
            <td><button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Approve</button><button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
