@extends('layouts.app')

@section('title', 'EFT Transfers - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'eft-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">EFT Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Electronic Funds Transfer (EFT / Wire)</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting EFT Remittance Files...');"><i class="ph ph-file-arrow-down me-1"></i> Export EFT Log</button>
      <button id="btnCreateEft" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createEftModal"><i class="ph ph-plus me-1"></i> New Bank Transfer Batch</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Bi-Weekly Staff Payroll Batches</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-users-three fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱410,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Supplier Digital Transfers</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱850,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Processing Success Rate</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Settled</h4>
      </div>
    </div>
  </div>

  <!-- EFT Transfers Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="eftTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Transfer Type:</label>
          <select id="eftTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Transfer Types</option>
            <option value="payroll">Direct Payroll Batch</option>
            <option value="pesonet">PESONet Supplier Wire</option>
          </select>
        </div>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="eftSearchInput" class="form-control form-control-sm" placeholder="Search transfer ref, bank, particulars...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="eftTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Transfer Ref</th>
              <th>Transfer Type</th>
              <th>Recipient Bank &amp; Account</th>
              <th>Particulars / Purpose</th>
              <th>Execution Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Settlement Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($efts ?? [] as $e)
            @php
              $eArr = is_array($e) ? $e : [
                'ref' => $e->reference_number ?? 'EFT-N/A',
                'type' => $e->transfer_type ?? 'EFT',
                'type_code' => strtolower($e->transfer_type ?? 'eft'),
                'badge' => 'bg-primary-subtle text-primary',
                'bank' => $e->bank_name ?? 'N/A',
                'purpose' => $e->purpose ?? 'N/A',
                'date' => $e->transfer_date ? $e->transfer_date->format('Y-m-d') : 'N/A',
                'amount' => '₱' . number_format($e->amount ?? 0, 2),
                'status' => $e->status ?? 'Pending',
                'status_badge' => 'bg-warning-subtle text-warning',
                'status_icon' => 'ph-clock',
                'bank_ref' => $e->bank_reference ?? 'N/A',
              ];
            @endphp
            <tr class="eft-row" style="cursor: pointer;" data-type="{{ $eArr['type_code'] }}" onclick="openEftDetailsModal({{ json_encode($eArr) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $eArr['ref'] }}</span></td>
              <td><span class="badge {{ $eArr['badge'] }}">{{ $eArr['type'] }}</span></td>
              <td class="fs-xs fw-semibold text-dark">{{ $eArr['bank'] }}</td>
              <td><span class="text-truncate d-inline-block" style="max-width: 250px;">{{ $eArr['purpose'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $eArr['date'] }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $eArr['amount'] }}</td>
              <td><span class="badge {{ $eArr['status_badge'] }}"><i class="ph {{ $eArr['status_icon'] }} me-1"></i> {{ $eArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Remittance File" onclick="openEftDetailsModal({{ json_encode($eArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No EFT transfers recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="eftSummaryText">Showing {{ count($efts ?? []) }} Bank Transfers</span>
      <nav aria-label="EFT Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth EFT Details (Executive Design) -->
<div class="modal fade" id="eftDetailsModal" tabindex="-1" aria-labelledby="eftDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailEftRef">EFT-2026-901</span>
            <span class="badge bg-primary-subtle text-primary" id="detailEftType">Direct Payroll Batch</span>
            <span class="badge bg-success-subtle text-success" id="detailEftStatus"><i class="ph ph-check-circle me-1"></i> Settled / Transferred</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailEftBank">BDO Unibank (**** 4819)</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Electronic Transfer Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailEftAmount">₱410,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Execution Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailEftDate">2026-08-05</h4>
            </div>
          </div>
        </div>

        <!-- Particulars & Wire Info -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Remittance Particulars &amp; Purpose</h6>
          <p class="small text-muted mb-0 lh-base" id="detailEftPurpose">Bi-Weekly Medical Staff &amp; Nurse Payroll Direct Deposit</p>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Transparency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bank Settlement Trace Reference:</span>
              <span class="font-monospace fw-bold text-primary" id="detailEftBankRef">BDO-WIRE-889104</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Gateway Authorization:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> API Direct Clearing Confirmed</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Log Stamp:</span>
              <span class="font-monospace text-muted">LOG-EFT-2026-901 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Downloading Bank Remittance File...');"><i class="ph ph-download-simple me-1"></i> Download Remittance Advice</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Bank Transfer Batch -->
<div class="modal fade" id="createEftModal" tabindex="-1" aria-labelledby="createEftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createEftModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Bank Transfer Batch (EFT / Wire)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createEftForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalEftRef" class="form-control form-control-sm font-monospace" placeholder="e.g. EFT-2026-903" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Category <span class="text-danger">*</span></label>
              <select id="modalEftType" class="form-select form-select-sm" required>
                <option value="payroll">Direct Payroll Batch</option>
                <option value="pesonet">PESONet Supplier Wire</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Recipient Bank &amp; Account <span class="text-danger">*</span></label>
              <input type="text" id="modalEftBank" class="form-control form-control-sm" placeholder="e.g. BPI Account (**** 1092)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalEftAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="75000.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Particulars &amp; Remittance Purpose <span class="text-danger">*</span></label>
              <textarea id="modalEftPurpose" class="form-control form-control-sm" rows="2" placeholder="State purpose of electronic fund release..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit Wire Transfer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEftDetailsModal(e) {
  if (!e) return;

  document.getElementById('detailEftRef').textContent = e.ref || 'EFT-000';
  document.getElementById('detailEftBank').textContent = e.bank || 'Recipient Bank';
  document.getElementById('detailEftType').textContent = e.type || 'Transfer Type';
  document.getElementById('detailEftAmount').textContent = e.amount || '₱0.00';
  document.getElementById('detailEftDate').textContent = e.date || '-';
  document.getElementById('detailEftPurpose').textContent = e.purpose || 'Purpose';
  document.getElementById('detailEftBankRef').textContent = e.bank_ref || 'WIRE-GEN-000';

  const statusEl = document.getElementById('detailEftStatus');
  if (statusEl) {
    statusEl.textContent = e.status;
    statusEl.className = 'badge ' + (e.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('eftDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('eftSearchInput');
  const typeSelect = document.getElementById('eftTypeSelect');
  const summaryText = document.getElementById('eftSummaryText');
  const btnCreateEft = document.getElementById('btnCreateEft');

  if (btnCreateEft) {
    btnCreateEft.addEventListener('click', function() {
      const modalEl = document.getElementById('createEftModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterEftTransfers() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedType = typeSelect ? typeSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.eft-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowType = row.getAttribute('data-type') || '';
      const rowText = row.textContent.toLowerCase();

      const matchType = !selectedType || rowType.includes(selectedType);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchType && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Bank Transfer${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noEftRow');
    const tbody = document.querySelector('#eftTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noEftRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No bank transfers found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterEftTransfers);
    searchInput.addEventListener('keyup', filterEftTransfers);
  }
  if (typeSelect) typeSelect.addEventListener('change', filterEftTransfers);

  const createEftForm = document.getElementById('createEftForm');
  if (createEftForm) {
    createEftForm.addEventListener('submit', function(ev) {
      ev.preventDefault();

      const refVal = document.getElementById('modalEftRef').value;
      const typeCodeVal = document.getElementById('modalEftType').value;
      const bankVal = document.getElementById('modalEftBank').value;
      const rawAmount = parseFloat(document.getElementById('modalEftAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const purposeVal = document.getElementById('modalEftPurpose').value;

      let typeLabel = 'PESONet Supplier Wire';
      let badgeStyle = 'bg-info-subtle text-info';
      if (typeCodeVal === 'payroll') {
        typeLabel = 'Direct Payroll Batch';
        badgeStyle = 'bg-primary-subtle text-primary';
      }

      const eftObj = {
        ref: refVal,
        type: typeLabel,
        type_code: typeCodeVal,
        badge: badgeStyle,
        bank: bankVal,
        purpose: purposeVal,
        date: "{{ date('Y-m-d') }}",
        amount: formattedAmount,
        status: 'Settled / Transferred',
        status_badge: 'bg-success-subtle text-success',
        status_icon: 'ph-check-circle',
        bank_ref: 'WIRE-NEW-100293'
      };

      const tbody = document.querySelector('#eftTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'eft-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-type', typeCodeVal);

        newRow.onclick = function() { openEftDetailsModal(eftObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${refVal}</span></td>
          <td><span class="badge ${badgeStyle}">${typeLabel}</span></td>
          <td class="fs-xs fw-semibold text-dark">${bankVal}</td>
          <td><span class="text-truncate d-inline-block" style="max-width: 250px;">${purposeVal}</span></td>
          <td class="font-monospace fs-xs">{{ date('Y-m-d') }}</td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Settled / Transferred</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Remittance File"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Remittance File"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openEftDetailsModal(eftObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createEftModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createEftForm.reset();
      filterEftTransfers();
    });
  }

  filterEftTransfers();
});
</script>
@endpush
