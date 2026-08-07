@extends('layouts.app')

@section('title', 'EFT Transfers - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'eft-transfers')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement</li>
          <li class="breadcrumb-item active">EFT Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Electronic Funds Transfer (EFT)</h1>
      <p class="text-muted small mb-0">Automated bank disbursements for supplier invoices and staff payroll.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Transfer Ref</th><th>Recipient Bank</th><th>Account No</th><th>Purpose</th><th class="text-end">Amount (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">EFT-2026-901</span></td>
            <td>BDO Unibank</td>
            <td>**** 4819</td>
            <td>Bi-Weekly Staff Payroll Batch</td>
            <td class="text-end fw-semibold">₱410,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Completed</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
