@extends('layouts.app')

@section('title', 'Balance Sheet Statement - Financial Reporting | FMS')
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
      <p class="text-muted fs-xs mb-0">Summary of hospital financial standing asserting the fundamental accounting equality: Total Assets = Total Liabilities + Total Equity.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['General Ledger (Journal Entries)', 'Chart of Accounts', 'Fiscal Periods']" 
          :tables="['journal_entry_lines', 'journal_entries', 'accounts', 'fiscal_periods']"
          description="Compiles posted double-entry journal lines into standard financial statements." 
      />
      <a href="{{ route('reporting.balance-sheet.export', ['as_of_date' => $asOfDate ?? date('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
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
          <span class="text-muted small fw-medium">Total Assets (Debit)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($totalAssets ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liabilities (Credit)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) ($totalLiabilities ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Adjusted Net Equity</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($totalEquity ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Surplus: ₱{{ number_format((float) ($netSurplus ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Accounting Invariance</span>
          <span class="badge {{ ($isBalanced ?? true) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} p-2 rounded-2">
            <i class="ph {{ ($isBalanced ?? true) ? 'ph-check-circle' : 'ph-warning' }} fs-5"></i>
          </span>
        </div>
        <h4 class="fw-bold mb-0 {{ ($isBalanced ?? true) ? 'text-success' : 'text-danger' }}">
          {{ ($isBalanced ?? true) ? 'BALANCED' : 'UNBALANCED' }}
        </h4>
        <span class="fs-xs text-muted">A = L + E (Variance: ₱{{ number_format((float) ($variance ?? 0), 2) }})</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('reporting.balance-sheet') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold"><i class="ph ph-calendar me-1"></i> As-Of Cutoff Date:</label>
          <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate ?? date('Y-m-d') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Comparative Analysis:</label>
          <select name="comparison" class="form-select form-select-sm">
            <option value="none" {{ ($comparison ?? '') === 'none' ? 'selected' : '' }}>Standard Single Period</option>
            <option value="prior_year" {{ ($comparison ?? '') === 'prior_year' ? 'selected' : '' }}>Compare Prior Year (1 Year Prior)</option>
            <option value="prior_quarter" {{ ($comparison ?? '') === 'prior_quarter' ? 'selected' : '' }}>Compare Prior Quarter (3 Months Prior)</option>
          </select>
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-arrows-clockwise me-1"></i> Recompute Balance Sheet</button>
          <a href="{{ route('reporting.balance-sheet') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Dual Column Balance Sheet Layout -->
  <div class="row g-4">
    <!-- Assets Column -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-header bg-success-subtle border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-success"><i class="ph ph-vault me-2"></i>ASSETS (DEBIT ACCOUNTS)</h5>
          <span class="fs-xs font-monospace text-success fw-bold">₱{{ number_format((float) ($totalAssets ?? 0), 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Account Description</th>
                  <th class="text-end">Balance (₱)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($assets ?? [] as $a)
                <tr>
                  <td><span class="badge bg-light text-dark border font-monospace">{{ $a['code'] }}</span></td>
                  <td class="fw-semibold text-dark">{{ $a['name'] }}</td>
                  <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) $a['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center py-4 text-muted">No asset ledger accounts recorded.</td>
                </tr>
                @endforelse
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="2" class="text-uppercase text-dark">TOTAL ASSETS</td>
                  <td class="text-end font-monospace text-success fs-6">₱{{ number_format((float) ($totalAssets ?? 0), 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Liabilities & Equity Column -->
    <div class="col-md-6">
      <!-- Liabilities Card -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-danger-subtle border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-danger"><i class="ph ph-bank me-2"></i>LIABILITIES (CREDIT ACCOUNTS)</h5>
          <span class="fs-xs font-monospace text-danger fw-bold">₱{{ number_format((float) ($totalLiabilities ?? 0), 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Account Description</th>
                  <th class="text-end">Balance (₱)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($liabilities ?? [] as $l)
                <tr>
                  <td><span class="badge bg-light text-dark border font-monospace">{{ $l['code'] }}</span></td>
                  <td class="fw-semibold text-dark">{{ $l['name'] }}</td>
                  <td class="text-end font-monospace text-danger fw-bold">₱{{ number_format((float) $l['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center py-3 text-muted">No liability ledger accounts recorded.</td>
                </tr>
                @endforelse
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="2" class="text-uppercase text-dark">TOTAL LIABILITIES</td>
                  <td class="text-end font-monospace text-danger">₱{{ number_format((float) ($totalLiabilities ?? 0), 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Equity Card -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-primary-subtle border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title h6 mb-0 fw-bold text-primary"><i class="ph ph-scales me-2"></i>EQUITY &amp; ACCUMULATED SURPLUS</h5>
          <span class="fs-xs font-monospace text-primary fw-bold">₱{{ number_format((float) ($totalEquity ?? 0), 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Account Description</th>
                  <th class="text-end">Balance (₱)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($equity ?? [] as $e)
                <tr>
                  <td><span class="badge bg-light text-dark border font-monospace">{{ $e['code'] }}</span></td>
                  <td class="fw-semibold text-dark">{{ $e['name'] }}</td>
                  <td class="text-end font-monospace text-primary fw-bold">₱{{ number_format((float) $e['balance'], 2) }}</td>
                </tr>
                @empty
                @endforelse
                <tr class="table-warning-subtle">
                  <td><span class="badge bg-warning text-dark font-monospace">SURPLUS</span></td>
                  <td class="fw-bold text-dark">Current Year Operating Surplus / (Deficit)</td>
                  <td class="text-end font-monospace fw-bold text-primary">₱{{ number_format((float) ($netSurplus ?? 0), 2) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-light fw-bold">
                <tr>
                  <td colspan="2" class="text-uppercase text-dark">TOTAL LIABILITIES &amp; EQUITY</td>
                  <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) ($totalLiabAndEq ?? 0), 2) }}</td>
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
