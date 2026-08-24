@extends('layouts.app')

@section('title', 'Deposit Slips - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'deposit-slips')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Deposit Slips</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Batch Deposit Slips</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Downloading Armored Transit Manifest...');"><i class="ph ph-file-pdf me-1"></i> Download Manifest</button>
      <button id="btnCreateSlip" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createDepositSlipModal"><i class="ph ph-plus-circle me-1"></i> Create Deposit Slip</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Slips Prepared Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-path fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">3 Batch Slips</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cash for Vault Pickup</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱140,400.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Checks Pending Clearance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱75,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">In-Transit Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-truck fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Armored Pickup</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="bankSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Target Bank:</label>
          <select id="bankSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Bank Destinations</option>
            <option value="metrobank">Metrobank - Main</option>
            <option value="bdo">BDO Unibank - Collections</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="transitStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Transit Status:</label>
          <select id="transitStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Transit Statuses</option>
            <option value="ready">Ready for Transport</option>
            <option value="deposited">Deposited at Branch</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="slipSearchInput" class="form-control form-control-sm" placeholder="Search slip ref, bank, account...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="slipTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Slip Ref</th>
              <th>Batch Date</th>
              <th>Source Remittances</th>
              <th>Target Bank Account</th>
              <th class="text-end">Cash Amount (₱)</th>
              <th class="text-end">Check Amount (₱)</th>
              <th class="text-end">Total Deposit (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($slips ?? [] as $s)
            @php
              $sArr = is_array($s) ? $s : [
                'ref' => $s->slip_number ?? 'SLIP-N/A', 'date' => $s->slip_date ? $s->slip_date->format('Y-m-d') : 'N/A',
                'sources' => $s->terminal_sources ?? 'N/A', 'bank' => $s->bank_name ?? 'N/A',
                'acc' => $s->account_number ?? 'N/A',
                'cash' => '₱' . number_format($s->cash_amount ?? 0, 2),
                'check' => '₱' . number_format($s->check_amount ?? 0, 2),
                'total' => '₱' . number_format(($s->cash_amount ?? 0) + ($s->check_amount ?? 0), 2),
                'status' => $s->status ?? 'Pending', 'status_badge' => 'bg-warning-subtle text-warning',
                'status_icon' => 'ph-clock', 'bag_seal' => $s->bag_seal ?? 'N/A',
              ];
            @endphp
            <tr class="slip-row" style="cursor: pointer;" data-bank="{{ strtolower($sArr['bank']) }}" data-status="{{ strtolower($sArr['status']) }}" onclick="openSlipDetailsModal({{ json_encode($sArr) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $sArr['ref'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $sArr['date'] }}</td>
              <td><span class="badge bg-light text-dark border">{{ $sArr['sources'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $sArr['bank'] }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $sArr['acc'] }}</span>
              </td>
              <td class="text-end font-monospace">{{ $sArr['cash'] }}</td>
              <td class="text-end font-monospace">{{ $sArr['check'] }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $sArr['total'] }}</td>
              <td><span class="badge {{ $sArr['status_badge'] }}"><i class="ph {{ $sArr['status_icon'] }} me-1"></i> {{ $sArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Slip Details" onclick="openSlipDetailsModal({{ json_encode($sArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No deposit slips recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="slipSummaryText">Showing {{ count($slips ?? []) }} Batch Slips</span>
      <nav aria-label="Deposit Slip Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Deposit Slip Details (Executive Design) -->
<div class="modal fade" id="slipDetailsModal" tabindex="-1" aria-labelledby="slipDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailSlipRef">SLIP-2026-081</span>
            <span class="badge bg-primary-subtle text-primary" id="detailSlipStatus"><i class="ph ph-truck me-1"></i> Ready for Transport</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailSlipBank">Metrobank - Main</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Deposit Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailSlipTotal">₱45,200.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Batch Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailSlipDate">2026-08-08</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-bank me-1 text-primary"></i> Target Account &amp; Remittances</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Target Account Number</span>
              <span class="font-monospace fw-bold text-dark" id="detailSlipAcc">1020-8841-99</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Source Remittance Terminals</span>
              <span class="badge bg-light text-dark border" id="detailSlipSources">TERM-01, TERM-02</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Vault Security Seal Tag</span>
              <span class="font-monospace fw-bold text-primary" id="detailSlipSeal">SEAL-BAG-99201</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Armored Transit Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Armored Courier Service:</span>
              <span class="fw-semibold text-dark"><i class="ph ph-shield me-1 text-primary"></i> Secure-Way Armored Logistics (ID #9901)</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bank Machine Verification:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Teller Machine Stamp Verified</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Log:</span>
              <span class="font-monospace text-muted">LOG-SLIP-2026-081 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Deposit Slip PDF...');"><i class="ph ph-printer me-1"></i> Print Deposit Slip</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Deposit Slip -->
<div class="modal fade" id="createDepositSlipModal" tabindex="-1" aria-labelledby="createDepositSlipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createDepositSlipModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Batch Deposit Slip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="depositSlipForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Hospital Bank Account <span class="text-danger">*</span></label>
              <select id="modalSlipBank" class="form-select form-select-sm" required>
                <option value="Metrobank - Main">Metrobank Main (Acc #1020-8841-99)</option>
                <option value="BDO Unibank - Collections">BDO Collections (Acc #0091-2384-12)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Armored Pickup Date <span class="text-danger">*</span></label>
              <input type="date" id="modalSlipDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Cash Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalSlipCash" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="50000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Check Amount (₱)</label>
              <input type="number" id="modalSlipCheck" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="15000.00">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Vault Security Seal Tag Number <span class="text-danger">*</span></label>
              <input type="text" id="modalSlipSeal" class="form-control form-control-sm font-monospace" placeholder="e.g. SEAL-BAG-99202" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Generate Deposit Slip</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openSlipDetailsModal(s) {
  if (!s) return;

  document.getElementById('detailSlipRef').textContent = s.ref || 'SLIP-000';
  document.getElementById('detailSlipBank').textContent = s.bank || 'Bank Name';
  document.getElementById('detailSlipAcc').textContent = s.acc || '-';
  document.getElementById('detailSlipSources').textContent = s.sources || '-';
  document.getElementById('detailSlipTotal').textContent = s.total || '₱0.00';
  document.getElementById('detailSlipDate').textContent = s.date || '-';
  document.getElementById('detailSlipSeal').textContent = s.bag_seal || 'SEAL-BAG-000';

  const statusEl = document.getElementById('detailSlipStatus');
  if (statusEl) {
    statusEl.textContent = s.status;
    statusEl.className = 'badge ' + (s.status_badge || 'bg-primary-subtle text-primary');
  }

  const modalEl = document.getElementById('slipDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('slipSearchInput');
  const bankSelect = document.getElementById('bankSelect');
  const transitStatusSelect = document.getElementById('transitStatusSelect');
  const summaryText = document.getElementById('slipSummaryText');
  const btnCreateSlip = document.getElementById('btnCreateSlip');

  if (btnCreateSlip) {
    btnCreateSlip.addEventListener('click', function() {
      const modalEl = document.getElementById('createDepositSlipModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterSlips() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedBank = bankSelect ? bankSelect.value.toLowerCase() : '';
    const selectedStatus = transitStatusSelect ? transitStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.slip-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowBank = row.getAttribute('data-bank') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchBank = !selectedBank || rowBank.includes(selectedBank);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchBank && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Batch Slip${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noSlipRow');
    const tbody = document.querySelector('#slipTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noSlipRow';
        emptyRow.innerHTML = `<td colspan="9" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No deposit slips found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterSlips);
    searchInput.addEventListener('keyup', filterSlips);
  }
  if (bankSelect) bankSelect.addEventListener('change', filterSlips);
  if (transitStatusSelect) transitStatusSelect.addEventListener('change', filterSlips);

  const depositSlipForm = document.getElementById('depositSlipForm');
  if (depositSlipForm) {
    depositSlipForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const bankVal = document.getElementById('modalSlipBank').value;
      const dateVal = document.getElementById('modalSlipDate').value;
      const rawCash = parseFloat(document.getElementById('modalSlipCash').value || 0);
      const rawCheck = parseFloat(document.getElementById('modalSlipCheck').value || 0);
      const formattedCash = '₱' + rawCash.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedCheck = '₱' + rawCheck.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedTotal = '₱' + (rawCash + rawCheck).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const sealVal = document.getElementById('modalSlipSeal').value || 'SEAL-NEW-001';
      const nextRef = 'SLIP-2026-' + Math.floor(82 + Math.random() * 20);

      let accNum = '1020-8841-99';
      if (bankVal.includes('BDO')) accNum = '0091-2384-12';

      const slipObj = {
        ref: nextRef,
        date: dateVal,
        sources: 'TERM-01, TERM-02',
        bank: bankVal,
        acc: accNum,
        cash: formattedCash,
        check: formattedCheck,
        total: formattedTotal,
        status: 'Ready for Transport',
        status_badge: 'bg-primary-subtle text-primary',
        status_icon: 'ph-truck',
        bag_seal: sealVal
      };

      const tbody = document.querySelector('#slipTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'slip-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-bank', bankVal.toLowerCase());
        newRow.setAttribute('data-status', 'ready for transport');

        newRow.onclick = function() { openSlipDetailsModal(slipObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td><span class="badge bg-light text-dark border">TERM-01, TERM-02</span></td>
          <td>
            <div class="fw-semibold text-dark">${bankVal}</div>
            <span class="fs-xs font-monospace text-muted">${accNum}</span>
          </td>
          <td class="text-end font-monospace">${formattedCash}</td>
          <td class="text-end font-monospace">${formattedCheck}</td>
          <td class="text-end text-success fw-bold font-monospace">${formattedTotal}</td>
          <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-truck me-1"></i> Ready for Transport</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Slip Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Slip Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openSlipDetailsModal(slipObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createDepositSlipModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      depositSlipForm.reset();
      filterSlips();
    });
  }

  filterSlips();
});
</script>
@endpush
