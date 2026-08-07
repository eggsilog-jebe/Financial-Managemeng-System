@extends('layouts.app')

@section('title', 'Receivable Aging - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'ar-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Receivable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Receivable Aging &amp; DSO Analytics</h1>
      <p class="text-muted small mb-0">Age analysis of uncollected patient bills and pending HMO claim remittances categorized by payor classes.</p>
    </div>
    <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print AR Aging</button>
  </div>

  <!-- KPI Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">Days Sales Outstanding (DSO)</span>
        <h4 class="fw-bold text-primary mb-1">42.5 Days</h4>
        <span class="fs-xs text-success"><i class="ph ph-trend-down"></i> -3.2 Days vs Last Month</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">0-30 Days (Under Review)</span>
        <h4 class="fw-bold text-success mb-1">₱770,000.00</h4>
        <span class="fs-xs text-muted">Fresh HMO &amp; Patient Invoices</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">31-60 Days (Submitted)</span>
        <h4 class="fw-bold text-warning mb-1">₱330,000.00</h4>
        <span class="fs-xs text-muted">Awaiting HMO Remittance Batch</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small">61-90+ Days (High Risk)</span>
        <h4 class="fw-bold text-danger mb-1">₱120,000.00</h4>
        <span class="fs-xs text-danger"><i class="ph ph-warning"></i> Delayed Government Claims</span>
      </div>
    </div>
  </div>

  <!-- Aging Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Payor Name &amp; Guarantor</th>
              <th>Payor Type</th>
              <th class="text-end">0-30 Days (₱)</th>
              <th class="text-end">31-60 Days (₱)</th>
              <th class="text-end">61-90 Days (₱)</th>
              <th class="text-end">Over 90 Days (₱)</th>
              <th class="text-end">Total Uncollected (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-semibold text-dark">PhilHealth Insurance Corp</td>
              <td><span class="badge bg-success-subtle text-success">Government Guarantor</span></td>
              <td class="text-end text-success fw-semibold">₱450,000.00</td>
              <td class="text-end text-warning fw-semibold">₱250,000.00</td>
              <td class="text-end text-danger fw-semibold">₱120,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱820,000.00</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">Maxicare HMO Philippines</td>
              <td><span class="badge bg-info-subtle text-info">Commercial HMO</span></td>
              <td class="text-end text-success fw-semibold">₱320,000.00</td>
              <td class="text-end text-warning fw-semibold">₱80,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-bold text-dark">₱400,000.00</td>
            </tr>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="2" class="text-end">Total AR Outstanding:</td>
              <td class="text-end text-success">₱770,000.00</td>
              <td class="text-end text-warning">₱330,000.00</td>
              <td class="text-end text-danger">₱120,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-primary">₱1,220,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
