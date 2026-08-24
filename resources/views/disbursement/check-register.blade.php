@extends('layouts.app')

@section('title', 'Check Register - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'check-register')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Check Register</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Check Register</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Check Register</button>
      <button id="btnCreateCheck" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createCheckModal"><i class="ph ph-plus me-1"></i> Issue Physical Check</button>
    </div>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Checks Issued</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">42 Checks</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Cleared by Bank</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,450,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Outstanding Checks</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱245,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Voided / Cancelled Checks</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-x-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Check Register Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="checkStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select id="checkStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Check Statuses</option>
            <option value="cleared by bank">Cleared by Bank</option>
            <option value="outstanding">Outstanding (In Transit)</option>
            <option value="voided">Voided / Cancelled</option>
          </select>
        </div>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="checkSearchInput" class="form-control form-control-sm" placeholder="Search check #, payee, bank...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="checkTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Check No</th>
              <th>Issue Date</th>
              <th>Payee Name</th>
              <th>Bank Account</th>
              <th>Voucher Ref</th>
              <th class="text-end">Amount (₱)</th>
              <th>Check Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($checks ?? [] as $c)
            @php
              $cArr = is_array($c) ? $c : [
                'chk_no' => $c->check_number ?? 'CHK-N/A',
                'date' => $c->check_date ? $c->check_date->format('Y-m-d') : 'N/A',
                'payee' => $c->payee_name ?? 'N/A',
                'bank' => $c->bank_name ?? 'N/A',
                'voucher' => $c->voucher_reference ?? 'N/A',
                'amount' => '₱' . number_format($c->amount ?? 0, 2),
                'status' => $c->status ?? 'Pending',
                'status_badge' => 'bg-warning-subtle text-warning',
                'status_icon' => 'ph-clock',
                'disb_officer' => $c->disbursing_officer ?? 'N/A',
              ];
            @endphp
            <tr class="check-row" style="cursor: pointer;" data-status="{{ strtolower($cArr['status']) }}" onclick="openCheckDetailsModal({{ json_encode($cArr) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $cArr['chk_no'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $cArr['date'] }}</td>
              <td class="fw-semibold text-dark">{{ $cArr['payee'] }}</td>
              <td><span class="fs-xs text-muted">{{ $cArr['bank'] }}</span></td>
              <td><span class="font-monospace text-primary">{{ $cArr['voucher'] }}</span></td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $cArr['amount'] }}</td>
              <td><span class="badge {{ $cArr['status_badge'] }}"><i class="ph {{ $cArr['status_icon'] }} me-1"></i> {{ $cArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Check Copy" onclick="openCheckDetailsModal({{ json_encode($cArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No checks registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="checkSummaryText">Showing {{ count($checks ?? []) }} Checks</span>
      <nav aria-label="Check Register Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Check Details (Executive Design) -->
<div class="modal fade" id="checkDetailsModal" tabindex="-1" aria-labelledby="checkDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailChkNo">CHK-004812</span>
            <span class="badge bg-success-subtle text-success" id="detailChkStatus"><i class="ph ph-check-circle me-1"></i> Cleared by Bank</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailChkPayee">PharmaCorp Philippines</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Check Face Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailChkAmount">₱120,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Check Issue Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailChkDate">2026-08-04</h4>
            </div>
          </div>
        </div>

        <!-- Particulars & Bank Info -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-bank me-1 text-primary"></i> Banking &amp; Voucher Reference</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Source Bank Account</span>
              <span class="font-monospace fw-bold text-dark" id="detailChkBank">Metrobank Operating #1020</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Associated AP Voucher</span>
              <span class="font-monospace fw-bold text-primary" id="detailChkVoucher">APV-2026-091</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Disbursement Officer:</span>
              <span class="fw-semibold text-dark" id="detailChkOfficer"><i class="ph ph-user me-1 text-primary"></i> J. Dela Cruz (Treasury Officer)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Check Print Verification:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Dual-Signatory Approved</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Log Stamp:</span>
              <span class="font-monospace text-muted">LOG-CHK-2026-004812 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Physical Check Copy PDF...');"><i class="ph ph-printer me-1"></i> Print Check Copy</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Physical Check -->
<div class="modal fade" id="createCheckModal" tabindex="-1" aria-labelledby="createCheckModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createCheckModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue Physical Crossed Check</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createCheckForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Number <span class="text-danger">*</span></label>
              <input type="text" id="modalChkNo" class="form-control form-control-sm font-monospace" placeholder="e.g. CHK-004814" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee Vendor Name <span class="text-danger">*</span></label>
              <input type="text" id="modalChkPayee" class="form-control form-control-sm" placeholder="e.g. MedTech Diagnostics Inc" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Bank Account <span class="text-danger">*</span></label>
              <select id="modalChkBank" class="form-select form-select-sm" required>
                <option value="Metrobank Operating #1020">Metrobank Operating #1020</option>
                <option value="BDO Corporate Treasury #4401">BDO Corporate Treasury #4401</option>
                <option value="LBP Government Fund #9012">LBP Government Fund #9012</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Associated AP Voucher Ref <span class="text-danger">*</span></label>
              <input type="text" id="modalChkVoucher" class="form-control form-control-sm font-monospace" placeholder="e.g. APV-2026-093" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Issue Date <span class="text-danger">*</span></label>
              <input type="date" id="modalChkDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalChkAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="50000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Issue Check</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openCheckDetailsModal(c) {
  if (!c) return;

  document.getElementById('detailChkNo').textContent = c.chk_no || 'CHK-000';
  document.getElementById('detailChkPayee').textContent = c.payee || 'Payee Name';
  document.getElementById('detailChkAmount').textContent = c.amount || '₱0.00';
  document.getElementById('detailChkDate').textContent = c.date || '-';
  document.getElementById('detailChkBank').textContent = c.bank || 'Bank Account';
  document.getElementById('detailChkVoucher').textContent = c.voucher || 'Voucher';
  document.getElementById('detailChkOfficer').innerHTML = `<i class="ph ph-user me-1 text-primary"></i> ${c.disb_officer || 'Treasury Officer'}`;

  const statusEl = document.getElementById('detailChkStatus');
  if (statusEl) {
    statusEl.textContent = c.status;
    statusEl.className = 'badge ' + (c.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('checkDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('checkSearchInput');
  const statusSelect = document.getElementById('checkStatusSelect');
  const summaryText = document.getElementById('checkSummaryText');
  const btnCreateCheck = document.getElementById('btnCreateCheck');

  if (btnCreateCheck) {
    btnCreateCheck.addEventListener('click', function() {
      const modalEl = document.getElementById('createCheckModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterChecks() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.check-row');
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
      summaryText.textContent = `Showing ${visibleCount} Check${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noCheckRow');
    const tbody = document.querySelector('#checkTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noCheckRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No check records found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterChecks);
    searchInput.addEventListener('keyup', filterChecks);
  }
  if (statusSelect) statusSelect.addEventListener('change', filterChecks);

  const createCheckForm = document.getElementById('createCheckForm');
  if (createCheckForm) {
    createCheckForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const chkNoVal = document.getElementById('modalChkNo').value;
      const payeeVal = document.getElementById('modalChkPayee').value;
      const bankVal = document.getElementById('modalChkBank').value;
      const voucherVal = document.getElementById('modalChkVoucher').value;
      const dateVal = document.getElementById('modalChkDate').value;
      const rawAmount = parseFloat(document.getElementById('modalChkAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      const checkObj = {
        chk_no: chkNoVal,
        date: dateVal,
        payee: payeeVal,
        bank: bankVal,
        voucher: voucherVal,
        amount: formattedAmount,
        status: 'Outstanding (In Transit)',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock',
        disb_officer: 'Active User (Treasury Officer)'
      };

      const tbody = document.querySelector('#checkTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'check-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-status', 'outstanding (in transit)');

        newRow.onclick = function() { openCheckDetailsModal(checkObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${chkNoVal}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td class="fw-semibold text-dark">${payeeVal}</td>
          <td><span class="fs-xs text-muted">${bankVal}</span></td>
          <td><span class="font-monospace text-primary">${voucherVal}</span></td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Outstanding (In Transit)</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Check Copy"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Check Copy"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openCheckDetailsModal(checkObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createCheckModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createCheckForm.reset();
      filterChecks();
    });
  }

  filterChecks();
});
</script>
@endpush
