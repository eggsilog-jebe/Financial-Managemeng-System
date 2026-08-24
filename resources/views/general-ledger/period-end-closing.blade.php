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
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="procedureStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Verification Status:</label>
          <select id="procedureStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Statuses</option>
            <option value="completed">Completed &amp; Verified</option>
            <option value="pending">Pending Match</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="procedureOfficerSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Officer Role:</label>
          <select id="procedureOfficerSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Roles</option>
            <option value="ap">AP Lead Accountant</option>
            <option value="treasury">Treasury Accountant</option>
            <option value="gl">Senior GL Controller</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="procedureSearchInput" class="form-control form-control-sm" placeholder="Search procedure name, officer...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="closingChecklistTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Checklist Procedure</th>
              <th>Responsible Officer</th>
              <th>Verification Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $procedures = [
                [
                  'title' => '1. Accounts Payable & Vendor Bill Closure',
                  'desc' => 'Confirm all July vendor invoices are approved & posted',
                  'officer' => 'AP Lead Accountant',
                  'role' => 'ap',
                  'status' => 'Completed & Verified',
                  'status_badge' => 'bg-success-subtle text-success'
                ],
                [
                  'title' => '2. Bank Reconciliation & Cash Match',
                  'desc' => 'Reconcile Metrobank & BDO bank statements',
                  'officer' => 'Treasury Accountant',
                  'role' => 'treasury',
                  'status' => 'Pending Match',
                  'status_badge' => 'bg-warning-subtle text-warning'
                ],
                [
                  'title' => '3. Trial Balance Debit/Credit Verification',
                  'desc' => 'Ensure total debits equal total credits with zero variance',
                  'officer' => 'Senior GL Controller',
                  'role' => 'gl',
                  'status' => 'Completed & Verified',
                  'status_badge' => 'bg-success-subtle text-success'
                ],
              ];
            @endphp

            @foreach($procedures as $proc)
            <tr class="procedure-row" style="cursor: pointer;" data-role="{{ $proc['role'] }}" data-status="{{ strtolower($proc['status']) }}" onclick="openProcedureDetailsModal({{ json_encode($proc) }})">
              <td><div class="fw-bold text-dark">{{ $proc['title'] }}</div></td>
              <td class="fs-xs text-muted">{{ $proc['officer'] }}</td>
              <td><span class="badge {{ $proc['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $proc['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                @if($proc['status'] === 'Pending Match')
                  <button class="btn btn-sm btn-outline-primary py-1 px-3 fs-xs me-1" onclick="verifyTask2()"><i class="ph ph-check me-1"></i> Verify &amp; Complete</button>
                @endif
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Verification Log" onclick="openProcedureDetailsModal({{ json_encode($proc) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="procedureSummaryText">Showing {{ count($procedures) }} Month-End Procedures</span>
      <nav aria-label="Procedure Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Procedure Details (Executive Design) -->
<div class="modal fade" id="procedureDetailsModal" tabindex="-1" aria-labelledby="procedureDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">MONTH-END CLOSE</span>
            <span class="badge bg-success-subtle text-success" id="detailProcStatus"><i class="ph ph-check-circle me-1"></i> Completed &amp; Verified</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailProcTitle">1. Accounts Payable &amp; Vendor Bill Closure</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-list-checks me-1 text-primary"></i> Procedure Scope &amp; Officer Responsibility</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Responsible Officer Title</span>
              <span class="font-monospace fw-bold text-dark" id="detailProcOfficer">AP Lead Accountant</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Procedure Description</span>
              <span class="text-muted" id="detailProcDesc">Confirm all July vendor invoices are approved &amp; posted</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Sign-Off Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">GL Closing Authorization:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Pre-Closing Audit Verified</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-CLOSE-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Procedure Verification Sign-off...');"><i class="ph ph-file-text me-1"></i> Export Sign-Off Brief</button>
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
function openProcedureDetailsModal(proc) {
  if (!proc) return;

  document.getElementById('detailProcTitle').textContent = proc.title || 'Procedure Title';
  document.getElementById('detailProcDesc').textContent = proc.desc || '-';
  document.getElementById('detailProcOfficer').textContent = proc.officer || '-';

  const statusEl = document.getElementById('detailProcStatus');
  if (statusEl) {
    statusEl.textContent = proc.status;
    statusEl.className = 'badge ' + (proc.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('procedureDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

function verifyTask2() {
  const pendingCard = document.getElementById('pendingTasksCount');

  if (pendingCard) {
    pendingCard.textContent = '0 Tasks (Ready)';
    pendingCard.className = 'fw-bold mb-0 text-success';
  }

  alert('Procedure "2. Bank Reconciliation & Cash Match" marked as Verified!');
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('procedureSearchInput');
  const statusSelect = document.getElementById('procedureStatusSelect');
  const officerSelect = document.getElementById('procedureOfficerSelect');
  const summaryText = document.getElementById('procedureSummaryText');
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

  function filterProcedures() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const selectedRole = officerSelect ? officerSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.procedure-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowRole = row.getAttribute('data-role') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchRole = !selectedRole || rowRole.includes(selectedRole);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchRole && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Month-End Procedure${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noProcedureRow');
    const tbody = document.querySelector('#closingChecklistTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noProcedureRow';
        emptyRow.innerHTML = `<td colspan="4" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No procedures found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterProcedures);
    searchInput.addEventListener('keyup', filterProcedures);
  }
  if (statusSelect) statusSelect.addEventListener('change', filterProcedures);
  if (officerSelect) officerSelect.addEventListener('change', filterProcedures);

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

  filterProcedures();
});
</script>
@endpush
