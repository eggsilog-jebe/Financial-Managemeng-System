@extends('layouts.app')

@section('title', 'Tax Exemptions - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-exemptions')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Exemptions</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Exemptions &amp; Statutory Exemption Register</h1>
      <p class="text-muted small mb-0">Record of VAT-exempt prescription medicine sales, Senior Citizen / PWD discounts, and non-profit hospital grants.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Exemption Audit PDF</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#registerExemptionModal"><i class="ph ph-plus-circle me-1"></i> Register Exemption Rule</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Exemption Classes</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Classes</h4>
        <span class="fs-xs text-muted">Essential Meds, Sr Citizen, PWD &amp; Non-Profit</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">YTD Exempt Gross Amount</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
        <span class="fs-xs text-muted">Total Sales Qualifying for VAT Exemption</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Saved / Waived</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-piggy-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱582,000.00</h4>
        <span class="fs-xs text-muted">12% VAT Exemption Savings Pass-Through</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Audit Compliance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-square fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Valid</h4>
        <span class="fs-xs text-muted">Supported by BIR Certificate References</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Exemption Class, Republic Act, or Code...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Exemption Categories</option>
            <option value="meds">Essential Medicine (RA 11534 CREATE)</option>
            <option value="senior">Senior Citizen &amp; PWD Discounts (RA 9994)</option>
            <option value="nonprofit">Non-Profit Hospital Exemption</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Statuses</option>
            <option value="active">Active &amp; Enforced</option>
            <option value="expired">Expired / Under Review</option>
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
              <th>Exemption Class</th>
              <th>Legal Basis / Statutory Authority</th>
              <th>Certificate Ref</th>
              <th class="text-end">YTD Exempt Gross (₱)</th>
              <th class="text-end">Tax Saved (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">RA 11534 (CREATE Act - Essential Medicines)</div>
                <span class="fs-xs text-muted">Diabetes, Hypertension &amp; Oncology Drugs</span>
              </td>
              <td>BIR Revenue Regulation 04-2021</td>
              <td><span class="font-monospace text-primary">BIR-CERT-2026-EX01</span></td>
              <td class="text-end font-monospace fw-semibold">₱1,450,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱174,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Enforced</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Legal Basis"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">RA 9994 / RA 10754 (Senior Citizen &amp; PWD VAT Exemption)</div>
                <span class="fs-xs text-muted">Inpatient &amp; Outpatient Hospitalization</span>
              </td>
              <td>DOF-BIR Joint Circular 001-2017</td>
              <td><span class="font-monospace text-primary">BIR-CERT-2026-EX02</span></td>
              <td class="text-end font-monospace fw-semibold">₱3,400,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱408,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Enforced</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Legal Basis"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Register Tax Exemption Rule -->
<div class="modal fade" id="registerExemptionModal" tabindex="-1" aria-labelledby="registerExemptionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="registerExemptionModalLabel"><i class="ph ph-shield-check me-2 text-primary"></i>Register Statutory Tax Exemption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Exemption Rule registered!'); bootstrap.Modal.getInstance(document.getElementById('registerExemptionModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Exemption Class Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Non-Profit Hospital Income Exemption" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Legal Basis / Statutory Law <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. NIRC Section 30(E)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR Exemption Certificate Ref No. <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. BIR-CERT-2026-EX03" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Effective Validity Date</label>
              <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Exemption Details &amp; Accounting Treatment</label>
              <textarea class="form-control form-control-sm" rows="3" placeholder="Exempts hospital revenue generated from non-profit charitable ward beds under Section 30(E)..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Exemption Rule</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
