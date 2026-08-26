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
          <li class="breadcrumb-item"><a href="{{ route('gl.journal-entries') }}">General Ledger</a></li>
          <li class="breadcrumb-item active" aria-current="page">Chart of Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Chart of Accounts</h1>
      <p class="text-muted fs-xs mb-0">Master register of active and archived General Ledger balance sheet &amp; nominal accounts.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="standalone" 
          description="Master system accounting and statutory tax setup." 
      />
      <a href="{{ route('gl.trial-balance.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-download-simple me-1"></i> Export COA Schedule
      </a>
      <button id="btnAddAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal">
        <i class="ph ph-plus me-1"></i> Add Account
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-warning-circle me-1"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Summary Cards Row (Clean 5-Column Grid) -->
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Assets (1000s)</span>
          <span class="p-2 rounded-3 bg-success-subtle text-success fs-xs"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($assetTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities (2000s)</span>
          <span class="p-2 rounded-3 bg-danger-subtle text-danger fs-xs"><i class="ph ph-warning-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($liabilityTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Equity (3000s)</span>
          <span class="p-2 rounded-3 bg-primary-subtle text-primary fs-xs"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($equityTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Revenue (4000s)</span>
          <span class="p-2 rounded-3 bg-info-subtle text-info fs-xs"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($revenueTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Expenses (5000s)</span>
          <span class="p-2 rounded-3 bg-warning-subtle text-warning fs-xs"><i class="ph ph-chart-line-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($expenseTotal ?? 0, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Main Table Section -->
  <div class="card border-0 shadow-sm rounded-3">
    <!-- Toolbar Header -->
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('gl.chart-of-accounts') }}" id="coaFilterForm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <!-- Category Filter Dropdown -->
          <div class="d-flex align-items-center gap-2">
            <label for="accountCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Category:</label>
            <select name="category" id="accountCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 180px;" onchange="this.form.submit()">
              <option value="" {{ empty($category) ? 'selected' : '' }}>All Categories</option>
              <option value="ASSET" {{ ($category ?? '') === 'ASSET' ? 'selected' : '' }}>Assets</option>
              <option value="LIABILITY" {{ ($category ?? '') === 'LIABILITY' ? 'selected' : '' }}>Liabilities</option>
              <option value="EQUITY" {{ ($category ?? '') === 'EQUITY' ? 'selected' : '' }}>Equity</option>
              <option value="REVENUE" {{ ($category ?? '') === 'REVENUE' ? 'selected' : '' }}>Revenue</option>
              <option value="EXPENSE" {{ ($category ?? '') === 'EXPENSE' ? 'selected' : '' }}>Expenses</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="d-flex align-items-center gap-2">
            <label for="accountStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
            <select name="status" id="accountStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 150px;" onchange="this.form.submit()">
              <option value="" {{ empty($status) ? 'selected' : '' }}>All Statuses</option>
              <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
              <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
          </div>

          <!-- Search Bar -->
          <div class="search-box ms-auto" style="width: 280px;">
            <i class="ph ph-magnifying-glass"></i>
            <input type="search" name="q" id="accountSearchInput" class="form-control form-control-sm" placeholder="Search code, name, unit..." value="{{ $search ?? '' }}">
          </div>
        </div>
      </form>
    </div>

    <!-- Accounts Table -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="coaTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" style="width: 110px;">Code</th>
              <th scope="col">Account Name</th>
              <th scope="col">Type / Category</th>
              <th scope="col">Department</th>
              <th scope="col">Normal Balance</th>
              <th scope="col" class="text-end">Current Balance (₱)</th>
              <th scope="col" class="text-center">Active Status</th>
              <th scope="col" class="text-end" style="width: 140px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts as $acc)
            @php
              $code          = $acc->code;
              $name          = $acc->name;
              $catUpper      = strtoupper((string) $acc->category);
              $dept          = $acc->department ?? 'General';
              $normalBalance = strtoupper((string) $acc->normal_balance);
              $balance       = (float) $acc->current_balance;
              $hasLines      = $acc->journalEntryLines->isNotEmpty();

              $badgeClass = match($catUpper) {
                'ASSET'     => 'bg-success-subtle text-success',
                'LIABILITY' => 'bg-danger-subtle text-danger',
                'EQUITY'    => 'bg-primary-subtle text-primary',
                'REVENUE'   => 'bg-info-subtle text-info',
                'EXPENSE'   => 'bg-warning-subtle text-warning',
                default     => 'bg-secondary-subtle text-secondary',
              };

              $accJson = json_encode([
                'id'             => $acc->id,
                'code'           => $acc->code,
                'name'           => $acc->name,
                'category'       => $catUpper,
                'normal_balance' => $normalBalance,
                'department'     => $acc->department,
                'is_active'      => $acc->is_active,
                'balance'        => '₱' . number_format($balance, 2),
                'has_lines'      => $hasLines,
              ]);
            @endphp
            <tr class="account-row" style="cursor: pointer;" onclick="openAccountDetailsModal({{ $accJson }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">{{ $code }}</span></td>
              <td><div class="fw-semibold text-dark">{{ $name }}</div></td>
              <td><span class="badge {{ $badgeClass }}">{{ $catUpper }}</span></td>
              <td><span class="fs-xs text-muted">{{ $dept }}</span></td>
              <td><span class="badge bg-light text-dark border font-monospace fs-xs">{{ $normalBalance }}</span></td>
              <td class="text-end fw-bold text-dark font-monospace">₱{{ number_format($balance, 2) }}</td>
              <td class="text-center" onclick="event.stopPropagation();">
                <form action="{{ route('gl.chart-of-accounts.toggle-status', $acc->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-sm py-0 px-2 {{ $acc->is_active ? 'btn-success' : 'btn-secondary' }}" style="font-size: 0.75rem;" title="{{ $acc->is_active ? 'Click to Deactivate' : 'Click to Activate' }}">
                    <i class="ph {{ $acc->is_active ? 'ph-check-circle' : 'ph-x-circle' }} me-1"></i>
                    {{ $acc->is_active ? 'ACTIVE' : 'INACTIVE' }}
                  </button>
                </form>
              </td>
              <td class="text-end" onclick="event.stopPropagation();">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('gl.ledger-books', ['account_id' => $acc->id]) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View Ledger Book">
                    <i class="ph ph-book-open"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Account" onclick="openEditAccountModal({{ $accJson }})">
                    <i class="ph ph-pencil-simple"></i>
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="ph ph-folder-dashed fs-2 d-block mb-2 text-secondary"></i>
                No GL accounts registered matching query criteria.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="coaSummaryText">Showing {{ count($accounts ?? []) }} General Ledger Accounts</span>
      <div class="fs-xs text-muted">GAAP / IFRS Double-Entry Compliant</div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Account Details -->
<div class="modal fade" id="accountDetailsModal" tabindex="-1" aria-labelledby="accountDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAccCode">1010</span>
            <span class="badge bg-success-subtle text-success" id="detailAccCategory">Asset</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAccName">Account Title</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Ledger Balance</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailAccBalance">₱0.00</h4>
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
              <span class="font-monospace fw-bold text-dark" id="detailAccDept">-</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Active Posting Status:</span>
              <span class="badge bg-success-subtle text-success" id="detailAccStatus">ACTIVE</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail Control -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> BIR CAS Audit &amp; Control</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Has Transaction Activity:</span>
              <span id="detailAccHasLines" class="fw-semibold text-dark">-</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Tamper-Proof Audit Seal:</span>
              <span class="font-monospace text-muted">CAS-SHA256-AUTHENTICATED</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a id="detailAccLedgerLink" href="#" class="btn btn-sm btn-primary"><i class="ph ph-book-open me-1"></i> Open Ledger Book</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add New GL Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-primary text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2"></i>Add New GL Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('gl.chart-of-accounts.store') }}" method="POST">
        @csrf
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">GL Account Code <span class="text-danger">*</span></label>
              <input type="text" name="code" class="form-control form-control-sm font-monospace" placeholder="e.g. 1060" required pattern="[0-9A-Za-z\-]+">
              <div class="form-text fs-xs">Unique numeric/alphanumeric code.</div>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Allowance for Doubtful Accounts" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category" id="addAccCategory" class="form-select form-select-sm" required onchange="autoSetNormalBalance(this.value, 'addAccNormalBalance')">
                <option value="ASSET">ASSET (1000s)</option>
                <option value="LIABILITY">LIABILITY (2000s)</option>
                <option value="EQUITY">EQUITY (3000s)</option>
                <option value="REVENUE">REVENUE (4000s)</option>
                <option value="EXPENSE">EXPENSE (5000s)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Normal Balance <span class="text-danger">*</span></label>
              <select name="normal_balance" id="addAccNormalBalance" class="form-select form-select-sm" required>
                <option value="DEBIT">DEBIT</option>
                <option value="CREDIT">CREDIT</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Department / Cost Center</label>
              <input type="text" name="department" class="form-control form-control-sm" placeholder="e.g. Finance, Nursing, Pharmacy">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-check me-1"></i> Save Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit GL Account -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="editAccountModalLabel"><i class="ph ph-pencil-simple me-2"></i>Edit GL Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editAccountForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">GL Account Code <span class="text-danger">*</span></label>
              <input type="text" name="code" id="editAccCode" class="form-control form-control-sm font-monospace" required>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Account Title / Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="editAccName" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category" id="editAccCategory" class="form-select form-select-sm" required>
                <option value="ASSET">ASSET</option>
                <option value="LIABILITY">LIABILITY</option>
                <option value="EQUITY">EQUITY</option>
                <option value="REVENUE">REVENUE</option>
                <option value="EXPENSE">EXPENSE</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Normal Balance <span class="text-danger">*</span></label>
              <select name="normal_balance" id="editAccNormalBalance" class="form-select form-select-sm" required>
                <option value="DEBIT">DEBIT</option>
                <option value="CREDIT">CREDIT</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Department / Cost Center</label>
              <input type="text" name="department" id="editAccDept" class="form-control form-control-sm">
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-check me-1"></i> Update Account</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function autoSetNormalBalance(category, targetId) {
  const el = document.getElementById(targetId);
  if (!el) return;
  if (category === 'ASSET' || category === 'EXPENSE') {
    el.value = 'DEBIT';
  } else {
    el.value = 'CREDIT';
  }
}

function openAccountDetailsModal(acc) {
  if (!acc) return;
  document.getElementById('detailAccName').textContent = acc.name || 'Account Title';
  document.getElementById('detailAccCode').textContent = acc.code || '0000';
  document.getElementById('detailAccCategory').textContent = acc.category || 'ASSET';
  document.getElementById('detailAccDept').textContent = acc.department || 'General';
  document.getElementById('detailAccType').textContent = acc.normal_balance || 'DEBIT';
  document.getElementById('detailAccBalance').textContent = acc.balance || '₱0.00';
  document.getElementById('detailAccStatus').textContent = acc.is_active ? 'ACTIVE' : 'INACTIVE';
  document.getElementById('detailAccHasLines').textContent = acc.has_lines ? 'Yes (Ledger History Recorded)' : 'No (Zero transactions)';
  document.getElementById('detailAccLedgerLink').href = "{{ route('gl.ledger-books') }}?account_id=" + acc.id;

  const modalEl = document.getElementById('accountDetailsModal');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

function openEditAccountModal(acc) {
  if (!acc) return;
  document.getElementById('editAccCode').value = acc.code || '';
  document.getElementById('editAccName').value = acc.name || '';
  document.getElementById('editAccCategory').value = acc.category || 'ASSET';
  document.getElementById('editAccNormalBalance').value = acc.normal_balance || 'DEBIT';
  document.getElementById('editAccDept').value = acc.department || '';

  const form = document.getElementById('editAccountForm');
  form.action = "{{ url('/general-ledger/chart-of-accounts') }}/" + acc.id;

  const modalEl = document.getElementById('editAccountModal');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}
</script>
@endpush
