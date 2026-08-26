@extends('layouts.app')

@section('title', 'Executive Reports Dossier - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'executive-reports')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Executive Dossier</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Executive Board Report &amp; Financial Dossier</h1>
      <p class="text-muted fs-xs mb-0">Consolidated board-ready report package combining Balance Sheet, P&amp;L, Cash Flows, Key Metrics, and CFO Attestation Sign-offs.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Consolidated Ledgers', 'Executive Attestation']" 
          description="Consolidated financial & operational dossier with verified attestation blocks." 
      />
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Complete Dossier
      </button>
      <a href="{{ route('reporting.balance-sheet.export') }}" class="btn btn-primary btn-sm">
        <i class="ph ph-download-simple me-1"></i> Export Balance Sheet (CSV)
      </a>
    </div>
  </div>

  <!-- Cutoff Date Selector -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('reporting.executive-reports') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Dossier Statement Cutoff Date:</label>
          <input type="date" name="cutoff_date" class="form-control form-control-sm" value="{{ $as_of_date ?? date('Y-m-d') }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-8 d-flex justify-content-end align-items-end">
          <span class="fs-xs text-muted font-monospace">Generated on {{ $generated_at ?? date('Y-m-d H:i:s') }} PST</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Branded Printable Dossier Container -->
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 mb-5 bg-white">
    <!-- Header -->
    <div class="border-bottom pb-4 mb-4 text-center">
      <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
        <i class="ph ph-hospital fs-2 text-primary"></i>
        <h2 class="h4 fw-bold text-dark mb-0">{{ $hospital_name ?? 'St. Jude General Hospital & Medical Center' }}</h2>
      </div>
      <div class="fs-xs text-muted">
        <span>TIN: {{ $hospital_tin ?? '004-982-114-000-VAT' }}</span> &bull; 
        <span>Accredited Healthcare Provider &bull; ISO 9001:2015</span>
      </div>
      <div class="badge bg-primary-subtle text-primary px-3 py-2 mt-2 fs-6 fw-bold">
        {{ $dossier_title ?? 'Executive Financial Dossier' }} &bull; FY {{ $fiscal_year ?? date('Y') }}
      </div>
      <div class="fs-xs text-muted mt-1 font-monospace">Period Covered: {{ $period_covered ?? date('Y-m-d') }}</div>
    </div>

    <!-- Section 1: Executive KPI Scorecard -->
    <div class="mb-5">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="ph ph-chart-polar me-2 text-primary"></i>1. Executive Financial Scorecard</h5>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="border rounded-3 p-3 text-center bg-light-subtle">
            <span class="text-muted fs-xs text-uppercase fw-semibold d-block">Operating Margin</span>
            <h4 class="fw-bold text-success mb-0">{{ number_format((float) ($kpis['operating_margin'] ?? 0), 1) }}%</h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 text-center bg-light-subtle">
            <span class="text-muted fs-xs text-uppercase fw-semibold d-block">Days Sales Outstanding</span>
            <h4 class="fw-bold text-primary mb-0">{{ number_format((float) ($kpis['dso'] ?? 0), 1) }} Days</h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 text-center bg-light-subtle">
            <span class="text-muted fs-xs text-uppercase fw-semibold d-block">Days Cash on Hand</span>
            <h4 class="fw-bold text-info mb-0">{{ number_format((float) ($kpis['days_cash_on_hand'] ?? 0), 1) }} Days</h4>
          </div>
        </div>
        <div class="col-md-3">
          <div class="border rounded-3 p-3 text-center bg-light-subtle">
            <span class="text-muted fs-xs text-uppercase fw-semibold d-block">Current Working Ratio</span>
            <h4 class="fw-bold text-dark mb-0">{{ number_format((float) ($kpis['current_ratio'] ?? 0), 2) }}x</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Statement of Financial Position (Balance Sheet Summary) -->
    <div class="mb-5">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="ph ph-scales me-2 text-primary"></i>2. Condensed Statement of Financial Position</h5>
      <table class="table table-bordered table-sm align-middle fs-xs">
        <thead class="table-light">
          <tr>
            <th>Balance Sheet Classification</th>
            <th class="text-end" style="width: 250px;">Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-bold text-success">TOTAL HOSPITAL ASSETS (Cash, AR, Equipment)</td>
            <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) ($balance_sheet['total_assets'] ?? 0), 2) }}</td>
          </tr>
          <tr>
            <td class="fw-bold text-danger">TOTAL LIABILITIES (Accounts Payable, Accruals)</td>
            <td class="text-end font-monospace text-danger fw-bold">₱{{ number_format((float) ($balance_sheet['total_liabilities'] ?? 0), 2) }}</td>
          </tr>
          <tr>
            <td class="fw-bold text-primary">TOTAL NET EQUITY (Capital Reserves + Current Surplus)</td>
            <td class="text-end font-monospace text-primary fw-bold">₱{{ number_format((float) ($balance_sheet['total_equity'] ?? 0), 2) }}</td>
          </tr>
          <tr class="table-light fw-bold">
            <td class="text-uppercase text-dark">TOTAL LIABILITIES &amp; NET EQUITY</td>
            <td class="text-end font-monospace text-dark fs-6">₱{{ number_format((float) ($balance_sheet['total_liab_and_equity'] ?? 0), 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Section 3: Statement of Comprehensive Income (P&L Summary) -->
    <div class="mb-5">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="ph ph-receipt me-2 text-primary"></i>3. Condensed Statement of Comprehensive Income</h5>
      <table class="table table-bordered table-sm align-middle fs-xs">
        <thead class="table-light">
          <tr>
            <th>Revenue &amp; Expense Summary</th>
            <th class="text-end" style="width: 250px;">Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Gross Operating Revenues (Inpatient, Outpatient, Diagnostics, Pharmacy)</td>
            <td class="text-end font-monospace text-success">₱{{ number_format((float) ($profit_and_loss['gross_revenue'] ?? 0), 2) }}</td>
          </tr>
          <tr>
            <td>Less: Statutory &amp; Institutional Discounts (5010)</td>
            <td class="text-end font-monospace text-warning">-₱{{ number_format((float) ($profit_and_loss['sales_discounts'] ?? 0), 2) }}</td>
          </tr>
          <tr class="table-light fw-semibold">
            <td>Net Operating Revenues</td>
            <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) ($profit_and_loss['net_revenue'] ?? 0), 2) }}</td>
          </tr>
          <tr>
            <td>Less: Operating Expenses (Supplies, Salaries, Facilities, Overhead)</td>
            <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($profit_and_loss['total_expenses'] ?? 0), 2) }}</td>
          </tr>
          <tr class="table-primary fw-bold">
            <td class="text-uppercase text-primary fs-6">NET OPERATING SURPLUS / (DEFICIT)</td>
            <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) ($profit_and_loss['net_income'] ?? 0), 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Section 4: Sign-off & Signature Blocks -->
    <div class="mt-5 pt-4 border-top">
      <h6 class="fw-bold text-dark mb-4 text-center text-uppercase fs-xs">Executive Financial Statement Verification &amp; Attestation</h6>
      <div class="row g-4 text-center">
        @foreach($signatories ?? [] as $sig)
        <div class="col-md-4">
          <div class="pt-4 border-top border-dark mx-3">
            <div class="fw-bold text-dark">{{ $sig['name'] }}</div>
            <div class="fs-xs text-muted">{{ $sig['title'] }}</div>
            <span class="badge bg-success-subtle text-success fs-xs mt-1"><i class="ph ph-check me-1"></i> Verified &amp; Certified</span>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
