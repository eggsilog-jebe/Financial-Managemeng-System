@extends('layouts.app')

@section('title', 'Disbursement Approvals - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'disbursement-approval')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Disbursement Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Treasury Disbursement Approvals</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="location.reload()"><i class="ph ph-arrow-clockwise me-1"></i> Refresh Queue</button>
    </div>
  </div>

  <!-- Approval Limit Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Check Releases</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱98,400.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending EFT Batches</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱410,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Released Today</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱168,000.00</h4>
      </div>
    </div>
  </div>

  <!-- Approvals Queue Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="disbLevelSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Level:</label>
          <select id="disbLevelSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Authorization Levels</option>
            <option value="cfo">CFO Authorization Needed</option>
            <option value="controller">Controller Sign-off</option>
          </select>
        </div>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="disbSearchInput" class="form-control form-control-sm" placeholder="Search payee name or ref...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="disbApprovalsTable">
          <thead class="table-light">
            <tr>
              <th>Disbursement Ref</th>
              <th>Payee Name</th>
              <th>Payment Method</th>
              <th>Source Bank Account</th>
              <th class="text-end">Amount (₱)</th>
              <th>Authorization Level</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr id="row-DISB-APP-201" class="disb-row" data-level="cfo">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">DISB-APP-201</span></td>
              <td class="fw-semibold text-dark">Medical Staff Payroll Direct Batch</td>
              <td><span class="badge bg-info-subtle text-info">EFT Direct Deposit</span></td>
              <td>Metrobank Payroll #8841</td>
              <td class="text-end fw-bold text-dark font-monospace">₱410,000.00</td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-shield-star me-1"></i> CFO Authorization Needed</span></td>
              <td class="status-cell"><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Release</span></td>
              <td class="text-end action-cell">
                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                  <button class="btn btn-success" type="button" onclick="openDisbAuthorizeModal('DISB-APP-201', 'Medical Staff Payroll Direct Batch', '₱410,000.00', 'EFT Direct Deposit')"><i class="ph ph-check me-1"></i> Release Wire</button>
                  <button class="btn btn-outline-danger" type="button" onclick="openDisbRejectModal('DISB-APP-201', 'Medical Staff Payroll Direct Batch', '₱410,000.00')"><i class="ph ph-x me-1"></i> Reject</button>
                </div>
              </td>
            </tr>
            <tr id="row-DISB-APP-202" class="disb-row" data-level="controller">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">DISB-APP-202</span></td>
              <td class="fw-semibold text-dark">MedTech Diagnostics Inc</td>
              <td><span class="badge bg-primary-subtle text-primary">Physical Crossed Check</span></td>
              <td>Metrobank Operating #1020</td>
              <td class="text-end fw-bold text-dark font-monospace">₱98,400.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-shield-check me-1"></i> Controller Sign-off</span></td>
              <td class="status-cell"><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Release</span></td>
              <td class="text-end action-cell">
                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                  <button class="btn btn-success" type="button" onclick="openDisbAuthorizeModal('DISB-APP-202', 'MedTech Diagnostics Inc', '₱98,400.00', 'Physical Crossed Check')"><i class="ph ph-check me-1"></i> Authorize Check</button>
                  <button class="btn btn-outline-danger" type="button" onclick="openDisbRejectModal('DISB-APP-202', 'MedTech Diagnostics Inc', '₱98,400.00')"><i class="ph ph-x me-1"></i> Reject</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Authorize Disbursement -->
<div class="modal fade" id="disbAuthorizeModal" tabindex="-1" aria-labelledby="disbAuthorizeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold text-success" id="disbAuthorizeModalLabel"><i class="ph ph-check-circle me-2"></i>Authorize Treasury Disbursement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-success d-flex align-items-center py-2 mb-3 fs-xs">
          <i class="ph ph-shield-check fs-5 me-2"></i>
          <div>Authorizing will initiate bank transfer or unlock check printing.</div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Disbursement Reference</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace" id="disbAuthRef" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Payee &amp; Total Amount</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace text-success fw-bold" id="disbAuthDetails" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Treasury Release Key / Authorization PIN</label>
          <input type="password" class="form-control form-control-sm" id="disbAuthPin" placeholder="Enter CFO release key..." value="8841">
        </div>

        <!-- Audit Trail & Transparency Logs -->
        <div class="bg-light border rounded-3 p-3 mb-3">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Log</h6>
          <div class="d-flex flex-column gap-1 fs-xs text-muted">
            <div><strong class="text-dark">Role Authorized:</strong> Treasury CFO / Controller</div>
            <div><strong class="text-dark">Audit Log ID:</strong> LOG-DISB-AUTH-2026-901</div>
            <div><strong class="text-dark">System Timestamp:</strong> {{ date('Y-m-d H:i:s') }} PST</div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-sm btn-success" onclick="confirmDisbAuthorization()"><i class="ph ph-check me-1"></i> Release Funds</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reject Disbursement -->
<div class="modal fade" id="disbRejectModal" tabindex="-1" aria-labelledby="disbRejectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold text-danger" id="disbRejectModalLabel"><i class="ph ph-x-circle me-2"></i>Reject Disbursement Release</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-danger d-flex align-items-center py-2 mb-3 fs-xs">
          <i class="ph ph-warning-circle fs-5 me-2"></i>
          <div>Rejecting this disbursement will prevent wire execution or check printing.</div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Disbursement Reference</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace" id="disbRejectRef" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="disbRejectReasonSelect" required>
            <option value="Insufficient Liquidity in Bank Account">Insufficient Liquidity in Source Bank Account</option>
            <option value="Bank Account Detail Mismatch">Bank Account Detail Mismatch</option>
            <option value="Duplicate Wire Execution Risk">Duplicate Wire Execution Risk</option>
            <option value="Unauthorized Payee Account">Unauthorized Payee Account</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Audit Notes</label>
          <textarea class="form-control form-control-sm" id="disbRejectNotes" rows="2" placeholder="State reason for withholding disbursement..."></textarea>
        </div>

        <!-- Audit Trail & Transparency Logs -->
        <div class="bg-light border rounded-3 p-3 mb-3">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-shield-warning me-1 text-danger"></i> Audit Trail &amp; Rejection Log</h6>
          <div class="d-flex flex-column gap-1 fs-xs text-muted">
            <div><strong class="text-dark">Audit Log ID:</strong> LOG-DISB-REJ-2026-902</div>
            <div><strong class="text-dark">System Timestamp:</strong> {{ date('Y-m-d H:i:s') }} PST</div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-sm btn-danger" onclick="confirmDisbRejection()"><i class="ph ph-x me-1"></i> Confirm Rejection</button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  let currentDisbTargetId = '';

  function openDisbAuthorizeModal(id, payee, amount, method) {
    currentDisbTargetId = id;
    document.getElementById('disbAuthRef').value = id + ' (' + method + ')';
    document.getElementById('disbAuthDetails').value = payee + ' - ' + amount;
    const modal = new bootstrap.Modal(document.getElementById('disbAuthorizeModal'));
    modal.show();
  }

  function confirmDisbAuthorization() {
    if (!currentDisbTargetId) return;
    const row = document.getElementById('row-' + currentDisbTargetId);
    if (row) {
      row.querySelector('.status-cell').innerHTML = '<span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Funds Released</span>';
      row.querySelector('.action-cell').innerHTML = '<div class="d-inline-flex align-items-center justify-content-end"><span class="badge bg-success-subtle text-success px-2 py-1"><i class="ph ph-check-circle me-1"></i> Released</span></div>';
    }
    const modalEl = document.getElementById('disbAuthorizeModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    if (window.HimsComponents && window.HimsComponents.notify) {
      window.HimsComponents.notify({ tone: 'success', title: 'Disbursement Released', message: 'Disbursement ' + currentDisbTargetId + ' funds released successfully.' });
    } else {
      alert('Disbursement ' + currentDisbTargetId + ' funds released successfully!');
    }
  }

  function openDisbRejectModal(id, payee, amount) {
    currentDisbTargetId = id;
    document.getElementById('disbRejectRef').value = id + ' - ' + payee;
    const modal = new bootstrap.Modal(document.getElementById('disbRejectModal'));
    modal.show();
  }

  function confirmDisbRejection() {
    if (!currentDisbTargetId) return;
    const reason = document.getElementById('disbRejectReasonSelect').value;
    const row = document.getElementById('row-' + currentDisbTargetId);
    if (row) {
      row.querySelector('.status-cell').innerHTML = '<span class="badge bg-danger-subtle text-danger" title="' + reason + '"><i class="ph ph-x-circle me-1"></i> Release Rejected</span>';
      row.querySelector('.action-cell').innerHTML = '<div class="d-inline-flex align-items-center justify-content-end"><span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="ph ph-prohibited me-1"></i> Rejected</span></div>';
    }
    const modalEl = document.getElementById('disbRejectModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    if (window.HimsComponents && window.HimsComponents.notify) {
      window.HimsComponents.notify({ tone: 'danger', title: 'Disbursement Rejected', message: 'Disbursement ' + currentDisbTargetId + ' rejected: ' + reason });
    } else {
      alert('Disbursement ' + currentDisbTargetId + ' rejected: ' + reason);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    const levelSelect = document.getElementById('disbLevelSelect');
    const searchInput = document.getElementById('disbSearchInput');

    function filterDisbursements() {
      const selectedLevel = levelSelect ? levelSelect.value.toLowerCase() : '';
      const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
      const rows = document.querySelectorAll('.disb-row');
      let visibleCount = 0;

      rows.forEach(function(row) {
        const rowLevel = row.getAttribute('data-level') || '';
        const rowText = row.textContent.toLowerCase();

        const matchLevel = !selectedLevel || rowLevel.includes(selectedLevel);
        const matchSearch = !searchQuery || rowText.includes(searchQuery);

        if (matchLevel && matchSearch) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });
    }

    if (levelSelect) levelSelect.addEventListener('change', filterDisbursements);
    if (searchInput) {
      searchInput.addEventListener('input', filterDisbursements);
      searchInput.addEventListener('keyup', filterDisbursements);
    }
  });
</script>
@endpush
@endsection
