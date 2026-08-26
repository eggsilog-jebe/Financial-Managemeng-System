@extends('layouts.app')

@section('title', 'Cash Flow Forecasting - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'cash-flow-forecast')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Cash Flow Forecasting</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Cash Flow Forecasting Engine</h1>
      <p class="text-muted fs-xs mb-0">Project 30, 60, and 90-day cash positions by combining expected patient collections, HMO reimbursements, supplier payables, and payroll.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['AR Invoices', 'HMO Claims', 'AP Bills', 'Payroll Runs']" 
          description="Predicts 30/60/90-day cash liquidity curves." 
      />
      <a href="{{ route('cash.cash-flow-forecast.export', ['horizon' => $horizon_days ?? 30]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export 30-Day Schedule (CSV)
      </a>
      <a href="{{ route('cash.liquidity') }}" class="btn btn-primary btn-sm">
        <i class="ph ph-chart-line-up me-1"></i> Liquidity Ratios
      </a>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Liquid Cash</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($available_cash ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Across {{ count($bank_accounts ?? []) }} Active Bank Accounts</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Projected 30-Day Inflows</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($total_projected_inflows ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">Patient: ₱{{ number_format((float) ($patient_inflows ?? 0), 2) }} | HMO: ₱{{ number_format((float) ($hmo_inflows ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Committed 30-Day Outflows</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) ($total_committed_outflows ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">AP: ₱{{ number_format((float) ($ap_outflows ?? 0), 2) }} | Payroll: ₱{{ number_format((float) ($payroll_outflows ?? 0), 2) }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Projected Ending Position</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        @php
          $isPositive = (bccomp((string) ($net_operating_position ?? 0), '0.0000', 4) >= 0);
        @endphp
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($projected_ending_cash ?? 0), 2) }}</h4>
        <span class="fs-xs {{ $isPositive ? 'text-success' : 'text-danger' }}">
          Net Flow: {{ $isPositive ? '+' : '' }}₱{{ number_format((float) ($net_operating_position ?? 0), 2) }}
        </span>
      </div>
    </div>
  </div>

  <!-- Forecasting Horizon Filter -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('cash.cash-flow-forecast') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Forecasting Window Horizon:</label>
          <select name="horizon" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="15" {{ request('horizon') == 15 ? 'selected' : '' }}>Next 15 Days (Bi-Weekly Cash Runway)</option>
            <option value="30" {{ request('horizon', 30) == 30 ? 'selected' : '' }}>Next 30 Days (Monthly Operational Forecast)</option>
            <option value="60" {{ request('horizon') == 60 ? 'selected' : '' }}>Next 60 Days (Two-Month Horizon)</option>
            <option value="90" {{ request('horizon') == 90 ? 'selected' : '' }}>Next 90 Days (Quarterly Liquidity Outlook)</option>
          </select>
        </div>
      </form>
    </div>
  </div>

  <!-- Chronological Cash Events Schedule Grid -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-calendar-blank me-2 text-primary"></i>Chronological Inflows vs. Outflows Schedule</h6>
      <span class="badge bg-primary-subtle text-primary">{{ count($events ?? []) }} Scheduled Events</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Event Type</th>
              <th>Category</th>
              <th>Reference #</th>
              <th>Counterparty / Entity</th>
              <th>Expected Due Date</th>
              <th class="text-end">Projected Amount (₱)</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($events ?? [] as $evt)
            @php
              $isInflow = ($evt['type'] === 'INFLOW');
            @endphp
            <tr>
              <td>
                @if($isInflow)
                  <span class="badge bg-success-subtle text-success"><i class="ph ph-arrow-down-left me-1"></i> INFLOW</span>
                @else
                  <span class="badge bg-danger-subtle text-danger"><i class="ph ph-arrow-up-right me-1"></i> OUTFLOW</span>
                @endif
              </td>
              <td class="fw-semibold text-dark">{{ $evt['category'] }}</td>
              <td><span class="font-monospace text-primary fw-bold">{{ $evt['reference'] }}</span></td>
              <td><span class="text-dark">{{ $evt['counterparty'] }}</span></td>
              <td class="font-monospace fs-xs">{{ date('M d, Y', strtotime($evt['due_date'])) }}</td>
              <td class="text-end font-monospace fw-bold {{ $isInflow ? 'text-success' : 'text-danger' }}">
                {{ $isInflow ? '+' : '-' }}₱{{ number_format((float) $evt['amount'], 2) }}
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border">{{ $evt['status'] }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No scheduled cash events within the selected horizon.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ count($events ?? []) }} chronological cash forecasting line items</span>
    </div>
  </div>
</div>
@endsection
