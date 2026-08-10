@extends('layouts.app')

@section('title', 'Petty Cash - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'petty-cash')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Petty Cash</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Petty Cash Fund</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Auditing cash fund drawer count...');"><i class="ph ph-arrows-clockwise me-1"></i> Audit Cash Fund</button>
      <button id="btnCreatePcv" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createPcvModal"><i class="ph ph-plus me-1"></i> Issue PCV Voucher</button>
    </div>
  </div>

  <!-- Fund Status Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Fixed Fund Ceiling</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-wallet fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱50,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Cash Remaining in Drawer</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱34,250.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Disbursed Vouchers</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱15,750.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Fund Custodian</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-user-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Anna Reyes</h4>
      </div>
    </div>
  </div>

  <!-- Petty Cash Vouchers Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="pcvStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Audit Status:</label>
          <select id="pcvStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Audit Statuses</option>
            <option value="receipt attached">Receipt Attached</option>
            <option value="pending receipt">Pending Receipt</option>
          </select>
        </div>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="pcvSearchInput" class="form-control form-control-sm" placeholder="Search voucher #, claimant, particulars...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="pcvTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher #</th>
              <th>Date</th>
              <th>Claimant &amp; Department</th>
              <th>Expense Particulars</th>
              <th class="text-end">Amount (₱)</th>
              <th>Audit Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @php
              $pcvs = [
                [
                  'ref' => 'PCV-2026-081',
                  'date' => '2026-08-06',
                  'claimant' => 'ER Desk Nurse',
                  'dept' => 'Emergency Room',
                  'purpose' => 'Urgent Courier Fee for Reference Lab Specimen Transport',
                  'amount' => '₱85.00',
                  'status' => 'Receipt Attached',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check',
                  'receipt_no' => 'OR-LAB-88120'
                ],
                [
                  'ref' => 'PCV-2026-082',
                  'date' => '2026-08-07',
                  'claimant' => 'OR Head Nurse',
                  'dept' => 'Surgery & Operating Room',
                  'purpose' => 'Emergency Distilled Water for Sterilizer Reservoir',
                  'amount' => '₱240.00',
                  'status' => 'Receipt Attached',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check',
                  'receipt_no' => 'OR-SUP-99412'
                ],
              ];
            @endphp

            @foreach($pcvs as $p)
            <tr class="pcv-row" style="cursor: pointer;" data-status="{{ strtolower($p['status']) }}" onclick="openPcvDetailsModal({{ json_encode($p) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $p['ref'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $p['date'] }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $p['claimant'] }}</div>
                <div class="text-muted fs-xs">{{ $p['dept'] }}</div>
              </td>
              <td><span class="text-truncate d-inline-block" style="max-width: 250px;">{{ $p['purpose'] }}</span></td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $p['amount'] }}</td>
              <td><span class="badge {{ $p['status_badge'] }}"><i class="ph {{ $p['status_icon'] }} me-1"></i> {{ $p['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Receipt Attachment" onclick="openPcvDetailsModal({{ json_encode($p) }})"><i class="ph ph-file-image"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="pcvSummaryText">Showing {{ count($pcvs) }} Petty Cash Vouchers</span>
      <nav aria-label="PCV Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth PCV Details (Executive Design) -->
<div class="modal fade" id="pcvDetailsModal" tabindex="-1" aria-labelledby="pcvDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailPcvRef">PCV-2026-081</span>
            <span class="badge bg-success-subtle text-success" id="detailPcvStatus"><i class="ph ph-check me-1"></i> Receipt Attached</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailPcvClaimant">ER Desk Nurse</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Petty Cash Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailPcvAmount">₱85.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Disbursement Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailPcvDate">2026-08-06</h4>
            </div>
          </div>
        </div>

        <!-- Expense Particulars & Department Card -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Department &amp; Expense Particulars</h6>
          <h6 class="fw-bold text-dark mb-1" id="detailPcvDept">Emergency Room</h6>
          <p class="small text-muted mb-0 lh-base" id="detailPcvPurpose">Urgent Courier Fee for Reference Lab Specimen Transport</p>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Cash Drawer Custodian:</span>
              <span class="fw-semibold text-dark"><i class="ph ph-user me-1 text-primary"></i> Anna Reyes (Main Custodian)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Official Receipt Reference:</span>
              <span class="font-monospace fw-bold text-success" id="detailPcvReceiptNo">OR-LAB-88120</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Log:</span>
              <span class="font-monospace text-muted">LOG-PCV-2026-081 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Viewing attached physical receipt scan...');"><i class="ph ph-file-image me-1"></i> View Receipt Attachment</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue PCV Voucher -->
<div class="modal fade" id="createPcvModal" tabindex="-1" aria-labelledby="createPcvModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createPcvModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue Petty Cash Voucher (PCV)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createPcvForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Voucher Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalPcvRef" class="form-control form-control-sm font-monospace" placeholder="e.g. PCV-2026-083" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Claimant Staff Name <span class="text-danger">*</span></label>
              <input type="text" id="modalPcvClaimant" class="form-control form-control-sm" placeholder="e.g. Pharmacy Stock Clerk" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
              <input type="text" id="modalPcvDept" class="form-control form-control-sm" placeholder="e.g. Central Pharmacy" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Voucher Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalPcvAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="150.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Expense Particulars <span class="text-danger">*</span></label>
              <textarea id="modalPcvPurpose" class="form-control form-control-sm" rows="2" placeholder="State minor operational expense purpose..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Issue Cash Voucher</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openPcvDetailsModal(p) {
  if (!p) return;

  document.getElementById('detailPcvRef').textContent = p.ref || 'PCV-000';
  document.getElementById('detailPcvClaimant').textContent = p.claimant || 'Claimant';
  document.getElementById('detailPcvAmount').textContent = p.amount || '₱0.00';
  document.getElementById('detailPcvDate').textContent = p.date || '-';
  document.getElementById('detailPcvDept').textContent = p.dept || 'Department';
  document.getElementById('detailPcvPurpose').textContent = p.purpose || 'Purpose';
  document.getElementById('detailPcvReceiptNo').textContent = p.receipt_no || 'OR-PENDING';

  const statusEl = document.getElementById('detailPcvStatus');
  if (statusEl) {
    statusEl.textContent = p.status;
    statusEl.className = 'badge ' + (p.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('pcvDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('pcvSearchInput');
  const statusSelect = document.getElementById('pcvStatusSelect');
  const summaryText = document.getElementById('pcvSummaryText');
  const btnCreatePcv = document.getElementById('btnCreatePcv');

  if (btnCreatePcv) {
    btnCreatePcv.addEventListener('click', function() {
      const modalEl = document.getElementById('createPcvModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterPcvs() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.pcv-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Petty Cash Voucher${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noPcvRow');
    const tbody = document.querySelector('#pcvTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noPcvRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No petty cash vouchers found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterPcvs);
    searchInput.addEventListener('keyup', filterPcvs);
  }
  if (statusSelect) statusSelect.addEventListener('change', filterPcvs);

  const createPcvForm = document.getElementById('createPcvForm');
  if (createPcvForm) {
    createPcvForm.addEventListener('submit', function(ev) {
      ev.preventDefault();

      const refVal = document.getElementById('modalPcvRef').value;
      const claimantVal = document.getElementById('modalPcvClaimant').value;
      const deptVal = document.getElementById('modalPcvDept').value;
      const rawAmount = parseFloat(document.getElementById('modalPcvAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const purposeVal = document.getElementById('modalPcvPurpose').value;

      const pcvObj = {
        ref: refVal,
        date: "{{ date('Y-m-d') }}",
        claimant: claimantVal,
        dept: deptVal,
        purpose: purposeVal,
        amount: formattedAmount,
        status: 'Receipt Attached',
        status_badge: 'bg-success-subtle text-success',
        status_icon: 'ph-check',
        receipt_no: 'OR-GEN-99012'
      };

      const tbody = document.querySelector('#pcvTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'pcv-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-status', 'receipt attached');

        newRow.onclick = function() { openPcvDetailsModal(pcvObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${refVal}</span></td>
          <td class="font-monospace fs-xs">{{ date('Y-m-d') }}</td>
          <td>
            <div class="fw-semibold text-dark">${claimantVal}</div>
            <div class="text-muted fs-xs">${deptVal}</div>
          </td>
          <td><span class="text-truncate d-inline-block" style="max-width: 250px;">${purposeVal}</span></td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Receipt Attached</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Receipt Attachment"><i class="ph ph-file-image"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Receipt Attachment"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openPcvDetailsModal(pcvObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createPcvModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createPcvForm.reset();
      filterPcvs();
    });
  }

  filterPcvs();
});
</script>
@endpush
