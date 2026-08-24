@extends('layouts.app')

@section('title', 'Financial Reports Hub')
@section('module', 'financial-reporting')
@section('page', 'reports-hub')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Financial Reports &amp; Statements Hub</h1>
      <p class="text-muted mb-0">Real-Time Trial Balance &bull; Profit &amp; Loss &bull; Balance Sheet &bull; BIR Schedules</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('accounting.export.trial-balance-csv') }}" class="btn btn-outline-success btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Trial Balance CSV
      </a>
      <a href="{{ route('accounting.export.general-ledger-csv') }}" class="btn btn-outline-info btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export GL Book (CAS Audit)
      </a>
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Report</button>
      <a href="{{ route('accounting.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $tab === 'trial-balance' ? 'active' : '' }} fw-semibold" 
         href="{{ route('accounting.reports.index', ['tab' => 'trial-balance']) }}">
        <i class="ph ph-scales me-1"></i> Trial Balance
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $tab === 'pnl' ? 'active' : '' }} fw-semibold" 
         href="{{ route('accounting.reports.index', ['tab' => 'pnl']) }}">
        <i class="ph ph-chart-line-up me-1"></i> Income Statement (P&amp;L)
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $tab === 'balance-sheet' ? 'active' : '' }} fw-semibold" 
         href="{{ route('accounting.reports.index', ['tab' => 'balance-sheet']) }}">
        <i class="ph ph-shield-check me-1"></i> Balance Sheet
      </a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $tab === 'bir-schedules' ? 'active' : '' }} fw-semibold" 
         href="{{ route('accounting.reports.index', ['tab' => 'bir-schedules']) }}">
        <i class="ph ph-file-text me-1"></i> BIR Tax Returns
      </a>
    </li>
  </ul>

  <!-- TAB 1: TRIAL BALANCE -->
  @if($tab === 'trial-balance')
    <div class="card border-0 shadow-sm rounded-3 bg-white">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="fw-bold mb-0 text-dark">General Ledger Trial Balance</h5>
          <small class="text-muted">Real-time balances across all Chart of Accounts</small>
        </div>
        <div>
          <span class="badge {{ $trialBalance['is_balanced'] ? 'bg-success fs-6 py-2 px-3' : 'bg-danger fs-6 py-2 px-3' }}">
            <i class="ph {{ $trialBalance['is_balanced'] ? 'ph-check-circle' : 'ph-warning' }} me-1"></i>
            {{ $trialBalance['is_balanced'] ? 'DOUBLE-ENTRY BALANCED' : 'OUT OF BALANCE' }}
          </span>
        </div>
      </div>

      <div class="table-responsive p-3">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr class="fs-xs text-muted text-uppercase">
              <th style="width: 120px;">Code</th>
              <th>Account Title</th>
              <th>Classification</th>
              <th>Normal Balance</th>
              <th class="text-end" style="width: 180px;">Debit (DR)</th>
              <th class="text-end" style="width: 180px;">Credit (CR)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($trialBalance['accounts'] as $acc)
              <tr>
                <td><span class="badge bg-light text-dark font-monospace border">{{ $acc['code'] }}</span></td>
                <td><strong class="text-dark">{{ $acc['name'] }}</strong></td>
                <td><span class="badge bg-secondary-subtle text-secondary">{{ $acc['category'] }}</span></td>
                <td><small class="text-muted font-monospace">{{ $acc['normal_balance'] }}</small></td>
                <td class="text-end font-monospace {{ (float) $acc['debit'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                  {{ (float) $acc['debit'] > 0 ? '₱' . number_format((float) $acc['debit'], 2) : '-' }}
                </td>
                <td class="text-end font-monospace {{ (float) $acc['credit'] > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                  {{ (float) $acc['credit'] > 0 ? '₱' . number_format((float) $acc['credit'], 2) : '-' }}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot class="table-dark">
            <tr class="fw-bold font-monospace fs-6">
              <td colspan="4" class="text-uppercase text-end">Grand Total Balances:</td>
              <td class="text-end text-success">₱{{ number_format((float) $trialBalance['total_debit'], 2) }}</td>
              <td class="text-end text-success">₱{{ number_format((float) $trialBalance['total_credit'], 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  @endif

  <!-- TAB 2: INCOME STATEMENT (P&L) -->
  @if($tab === 'pnl')
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold mb-0 text-success"><i class="ph ph-trend-up me-2"></i>Operating Revenues</h5>
          </div>
          <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr class="fs-xs text-muted text-uppercase">
                  <th>Code</th>
                  <th>Revenue Account</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pnl['revenues'] as $rev)
                  <tr>
                    <td><span class="badge bg-light text-dark font-monospace border">{{ $rev['code'] }}</span></td>
                    <td>{{ $rev['name'] }}</td>
                    <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $rev['balance'], 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-center py-3 text-muted">No revenues recorded.</td></tr>
                @endforelse
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold font-monospace">
                  <td colspan="2">Total Gross Revenues:</td>
                  <td class="text-end text-success fs-5">₱{{ number_format((float) $pnl['total_revenue'], 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
          <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h5 class="fw-bold mb-0 text-danger"><i class="ph ph-trend-down me-2"></i>Operating &amp; Direct Expenses</h5>
          </div>
          <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr class="fs-xs text-muted text-uppercase">
                  <th>Code</th>
                  <th>Expense Account</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pnl['expenses'] as $exp)
                  <tr>
                    <td><span class="badge bg-light text-dark font-monospace border">{{ $exp['code'] }}</span></td>
                    <td>{{ $exp['name'] }}</td>
                    <td class="text-end font-monospace fw-bold text-danger">₱{{ number_format((float) $exp['balance'], 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-center py-3 text-muted">No expenses recorded.</td></tr>
                @endforelse
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold font-monospace">
                  <td colspan="2">Total Operating Expenses:</td>
                  <td class="text-end text-danger fs-5">₱{{ number_format((float) $pnl['total_expense'], 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-4 bg-primary text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4 class="fw-bold mb-1">Net Hospital Margin / Net Income</h4>
              <p class="mb-0 opacity-75">Gross Clinical Revenue less Operating &amp; Direct Medical Expenses</p>
            </div>
            <h2 class="fw-bold mb-0 font-monospace">₱{{ number_format((float) $pnl['net_income'], 2) }}</h2>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- TAB 3: BALANCE SHEET -->
  @if($tab === 'balance-sheet')
    <div class="card border-0 shadow-sm rounded-3 bg-white">
      <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="fw-bold mb-0 text-dark">Statement of Financial Position (Balance Sheet)</h5>
          <small class="text-muted">Assets = Liabilities + Owner's Equity + Current Net Income</small>
        </div>
        <span class="badge {{ $balanceSheet['is_balanced'] ? 'bg-success fs-6 py-2 px-3' : 'bg-danger fs-6 py-2 px-3' }}">
          {{ $balanceSheet['is_balanced'] ? 'A = L + E (BALANCED)' : 'EQUATION DISCREPANCY' }}
        </span>
      </div>

      <div class="card-body p-4">
        <div class="row g-4">
          <!-- Assets Column -->
          <div class="col-md-6 border-end">
            <h6 class="fw-bold text-primary mb-3 text-uppercase"><i class="ph ph-bank me-2"></i>Assets</h6>
            <ul class="list-group list-group-flush mb-3">
              @foreach($balanceSheet['assets'] as $asset)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                  <div>
                    <span class="badge bg-light text-dark font-monospace me-2">{{ $asset['code'] }}</span>
                    <span>{{ $asset['name'] }}</span>
                  </div>
                  <strong class="font-monospace text-dark">₱{{ number_format((float) $asset['balance'], 2) }}</strong>
                </li>
              @endforeach
            </ul>
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center fw-bold">
              <span>Total Assets:</span>
              <span class="font-monospace text-primary fs-5">₱{{ number_format((float) $balanceSheet['total_assets'], 2) }}</span>
            </div>
          </div>

          <!-- Liabilities & Equity Column -->
          <div class="col-md-6">
            <h6 class="fw-bold text-danger mb-3 text-uppercase"><i class="ph ph-receipt me-2"></i>Liabilities</h6>
            <ul class="list-group list-group-flush mb-3">
              @foreach($balanceSheet['liabilities'] as $liab)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                  <div>
                    <span class="badge bg-light text-dark font-monospace me-2">{{ $liab['code'] }}</span>
                    <span>{{ $liab['name'] }}</span>
                  </div>
                  <strong class="font-monospace text-dark">₱{{ number_format((float) $liab['balance'], 2) }}</strong>
                </li>
              @endforeach
            </ul>
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center fw-bold mb-4">
              <span>Total Liabilities:</span>
              <span class="font-monospace text-danger">₱{{ number_format((float) $balanceSheet['total_liabilities'], 2) }}</span>
            </div>

            <h6 class="fw-bold text-info mb-3 text-uppercase"><i class="ph ph-buildings me-2"></i>Equity &amp; Earnings</h6>
            <ul class="list-group list-group-flush mb-3">
              @foreach($balanceSheet['equity'] as $eq)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                  <div>
                    <span class="badge bg-light text-dark font-monospace me-2">{{ $eq['code'] }}</span>
                    <span>{{ $eq['name'] }}</span>
                  </div>
                  <strong class="font-monospace text-dark">₱{{ number_format((float) $eq['balance'], 2) }}</strong>
                </li>
              @endforeach
              <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                <div>
                  <span class="badge bg-light text-dark font-monospace me-2">NET-INC</span>
                  <span>Current Period Net Income (Retained)</span>
                </div>
                <strong class="font-monospace text-success">₱{{ number_format((float) $balanceSheet['current_period_net_income'], 2) }}</strong>
              </li>
            </ul>
            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center fw-bold">
              <span>Total Liabilities &amp; Equity:</span>
              <span class="font-monospace text-primary fs-5">₱{{ number_format((float) $balanceSheet['total_liabilities_and_equity'], 2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- TAB 4: BIR TAX RETURNS -->
  @if($tab === 'bir-schedules')
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 h-100">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0"><i class="ph ph-file-text text-primary me-2"></i>BIR Form 1601-EQ</h5>
            <span class="badge bg-primary-subtle text-primary">Expanded Withholding Tax</span>
          </div>
          <p class="text-muted small">Quarterly creditable income taxes withheld from medical suppliers and doctor fees.</p>
          <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Total Tax Base:</span>
              <strong class="font-monospace">₱{{ number_format((float) $bir1601eq['total_tax_base'], 2) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>Total Form 2307 Certificates:</span>
              <strong class="font-monospace">{{ $bir1601eq['total_forms'] }} Issued</strong>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fs-6 fw-bold">
              <span>Total Creditable Tax Withheld:</span>
              <span class="text-primary font-monospace">₱{{ number_format((float) $bir1601eq['total_withheld'], 2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4 h-100">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-dark mb-0"><i class="ph ph-percent text-success me-2"></i>BIR Form 2550M / 2550Q</h5>
            <span class="badge bg-success-subtle text-success">Value-Added Tax (VAT)</span>
          </div>
          <p class="text-muted small">Monthly and quarterly VAT declaration summaries on hospital gross receipts.</p>
          <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span>Total Official Receipts Count:</span>
              <strong class="font-monospace">{{ $birVat['total_receipts_count'] }} Receipts</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>Total Gross Cashier Collections:</span>
              <strong class="font-monospace">₱{{ number_format((float) $birVat['total_collections'], 2) }}</strong>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fs-6 fw-bold">
              <span>Output VAT Relief / Exempt Status:</span>
              <span class="text-success font-monospace">RA 9994 / RA 10754 Applied</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
