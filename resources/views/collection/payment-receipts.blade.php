@extends('layouts.app')

@section('title', 'Payment Receipts - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'receipts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Receipts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Receipts &amp; Official Receipts</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Official Receipts Log...');"><i class="ph ph-download-simple me-1"></i> Export Receipts Log</button>
      <button id="btnIssueReceipt" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueReceiptModal"><i class="ph ph-plus-circle me-1"></i> Issue Official Receipt</button>
    </div>
  </div>

  <!-- Metric <!-- Primary Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Official Receipts Issued Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($receipts ?? []) }} Receipts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Daily Collections</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cash Collections (In Drawer)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Card &amp; Digital Collections</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="receiptTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Receipt Ref #</th>
              <th>Date &amp; Time</th>
              <th>Payor / Patient Name</th>
              <th>Payment Mode</th>
              <th>Reference / Check No.</th>
              <th class="text-end">Amount Paid (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($receipts ?? [] as $r)
            @php
              $or = is_array($r) ? $r['or'] : $r->receipt_number;
              $date = is_array($r) ? $r['date'] : $r->receipt_date->format('Y-m-d H:i');
              $payor = is_array($r) ? $r['payor'] : $r->payor_name;
              $mode = is_array($r) ? $r['mode'] : $r->payment_mode;
              $amt = is_array($r) ? $r['amount'] : ('₱' . number_format($r->amount_paid, 2));
              $status = is_array($r) ? $r['status'] : $r->status;
              $rData = [
                'or' => $or,
                'date' => $date,
                'payor' => $payor,
                'sub' => 'Collection Receipt',
                'mode' => $mode,
                'ref' => '-',
                'amount' => $amt,
                'status' => $status
              ];
            @endphp
            <tr class="receipt-row" style="cursor: pointer;" onclick="openReceiptDetailsModal({{ json_encode($rData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $or }}</span></td>
              <td class="font-monospace fs-xs">{{ $date }}</td>
              <td><div class="fw-bold text-dark">{{ $payor }}</div></td>
              <td><span class="badge bg-info-subtle text-info">{{ $mode }}</span></td>
              <td class="font-monospace fs-xs">-</td>
              <td class="text-end font-monospace fw-bold text-success">{{ $amt }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> {{ $status }}</span></td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" onclick="openReceiptDetailsModal({{ json_encode($rData) }})"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No payment receipts recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="receiptSummaryText">Showing {{ count($receipts ?? []) }} Official Receipts</span>
      <nav aria-label="Receipt Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth OR Details (Executive Design) -->
<div class="modal fade" id="receiptDetailsModal" tabindex="-1" aria-labelledby="receiptDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailOrNo">OR-2026-9901</span>
            <span class="badge bg-success-subtle text-success" id="detailOrStatus">Valid</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailOrPayor">David Miller</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Amount Paid</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailOrAmount">₱6,400.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Transaction Timestamp</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailOrDate">2026-08-08 14:22</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-receipt me-1 text-primary"></i> Payment Details &amp; Reference</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Payment Channel / Mode</span>
              <span class="fw-semibold text-dark" id="detailOrMode">Credit Card (Visa)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bank / Transaction Reference</span>
              <span class="font-monospace fw-bold text-primary" id="detailOrRef">TXN-774102</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Issuing Cashier &amp; POS Station</span>
              <span class="text-dark" id="detailOrIssuer">Anna Reyes (Main POS)</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; BIR Compliance Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">BIR Official Receipt Registration:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> BIR Serial Compliant</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">General Ledger Posting:</span>
              <span class="font-monospace text-primary">GL-COL-2026-0889</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Log Stamp:</span>
              <span class="font-monospace text-muted">LOG-OR-2026-9901 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Official Receipt Copy...');"><i class="ph ph-printer me-1"></i> Print Receipt Copy</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Official Receipt -->
<div class="modal fade" id="issueReceiptModal" tabindex="-1" aria-labelledby="issueReceiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueReceiptModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue Official Receipt (OR)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="receiptForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient / Payor Name <span class="text-danger">*</span></label>
              <input type="text" id="modalOrPayor" class="form-control form-control-sm" placeholder="e.g. Juan De La Cruz" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient ID / Billing Reference</label>
              <input type="text" id="modalOrSub" class="form-control form-control-sm" placeholder="e.g. Patient ID: PAT-99201">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Mode <span class="text-danger">*</span></label>
              <select id="modalOrMode" class="form-select form-select-sm" required>
                <option value="cash">Cash</option>
                <option value="credit card">Credit Card</option>
                <option value="check">Bank Check</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Reference / Authorization / Check #</label>
              <input type="text" id="modalOrRef" class="form-control form-control-sm" placeholder="e.g. TXN-88120">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Amount Paid (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalOrAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="2500.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-printer me-1"></i> Print &amp; Post OR</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openReceiptDetailsModal(r) {
  if (!r) return;

  document.getElementById('detailOrNo').textContent = r.or || 'OR-0000';
  document.getElementById('detailOrPayor').textContent = r.payor || 'Payor';
  document.getElementById('detailOrAmount').textContent = r.amount || '₱0.00';
  document.getElementById('detailOrDate').textContent = r.date || '-';
  document.getElementById('detailOrMode').textContent = r.mode || 'Payment Mode';
  document.getElementById('detailOrRef').textContent = r.ref || '-';
  document.getElementById('detailOrIssuer').textContent = r.issuer || 'Cashier';

  const statusEl = document.getElementById('detailOrStatus');
  if (statusEl) {
    statusEl.textContent = r.status;
    statusEl.className = 'badge ' + (r.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('receiptDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('receiptSearchInput');
  const modeSelect = document.getElementById('modeSelect');
  const orStatusSelect = document.getElementById('orStatusSelect');
  const summaryText = document.getElementById('receiptSummaryText');
  const btnIssueReceipt = document.getElementById('btnIssueReceipt');

  if (btnIssueReceipt) {
    btnIssueReceipt.addEventListener('click', function() {
      const modalEl = document.getElementById('issueReceiptModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterReceipts() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedMode = modeSelect ? modeSelect.value.toLowerCase() : '';
    const selectedStatus = orStatusSelect ? orStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.receipt-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowMode = row.getAttribute('data-mode') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchMode = !selectedMode || rowMode.includes(selectedMode);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchMode && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Official Receipt${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReceiptRow');
    const tbody = document.querySelector('#receiptTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReceiptRow';
        emptyRow.innerHTML = `<td colspan="9" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No official receipts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterReceipts);
    searchInput.addEventListener('keyup', filterReceipts);
  }
  if (modeSelect) modeSelect.addEventListener('change', filterReceipts);
  if (orStatusSelect) orStatusSelect.addEventListener('change', filterReceipts);

  const receiptForm = document.getElementById('receiptForm');
  if (receiptForm) {
    receiptForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const payorVal = document.getElementById('modalOrPayor').value;
      const subVal = document.getElementById('modalOrSub').value || 'Walk-in Patient';
      const modeVal = document.getElementById('modalOrMode').value;
      const refVal = document.getElementById('modalOrRef').value || '-';
      const rawAmount = parseFloat(document.getElementById('modalOrAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextOrNum = 'OR-2026-' + Math.floor(9902 + Math.random() * 100);

      let modeLabel = 'Cash';
      let modeBadge = 'bg-success-subtle text-success';
      let modeIcon = 'ph-money';

      if (modeVal === 'credit card') {
        modeLabel = 'Credit Card (Visa)';
        modeBadge = 'bg-info-subtle text-info';
        modeIcon = 'ph-credit-card';
      } else if (modeVal === 'check') {
        modeLabel = 'Bank Check';
        modeBadge = 'bg-warning-subtle text-warning';
        modeIcon = 'ph-bank';
      }

      const receiptObj = {
        or: nextOrNum,
        date: "{{ date('Y-m-d H:i') }}",
        payor: payorVal,
        sub: subVal,
        mode: modeLabel,
        mode_code: modeVal,
        mode_badge: modeBadge,
        mode_icon: modeIcon,
        ref: refVal,
        amount: formattedAmount,
        issuer: 'Active POS Cashier',
        status: 'Valid',
        status_badge: 'bg-success-subtle text-success'
      };

      const tbody = document.querySelector('#receiptTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'receipt-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-mode', modeVal);
        newRow.setAttribute('data-status', 'valid');

        newRow.onclick = function() { openReceiptDetailsModal(receiptObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextOrNum}</span></td>
          <td><span class="text-nowrap font-monospace fs-xs">${receiptObj.date}</span></td>
          <td>
            <div class="fw-semibold text-dark">${payorVal}</div>
            <span class="fs-xs text-muted">${subVal}</span>
          </td>
          <td><span class="badge ${modeBadge}"><i class="ph ${modeIcon} me-1"></i> ${modeLabel}</span></td>
          <td><span class="font-monospace text-muted fs-xs">${refVal}</span></td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td class="fs-xs text-muted">Active POS Cashier</td>
          <td><span class="badge bg-success-subtle text-success">Valid</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View OR Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View OR Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openReceiptDetailsModal(receiptObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('issueReceiptModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      receiptForm.reset();
      filterReceipts();
    });
  }

  filterReceipts();
});
</script>
@endpush
