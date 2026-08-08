@extends('layouts.app')

@section('title', 'Fiscal Year Planning - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'fiscal-planning')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Fiscal Year Planning</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Fiscal Year Planning &amp; Target Setting</h1>
      <p class="text-muted small mb-0">High-level hospital operational revenue targets, capital expenditure caps, and master fiscal plans.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-download-simple me-1"></i> Export Master Plan</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createPlanModal"><i class="ph ph-plus-circle me-1"></i> Create Plan Draft</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Master Revenue Target</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-chart-line-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱120,000,000.00</h4>
        <span class="fs-xs text-muted">FY 2026 Inpatient &amp; Outpatient Target</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Master Expense Cap</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱85,000,000.00</h4>
        <span class="fs-xs text-muted">OPEX &amp; CAPEX Ceiling Limit</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Projected Operating Margin</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">29.17%</h4>
        <span class="fs-xs text-muted">Net Target: <strong class="text-success">₱35,000,000.00</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Fiscal Plans</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2 Plans</h4>
        <span class="fs-xs text-muted">1 Active Master, 1 Draft Plan</span>
      </div>
    </div>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Plan Name or Fiscal Period...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Fiscal Years</option>
            <option value="2026">FY 2026 (Current)</option>
            <option value="2027">FY 2027 (Next)</option>
            <option value="2025">FY 2025 (Archived)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Plan Statuses</option>
            <option value="active">Active Master Budget</option>
            <option value="draft">Draft Plan</option>
            <option value="archived">Archived</option>
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
              <th>Plan Name</th>
              <th>Fiscal Period</th>
              <th class="text-end">Revenue Target (₱)</th>
              <th class="text-end">Expense Budget (₱)</th>
              <th class="text-end">Target Net Margin (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">FY 2026 Approved Operating Master Plan</div>
                <span class="fs-xs text-muted">Board Resolution Ref: RES-2025-99</span>
              </td>
              <td>Jan 01, 2026 - Dec 31, 2026</td>
              <td class="text-end text-success font-monospace">₱120,000,000.00</td>
              <td class="text-end text-danger font-monospace">₱85,000,000.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱35,000,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active Master Budget</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Full Plan Breakdown"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Export PDF"><i class="ph ph-file-pdf"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">FY 2027 Expansion &amp; Medical Tech Draft</div>
                <span class="fs-xs text-muted">Preliminary Proposal</span>
              </td>
              <td>Jan 01, 2027 - Dec 31, 2027</td>
              <td class="text-end text-success font-monospace">₱145,000,000.00</td>
              <td class="text-end text-danger font-monospace">₱102,000,000.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱43,000,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Under Review</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Draft"><i class="ph ph-pencil-simple"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Submit to Board"><i class="ph ph-paper-plane-tilt"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Plan Draft -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createPlanModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Fiscal Year Plan Draft</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Fiscal Plan Draft created successfully!'); bootstrap.Modal.getInstance(document.getElementById('createPlanModal')).hide();">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Master Plan Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. FY 2027 Master Operating Plan" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Fiscal Year <span class="text-danger">*</span></label>
              <input type="number" class="form-control form-control-sm" value="2027" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Revenue (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Approved Expense Budget Cap (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Executive Strategy Notes &amp; Assumptions</label>
              <textarea class="form-control form-control-sm" rows="3" placeholder="Notes on anticipated inpatient growth, new medical wing expansion, nurse salary adjustments..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Plan Draft</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
