@extends('layouts.app')

@section('title', 'Liquidity Management - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'liquidity')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Liquidity Management</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Liquidity &amp; Treasury Management</h1>
      <p class="text-muted small mb-0">Short-term investments, treasury reserve balances, and minimum working capital monitoring.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Reserve Instrument</th><th>Institution</th><th>Yield / Interest Rate</th><th class="text-end">Principal Amount (₱)</th><th>Maturity Date</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">High-Yield Treasury Time Deposit</td>
            <td>Metrobank Trust Group</td>
            <td>5.25% p.a.</td>
            <td class="text-end text-success fw-bold">₱5,000,000.00</td>
            <td>2026-12-15</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
