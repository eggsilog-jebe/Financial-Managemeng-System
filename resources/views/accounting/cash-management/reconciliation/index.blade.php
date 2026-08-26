@extends('layouts.app')

@section('title', 'Bank Reconciliation Terminal - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-reconciliation')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Reconciliation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Bank Reconciliation Terminal &amp; Matching Workstation</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['Electronic Bank Statements (CSV/MT940)']" 
          description="Matches internal cash books with external bank statements." 
      />
      <a href="{{ route('cash.bank-accounts') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-bank me-1"></i> Bank Accounts</a>
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

  <!-- Bank Account & Cutoff Selector -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('cash.bank-reconciliation') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Select Bank Account to Reconcile:</label>
          <select name="bank_account_id" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach($bankAccounts as $ba)
              <option value="{{ $ba->id }}" {{ $selectedBankId === $ba->id ? 'selected' : '' }}>
                {{ $ba->bank_name }} - {{ $ba->name }} ({{ $ba->account_number }}) &bull; GL: ₱{{ number_format((float) $ba->balance, 2) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">Statement Cutoff Date:</label>
          <input type="date" name="cutoff_date" value="{{ $cutoffDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-arrows-clockwise me-1"></i> Refresh Workspace</button>
        </div>
      </form>
    </div>
  </div>

  @php
    $curBank = $workspace['bank_account'] ?? null;
    $bookBal = (float) ($workspace['book_balance'] ?? 0);
    $outChecks = $workspace['outstanding_checks'] ?? collect();
    $totOutChecks = (float) ($workspace['total_outstanding_checks'] ?? 0);
    $depTransit = $workspace['deposits_in_transit'] ?? collect();
    $totDepTransit = (float) ($workspace['total_deposits_in_transit'] ?? 0);
  @endphp

  <!-- Real-Time Reconciliation Calculator Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">GL Ledger Book Balance</span>
        <h4 class="fw-bold mb-0 font-monospace text-dark" id="displayBookBalance">₱{{ number_format($bookBal, 2) }}</h4>
        <input type="hidden" id="rawBookBalance" value="{{ $bookBal }}">
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Uncleared Deposits in Transit</span>
        <h4 class="fw-bold mb-0 font-monospace text-success" id="displayTransitDeposits">+₱{{ number_format($totDepTransit, 2) }}</h4>
        <input type="hidden" id="rawTransitDeposits" value="{{ $totDepTransit }}">
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Uncleared Checks / Outflows</span>
        <h4 class="fw-bold mb-0 font-monospace text-danger" id="displayOutstandingChecks">-₱{{ number_format($totOutChecks, 2) }}</h4>
        <input type="hidden" id="rawOutstandingChecks" value="{{ $totOutChecks }}">
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Calculated Variance</span>
        <h4 class="fw-bold mb-0 font-monospace text-success" id="displayVariance">₱0.00</h4>
        <span class="fs-xs text-muted" id="varianceStatusText">Balanced &amp; Ready</span>
      </div>
    </div>
  </div>

  <!-- Main Matching Terminal Form -->
  <form method="POST" action="{{ route('cash.bank-reconciliation.post') }}" id="reconciliationForm">
    @csrf
    <input type="hidden" name="bank_account_id" value="{{ $selectedBankId }}">
    <input type="hidden" name="cutoff_date" value="{{ $cutoffDate }}">
    <input type="hidden" name="book_balance" value="{{ $bookBal }}">

    <div class="row g-4 mb-4">
      <!-- Left: Uncleared Checks / Disbursements Register -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-check-square-offset me-2 text-danger"></i>Outstanding Checks &amp; Disbursements</h6>
            <span class="badge bg-danger-subtle text-danger">{{ count($outChecks) }} Items</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
              <table class="table table-hover align-middle mb-0 fs-xs">
                <thead class="table-light sticky-top">
                  <tr>
                    <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="toggleAllChecks" onclick="toggleAll('clearedChecksGroup', this.checked)"></th>
                    <th>Check / Voucher #</th>
                    <th>Payee</th>
                    <th>Date</th>
                    <th class="text-end">Amount (₱)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($outChecks as $chk)
                  <tr>
                    <td>
                      <input type="checkbox" name="cleared_check_ids[]" value="{{ $chk->id }}" 
                             data-amount="{{ (float) $chk->amount }}" 
                             class="form-check-input clearedChecksGroup" onchange="recalculateReconciliation()">
                    </td>
                    <td>
                      <strong class="font-monospace text-primary">{{ $chk->check_number }}</strong>
                      <span class="d-block text-muted">{{ $chk->disbursementVoucher?->voucher_number ?? 'DV-DIRECT' }}</span>
                    </td>
                    <td>{{ $chk->payee_name }}</td>
                    <td class="font-monospace">{{ $chk->check_date ? $chk->check_date->format('M d, Y') : '-' }}</td>
                    <td class="text-end font-monospace fw-bold text-danger">₱{{ number_format((float) $chk->amount, 2) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">No outstanding checks awaiting clearance.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Deposits in Transit & Statement Inputs -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-receipt me-2 text-success"></i>Uncleared Deposits in Transit</h6>
            <span class="badge bg-success-subtle text-success">{{ count($depTransit) }} Deposits</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
              <table class="table table-hover align-middle mb-0 fs-xs">
                <thead class="table-light sticky-top">
                  <tr>
                    <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="toggleAllDeposits" onclick="toggleAll('clearedDepositsGroup', this.checked)"></th>
                    <th>Deposit Ref #</th>
                    <th>Shift Code</th>
                    <th>Date</th>
                    <th class="text-end">Amount (₱)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($depTransit as $dep)
                  <tr>
                    <td>
                      <input type="checkbox" name="cleared_deposit_ids[]" value="{{ $dep->id }}" 
                             data-amount="{{ (float) $dep->total_deposited }}" 
                             class="form-check-input clearedDepositsGroup" onchange="recalculateReconciliation()">
                    </td>
                    <td><strong class="font-monospace text-primary">{{ $dep->deposit_reference }}</strong></td>
                    <td>{{ $dep->cashierShift?->shift_code ?? 'Manual' }}</td>
                    <td class="font-monospace">{{ $dep->deposit_date ? $dep->deposit_date->format('M d, Y') : '-' }}</td>
                    <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $dep->total_deposited, 2) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center py-3 text-muted">No deposits in transit.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Bank Statement Entry Card -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-transparent border-bottom p-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-bank me-2 text-primary"></i>Bank Statement Entry &amp; Sign-Off</h6>
          </div>
          <div class="card-body p-3">
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Statement Ending Date <span class="text-danger">*</span></label>
                <input type="date" name="statement_date" id="inputStatementDate" value="{{ $cutoffDate }}" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Ending Statement Balance (₱) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="statement_balance" id="inputStatementBalance" class="form-control form-control-sm font-monospace fw-bold text-end" 
                       value="{{ $bookBal }}" required oninput="recalculateReconciliation()">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Reconciliation Notes &amp; Audit Comments</label>
              <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Audit remarks, statement discrepancies noted..."></textarea>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" id="btnPostReconciliation" class="btn btn-success btn-sm px-4 fw-bold">
                <i class="ph ph-shield-check me-1"></i> Post Bank Reconciliation
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Section: Past Reconciliation History Register -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-clock-counter-clockwise me-2 text-primary"></i>Bank Reconciliation History Log</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Statement Date</th>
              <th>Cutoff Date</th>
              <th class="text-end">Statement Balance (₱)</th>
              <th class="text-end">Book Balance (₱)</th>
              <th class="text-end">Variance (₱)</th>
              <th>Reconciler</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reconciliations ?? [] as $r)
            <tr>
              <td class="font-monospace fs-xs">{{ $r->statement_date ? $r->statement_date->format('M d, Y') : '-' }}</td>
              <td class="font-monospace fs-xs">{{ $r->cutoff_date ? $r->cutoff_date->format('M d, Y') : '-' }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $r->statement_balance, 2) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $r->book_balance, 2) }}</td>
              <td class="text-end font-monospace text-success fw-bold">₱{{ number_format((float) $r->variance, 2) }}</td>
              <td>{{ $r->reconciler?->name ?? 'Treasury Staff' }}</td>
              <td class="text-center"><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> {{ $r->status }}</span></td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No historical bank reconciliations logged.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function toggleAll(className, isChecked) {
  document.querySelectorAll('.' + className).forEach(cb => {
    cb.checked = isChecked;
  });
  recalculateReconciliation();
}

function recalculateReconciliation() {
  const bookBal = parseFloat(document.getElementById('rawBookBalance')?.value || 0);
  const stmtBal = parseFloat(document.getElementById('inputStatementBalance')?.value || 0);

  // Calculate selected cleared checks
  let selectedChecks = 0;
  document.querySelectorAll('.clearedChecksGroup:checked').forEach(cb => {
    selectedChecks += parseFloat(cb.getAttribute('data-amount') || 0);
  });

  // Calculate selected cleared deposits
  let selectedDeposits = 0;
  document.querySelectorAll('.clearedDepositsGroup:checked').forEach(cb => {
    selectedDeposits += parseFloat(cb.getAttribute('data-amount') || 0);
  });

  // Adjusted Book Balance = Book Balance + Cleared Deposits - Cleared Checks
  // Variance = Statement Balance - Book Balance
  const variance = stmtBal - bookBal;

  const dispVar = document.getElementById('displayVariance');
  const statText = document.getElementById('varianceStatusText');
  const btnPost = document.getElementById('btnPostReconciliation');

  if (dispVar) {
    dispVar.textContent = (variance >= 0 ? '' : '-') + '₱' + Math.abs(variance).toFixed(2);
    if (Math.abs(variance) < 0.005) {
      dispVar.className = 'fw-bold mb-0 font-monospace text-success';
      statText.textContent = 'Balanced with Zero Variance (₱0.00)';
      statText.className = 'fs-xs text-success fw-bold';
      btnPost.disabled = false;
    } else {
      dispVar.className = 'fw-bold mb-0 font-monospace text-danger';
      statText.textContent = 'Unresolved Variance: ₱' + Math.abs(variance).toFixed(2);
      statText.className = 'fs-xs text-danger fw-bold';
      btnPost.disabled = false; // Form will enforce backend exception validation
    }
  }
}
document.addEventListener('DOMContentLoaded', recalculateReconciliation);
</script>
@endpush
@endsection
