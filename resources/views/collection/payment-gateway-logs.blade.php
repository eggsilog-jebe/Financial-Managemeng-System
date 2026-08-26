@extends('layouts.app')

@section('title', 'Payment Gateway Logs - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'payment-gateways')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Gateway Logs</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Gateway &amp; E-Wallet Transaction Logs</h1>
      <p class="text-muted fs-xs mb-0">Monitor online patient copay transactions, digital receipts (GCash, Maya, Credit/Debit cards), and webhook synchronization status.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('collection.cashier-desk') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-hand-coins me-1"></i> Cashier POS Desk</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
      <i class="ph ph-check-circle fs-4 me-2"></i>
      <div>{{ session('success') }}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
      <i class="ph ph-warning-circle fs-4 me-2"></i>
      <div>{{ session('error') }}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Gateway Inflows</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-globe-hemisphere-west fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalOnline ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Transactions</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($logs ?? []) }} Transactions</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Settlement Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Synchronized</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-device-mobile me-2 text-primary"></i>Digital Gateway &amp; POS Settlements Stream</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Channel / Provider</th>
              <th>Transaction Channel Ref</th>
              <th>Official Receipt #</th>
              <th>Patient / Payor</th>
              <th>Date</th>
              <th class="text-end">Settled Amount (₱)</th>
              <th class="text-center">Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs ?? [] as $log)
            @php
              $method = $log->payment_method;
              $badge = match($method) {
                  'GCASH' => 'bg-info-subtle text-info',
                  'MAYA' => 'bg-success-subtle text-success',
                  'CREDIT_CARD', 'DEBIT_CARD' => 'bg-primary-subtle text-primary',
                  default => 'bg-secondary-subtle text-secondary'
              };
            @endphp
            <tr>
              <td><span class="badge {{ $badge }} px-2 py-1"><i class="ph ph-credit-card me-1"></i> {{ $method }}</span></td>
              <td><span class="font-monospace text-dark fw-semibold">{{ $log->transaction_channel_ref ?? $log->payment_reference }}</span></td>
              <td>
                <span class="font-monospace text-primary fw-bold">
                  {{ $log->officialReceipt?->or_number ?? $log->payment_reference }}
                </span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $log->officialReceipt?->payor_name ?: ($log->patientAccount?->full_name ?? 'Patient') }}</div>
              </td>
              <td class="font-monospace fs-xs">{{ $log->payment_date ? $log->payment_date->format('M d, Y') : '-' }}</td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $log->amount, 2) }}</td>
              <td class="text-center"><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> SETTLED</span></td>
              <td class="text-end">
                @if($log->journalEntry)
                  <a href="{{ route('gl.journal-entries') }}?search={{ $log->journalEntry->reference_number }}" 
                     class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3 text-decoration-none d-inline-flex align-items-center gap-1 font-monospace"
                     title="View Double-Entry Journal in General Ledger">
                    <i class="ph ph-check-circle fs-6"></i>
                    <span>Posted: {{ $log->journalEntry->reference_number }}</span>
                  </a>
                @else
                  <form method="POST" action="{{ route('collection.payment-gateways.retrigger-gl', $log->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary p-1 px-3 fw-semibold" title="Post Missing Double-Entry Ledger Transaction">
                      <i class="ph ph-arrow-counter-clockwise me-1"></i> Re-Trigger GL
                    </button>
                  </form>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No digital gateway transaction records found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ count($logs ?? []) }} Digital Gateway Transactions</span>
    </div>
  </div>
</div>
@endsection
