@extends('layouts.app')

@section('title', 'Bank Accounts - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-accounts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Bank Accounts Master Register</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Syncing bank account balances with GL...');"><i class="ph ph-arrows-counter-clockwise me-1"></i> Refresh Balances</button>
      <button id="btnAddAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="ph ph-plus-circle me-1"></i> Add Bank Account</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Bank Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($bankAccounts ?? []) }} Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Ledger Cash</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format(($bankAccounts ?? collect())->sum('balance'), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Main Operating Account</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        @php $mainAccount = ($bankAccounts ?? collect())->where('purpose', 'like', '%Operations%')->first(); @endphp
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($mainAccount?->balance ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Collections Account</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        @php $collectionsAccount = ($bankAccounts ?? collect())->where('purpose', 'like', '%Collections%')->first(); @endphp
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($collectionsAccount?->balance ?? 0, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="accountPurposeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Purpose:</label>
          <select id="accountPurposeSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Account Purposes</option>
            <option value="operations">Primary Operations &amp; Payroll</option>
            <option value="collections">Collections &amp; HMO Deposits</option>
            <option value="reserve">Emergency Capital Reserve</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="currencySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Currency:</label>
          <select id="currencySelect" class="form-select form-select-sm bg-light" style="min-width: 150px;">
            <option value="" selected>All Currencies</option>
            <option value="php">PHP (₱)</option>
            <option value="usd">USD ($)</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="accountSearchInput" class="form-control form-control-sm" placeholder="Search bank, account no, purpose...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="bankAccountTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bank Name &amp; Branch</th>
              <th>Account Number</th>
              <th>Account Purpose</th>
              <th>Currency</th>
              <th class="text-end">Ledger Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bankAccounts ?? [] as $acc)
            @php
              $bank    = $acc->name . ' (' . $acc->bank_name . ')';
              $no      = $acc->account_number;
              $purpose = $acc->purpose;
              $curr    = $acc->currency;
              $bal     = '₱' . number_format($acc->balance, 2);
              $gl      = $acc->gl_code;
              $accData = [
                'bank'     => $bank,
                'no'       => $no,
                'purpose'  => $purpose,
                'currency' => $curr,
                'balance'  => $bal,
                'gl_code'  => $gl,
              ];
            @endphp
            <tr class="account-row" style="cursor: pointer;" onclick="openBankAccountDetailsModal({{ json_encode($accData) }})">
              <td class="fw-bold text-dark">{{ $bank }}</td>
              <td><span class="font-monospace text-primary fw-bold">{{ $no }}</span></td>
              <td class="fs-xs text-muted">{{ $purpose }}</td>
              <td><span class="badge bg-light text-dark border">{{ $curr }}</span></td>
              <td class="text-end text-success fw-bold font-monospace">{{ $bal }}</td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Details" onclick="openBankAccountDetailsModal({{ json_encode($accData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No bank accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="accountSummaryText">Showing {{ count($bankAccounts ?? []) }} Bank Accounts</span>
      <nav aria-label="Account Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Bank Account Details (Executive Design) -->
<div class="modal fade" id="bankAccountDetailsModal" tabindex="-1" aria-labelledby="bankAccountDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAccNo">1020-8841-99</span>
            <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active Commercial Account</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAccBank">Metrobank - Main Branch</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Ledger Cash Balance</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailAccBalance">₱4,850,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">GL Account Link Code</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailAccGlCode">1010-01-METRO</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-bank me-1 text-primary"></i> Account Purpose &amp; Currency</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Designated Purpose</span>
              <span class="fw-semibold text-dark" id="detailAccPurpose">Primary Operations &amp; Payroll Payouts</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Currency Denomination</span>
              <span class="badge bg-light text-dark border" id="detailAccCurrency">PHP (₱)</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Bank Reconciliation Status</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">GL Ledger Reconciliation:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Reconciled &amp; Verified</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-BANK-2026-01 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Bank Account Transaction Statement...');"><i class="ph ph-file-text me-1"></i> Export Statement</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Bank Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add Hospital Bank Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="addAccountForm">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank &amp; Branch Name <span class="text-danger">*</span></label>
            <input type="text" id="modalAccBank" class="form-control form-control-sm" placeholder="e.g. Landbank - City Hall Branch" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Number <span class="text-danger">*</span></label>
            <input type="text" id="modalAccNo" class="form-control form-control-sm font-monospace" placeholder="0000-0000-00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Purpose <span class="text-danger">*</span></label>
            <input type="text" id="modalAccPurpose" class="form-control form-control-sm" placeholder="e.g. Government Health Program Subsidy" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Opening Balance (₱) <span class="text-danger">*</span></label>
            <input type="number" id="modalAccBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="500000.00" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openBankAccountDetailsModal(acc) {
  if (!acc) return;

  document.getElementById('detailAccBank').textContent = acc.bank || 'Bank Name';
  document.getElementById('detailAccNo').textContent = acc.no || '0000-0000-00';
  document.getElementById('detailAccPurpose').textContent = acc.purpose || '-';
  document.getElementById('detailAccCurrency').textContent = acc.currency || 'PHP (₱)';
  document.getElementById('detailAccBalance').textContent = acc.balance || '₱0.00';
  document.getElementById('detailAccGlCode').textContent = acc.gl_code || '1010-00';

  const modalEl = document.getElementById('bankAccountDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('accountSearchInput');
  const purposeSelect = document.getElementById('accountPurposeSelect');
  const currencySelect = document.getElementById('currencySelect');
  const summaryText = document.getElementById('accountSummaryText');
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
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedPurpose = purposeSelect ? purposeSelect.value.toLowerCase() : '';
    const selectedCurrency = currencySelect ? currencySelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.account-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowPurpose = row.getAttribute('data-purpose') || '';
      const rowCurrency = row.getAttribute('data-currency') || '';
      const rowText = row.textContent.toLowerCase();

      const matchPurpose = !selectedPurpose || rowPurpose.includes(selectedPurpose);
      const matchCurrency = !selectedCurrency || rowCurrency.includes(selectedCurrency);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchPurpose && matchCurrency && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Bank Account${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noAccountRow');
    const tbody = document.querySelector('#bankAccountTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAccountRow';
        emptyRow.innerHTML = `<td colspan="6" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No bank accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterAccounts);
    searchInput.addEventListener('keyup', filterAccounts);
  }
  if (purposeSelect) purposeSelect.addEventListener('change', filterAccounts);
  if (currencySelect) currencySelect.addEventListener('change', filterAccounts);

  const addAccountForm = document.getElementById('addAccountForm');
  if (addAccountForm) {
    addAccountForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const bankVal = document.getElementById('modalAccBank').value;
      const noVal = document.getElementById('modalAccNo').value;
      const purposeVal = document.getElementById('modalAccPurpose').value;
      const rawBalance = parseFloat(document.getElementById('modalAccBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextGl = '1010-04-' + Math.floor(10 + Math.random() * 90);

      const accObj = {
        bank: bankVal,
        no: noVal,
        purpose: purposeVal,
        currency: 'PHP (₱)',
        balance: formattedBalance,
        gl_code: nextGl
      };

      const tbody = document.querySelector('#bankAccountTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'account-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-purpose', purposeVal.toLowerCase());
        newRow.setAttribute('data-currency', 'php (₱)');

        newRow.onclick = function() { openBankAccountDetailsModal(accObj); };

        newRow.innerHTML = `
          <td class="fw-bold text-dark">${bankVal}</td>
          <td><span class="font-monospace text-primary fw-bold">${noVal}</span></td>
          <td class="fs-xs text-muted">${purposeVal}</td>
          <td><span class="badge bg-light text-dark border">PHP (₱)</span></td>
          <td class="text-end text-success fw-bold font-monospace">${formattedBalance}</td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Account Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Account Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openBankAccountDetailsModal(accObj);
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
