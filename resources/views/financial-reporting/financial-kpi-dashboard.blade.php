@extends('layouts.app')

@section('title', 'Financial KPI Dashboard - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'kpi-dashboard')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Financial KPI Dashboard</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Healthcare Financial Analytics &amp; KPI Dashboard</h1>
      <p class="text-muted small mb-0">Real-time business intelligence: Days Sales Outstanding (DSO), Net Margins, ARPOB, and Liquidity ratios.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrow-clockwise me-1"></i> Refresh Analytics</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('KPI Performance Brief downloaded!');"><i class="ph ph-download-simple me-1"></i> Export KPI Brief</button>
    </div>
  </div>

  <!-- Primary Executive Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Sales Outstanding (DSO)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">42.5 Days</h4>
        <span class="fs-xs text-muted">HMO Collection Speed: <strong class="text-success">Optimal</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Profit Margin</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">34.4%</h4>
        <span class="fs-xs text-muted">EBITDA Profitability Ratio</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Avg Revenue Per Occupied Bed</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-bed fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱12,400.00</h4>
        <span class="fs-xs text-muted">Daily Inpatient yield per bed</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">2.4x</h4>
        <span class="fs-xs text-muted">Assets vs Current Liabilities</span>
      </div>
    </div>
  </div>

  <!-- Secondary Healthcare Performance Panels -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-light fw-bold">Collection &amp; Credit Efficiency Metrics</div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="fw-semibold">HMO Collection Efficiency Rate</div>
              <span class="fs-xs text-muted">Maxicare, Intellicare &amp; PhilHealth claims paid vs filed</span>
            </div>
            <div class="text-end">
              <span class="h5 fw-bold text-success mb-0">94.2%</span>
            </div>
          </div>
          <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: 94.2%;"></div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="fw-semibold">Bad Debt &amp; Uncollectible Reserve Ratio</div>
              <span class="fs-xs text-muted">Percentage of defaulted receivables</span>
            </div>
            <div class="text-end">
              <span class="h5 fw-bold text-primary mb-0">1.8%</span>
            </div>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" style="width: 1.8%;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-light fw-bold">Operational Productivity Indicators</div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="fw-semibold">Inpatient Bed Occupancy Rate</div>
              <span class="fs-xs text-muted">Active ward admissions vs total capacity</span>
            </div>
            <div class="text-end">
              <span class="h5 fw-bold text-info mb-0">82.5%</span>
            </div>
          </div>
          <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar bg-info" style="width: 82.5%;"></div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="fw-semibold">Pharmacy Gross Margin Yield</div>
              <span class="fs-xs text-muted">Revenue contribution from drug dispensing</span>
            </div>
            <div class="text-end">
              <span class="h5 fw-bold text-warning mb-0">41.0%</span>
            </div>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-warning" style="width: 41.0%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
