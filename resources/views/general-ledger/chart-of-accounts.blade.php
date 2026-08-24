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
      <button class="btn btn-outline-secondary btn-sm" onclick="alert('Exporting Chart of Accounts Schedule PDF...');"><i class="ph ph-download-simple me-1"></i> Export COA</button>
      <button id="btnAddAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="ph ph-plus me-1"></i> Add Account</button>
    </div>
  </div>

  <!-- Summary Cards Row (Clean 5-Column Grid) -->
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Assets</span>
          <span class="p-2 rounded-3 bg-success-subtle text-success fs-xs"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format(($accounts ?? collect())->where('category', 'Asset')->sum('current_balance'), 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities</span>
          <span class="p-2 rounded-3 bg-danger-subtle text-danger fs-xs"><i class="ph ph-warning-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format(($accounts ?? collect())->where('category', 'Liability')->sum('current_balance'), 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Equity</span>
          <span class="p-2 rounded-3 bg-primary-subtle text-primary fs-xs"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format(($accounts ?? collect())->where('category', 'Equity')->sum('current_balance'), 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Revenue</span>
          <span class="p-2 rounded-3 bg-info-subtle text-info fs-xs"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format(($accounts ?? collect())->where('category', 'Revenue')->sum('current_balance'), 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Expenses</span>
          <span class="p-2 rounded-3 bg-warning-subtle text-warning fs-xs"><i class="ph ph-chart-line-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format(($accounts ?? collect())->where('category', 'Expense')->sum('current_balance'), 2) }}</h4>
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
            <option value="" selected>All Categories</option>
            <option value="assets">Assets</option>
            <option value="liabilities">Liabilities</option>
            <option value="equity">Equity</option>
            <option value="revenue">Revenue</option>
            <option value="expenses">Expenses</option>
          </select>
        </div>

        <!-- Search Bar -->
        <div class="search-box ms-auto" style="width: 280px;">
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
                 <tbody>
            @forelse($accounts as $acc)
            @php
              $code = is_array($acc) ? $acc['code'] : $acc->code;
              $name = is_array($acc) ? $acc['name'] : $acc->name;
              $category = is_array($acc) ? $acc['category'] : ucfirst(strtolower($acc->category));
              $catType = is_array($acc) ? $acc['cat_type'] : strtolower($acc->category);
              $dept = is_array($acc) ? $acc['dept'] : ($acc->department ?? 'General');
              $normalBalance = is_array($acc) ? $acc['type'] : ucfirst(strtolower($acc->normal_balance));
              $status = is_array($acc) ? $acc['status'] : ($acc->is_active ? 'Active' : 'Inactive');
              $badgeClass = match(strtolower($category)) {
                'asset' => 'bg-success-subtle text-success',
                'liability' => 'bg-danger-subtle text-danger',
                'equity' => 'bg-primary-subtle text-primary',
                'revenue' => 'bg-info-subtle text-info',
                default => 'bg-warning-subtle text-warning',
              };
              $accData = [
                'code' => $code,
                'name' => $name,
                'desc' => 'Chart of accounts ledger item for ' . $name,
                'category' => $category,
                'cat_type' => $catType,
                'dept' => $dept,
                'type' => $normalBalance,
                'balance' => '₱' . number_format(is_array($acc) ? 250000 : 500000, 2),
                'status' => $status,
                'badge' => $badgeClass
              ];
            @endphp
            <tr class="account-row" style="cursor: pointer;" data-category="{{ $catType }}" onclick="openAccountDetailsModal({{ json_encode($accData) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">{{ $code }}</span></td>
              <td><div class="fw-semibold text-dark">{{ $name }}</div></td>
              <td><span class="badge {{ $badgeClass }}">{{ $category }}</span></td>
              <td><span class="fs-xs text-muted">{{ $dept }}</span></td>
              <td><span class="badge bg-light text-dark border font-monospace fs-xs">{{ $normalBalance }}</span></td>
              <td class="text-end fw-bold text-dark font-monospace">₱500,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> {{ $status }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Details" onclick="openAccountDetailsModal({{ json_encode($accData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="coaSummaryText">Showing {{ count($accounts ?? []) }} Accounts</span>
      <nav aria-label="COA Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Account Details (Executive Design) -->
<div class="modal fade" id="accountDetailsModal" tabindex="-1" aria-labelledby="accountDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAccCode">1010</span>
            <span class="badge bg-success-subtle text-success" id="detailAccCategory">Asset Account</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAccName">Cash on Hand - Main Vault</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Ledger Balance</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailAccBalance">₱250,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Normal Accounting Balance</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailAccType">Debit</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-buildings me-1 text-primary"></i> Organizational Mapping</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Assigned Department / Cost Center</span>
              <span class="font-monospace fw-bold text-dark" id="detailAccDept">Treasury / Cashier</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Account Operational Description</span>
              <span class="text-muted" id="detailAccDesc">Physical currency drawer held in hospital main vault.</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; General Ledger Control</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Double-Entry Ledger Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Active GL Account Code</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-COA-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Ledger Book for Account...');"><i class="ph ph-book-open me-1"></i> Open Ledger Book</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add New Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add New GL Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="addAccountForm">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">GL Account Code <span class="text-danger">*</span></label>
              <input type="text" id="modalAccountCode" class="form-control form-control-sm font-monospace" placeholder="e.g. 1060" required>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
              <input type="text" id="modalAccountName" class="form-control form-control-sm" placeholder="e.g. Allowance for Doubtful Accounts" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Account Category <span class="text-danger">*</span></label>
              <select id="modalAccountCategory" class="form-select form-select-sm" required>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Revenue">Revenue</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Department / Cost Center <span class="text-danger">*</span></label>
              <input type="text" id="modalAccountDept" class="form-control form-control-sm" placeholder="e.g. Financial Services" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Normal Balance <span class="text-danger">*</span></label>
              <select id="modalAccountType" class="form-select form-select-sm" required>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Initial Opening Balance (₱)</label>
              <input type="number" id="modalAccountBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="0.00">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Operational Description</label>
              <input type="text" id="modalAccountDesc" class="form-control form-control-sm" placeholder="e.g. Account description..." value="Newly created account ledger">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save GL Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openAccountDetailsModal(acc) {
  if (!acc) return;

  document.getElementById('detailAccName').textContent = acc.name || 'Account Name';
  document.getElementById('detailAccCode').textContent = acc.code || '0000';
  document.getElementById('detailAccCategory').textContent = acc.category || 'Asset';
  document.getElementById('detailAccDept').textContent = acc.dept || '-';
  document.getElementById('detailAccType').textContent = acc.type || 'Debit';
  document.getElementById('detailAccBalance').textContent = acc.balance || '₱0.00';
  document.getElementById('detailAccDesc').textContent = acc.desc || '-';

  const modalEl = document.getElementById('accountDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

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
    const selectedCategory = categorySelect ? categorySelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const rows = document.querySelectorAll('.account-row');
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
      summaryText.textContent = `Showing ${visibleCount} Account${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noAccountRow');
    const tbody = document.querySelector('#coaTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAccountRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (categorySelect) categorySelect.addEventListener('change', filterAccounts);
  if (searchInput) {
    searchInput.addEventListener('input', filterAccounts);
    searchInput.addEventListener('keyup', filterAccounts);
  }

  const addAccountForm = document.getElementById('addAccountForm');
  if (addAccountForm) {
    addAccountForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const codeVal = document.getElementById('modalAccountCode').value;
      const nameVal = document.getElementById('modalAccountName').value;
      const categoryVal = document.getElementById('modalAccountCategory').value;
      const deptVal = document.getElementById('modalAccountDept').value;
      const typeVal = document.getElementById('modalAccountType').value;
      const descVal = document.getElementById('modalAccountDesc').value || 'Newly created account ledger';
      const rawBalance = parseFloat(document.getElementById('modalAccountBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let catType = categoryVal.toLowerCase() + 's';
      if (categoryVal === 'Liability') catType = 'liabilities';
      else if (categoryVal === 'Equity') catType = 'equity';

      let badgeClass = 'bg-secondary-subtle text-secondary';
      if (categoryVal === 'Asset') badgeClass = 'bg-success-subtle text-success';
      else if (categoryVal === 'Liability') badgeClass = 'bg-danger-subtle text-danger';
      else if (categoryVal === 'Equity') badgeClass = 'bg-primary-subtle text-primary';
      else if (categoryVal === 'Revenue') badgeClass = 'bg-info-subtle text-info';
      else if (categoryVal === 'Expense') badgeClass = 'bg-warning-subtle text-warning';

      const accObj = {
        code: codeVal,
        name: nameVal,
        desc: descVal,
        category: categoryVal,
        cat_type: catType,
        dept: deptVal,
        type: typeVal,
        balance: formattedBalance,
        status: 'Active',
        badge: badgeClass
      };

      const tbody = document.querySelector('#coaTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'account-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-category', catType);

        newRow.onclick = function() { openAccountDetailsModal(accObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">${codeVal}</span></td>
          <td><div class="fw-semibold text-dark">${nameVal}</div></td>
          <td><span class="badge ${badgeClass}">${categoryVal}</span></td>
          <td><span class="fs-xs text-muted">${deptVal}</span></td>
          <td><span class="badge bg-light text-dark border font-monospace fs-xs">${typeVal}</span></td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedBalance}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Account Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openAccountDetailsModal(accObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('addAccountModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      addAccountForm.reset();
      filterAccounts();
    });
  }

  filterAccounts();
});
</script>
@endpush
