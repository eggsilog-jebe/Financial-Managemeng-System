@extends('layouts.app')

@section('title', 'Variance Analysis - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'variance-analysis')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Variance Analysis</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget vs. Actual Variance Analysis</h1>
      <p class="text-muted small mb-0">Audit and real-time comparison of projected budget allocations against actual hospital spending and revenue.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrows-counter-clockwise me-1"></i> Re-Calculate Variances</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#exportVarianceModal"><i class="ph ph-file-arrow-down me-1"></i> Export Audit Variance PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Favorable Variances (Savings)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">+₱1,420,000.00</h4>
        <span class="fs-xs text-muted">Cost savings under budget caps</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unfavorable Variances (Over-Spend)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">-₱345,000.00</h4>
        <span class="fs-xs text-muted">Cost overruns flagged for review</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Budget Variance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">+₱1,075,000.00</h4>
        <span class="fs-xs text-muted">Net hospital budget surplus</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Over-Budget Flagged Accounts</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-warning-octagon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2 Accounts</h4>
        <span class="fs-xs text-muted">Facility Power Utilities &amp; Nurse Overtime</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Expense Category or Account Line...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Variance Types</option>
            <option value="favorable">Favorable (Under Budget)</option>
            <option value="unfavorable">Unfavorable (Over Budget)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Departments</option>
            <option value="pharmacy">Pharmacy</option>
            <option value="facilities">Facilities &amp; Power</option>
            <option value="icu">ICU &amp; Surgery</option>
          </select>
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-sm btn-light border w-100"><i class="ph ph-funnel me-1"></i> Filter</button>
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
              <th>Expense Category / Line Item</th>
              <th class="text-end">Budgeted (₱)</th>
              <th class="text-end">Actual Realized (₱)</th>
              <th class="text-end">Variance (₱)</th>
              <th>Variance %</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">Pharmacy Medical Supplies &amp; Antibiotics</div>
                <span class="fs-xs text-muted">Cost Center: CC-101 (Pharmacy)</span>
              </td>
              <td class="text-end font-monospace">₱2,500,000.00</td>
              <td class="text-end font-monospace">₱2,280,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">+₱220,000.00</td>
              <td><span class="text-success fw-semibold">+8.8%</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Favorable (Under Budget)</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Transaction Audit"><i class="ph ph-magnifying-glass-plus"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Facility Electric Utility &amp; Generator Power</div>
                <span class="fs-xs text-muted">Cost Center: CC-104 (Facilities)</span>
              </td>
              <td class="text-end font-monospace">₱600,000.00</td>
              <td class="text-end font-monospace">₱645,000.00</td>
              <td class="text-end text-danger fw-bold font-monospace">-₱45,000.00</td>
              <td><span class="text-danger fw-semibold">-7.5%</span></td>
              <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-warning-circle me-1"></i> Unfavorable (Over Budget)</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Request Reallocation Transfer"><i class="ph ph-arrows-left-right"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">ICU Surgical Equipment Maintenance</div>
                <span class="fs-xs text-muted">Cost Center: CC-102 (ICU Care)</span>
              </td>
              <td class="text-end font-monospace">₱1,800,000.00</td>
              <td class="text-end font-monospace">₱1,450,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">+₱350,000.00</td>
              <td><span class="text-success fw-semibold">+19.4%</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Favorable (Under Budget)</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Transaction Audit"><i class="ph ph-magnifying-glass-plus"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Export Variance Analysis Report -->
<div class="modal fade" id="exportVarianceModal" tabindex="-1" aria-labelledby="exportVarianceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="exportVarianceModalLabel"><i class="ph ph-file-arrow-down me-2 text-primary"></i>Export Variance Analysis Audit PDF</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Variance PDF Audit Report downloaded!'); bootstrap.Modal.getInstance(document.getElementById('exportVarianceModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Reporting Fiscal Period</label>
            <select class="form-select form-select-sm">
              <option value="ytd">Year-To-Date 2026 (Jan - Aug)</option>
              <option value="q1">Q1 2026</option>
              <option value="q2">Q2 2026</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Export Scope</label>
            <select class="form-select form-select-sm">
              <option value="all">All Hospital Cost Centers &amp; Lines</option>
              <option value="unfavorable_only">Unfavorable Over-Budget Accounts Only</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-download me-1"></i> Generate &amp; Download PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
