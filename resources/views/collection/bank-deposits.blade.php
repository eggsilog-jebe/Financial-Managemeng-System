@extends('layouts.app')

@section('title', 'Bank Deposits - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'bank-deposits')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Bank Deposits</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Deposits Log &amp; Verification</h1>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('collection.deposit-slips') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-path me-1"></i> Batch Deposit Slips</a>
      <button id="btnCreateDeposit" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createDepositModal"><i class="ph ph-plus-circle me-1"></i> New Bank Deposit</button>
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
          <span class="text-muted small fw-medium">Total Bank Deposits</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalDeposits ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Records</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($deposits ?? []) }} Deposits</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Teller Clearance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        @php
          $pendingCnt = ($deposits ?? collect())->whereIn('status', ['PREPARED', 'IN_TRANSIT'])->count();
        @endphp
        <h4 class="fw-bold mb-0 text-dark">{{ $pendingCnt }} Slips</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-bank me-2 text-primary"></i>Bank Deposit &amp; Clearing Register</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Deposit Ref #</th>
              <th>Deposit Date</th>
              <th>Bank Account</th>
              <th>Cashier Shift</th>
              <th class="text-end">Cash / Check Amount</th>
              <th class="text-end">Total Deposited</th>
              <th>Bank Reference / Teller</th>
              <th class="text-center">Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($deposits ?? [] as $d)
            @php
              $st = $d->status;
              $isCleared = in_array($st, ['DEPOSITED', 'CLEARED', 'RECONCILED']);
              $badge = $isCleared ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
            @endphp
            <tr>
              <td><span class="font-monospace text-primary fw-bold">{{ $d->deposit_reference }}</span></td>
              <td class="font-monospace fs-xs">{{ $d->deposit_date ? $d->deposit_date->format('M d, Y') : '-' }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $d->bankAccount?->bank_name ?? 'Bank Account' }}</div>
                <span class="fs-xs text-muted font-monospace">{{ $d->bankAccount?->account_number }}</span>
              </td>
              <td><span class="font-monospace fs-xs text-muted">{{ $d->cashierShift?->shift_code ?? '-' }}</span></td>
              <td class="text-end font-monospace fs-xs text-muted">
                ₱{{ number_format((float) $d->cash_amount, 2) }} / ₱{{ number_format((float) $d->check_amount, 2) }}
              </td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $d->total_deposited, 2) }}</td>
              <td>
                @if($d->bank_reference_number)
                  <span class="font-monospace fs-xs d-block text-dark">{{ $d->bank_reference_number }}</span>
                  <span class="fs-xs text-muted">{{ $d->validated_by_teller }}</span>
                @else
                  <span class="text-muted fs-xs fst-italic">Pending teller validation</span>
                @endif
              </td>
              <td class="text-center"><span class="badge {{ $badge }}">{{ $st }}</span></td>
              <td class="text-end">
                @if(! $isCleared)
                  <button class="btn btn-sm btn-outline-success p-1 px-2" type="button" title="Validate & Clear Bank Deposit" onclick="openClearModal('{{ $d->id }}', '{{ $d->deposit_reference }}', '{{ number_format((float) $d->total_deposited, 2) }}')">
                    <i class="ph ph-check-circle me-1"></i> Clear
                  </button>
                  <button class="btn btn-sm btn-outline-danger p-1 px-2" type="button" title="Reject Deposit Slip" onclick="openRejectModal('{{ $d->id }}', '{{ $d->deposit_reference }}')">
                    <i class="ph ph-x-circle"></i>
                  </button>
                @else
                  <span class="badge bg-light text-success border py-1 px-2"><i class="ph ph-check-fat me-1"></i> Cleared &amp; GL Posted</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No bank deposits logged.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ count($deposits ?? []) }} Bank Deposits</span>
    </div>
  </div>
</div>

<!-- Modal: New Bank Deposit -->
<div class="modal fade" id="createDepositModal" tabindex="-1" aria-labelledby="createDepositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createDepositModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Record Bank Deposit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('collection.bank-deposits.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Depository Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" class="form-select form-select-sm" required>
              @foreach($bankAccounts ?? [] as $ba)
                <option value="{{ $ba->id }}">{{ $ba->bank_name }} - {{ $ba->account_name }} ({{ $ba->account_number }})</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Linked Closed Cashier Shift (Optional)</label>
            <select name="cashier_shift_id" class="form-select form-select-sm">
              <option value="">-- No shift linked (Direct deposit) --</option>
              @foreach($closedShifts ?? [] as $cs)
                <option value="{{ $cs->id }}">{{ $cs->shift_code }} | {{ $cs->terminal_name }} (₱{{ number_format((float) $cs->actual_cash_counted, 2) }})</option>
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
              <input type="number" step="0.01" min="0" name="cash_amount" class="form-control form-control-sm text-end font-monospace" value="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Amount (₱)</label>
              <input type="number" step="0.01" min="0" name="check_amount" class="form-control form-control-sm text-end font-monospace" value="0.00">
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Bank Deposit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Validate & Clear Bank Deposit -->
<div class="modal fade" id="clearDepositModal" tabindex="-1" aria-labelledby="clearDepositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success-subtle text-success border-0 pb-2">
        <h5 class="modal-title font-weight-bold" id="clearDepositModalLabel"><i class="ph ph-check-circle me-2"></i>Validate &amp; Clear Bank Deposit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="clearForm" method="POST" action="">
          @csrf
          <p class="fs-sm text-muted">
            Clearing deposit <strong id="clearDepositRef" class="text-primary"></strong> for <strong id="clearDepositAmount" class="text-dark"></strong> will update the hospital's bank balance and trigger balanced GL posting (DR 1020 Cash in Bank / CR 1011 Undeposited Collections).
          </p>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank Machine Validation / Reference Code <span class="text-danger">*</span></label>
            <input type="text" name="bank_reference_number" class="form-control form-control-sm font-monospace" placeholder="e.g. BDO-TRN-9018471" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank Branch / Teller ID (Optional)</label>
            <input type="text" name="validated_by_teller" class="form-control form-control-sm" placeholder="e.g. Teller #14 - Makati Main Branch">
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-success"><i class="ph ph-bank me-1"></i> Post Bank Clearance &amp; GL</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reject Deposit -->
<div class="modal fade" id="rejectDepositModal" tabindex="-1" aria-labelledby="rejectDepositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger-subtle text-danger border-0 pb-2">
        <h5 class="modal-title font-weight-bold" id="rejectDepositModalLabel"><i class="ph ph-x-circle me-2"></i>Reject Deposit Slip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="rejectForm" method="POST" action="">
          @csrf
          <p class="fs-sm text-muted">
            Are you sure you want to flag deposit <strong id="rejectDepositRef" class="text-danger"></strong> as rejected/discrepant?
          </p>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Discrepancy / Rejection Reason <span class="text-danger">*</span></label>
            <textarea name="reason" rows="3" class="form-control form-control-sm" placeholder="Bank teller rejected check, short cash amount..." required></textarea>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-danger"><i class="ph ph-prohibit me-1"></i> Reject Deposit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openClearModal(id, ref, amount) {
  const form = document.getElementById('clearForm');
  form.action = "{{ url('/collection-management/bank-deposits') }}/" + id + "/clear";
  document.getElementById('clearDepositRef').textContent = ref;
  document.getElementById('clearDepositAmount').textContent = '₱' + amount;
  const modal = new bootstrap.Modal(document.getElementById('clearDepositModal'));
  modal.show();
}

function openRejectModal(id, ref) {
  const form = document.getElementById('rejectForm');
  form.action = "{{ url('/collection-management/bank-deposits') }}/" + id + "/reject";
  document.getElementById('rejectDepositRef').textContent = ref;
  const modal = new bootstrap.Modal(document.getElementById('rejectDepositModal'));
  modal.show();
}
</script>
@endpush
