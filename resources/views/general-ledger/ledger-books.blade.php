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
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('GL Books exported!');"><i class="ph ph-file-arrow-down me-1"></i> Export Master GL</button>
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
        <h4 class="fw-bold mb-0 text-dark">48 Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Debit Movement</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱54,110,200.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Credit Movement</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱54,110,200.00</h4>
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

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0" id="glSearchIcon" style="cursor: pointer;"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" id="glSearchInput" class="form-control bg-light border-start-0" placeholder="Search Account Code or Account Name...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="glTypeSelect" class="form-select form-select-sm bg-light">
            <option value="">All Account Types</option>
            <option value="asset">1000 - Assets</option>
            <option value="liability">2000 - Liabilities</option>
            <option value="equity">3000 - Equity</option>
            <option value="revenue">4000 - Revenue</option>
            <option value="expense">5000 - Expenses</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="glPeriodSelect" class="form-select form-select-sm bg-light">
            <option value="">All Fiscal Periods</option>
            <option value="ytd">FY 2026 Year-To-Date</option>
            <option value="q2">Q2 2026</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
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
            @php
              $glAccounts = [
                [
                  'code' => '1010',
                  'name' => 'Metrobank Operating Cash Account',
                  'type' => 'Asset',
                  'type_key' => 'asset',
                  'opening' => '₱2,500,000.00',
                  'debit' => '+₱8,450,000.00',
                  'credit' => '-₱6,100,000.00',
                  'ending' => '₱4,850,000.00',
                  'badge' => 'bg-success-subtle text-success'
                ],
                [
                  'code' => '1200',
                  'name' => 'Accounts Receivable (Patients & HMOs)',
                  'type' => 'Asset',
                  'type_key' => 'asset',
                  'opening' => '₱1,850,000.00',
                  'debit' => '+₱7,620,000.00',
                  'credit' => '-₱6,399,800.00',
                  'ending' => '₱3,070,200.00',
                  'badge' => 'bg-success-subtle text-success'
                ],
                [
                  'code' => '2010',
                  'name' => 'Accounts Payable (Medical Suppliers & Vendors)',
                  'type' => 'Liability',
                  'type_key' => 'liability',
                  'opening' => '₱980,000.00',
                  'debit' => '+₱3,400,000.00',
                  'credit' => '-₱4,520,000.00',
                  'ending' => '₱2,100,000.00',
                  'badge' => 'bg-danger-subtle text-danger'
                ],
                [
                  'code' => '3010',
                  'name' => 'Hospital Capital Reserve & Retained Capital',
                  'type' => 'Equity',
                  'type_key' => 'equity',
                  'opening' => '₱6,330,000.00',
                  'debit' => '+₱0.00',
                  'credit' => '-₱0.00',
                  'ending' => '₱6,330,000.00',
                  'badge' => 'bg-primary-subtle text-primary'
                ],
                [
                  'code' => '4010',
                  'name' => 'Inpatient & Emergency Care Service Revenue',
                  'type' => 'Revenue',
                  'type_key' => 'revenue',
                  'opening' => '₱0.00',
                  'debit' => '+₱0.00',
                  'credit' => '-₱5,240,000.00',
                  'ending' => '₱5,240,000.00',
                  'badge' => 'bg-info-subtle text-info'
                ],
                [
                  'code' => '5010',
                  'name' => 'Medical & Surgical Supplies Expense',
                  'type' => 'Expense',
                  'type_key' => 'expense',
                  'opening' => '₱0.00',
                  'debit' => '+₱3,180,000.00',
                  'credit' => '-₱0.00',
                  'ending' => '₱3,180,000.00',
                  'badge' => 'bg-warning-subtle text-warning'
                ],
              ];
            @endphp

            @foreach($glAccounts as $gl)
            <tr class="gl-row" data-type="{{ $gl['type_key'] }}">
              <td><span class="font-monospace text-primary fw-bold">{{ $gl['code'] }}</span></td>
              <td><div class="fw-bold text-dark">{{ $gl['name'] }}</div></td>
              <td><span class="badge {{ $gl['badge'] }}">{{ $gl['type'] }}</span></td>
              <td class="text-end font-monospace">{{ $gl['opening'] }}</td>
              <td class="text-end text-success font-monospace">{{ $gl['debit'] }}</td>
              <td class="text-end text-danger font-monospace">{{ $gl['credit'] }}</td>
              <td class="text-end text-primary fw-bold font-monospace">{{ $gl['ending'] }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Account Movement Ledger"><i class="ph ph-list-numbers"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const typeSelect = document.getElementById('glTypeSelect');
  const periodSelect = document.getElementById('glPeriodSelect');
  const searchInput = document.getElementById('glSearchInput');
  const searchIcon = document.getElementById('glSearchIcon');

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
  if (searchIcon) searchIcon.addEventListener('click', filterGLBooks);

  filterGLBooks();
});
</script>
@endpush
