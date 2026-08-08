@extends('layouts.app')

@section('title', 'Tax Audit Trail - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-audit')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Audit Trail</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Audit Trail &amp; Immutable Log</h1>
      <p class="text-muted small mb-0">Immutably logged transaction audit records for internal tax compliance and external revenue authority reviews.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-shield-check me-1"></i> Verify Hash Integrity</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#exportAuditModal"><i class="ph ph-file-arrow-down me-1"></i> Export Tax Audit Log</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Logged Tax Audit Events</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-list-checks fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1,248 Logs</h4>
        <span class="fs-xs text-muted">Immutable system audit entries</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Verified Tax Impacts</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100.0%</h4>
        <span class="fs-xs text-muted">Zero unauthorized modifications</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tax Discrepancy Flags</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">0 Flags</h4>
        <span class="fs-xs text-muted">Clean BIR audit compliance</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">SHA-256 Hash Integrity</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-lock-key fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Encrypted</h4>
        <span class="fs-xs text-muted">Tamper-evident log chain</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Voucher ID, User, or Event...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Event Categories</option>
            <option value="form_2307">EWT 2307 Form Generation</option>
            <option value="tax_return">Statutory Return Filing</option>
            <option value="config_change">Tax Configuration Modification</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Officers / Users</option>
            <option value="tax_officer_1">tax_officer_1 (Anna Reyes)</option>
            <option value="cfo_user">cfo_user (Office of CFO)</option>
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
              <th>Audit Timestamp</th>
              <th>User / Officer</th>
              <th>Event Category</th>
              <th>Source Voucher / Form</th>
              <th class="text-end">Tax Impact (₱)</th>
              <th>Security Cryptographic Hash</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="text-nowrap">2026-08-08 14:22:10</span></td>
              <td>
                <div class="fw-semibold text-dark">tax_officer_1</div>
                <span class="fs-xs text-muted">IP: 192.168.1.45</span>
              </td>
              <td><span class="badge bg-info-subtle text-info">EWT 2307 Form Generation</span></td>
              <td><span class="font-monospace text-primary fw-bold">C2307-2026-881</span></td>
              <td class="text-end text-danger fw-bold font-monospace">-₱12,000.00</td>
              <td><span class="font-monospace fs-xs text-muted">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Payload Snapshot"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="text-nowrap">2026-08-07 10:14:00</span></td>
              <td>
                <div class="fw-semibold text-dark">cfo_user</div>
                <span class="fs-xs text-muted">IP: 192.168.1.10</span>
              </td>
              <td><span class="badge bg-success-subtle text-success">Statutory Return Filing</span></td>
              <td><span class="font-monospace text-primary fw-bold">FORM 2550Q</span></td>
              <td class="text-end text-danger fw-bold font-monospace">-₱215,000.00</td>
              <td><span class="font-monospace fs-xs text-muted">8f434346648f6b96df89dda901c5176b10a6d83961dd3c1ac88b59b2dc327aa4</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Payload Snapshot"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Export Tax Audit Log -->
<div class="modal fade" id="exportAuditModal" tabindex="-1" aria-labelledby="exportAuditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="exportAuditModalLabel"><i class="ph ph-file-arrow-down me-2 text-primary"></i>Export Signed Tax Audit Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Signed Tax Audit Log exported!'); bootstrap.Modal.getInstance(document.getElementById('exportAuditModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Audit Date Range</label>
            <select class="form-select form-select-sm">
              <option value="ytd">Year-To-Date FY 2026</option>
              <option value="q2">Q2 2026</option>
              <option value="all">Full Audit Chain</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Export Format</label>
            <select class="form-select form-select-sm">
              <option value="pdf">Auditor Signed PDF Package</option>
              <option value="csv">Encrypted CSV Audit Dump</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-download me-1"></i> Generate &amp; Download</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
