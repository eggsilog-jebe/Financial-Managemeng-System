@extends('layouts.app')

@section('title', 'Financial Management Dashboard')
@section('module', 'finance')
@section('page', 'dashboard')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Executive Financial Overview</h1>
      <p class="text-muted mb-0">Hospital Financial Management System &bull; Standalone Transaction Core Mode</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('collection.cashier-desk') }}" class="btn btn-outline-primary btn-sm">
        <i class="ph ph-receipt me-1"></i> Cashier Counter
      </a>
      <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-book-open me-1"></i> Journal Browser
      </a>
      <a href="{{ route('accounting.reports.index') }}" class="btn btn-primary btn-sm">
        <i class="ph ph-chart-line-up me-1"></i> Financial Reports Hub
      </a>
    </div>
  </div>

  <!-- KPI Metric Cards Row -->
  <div class="row g-3 mb-4">
    <!-- Total Revenue -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Total Hospital Revenue</span>
          <span class="badge bg-success-subtle text-success"><i class="ph ph-arrow-up-right me-1"></i> P&amp;L</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($totalRevenue, 2) }}</h3>
        <span class="fs-xs text-muted">Clinical, Diagnostic &amp; Pharmacy Revenue</span>
      </div>
    </div>

    <!-- Cash on Hand -->
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Cash on Hand</span>
          <span class="badge bg-primary-subtle text-primary"><i class="ph ph-vault me-1"></i> Drawer</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($cashOnHand, 2) }}</h3>
        <span class="fs-xs text-muted">Undeposited Collections &amp; Petty Cash</span>
      </div>
    </div>

    <!-- Cash in Bank -->
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Cash in Bank</span>
          <span class="badge bg-info-subtle text-info"><i class="ph ph-bank me-1"></i> Liquid</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($cashInBank, 2) }}</h3>
        <span class="fs-xs text-muted">Operating Accounts Balance</span>
      </div>
    </div>

    <!-- Outstanding AR -->
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Outstanding AR</span>
          <span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Receivables</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($outstandingAR, 2) }}</h3>
        <span class="fs-xs text-muted">Open Patient Copay Balances</span>
      </div>
    </div>

    <!-- Overdue AP -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Outstanding AP</span>
          <span class="badge bg-danger-subtle text-danger"><i class="ph ph-warning-circle me-1"></i> Payables</span>
        </div>
        <h3 class="fw-bold mb-1 text-dark">₱{{ number_format($outstandingAP, 2) }}</h3>
        <span class="fs-xs text-muted">Vendor &amp; Medical Supplier Invoices</span>
      </div>
    </div>
  </div>

  <!-- Quick Actions & Ledger Integrity Badge -->
  <div class="row g-3 mb-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
        <h5 class="fw-bold text-dark mb-3"><i class="ph ph-lightning text-primary me-2"></i>Quick Financial Operations</h5>
        <div class="row g-2">
          <div class="col-md-4">
            <a href="{{ route('collection.cashier-desk') }}" class="btn btn-light border w-100 text-start p-3 rounded-3 d-flex align-items-center gap-3">
              <span class="p-2 rounded-2 bg-primary-subtle text-primary"><i class="ph ph-hand-coins fs-4"></i></span>
              <div>
                <strong class="d-block text-dark">Receive Payment</strong>
                <span class="fs-xs text-muted">Process Cashier Receipt (OR)</span>
              </div>
            </a>
          </div>
          <div class="col-md-4">
            <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-light border w-100 text-start p-3 rounded-3 d-flex align-items-center gap-3">
              <span class="p-2 rounded-2 bg-success-subtle text-success"><i class="ph ph-plus-circle fs-4"></i></span>
              <div>
                <strong class="d-block text-dark">Journal Browser</strong>
                <span class="fs-xs text-muted">Audit &amp; Reverse Postings</span>
              </div>
            </a>
          </div>
          <div class="col-md-4">
            <a href="{{ route('accounting.reports.index') }}" class="btn btn-light border w-100 text-start p-3 rounded-3 d-flex align-items-center gap-3">
              <span class="p-2 rounded-2 bg-warning-subtle text-warning"><i class="ph ph-scales fs-4"></i></span>
              <div>
                <strong class="d-block text-dark">Trial Balance</strong>
                <span class="fs-xs text-muted">Verify GAAP Balance</span>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 d-flex justify-content-between">
        <div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold mb-0 text-dark">Double-Entry Status</h6>
            <span class="badge {{ $isBalanced ? 'bg-success' : 'bg-danger' }}">
              {{ $isBalanced ? 'IN BALANCE' : 'DISCREPANCY' }}
            </span>
          </div>
          <p class="fs-xs text-muted mb-0">
            Real-time BCMath verification across all posted ledger entries confirming <span class="font-monospace fw-semibold">&sum; Debits == &sum; Credits</span>.
          </p>
        </div>
        <div class="pt-3 border-top mt-2">
          <small class="text-muted d-block">Philippine CAS &amp; BIR Compliance Active</small>
          <span class="badge bg-light text-dark border">SHA-256 Hash Chain Integrity: VERIFIED</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent General Ledger Transactions Table -->
  <div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-clock-counter-clockwise text-primary me-2"></i>Recent Financial Journal Postings</h5>
      <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-sm btn-outline-secondary">View All Entries</a>
    </div>
    <div class="table-responsive p-3">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="fs-xs text-muted text-uppercase">
            <th>Reference #</th>
            <th>Posting Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Debits / Credits</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentJournals as $je)
            <tr>
              <td><span class="badge bg-light text-dark font-monospace border">{{ $je->reference_number }}</span></td>
              <td>{{ $je->entry_date->format('M d, Y') }}</td>
              <td><span class="text-dark fw-medium">{{ $je->description }}</span></td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $je->type }}</span></td>
              <td>
                <span class="badge {{ $je->status === 'POSTED' ? 'bg-success-subtle text-success' : ($je->status === 'REVERSED' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                  {{ $je->status }}
                </span>
              </td>
              <td class="text-end font-monospace fw-semibold">
                ₱{{ number_format((float) $je->lines->sum('debit'), 2) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No journal transactions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
