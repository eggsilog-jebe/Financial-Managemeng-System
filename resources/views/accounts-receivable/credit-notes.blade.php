@extends('layouts.app')

@section('title', 'Credit Notes - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Credit Notes</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AR Credit Notes &amp; Billing Adjustments</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Credit Notes Log PDF...');"><i class="ph ph-file-arrow-down me-1"></i> Credit Log PDF</button>
      <button id="btnIssueCreditNote" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueCreditNoteModal"><i class="ph ph-plus-circle me-1"></i> Issue Credit Note</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Credit Notes (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-note-pencil fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Credit Memos</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Value</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱84,500.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Contract Disallowance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱62,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Manager Approvals</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Pending</h4>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" id="cnSearchInput" class="form-control bg-light border-start-0" placeholder="Search CN Ref, Patient Name, or Invoice Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="cnReasonSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Reason Categories</option>
            <option value="hmo">HMO Contractual Rate Adjustment</option>
            <option value="courtesy">Senior / Courtesy Discount</option>
            <option value="error">Billing Correction</option>
          </select>
        </div>
        <div class="col-md-4">
          <select id="cnStatusSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Statuses</option>
            <option value="applied">Applied &amp; Settled</option>
            <option value="pending approval">Pending Approval</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="creditNotesTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Credit Note Ref</th>
              <th>Date</th>
              <th>Patient / Payor Name</th>
              <th>Target Invoice Ref</th>
              <th class="text-end">Credit Amount (₱)</th>
              <th>Reason</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $creditNotes = [
                [
                  'ref' => 'CN-2026-041',
                  'date' => '2026-08-07',
                  'payor' => 'Maxicare Healthcare Corp',
                  'sub' => 'HMO Agreed Tariff Discount',
                  'inv_ref' => 'INV-2026-0881',
                  'amount' => '₱12,500.00',
                  'reason' => 'HMO Contractual Rate Reduction',
                  'reason_type' => 'hmo',
                  'status' => 'Applied',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'notes' => 'Contractual fee adjustment per Maxicare Tier-1 agreement clause 4.2.'
                ],
                [
                  'ref' => 'CN-2026-042',
                  'date' => '2026-08-06',
                  'payor' => 'Ricardo Reyes',
                  'sub' => 'Senior Citizen Statutory 20% Discount',
                  'inv_ref' => 'INV-2026-0884',
                  'amount' => '₱8,400.00',
                  'reason' => 'Senior / PWD Courtesy Discount',
                  'reason_type' => 'courtesy',
                  'status' => 'Applied',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'notes' => 'RA 9994 Senior Citizen 20% discount on professional fee & hospital bed charges.'
                ],
                [
                  'ref' => 'CN-2026-043',
                  'date' => '2026-08-05',
                  'payor' => 'Intellicare HMO',
                  'sub' => 'Pharmacy Unit Cost Correction',
                  'inv_ref' => 'INV-2026-0883',
                  'amount' => '₱3,600.00',
                  'reason' => 'Billing Correction',
                  'reason_type' => 'error',
                  'status' => 'Applied',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'notes' => 'Corrected duplicate entry for IV drip set item code 4091.'
                ],
                [
                  'ref' => 'CN-2026-044',
                  'date' => '2026-08-04',
                  'payor' => 'Maria Santos',
                  'sub' => 'Courtesy Relief Adjustment',
                  'inv_ref' => 'INV-2026-0883',
                  'amount' => '₱5,000.00',
                  'reason' => 'Senior / Courtesy Discount',
                  'reason_type' => 'courtesy',
                  'status' => 'Pending Approval',
                  'status_badge' => 'bg-warning-subtle text-warning',
                  'status_icon' => 'ph-clock',
                  'notes' => 'Director approval pending for indigent patient room rate discount.'
                ],
              ];
            @endphp

            @foreach($creditNotes as $cn)
            <tr class="cn-row" style="cursor: pointer;" data-reason="{{ $cn['reason_type'] }}" data-status="{{ strtolower($cn['status']) }}" onclick="openCreditNoteDetailsModal({{ json_encode($cn) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $cn['ref'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $cn['date'] }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $cn['payor'] }}</div>
                <span class="fs-xs text-muted">{{ $cn['sub'] }}</span>
              </td>
              <td><span class="font-monospace text-muted">{{ $cn['inv_ref'] }}</span></td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $cn['amount'] }}</td>
              <td>{{ $cn['reason'] }}</td>
              <td><span class="badge {{ $cn['status_badge'] }}"><i class="ph {{ $cn['status_icon'] }} me-1"></i> {{ $cn['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Credit Note Details" onclick="openCreditNoteDetailsModal({{ json_encode($cn) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="cnSummaryText">Showing {{ count($creditNotes) }} Credit Notes</span>
      <nav aria-label="Credit Notes Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Credit Note Details (Executive Design) -->
<div class="modal fade" id="creditNoteDetailsModal" tabindex="-1" aria-labelledby="creditNoteDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailCnRef">CN-2026-041</span>
            <span class="badge bg-success-subtle text-success" id="detailCnStatus"><i class="ph ph-check-circle me-1"></i> Applied</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailCnPayor">Maxicare Healthcare Corp</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Credit Adjustment Amount</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailCnAmount">₱12,500.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Target Billing Invoice</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailCnInvRef">INV-2026-0881</h4>
            </div>
          </div>
        </div>

        <!-- Particulars & Justification -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Adjustment Reason &amp; Audit Justification</h6>
          <h6 class="fw-bold text-dark mb-2" id="detailCnReason">HMO Contractual Rate Reduction</h6>
          <p class="small text-muted mb-0 lh-base" id="detailCnNotes">Contractual fee adjustment per Maxicare Tier-1 agreement clause 4.2.</p>
        </div>

        <!-- Master Info -->
        <div class="bg-white border rounded-3 p-3 mb-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-receipt me-1 text-primary"></i> Master Registry Info</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Issue Date</span>
              <span class="font-monospace fw-bold text-dark" id="detailCnDate">2026-08-07</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Description Subtitle</span>
              <span class="text-dark fw-medium" id="detailCnSub">HMO Agreed Tariff Discount</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Transparency Verification -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Prepared &amp; Inputted By:</span>
              <span class="fw-semibold text-dark"><i class="ph ph-user me-1 text-primary"></i> B. Santos (Billing Specialist ID #204)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Accounting Verification:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Dual-Entry GL Ledger Verified</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Timestamp:</span>
              <span class="font-monospace text-muted" id="detailCnTimestamp">2026-08-07 14:32:05 PST (IP: 192.168.10.45)</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Credit Note PDF...');"><i class="ph ph-printer me-1"></i> Print Credit Memo</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue AR Credit Note -->
<div class="modal fade" id="issueCreditNoteModal" tabindex="-1" aria-labelledby="issueCreditNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueCreditNoteModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue AR Credit Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="issueCreditNoteForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Note Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalCnRef" class="form-control form-control-sm font-monospace" placeholder="e.g. CN-2026-045" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient / Payor Name <span class="text-danger">*</span></label>
              <input type="text" id="modalCnPayor" class="form-control form-control-sm" placeholder="e.g. Maxicare or Juan Dela Cruz" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Invoice Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalCnInvRef" class="form-control form-control-sm font-monospace" placeholder="e.g. INV-2026-0881" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Adjustment Category <span class="text-danger">*</span></label>
              <select id="modalCnReasonType" class="form-select form-select-sm" required>
                <option value="hmo">HMO Contract Rate Disallowance</option>
                <option value="courtesy">Senior / PWD Courtesy Discount</option>
                <option value="error">Billing Charge Correction</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Issue Date <span class="text-danger">*</span></label>
              <input type="date" id="modalCnDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Adjustment Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalCnAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace text-danger fw-bold" placeholder="0.00" value="5000.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Audit Justification Notes <span class="text-danger">*</span></label>
              <textarea id="modalCnNotes" class="form-control form-control-sm" rows="2" placeholder="State reason for credit memo..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Issue &amp; Apply Credit Note</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openCreditNoteDetailsModal(cn) {
  if (!cn) return;

  document.getElementById('detailCnRef').textContent = cn.ref || 'CN-000';
  document.getElementById('detailCnPayor').textContent = cn.payor || 'Payor Name';
  document.getElementById('detailCnAmount').textContent = cn.amount || '₱0.00';
  document.getElementById('detailCnInvRef').textContent = cn.inv_ref || 'INV-000';
  document.getElementById('detailCnReason').textContent = cn.reason || 'Reason';
  document.getElementById('detailCnNotes').textContent = cn.notes || 'No notes provided.';
  document.getElementById('detailCnDate').textContent = cn.date || '-';
  document.getElementById('detailCnSub').textContent = cn.sub || '-';

  const statusEl = document.getElementById('detailCnStatus');
  if (statusEl) {
    statusEl.textContent = cn.status;
    statusEl.className = 'badge ' + (cn.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('creditNoteDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('cnSearchInput');
  const reasonSelect = document.getElementById('cnReasonSelect');
  const statusSelect = document.getElementById('cnStatusSelect');
  const summaryText = document.getElementById('cnSummaryText');
  const btnIssueCreditNote = document.getElementById('btnIssueCreditNote');

  if (btnIssueCreditNote) {
    btnIssueCreditNote.addEventListener('click', function() {
      const modalEl = document.getElementById('issueCreditNoteModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterCreditNotes() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedReason = reasonSelect ? reasonSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.cn-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowReason = row.getAttribute('data-reason') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchReason = !selectedReason || rowReason.includes(selectedReason);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchReason && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Credit Note${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noCnRow');
    const tbody = document.querySelector('#creditNotesTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noCnRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No credit notes found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterCreditNotes);
    searchInput.addEventListener('keyup', filterCreditNotes);
  }
  if (reasonSelect) reasonSelect.addEventListener('change', filterCreditNotes);
  if (statusSelect) statusSelect.addEventListener('change', filterCreditNotes);

  const issueCreditNoteForm = document.getElementById('issueCreditNoteForm');
  if (issueCreditNoteForm) {
    issueCreditNoteForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const refVal = document.getElementById('modalCnRef').value;
      const payorVal = document.getElementById('modalCnPayor').value;
      const invRefVal = document.getElementById('modalCnInvRef').value;
      const reasonTypeVal = document.getElementById('modalCnReasonType').value;
      const dateVal = document.getElementById('modalCnDate').value;
      const rawAmount = parseFloat(document.getElementById('modalCnAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const notesVal = document.getElementById('modalCnNotes').value;

      let reasonLabel = 'HMO Contractual Rate Reduction';
      if (reasonTypeVal === 'courtesy') reasonLabel = 'Senior / PWD Courtesy Discount';
      else if (reasonTypeVal === 'error') reasonLabel = 'Billing Correction';

      const cnObj = {
        ref: refVal,
        date: dateVal,
        payor: payorVal,
        sub: 'Newly Issued Credit Memo',
        inv_ref: invRefVal,
        amount: formattedAmount,
        reason: reasonLabel,
        reason_type: reasonTypeVal,
        status: 'Applied',
        status_badge: 'bg-success-subtle text-success',
        status_icon: 'ph-check-circle',
        notes: notesVal
      };

      const tbody = document.querySelector('#creditNotesTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'cn-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-reason', reasonTypeVal);
        newRow.setAttribute('data-status', 'applied');

        newRow.onclick = function() { openCreditNoteDetailsModal(cnObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${refVal}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td>
            <div class="fw-semibold text-dark">${payorVal}</div>
            <span class="fs-xs text-muted">Newly Issued Credit Memo</span>
          </td>
          <td><span class="font-monospace text-muted">${invRefVal}</span></td>
          <td class="text-end text-danger fw-bold font-monospace">${formattedAmount}</td>
          <td>${reasonLabel}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Applied</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Credit Note Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Credit Note Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openCreditNoteDetailsModal(cnObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('issueCreditNoteModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      issueCreditNoteForm.reset();
      filterCreditNotes();
    });
  }

  filterCreditNotes();
});
</script>
@endpush
