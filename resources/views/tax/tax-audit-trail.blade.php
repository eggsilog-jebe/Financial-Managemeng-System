@extends('layouts.app')

@section('title', 'Tax Audit Trail - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-audit')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Audit Trail</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Audit Trail &amp; Verification Logs</h1>
      <p class="text-muted small mb-0">Immutably logged transaction audit records for internal tax compliance and external revenue authority reviews.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Audit Timestamp</th><th>User</th><th>Event Category</th><th>Source Voucher</th><th>Tax Impact (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>2026-08-07 10:14:22</td>
            <td>tax_officer_1</td>
            <td>EWT 2307 Form Generation</td>
            <td>C2307-2026-881</td>
            <td class="text-end text-danger fw-bold">-₱12,000.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
