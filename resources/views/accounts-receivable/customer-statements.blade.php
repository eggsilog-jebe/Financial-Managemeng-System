@extends('layouts.app')

@section('title', 'Customer Statements - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'statements')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Customer Statements</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Account (SOA) Generator</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Sending email billing reminders...');"><i class="ph ph-envelope me-1"></i> Email All Statements</button>
      <button id="btnGenerateStatement" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#generateStatementModal"><i class="ph ph-plus-circle me-1"></i> Generate Statement</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active SOAs Generated</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">18 Statements</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Outstanding AR</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱3,005,300.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Corporate Guarantors</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">5 HMO Companies</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Average Statement Age</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">28 Days</h4>
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
            <input type="text" id="soaSearchInput" class="form-control bg-light border-start-0" placeholder="Search Payor Name, SOA Ref, or Contract Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="soaTypeSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Account Types</option>
            <option value="hmo">HMO Corporate</option>
            <option value="government">Government Guarantor</option>
            <option value="patient">Private Patient</option>
          </select>
        </div>
        <div class="col-md-4">
          <select id="soaStatusSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Statuses</option>
            <option value="sent">Sent - Awaiting Payment</option>
            <option value="paid">Paid &amp; Settled</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="soaTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Statement Ref</th>
              <th>Payor Name</th>
              <th>Account Type</th>
              <th>Statement Date</th>
              <th class="text-end">Unpaid Balance (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($statements ?? [] as $s)
            @php
              $sArr = is_array($s) ? $s : [
                'ref' => $s->reference_number ?? 'SOA-N/A', 'payor' => $s->payor_name ?? 'N/A',
                'sub' => $s->description ?? 'N/A', 'type' => $s->payor_type ?? 'N/A',
                'type_code' => strtolower($s->payor_type ?? 'general'), 'badge' => 'bg-info-subtle text-info',
                'date' => $s->statement_date ? $s->statement_date->format('Y-m-d') : 'N/A',
                'balance' => '₱' . number_format($s->outstanding_balance ?? 0, 2),
                'status' => $s->status ?? 'Pending', 'status_code' => strtolower($s->status ?? 'pending'),
                'status_badge' => 'bg-warning-subtle text-warning', 'status_icon' => 'ph-clock',
                'period' => $s->billing_period ?? 'N/A',
              ];
            @endphp
            <tr class="soa-row" style="cursor: pointer;" data-type="{{ $sArr['type_code'] }}" data-status="{{ $sArr['status_code'] }}" onclick="openSoaDetailsModal({{ json_encode($sArr) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $sArr['ref'] }}</span></td>
              <td>
                <div class="fw-bold text-dark">{{ $sArr['payor'] }}</div>
                <span class="fs-xs text-muted">{{ $sArr['sub'] }}</span>
              </td>
              <td><span class="badge {{ $sArr['badge'] }}">{{ $sArr['type'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $sArr['date'] }}</td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $sArr['balance'] }}</td>
              <td><span class="badge {{ $sArr['status_badge'] }}"><i class="ph {{ $sArr['status_icon'] }} me-1"></i> {{ $sArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View SOA Details" onclick="openSoaDetailsModal({{ json_encode($sArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No customer statements recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="soaSummaryText">Showing {{ count($statements ?? []) }} Statements</span>
      <nav aria-label="SOAs Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth SOA Details (Executive Design) -->
<div class="modal fade" id="soaDetailsModal" tabindex="-1" aria-labelledby="soaDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailSoaRef">SOA-2026-0701</span>
            <span class="badge bg-info-subtle text-info" id="detailSoaType">HMO Corporate</span>
            <span class="badge bg-warning-subtle text-warning" id="detailSoaStatus"><i class="ph ph-clock me-1"></i> Sent - Awaiting Payment</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailSoaPayor">Maxicare Healthcare Corp</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Unpaid Statement Balance</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailSoaBalance">₱1,220,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Statement Issue Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailSoaDate">2026-08-01</h4>
            </div>
          </div>
        </div>

        <!-- Master Info -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Statement Master Details</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Contract Reference</span>
              <span class="font-monospace fw-bold text-dark" id="detailSoaSub">Contract Ref: HMO-MAX-2026</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Billing Cycle Period</span>
              <span class="fw-medium text-dark" id="detailSoaPeriod">July 2026 Billing Pack</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Downloading SOA PDF...');"><i class="ph ph-file-pdf me-1"></i> Download Official SOA PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Generate Account Statement -->
<div class="modal fade" id="generateStatementModal" tabindex="-1" aria-labelledby="generateStatementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="generateStatementModalLabel"><i class="ph ph-file-plus me-2 text-primary"></i>Generate Statement of Account (SOA)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="generateStatementForm">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Statement Reference <span class="text-danger">*</span></label>
            <input type="text" id="modalSoaRef" class="form-control form-control-sm font-monospace" placeholder="e.g. SOA-2026-0705" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Select Payor / HMO Corporate Account <span class="text-danger">*</span></label>
            <select id="modalSoaPayor" class="form-select form-select-sm" required>
              <option value="Maxicare Healthcare Corp">Maxicare Healthcare Corp</option>
              <option value="Intellicare / Asuris Healthcare">Intellicare / Asuris Healthcare</option>
              <option value="Medicard Philippines Inc">Medicard Philippines Inc</option>
              <option value="PhilHealth Insurance Corp">PhilHealth Insurance Corp</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Statement Issue Date <span class="text-danger">*</span></label>
            <input type="date" id="modalSoaDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Unpaid Statement Balance (₱) <span class="text-danger">*</span></label>
            <input type="number" id="modalSoaBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="100000.00" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-file-pdf me-1"></i> Generate SOA</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openSoaDetailsModal(s) {
  if (!s) return;

  document.getElementById('detailSoaRef').textContent = s.ref || 'SOA-000';
  document.getElementById('detailSoaPayor').textContent = s.payor || 'Payor Name';
  document.getElementById('detailSoaType').textContent = s.type || 'Account Type';
  document.getElementById('detailSoaBalance').textContent = s.balance || '₱0.00';
  document.getElementById('detailSoaDate').textContent = s.date || '-';
  document.getElementById('detailSoaSub').textContent = s.sub || '-';
  document.getElementById('detailSoaPeriod').textContent = s.period || 'Billing Pack';

  const statusEl = document.getElementById('detailSoaStatus');
  if (statusEl) {
    statusEl.textContent = s.status;
    statusEl.className = 'badge ' + (s.status_badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('soaDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('soaSearchInput');
  const typeSelect = document.getElementById('soaTypeSelect');
  const statusSelect = document.getElementById('soaStatusSelect');
  const summaryText = document.getElementById('soaSummaryText');
  const btnGenerateStatement = document.getElementById('btnGenerateStatement');

  if (btnGenerateStatement) {
    btnGenerateStatement.addEventListener('click', function() {
      const modalEl = document.getElementById('generateStatementModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterStatements() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedType = typeSelect ? typeSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.soa-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowType = row.getAttribute('data-type') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchType = !selectedType || rowType.includes(selectedType);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchType && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Statement${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noSoaRow');
    const tbody = document.querySelector('#soaTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noSoaRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No statement of accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterStatements);
    searchInput.addEventListener('keyup', filterStatements);
  }
  if (typeSelect) typeSelect.addEventListener('change', filterStatements);
  if (statusSelect) statusSelect.addEventListener('change', filterStatements);

  const generateStatementForm = document.getElementById('generateStatementForm');
  if (generateStatementForm) {
    generateStatementForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const refVal = document.getElementById('modalSoaRef').value;
      const payorVal = document.getElementById('modalSoaPayor').value;
      const dateVal = document.getElementById('modalSoaDate').value;
      const rawBalance = parseFloat(document.getElementById('modalSoaBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let typeCode = 'hmo';
      let typeLabel = 'HMO Corporate';
      let badgeStyle = 'bg-info-subtle text-info';
      if (payorVal.includes('PhilHealth')) {
        typeCode = 'government';
        typeLabel = 'Government Guarantor';
        badgeStyle = 'bg-success-subtle text-success';
      }

      const soaObj = {
        ref: refVal,
        payor: payorVal,
        sub: 'Contract Ref: HMO-GEN-2026',
        type: typeLabel,
        type_code: typeCode,
        badge: badgeStyle,
        date: dateVal,
        balance: formattedBalance,
        status: 'Sent - Awaiting Payment',
        status_code: 'sent',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock',
        period: 'Newly Generated Billing Pack'
      };

      const tbody = document.querySelector('#soaTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'soa-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-type', typeCode);
        newRow.setAttribute('data-status', 'sent');

        newRow.onclick = function() { openSoaDetailsModal(soaObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${refVal}</span></td>
          <td>
            <div class="fw-bold text-dark">${payorVal}</div>
            <span class="fs-xs text-muted">Contract Ref: HMO-GEN-2026</span>
          </td>
          <td><span class="badge ${badgeStyle}">${typeLabel}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td class="text-end text-danger fw-bold font-monospace">${formattedBalance}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Sent - Awaiting Payment</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View SOA Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View SOA Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openSoaDetailsModal(soaObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('generateStatementModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      generateStatementForm.reset();
      filterStatements();
    });
  }

  filterStatements();
});
</script>
@endpush
