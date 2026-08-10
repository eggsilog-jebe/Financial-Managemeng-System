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

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0" id="tbSearchIcon" style="cursor: pointer;"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" id="tbSearchInput" class="form-control bg-light border-start-0" placeholder="Search account code, title, or category...">
          </div>
        </div>
        <div class="col-md-4">
          <select id="tbCategorySelect" class="form-select form-select-sm bg-light">
            <option value="">All Categories</option>
            <option value="asset">Assets</option>
            <option value="liability">Liabilities</option>
            <option value="equity">Equity</option>
            <option value="revenue">Revenue</option>
            <option value="expense">Expenses</option>
          </select>
        </div>
        <div class="col-md-3">
          <input type="date" id="tbDateInput" class="form-control form-control-sm bg-light" value="{{ date('Y-m-d') }}">
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
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
                  'code' => '2030',
                  'title' => 'Accrued Staff Payroll Liability',
                  'category' => 'Liability',
                  'category_key' => 'liability',
                  'debit' => '-',
                  'credit' => '₱880,000.00',
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
                [
                  'code' => '4010',
                  'title' => 'Inpatient & Emergency Care Service Revenue',
                  'category' => 'Revenue',
                  'category_key' => 'revenue',
                  'debit' => '-',
                  'credit' => '₱5,240,000.00',
                  'badge' => 'bg-info-subtle text-info'
                ],
                [
                  'code' => '5010',
                  'title' => 'Medical & Surgical Supplies Operating Expense',
                  'category' => 'Expense',
                  'category_key' => 'expense',
                  'debit' => '₱3,180,000.00',
                  'credit' => '-',
                  'badge' => 'bg-warning-subtle text-warning'
                ],
                [
                  'code' => '5020',
                  'title' => 'Hospital Facility Utility & Power Operating Expense',
                  'category' => 'Expense',
                  'category_key' => 'expense',
                  'debit' => '₱2,469,800.00',
                  'credit' => '-',
                  'badge' => 'bg-warning-subtle text-warning'
                ],
              ];
            @endphp

            @foreach($tbAccounts as $acc)
            <tr class="tb-row" data-category="{{ $acc['category_key'] }}">
              <td><span class="font-monospace text-primary fw-bold">{{ $acc['code'] }}</span></td>
              <td><div class="fw-bold text-dark">{{ $acc['title'] }}</div></td>
              <td><span class="badge {{ $acc['badge'] }}">{{ $acc['category'] }}</span></td>
              <td class="text-end @if($acc['debit'] !== '-') text-success fw-bold @else text-muted @endif font-monospace">{{ $acc['debit'] }}</td>
              <td class="text-end @if($acc['credit'] !== '-') text-danger fw-bold @else text-muted @endif font-monospace">{{ $acc['credit'] }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-dark font-monospace fw-bold">
            <tr>
              <td colspan="3" class="text-end fs-6">TOTAL TRIAL BALANCE:</td>
              <td class="text-end text-success fs-6" id="footDebitTotal">₱14,550,000.00</td>
              <td class="text-end text-info fs-6" id="footCreditTotal">₱14,550,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('tbSearchInput');
  const searchIcon = document.getElementById('tbSearchIcon');
  const categorySelect = document.getElementById('tbCategorySelect');

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

    let emptyRow = document.getElementById('noTBRow');
    const tbody = document.querySelector('#trialBalanceTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTBRow';
        emptyRow.innerHTML = `<td colspan="5" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No accounts found matching the current filter.</td>`;
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
  if (searchIcon) searchIcon.addEventListener('click', filterTrialBalance);
  if (categorySelect) categorySelect.addEventListener('change', filterTrialBalance);

  filterTrialBalance();
});
</script>
@endpush
