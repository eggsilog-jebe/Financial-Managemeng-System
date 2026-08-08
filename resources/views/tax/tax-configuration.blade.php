@extends('layouts.app')

@section('title', 'Tax Configuration - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-config')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Configuration</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Rates &amp; Statutory Configuration</h1>
      <p class="text-muted small mb-0">Master tax rate rules, BIR Alphanumeric Tax Codes (ATC), Withholding Tax percentages, and VAT rates.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrow-counter-clockwise me-1"></i> Sync Tax Rates</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addTaxRuleModal"><i class="ph ph-plus-circle me-1"></i> Add Tax Rate Rule</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Tax Rules</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Active Rules</h4>
        <span class="fs-xs text-muted">EWT, VAT, Corporate Tax &amp; PhilHealth</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Standard EWT Rate (Doctors)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-user-stethoscope fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">10.0% / 15.0%</h4>
        <span class="fs-xs text-muted">ATC Code WI010 / WI011</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Default VAT Rate</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">12.0%</h4>
        <span class="fs-xs text-muted">Standard Value Added Tax</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Corporate Income Tax</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">25.0% / 10.0%</h4>
        <span class="fs-xs text-muted">Proprietary Non-Profit Hospital Rate</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Tax Code, ATC, or Description...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Tax Categories</option>
            <option value="ewt">Expanded Withholding Tax (EWT)</option>
            <option value="vat">Value Added Tax (VAT)</option>
            <option value="cit">Corporate Income Tax (CIT)</option>
            <option value="final_vat">Final Withholding VAT</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Rule Statuses</option>
            <option value="active">Active Rules</option>
            <option value="inactive">Inactive Rules</option>
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
              <th>Tax Code &amp; Name</th>
              <th>ATC Code</th>
              <th>Category</th>
              <th class="text-end">Tax Rate (%)</th>
              <th>Applicable Scope</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">EWT - Professional Fees (Medical Consultants)</div>
                <span class="fs-xs font-monospace text-muted">TAX-EWT-DOC10</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">WI010</span></td>
              <td><span class="badge bg-info-subtle text-info">Expanded Withholding Tax</span></td>
              <td class="text-end font-monospace fw-bold text-danger">10.0%</td>
              <td>Visiting Doctors &amp; Medical Consultants (&lt; ₱3M Gross)</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Tax Rule"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">EWT - Medical &amp; Hospital Equipment Suppliers</div>
                <span class="fs-xs font-monospace text-muted">TAX-EWT-SUP01</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">WC158</span></td>
              <td><span class="badge bg-info-subtle text-info">Expanded Withholding Tax</span></td>
              <td class="text-end font-monospace fw-bold text-danger">1.0%</td>
              <td>Purchase of Medical Goods &amp; Pharmaceutical Supplies</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Tax Rule"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Standard Output Value Added Tax (VAT)</div>
                <span class="fs-xs font-monospace text-muted">TAX-VAT-12</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">WV010</span></td>
              <td><span class="badge bg-success-subtle text-success">Value Added Tax</span></td>
              <td class="text-end font-monospace fw-bold text-danger">12.0%</td>
              <td>Non-exempt hospital billings &amp; pharmacy OTC sales</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Edit Tax Rule"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add / Edit Tax Rate Rule -->
<div class="modal fade" id="addTaxRuleModal" tabindex="-1" aria-labelledby="addTaxRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addTaxRuleModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add Statutory Tax Rate Rule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Tax Rate Rule saved successfully!'); bootstrap.Modal.getInstance(document.getElementById('addTaxRuleModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Rule Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. EWT - Medical Services 15%" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR ATC Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. WI011 or WC158" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Category <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="ewt">Expanded Withholding Tax (EWT)</option>
                <option value="vat">Value Added Tax (VAT)</option>
                <option value="cit">Corporate Income Tax (CIT)</option>
                <option value="final_vat">Final Withholding VAT</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Rate Percentage (%) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="10.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Applicable Scope / Regulatory Description</label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="Applies to consultant physicians with gross annual income exceeding ₱3,000,000.00 under BIR RR 11-2018..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Tax Rule</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
