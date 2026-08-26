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
          <li class="breadcrumb-item active">Profit &amp; Loss Statement</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Profit &amp; Loss (Income Statement)</h1>
      <p class="text-muted fs-xs mb-0">Track total healthcare operating revenues minus cost of care and department expenses to calculate net hospital operating surplus or deficit.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['General Ledger (Journal Entries)', 'Chart of Accounts', 'Fiscal Periods']" 
          :tables="['journal_entry_lines', 'journal_entries', 'accounts', 'fiscal_periods']"
          description="Compiles posted double-entry journal lines into standard financial statements." 
      />
      <a href="{{ route('reporting.profit-loss.export', ['date_from' => $dateFrom ?? date('Y-01-01'), 'date_to' => $dateTo ?? date('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Statement (CSV)
      </a>
      <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Formal Statement
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Gross Operating Revenues</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($grossRevenue ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Sales Discounts &amp; Deductions</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-tag fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning">-₱{{ number_format((float) ($salesDiscounts ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Operating Expenses</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Operating Surplus</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        @php
          $isSurplus = (bccomp((string) ($netIncome ?? 0), '0.0000', 4) >= 0);
        @endphp
        <h4 class="fw-bold mb-0 {{ $isSurplus ? 'text-primary' : 'text-danger' }}">
          {{ $isSurplus ? '+' : '' }}₱{{ number_format((float) ($netIncome ?? 0), 2) }}
        </h4>
        <span class="fs-xs {{ $isSurplus ? 'text-success' : 'text-danger' }}">Margin: {{ number_format((float) ($profitMargin ?? 0), 1) }}%</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('reporting.profit-loss') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Reporting Date From:</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? date('Y-01-01') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Reporting Date To:</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? date('Y-m-d') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Department Breakdown:</label>
          <select name="department" class="form-select form-select-sm">
            <option value="">All Hospital Departments</option>
            <option value="Emergency" {{ ($department ?? '') === 'Emergency' ? 'selected' : '' }}>Emergency Room (ER)</option>
            <option value="Inpatient" {{ ($department ?? '') === 'Inpatient' ? 'selected' : '' }}>Inpatient Wards &amp; ICU</option>
            <option value="Outpatient" {{ ($department ?? '') === 'Outpatient' ? 'selected' : '' }}>Outpatient Department (OPD)</option>
            <option value="Laboratory" {{ ($department ?? '') === 'Laboratory' ? 'selected' : '' }}>Laboratory &amp; Diagnostics</option>
            <option value="Pharmacy" {{ ($department ?? '') === 'Pharmacy' ? 'selected' : '' }}>Pharmacy &amp; Therapeutics</option>
            <option value="Radiology" {{ ($department ?? '') === 'Radiology' ? 'selected' : '' }}>Radiology &amp; Imaging</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-end">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-arrows-clockwise me-1"></i> Recompute P&amp;L</button>
          <a href="{{ route('reporting.profit-loss') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Income Statement Sections -->
  <div class="row g-4">
    <!-- Revenue Section -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-success-subtle border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-success"><i class="ph ph-arrow-circle-down-right me-2"></i>OPERATING REVENUES</h5>
          <span class="fs-xs font-monospace text-success fw-bold">₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Revenue Stream</th>
                  <th>Department</th>
                  <th class="text-end">Amount (₱)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($revenues ?? [] as $r)
                <tr>
                  <td><span class="badge bg-light text-dark border font-monospace">{{ $r['code'] }}</span></td>
                  <td class="fw-semibold text-dark">{{ $r['name'] }}</td>
                  <td class="text-muted">{{ $r['department'] }}</td>
                  <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) $r['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">No revenue journal lines posted in period.</td>
                </tr>
                @endforelse
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="3" class="text-uppercase text-dark">NET OPERATING REVENUES</td>
                  <td class="text-end font-monospace text-success fs-6">₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Expenses Section -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-danger-subtle border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-danger"><i class="ph ph-arrow-circle-up-right me-2"></i>OPERATING EXPENSES</h5>
          <span class="fs-xs font-monospace text-danger fw-bold">₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Expense Category</th>
                  <th>Department</th>
                  <th class="text-end">Amount (₱)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($expenses ?? [] as $e)
                <tr>
                  <td><span class="badge bg-light text-dark border font-monospace">{{ $e['code'] }}</span></td>
                  <td class="fw-semibold text-dark">{{ $e['name'] }}</td>
                  <td class="text-muted">{{ $e['department'] }}</td>
                  <td class="text-end font-monospace text-danger fw-bold">₱{{ number_format((float) $e['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">No operating expenses recorded in period.</td>
                </tr>
                @endforelse
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="3" class="text-uppercase text-dark">TOTAL OPERATING EXPENSES</td>
                  <td class="text-end font-monospace text-danger fs-6">₱{{ number_format((float) ($totalExpense ?? 0), 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
