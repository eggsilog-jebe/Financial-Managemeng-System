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
        <h4 class="fw-bold mb-0 text-success">₱54,110,200.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱12,910,500.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Hospital Net Equity</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱41,199,700.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">9.78x</h4>
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
              <tr><th>Asset Account Category</th><th class="text-end">Amount (₱)</th></tr>
            </thead>
            <tbody>
              <tr class="table-light fw-bold"><td colspan="2">1. Current Assets</td></tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Cash & Bank Equivalents', 'Current Assets', '₱4,850,000.00', '1010-MASTER')">
                <td class="ps-4">Cash &amp; Bank Equivalents</td>
                <td class="text-end font-monospace">₱4,850,000.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Accounts Receivable (AR)', 'Current Assets', '₱3,070,200.00', '1100-AR-PATIENT')">
                <td class="ps-4">Accounts Receivable (AR - Patients &amp; HMOs)</td>
                <td class="text-end font-monospace">₱3,070,200.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Pharmacy Inventory', 'Current Assets', '₱990,000.00', '1200-INV-PHARM')">
                <td class="ps-4">Pharmacy &amp; Medical Supplies Inventory</td>
                <td class="text-end font-monospace">₱990,000.00</td>
              </tr>
              <tr class="fw-semibold"><td>Total Current Assets</td><td class="text-end text-success font-monospace">₱8,910,200.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">2. Non-Current Assets</td></tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Medical Equipment', 'Non-Current Assets', '₱28,500,000.00', '1500-EQP-MED')">
                <td class="ps-4">Medical Equipment &amp; MRI Scanners</td>
                <td class="text-end font-monospace">₱28,500,000.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Hospital Building', 'Non-Current Assets', '₱18,200,000.00', '1510-BLD-HOSP')">
                <td class="ps-4">Hospital Building &amp; Infrastructure</td>
                <td class="text-end font-monospace">₱18,200,000.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Accumulated Depreciation', 'Non-Current Assets', '-₱1,500,000.00', '1590-DEP-ACC')">
                <td class="ps-4 text-muted">Less: Accumulated Depreciation</td>
                <td class="text-end font-monospace text-danger">-₱1,500,000.00</td>
              </tr>
              <tr class="fw-semibold"><td>Total Non-Current Assets</td><td class="text-end text-success font-monospace">₱45,200,000.00</td></tr>
            </tbody>
            <tfoot class="table-success fw-bold">
              <tr>
                <td class="fs-6">TOTAL ASSETS</td>
                <td class="text-end text-success fs-6 font-monospace">₱54,110,200.00</td>
              </tr>
            </tfoot>
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
              <tr><th>Liabilities &amp; Equity Category</th><th class="text-end">Amount (₱)</th></tr>
            </thead>
            <tbody>
              <tr class="table-light fw-bold"><td colspan="2">1. Current Liabilities</td></tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Accounts Payable (AP)', 'Current Liabilities', '₱910,500.00', '2010-AP-VEND')">
                <td class="ps-4">Accounts Payable (AP Vendor Bills)</td>
                <td class="text-end font-monospace">₱910,500.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Accrued Payroll', 'Current Liabilities', '₱0.00', '2020-PAYROLL')">
                <td class="ps-4">Accrued Nurse &amp; Staff Payroll</td>
                <td class="text-end font-monospace">₱0.00</td>
              </tr>
              <tr class="fw-semibold"><td>Total Current Liabilities</td><td class="text-end text-danger font-monospace">₱910,500.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">2. Long-Term Liabilities</td></tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Equipment Term Loans', 'Long-Term Liabilities', '₱12,000,000.00', '2500-LOAN-EQUIP')">
                <td class="ps-4">Medical Equipment Term Loans</td>
                <td class="text-end font-monospace">₱12,000,000.00</td>
              </tr>
              <tr class="fw-semibold"><td>Total Liabilities</td><td class="text-end text-danger font-monospace">₱12,910,500.00</td></tr>
              
              <tr class="table-light fw-bold"><td colspan="2">3. Hospital Net Equity</td></tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Founding Capital Reserve', 'Net Equity', '₱25,000,000.00', '3010-CAP-FOUND')">
                <td class="ps-4">Founding Capital Reserve</td>
                <td class="text-end font-monospace">₱25,000,000.00</td>
              </tr>
              <tr class="bs-line-row" style="cursor: pointer;" onclick="openBalanceSheetDetailsModal('Retained Earnings', 'Net Equity', '₱16,199,700.00', '3020-RET-EARN')">
                <td class="ps-4">Retained Earnings (Accumulated Net Profits)</td>
                <td class="text-end font-monospace">₱16,199,700.00</td>
              </tr>
              <tr class="fw-semibold"><td>Total Net Equity</td><td class="text-end text-primary font-monospace">₱41,199,700.00</td></tr>
            </tbody>
            <tfoot class="table-primary fw-bold">
              <tr>
                <td class="fs-6">TOTAL LIABILITIES &amp; EQUITY</td>
                <td class="text-end text-primary fs-6 font-monospace">₱54,110,200.00</td>
              </tr>
            </tfoot>
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
