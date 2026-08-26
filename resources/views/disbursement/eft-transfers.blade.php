@extends('layouts.app')

@section('title', 'EFT & Electronic Payouts - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'eft-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">EFT &amp; Electronic Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Electronic Funds Transfer (EFT) Hub</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['PESONet', 'InstaPay', 'Corporate Banking Portals']" 
          description="Generates bulk NACHA/CSV bank payout batches." 
      />
      <a href="{{ route('disbursement.eft-transfers.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-download-simple me-1"></i> Export NACHA / Bank Batch CSV
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Total EFT Payouts</span>
        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ $totalTransfers ?? 0 }} Transfers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">PESONet Batch Volume</span>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) ($pesonetAmount ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">InstaPay Real-Time Volume</span>
        <h4 class="fw-bold mb-0 text-info font-monospace">₱{{ number_format((float) ($instapayAmount ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Total Electronic Disbursed</span>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalAmount ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Transfers Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('disbursement.eft-transfers') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Channel:</label>
          <select name="channel" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('channel') === null || request('channel') === '' ? 'selected' : '' }}>All Electronic Channels</option>
            <option value="PESONET_EFT" {{ request('channel') === 'PESONET_EFT' ? 'selected' : '' }}>PESONet (Batch Same-Day)</option>
            <option value="INSTAPAY" {{ request('channel') === 'INSTAPAY' ? 'selected' : '' }}>InstaPay (Real-Time)</option>
            <option value="TELEGRAPHIC_TRANSFER" {{ request('channel') === 'TELEGRAPHIC_TRANSFER' ? 'selected' : '' }}>Telegraphic Transfer (TT / Wire)</option>
          </select>
        </div>
        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search voucher #, payee, ref..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref #</th>
              <th>Beneficiary / Payee</th>
              <th>Channel</th>
              <th>Disbursing Bank</th>
              <th>Date</th>
              <th class="text-end">Transfer Amount (₱)</th>
              <th>Status</th>
              <th>Release Reference</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transfers as $t)
            @php
              $amt = (float) $t->net_disbursed_amount;
              $statusBadge = match($t->status) {
                'RELEASED' => 'bg-success-subtle text-success',
                'APPROVED' => 'bg-info-subtle text-info',
                default    => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $t->voucher_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $t->payee_name }}</div>
                <div class="fs-xs text-muted">{{ $t->description ?? 'Electronic Payout' }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', $t->payment_method) }}</span>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $t->bankAccount?->bank_name ?? 'Operating Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $t->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>{{ $t->voucher_date ? $t->voucher_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $t->status }}</span>
              </td>
              <td>
                <span class="font-monospace fs-xs text-muted">{{ $t->check_or_eft_ref ?? 'PENDING BATCH' }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No electronic transfers recorded for this filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $transfers->firstItem() ?? 0 }} - {{ $transfers->lastItem() ?? 0 }} of {{ $transfers->total() }} Transfers</span>
      <div>
        {{ $transfers->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
