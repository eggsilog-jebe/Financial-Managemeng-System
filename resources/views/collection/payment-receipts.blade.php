@extends('layouts.app')

@section('title', 'Payment Receipts - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'payment-receipts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Receipts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Receipts &amp; Official Receipts (OR) Hub</h1>
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
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Collections</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalCollected ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cash Collections</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($cashCollected ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital / E-Wallet</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($digitalCollected ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Count</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $payments->total() ?? count($payments) }} Records</h4>
      </div>
    </div>
  </div>

  <!-- Filter & Search Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('collection.receipts') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search OR #, patient, payor...">
        </div>
        <div class="col-md-2">
          <select name="method" class="form-select form-select-sm">
            <option value="">All Payment Methods</option>
            <option value="CASH" {{ request('method') === 'CASH' ? 'selected' : '' }}>CASH</option>
            <option value="GCASH" {{ request('method') === 'GCASH' ? 'selected' : '' }}>GCASH</option>
            <option value="MAYA" {{ request('method') === 'MAYA' ? 'selected' : '' }}>MAYA</option>
            <option value="CREDIT_CARD" {{ request('method') === 'CREDIT_CARD' ? 'selected' : '' }}>CREDIT CARD</option>
            <option value="DEBIT_CARD" {{ request('method') === 'DEBIT_CARD' ? 'selected' : '' }}>DEBIT CARD</option>
            <option value="CHECK" {{ request('method') === 'CHECK' ? 'selected' : '' }}>CHECK</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="From Date">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="To Date">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-magnifying-glass me-1"></i> Filter</button>
          <a href="{{ route('collection.receipts') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Official Receipt #</th>
              <th>Date</th>
              <th>Patient / Payor</th>
              <th>Invoice Number</th>
              <th>Payment Method</th>
              <th>Shift / Terminal</th>
              <th>General Ledger</th>
              <th class="text-end">Amount Paid (₱)</th>
              <th class="text-center">Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $pay)
            @php
              $orNo = $pay->officialReceipt?->or_number ?? $pay->payment_reference;
              $isCancelled = ($pay->officialReceipt?->status === 'CANCELLED');
            @endphp
            <tr class="{{ $isCancelled ? 'table-light text-muted' : '' }}">
              <td>
                <span class="font-monospace fw-bold {{ $isCancelled ? 'text-decoration-line-through text-danger' : 'text-primary' }}">
                  {{ $orNo }}
                </span>
                <div class="fs-xs text-muted font-monospace">{{ $pay->payment_reference }}</div>
              </td>
              <td class="font-monospace fs-xs">{{ $pay->payment_date ? $pay->payment_date->format('M d, Y') : '-' }}</td>
              <td>
                <strong class="d-block text-dark">{{ $pay->officialReceipt?->payor_name ?: ($pay->patientAccount?->full_name ?? 'Walk-In') }}</strong>
                <span class="fs-xs text-muted font-monospace">{{ $pay->patientAccount?->patient_id_number }}</span>
              </td>
              <td>
                <span class="badge bg-light text-dark font-monospace border">
                  {{ $pay->invoice?->invoice_number ?? 'COP-SETTLED' }}
                </span>
              </td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $pay->payment_method }}</span></td>
              <td>
                <span class="fs-xs font-monospace text-muted">{{ $pay->cashierShift?->shift_code ?? '-' }}</span>
              </td>
              <td>
                <a href="{{ route('gl.journal-entries') }}?search={{ $pay->payment_reference }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none">
                  <i class="ph ph-link-simple me-1"></i> JE-COL-{{ $pay->payment_reference }}
                </a>
              </td>
              <td class="text-end font-monospace fw-bold {{ $isCancelled ? 'text-muted' : 'text-success' }}">
                ₱{{ number_format((float) $pay->amount, 2) }}
              </td>
              <td class="text-center">
                @if($isCancelled)
                  <span class="badge bg-danger-subtle text-danger"><i class="ph ph-x-circle me-1"></i> VOIDED</span>
                @else
                  <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> VALID</span>
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('collection.receipts.print', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 px-2" title="Print Official Receipt">
                    <i class="ph ph-printer"></i>
                  </a>
                  @if(! $isCancelled)
                    <button class="btn btn-sm btn-outline-danger p-1 px-2" type="button" title="Void Official Receipt" onclick="openVoidModal('{{ $pay->id }}', '{{ $orNo }}', '{{ number_format((float) $pay->amount, 2) }}')">
                      <i class="ph ph-prohibit"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">No official payment receipts found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $payments->count() }} of {{ $payments->total() }} Official Receipts</span>
      {{ $payments->links() }}
    </div>
  </div>
</div>

<!-- Modal: Void Official Receipt -->
<div class="modal fade" id="voidReceiptModal" tabindex="-1" aria-labelledby="voidReceiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-danger-subtle">
        <h5 class="modal-title font-weight-bold text-danger"><i class="ph ph-warning me-2"></i>Void Official Receipt &amp; Reverse Ledger</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="voidReceiptForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            Voiding an official receipt marks it as <strong>CANCELLED</strong> in compliance with BIR CAS rules, restores the outstanding copay balance on the patient invoice, and automatically posts a balancing reversing General Ledger entry.
          </p>

          <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted fs-xs">Official Receipt:</span>
              <span id="voidOrNumber" class="font-monospace fw-bold text-dark">-</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted fs-xs">Amount to Reverse:</span>
              <span id="voidAmount" class="font-monospace fw-bold text-danger">-</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Reason for Voiding <span class="text-danger">*</span></label>
            <select name="reason" class="form-select form-select-sm mb-2" required>
              <option value="Erroneous Amount Tendered">Erroneous Amount Tendered</option>
              <option value="Duplicate Official Receipt">Duplicate Official Receipt</option>
              <option value="Patient Transaction Cancelled / Reversed">Patient Transaction Cancelled / Reversed</option>
              <option value="Payment Method Input Correction">Payment Method Input Correction</option>
              <option value="Management Discretion / Refund">Management Discretion / Refund</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Audit Justification Notes</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Additional audit details..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-danger"><i class="ph ph-prohibit me-1"></i> Confirm Receipt Void</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
function openVoidModal(paymentId, orNumber, amount) {
  const form = document.getElementById('voidReceiptForm');
  form.action = `/collection-management/payment-receipts/${paymentId}/void`;

  document.getElementById('voidOrNumber').textContent = orNumber;
  document.getElementById('voidAmount').textContent = '₱' + amount;

  const modal = new bootstrap.Modal(document.getElementById('voidReceiptModal'));
  modal.show();
}
</script>
@endpush
@endsection
