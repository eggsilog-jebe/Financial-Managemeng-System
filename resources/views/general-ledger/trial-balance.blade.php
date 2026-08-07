@extends('layouts.app')

@section('title', 'Trial Balance - General Ledger | FMS')
@section('module', 'finance')
@section('page', 'trial-balance')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Trial Balance</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Trial Balance Report</h1>
      <p class="text-muted small mb-0">Periodic verification that total debits equal total credits across all accounts.</p>
    </div>
    <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Export PDF</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Account Code</th>
            <th>Account Name</th>
            <th class="text-end">Debit Total (₱)</th>
            <th class="text-end">Credit Total (₱)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1020</td>
            <td class="fw-medium">Operating Bank Account - Metrobank</td>
            <td class="text-end text-success fw-semibold">₱3,420,000.00</td>
            <td class="text-end text-muted">₱0.00</td>
          </tr>
          <tr>
            <td>2010</td>
            <td class="fw-medium">Accounts Payable - Medical Vendors</td>
            <td class="text-end text-muted">₱0.00</td>
            <td class="text-end text-danger fw-semibold">₱3,420,000.00</td>
          </tr>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="2" class="text-end">Balanced Totals:</td>
            <td class="text-end text-success">₱3,420,000.00</td>
            <td class="text-end text-danger">₱3,420,000.00</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection
