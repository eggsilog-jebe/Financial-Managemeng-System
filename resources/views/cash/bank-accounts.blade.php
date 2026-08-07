@extends('layouts.app')

@section('title', 'Bank Accounts - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-accounts')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Bank Accounts</h1>
      <p class="text-muted small mb-0">Master register of active hospital commercial bank accounts, balances, and branch details.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add Account</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Bank Name</th><th>Account No</th><th>Account Purpose</th><th>Currency</th><th class="text-end">Ledger Balance (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">Metrobank - Main Branch</td>
            <td><span class="font-monospace">1020-8841-99</span></td>
            <td>Primary Operations &amp; Payroll</td>
            <td>PHP (₱)</td>
            <td class="text-end text-success fw-bold">₱4,850,000.00</td>
          </tr>
          <tr>
            <td class="fw-semibold">BDO Unibank - Medical City Branch</td>
            <td><span class="font-monospace">0091-2384-12</span></td>
            <td>Collections &amp; HMO Deposits</td>
            <td>PHP (₱)</td>
            <td class="text-end text-success fw-bold">₱2,140,000.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
