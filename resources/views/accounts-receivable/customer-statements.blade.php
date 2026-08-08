@extends('layouts.app')

@section('title', 'Customer Statements - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'statements')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Customer Statements</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Account (SOA) Generator</h1>
      <p class="text-muted small mb-0">Compiled monthly Statements of Account generated for corporate HMO guarantors, corporate sponsors, and private patients.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-envelope me-1"></i> Email All Statements</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#generateStatementModal"><i class="ph ph-plus-circle me-1"></i> Generate Statement</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active SOAs Generated</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">18 Statements</h4>
        <span class="fs-xs text-muted">Monthly Payor Billing Packs</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Outstanding AR</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱3,070,200.00</h4>
        <span class="fs-xs text-muted">Uncollected Patient &amp; HMO Pool</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Corporate Guarantors</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">5 HMO Companies</h4>
        <span class="fs-xs text-muted">Maxicare, Intellicare, Medicard, Philam, Insular</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Average Statement Age</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">28 Days</h4>
        <span class="fs-xs text-muted">Within Standard 45-Day Collection Terms</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Payor Name, SOA Ref, or TIN...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Account Types</option>
            <option value="hmo">HMO Guarantor</option>
            <option value="patient">Private Patient</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Statement Periods</option>
            <option value="jul_2026">July 2026 Statement</option>
            <option value="jun_2026">June 2026 Statement</option>
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
              <th>Statement Ref</th>
              <th>Payor Name</th>
              <th>Account Type</th>
              <th>Statement Date</th>
              <th class="text-end">Unpaid Balance (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">SOA-2026-0701</span></td>
              <td>
                <div class="fw-bold text-dark">Maxicare Healthcare Corp</div>
                <span class="fs-xs text-muted">Contract Ref: HMO-MAX-2026</span>
              </td>
              <td><span class="badge bg-info-subtle text-info">HMO Corporate</span></td>
              <td>2026-08-01</td>
              <td class="text-end text-danger fw-bold font-monospace">₱1,220,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Sent - Awaiting Payment</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Download Official SOA PDF"><i class="ph ph-file-pdf"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Send Email Reminder"><i class="ph ph-paper-plane-tilt"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Generate Account Statement -->
<div class="modal fade" id="generateStatementModal" tabindex="-1" aria-labelledby="generateStatementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="generateStatementModalLabel"><i class="ph ph-file-plus me-2 text-primary"></i>Generate Statement of Account (SOA)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Statement of Account generated!'); bootstrap.Modal.getInstance(document.getElementById('generateStatementModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Select Payor / HMO Corporate Account <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="maxicare">Maxicare Healthcare Corp</option>
              <option value="intellicare">Intellicare / Asuris</option>
              <option value="medicard">Medicard Philippines</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Statement Period <span class="text-danger">*</span></label>
            <input type="month" class="form-control form-control-sm" value="2026-07" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-file-pdf me-1"></i> Generate SOA PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
