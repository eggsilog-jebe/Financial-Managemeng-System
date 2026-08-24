@extends('layouts.app')

@section('title', 'Payment Requests - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'payment-requests')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Payment Requests</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Payment Requisitions</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Payment Requisitions...');"><i class="ph ph-download-simple me-1"></i> Export Requests</button>
      <button id="btnCreateRequest" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createRequestModal"><i class="ph ph-plus me-1"></i> Submit Payment Request</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Requisitions</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-file-text fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($requisitions ?? []) }} Requests</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending Approval</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Budget Verified</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">0% Encumbered</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Released Payments</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Requisitions Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Department Origin Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="reqDeptSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Department:</label>
          <select id="reqDeptSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="all" selected>All Requisitions</option>
            <option value="surgery">Surgery &amp; Operating Room</option>
            <option value="er">Emergency Room (ER)</option>
            <option value="biomedical">Biomedical Engineering</option>
          </select>
        </div>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="reqSearchInput" class="form-control form-control-sm" placeholder="Search req #, department, payee...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="requisitionTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Request Ref</th>
              <th>Department</th>
              <th>Payee Name</th>
              <th>Purpose / Particulars</th>
              <th>Encumbrance</th>
              <th class="text-end">Amount</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($requisitions ?? [] as $r)
            @php
              $ref = is_array($r) ? $r['ref'] : $r->request_number;
              $dept = is_array($r) ? $r['dept'] : $r->department;
              $payee = is_array($r) ? $r['payee'] : $r->payee_name;
              $purpose = is_array($r) ? $r['purpose'] : $r->purpose;
              $amt = is_array($r) ? $r['amount'] : ('₱' . number_format($r->amount, 2));
              $status = is_array($r) ? $r['status'] : $r->status;
              $rData = [
                'ref' => $ref,
                'dept' => $dept,
                'payee' => $payee,
                'purpose' => $purpose,
                'amount' => $amt,
                'status' => $status
              ];
            @endphp
            <tr class="req-row" style="cursor: pointer;" onclick="openRequestDetailsModal({{ json_encode($rData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $ref }}</span></td>
              <td class="fw-semibold text-dark">{{ $dept }}</td>
              <td>{{ $payee }}</td>
              <td class="fs-xs text-muted">{{ $purpose }}</td>
              <td><span class="badge bg-success-subtle text-success">Verified</span></td>
              <td class="text-end font-monospace fw-bold text-danger">{{ $amt }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> {{ $status }}</span></td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" onclick="openRequestDetailsModal({{ json_encode($rData) }})"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No payment requests recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="reqSummaryText">Showing {{ count($requisitions ?? []) }} Requisitions</span>
      <nav aria-label="Requisitions Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Payment Requisition Details (Executive Design) -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailReqRef">REQ-2026-114</span>
            <span class="badge bg-warning-subtle text-warning" id="detailReqStatus"><i class="ph ph-clock me-1"></i> Pending CFO Sign-off</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailReqDept">Surgery &amp; Operating Room</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Requested Fund Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailReqAmount">₱18,500.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Budget Verification</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailReqCc">Encumbered (CC-104)</h4>
            </div>
          </div>
        </div>

        <!-- Payee & Particulars Card -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-buildings me-1 text-primary"></i> Target Payee &amp; Particular Purpose</h6>
          <h6 class="fw-bold text-dark mb-1" id="detailReqPayee">Surgical Supplies &amp; Implants Co.</h6>
          <p class="small text-muted mb-0 lh-base" id="detailReqPurpose">Emergency Sterilizer Maintenance Pack &amp; Autoclave Seals</p>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Requisition Requested By:</span>
              <span class="fw-semibold text-dark" id="detailReqRequestedBy"><i class="ph ph-user me-1 text-primary"></i> Dr. A. Ramos (Surgery Dept Head)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Cost Center Verification:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Cost Center Funds Available</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Log Stamp:</span>
              <span class="font-monospace text-muted">LOG-REQ-2026-114 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Requisition Document PDF...');"><i class="ph ph-printer me-1"></i> Print Requisition PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Submit Payment Request -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createRequestModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Submit Departmental Payment Requisition</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createRequestForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Requisition Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalReqRef" class="form-control form-control-sm font-monospace" placeholder="e.g. REQ-2026-117" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department Origin <span class="text-danger">*</span></label>
              <select id="modalReqDept" class="form-select form-select-sm" required>
                <option value="surgery">Surgery &amp; Operating Room</option>
                <option value="er">Emergency Room (ER)</option>
                <option value="biomedical">Biomedical Engineering</option>
                <option value="pharmacy">Central Pharmacy</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee / Vendor Name <span class="text-danger">*</span></label>
              <input type="text" id="modalReqPayee" class="form-control form-control-sm" placeholder="e.g. Siemens Healthcare" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Requested Fund Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalReqAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="15000.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Purpose &amp; Particulars <span class="text-danger">*</span></label>
              <textarea id="modalReqPurpose" class="form-control form-control-sm" rows="2" placeholder="Explain the operational necessity for this fund requisition..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit Requisition</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openRequestDetailsModal(r) {
  if (!r) return;

  document.getElementById('detailReqRef').textContent = r.ref || 'REQ-000';
  document.getElementById('detailReqDept').textContent = r.dept || 'Department';
  document.getElementById('detailReqAmount').textContent = r.amount || '₱0.00';
  document.getElementById('detailReqCc').textContent = r.cc || 'Encumbered';
  document.getElementById('detailReqPayee').textContent = r.payee || 'Payee Vendor';
  document.getElementById('detailReqPurpose').textContent = r.purpose || 'Purpose';
  document.getElementById('detailReqRequestedBy').innerHTML = `<i class="ph ph-user me-1 text-primary"></i> ${r.requested_by || 'Department Supervisor'}`;

  const statusEl = document.getElementById('detailReqStatus');
  if (statusEl) {
    statusEl.textContent = r.status;
    statusEl.className = 'badge ' + (r.status_badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('requestDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('reqSearchInput');
  const summaryText = document.getElementById('reqSummaryText');
  const btnCreateRequest = document.getElementById('btnCreateRequest');
  let activeDept = 'all';

  if (btnCreateRequest) {
    btnCreateRequest.addEventListener('click', function() {
      const modalEl = document.getElementById('createRequestModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  const reqDeptSelect = document.getElementById('reqDeptSelect');
  if (reqDeptSelect) {
    reqDeptSelect.addEventListener('change', function() {
      activeDept = this.value || 'all';
      filterRequisitions();
    });
  }

  function filterRequisitions() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.req-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowDept = row.getAttribute('data-dept') || '';
      const rowText = row.textContent.toLowerCase();

      const matchDept = activeDept === 'all' || rowDept === activeDept;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchDept && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Requisition${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReqRow');
    const tbody = document.querySelector('#reqTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReqRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No payment requisitions found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterRequisitions);
    searchInput.addEventListener('keyup', filterRequisitions);
  }

  const createRequestForm = document.getElementById('createRequestForm');
  if (createRequestForm) {
    createRequestForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const refVal = document.getElementById('modalReqRef').value;
      const deptCodeVal = document.getElementById('modalReqDept').value;
      const payeeVal = document.getElementById('modalReqPayee').value;
      const rawAmount = parseFloat(document.getElementById('modalReqAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const purposeVal = document.getElementById('modalReqPurpose').value;

      let deptLabel = 'Surgery & Operating Room';
      if (deptCodeVal === 'er') deptLabel = 'Emergency Room (ER)';
      else if (deptCodeVal === 'biomedical') deptLabel = 'Biomedical Engineering';
      else if (deptCodeVal === 'pharmacy') deptLabel = 'Central Pharmacy';

      const reqObj = {
        ref: refVal,
        dept: deptLabel,
        dept_code: deptCodeVal,
        payee: payeeVal,
        purpose: purposeVal,
        cc: 'Encumbered (CC-109)',
        amount: formattedAmount,
        status: 'Pending Dept Head',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock',
        requested_by: 'Active User (Department Supervisor)'
      };

      const tbody = document.querySelector('#reqTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'req-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-dept', deptCodeVal);

        newRow.onclick = function() { openRequestDetailsModal(reqObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${refVal}</span></td>
          <td class="fw-semibold text-dark">${deptLabel}</td>
          <td>${payeeVal}</td>
          <td><span class="text-truncate d-inline-block" style="max-width: 250px;">${purposeVal}</span></td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Encumbered (CC-109)</span></td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Dept Head</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Requisition Document"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Requisition Document"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openRequestDetailsModal(reqObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createRequestModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createRequestForm.reset();
      filterRequisitions();
    });
  }

  filterRequisitions();
});
</script>
@endpush
