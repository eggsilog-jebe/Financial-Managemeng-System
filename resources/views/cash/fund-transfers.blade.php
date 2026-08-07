@extends('layouts.app')

@section('title', 'Inter-Bank Fund Transfers - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'fund-transfers')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Fund Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Inter-Bank Fund Transfers</h1>
      <p class="text-muted small mb-0">Internal movement of liquidity between commercial bank operational accounts.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> New Transfer</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Transfer ID</th><th>Source Account</th><th>Destination Account</th><th class="text-end">Amount (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">TRF-2026-092</span></td>
            <td>BDO Collection #2384</td>
            <td>Metrobank Operating #1020</td>
            <td class="text-end fw-bold">₱300,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Completed</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
