@extends('layouts.app')

@section('title', 'Bank Deposits - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'bank-deposits')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Bank Deposits</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Deposits Log &amp; Verification</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Bank Deposit Audit...');"><i class="ph ph-file-arrow-down me-1"></i> Export Deposit Audit</button>
      <button id="btnVerifyDeposit" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#verifyDepositModal"><i class="ph ph-check-circle me-1"></i> Record Bank Validation</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Confirmed Deposits (Month)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,842,500.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Verification</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Deposit</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Deposit Discrepancies</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">GL Reconciliation Match</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
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
          <label for="bankAccSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Bank Account:</label>
          <select id="bankAccSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Hospital Accounts</option>
            <option value="metrobank">Metrobank Operating</option>
            <option value="bdo">BDO Collections</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="verificationSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Verification:</label>
          <select id="verificationSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="verified by bank">Verified by Bank</option>
            <option value="pending">Pending Teller Verification</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="bankDepositSearchInput" class="form-control form-control-sm" placeholder="Search deposit ref, stamp, bank...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="bankDepositTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Deposit Ref</th>
              <th>Linked Batch Slip</th>
              <th>Bank Account</th>
              <th>Deposit Date</th>
              <th class="text-end">Amount Deposited (₱)</th>
              <th>Teller Stamp / Machine Ref</th>
              <th>Verification</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($deposits ?? [] as $d)
            @php
              $dArr = is_array($d) ? $d : [
                'ref' => $d->reference_number ?? 'DEP-N/A', 'slip' => $d->slip_reference ?? 'N/A',
                'bank' => $d->bank_name ?? 'N/A', 'acc' => $d->account_number ?? 'N/A',
                'date' => $d->deposit_date ? $d->deposit_date->format('Y-m-d H:i') : 'N/A',
                'amount' => '₱' . number_format($d->amount ?? 0, 2),
                'stamp' => $d->teller_stamp ?? 'N/A', 'status' => $d->status ?? 'Pending',
                'status_badge' => 'bg-warning-subtle text-warning', 'status_icon' => 'ph-clock',
              ];
            @endphp
            <tr class="bank-deposit-row" style="cursor: pointer;" data-bank="{{ strtolower($dArr['bank']) }}" data-status="{{ strtolower($dArr['status']) }}" onclick="openBankDepositDetailsModal({{ json_encode($dArr) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $dArr['ref'] }}</span></td>
              <td><span class="font-monospace text-muted fs-xs">{{ $dArr['slip'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $dArr['bank'] }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $dArr['acc'] }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $dArr['date'] }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $dArr['amount'] }}</td>
              <td><span class="font-monospace text-dark fs-xs">{{ $dArr['stamp'] }}</span></td>
              <td><span class="badge {{ $dArr['status_badge'] }}"><i class="ph {{ $dArr['status_icon'] }} me-1"></i> {{ $dArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Deposit Details" onclick="openBankDepositDetailsModal({{ json_encode($dArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No bank deposits recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="bankDepositSummaryText">Showing {{ count($deposits ?? []) }} Bank Deposits</span>
      <nav aria-label="Bank Deposit Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Bank Deposit Details (Executive Design) -->
<div class="modal fade" id="bankDepositDetailsModal" tabindex="-1" aria-labelledby="bankDepositDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailDepRef">DEP-2026-302</span>
            <span class="badge bg-success-subtle text-success" id="detailDepStatus"><i class="ph ph-check-circle me-1"></i> Verified by Bank</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailDepBank">Metrobank Operating</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Confirmed Deposited Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailDepAmount">₱125,400.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Deposit Date &amp; Time</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailDepDate">2026-08-07 15:30</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-bank me-1 text-primary"></i> Banking &amp; Slip Reference</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Target Account Number</span>
              <span class="font-monospace fw-bold text-dark" id="detailDepAcc">#1020-8841-99</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Linked Batch Deposit Slip</span>
              <span class="font-monospace fw-bold text-primary" id="detailDepSlip">SLIP-2026-080</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Bank Teller Machine Stamp</span>
              <span class="font-monospace fw-bold text-dark" id="detailDepStamp">MB-STAMP-99210</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; GL Cash Book Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">General Ledger Cash Match:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Cash Book Reconciled</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bank Statement Line ID:</span>
              <span class="font-monospace text-primary">STMT-2026-08-1029</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-DEP-2026-302 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Viewing Bank Machine Deposit Slip Photo Scan...');"><i class="ph ph-file-image me-1"></i> View Machine Slip Image</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Record / Verify Bank Deposit -->
<div class="modal fade" id="verifyDepositModal" tabindex="-1" aria-labelledby="verifyDepositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="verifyDepositModalLabel"><i class="ph ph-check-circle me-2 text-primary"></i>Record &amp; Verify Bank Deposit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="bankDepositForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Select Batch Deposit Slip <span class="text-danger">*</span></label>
              <select id="modalDepSlip" class="form-select form-select-sm" required>
                <option value="SLIP-2026-081">SLIP-2026-081 (₱45,200.00 - Metrobank)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Hospital Bank Account <span class="text-danger">*</span></label>
              <select id="modalDepBank" class="form-select form-select-sm" required>
                <option value="Metrobank Operating">Metrobank Operating (#1020-8841-99)</option>
                <option value="BDO Collections">BDO Collections (#0091-2384-12)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Bank Machine Stamp Ref <span class="text-danger">*</span></label>
              <input type="text" id="modalDepStamp" class="form-control form-control-sm font-monospace" placeholder="e.g. MB-STAMP-99211" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Confirmed Deposited Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalDepAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="45200.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Verify &amp; Match Cash Book</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openBankDepositDetailsModal(d) {
  if (!d) return;

  document.getElementById('detailDepRef').textContent = d.ref || 'DEP-000';
  document.getElementById('detailDepBank').textContent = d.bank || 'Bank Name';
  document.getElementById('detailDepAcc').textContent = d.acc || '-';
  document.getElementById('detailDepSlip').textContent = d.slip || '-';
  document.getElementById('detailDepAmount').textContent = d.amount || '₱0.00';
  document.getElementById('detailDepDate').textContent = d.date || '-';
  document.getElementById('detailDepStamp').textContent = d.stamp || '-';

  const statusEl = document.getElementById('detailDepStatus');
  if (statusEl) {
    statusEl.textContent = d.status;
    statusEl.className = 'badge ' + (d.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('bankDepositDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('bankDepositSearchInput');
  const bankAccSelect = document.getElementById('bankAccSelect');
  const verificationSelect = document.getElementById('verificationSelect');
  const summaryText = document.getElementById('bankDepositSummaryText');
  const btnVerifyDeposit = document.getElementById('btnVerifyDeposit');

  if (btnVerifyDeposit) {
    btnVerifyDeposit.addEventListener('click', function() {
      const modalEl = document.getElementById('verifyDepositModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterBankDeposits() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedBank = bankAccSelect ? bankAccSelect.value.toLowerCase() : '';
    const selectedVerification = verificationSelect ? verificationSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.bank-deposit-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowBank = row.getAttribute('data-bank') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchBank = !selectedBank || rowBank.includes(selectedBank);
      const matchStatus = !selectedVerification || rowStatus.includes(selectedVerification);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchBank && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Bank Deposit${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noBankDepositRow');
    const tbody = document.querySelector('#bankDepositTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noBankDepositRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No bank deposit records found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterBankDeposits);
    searchInput.addEventListener('keyup', filterBankDeposits);
  }
  if (bankAccSelect) bankAccSelect.addEventListener('change', filterBankDeposits);
  if (verificationSelect) verificationSelect.addEventListener('change', filterBankDeposits);

  const bankDepositForm = document.getElementById('bankDepositForm');
  if (bankDepositForm) {
    bankDepositForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const slipVal = document.getElementById('modalDepSlip').value;
      const bankVal = document.getElementById('modalDepBank').value;
      const stampVal = document.getElementById('modalDepStamp').value;
      const rawAmount = parseFloat(document.getElementById('modalDepAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextRef = 'DEP-2026-' + Math.floor(303 + Math.random() * 20);

      let accNum = '#1020-8841-99';
      if (bankVal.includes('BDO')) accNum = '#0091-2384-12';

      const depositObj = {
        ref: nextRef,
        slip: slipVal,
        bank: bankVal,
        acc: accNum,
        date: "{{ date('Y-m-d H:i') }}",
        amount: formattedAmount,
        stamp: stampVal,
        status: 'Verified by Bank',
        status_badge: 'bg-success-subtle text-success',
        status_icon: 'ph-check-circle'
      };

      const tbody = document.querySelector('#bankDepositTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'bank-deposit-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-bank', bankVal.toLowerCase());
        newRow.setAttribute('data-status', 'verified by bank');

        newRow.onclick = function() { openBankDepositDetailsModal(depositObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td><span class="font-monospace text-muted fs-xs">${slipVal}</span></td>
          <td>
            <div class="fw-semibold text-dark">${bankVal}</div>
            <span class="fs-xs font-monospace text-muted">${accNum}</span>
          </td>
          <td class="font-monospace fs-xs">${depositObj.date}</td>
          <td class="text-end text-success fw-bold font-monospace">${formattedAmount}</td>
          <td><span class="font-monospace text-dark fs-xs">${stampVal}</span></td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Verified by Bank</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Deposit Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Deposit Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openBankDepositDetailsModal(depositObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('verifyDepositModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      bankDepositForm.reset();
      filterBankDeposits();
    });
  }

  filterBankDeposits();
});
</script>
@endpush
