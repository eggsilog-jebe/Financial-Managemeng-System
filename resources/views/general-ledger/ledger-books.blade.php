@extends('layouts.app')

@section('title', 'Ledger Books - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'ledger-books')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">General Ledger Books</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">General Ledger Account Books</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print GL Books</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Master GL Books exported!');"><i class="ph ph-file-arrow-down me-1"></i> Export Master GL</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active GL Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-book-open fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($accounts ?? []) }} Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Debit Movement</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Credit Movement</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Ledger Book Solvency</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Balanced</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="glTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Account Type:</label>
          <select id="glTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Account Types</option>
            <option value="asset">1000 - Assets</option>
            <option value="liability">2000 - Liabilities</option>
            <option value="equity">3000 - Equity</option>
            <option value="revenue">4000 - Revenue</option>
            <option value="expense">5000 - Expenses</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="glPeriodSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Fiscal Period:</label>
          <select id="glPeriodSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Fiscal Periods</option>
            <option value="ytd">FY 2026 Year-To-Date</option>
            <option value="q2">Q2 2026</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="glSearchInput" class="form-control form-control-sm" placeholder="Search code or account name...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="glBooksTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Account Code</th>
              <th>Account Name</th>
              <th>Account Type</th>
              <th class="text-end">Opening (₱)</th>
              <th class="text-end">Debit Total (₱)</th>
              <th class="text-end">Credit Total (₱)</th>
              <th class="text-end">Ending Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts ?? [] as $acc)
            @php
              $code = is_array($acc) ? $acc['code'] : $acc->code;
              $name = is_array($acc) ? $acc['name'] : $acc->name;
              $type = is_array($acc) ? $acc['type'] : ucfirst(strtolower($acc->category));
              $typeKey = is_array($acc) ? $acc['type_key'] : strtolower($acc->category);
              $badgeClass = match(strtolower($type)) {
                'asset' => 'bg-success-subtle text-success',
                'liability' => 'bg-danger-subtle text-danger',
                'equity' => 'bg-primary-subtle text-primary',
                'revenue' => 'bg-info-subtle text-info',
                default => 'bg-warning-subtle text-warning',
              };
              $glData = [
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'type_key' => $typeKey,
                'opening' => '₱0.00',
                'debit' => '+₱0.00',
                'credit' => '-₱0.00',
                'ending' => '₱0.00',
                'badge' => $badgeClass
              ];
            @endphp
            <tr class="gl-row" style="cursor: pointer;" data-type="{{ $typeKey }}" onclick="openLedgerBookDetailsModal({{ json_encode($glData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $code }}</span></td>
              <td><div class="fw-bold text-dark">{{ $name }}</div></td>
              <td><span class="badge {{ $badgeClass }}">{{ $type }}</span></td>
              <td class="text-end font-monospace">₱0.00</td>
              <td class="text-end text-success font-monospace">+₱0.00</td>
              <td class="text-end text-danger font-monospace">-₱0.00</td>
              <td class="text-end text-primary fw-bold font-monospace">₱0.00</td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Movement Ledger" onclick="openLedgerBookDetailsModal({{ json_encode($glData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No ledger accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="glSummaryText">Showing {{ count($accounts ?? []) }} Ledger Account Books</span>
      <nav aria-label="GL Book Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Ledger Book Details (Executive Design) -->
<div class="modal fade" id="ledgerBookDetailsModal" tabindex="-1" aria-labelledby="ledgerBookDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailGlCode">1010</span>
            <span class="badge bg-success-subtle text-success" id="detailGlType">Asset Account</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailGlName">Metrobank Operating Cash Account</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Opening Balance</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailGlOpening">₱2,500,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Period Debits</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailGlDebit">+₱8,450,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Period Credits</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailGlCredit">-₱6,100,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4 text-center">
          <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Calculated Ending Net Balance</span>
          <h3 class="fw-bold text-primary mb-0 font-monospace" id="detailGlEnding">₱4,850,000.00</h3>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Ledger Movement Integrity</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Ledger Reconciliation Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Reconciled with Trial Balance</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-GLBOOK-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Full Ledger Movement Log PDF...');"><i class="ph ph-file-text me-1"></i> Export Movement Log</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openLedgerBookDetailsModal(gl) {
  if (!gl) return;

  document.getElementById('detailGlName').textContent = gl.name || 'Account Name';
  document.getElementById('detailGlCode').textContent = gl.code || '0000';
  document.getElementById('detailGlType').textContent = gl.type || 'Asset';
  document.getElementById('detailGlOpening').textContent = gl.opening || '₱0.00';
  document.getElementById('detailGlDebit').textContent = gl.debit || '₱0.00';
  document.getElementById('detailGlCredit').textContent = gl.credit || '₱0.00';
  document.getElementById('detailGlEnding').textContent = gl.ending || '₱0.00';

  const modalEl = document.getElementById('ledgerBookDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const typeSelect = document.getElementById('glTypeSelect');
  const periodSelect = document.getElementById('glPeriodSelect');
  const searchInput = document.getElementById('glSearchInput');
  const summaryText = document.getElementById('glSummaryText');

  function filterGLBooks() {
    const typeVal = typeSelect ? typeSelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.gl-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowType = row.getAttribute('data-type') || '';
      const rowText = row.textContent.toLowerCase();

      const matchType = !typeVal || rowType === typeVal;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchType && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Ledger Account Book${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noGLBooksRow');
    const tbody = document.querySelector('#glBooksTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noGLBooksRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No ledger accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (typeSelect) typeSelect.addEventListener('change', filterGLBooks);
  if (periodSelect) periodSelect.addEventListener('change', filterGLBooks);
  if (searchInput) {
    searchInput.addEventListener('input', filterGLBooks);
    searchInput.addEventListener('keyup', filterGLBooks);
  }

  filterGLBooks();
});
</script>
@endpush
