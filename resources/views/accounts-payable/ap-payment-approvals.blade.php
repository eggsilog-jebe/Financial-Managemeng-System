@extends('layouts.app')

@section('title', 'AP Payment Approvals - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'ap-approvals')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payment Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AP Payment Approvals &amp; Authorizations</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="location.reload()"><i class="ph ph-arrow-clockwise me-1"></i> Refresh Queue</button>
    </div>
  </div>

  <!-- Approval Tiers Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tier 1: Dept Head (&lt; ₱50k)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-user-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="tier1Count">{{ ($approvals ?? collect())->where('tier', 'tier 1')->count() }} Pending</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tier 2: Finance Officer (&lt; ₱250k)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="tier2Count">{{ ($approvals ?? collect())->where('tier', 'tier 2')->count() }} Pending</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tier 3: CFO Final Release (&gt; ₱250k)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-star fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="tier3Count">{{ ($approvals ?? collect())->where('tier', 'tier 3')->count() }} Pending</h4>
      </div>
    </div>
  </div>

  <!-- Approvals Queue Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Tier Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="approvalTierSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Approval Tier:</label>
          <select id="approvalTierSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Tiers</option>
            <option value="tier 1">Tier 1: Dept Head</option>
            <option value="tier 2">Tier 2: Finance Officer</option>
            <option value="tier 3">Tier 3: CFO Release</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="approvalSearchInput" class="form-control form-control-sm" placeholder="Search ID, payee, voucher ref...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="apApprovalsTable">
          <thead class="table-light">
            <tr>
              <th>Approval ID</th>
              <th>Vendor / Payee</th>
              <th>Voucher Ref</th>
              <th>Department Origin</th>
              <th class="text-end">Voucher Amount (₱)</th>
              <th>Approval Tier Required</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($approvals ?? [] as $app)
            @php
              $id = is_array($app) ? $app['id'] : ($app->approval_code ?? 'APP-'.$app->id);
              $vendor = is_array($app) ? $app['vendor'] : ($app->vendor->name ?? 'Vendor');
              $voucher = is_array($app) ? $app['voucher'] : ($app->voucher_reference ?? 'N/A');
              $dept = is_array($app) ? $app['dept'] : ($app->department ?? 'General');
              $amt = is_array($app) ? $app['amount'] : ('₱' . number_format($app->amount ?? 0, 2));
              $tier = is_array($app) ? $app['tier'] : ($app->tier ?? 'tier 1');
              $tierLabel = is_array($app) ? $app['tier_label'] : ($app->tier_label ?? 'Tier 1: Dept Head');
              $status = is_array($app) ? $app['status'] : ($app->status ?? 'Pending Authorization');
            @endphp
            <tr id="row-{{ $id }}" class="approval-row" data-tier="{{ strtolower($tier) }}">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $id }}</span></td>
              <td class="fw-semibold text-dark">{{ $vendor }}</td>
              <td><span class="font-monospace text-primary">{{ $voucher }}</span></td>
              <td>{{ $dept }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $amt }}</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-shield-check me-1"></i> {{ $tierLabel }}</span></td>
              <td class="status-cell"><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> {{ $status }}</span></td>
              <td class="text-end action-cell">
                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                  <button class="btn btn-success" type="button" onclick="openAuthorizeModal('{{ $id }}', '{{ $vendor }}', '{{ $amt }}', '{{ $voucher }}')"><i class="ph ph-check"></i> Authorize</button>
                  <button class="btn btn-outline-danger" type="button" onclick="openRejectModal('{{ $id }}', '{{ $vendor }}', '{{ $amt }}')"><i class="ph ph-x"></i> Reject</button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No pending payment approvals.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Authorize AP Payment -->
<div class="modal fade" id="authorizeModal" tabindex="-1" aria-labelledby="authorizeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold text-success" id="authorizeModalLabel"><i class="ph ph-check-circle me-2"></i>Authorize AP Payment Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-success d-flex align-items-center py-2 mb-3 fs-xs">
          <i class="ph ph-shield-check fs-5 me-2"></i>
          <div>Confirming authorization will approve fund release for this AP voucher.</div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Approval Reference</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace" id="authApprovalId" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Payee Vendor &amp; Amount</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace text-success fw-bold" id="authPayeeDetails" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Authorizer Authorization Passcode / Notes</label>
          <input type="password" class="form-control form-control-sm" id="authPasscode" placeholder="Enter security passcode or PIN..." value="1234">
        </div>

        <!-- Audit Trail & Transparency Logs -->
        <div class="bg-light border rounded-3 p-3 mb-3">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Segregation of Duties</h6>
          <div class="d-flex flex-column gap-1 fs-xs text-muted">
            <div><strong class="text-dark">Role Authorized:</strong> Dual-Control Authorized Approver</div>
            <div><strong class="text-dark">Audit Log ID:</strong> LOG-AUTH-2026-88012</div>
            <div><strong class="text-dark">System Timestamp:</strong> {{ date('Y-m-d H:i:s') }} PST</div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-sm btn-success" onclick="confirmAuthorization()"><i class="ph ph-check me-1"></i> Confirm &amp; Authorize</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reject AP Payment -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold text-danger" id="rejectModalLabel"><i class="ph ph-x-circle me-2"></i>Reject AP Payment Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-danger d-flex align-items-center py-2 mb-3 fs-xs">
          <i class="ph ph-warning-circle fs-5 me-2"></i>
          <div>Rejecting this voucher will halt disbursement and return it to AP for revision.</div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Approval Reference</label>
          <input type="text" class="form-control form-control-sm bg-light font-monospace" id="rejectApprovalId" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Mandatory Rejection Reason <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="rejectReasonSelect" required>
            <option value="Incomplete Supporting Documents">Incomplete Supporting Documents / Invoices</option>
            <option value="Price Variance Exceeds Purchase Order">Price Variance Exceeds Purchase Order</option>
            <option value="Duplicate Invoice Submission">Duplicate Invoice Submission</option>
            <option value="Budget Quota Exceeded">Budget Quota Exceeded</option>
            <option value="Other Audit Discrepancy">Other Audit Discrepancy</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Detailed Rejection Notes</label>
          <textarea class="form-control form-control-sm" id="rejectNotes" rows="2" placeholder="Explain rejection reason for the AP team..."></textarea>
        </div>

        <!-- Audit Trail & Transparency Logs -->
        <div class="bg-light border rounded-3 p-3 mb-3">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-shield-warning me-1 text-danger"></i> Audit Trail &amp; Rejection Log</h6>
          <div class="d-flex flex-column gap-1 fs-xs text-muted">
            <div><strong class="text-dark">Audit Log ID:</strong> LOG-REJ-2026-99041</div>
            <div><strong class="text-dark">System Timestamp:</strong> {{ date('Y-m-d H:i:s') }} PST</div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-sm btn-danger" onclick="confirmRejection()"><i class="ph ph-x me-1"></i> Confirm Rejection</button>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  let currentTargetId = '';

  function openAuthorizeModal(id, payee, amount, voucherRef) {
    currentTargetId = id;
    document.getElementById('authApprovalId').value = id + ' (' + voucherRef + ')';
    document.getElementById('authPayeeDetails').value = payee + ' - ' + amount;
    const modal = new bootstrap.Modal(document.getElementById('authorizeModal'));
    modal.show();
  }

  function confirmAuthorization() {
    if (!currentTargetId) return;
    const row = document.getElementById('row-' + currentTargetId);
    if (row) {
      row.querySelector('.status-cell').innerHTML = '<span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Authorized</span>';
      row.querySelector('.action-cell').innerHTML = '<div class="d-inline-flex align-items-center justify-content-end"><span class="badge bg-success-subtle text-success px-2 py-1"><i class="ph ph-shield-check me-1"></i> Approved</span></div>';
    }
    const modalEl = document.getElementById('authorizeModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    
    if (window.HimsComponents && window.HimsComponents.notify) {
      window.HimsComponents.notify({ tone: 'success', title: 'Payment Authorized', message: 'AP Payment Voucher ' + currentTargetId + ' has been approved for release.' });
    } else {
      alert('AP Payment Voucher ' + currentTargetId + ' authorized successfully!');
    }
  }

  function openRejectModal(id, payee, amount) {
    currentTargetId = id;
    document.getElementById('rejectApprovalId').value = id + ' - ' + payee;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
  }

  function confirmRejection() {
    if (!currentTargetId) return;
    const reason = document.getElementById('rejectReasonSelect').value;
    const row = document.getElementById('row-' + currentTargetId);
    if (row) {
      row.querySelector('.status-cell').innerHTML = '<span class="badge bg-danger-subtle text-danger" title="' + reason + '"><i class="ph ph-x-circle me-1"></i> Rejected</span>';
      row.querySelector('.action-cell').innerHTML = '<div class="d-inline-flex align-items-center justify-content-end"><span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="ph ph-prohibited me-1"></i> Returned to AP</span></div>';
    }
    const modalEl = document.getElementById('rejectModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    if (window.HimsComponents && window.HimsComponents.notify) {
      window.HimsComponents.notify({ tone: 'danger', title: 'Payment Rejected', message: 'AP Payment Voucher ' + currentTargetId + ' has been rejected: ' + reason });
    } else {
      alert('AP Payment Voucher ' + currentTargetId + ' rejected: ' + reason);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    const tierSelect = document.getElementById('approvalTierSelect');
    const searchInput = document.getElementById('approvalSearchInput');

    function filterApprovals() {
      const selectedTier = tierSelect ? tierSelect.value.toLowerCase() : '';
      const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
      const rows = document.querySelectorAll('.approval-row');
      let visibleCount = 0;

      rows.forEach(function(row) {
        const rowTier = row.getAttribute('data-tier') || '';
        const rowText = row.textContent.toLowerCase();

        const matchTier = !selectedTier || rowTier.includes(selectedTier);
        const matchSearch = !searchQuery || rowText.includes(searchQuery);

        if (matchTier && matchSearch) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      let emptyRow = document.getElementById('noApprovalsRow');
      const tbody = document.querySelector('#apApprovalsTable tbody');
      if (visibleCount === 0) {
        if (!emptyRow && tbody) {
          emptyRow = document.createElement('tr');
          emptyRow.id = 'noApprovalsRow';
          emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No authorization requests found matching the current filter.</td>`;
          tbody.appendChild(emptyRow);
        }
        if (emptyRow) emptyRow.style.display = '';
      } else if (emptyRow) {
        emptyRow.style.display = 'none';
      }
    }

    if (tierSelect) tierSelect.addEventListener('change', filterApprovals);
    if (searchInput) {
      searchInput.addEventListener('input', filterApprovals);
      searchInput.addEventListener('keyup', filterApprovals);
    }

    filterApprovals();
  });
</script>
@endpush
@endsection
