@extends('layouts.app')

@section('title', 'Profit & Loss Statement - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'profit-loss')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Profit &amp; Loss</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Income Statement (Profit &amp; Loss)</h1>
      <p class="text-muted small mb-0">Hospital operating revenue, direct medical costs, overhead, and net margin performance.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print P&amp;L</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Income Statement PDF exported!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Operating Revenue</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱18,450,000.00</h4>
        <span class="fs-xs text-muted">+13.8% Growth vs. Prior Period</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Expenses (OPEX)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱12,100,000.00</h4>
        <span class="fs-xs text-muted">Salaries, Supplies &amp; Utilities</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Profit (EBITDA)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱6,350,000.00</h4>
        <span class="fs-xs text-muted">+32.3% Operating Profit Increase</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Net Margin %</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">34.42%</h4>
        <span class="fs-xs text-muted">Healthy Operating Margin</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Reporting Fiscal Period</label>
          <select class="form-select form-select-sm bg-light">
            <option value="ytd">Year-To-Date 2026 (Jan 01 - Aug 08)</option>
            <option value="q2">Q2 2026 (Apr - Jun)</option>
            <option value="q1">Q1 2026 (Jan - Mar)</option>
            <option value="custom">Custom Date Range</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Comparison Period</label>
          <select class="form-select form-select-sm bg-light">
            <option value="prior_period">Prior Fiscal Period (₱16,200,000.00)</option>
            <option value="prior_year">Prior Year Same Period (FY 2025)</option>
          </select>
        </div>
        <div class="col-md-4 text-end pt-3">
          <button class="btn btn-sm btn-primary"><i class="ph ph-funnel me-1"></i> Apply Filter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Income Statement Data Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Financial Item &amp; Revenue / Expense Account</th>
              <th class="text-end">Current Period (₱)</th>
              <th class="text-end">Prior Period (₱)</th>
              <th class="text-end">Variance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-light fw-bold text-success"><td colspan="4">1. Hospital Operating Revenue</td></tr>
            <tr>
              <td class="ps-4 fw-semibold">Inpatient Ward &amp; ICU Admission Revenue</td>
              <td class="text-end font-monospace text-success fw-semibold">₱11,200,000.00</td>
              <td class="text-end font-monospace">₱9,800,000.00</td>
              <td class="text-end font-monospace text-success">+₱1,400,000.00</td>
            </tr>
            <tr>
              <td class="ps-4 fw-semibold">Outpatient Clinic &amp; ER Consultation Fees</td>
              <td class="text-end font-monospace text-success fw-semibold">₱4,250,000.00</td>
              <td class="text-end font-monospace">₱3,900,000.00</td>
              <td class="text-end font-monospace text-success">+₱350,000.00</td>
            </tr>
            <tr>
              <td class="ps-4 fw-semibold">Pharmacy &amp; Diagnostic Sales</td>
              <td class="text-end font-monospace text-success fw-semibold">₱3,000,000.00</td>
              <td class="text-end font-monospace">₱2,500,000.00</td>
              <td class="text-end font-monospace text-success">+₱500,000.00</td>
            </tr>
            <tr class="table-success fw-bold">
              <td>TOTAL GROSS REVENUE</td>
              <td class="text-end text-success font-monospace fs-6">₱18,450,000.00</td>
              <td class="text-end font-monospace fs-6">₱16,200,000.00</td>
              <td class="text-end text-success font-monospace fs-6">+₱2,250,000.00</td>
            </tr>

            <tr class="table-light fw-bold text-danger"><td colspan="4">2. Operating Expenses (OPEX)</td></tr>
            <tr>
              <td class="ps-4 text-muted">Doctors &amp; Nursing Staff Payroll Benefits</td>
              <td class="text-end font-monospace text-danger">-₱6,500,000.00</td>
              <td class="text-end font-monospace">-₱6,100,000.00</td>
              <td class="text-end font-monospace text-danger">-₱400,000.00</td>
            </tr>
            <tr>
              <td class="ps-4 text-muted">Medical Consumables &amp; Pharmaceuticals</td>
              <td class="text-end font-monospace text-danger">-₱3,800,000.00</td>
              <td class="text-end font-monospace">-₱3,500,000.00</td>
              <td class="text-end font-monospace text-danger">-₱300,000.00</td>
            </tr>
            <tr>
              <td class="ps-4 text-muted">Facility Electricity &amp; Water Utilities</td>
              <td class="text-end font-monospace text-danger">-₱1,800,000.00</td>
              <td class="text-end font-monospace">-₱1,800,000.00</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
            </tr>
            <tr class="table-danger fw-bold">
              <td>TOTAL OPERATING EXPENSES</td>
              <td class="text-end text-danger font-monospace fs-6">-₱12,100,000.00</td>
              <td class="text-end font-monospace fs-6">-₱11,400,000.00</td>
              <td class="text-end text-danger font-monospace fs-6">-₱700,000.00</td>
            </tr>

            <tr class="table-primary fw-bold">
              <td class="fs-6">NET OPERATING PROFIT (EBITDA)</td>
              <td class="text-end text-primary font-monospace fs-5">₱6,350,000.00</td>
              <td class="text-end font-monospace fs-5">₱4,800,000.00</td>
              <td class="text-end text-success font-monospace fs-5">+₱1,550,000.00</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
