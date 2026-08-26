@extends('layouts.app')

@section('title', 'Statement of Cash Flows - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'cash-flow-statement')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Statement of Cash Flows</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">PAS 7 Statement of Cash Flows</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Operating Activities', 'Investing CapEx', 'Financing Reserves', 'Bank Accounts']" 
          description="PAS 7 Compliant Statement of Cash Flows." 
      />
      <a href="{{ route('reporting.cash-flow-statement.export', ['date_from' => $date_from ?? date('Y-01-01'), 'date_to' => $date_to ?? date('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Statement (CSV)
      </a>
      <button class="btn btn-primary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Statement
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Cash Flow</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($net_operating_cash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Investing Activities (CapEx)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($net_investing_cash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Financing Activities</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($net_financing_cash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Ending Liquid Cash Pool</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($closing_cash ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('reporting.cash-flow-statement') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Reporting Period From:</label>
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $date_from ?? date('Y-01-01') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Reporting Period To:</label>
          <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $date_to ?? date('Y-m-d') }}">
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-arrows-clockwise me-1"></i> Recompute Cash Flow</button>
          <a href="{{ route('reporting.cash-flow-statement') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- PAS 7 Formal Statement Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-list-numbers me-2 text-primary"></i>PAS 7 Statement of Cash Flows Breakdown</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Cash Flow Line Item &amp; Classification</th>
              <th class="text-end" style="width: 250px;">Amount (₱)</th>
            </tr>
          </thead>
          <tbody>
            <!-- 1. Operating Activities -->
            <tr class="table-light fw-bold">
              <td colspan="2"><i class="ph ph-caret-right me-1 text-primary"></i> 1. CASH FLOWS FROM OPERATING ACTIVITIES</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Cash received from Patient Copays, HMO Settlements &amp; Clinical Fees</td>
              <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) ($operating_receipts ?? 0), 2) }}</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Cash paid to Medical Suppliers, Pharmaceuticals &amp; Consumables</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($supplier_disbursements ?? 0), 2) }}</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Cash paid to Healthcare Personnel, Doctors &amp; Hospital Staff Payroll</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($payroll_disbursements ?? 0), 2) }}</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Cash paid for Direct Clinical Operations &amp; Facility Utilities</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($direct_opex_cash ?? 0), 2) }}</td>
            </tr>
            <tr class="fw-bold bg-success-subtle">
              <td class="ps-4 text-success text-uppercase">Net Cash Provided by (Used in) Operating Activities</td>
              <td class="text-end font-monospace text-success fs-6">₱{{ number_format((float) ($net_operating_cash ?? 0), 2) }}</td>
            </tr>

            <!-- 2. Investing Activities -->
            <tr class="table-light fw-bold">
              <td colspan="2"><i class="ph ph-caret-right me-1 text-primary"></i> 2. CASH FLOWS FROM INVESTING ACTIVITIES</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Acquisition of Medical Diagnostic Equipment &amp; Hospital Infrastructure (CapEx)</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($capex_outflows ?? 0), 2) }}</td>
            </tr>
            <tr class="fw-bold bg-light">
              <td class="ps-4 text-dark text-uppercase">Net Cash Provided by (Used in) Investing Activities</td>
              <td class="text-end font-monospace text-dark">₱{{ number_format((float) ($net_investing_cash ?? 0), 2) }}</td>
            </tr>

            <!-- 3. Financing Activities -->
            <tr class="table-light fw-bold">
              <td colspan="2"><i class="ph ph-caret-right me-1 text-primary"></i> 3. CASH FLOWS FROM FINANCING ACTIVITIES</td>
            </tr>
            <tr>
              <td class="ps-4 text-dark">Proceeds from Institutional Capital Reserves &amp; Credit Facilities</td>
              <td class="text-end font-monospace text-dark">₱{{ number_format((float) ($net_financing_cash ?? 0), 2) }}</td>
            </tr>
            <tr class="fw-bold bg-light">
              <td class="ps-4 text-dark text-uppercase">Net Cash Provided by (Used in) Financing Activities</td>
              <td class="text-end font-monospace text-dark">₱{{ number_format((float) ($net_financing_cash ?? 0), 2) }}</td>
            </tr>

            <!-- Summary Net Cash Movement -->
            <tr class="table-primary fw-bold">
              <td class="text-uppercase text-primary fs-6">NET INCREASE / (DECREASE) IN CASH &amp; CASH EQUIVALENTS</td>
              <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) ($net_cash_flow ?? 0), 2) }}</td>
            </tr>
            <tr>
              <td class="ps-4 text-muted">Cash and Cash Equivalents at Beginning of Period</td>
              <td class="text-end font-monospace text-muted">₱{{ number_format((float) ($opening_cash ?? 0), 2) }}</td>
            </tr>
            <tr class="table-success fw-bold">
              <td class="text-uppercase text-success fs-6">CASH AND CASH EQUIVALENTS AT END OF PERIOD (RECONCILED)</td>
              <td class="text-end font-monospace text-success fs-6">₱{{ number_format((float) ($closing_cash ?? 0), 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
