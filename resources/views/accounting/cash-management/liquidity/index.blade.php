@extends('layouts.app')

@section('title', 'Liquidity Management & Ratios - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'liquidity')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Liquidity Management</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Treasury Liquidity &amp; Cash Health</h1>
      <p class="text-muted fs-xs mb-0">Evaluate hospital solvency, Days Cash on Hand (runway), quick ratio, and required minimum operating reserve thresholds.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Bank Accounts', 'AP Liabilities']" 
          description="Monitors Days Cash on Hand (DCOH) and reserve ratios." 
      />
      <a href="{{ route('cash.liquidity.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Liquidity Report (CSV)
      </a>
      <button class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Treasury Report
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Liquid Reserves</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($total_cash ?? 0), 2) }}</h4>
        <span class="fs-xs text-muted">{{ $active_accounts_count ?? 0 }} Active Depository Accounts</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Cash on Hand (DCOH)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hourglass-high fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">{{ number_format((float) ($days_cash_on_hand ?? 0), 1) }} Days</h4>
        <span class="fs-xs text-muted">Daily Burn: ₱{{ number_format((float) ($daily_burn_rate ?? 0), 2) }}/day</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Treasury Health Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">{{ $liquidity_status['rating'] ?? 'ADEQUATE' }}</h4>
        <span class="fs-xs text-muted">{{ $liquidity_status['desc'] ?? 'Healthy runway' }}</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Safety Floor Violations</span>
          <span class="badge {{ ($below_minimum_count ?? 0) > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} p-2 rounded-2">
            <i class="ph ph-warning fs-5"></i>
          </span>
        </div>
        <h4 class="fw-bold mb-0 {{ ($below_minimum_count ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
          {{ $below_minimum_count ?? 0 }} Accounts
        </h4>
        <span class="fs-xs text-muted">Below Minimum Operating Reserve</span>
      </div>
    </div>
  </div>

  <!-- Institutional Distribution & Concentration Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-chart-pie-slice me-2 text-primary"></i>Institutional Depository Concentration &amp; Reserve Floor Monitors</h6>
      <span class="badge bg-primary-subtle text-primary">{{ count($concentration ?? []) }} Depository Institutions</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bank &amp; Account Name</th>
              <th>Account Number</th>
              <th>GL Account Code</th>
              <th class="text-end">Current Balance (₱)</th>
              <th class="text-end">Minimum Safety Floor (₱)</th>
              <th class="text-center" style="width: 180px;">Concentration %</th>
              <th class="text-center">Safety Status</th>
              <th class="text-center">Account Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($concentration ?? [] as $c)
            @php
              $isBelow = $c['is_below_min'];
              $pct = $c['percentage'];
            @endphp
            <tr class="{{ $isBelow ? 'table-danger-subtle' : '' }}">
              <td>
                <strong class="d-block text-dark">{{ $c['name'] }}</strong>
                <span class="fs-xs text-muted">{{ $c['bank_name'] }}</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">{{ $c['account_number'] }}</span></td>
              <td><span class="badge bg-light text-dark border font-monospace">{{ $c['gl_code'] }}</span></td>
              <td class="text-end font-monospace fw-bold {{ $isBelow ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format((float) $c['balance'], 2) }}
              </td>
              <td class="text-end font-monospace fs-xs text-muted">
                ₱{{ number_format((float) $c['minimum_balance'], 2) }}
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar {{ $pct > 50 ? 'bg-warning' : 'bg-primary' }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <span class="font-monospace fs-xs fw-semibold">{{ number_format((float) $pct, 1) }}%</span>
                </div>
              </td>
              <td class="text-center">
                @if($isBelow)
                  <span class="badge bg-danger-subtle text-danger"><i class="ph ph-warning-circle me-1"></i> BELOW FLOOR</span>
                @else
                  <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> OPTIMAL</span>
                @endif
              </td>
              <td class="text-center">
                @if($c['is_active'])
                  <span class="badge bg-success-subtle text-success">Active</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary">{{ $c['status'] }}</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No depository accounts found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ count($concentration ?? []) }} institutional bank accounts</span>
    </div>
  </div>
</div>
@endsection
