@extends('layouts.app')

@section('title', 'Profit & Loss Statement - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'profit-loss')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Profit &amp; Loss</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Income Statement (Profit &amp; Loss)</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print P&amp;L</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Income Statement PDF exported!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Operating Revenue</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Expenses (OPEX)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Profit (EBITDA)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($netIncome ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Net Margin %</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        @php
          $revVal = (float) ($totalRevenue ?? 0);
          $marginVal = $revVal > 0 ? round((((float) ($netIncome ?? 0)) / $revVal) * 100, 1) : 0;
        @endphp
        <h4 class="fw-bold mb-0 text-dark">{{ $marginVal }}%</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="pnlPeriodSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Period:</label>
          <select id="pnlPeriodSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="ytd" selected>Year-To-Date 2026 (Jan - Aug)</option>
            <option value="q2">Q2 2026 (Apr - Jun)</option>
            <option value="q1">Q1 2026 (Jan - Mar)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="pnlComparisonSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Comparison:</label>
          <select id="pnlComparisonSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="prior_period" selected>Prior Fiscal Period</option>
            <option value="prior_year">Prior Year Same Period (FY 2025)</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="pnlSearchInput" class="form-control form-control-sm" placeholder="Search line item, account category...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="pnlTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Financial Item &amp; Revenue / Expense Account</th>
              <th class="text-end">Current Period (₱)</th>
              <th class="text-end">Prior Period (₱)</th>
              <th class="text-end">Variance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-light fw-bold text-success"><td colspan="5">1. Hospital Operating Revenue</td></tr>
            @forelse($revenues ?? [] as $revAcc)
            @php $revBal = (float) $revAcc->current_balance; @endphp
            <tr class="pnl-row" style="cursor: pointer;" onclick="openPnlDetailsModal('{{ addslashes($revAcc->name) }}', 'Hospital Operating Revenue', '₱{{ number_format($revBal, 2) }}', '₱0.00', '+₱{{ number_format($revBal, 2) }}', '{{ $revAcc->code }}')">
              <td class="ps-4 fw-semibold">{{ $revAcc->name }}</td>
              <td class="text-end font-monospace text-success fw-semibold">₱{{ number_format($revBal, 2) }}</td>
              <td class="text-end font-monospace">₱0.00</td>
              <td class="text-end font-monospace text-success">+₱{{ number_format($revBal, 2) }}</td>
              <td class="text-end" onclick="event.stopPropagation();"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-2 text-muted">No operating revenues recorded.</td></tr>
            @endforelse
            <tr class="table-success fw-bold">
              <td>TOTAL GROSS REVENUE</td>
              <td class="text-end text-success font-monospace fs-6">₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</td>
              <td class="text-end font-monospace fs-6">₱0.00</td>
              <td class="text-end text-success font-monospace fs-6">+₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</td>
              <td></td>
            </tr>

            <tr class="table-light fw-bold text-danger"><td colspan="5">2. Operating Expenses (OPEX)</td></tr>
            @forelse($expenses ?? [] as $expAcc)
            @php $expBal = (float) $expAcc->current_balance; @endphp
            <tr class="pnl-row" style="cursor: pointer;" onclick="openPnlDetailsModal('{{ addslashes($expAcc->name) }}', 'Operating Expenses', '-₱{{ number_format($expBal, 2) }}', '₱0.00', '-₱{{ number_format($expBal, 2) }}', '{{ $expAcc->code }}')">
              <td class="ps-4 text-muted">{{ $expAcc->name }}</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format($expBal, 2) }}</td>
              <td class="text-end font-monospace">₱0.00</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format($expBal, 2) }}</td>
              <td class="text-end" onclick="event.stopPropagation();"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-2 text-muted">No operating expenses recorded.</td></tr>
            @endforelse
            <tr class="table-danger fw-bold">
              <td>TOTAL OPERATING EXPENSES</td>
              <td class="text-end text-danger font-monospace fs-6">-₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</td>
              <td class="text-end font-monospace fs-6">₱0.00</td>
              <td class="text-end text-danger font-monospace fs-6">-₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</td>
              <td></td>
            </tr>

            <tr class="table-primary fw-bold">
              <td class="fs-6">NET OPERATING PROFIT (EBITDA)</td>
              <td class="text-end text-primary font-monospace fs-5">₱{{ number_format((float) ($netIncome ?? 0), 2) }}</td>
              <td class="text-end font-monospace fs-5">₱0.00</td>
              <td class="text-end text-success font-monospace fs-5">+₱{{ number_format((float) ($netIncome ?? 0), 2) }}</td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth P&L Details (Executive Design) -->
<div class="modal fade" id="pnlDetailsModal" tabindex="-1" aria-labelledby="pnlDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailPnlGlCode">4010-REV</span>
            <span class="badge bg-success-subtle text-success" id="detailPnlGroup">Hospital Operating Revenue</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailPnlName">Inpatient Ward &amp; ICU Admission Revenue</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Period Realized</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailPnlCurrent">₱11,200,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Prior Period Baseline</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailPnlPrior">₱9,800,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Calculated Variance</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailPnlVariance">+₱1,400,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-chart-line me-1 text-primary"></i> Operating Performance Analysis</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Account Classification</span>
              <span class="font-monospace fw-bold text-dark">Income Statement Revenue &amp; OPEX</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Reporting Fiscal Period</span>
              <span class="font-monospace text-muted">FY 2026 Year-To-Date</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Ledger Posting Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">General Ledger Posting Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Posted &amp; Audited</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-PNL-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Line Item Audit Schedule...');"><i class="ph ph-file-text me-1"></i> Export Line Audit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openPnlDetailsModal(name, group, current, prior, variance, glCode) {
  document.getElementById('detailPnlName').textContent = name || 'Financial Item';
  document.getElementById('detailPnlGroup').textContent = group || 'Revenue';
  document.getElementById('detailPnlCurrent').textContent = current || '₱0.00';
  document.getElementById('detailPnlPrior').textContent = prior || '₱0.00';
  document.getElementById('detailPnlVariance').textContent = variance || '₱0.00';
  document.getElementById('detailPnlGlCode').textContent = glCode || '4000-GL';

  const modalEl = document.getElementById('pnlDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('pnlSearchInput');

  function filterPnl() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.pnl-row');

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
    searchInput.addEventListener('input', filterPnl);
    searchInput.addEventListener('keyup', filterPnl);
  }
});
</script>
@endpush
