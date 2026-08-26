@extends('layouts.app')

@section('title', 'Financial KPI Dashboard - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'financial-kpi-dashboard')

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
      <h1 class="h3 mb-0 font-weight-bold">Hospital Executive Financial KPI &amp; Analytics Deck</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Financial Statements', 'AR/AP Aging', 'Treasury Reserves']" 
          description="Hospital executive financial scorecard and 12-month trajectory analytics." 
      />
      <a href="{{ route('reporting.kpi-dashboard.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export KPI Deck (CSV)
      </a>
      <a href="{{ route('reporting.executive-reports') }}" class="btn btn-primary btn-sm">
        <i class="ph ph-briefcase me-1"></i> Executive Dossier
      </a>
    </div>
  </div>

  <!-- KPI Metric Cards Grid -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Profit Margin</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h3 class="fw-bold mb-0 text-success">{{ number_format((float) ($operating_margin ?? $operatingProfitMargin ?? 0), 1) }}%</h3>
        <span class="fs-xs text-muted">Target: &gt; 15.0% Operating Surplus</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Sales Outstanding (DSO)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-calendar-check fs-5"></i></span>
        </div>
        <h3 class="fw-bold mb-0 text-primary">{{ number_format((float) ($dso ?? 0), 1) }} Days</h3>
        <span class="fs-xs text-muted">AR Cycle: ₱{{ number_format((float) ($total_ar ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Payable Outstanding (DPO)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ number_format((float) ($dpo ?? 0), 1) }} Days</h3>
        <span class="fs-xs text-muted">AP Cycle: ₱{{ number_format((float) ($total_ap ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ number_format((float) ($current_ratio ?? $currentRatio ?? 0), 2) }}x</h3>
        <span class="fs-xs text-muted">Quick Ratio: {{ number_format((float) ($quick_ratio ?? 0), 2) }}x</span>
      </div>
    </div>
  </div>

  <!-- Secondary Metrics Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium d-block mb-1">Total Operating Revenue</span>
        <h4 class="fw-bold text-success mb-0">₱{{ number_format((float) ($total_revenue ?? $totalRevenue ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Clinical Services, HMOs &amp; Diagnostics</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium d-block mb-1">Total Operating Expenses</span>
        <h4 class="fw-bold text-danger mb-0">₱{{ number_format((float) ($total_expense ?? $totalExpense ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Medical Supplies, Payroll &amp; Overhead</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium d-block mb-1">Liquid Cash Reserves &amp; DCOH</span>
        <h4 class="fw-bold text-primary mb-0">₱{{ number_format((float) ($total_cash ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">{{ number_format((float) ($days_cash_on_hand ?? 0), 1) }} Days Cash on Hand</span>
      </div>
    </div>
  </div>

  <!-- 12-Month Financial Trajectory Grid -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-chart-line-up me-2 text-primary"></i>12-Month Trajectory: Revenue vs. Expense &amp; Net Surplus Run</h6>
      <span class="badge bg-primary-subtle text-primary">Trailing 12 Months</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Fiscal Month</th>
              <th class="text-end">Revenue Inflow (₱)</th>
              <th class="text-end">Expense Outflow (₱)</th>
              <th class="text-end">Operating Surplus / (Deficit) (₱)</th>
              <th class="text-center">Performance Indicator</th>
            </tr>
          </thead>
          <tbody>
            @forelse($trajectory ?? [] as $t)
            @php
              $isMonthSurplus = ($t['surplus'] >= 0);
            @endphp
            <tr>
              <td class="fw-bold text-dark">{{ $t['label'] }}</td>
              <td class="text-end font-monospace text-success">₱{{ number_format((float) $t['revenue'], 2) }}</td>
              <td class="text-end font-monospace text-danger">₱{{ number_format((float) $t['expense'], 2) }}</td>
              <td class="text-end font-monospace fw-bold {{ $isMonthSurplus ? 'text-primary' : 'text-danger' }}">
                {{ $isMonthSurplus ? '+' : '' }}₱{{ number_format((float) $t['surplus'], 2) }}
              </td>
              <td class="text-center">
                @if($isMonthSurplus)
                  <span class="badge bg-success-subtle text-success"><i class="ph ph-arrow-up-right me-1"></i> Surplus</span>
                @else
                  <span class="badge bg-danger-subtle text-danger"><i class="ph ph-arrow-down-right me-1"></i> Deficit</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">No monthly trajectory data recorded.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
