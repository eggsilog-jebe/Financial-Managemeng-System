@extends('layouts.app')

@section('title', 'Payment Gateway Logs - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'payment-gateways')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Gateway Logs</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Gateway Logs</h1>
      <p class="text-muted small mb-0">Digital logs for online bill payments received via patient portal, credit cards, and e-wallets.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Gateway Txn ID</th><th>Gateway Provider</th><th>Patient Account</th><th class="text-end">Amount (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">GW-PAY-98124</span></td>
            <td>PayMaya / GCash Gateway</td>
            <td>AR-PAT-881</td>
            <td class="text-end text-success fw-bold">₱2,500.00</td>
            <td><span class="badge bg-success-subtle text-success">Settled</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
