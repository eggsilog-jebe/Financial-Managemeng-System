@extends('layouts.app')

@section('title', 'Statement of Changes in Equity - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'equity')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Statement of Changes in Equity</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Changes in Equity (PFRS / IAS 1)</h1>
      <p class="text-muted fs-xs mb-0">Reconciliation of opening to closing equity across Share Capital, Contributed Surplus, and Current Operating Earnings.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['General Ledger', 'Profit & Loss (P&L)', 'Balance Sheet']" 
          :tables="['journal_entry_lines', 'journal_entries', 'accounts', 'fiscal_periods']"
          description="Compiles PFRS-compliant equity movements reconciling P&L net surplus to Balance Sheet." 
      />
      <a href="{{ route('reporting.equity.export', ['date_from' => $date_from, 'date_to' => $date_to]) }}" class="btn btn-outline-secondary btn-sm">
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
          <span class="text-muted small fw-medium">Opening Total Equity</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-clock-counter-clockwise fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($opening_equity ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">As of {{ $date_from }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Period Surplus / (Loss)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-chart-line-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 {{ (float) ($current_period_surplus ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
          ₱{{ number_format((float) ($current_period_surplus ?? 0), 2) }}
        </h4>
        <span class="fs-xs text-muted">Flows from P&amp;L</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Capital Movements</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-arrows-left-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($net_equity_movements ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Additions: ₱{{ number_format((float) ($capital_additions ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Closing Total Equity</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($total_closing_equity ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Reconciled to Balance Sheet</span>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('reporting.equity') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fs-xs fw-semibold text-muted">Period Start Date</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $date_from }}">
        </div>
        <div class="col-md-4">
          <label class="form-label fs-xs fw-semibold text-muted">Period Cutoff Date</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $date_to }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="ph ph-funnel me-1"></i> Update Statement
          </button>
          <a href="{{ route('reporting.equity') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph ph-arrow-counter-clockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Statement Table Card -->
  <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
      <div>
        <h5 class="fw-bold mb-0 text-dark">Statement of Changes in Equity Breakdown</h5>
        <span class="fs-xs text-muted">Reporting Period: <strong>{{ $date_from }}</strong> to <strong>{{ $date_to }}</strong> (PFRS / IAS 1)</span>
      </div>
      <span class="badge bg-success-subtle text-success border font-monospace">PFRS COMPLIANT</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light fs-xs text-uppercase text-muted">
            <tr>
              <th style="width: 120px;">Account Code</th>
              <th>Equity Component / Fund</th>
              <th class="text-end">Opening Balance (₱)</th>
              <th class="text-end">Capital Additions (₱)</th>
              <th class="text-end">Drawdowns / Reductions (₱)</th>
              <th class="text-end">Closing Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts ?? [] as $acc)
            <tr>
              <td><span class="font-monospace text-primary fw-bold">{{ $acc['code'] }}</span></td>
              <td><span class="fw-semibold text-dark">{{ $acc['name'] }}</span></td>
              <td class="text-end font-monospace text-muted">₱{{ number_format((float) $acc['opening_balance'], 2) }}</td>
              <td class="text-end font-monospace text-success">+₱{{ number_format((float) $acc['additions'], 2) }}</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) $acc['deductions'], 2) }}</td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format((float) $acc['closing_balance'], 2) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No equity accounts found.</td>
            </tr>
            @endforelse

            <!-- Net Operating Surplus Line -->
            <tr class="bg-light-subtle">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace">P&amp;L-SURPLUS</span></td>
              <td>
                <span class="fw-bold text-dark">Current Period Net Operating Surplus / (Deficit)</span>
                <span class="d-block fs-xs text-muted">Accumulated clinical and operating earnings for the period</span>
              </td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td class="text-end font-monospace text-success">+₱{{ number_format((float) ($current_period_surplus ?? 0), 2) }}</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) ($current_period_surplus ?? 0), 2) }}</td>
            </tr>
          </tbody>
          <tfoot class="table-light">
            <tr class="fw-bold">
              <td colspan="2" class="text-uppercase text-dark">Total Ending Equity (PFRS Reconciled)</td>
              <td class="text-end font-monospace text-muted">₱{{ number_format((float) ($opening_equity ?? 0), 2) }}</td>
              <td class="text-end font-monospace text-success">+₱{{ number_format((float) bcadd((string) $capital_additions, (string) $current_period_surplus, 4), 2) }}</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) $distributions, 2) }}</td>
              <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) ($total_closing_equity ?? 0), 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center">
      <span class="fs-xs text-muted"><i class="ph ph-shield-check me-1 text-success"></i> Reconciles exactly with Balance Sheet Statement Equity total.</span>
      <div class="d-flex gap-2">
        <a href="{{ route('reporting.balance-sheet') }}" class="btn btn-outline-primary btn-sm"><i class="ph ph-shield-check me-1"></i> View Balance Sheet</a>
        <a href="{{ route('reporting.profit-loss') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-chart-line-up me-1"></i> View P&amp;L Statement</a>
      </div>
    </div>
  </div>
</div>
@endsection
