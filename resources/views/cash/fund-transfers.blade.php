@extends('layouts.app')

@section('title', 'Fund Transfers - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'fund-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Fund Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Inter-Account Bank Fund Transfers</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Transfer Audit Log...');"><i class="ph ph-file-arrow-down me-1"></i> Transfer Log PDF</button>
      <button id="btnNewTransfer" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newTransferModal"><i class="ph ph-arrows-left-right me-1"></i> New Fund Transfer</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Inter-Account Transfers (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrows-left-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">8 Transfers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Transfer Volume</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱3,450,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Approvals</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Pending</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Bank Wire Fees</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱200.00</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="transferSourceSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Source Bank:</label>
          <select id="transferSourceSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Source Banks</option>
            <option value="bdo">BDO Collections</option>
            <option value="metrobank">Metrobank Operating</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="transferStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="transferStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="completed">Completed &amp; Posted</option>
            <option value="pending">Pending Approval</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="transferSearchInput" class="form-control form-control-sm" placeholder="Search transfer ref, source, target...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="transferTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Transfer Ref</th>
              <th>Source Account (From)</th>
              <th>Destination Account (To)</th>
              <th class="text-end">Transfer Amount (₱)</th>
              <th>Transfer Date</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $transfers = [
                [
                  'ref' => 'TRF-2026-088',
                  'from' => 'BDO Collections',
                  'from_no' => '#0091-2384-12',
                  'to' => 'Metrobank Operating',
                  'to_no' => '#1020-8841-99',
                  'amount' => '₱500,000.00',
                  'date' => '2026-08-07',
                  'status' => 'Completed & Posted',
                  'status_badge' => 'bg-success-subtle text-success',
                  'method' => 'PESONet Interbank Transfer'
                ],
                [
                  'ref' => 'TRF-2026-087',
                  'from' => 'Metrobank Operating',
                  'from_no' => '#1020-8841-99',
                  'to' => 'BPI Payroll Account',
                  'to_no' => '#0012-4412-00',
                  'amount' => '₱850,000.00',
                  'date' => '2026-08-06',
                  'status' => 'Pending Treasury Approval',
                  'status_badge' => 'bg-warning-subtle text-warning',
                  'method' => 'Internal Bank Clearing'
                ],
              ];
            @endphp

            @foreach($transfers as $t)
            <tr class="transfer-row" style="cursor: pointer;" data-source="{{ strtolower($t['from']) }}" data-status="{{ strtolower($t['status']) }}" onclick="openFundTransferDetailsModal({{ json_encode($t) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $t['ref'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $t['from'] }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $t['from_no'] }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $t['to'] }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $t['to_no'] }}</span>
              </td>
              <td class="text-end text-success fw-bold font-monospace">{{ $t['amount'] }}</td>
              <td class="font-monospace fs-xs">{{ $t['date'] }}</td>
              <td><span class="badge {{ $t['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $t['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Transfer Audit" onclick="openFundTransferDetailsModal({{ json_encode($t) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="transferSummaryText">Showing {{ count($transfers) }} Fund Transfers</span>
      <nav aria-label="Transfer Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Fund Transfer Details (Executive Design) -->
<div class="modal fade" id="transferDetailsModal" tabindex="-1" aria-labelledby="transferDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailTrfRef">TRF-2026-088</span>
            <span class="badge bg-success-subtle text-success" id="detailTrfStatus"><i class="ph ph-check-circle me-1"></i> Completed &amp; Posted</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0">Inter-Bank Fund Transfer</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Transfer Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailTrfAmount">₱500,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Transfer Method</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailTrfMethod">PESONet Interbank Transfer</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-arrows-left-right me-1 text-primary"></i> Source &amp; Destination Accounts</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Source Account (From)</span>
              <span class="font-monospace fw-bold text-dark" id="detailTrfFrom">BDO Collections (#0091-2384-12)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Destination Account (To)</span>
              <span class="font-monospace fw-bold text-primary" id="detailTrfTo">Metrobank Operating (#1020-8841-99)</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Transfer Execution Date</span>
              <span class="font-monospace text-muted" id="detailTrfDate">2026-08-07</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Treasury Release Authorization</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Treasury Release Authorization:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Authorized by Treasury Manager</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-TRF-2026-088 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Fund Transfer Receipt...');"><i class="ph ph-file-text me-1"></i> Export Receipt PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Bank Fund Transfer -->
<div class="modal fade" id="newTransferModal" tabindex="-1" aria-labelledby="newTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newTransferModalLabel"><i class="ph ph-arrows-left-right me-2 text-primary"></i>Execute Inter-Account Fund Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="newTransferForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Bank Account (From) <span class="text-danger">*</span></label>
              <select id="modalTrfFrom" class="form-select form-select-sm" required>
                <option value="BDO Collections (#0091-2384-12)">BDO Collections (#0091-2384-12)</option>
                <option value="Metrobank Operating (#1020-8841-99)">Metrobank Operating (#1020-8841-99)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Destination Bank Account (To) <span class="text-danger">*</span></label>
              <select id="modalTrfTo" class="form-select form-select-sm" required>
                <option value="Metrobank Operating (#1020-8841-99)">Metrobank Operating (#1020-8841-99)</option>
                <option value="BPI Payroll Account (#0012-4412-00)">BPI Payroll Account (#0012-4412-00)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalTrfAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="250000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Method</label>
              <select id="modalTrfMethod" class="form-select form-select-sm">
                <option value="Internal Bank Clearing">Internal Bank Clearing (Same Day)</option>
                <option value="PESONet Interbank Transfer">PESONet Electronic Transfer</option>
                <option value="InstaPay Real-Time">InstaPay Real-Time</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-paper-plane-tilt me-1"></i> Execute Transfer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openFundTransferDetailsModal(t) {
  if (!t) return;

  document.getElementById('detailTrfRef').textContent = t.ref || 'TRF-000';
  document.getElementById('detailTrfFrom').textContent = (t.from || 'Source') + ' (' + (t.from_no || '') + ')';
  document.getElementById('detailTrfTo').textContent = (t.to || 'Target') + ' (' + (t.to_no || '') + ')';
  document.getElementById('detailTrfAmount').textContent = t.amount || '₱0.00';
  document.getElementById('detailTrfDate').textContent = t.date || '-';
  document.getElementById('detailTrfMethod').textContent = t.method || 'Internal Transfer';

  const statusEl = document.getElementById('detailTrfStatus');
  if (statusEl) {
    statusEl.textContent = t.status;
    statusEl.className = 'badge ' + (t.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('transferDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('transferSearchInput');
  const sourceSelect = document.getElementById('transferSourceSelect');
  const statusSelect = document.getElementById('transferStatusSelect');
  const summaryText = document.getElementById('transferSummaryText');
  const btnNewTransfer = document.getElementById('btnNewTransfer');

  if (btnNewTransfer) {
    btnNewTransfer.addEventListener('click', function() {
      const modalEl = document.getElementById('newTransferModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterTransfers() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedSource = sourceSelect ? sourceSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.transfer-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowSource = row.getAttribute('data-source') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchSource = !selectedSource || rowSource.includes(selectedSource);
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
      summaryText.textContent = `Showing ${visibleCount} Fund Transfer${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noTransferRow');
    const tbody = document.querySelector('#transferTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTransferRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No fund transfers found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTransfers);
    searchInput.addEventListener('keyup', filterTransfers);
  }
  if (sourceSelect) sourceSelect.addEventListener('change', filterTransfers);
  if (statusSelect) statusSelect.addEventListener('change', filterTransfers);

  const newTransferForm = document.getElementById('newTransferForm');
  if (newTransferForm) {
    newTransferForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const fromVal = document.getElementById('modalTrfFrom').value;
      const toVal = document.getElementById('modalTrfTo').value;
      const methodVal = document.getElementById('modalTrfMethod').value;
      const rawAmount = parseFloat(document.getElementById('modalTrfAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextRef = 'TRF-2026-' + Math.floor(90 + Math.random() * 10);
      const todayStr = new Date().toISOString().split('T')[0];

      const trfObj = {
        ref: nextRef,
        from: fromVal.split(' ')[0],
        from_no: fromVal.includes('#') ? '#' + fromVal.split('#')[1] : '',
        to: toVal.split(' ')[0],
        to_no: toVal.includes('#') ? '#' + toVal.split('#')[1] : '',
        amount: formattedAmount,
        date: todayStr,
        status: 'Completed & Posted',
        status_badge: 'bg-success-subtle text-success',
        method: methodVal
      };

      const tbody = document.querySelector('#transferTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'transfer-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-source', fromVal.toLowerCase());
        newRow.setAttribute('data-status', 'completed & posted');

        newRow.onclick = function() { openFundTransferDetailsModal(trfObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td>
            <div class="fw-semibold text-dark">${trfObj.from}</div>
            <span class="fs-xs font-monospace text-muted">${trfObj.from_no}</span>
          </td>
          <td>
            <div class="fw-semibold text-dark">${trfObj.to}</div>
            <span class="fs-xs font-monospace text-muted">${trfObj.to_no}</span>
          </td>
          <td class="text-end text-success fw-bold font-monospace">${formattedAmount}</td>
          <td class="font-monospace fs-xs">${todayStr}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Completed &amp; Posted</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Transfer Audit"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Transfer Audit"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openFundTransferDetailsModal(trfObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('newTransferModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      newTransferForm.reset();
      filterTransfers();
    });
  }

  filterTransfers();
});
</script>
@endpush
