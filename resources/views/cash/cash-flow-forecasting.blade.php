@extends('layouts.app')

@section('title', 'Cash Flow Forecasting - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'cash-forecasting')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Cash Flow Forecasting</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Cash Flow Forecasting</h1>
      <p class="text-muted small mb-0">Predictive liquidity projection model based on expected collections and scheduled vendor disbursements.</p>
    </div>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Projected Inflows (30 Days)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">+₱2,450,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Projected Outflows (30 Days)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">-₱1,820,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Estimated Net Liquidity Surplus</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">+₱630,000.00</h4>
      </div>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
      <h6 class="fw-bold mb-3">30-Day Liquidity Outlook</h6>
      <p class="text-muted small mb-0">Hospital cash balances remain strong. High inflow expected from PhilHealth HMO batch payouts in Week 3.</p>
    </div>
  </div>
</div>
@endsection
