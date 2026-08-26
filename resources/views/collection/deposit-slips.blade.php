@extends('layouts.app')

@section('title', 'Batch Deposit Slips - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'deposit-slips')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Deposit Slips</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Deposit Slips &amp; Cash Handover</h1>
      <p class="text-muted fs-xs mb-0">Consolidate daily cashier cash and check collections into bank deposit slips and custody turnover manifests.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Turnover Manifest</button>
      <button id="btnCreateSlip" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createDepositSlipModal"><i class="ph ph-plus-circle me-1"></i> Create Deposit Slip</button>
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
          <span class="text-muted small fw-medium">Prepared Deposit Slips</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-path fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($deposits ?? []) }} Batch Slips</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Vault Deposits</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalDeposits ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Closed Shifts Pending Remittance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($closedShifts ?? []) }} Shifts</h4>
      </div>
    </div>
  </div>

  <!-- Section: Closed Shifts Ready for Custody Handover -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-vault me-2 text-warning"></i>Closed Shifts Ready for Bank Remittance</h6>
      <span class="badge bg-warning-subtle text-warning">{{ count($closedShifts ?? []) }} Awaiting Deposit</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Shift Code</th>
              <th>Terminal</th>
              <th>Cashier Officer</th>
              <th>Closed Timestamp</th>
              <th class="text-end">Actual Cash Counted</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($closedShifts ?? [] as $cs)
            <tr>
              <td><span class="font-monospace text-primary fw-bold">{{ $cs->shift_code }}</span></td>
              <td>{{ $cs->terminal_name }}</td>
              <td>{{ $cs->cashier?->name ?? 'Cashier' }}</td>
              <td class="font-monospace fs-xs">{{ $cs->closed_at ? $cs->closed_at->format('M d, Y h:i A') : '-' }}</td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $cs->actual_cash_counted, 2) }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary" type="button" onclick="prepareDepositForShift('{{ $cs->id }}', '{{ $cs->shift_code }}', '{{ $cs->actual_cash_counted }}')">
                  <i class="ph ph-plus-circle me-1"></i> Prepare Deposit
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-3 text-muted">No closed cashier shifts pending deposit.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Section: Prepared Batch Deposit Slips -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-receipt me-2 text-primary"></i>Batch Deposit Slips History</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Deposit Ref #</th>
              <th>Date</th>
              <th>Depository Bank</th>
              <th>Shift Ref</th>
              <th class="text-end">Cash Amount (₱)</th>
              <th class="text-end">Check Amount (₱)</th>
              <th class="text-end">Total Deposited (₱)</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($deposits ?? [] as $d)
            @php
              $st = $d->status;
              $badge = match($st) {
                  'PREPARED' => 'bg-secondary-subtle text-secondary',
                  'IN_TRANSIT' => 'bg-warning-subtle text-warning',
                  'DEPOSITED', 'CLEARED' => 'bg-success-subtle text-success',
                  'RECONCILED' => 'bg-primary-subtle text-primary',
                  default => 'bg-light text-dark'
              };
            @endphp
            <tr>
              <td><span class="font-monospace text-primary fw-bold">{{ $d->deposit_reference }}</span></td>
              <td class="font-monospace fs-xs">{{ $d->deposit_date ? $d->deposit_date->format('M d, Y') : '-' }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $d->bankAccount?->bank_name ?? 'Operational Bank' }}</div>
                <span class="fs-xs text-muted font-monospace">{{ $d->bankAccount?->account_number }}</span>
              </td>
              <td><span class="font-monospace fs-xs text-muted">{{ $d->cashierShift?->shift_code ?? 'Manual' }}</span></td>
              <td class="text-end font-monospace text-muted">₱{{ number_format((float) $d->cash_amount, 2) }}</td>
              <td class="text-end font-monospace text-muted">₱{{ number_format((float) $d->check_amount, 2) }}</td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $d->total_deposited, 2) }}</td>
              <td class="text-center"><span class="badge {{ $badge }}">{{ $st }}</span></td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No deposit slips generated yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Deposit Slip -->
<div class="modal fade" id="createDepositSlipModal" tabindex="-1" aria-labelledby="createDepositSlipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createDepositSlipModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Bank Deposit Slip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('collection.bank-deposits.store') }}" id="createSlipForm">
          @csrf
          <input type="hidden" name="cashier_shift_id" id="slipShiftId">

          <div class="mb-3">
            <label class="form-label small fw-semibold">Depository Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" class="form-select form-select-sm" required>
              @foreach($bankAccounts ?? [] as $ba)
                <option value="{{ $ba->id }}">{{ $ba->bank_name }} - {{ $ba->account_name }} ({{ $ba->account_number }})</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Deposit Date <span class="text-danger">*</span></label>
            <input type="date" name="deposit_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Cash Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="cash_amount" id="slipCashAmount" class="form-control form-control-sm text-end font-monospace" value="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Amount (₱)</label>
              <input type="number" step="0.01" min="0" name="check_amount" id="slipCheckAmount" class="form-control form-control-sm text-end font-monospace" value="0.00">
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Deposit Slip</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function prepareDepositForShift(shiftId, shiftCode, amount) {
  document.getElementById('slipShiftId').value = shiftId;
  document.getElementById('slipCashAmount').value = parseFloat(amount).toFixed(2);
  const modal = new bootstrap.Modal(document.getElementById('createDepositSlipModal'));
  modal.show();
}
</script>
@endpush
