@extends('layouts.app')

@section('title', 'Cash Flow Forecasting - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'cash-flow-forecast')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Cash Flow Forecasting</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Predictive Cash Flow Forecasting Model</h1>
      <p class="text-muted small mb-0">Predictive liquidity projection model based on expected collections and scheduled vendor disbursements.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-chart-line me-1"></i> Forecast Chart</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#runForecastModal"><i class="ph ph-play me-1"></i> Run Projection Model</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">30-Day Projected End Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱9,420,000.00</h4>
        <span class="fs-xs text-muted">Positive Net Operating Cashflow</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Expected Collections (30 Days)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱6,800,000.00</h4>
        <span class="fs-xs text-muted">Patient Cash &amp; HMO Claims</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Scheduled Outflows (30 Days)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱5,220,000.00</h4>
        <span class="fs-xs text-muted">AP Invoices, Payroll &amp; Utilities</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Monthly Surplus</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">+₱1,580,000.00</h4>
        <span class="fs-xs text-muted">Net Cash Generation</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Forecast Horizon</label>
          <select class="form-select form-select-sm bg-light">
            <option value="30">30-Day Rolling Forecast</option>
            <option value="60">60-Day Rolling Forecast</option>
            <option value="90">90-Day Quarterly Forecast</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-0">Model Sensitivity</label>
          <select class="form-select form-select-sm bg-light">
            <option value="base">Baseline Collection Model</option>
            <option value="conservative">Conservative (15% Delayed Collections)</option>
            <option value="optimistic">Optimistic (Fast HMO Payouts)</option>
          </select>
        </div>
        <div class="col-md-4 text-end pt-3">
          <button class="btn btn-sm btn-primary"><i class="ph ph-play me-1"></i> Update Projection</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Weekly Period</th>
              <th class="text-end">Starting Cash (₱)</th>
              <th class="text-end">Expected Inflow (₱)</th>
              <th class="text-end">Scheduled Outflow (₱)</th>
              <th class="text-end">Ending Cash Position (₱)</th>
              <th>Liquidity Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="fw-bold text-dark">Week 1 (Aug 08 - Aug 14)</span></td>
              <td class="text-end font-monospace">₱7,840,000.00</td>
              <td class="text-end text-success font-monospace">+₱1,850,000.00</td>
              <td class="text-end text-danger font-monospace">-₱1,400,000.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱8,290,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Healthy Buffer</span></td>
            </tr>
            <tr>
              <td><span class="fw-bold text-dark">Week 2 (Aug 15 - Aug 21)</span></td>
              <td class="text-end font-monospace">₱8,290,000.00</td>
              <td class="text-end text-success font-monospace">+₱1,650,000.00</td>
              <td class="text-end text-danger font-monospace">-₱1,300,000.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱8,640,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Healthy Buffer</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Run Liquidity Projection Model -->
<div class="modal fade" id="runForecastModal" tabindex="-1" aria-labelledby="runForecastModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="runForecastModalLabel"><i class="ph ph-play me-2 text-primary"></i>Run Predictive Liquidity Model</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Cash Flow Forecast model updated!'); bootstrap.Modal.getInstance(document.getElementById('runForecastModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Forecast Period <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="30">30-Day Short Term Forecast</option>
              <option value="60">60-Day Mid Term Forecast</option>
              <option value="90">90-Day Quarterly Forecast</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Include Pending Purchase Orders in Outflows?</label>
            <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incPo"><label class="form-check-label small" for="incPo">Yes, include encumbered POs</label></div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-play me-1"></i> Execute Forecast Engine</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
