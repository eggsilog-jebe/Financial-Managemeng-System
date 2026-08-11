@extends('layouts.app')

@section('title', 'Budget Reallocations - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'reallocations')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Reallocations</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Reallocations &amp; Inter-Departmental Transfers</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Downloading Transfer Log PDF...');"><i class="ph ph-file-text me-1"></i> Transfer Log PDF</button>
      <button id="btnRequestTransfer" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#requestTransferModal"><i class="ph ph-arrows-left-right me-1"></i> Request Transfer</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Transfer Requests</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Request</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Approved Transfers (YTD)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Approved</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Budget Impact</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Rejected Requests</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-x-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">0 Requests</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="sourceDeptSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Source Dept:</label>
          <select id="sourceDeptSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Source Departments</option>
            <option value="radiology">Radiology &amp; Imaging</option>
            <option value="outpatient">Outpatient Clinic</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="transferStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="transferStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Transfer Statuses</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending CFO Review</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="reallocSearchInput" class="form-control form-control-sm" placeholder="Search transfer ref, source, target...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="reallocTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Transfer Ref</th>
              <th>Source Department (From)</th>
              <th>Destination Department (To)</th>
              <th class="text-end">Transfer Amount (₱)</th>
              <th>Operational Reason</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $reallocations = [
                [
                  'ref' => 'REAL-2026-05',
                  'from' => 'Radiology & Imaging',
                  'to' => 'Facilities & Utilities',
                  'amount' => '₱150,000.00',
                  'reason' => 'Coverage for power generator fuel rate hike',
                  'status' => 'Pending CFO Review',
                  'status_badge' => 'bg-warning-subtle text-warning',
                  'status_icon' => 'ph-clock'
                ],
                [
                  'ref' => 'REAL-2026-04',
                  'from' => 'Outpatient Clinic',
                  'to' => 'ICU & Emergency',
                  'amount' => '₱50,000.00',
                  'reason' => 'Emergency Ventilator Parts Acquisition',
                  'status' => 'Approved',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle'
                ],
              ];
            @endphp

            @foreach($reallocations as $r)
            <tr class="realloc-row" style="cursor: pointer;" data-from="{{ strtolower($r['from']) }}" data-status="{{ strtolower($r['status']) }}" onclick="openReallocationDetailsModal({{ json_encode($r) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $r['ref'] }}</span></td>
              <td><span class="badge bg-light text-dark border">{{ $r['from'] }}</span></td>
              <td><span class="badge bg-light text-dark border">{{ $r['to'] }}</span></td>
              <td class="text-end text-success fw-bold font-monospace">{{ $r['amount'] }}</td>
              <td class="fs-xs text-muted">{{ $r['reason'] }}</td>
              <td><span class="badge {{ $r['status_badge'] }}"><i class="ph {{ $r['status_icon'] }} me-1"></i> {{ $r['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Transfer Details" onclick="openReallocationDetailsModal({{ json_encode($r) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="reallocSummaryText">Showing {{ count($reallocations) }} Transfer Requests</span>
      <nav aria-label="Reallocation Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Reallocation Details (Executive Design) -->
<div class="modal fade" id="reallocationDetailsModal" tabindex="-1" aria-labelledby="reallocationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailRealRef">REAL-2026-05</span>
            <span class="badge bg-warning-subtle text-warning" id="detailRealStatus"><i class="ph ph-clock me-1"></i> Pending CFO Review</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0">Inter-Departmental Budget Transfer</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Transfer Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailRealAmount">₱150,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Source &amp; Target Route</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailRealRoute">Radiology ➔ Facilities</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-arrows-left-right me-1 text-primary"></i> Transfer Justification &amp; Departments</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Source Department (Surplus)</span>
              <span class="badge bg-light text-dark border" id="detailRealFrom">Radiology &amp; Imaging</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Destination Department (Deficit)</span>
              <span class="badge bg-light text-dark border" id="detailRealTo">Facilities &amp; Utilities</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Operational Reason</span>
              <span class="fw-semibold text-dark" id="detailRealReason">Coverage for power generator fuel rate hike</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; CFO Approval Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Dual Sign-off Authorization:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Department Heads Approved</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-REAL-2026-05 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-success" onclick="alert('Transfer Approved by CFO!');"><i class="ph ph-check me-1"></i> Approve Transfer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Request Inter-Departmental Transfer -->
<div class="modal fade" id="requestTransferModal" tabindex="-1" aria-labelledby="requestTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="requestTransferModalLabel"><i class="ph ph-arrows-left-right me-2 text-primary"></i>Request Inter-Departmental Budget Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="requestTransferForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Department (Surplus) <span class="text-danger">*</span></label>
              <select id="modalRealFrom" class="form-select form-select-sm" required>
                <option value="Radiology & Imaging">Radiology &amp; Imaging</option>
                <option value="Outpatient Clinic">Outpatient Clinic</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Destination Department (Deficit) <span class="text-danger">*</span></label>
              <select id="modalRealTo" class="form-select form-select-sm" required>
                <option value="Facilities & Utilities">Facilities &amp; Utilities</option>
                <option value="ICU & Emergency">ICU &amp; Emergency</option>
                <option value="Pharmacy & Medical Supplies">Pharmacy &amp; Medical Supplies</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalRealAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="100000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Effective Period</label>
              <select class="form-select form-select-sm">
                <option value="q3">Q3 2026 (Immediate)</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Operational Justification <span class="text-danger">*</span></label>
              <input type="text" id="modalRealReason" class="form-control form-control-sm" placeholder="e.g. Emergency equipment repair cost overrun" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-paper-plane-tilt me-1"></i> Submit Transfer Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openReallocationDetailsModal(r) {
  if (!r) return;

  document.getElementById('detailRealRef').textContent = r.ref || 'REAL-000';
  document.getElementById('detailRealFrom').textContent = r.from || 'Source';
  document.getElementById('detailRealTo').textContent = r.to || 'Target';
  document.getElementById('detailRealAmount').textContent = r.amount || '₱0.00';
  document.getElementById('detailRealRoute').textContent = (r.from || 'Source') + ' ➔ ' + (r.to || 'Target');
  document.getElementById('detailRealReason').textContent = r.reason || '-';

  const statusEl = document.getElementById('detailRealStatus');
  if (statusEl) {
    statusEl.textContent = r.status;
    statusEl.className = 'badge ' + (r.status_badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('reallocationDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('reallocSearchInput');
  const sourceDeptSelect = document.getElementById('sourceDeptSelect');
  const transferStatusSelect = document.getElementById('transferStatusSelect');
  const summaryText = document.getElementById('reallocSummaryText');
  const btnRequestTransfer = document.getElementById('btnRequestTransfer');

  if (btnRequestTransfer) {
    btnRequestTransfer.addEventListener('click', function() {
      const modalEl = document.getElementById('requestTransferModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterReallocations() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedSource = sourceDeptSelect ? sourceDeptSelect.value.toLowerCase() : '';
    const selectedStatus = transferStatusSelect ? transferStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.realloc-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowFrom = row.getAttribute('data-from') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchSource = !selectedSource || rowFrom.includes(selectedSource);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchSource && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Transfer Request${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReallocRow');
    const tbody = document.querySelector('#reallocTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReallocRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No transfer requests found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterReallocations);
    searchInput.addEventListener('keyup', filterReallocations);
  }
  if (sourceDeptSelect) sourceDeptSelect.addEventListener('change', filterReallocations);
  if (transferStatusSelect) transferStatusSelect.addEventListener('change', filterReallocations);

  const requestTransferForm = document.getElementById('requestTransferForm');
  if (requestTransferForm) {
    requestTransferForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const fromVal = document.getElementById('modalRealFrom').value;
      const toVal = document.getElementById('modalRealTo').value;
      const reasonVal = document.getElementById('modalRealReason').value;
      const rawAmount = parseFloat(document.getElementById('modalRealAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextRef = 'REAL-2026-' + Math.floor(6 + Math.random() * 20);

      const reallocObj = {
        ref: nextRef,
        from: fromVal,
        to: toVal,
        amount: formattedAmount,
        reason: reasonVal,
        status: 'Pending CFO Review',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock'
      };

      const tbody = document.querySelector('#reallocTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'realloc-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-from', fromVal.toLowerCase());
        newRow.setAttribute('data-status', 'pending cfo review');

        newRow.onclick = function() { openReallocationDetailsModal(reallocObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td><span class="badge bg-light text-dark border">${fromVal}</span></td>
          <td><span class="badge bg-light text-dark border">${toVal}</span></td>
          <td class="text-end text-success fw-bold font-monospace">${formattedAmount}</td>
          <td class="fs-xs text-muted">${reasonVal}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending CFO Review</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Transfer Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Transfer Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openReallocationDetailsModal(reallocObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('requestTransferModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      requestTransferForm.reset();
      filterReallocations();
    });
  }

  filterReallocations();
});
</script>
@endpush
