@extends('layouts.app')

@section('title', 'Trial Balance - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'trial-balance')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Trial Balance</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Trial Balance</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Trial Balance PDF Statement exported!');"><i class="ph ph-file-pdf me-1"></i> Export Trial Balance PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Debit Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success" id="tbTotalDebitCard">₱14,550,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Balance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary" id="tbTotalCreditCard">₱14,550,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Balance Discrepancy</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark" id="tbDiscrepancyCard">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Audit Verification</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Passed</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="tbCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Category:</label>
          <select id="tbCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Categories</option>
            <option value="asset">Assets</option>
            <option value="liability">Liabilities</option>
            <option value="equity">Equity</option>
            <option value="revenue">Revenue</option>
            <option value="expense">Expenses</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="tbDateInput" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-calendar me-1"></i> As-Of Date:</label>
          <input type="date" id="tbDateInput" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}" style="min-width: 170px;">
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="tbSearchInput" class="form-control form-control-sm" placeholder="Search account code, title...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="trialBalanceTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Account Code</th>
              <th>Account Title</th>
              <th>Category</th>
              <th class="text-end">Debit Balance (₱)</th>
              <th class="text-end">Credit Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $tbAccounts = [
                [
                  'code' => '1010',
                  'title' => 'Metrobank Operating Cash Account',
                  'category' => 'Asset',
                  'category_key' => 'asset',
                  'debit' => '₱4,850,000.00',
                  'credit' => '-',
                  'badge' => 'bg-success-subtle text-success'
                ],
                [
                  'code' => '1200',
                  'title' => 'Accounts Receivable (Patients & HMOs)',
                  'category' => 'Asset',
                  'category_key' => 'asset',
                  'debit' => '₱3,070,200.00',
                  'credit' => '-',
                  'badge' => 'bg-success-subtle text-success'
                ],
                [
                  'code' => '1300',
                  'title' => 'Pharmacy Stock & Medicine Inventory',
                  'category' => 'Asset',
                  'category_key' => 'asset',
                  'debit' => '₱980,000.00',
                  'credit' => '-',
                  'badge' => 'bg-success-subtle text-success'
                ],
                [
                  'code' => '2010',
                  'title' => 'Accounts Payable (Medical Suppliers & Vendors)',
                  'category' => 'Liability',
                  'category_key' => 'liability',
                  'debit' => '-',
                  'credit' => '₱2,100,000.00',
                  'badge' => 'bg-danger-subtle text-danger'
                ],
                [
                  'code' => '3010',
                  'title' => 'Hospital Capital Reserve & Retained Earnings',
                  'category' => 'Equity',
                  'category_key' => 'equity',
                  'debit' => '-',
                  'credit' => '₱6,330,000.00',
                  'badge' => 'bg-primary-subtle text-primary'
                ],
              ];
            @endphp

            @foreach($tbAccounts as $acc)
            <tr class="tb-row" style="cursor: pointer;" data-category="{{ $acc['category_key'] }}" onclick="openTbDetailsModal({{ json_encode($acc) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $acc['code'] }}</span></td>
              <td><div class="fw-bold text-dark">{{ $acc['title'] }}</div></td>
              <td><span class="badge {{ $acc['badge'] }}">{{ $acc['category'] }}</span></td>
              <td class="text-end @if($acc['debit'] !== '-') text-success fw-bold @else text-muted @endif font-monospace">{{ $acc['debit'] }}</td>
              <td class="text-end @if($acc['credit'] !== '-') text-danger fw-bold @else text-muted @endif font-monospace">{{ $acc['credit'] }}</td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Trial Balance Details" onclick="openTbDetailsModal({{ json_encode($acc) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-dark font-monospace fw-bold">
            <tr>
              <td colspan="3" class="text-end fs-6">TOTAL TRIAL BALANCE:</td>
              <td class="text-end text-success fs-6" id="footDebitTotal">₱14,550,000.00</td>
              <td class="text-end text-info fs-6" id="footCreditTotal">₱14,550,000.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="tbSummaryText">Showing {{ count($tbAccounts) }} Trial Balance Accounts</span>
      <nav aria-label="TB Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Trial Balance Details (Executive Design) -->
<div class="modal fade" id="tbDetailsModal" tabindex="-1" aria-labelledby="tbDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailTbCode">1010</span>
            <span class="badge bg-success-subtle text-success" id="detailTbCategory">Asset</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailTbTitle">Metrobank Operating Cash Account</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Debit Balance</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailTbDebit">₱4,850,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Credit Balance</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailTbCredit">-</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-scales me-1 text-primary"></i> Trial Balance Equality Scope</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">As-Of Reporting Date</span>
              <span class="font-monospace fw-bold text-dark">{{ date('Y-m-d') }}</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">General Ledger Trial Solvency</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Equal Debit &amp; Credit Solvency</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Double-Entry Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Audit Verification Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Passed Trial Balance Audit</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-TB-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Trial Balance Audit PDF...');"><i class="ph ph-file-text me-1"></i> Export Account Audit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openTbDetailsModal(acc) {
  if (!acc) return;

  document.getElementById('detailTbTitle').textContent = acc.title || 'Account Title';
  document.getElementById('detailTbCode').textContent = acc.code || '0000';
  document.getElementById('detailTbCategory').textContent = acc.category || 'Asset';
  document.getElementById('detailTbDebit').textContent = acc.debit || '-';
  document.getElementById('detailTbCredit').textContent = acc.credit || '-';

  const modalEl = document.getElementById('tbDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('tbSearchInput');
  const categorySelect = document.getElementById('tbCategorySelect');
  const summaryText = document.getElementById('tbSummaryText');

  function filterTrialBalance() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCategory = categorySelect ? categorySelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.tb-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCategory = row.getAttribute('data-category') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCategory = !selectedCategory || rowCategory === selectedCategory;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCategory && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Trial Balance Account${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noTBRow');
    const tbody = document.querySelector('#trialBalanceTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTBRow';
        emptyRow.innerHTML = `<td colspan="6" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTrialBalance);
    searchInput.addEventListener('keyup', filterTrialBalance);
  }
  if (categorySelect) categorySelect.addEventListener('change', filterTrialBalance);

  filterTrialBalance();
});
</script>
@endpush
