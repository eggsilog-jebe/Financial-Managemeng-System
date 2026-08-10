@extends('layouts.app')

@section('title', 'Period-End Closing - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'period-end-closing')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Period-End Closing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Period-End Financial Closing &amp; GL Locking</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Running pre-closing audit scan...');"><i class="ph ph-list-checks me-1"></i> Pre-Closing Audit</button>
      <button id="btnClosePeriod" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#closePeriodModal"><i class="ph ph-lock-key me-1"></i> Execute Period Close</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Active Period</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-calendar-blank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="activePeriodText">August 2026</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Closing Tasks</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="pendingTasksCount">1 Task Left</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Closed Fiscal Periods</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-lock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="closedPeriodsCount">7 Months</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">GL Lock Integrity</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="lockStatusText">Secure</h4>
      </div>
    </div>
  </div>

  <!-- Closing Checklist Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between">
      <h6 class="fw-bold mb-0">Month-End Closing Audit Checklist (July 2026 Period)</h6>
      <span class="badge bg-primary-subtle text-primary" id="checklistBadge">2 of 3 Verified</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Checklist Procedure</th>
              <th>Responsible Officer</th>
              <th>Verification Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">1. Accounts Payable &amp; Vendor Bill Closure</div>
                <span class="fs-xs text-muted">Confirm all July vendor invoices are approved &amp; posted</span>
              </td>
              <td>AP Lead Accountant</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Completed &amp; Verified</span></td>
              <td class="text-end"><button class="btn btn-sm btn-light border p-1" title="View Verification Log"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr id="taskRow2">
              <td>
                <div class="fw-bold text-dark">2. Bank Reconciliation &amp; Cash Match</div>
                <span class="fs-xs text-muted">Reconcile Metrobank &amp; BDO bank statements</span>
              </td>
              <td>Treasury Accountant</td>
              <td id="taskStatusCell2"><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Final Match</span></td>
              <td class="text-end" id="taskActionCell2">
                <button class="btn btn-sm btn-outline-primary py-1 px-3 fs-xs" onclick="verifyTask2()"><i class="ph ph-check me-1"></i> Verify &amp; Complete</button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">3. Trial Balance Debit/Credit Verification</div>
                <span class="fs-xs text-muted">Ensure total debits equal total credits with zero variance</span>
              </td>
              <td>Senior GL Controller</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Completed &amp; Verified</span></td>
              <td class="text-end"><button class="btn btn-sm btn-light border p-1" title="View Verification Log"><i class="ph ph-eye"></i></button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Execute Period Close -->
<div class="modal fade" id="closePeriodModal" tabindex="-1" aria-labelledby="closePeriodModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="closePeriodModalLabel"><i class="ph ph-lock-key me-2 text-danger"></i>Execute Fiscal Period Close</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="closePeriodForm">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Select Fiscal Period to Close <span class="text-danger">*</span></label>
            <select id="modalClosePeriod" class="form-select form-select-sm" required>
              <option value="July 2026">July 2026 (Month-End Close)</option>
              <option value="Q2 2026">Q2 2026 (Quarter-End Close)</option>
            </select>
          </div>
          <div class="alert alert-warning fs-xs mb-3">
            <i class="ph ph-warning me-1"></i>
            <strong>Warning:</strong> Closing this period will permanently lock all General Ledger journal postings for the selected timeframe.
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-danger"><i class="ph ph-lock me-1"></i> Confirm &amp; Lock Period</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function verifyTask2() {
  const statusCell = document.getElementById('taskStatusCell2');
  const actionCell = document.getElementById('taskActionCell2');
  const pendingCard = document.getElementById('pendingTasksCount');
  const checklistBadge = document.getElementById('checklistBadge');

  if (statusCell) {
    statusCell.innerHTML = '<span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Completed & Verified</span>';
  }
  if (actionCell) {
    actionCell.innerHTML = '<button class="btn btn-sm btn-light border p-1" title="View Verification Log"><i class="ph ph-eye"></i></button>';
  }
  if (pendingCard) {
    pendingCard.textContent = '0 Tasks (Ready)';
    pendingCard.className = 'fw-bold mb-0 text-success';
  }
  if (checklistBadge) {
    checklistBadge.textContent = '3 of 3 Verified';
    checklistBadge.className = 'badge bg-success-subtle text-success';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const btnClosePeriod = document.getElementById('btnClosePeriod');
  if (btnClosePeriod) {
    btnClosePeriod.addEventListener('click', function() {
      const modalEl = document.getElementById('closePeriodModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  const closePeriodForm = document.getElementById('closePeriodForm');
  if (closePeriodForm) {
    closePeriodForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const selectedPeriod = document.getElementById('modalClosePeriod').value;
      const closedCountCard = document.getElementById('closedPeriodsCount');

      if (closedCountCard) {
        closedCountCard.textContent = '8 Months';
      }

      alert('Fiscal Period (' + selectedPeriod + ') has been successfully closed and locked!');

      const modalEl = document.getElementById('closePeriodModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      closePeriodForm.reset();
    });
  }
});
</script>
@endpush
