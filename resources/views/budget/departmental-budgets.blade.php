@extends('layouts.app')

@section('title', 'Departmental Budgets - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'departmental-budgets')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Departmental Budgets</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Operating Budgets</h1>
      <p class="text-muted small mb-0">Detailed breakdown of operational spending caps assigned to hospital heads of department.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-chart-pie-slice me-1"></i> Department Summary</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editDepartmentModal"><i class="ph ph-pencil-line me-1"></i> Edit Department Cap</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Department Units</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Departments</h4>
        <span class="fs-xs text-muted">ER, ICU, Pharmacy, Surgery, OPD, IT</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Department Caps</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱85,000,000.00</h4>
        <span class="fs-xs text-muted">Annual approved quota pool</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Consumed</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱42,500,000.00</h4>
        <span class="fs-xs text-muted">Realized disbursements &amp; POs</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Average Hospital Burn Rate</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-gauge fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">50.0%</h4>
        <span class="fs-xs text-muted">On Track for Q3/Q4 Target</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Department, Head of Dept, or Code...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Hospital Wings</option>
            <option value="clinical">Clinical Services (ER, ICU, Surgery)</option>
            <option value="pharmacy">Pharmacy &amp; Supplies</option>
            <option value="admin">Admin &amp; IT Support</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Burn Rate Statuses</option>
            <option value="normal">Normal (&lt; 75%)</option>
            <option value="warning">High Burn (75% - 90%)</option>
            <option value="exceeded">Critical / Over Budget (&gt; 90%)</option>
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
              <th>Department Name</th>
              <th>Head of Department</th>
              <th class="text-end">Annual Cap (₱)</th>
              <th class="text-end">YTD Spent (₱)</th>
              <th class="text-end">Available Quota (₱)</th>
              <th style="min-width: 180px;">Burn Rate %</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">Cardiology &amp; ICU Care</div>
                <span class="fs-xs font-monospace text-muted">DEPT-ICU-01</span>
              </td>
              <td>Dr. Alejandro Santos</td>
              <td class="text-end font-monospace fw-semibold">₱22,000,000.00</td>
              <td class="text-end text-primary font-monospace">₱12,700,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱9,300,000.00</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar bg-info" style="width: 57.7%;"></div>
                  </div>
                  <span class="fs-xs fw-semibold">57.7%</span>
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Department Ledger"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Adjust Cap" data-bs-toggle="modal" data-bs-target="#editDepartmentModal"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Pharmacy &amp; Medical Therapeutics</div>
                <span class="fs-xs font-monospace text-muted">DEPT-PHARM-02</span>
              </td>
              <td>Pharm. Elena Rostova</td>
              <td class="text-end font-monospace fw-semibold">₱25,000,000.00</td>
              <td class="text-end text-primary font-monospace">₱19,300,000.00</td>
              <td class="text-end text-warning fw-bold font-monospace">₱5,700,000.00</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 77.2%;"></div>
                  </div>
                  <span class="fs-xs fw-semibold text-warning">77.2%</span>
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Department Ledger"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Adjust Cap" data-bs-toggle="modal" data-bs-target="#editDepartmentModal"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Emergency Room (ER) Operations</div>
                <span class="fs-xs font-monospace text-muted">DEPT-ER-03</span>
              </td>
              <td>Dr. Marcus Vance</td>
              <td class="text-end font-monospace fw-semibold">₱18,000,000.00</td>
              <td class="text-end text-primary font-monospace">₱8,500,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱9,500,000.00</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 47.2%;"></div>
                  </div>
                  <span class="fs-xs fw-semibold text-success">47.2%</span>
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Department Ledger"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Adjust Cap" data-bs-toggle="modal" data-bs-target="#editDepartmentModal"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Departmental Budget Cap -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="editDepartmentModalLabel"><i class="ph ph-pencil-line me-2 text-primary"></i>Adjust Departmental Budget Cap</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Departmental Budget Cap updated!'); bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Department <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="DEPT-PHARM-02">Pharmacy &amp; Medical Therapeutics (Pharm. Elena Rostova)</option>
              <option value="DEPT-ICU-01">Cardiology &amp; ICU Care (Dr. Alejandro Santos)</option>
              <option value="DEPT-ER-03">Emergency Room Operations (Dr. Marcus Vance)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">New Annual Budget Cap (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" value="28000000.00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Justification for Cap Revision</label>
            <textarea class="form-control form-control-sm" rows="3" placeholder="Explain reason for cap adjustment (e.g., patient volume spike, unexpected drug cost inflation)..."></textarea>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Update Annual Cap</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
