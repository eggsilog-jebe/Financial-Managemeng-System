@extends('layouts.app')

@section('title', 'Journal Entries - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'journal-entries')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Journal Entries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Double-Entry Journal Entries</h1>
      <p class="text-muted small mb-0">Record, post, and audit day-to-day double-entry general ledger accounting transactions.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Export Journal Log</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newJournalModal"><i class="ph ph-plus-circle me-1"></i> New Journal Entry</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Debit Volume (Month)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,850,000.00</h4>
        <span class="fs-xs text-muted">Total Debit Journal Transactions</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Volume (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,850,000.00</h4>
        <span class="fs-xs text-muted">100% Balanced Double-Entry System</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Posted Journal Entries</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">142 Entries</h4>
        <span class="fs-xs text-muted">Committed to General Ledger Books</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unposted Draft Entries</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2 Drafts</h4>
        <span class="fs-xs text-muted">Awaiting Senior Accountant Review</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Entry Ref, Description, or Account...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Posting Statuses</option>
            <option value="posted">Posted to Ledger</option>
            <option value="draft">Draft Entry</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Journal Types</option>
            <option value="general">General Journal</option>
            <option value="adjusting">Adjusting Entry</option>
            <option value="closing">Closing Entry</option>
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
              <th>Entry Ref</th>
              <th>Date</th>
              <th>Journal Description</th>
              <th class="text-end">Debit Amount (₱)</th>
              <th class="text-end">Credit Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">JE-2026-0041</span></td>
              <td>2026-08-07</td>
              <td>
                <div class="fw-semibold text-dark">Pharmacy Inventory Bulk Replenishment Payout</div>
                <span class="fs-xs text-muted">Debit: 1300 Pharmacy Inv | Credit: 1010 Operating Cash</span>
              </td>
              <td class="text-end text-success fw-bold font-monospace">₱120,000.00</td>
              <td class="text-end text-danger fw-bold font-monospace">₱120,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Posted</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Double-Entry Voucher"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Print Voucher"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">JE-2026-0040</span></td>
              <td>2026-08-06</td>
              <td>
                <div class="fw-semibold text-dark">MRI Scanner Depreciation Expense (Monthly)</div>
                <span class="fs-xs text-muted">Debit: 5200 Depr Expense | Credit: 1590 Acc Depr</span>
              </td>
              <td class="text-end text-success fw-bold font-monospace">₱125,000.00</td>
              <td class="text-end text-danger fw-bold font-monospace">₱125,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Posted</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Double-Entry Voucher"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Print Voucher"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Double-Entry Journal Modal -->
<div class="modal fade" id="newJournalModal" tabindex="-1" aria-labelledby="newJournalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newJournalModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>New Double-Entry Journal Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Journal Entry posted to General Ledger!'); bootstrap.Modal.getInstance(document.getElementById('newJournalModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Entry Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Journal Type <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="general">General Operating Journal</option>
                <option value="adjusting">Month-End Adjusting Entry</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Debit Account <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="1300">1300 - Pharmacy Inventory Asset</option>
                <option value="5100">5100 - Doctor Salaries Expense</option>
                <option value="5200">5200 - Medical Equipment Depreciation</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Account <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="1010">1010 - Metrobank Operating Cash</option>
                <option value="2100">2100 - Accounts Payable Liability</option>
                <option value="1590">1590 - Accumulated Depreciation</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transaction Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Document / Reference</label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Inv #99201 or Depr Schedule">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Journal Description / Particulars <span class="text-danger">*</span></label>
              <textarea class="form-control form-control-sm" rows="2" placeholder="Record purchase of emergency room antibiotics..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Double-Entry</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
