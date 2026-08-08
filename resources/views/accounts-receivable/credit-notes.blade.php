@extends('layouts.app')

@section('title', 'Credit Notes - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Credit Notes</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AR Credit Notes &amp; Billing Adjustments</h1>
      <p class="text-muted small mb-0">Issue credit memos for patient bill discounts, HMO price write-downs, and billing error adjustments.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Credit Log PDF</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueCreditNoteModal"><i class="ph ph-plus-circle me-1"></i> Issue Credit Note</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Credit Notes (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-note-pencil fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Credit Memos</h4>
        <span class="fs-xs text-muted">HMO &amp; Patient Price Adjustments</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Value</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱84,500.00</h4>
        <span class="fs-xs text-muted">Applied against AR balances</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Contract Disallowance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱62,000.00</h4>
        <span class="fs-xs text-muted">Approved HMO fee reductions</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Manager Approvals</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Pending</h4>
        <span class="fs-xs text-muted">Courtesy discount review</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search CN Ref, Patient Name, or Invoice Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Reason Categories</option>
            <option value="hmo_contract">HMO Contractual Rate Adjustment</option>
            <option value="courtesy">Senior / Courtesy Discount</option>
            <option value="error">Billing Correction</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Statuses</option>
            <option value="approved">Approved &amp; Applied</option>
            <option value="pending">Pending Approval</option>
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
              <th>Credit Note Ref</th>
              <th>Date</th>
              <th>Patient / Payor Name</th>
              <th>Target Invoice Ref</th>
              <th class="text-end">Credit Amount (₱)</th>
              <th>Reason</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">CN-2026-041</span></td>
              <td>2026-08-07</td>
              <td>
                <div class="fw-semibold text-dark">Maxicare Healthcare Corp</div>
                <span class="fs-xs text-muted">HMO Agreed Tariff Discount</span>
              </td>
              <td><span class="font-monospace text-muted">INV-2026-0881</span></td>
              <td class="text-end text-danger fw-bold font-monospace">₱12,500.00</td>
              <td>HMO Contractual Rate Reduction</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Applied</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print CN PDF"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Credit Note -->
<div class="modal fade" id="issueCreditNoteModal" tabindex="-1" aria-labelledby="issueCreditNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueCreditNoteModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue AR Credit Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Credit Note issued!'); bootstrap.Modal.getInstance(document.getElementById('issueCreditNoteModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient / Payor Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Maxicare or Juan De La Cruz" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Original Invoice Reference <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. INV-2026-0881" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Adjustment Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace text-danger fw-bold" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Adjustment Category</label>
              <select class="form-select form-select-sm">
                <option value="hmo">HMO Contract Rate Disallowance</option>
                <option value="discount">Senior / PWD Courtesy Discount</option>
                <option value="error">Billing Charge Correction</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Audit Justification Notes <span class="text-danger">*</span></label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="State reason for credit memo..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Issue &amp; Apply Credit Note</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
