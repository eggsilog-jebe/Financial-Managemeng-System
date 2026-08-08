@extends('layouts.app')

@section('title', 'Budget Allocation - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'budget-allocation')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Allocation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Category Allocation</h1>
      <p class="text-muted small mb-0">Distribution of approved hospital master funds to category expenditure accounts and cost centers.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-sliders me-1"></i> Allocation Matrix</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#allocateBudgetModal"><i class="ph ph-plus-circle me-1"></i> Allocate Funds</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Master Allocated</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱85,000,000.00</h4>
        <span class="fs-xs text-muted">100% of FY 2026 Expense Cap</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Encumbered / PO Committed</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-lock-key fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱18,200,000.00</h4>
        <span class="fs-xs text-muted">Active Purchase Orders &amp; Contracts</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Actual Expended (YTD)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱42,500,000.00</h4>
        <span class="fs-xs text-muted">50.0% of Total Annual Cap Spent</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Uncommitted Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱24,300,000.00</h4>
        <span class="fs-xs text-muted">Free pool for remaining FY2026 spend</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Cost Center ID, Category, or Account Code...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Expenditure Categories</option>
            <option value="salaries">Salaries &amp; Personnel Benefits</option>
            <option value="supplies">Medical &amp; Pharmacy Supplies</option>
            <option value="utilities">Facility Utilities &amp; Power</option>
            <option value="capex">Capital Equipment (CAPEX)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Cost Centers</option>
            <option value="CC-101">CC-101: Pharmacy &amp; Therapeutics</option>
            <option value="CC-102">CC-102: Emergency &amp; ICU</option>
            <option value="CC-103">CC-103: Operating Room (OR)</option>
            <option value="CC-104">CC-104: Facilities &amp; Utilities</option>
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
              <th>Cost Center ID</th>
              <th>Expenditure Category</th>
              <th class="text-end">Initial Allocation (₱)</th>
              <th class="text-end">Encumbered / POs (₱)</th>
              <th class="text-end">Actual Expended (₱)</th>
              <th class="text-end">Available Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">CC-101</span></td>
              <td>
                <div class="fw-semibold text-dark">Pharmacy &amp; Therapeutics</div>
                <span class="fs-xs text-muted">Medical Supplies &amp; Outpatient Drugs</span>
              </td>
              <td class="text-end font-monospace fw-semibold">₱25,000,000.00</td>
              <td class="text-end text-warning font-monospace">₱6,500,000.00</td>
              <td class="text-end text-danger font-monospace">₱12,800,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱5,700,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Allocation"><i class="ph ph-pencil"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Encumbrances"><i class="ph ph-list-magnifying-glass"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">CC-102</span></td>
              <td>
                <div class="fw-semibold text-dark">Emergency &amp; ICU Care</div>
                <span class="fs-xs text-muted">Life Support &amp; Emergency Consumables</span>
              </td>
              <td class="text-end font-monospace fw-semibold">₱30,000,000.00</td>
              <td class="text-end text-warning font-monospace">₱8,200,000.00</td>
              <td class="text-end text-danger font-monospace">₱16,500,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱5,300,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Allocation"><i class="ph ph-pencil"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Encumbrances"><i class="ph ph-list-magnifying-glass"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">CC-104</span></td>
              <td>
                <div class="fw-semibold text-dark">Facilities &amp; Power Utilities</div>
                <span class="fs-xs text-muted">Electricity, Water &amp; Medical Waste Disposal</span>
              </td>
              <td class="text-end font-monospace fw-semibold">₱12,000,000.00</td>
              <td class="text-end text-warning font-monospace">₱1,500,000.00</td>
              <td class="text-end text-danger font-monospace">₱7,200,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱3,300,000.00</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Allocation"><i class="ph ph-pencil"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Encumbrances"><i class="ph ph-list-magnifying-glass"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Allocate Category Budget -->
<div class="modal fade" id="allocateBudgetModal" tabindex="-1" aria-labelledby="allocateBudgetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="allocateBudgetModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Allocate Budget to Cost Center</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Budget allocation saved successfully!'); bootstrap.Modal.getInstance(document.getElementById('allocateBudgetModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Cost Center <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="CC-101">CC-101: Pharmacy &amp; Therapeutics</option>
                <option value="CC-102">CC-102: Emergency &amp; ICU Care</option>
                <option value="CC-103">CC-103: Operating Room (OR)</option>
                <option value="CC-104">CC-104: Facilities &amp; Power Utilities</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expenditure Category <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="salaries">Salaries &amp; Personnel Benefits</option>
                <option value="supplies">Medical Supplies &amp; Pharmacy</option>
                <option value="utilities">Facility Utilities &amp; Power</option>
                <option value="capex">Capital Equipment (CAPEX)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Allocation Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Fiscal Quarter / Effective Period</label>
              <select class="form-select form-select-sm">
                <option value="annual">Full Fiscal Year 2026</option>
                <option value="q1">Q1 (Jan - Mar)</option>
                <option value="q2">Q2 (Apr - Jun)</option>
                <option value="q3">Q3 (Jul - Sep)</option>
                <option value="q4">Q4 (Oct - Dec)</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Allocation Authorization Notes</label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="Approved under board resolution section 4.2 for hospital expansion..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Allocation</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
