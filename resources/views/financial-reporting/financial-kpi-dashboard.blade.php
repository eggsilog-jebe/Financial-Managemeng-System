@extends('layouts.app')

@section('title', 'Financial KPI Dashboard - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'kpi-dashboard')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Financial KPI Dashboard</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Financial Analytics &amp; KPI Dashboard</h1>
      <p class="text-muted small mb-0">Key healthcare financial indicators: Days Sales Outstanding (DSO), Net Margin, and Occupancy Revenue.</p>
    </div>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Days Sales Outstanding (DSO)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">42.5 Days</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Operating Profit Margin</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">34.4%</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Average Revenue Per Bed</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-bed fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱12,400.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Current Working Ratio</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">2.4x</h4>
      </div>
    </div>
  </div>
</div>
@endsection
