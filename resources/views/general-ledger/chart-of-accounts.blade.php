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
      <p class="text-muted small mb-0">Master index of financial accounts structured for hospital financial reporting.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export COA</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add Account</button>
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
        <h4 class="fw-bold mb-1 text-dark">₱8,450,000.00</h4>
        <span class="fs-xs text-muted">14 Master Accounts</span>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Total Liabilities</span>
          <span class="p-2 rounded-3 bg-danger-subtle text-danger fs-xs"><i class="ph ph-warning-circle"></i></span>
        </div>
        <h4 class="fw-bold mb-1 text-dark">₱2,120,000.00</h4>
        <span class="fs-xs text-muted">8 Vendor Accounts</span>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Total Equity</span>
          <span class="p-2 rounded-3 bg-primary-subtle text-primary fs-xs"><i class="ph ph-scales"></i></span>
        </div>
        <h4 class="fw-bold mb-1 text-dark">₱6,330,000.00</h4>
        <span class="fs-xs text-muted">4 Capital Reserves</span>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Operating Revenue</span>
          <span class="p-2 rounded-3 bg-info-subtle text-info fs-xs"><i class="ph ph-receipt"></i></span>
        </div>
        <h4 class="fw-bold mb-1 text-dark">₱5,240,000.00</h4>
        <span class="fs-xs text-muted">YTD Care &amp; Sales</span>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small">Operating Expenses</span>
          <span class="p-2 rounded-3 bg-warning-subtle text-warning fs-xs"><i class="ph ph-chart-line-down"></i></span>
        </div>
        <h4 class="fw-bold mb-1 text-dark">₱3,180,000.00</h4>
        <span class="fs-xs text-muted">YTD OPEX &amp; Supplies</span>
      </div>
    </div>
  </div>

  <!-- Main Table Section -->
  <div class="card border-0 shadow-sm rounded-3">
    <!-- Toolbar Header -->
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Category Filter Tabs -->
        <ul class="nav nav-pills flex-grow-1" id="accountCategoryTabs">
          <li class="nav-item">
            <button class="nav-link active btn-sm py-1 px-3 me-1 fw-semibold" data-bs-toggle="pill">All Accounts (10)</button>
          </li>
          <li class="nav-item">
            <button class="nav-link btn-sm py-1 px-3 me-1" data-bs-toggle="pill">Assets</button>
          </li>
          <li class="nav-item">
            <button class="nav-link btn-sm py-1 px-3 me-1" data-bs-toggle="pill">Liabilities</button>
          </li>
          <li class="nav-item">
            <button class="nav-link btn-sm py-1 px-3 me-1" data-bs-toggle="pill">Equity</button>
          </li>
          <li class="nav-item">
            <button class="nav-link btn-sm py-1 px-3 me-1" data-bs-toggle="pill">Revenue</button>
          </li>
          <li class="nav-item">
            <button class="nav-link btn-sm py-1 px-3 me-1" data-bs-toggle="pill">Expenses</button>
          </li>
        </ul>

        <!-- Search Bar -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search code, name, or unit...">
        </div>
      </div>
    </div>

    <!-- Accounts Table -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" style="width: 110px;">Code</th>
              <th scope="col">Account Name &amp; Description</th>
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
            <tr>
              <td>
                <span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">{{ $acc['code'] }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $acc['name'] }}</div>
                <div class="text-muted fs-xs">{{ $acc['desc'] }}</div>
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
      <span class="text-muted fs-xs">Showing 1 to 10 of 10 Master Accounts</span>
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
@endsection
