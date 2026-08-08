@extends('layouts.app')

@section('title', 'Payment Receipts - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'receipts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Receipts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Receipts &amp; Official Receipts</h1>
      <p class="text-muted small mb-0">Issue, audit, and track Official Receipts (OR) generated for patient, HMO, and corporate collections.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-download-simple me-1"></i> Export Receipts Log</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueReceiptModal"><i class="ph ph-plus-circle me-1"></i> Issue Official Receipt</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Receipts Issued Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">48 Receipts</h4>
        <span class="fs-xs text-muted">Sum Total: <strong class="text-success">₱184,500.00</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cash Collections</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱92,400.00</h4>
        <span class="fs-xs text-muted">50.1% of Total Daily Collections</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Non-Cash (Card / E-Wallet)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱92,100.00</h4>
        <span class="fs-xs text-muted">Checks, Visa/MC &amp; GCash</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Voided / Cancelled Receipts</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-prohibited fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 OR</h4>
        <span class="fs-xs text-muted">Audit Value: ₱3,200.00 (Reversed)</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search OR Number, Patient Name, or Bill Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Payment Modes</option>
            <option value="cash">Cash Payment</option>
            <option value="card">Credit / Debit Card</option>
            <option value="check">Bank Check</option>
            <option value="gcash">GCash / PayMaya</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Receipt Statuses</option>
            <option value="valid">Valid / Cleared</option>
            <option value="voided">Voided</option>
            <option value="refunded">Refunded</option>
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
              <th>OR Number</th>
              <th>Date &amp; Time</th>
              <th>Payor / Patient Name</th>
              <th>Payment Mode</th>
              <th>Reference / Check No.</th>
              <th class="text-end">Amount Paid (₱)</th>
              <th>Issued By</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">OR-2026-9901</span></td>
              <td><span class="text-nowrap">2026-08-08 14:22</span></td>
              <td>
                <div class="fw-semibold text-dark">David Miller</div>
                <span class="fs-xs text-muted">Patient ID: PAT-88412</span>
              </td>
              <td><span class="badge bg-info-subtle text-info"><i class="ph ph-credit-card me-1"></i> Credit Card (Visa)</span></td>
              <td><span class="font-monospace text-muted">TXN-774102</span></td>
              <td class="text-end text-success fw-bold">₱6,400.00</td>
              <td>Anna Reyes (Main POS)</td>
              <td><span class="badge bg-success-subtle text-success">Valid</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print OR"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">OR-2026-9900</span></td>
              <td><span class="text-nowrap">2026-08-08 13:45</span></td>
              <td>
                <div class="fw-semibold text-dark">Maria Clara Santos</div>
                <span class="fs-xs text-muted">Patient ID: PAT-99201</span>
              </td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-money me-1"></i> Cash</span></td>
              <td><span class="font-monospace text-muted">-</span></td>
              <td class="text-end text-success fw-bold">₱12,500.00</td>
              <td>Anna Reyes (Main POS)</td>
              <td><span class="badge bg-success-subtle text-success">Valid</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print OR"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">OR-2026-9899</span></td>
              <td><span class="text-nowrap">2026-08-08 11:10</span></td>
              <td>
                <div class="fw-semibold text-dark">Maxicare Healthcare Corp</div>
                <span class="fs-xs text-muted">HMO Billing Ref: HMO-2026-04</span>
              </td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-bank me-1"></i> Check</span></td>
              <td><span class="font-monospace text-muted">BDO-CHK-44910</span></td>
              <td class="text-end text-success fw-bold">₱65,000.00</td>
              <td>Carlos Vance (Billing Desk)</td>
              <td><span class="badge bg-success-subtle text-success">Valid</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print OR"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">OR-2026-9898</span></td>
              <td><span class="text-nowrap">2026-08-08 09:30</span></td>
              <td>
                <div class="fw-semibold text-dark">Robert Chen</div>
                <span class="fs-xs text-muted">Patient ID: PAT-77312</span>
              </td>
              <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-prohibited me-1"></i> Cash (Voided)</span></td>
              <td><span class="font-monospace text-muted">-</span></td>
              <td class="text-end text-muted text-decoration-line-through">₱3,200.00</td>
              <td>Anna Reyes (Main POS)</td>
              <td><span class="badge bg-danger-subtle text-danger">Voided</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Audit"><i class="ph ph-file-search"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Official Receipt -->
<div class="modal fade" id="issueReceiptModal" tabindex="-1" aria-labelledby="issueReceiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueReceiptModalLabel"><i class="ph ph-receipt me-2 text-primary"></i>Issue Official Receipt (OR)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="receiptForm" onsubmit="event.preventDefault(); alert('Official Receipt issued successfully!'); bootstrap.Modal.getInstance(document.getElementById('issueReceiptModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient / Payor Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Juan De La Cruz" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient ID / Billing Reference</label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. PAT-99201 or INV-2026-44">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Mode <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit / Debit Card</option>
                <option value="check">Bank Check</option>
                <option value="gcash">GCash / PayMaya</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Reference / Authorization / Check #</label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Card Approval / Check No.">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Amount to Pay (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tendered Cash Amount (₱)</label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Payment Notes / Revenue Breakdown</label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="Outpatient Consultation fee, Lab tests, Inpatient partial deposit..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-printer me-1"></i> Print &amp; Post OR</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
