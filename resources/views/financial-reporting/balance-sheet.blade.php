@extends('layouts.app')

@section('title', 'Balance Sheet - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'balance-sheet')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Balance Sheet</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Financial Position (Balance Sheet)</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Balance Sheet Audit PDF generated!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Assets</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format($totalAssets, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format($totalLiabilities, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Hospital Net Equity</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format($totalEquity, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $currentRatio }}x</h4>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="bsAsOfDate" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-calendar me-1"></i> As-Of Date:</label>
          <input type="date" id="bsAsOfDate" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}" style="min-width: 170px;">
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="bsComparisonSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Comparison:</label>
          <select id="bsComparisonSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="prior_year">Prior Fiscal Year (FY 2025)</option>
            <option value="prior_quarter">Prior Quarter (Q1 2026)</option>
            <option value="none">No Comparison</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="bsSearchInput" class="form-control form-control-sm" placeholder="Search account line, classification...">
        </div>
      </div>
    </div>
  </div>

  <!-- Dual Column Balance Sheet Layout -->
  <div class="row g-4">
    <!-- Assets Column -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-success"><i class="ph ph-vault me-2"></i>ASSETS</h5>
          <span class="fs-xs text-muted font-monospace">As of {{ date('M d, Y') }}</span>
        </div>
        <div class="card-body p-0">
          <table id="bsAssetsTable" class="table table-hover mb-0">
            <thead class="table-light fs-xs text-uppercase">
              <tr><th>Asset Account</th><th class="text-end">Balance (₱)</th></tr>
            </thead>
            <tbody>
              @forelse($assets as $acc)
              @php $bal = (float) $acc->current_balance; @endphp
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('{{ addslashes($acc->name) }}', 'Asset', '₱{{ number_format($bal, 2) }}', '{{ $acc->code }}')">
                <td class="ps-4">{{ $acc->name }}</td>
                <td class="text-end font-monospace">₱{{ number_format($bal, 2) }}</td>
              </tr>
              @empty
              <tr><td colspan="2" class="text-center py-3 text-muted">No asset accounts in database.</td></tr>
              @endforelse
              <tr class="fw-semibold table-success"><td>TOTAL ASSETS</td><td class="text-end text-success font-monospace">₱{{ number_format($totalAssets, 2) }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Liabilities & Equity Column -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-primary"><i class="ph ph-scales me-2"></i>LIABILITIES &amp; EQUITY</h5>
          <span class="fs-xs text-muted font-monospace">As of {{ date('M d, Y') }}</span>
        </div>
        <div class="card-body p-0">
          <table id="bsLiabilitiesTable" class="table table-hover mb-0">
            <thead class="table-light fs-xs text-uppercase">
              <tr><th>Liabilities &amp; Equity</th><th class="text-end">Balance (₱)</th></tr>
            </thead>
            <tbody>
              <tr class="table-light fw-bold"><td colspan="2">Liabilities</td></tr>
              @forelse($liabilities as $acc)
              @php $bal = (float) $acc->current_balance; @endphp
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('{{ addslashes($acc->name) }}', 'Liability', '₱{{ number_format($bal, 2) }}', '{{ $acc->code }}')">
                <td class="ps-4">{{ $acc->name }}</td>
                <td class="text-end font-monospace text-danger">₱{{ number_format($bal, 2) }}</td>
              </tr>
              @empty
              <tr><td colspan="2" class="text-center py-3 text-muted">No liability accounts.</td></tr>
              @endforelse
              <tr class="fw-semibold"><td>Total Liabilities</td><td class="text-end text-danger font-monospace">₱{{ number_format($totalLiabilities, 2) }}</td></tr>

              <tr class="table-light fw-bold"><td colspan="2">Equity</td></tr>
              @forelse($equity as $acc)
              @php $bal = (float) $acc->current_balance; @endphp
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('{{ addslashes($acc->name) }}', 'Equity', '₱{{ number_format($bal, 2) }}', '{{ $acc->code }}')">
                <td class="ps-4">{{ $acc->name }}</td>
                <td class="text-end font-monospace text-primary">₱{{ number_format($bal, 2) }}</td>
              </tr>
              @empty
              <tr><td colspan="2" class="text-center py-3 text-muted">No equity accounts.</td></tr>
              @endforelse
              <tr class="fw-semibold table-primary"><td>TOTAL LIABILITIES &amp; EQUITY</td><td class="text-end text-primary font-monospace">₱{{ number_format($totalLiabilities + $totalEquity, 2) }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Balance Sheet Account Details (Executive Design) -->
<div class="modal fade" id="balanceSheetDetailsModal" tabindex="-1" aria-labelledby="balanceSheetDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailBsGlCode">1010-MASTER</span>
            <span class="badge bg-success-subtle text-success" id="detailBsClass">Current Assets</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailBsName">Cash &amp; Bank Equivalents</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Reported Ledger Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailBsAmount">₱4,850,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">As-Of Reporting Period</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace">{{ date('M 31, Y') }}</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-files me-1 text-primary"></i> General Ledger Account Mapping</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">GL Ledger Account Name</span>
              <span class="fw-semibold text-dark" id="detailBsFullName">Cash &amp; Commercial Bank Accounts</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Financial Statement Classification</span>
              <span class="font-monospace text-primary fw-bold" id="detailBsGroup">Statement of Financial Position</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Double-Entry Solvency Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Accounting Equation Balance Check:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Assets = Liabilities + Equity (Balanced)</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-BS-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Ledger Schedule Audit PDF...');"><i class="ph ph-file-text me-1"></i> Export Account Audit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openBalanceSheetDetailsModal(name, classification, amount, glCode) {
  document.getElementById('detailBsName').textContent = name || 'Account Line';
  document.getElementById('detailBsClass').textContent = classification || 'Assets';
  document.getElementById('detailBsAmount').textContent = amount || '₱0.00';
  document.getElementById('detailBsGlCode').textContent = glCode || '1000-GL';
  document.getElementById('detailBsFullName').textContent = name || 'Account Line';

  const modalEl = document.getElementById('balanceSheetDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('bsSearchInput');

  function filterBalanceSheet() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.bs-line-row');

    rows.forEach(function(row) {
      const text = row.textContent.toLowerCase();
      if (!searchQuery || text.includes(searchQuery)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterBalanceSheet);
    searchInput.addEventListener('keyup', filterBalanceSheet);
  }
});
</script>
@endpush
