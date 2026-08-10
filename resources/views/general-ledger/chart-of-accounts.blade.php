@extends('layouts.app')

@section('title', 'Chart of Accounts - General Ledger | FMS')
@section('module', 'finance')
@section('page', 'chart-of-accounts')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active" aria-current="page">Chart of Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Chart of Accounts</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export COA</button>
      <button id="btnAddAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="ph ph-plus me-1"></i> Add Account</button>
    </div>
  </div>

  <!-- Summary Cards Row (Clean 5-Column Grid) -->
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Total Assets</span>
          <span class="p-2 rounded-3 bg-success-subtle text-success fs-xs"><i class="ph ph-trend-up"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱8,450,000.00</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Total Liabilities</span>
          <span class="p-2 rounded-3 bg-danger-subtle text-danger fs-xs"><i class="ph ph-warning-circle"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱2,120,000.00</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Total Equity</span>
          <span class="p-2 rounded-3 bg-primary-subtle text-primary fs-xs"><i class="ph ph-scales"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱6,330,000.00</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Operating Revenue</span>
          <span class="p-2 rounded-3 bg-info-subtle text-info fs-xs"><i class="ph ph-receipt"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱5,240,000.00</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Operating Expenses</span>
          <span class="p-2 rounded-3 bg-warning-subtle text-warning fs-xs"><i class="ph ph-chart-line-down"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱3,180,000.00</h4>
      </div>
    </div>
  </div>

  <!-- Main Table Section -->
  <div class="card border-0 shadow-sm rounded-3">
    <!-- Toolbar Header -->
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Category Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="accountCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Account Category:</label>
          <select id="accountCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="assets" selected>Assets</option>
            <option value="liabilities">Liabilities</option>
            <option value="equity">Equity</option>
            <option value="revenue">Revenue</option>
            <option value="expenses">Expenses</option>
          </select>
        </div>

        <!-- Search Bar -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="accountSearchInput" class="form-control form-control-sm" placeholder="Search code, name, or unit...">
        </div>
      </div>
    </div>

    <!-- Accounts Table -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="coaTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" style="width: 110px;">Code</th>
              <th scope="col">Account Name</th>
              <th scope="col">Category</th>
              <th scope="col">Department / Cost Center</th>
              <th scope="col">Normal Balance</th>
              <th scope="col" class="text-end">Current Balance (₱)</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-end" style="width: 100px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $accounts = [
                [
                  'code' => '1010',
                  'name' => 'Cash on Hand - Main Vault',
                  'desc' => 'Physical currency drawer held in hospital main vault.',
                  'category' => 'Asset',
                  'dept' => 'Treasury / Cashier',
                  'type' => 'Debit',
                  'balance' => '₱250,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '1020',
                  'name' => 'Operating Bank Account - Metrobank',
                  'desc' => 'Primary commercial bank account for payroll and AP disbursements.',
                  'category' => 'Asset',
                  'dept' => 'Hospital Treasury',
                  'type' => 'Debit',
                  'balance' => '₱3,420,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '1050',
                  'name' => 'Accounts Receivable - Patients & HMOs',
                  'desc' => 'Outstanding billing receivables due from admitted patients and insurers.',
                  'category' => 'Asset',
                  'dept' => 'Patient Billing / AR',
                  'type' => 'Debit',
                  'balance' => '₱1,850,500.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '1100',
                  'name' => 'Pharmacy Stock Inventory',
                  'desc' => 'Current store inventory valuation of pharmaceutical drugs and IV solutions.',
                  'category' => 'Asset',
                  'dept' => 'Pharmacy Department',
                  'type' => 'Debit',
                  'balance' => '₱980,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '2010',
                  'name' => 'Accounts Payable - Medical Vendors',
                  'desc' => 'Short-term liabilities owed to medical suppliers and device vendors.',
                  'category' => 'Liability',
                  'dept' => 'Accounts Payable',
                  'type' => 'Credit',
                  'balance' => '₱1,240,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '2030',
                  'name' => 'Accrued Hospital Staff Payroll',
                  'desc' => 'Accumulated salaries, nurse stipends, and medical staff bonuses payable.',
                  'category' => 'Liability',
                  'dept' => 'Human Resources & Payroll',
                  'type' => 'Credit',
                  'balance' => '₱880,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '3010',
                  'name' => 'Hospital Capital Reserve',
                  'desc' => 'Retained capital reserves for facility expansion and high-tech equipment.',
                  'category' => 'Equity',
                  'dept' => 'Executive Board',
                  'type' => 'Credit',
                  'balance' => '₱6,330,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '4010',
                  'name' => 'Inpatient Care Revenue',
                  'desc' => 'Gross billings for inpatient rooms, ICU stays, and surgical procedures.',
                  'category' => 'Revenue',
                  'dept' => 'Inpatient & Wards',
                  'type' => 'Credit',
                  'balance' => '₱3,150,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '4020',
                  'name' => 'Outpatient & Laboratory Revenue',
                  'desc' => 'Income generated from outpatient consultations, X-rays, and lab tests.',
                  'category' => 'Revenue',
                  'dept' => 'Laboratory & Outpatient',
                  'type' => 'Credit',
                  'balance' => '₱2,090,000.00',
                  'status' => 'Active'
                ],
                [
                  'code' => '5010',
                  'name' => 'Medical & Surgical Supplies Expense',
                  'desc' => 'Direct operating expenses for surgical gloves, syringes, and PPE.',
                  'category' => 'Expense',
                  'dept' => 'Surgery & Emergency',
                  'type' => 'Debit',
                  'balance' => '₱1,420,000.00',
                  'status' => 'Active'
                ],
              ];
            @endphp

            @foreach($accounts as $acc)
            <tr class="account-row" data-category="{{ strtolower($acc['category']) }}">
              <td>
                <span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">{{ $acc['code'] }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $acc['name'] }}</div>
              </td>
              <td>
                <span class="badge 
                  @if($acc['category'] === 'Asset') bg-success-subtle text-success
                  @elseif($acc['category'] === 'Liability') bg-danger-subtle text-danger
                  @elseif($acc['category'] === 'Equity') bg-primary-subtle text-primary
                  @elseif($acc['category'] === 'Revenue') bg-info-subtle text-info
                  @else bg-warning-subtle text-warning @endif">
                  {{ $acc['category'] }}
                </span>
              </td>
              <td><span class="fs-xs text-muted">{{ $acc['dept'] }}</span></td>
              <td>
                <span class="badge bg-light text-dark border font-monospace fs-xs">{{ $acc['type'] }}</span>
              </td>
              <td class="text-end fw-bold text-dark">{{ $acc['balance'] }}</td>
              <td>
                <span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> {{ $acc['status'] }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('gl.ledger-books') }}" class="btn btn-sm btn-icon btn-outline-secondary" title="View Ledger Book"><i class="ph ph-book-open"></i></a>
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Account"><i class="ph ph-pencil-simple"></i></button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Table Footer with Summary Stats & Pagination -->
    <div class="card-footer bg-transparent border-top p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
      <span id="coaSummaryText" class="text-muted fs-xs">Showing 4 Asset Accounts</span>
      <nav aria-label="COA Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
<!-- Modal: Add New Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add New Master Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="addAccountForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Account Code <span class="text-danger">*</span></label>
              <input type="text" id="modalAccountCode" class="form-control form-control-sm font-monospace" placeholder="e.g. 1060 or 2040" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Account Name <span class="text-danger">*</span></label>
              <input type="text" id="modalAccountName" class="form-control form-control-sm" placeholder="e.g. Emergency ICU Supplies Reserve" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Account Category <span class="text-danger">*</span></label>
              <select id="modalAccountCategory" class="form-select form-select-sm" required>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Revenue">Revenue</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department / Cost Center <span class="text-danger">*</span></label>
              <select id="modalAccountDept" class="form-select form-select-sm" required>
                <option value="Hospital Treasury">Hospital Treasury</option>
                <option value="Treasury / Cashier">Treasury / Cashier</option>
                <option value="Patient Billing / AR">Patient Billing / AR</option>
                <option value="Pharmacy Department">Pharmacy Department</option>
                <option value="Accounts Payable">Accounts Payable</option>
                <option value="Human Resources & Payroll">Human Resources & Payroll</option>
                <option value="Executive Board">Executive Board</option>
                <option value="Inpatient & Wards">Inpatient & Wards</option>
                <option value="Laboratory & Outpatient">Laboratory & Outpatient</option>
                <option value="Surgery & Emergency">Surgery & Emergency</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Normal Balance <span class="text-danger">*</span></label>
              <select id="modalAccountType" class="form-select form-select-sm" required>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Initial Balance (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalAccountBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="0.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const categorySelect = document.getElementById('accountCategorySelect');
  const searchInput = document.getElementById('accountSearchInput');
  const summaryText = document.getElementById('coaSummaryText');
  const btnAddAccount = document.getElementById('btnAddAccount');

  if (btnAddAccount) {
    btnAddAccount.addEventListener('click', function() {
      const modalEl = document.getElementById('addAccountModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterAccounts() {
    const selectedVal = categorySelect ? categorySelect.value.toLowerCase() : 'assets';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.account-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-category'); // e.g. asset, liability, equity, revenue, expense
      const rowText = row.textContent.toLowerCase();

      // Check category match (handle assets vs asset, liabilities vs liability, expenses vs expense)
      let matchCat = false;
      if (selectedVal === 'assets' && rowCat === 'asset') matchCat = true;
      else if (selectedVal === 'liabilities' && rowCat === 'liability') matchCat = true;
      else if (selectedVal === 'expenses' && rowCat === 'expense') matchCat = true;
      else if (selectedVal === rowCat) matchCat = true;

      // Check search query match
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCat && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Update summary text
    if (summaryText && categorySelect) {
      const selectedText = categorySelect.options[categorySelect.selectedIndex].text;
      summaryText.textContent = `Showing ${visibleCount} ${selectedText} Account${visibleCount !== 1 ? 's' : ''}`;
    }

    // Handle empty state display
    let emptyRow = document.getElementById('noAccountsRow');
    const tbody = document.querySelector('#coaTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAccountsRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (categorySelect) {
    categorySelect.addEventListener('change', filterAccounts);
  }
  if (searchInput) {
    searchInput.addEventListener('input', filterAccounts);
    searchInput.addEventListener('keyup', filterAccounts);
  }

  // Add Account Form Submission Handler
  const addAccountForm = document.getElementById('addAccountForm');
  if (addAccountForm) {
    addAccountForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const codeVal = document.getElementById('modalAccountCode').value;
      const nameVal = document.getElementById('modalAccountName').value;
      const categoryVal = document.getElementById('modalAccountCategory').value;
      const deptVal = document.getElementById('modalAccountDept').value;
      const typeVal = document.getElementById('modalAccountType').value;
      const rawBalance = parseFloat(document.getElementById('modalAccountBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let badgeClass = 'bg-secondary-subtle text-secondary';
      if (categoryVal === 'Asset') badgeClass = 'bg-success-subtle text-success';
      else if (categoryVal === 'Liability') badgeClass = 'bg-danger-subtle text-danger';
      else if (categoryVal === 'Equity') badgeClass = 'bg-primary-subtle text-primary';
      else if (categoryVal === 'Revenue') badgeClass = 'bg-info-subtle text-info';
      else if (categoryVal === 'Expense') badgeClass = 'bg-warning-subtle text-warning';

      const tbody = document.querySelector('#coaTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'account-row';
        newRow.setAttribute('data-category', categoryVal.toLowerCase());

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">${codeVal}</span></td>
          <td><div class="fw-semibold text-dark">${nameVal}</div></td>
          <td><span class="badge ${badgeClass}">${categoryVal}</span></td>
          <td><span class="fs-xs text-muted">${deptVal}</span></td>
          <td><span class="badge bg-light text-dark border font-monospace fs-xs">${typeVal}</span></td>
          <td class="text-end fw-bold text-dark">${formattedBalance}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
          <td class="text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('gl.ledger-books') }}" class="btn btn-sm btn-icon btn-outline-secondary" title="View Ledger Book"><i class="ph ph-book-open"></i></a>
              <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Account"><i class="ph ph-pencil-simple"></i></button>
            </div>
          </td>
        `;

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      // Auto-switch category filter to match new account category if needed
      if (categorySelect) {
        let matchOption = 'assets';
        if (categoryVal === 'Asset') matchOption = 'assets';
        else if (categoryVal === 'Liability') matchOption = 'liabilities';
        else if (categoryVal === 'Equity') matchOption = 'equity';
        else if (categoryVal === 'Revenue') matchOption = 'revenue';
        else if (categoryVal === 'Expense') matchOption = 'expenses';
        categorySelect.value = matchOption;
      }

      // Close modal
      const modalEl = document.getElementById('addAccountModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      // Reset form
      addAccountForm.reset();

      // Run filter to display new row
      filterAccounts();
    });
  }

  // Initial execution to filter based on default selected value (Assets)
  filterAccounts();
});
</script>
@endpush
