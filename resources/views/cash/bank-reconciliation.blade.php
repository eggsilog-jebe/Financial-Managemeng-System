@extends('layouts.app')

@section('title', 'Bank Reconciliation - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-reconciliation')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Reconciliation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Statement Reconciliation</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Running auto-match rules on open statement lines...');"><i class="ph ph-magic-wand me-1"></i> Auto-Match Transactions</button>
      <button id="btnUploadStatement" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#uploadStatementModal"><i class="ph ph-file-arrow-up me-1"></i> Upload Bank Statement</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Bank Statement Ending Balance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Hospital Cash Book Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-book fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱4,850,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Reconciliation Variance</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Statement Match Rate</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100.0%</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="reconAccountSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Bank Account:</label>
          <select id="reconAccountSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Bank Accounts</option>
            <option value="metrobank">Metrobank Operating #1020</option>
            <option value="bdo">BDO Collections #2384</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="reconStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Match Status:</label>
          <select id="reconStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="matched">Matched</option>
            <option value="unmatched">Unmatched</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="reconSearchInput" class="form-control form-control-sm" placeholder="Search line item, ref, voucher...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="reconciliationTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Bank Statement Line Item</th>
              <th>System Voucher Reference</th>
              <th class="text-end">Bank Amount (₱)</th>
              <th class="text-end">Cash Book Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $reconciliations = [
                [
                  'date' => '2026-08-07',
                  'item' => 'DEP-2026-302 Armored Deposit',
                  'stamp' => 'Machine Teller Stamp #MB-STAMP-99210',
                  'ref' => 'DEP-2026-302',
                  'bank_amt' => '+₱125,400.00',
                  'cash_amt' => '+₱125,400.00',
                  'status' => 'Matched',
                  'status_badge' => 'bg-success-subtle text-success',
                  'account' => 'metrobank'
                ],
                [
                  'date' => '2026-08-06',
                  'item' => 'EFT Payout - General Hospital Supplier',
                  'stamp' => 'Electronic Clearance Ref #EFT-88912',
                  'ref' => 'CHK-2026-809',
                  'bank_amt' => '-₱45,000.00',
                  'cash_amt' => '-₱45,000.00',
                  'status' => 'Matched',
                  'status_badge' => 'bg-success-subtle text-success',
                  'account' => 'metrobank'
                ],
                [
                  'date' => '2026-08-05',
                  'item' => 'Over-the-Counter Patient Cash Deposit',
                  'stamp' => 'Branch Teller Stamp #BDO-STAMP-1029',
                  'ref' => 'DEP-2026-301',
                  'bank_amt' => '+₱88,200.00',
                  'cash_amt' => '+₱88,200.00',
                  'status' => 'Matched',
                  'status_badge' => 'bg-success-subtle text-success',
                  'account' => 'bdo'
                ],
              ];
            @endphp

            @foreach($reconciliations as $rec)
            <tr class="recon-row" style="cursor: pointer;" data-account="{{ $rec['account'] }}" data-status="{{ strtolower($rec['status']) }}" onclick="openReconciliationDetailsModal({{ json_encode($rec) }})">
              <td class="font-monospace fs-xs">{{ $rec['date'] }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $rec['item'] }}</div>
                <span class="fs-xs text-muted">{{ $rec['stamp'] }}</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">{{ $rec['ref'] }}</span></td>
              <td class="text-end font-monospace fw-semibold">{{ $rec['bank_amt'] }}</td>
              <td class="text-end font-monospace fw-semibold">{{ $rec['cash_amt'] }}</td>
              <td><span class="badge {{ $rec['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $rec['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Match Audit" onclick="openReconciliationDetailsModal({{ json_encode($rec) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="reconSummaryText">Showing {{ count($reconciliations) }} Reconciled Line Items</span>
      <nav aria-label="Reconciliation Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Reconciliation Details (Executive Design) -->
<div class="modal fade" id="reconciliationDetailsModal" tabindex="-1" aria-labelledby="reconciliationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailReconRef">DEP-2026-302</span>
            <span class="badge bg-success-subtle text-success" id="detailReconStatus"><i class="ph ph-check-circle me-1"></i> Matched</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailReconItem">DEP-2026-302 Armored Deposit</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Bank Statement Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailReconBankAmt">+₱125,400.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">GL Cash Book Amount</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailReconCashAmt">+₱125,400.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-receipt me-1 text-primary"></i> Line Item Metadata</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Transaction Clearing Date</span>
              <span class="font-monospace fw-bold text-dark" id="detailReconDate">2026-08-07</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Bank Teller Reference</span>
              <span class="font-monospace text-muted" id="detailReconStamp">Machine Teller Stamp #MB-STAMP-99210</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Auto-Matching Rule Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Automated Reconciliation Rule:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Exact Amount &amp; Voucher Ref Match</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-RECON-2026-8801 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Line Item Reconciliation Voucher...');"><i class="ph ph-file-text me-1"></i> Export Voucher Audit</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Upload Bank Statement -->
<div class="modal fade" id="uploadStatementModal" tabindex="-1" aria-labelledby="uploadStatementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="uploadStatementModalLabel"><i class="ph ph-file-arrow-up me-2 text-primary"></i>Upload Electronic Bank Statement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Bank statement uploaded and auto-matched!'); bootstrap.Modal.getInstance(document.getElementById('uploadStatementModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Hospital Bank Account <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="1">Metrobank Main Branch (#1020-8841-99)</option>
              <option value="2">BDO Unibank Collections (#0091-2384-12)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Statement Ending Balance (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="4850000.00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Upload OFX / CSV / PDF Statement File <span class="text-danger">*</span></label>
            <input type="file" class="form-control form-control-sm" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Upload &amp; Run Auto-Match</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openReconciliationDetailsModal(rec) {
  if (!rec) return;

  document.getElementById('detailReconRef').textContent = rec.ref || 'DEP-000';
  document.getElementById('detailReconItem').textContent = rec.item || 'Statement Line';
  document.getElementById('detailReconDate').textContent = rec.date || '-';
  document.getElementById('detailReconStamp').textContent = rec.stamp || '-';
  document.getElementById('detailReconBankAmt').textContent = rec.bank_amt || '₱0.00';
  document.getElementById('detailReconCashAmt').textContent = rec.cash_amt || '₱0.00';

  const statusEl = document.getElementById('detailReconStatus');
  if (statusEl) {
    statusEl.textContent = rec.status;
    statusEl.className = 'badge ' + (rec.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('reconciliationDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('reconSearchInput');
  const accountSelect = document.getElementById('reconAccountSelect');
  const statusSelect = document.getElementById('reconStatusSelect');
  const summaryText = document.getElementById('reconSummaryText');
  const btnUploadStatement = document.getElementById('btnUploadStatement');

  if (btnUploadStatement) {
    btnUploadStatement.addEventListener('click', function() {
      const modalEl = document.getElementById('uploadStatementModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterReconciliations() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedAccount = accountSelect ? accountSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.recon-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowAccount = row.getAttribute('data-account') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchAccount = !selectedAccount || rowAccount.includes(selectedAccount);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchAccount && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Reconciled Line Item${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReconRow');
    const tbody = document.querySelector('#reconciliationTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReconRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No reconciliation lines found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterReconciliations);
    searchInput.addEventListener('keyup', filterReconciliations);
  }
  if (accountSelect) accountSelect.addEventListener('change', filterReconciliations);
  if (statusSelect) statusSelect.addEventListener('change', filterReconciliations);

  filterReconciliations();
});
</script>
@endpush
