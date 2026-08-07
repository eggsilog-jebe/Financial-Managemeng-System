@extends('layouts.app')

@section('title', 'Disbursement Approvals - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'disbursement-approval')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement</li>
          <li class="breadcrumb-item active">Disbursement Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Disbursement Approvals</h1>
      <p class="text-muted small mb-0">High-level treasury and CFO approval workflow prior to fund release.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Request Ref</th><th>Payee</th><th>Payment Method</th><th class="text-end">Amount (₱)</th><th>Action</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">REQ-2026-114</span></td>
            <td>Surgical Supplies Co.</td>
            <td>EFT Transfer</td>
            <td class="text-end fw-semibold">₱18,500.00</td>
            <td><button class="btn btn-success btn-sm me-1"><i class="ph ph-check"></i> Authorize</button><button class="btn btn-outline-danger btn-sm"><i class="ph ph-x"></i> Reject</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
