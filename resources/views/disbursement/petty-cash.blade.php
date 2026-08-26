@extends('layouts.app')

@section('title', 'Petty Cash Custody & Replenishment - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'petty-cash')

@section('content')
<div class="container-fluid p-4">
  <!-- Alerts -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-check-circle fs-4 me-2"></i>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning-circle fs-4 me-2"></i>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Petty Cash Custody</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Petty Cash Custody &amp; Replenishment</h1>
      <p class="text-muted fs-xs mb-0">Record small emergency cash expenses, track custodian cash balances, and request fund replenishments.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Petty Cash Float', 'GL 5000 Expense Accounts']" 
          description="Tracks revolving emergency cash drawer, expense receipts, and replenishment checks." 
      />
      @if($funds->isNotEmpty())
        <!-- Fund Selector Dropdown -->
        <div class="d-flex align-items-center gap-2 me-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-vault me-1"></i> Active Fund:</label>
          <select class="form-select form-select-sm bg-light fw-semibold" style="min-width: 240px;" onchange="window.location.href='{{ route('disbursement.petty-cash') }}?fund_id=' + this.value">
            @foreach($funds as $f)
              <option value="{{ $f->id }}" {{ $fund && $fund->id === $f->id ? 'selected' : '' }}>
                {{ $f->fund_name }} ({{ $f->custodian_name }})
              </option>
            @endforeach
          </select>
        </div>
      @endif

      <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createFundModal">
        <i class="ph ph-plus-circle me-1"></i> New Fund
      </button>

      @if($fund)
        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#replenishFundModal">
          <i class="ph ph-arrows-clockwise me-1"></i> Replenish Revolving Fund
        </button>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
          <i class="ph ph-plus me-1"></i> Record Expense Slip
        </button>
      @endif
    </div>
  </div>

  @if($fund)
    <!-- Metric Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted small fw-medium">Revolving Float Limit</span>
          <h4 class="fw-bold mb-0 text-dark font-monospace">₱{{ number_format((float) $fund->float_limit, 2) }}</h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted small fw-medium">Current Cash on Hand</span>
          <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $fund->current_balance, 2) }}</h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted small fw-medium">Unreplenished Expense Slips</span>
          <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) $unreplenishedTotal, 2) }}</h4>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted small fw-medium">Custodian in Charge</span>
          <h5 class="fw-bold mb-0 text-dark">{{ $fund->custodian_name }}</h5>
        </div>
      </div>
    </div>

    <!-- Expense Slips Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="ph ph-receipt me-1 text-primary"></i> Petty Cash Voucher Expense Log &mdash; {{ $fund->fund_name }}</h6>
        <span class="badge bg-light text-dark border">{{ $expenses->total() }} Logged Vouchers</span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Voucher #</th>
                <th>Payee &amp; Department</th>
                <th>Date</th>
                <th>Particulars</th>
                <th>Receipt Ref</th>
                <th class="text-end">Amount (₱)</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($expenses as $e)
              @php
                $statusBadge = match($e->status) {
                  'REPLENISHED' => 'bg-success-subtle text-success',
                  'VOIDED'      => 'bg-secondary-subtle text-secondary',
                  default       => 'bg-warning-subtle text-warning',
                };
              @endphp
              <tr>
                <td>
                  <span class="font-monospace fw-bold text-primary">{{ $e->voucher_number }}</span>
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ $e->payee }}</div>
                  <div class="fs-xs text-muted">{{ $e->department }}</div>
                </td>
                <td>{{ $e->expense_date ? $e->expense_date->format('M d, Y') : '—' }}</td>
                <td>{{ $e->particulars }}</td>
                <td>
                  <span class="font-monospace fs-xs text-muted">{{ $e->receipt_ref ?? '—' }}</span>
                </td>
                <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format((float) $e->amount, 2) }}</td>
                <td>
                  <span class="badge {{ $statusBadge }}">{{ $e->status }}</span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">No petty cash expense slips logged yet for this fund.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
        <span class="text-muted fs-xs">Showing {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} Slips</span>
        <div>
          {{ $expenses->links() }}
        </div>
      </div>
    </div>
  @else
    <!-- Zero State -->
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
      <div class="mb-3">
        <i class="ph ph-vault text-muted" style="font-size: 3.5rem;"></i>
      </div>
      <h4 class="fw-bold text-dark mb-1">No Petty Cash Revolving Funds Registered</h4>
      <p class="text-muted fs-sm mb-4">Set up a departmental or operational revolving fund with a designated custodian and float limit to start logging petty disbursements.</p>
      <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFundModal">
          <i class="ph ph-plus-circle me-1"></i> Register New Petty Cash Fund
        </button>
      </div>
    </div>
  @endif
</div>

<!-- Modal: Create Petty Cash Fund -->
<div class="modal fade" id="createFundModal" tabindex="-1" aria-labelledby="createFundModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-vault me-2 text-primary"></i>Register Petty Cash Fund</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('disbursement.petty-cash.funds.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Fund Name <span class="text-danger">*</span></label>
            <input type="text" name="fund_name" class="form-control form-control-sm" placeholder="e.g. Main Hospital Operating Petty Cash, ER Petty Cash" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Designated Custodian Legal Name <span class="text-danger">*</span></label>
            <input type="text" name="custodian_name" class="form-control form-control-sm" placeholder="e.g. Maria Santos (Chief Cashier)" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Float Limit (₱) <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" name="float_limit" class="form-control font-monospace" placeholder="50000.00" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">GL Account Code</label>
              <input type="text" name="gl_code" class="form-control form-control-sm font-monospace" placeholder="1030" value="1030">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Create Fund</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if($fund)
<!-- Modal: Record Expense Slip -->
<div class="modal fade" id="createExpenseModal" tabindex="-1" aria-labelledby="createExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-receipt me-2 text-primary"></i>Record Expense Slip &mdash; {{ $fund->fund_name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('disbursement.petty-cash.expense') }}">
        @csrf
        <input type="hidden" name="petty_cash_fund_id" value="{{ $fund->id }}">
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee Name <span class="text-danger">*</span></label>
              <input type="text" name="payee" class="form-control form-control-sm" placeholder="e.g. Courier Service / Medical Supplies" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
              <input type="text" name="department" class="form-control form-control-sm" placeholder="e.g. Administration, ER, Pharmacy" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expense Date <span class="text-danger">*</span></label>
              <input type="date" name="expense_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expense Amount (₱) <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" name="amount" class="form-control font-monospace" placeholder="0.00" max="{{ $fund->current_balance }}" required>
              </div>
              <div class="form-text fs-xs text-muted">Max cash available: ₱{{ number_format((float) $fund->current_balance, 2) }}</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Particulars / Purpose <span class="text-danger">*</span></label>
            <input type="text" name="particulars" class="form-control form-control-sm" placeholder="e.g. Urgent specimen dispatch / office hardware" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Official Receipt / Invoice Ref #</label>
            <input type="text" name="receipt_ref" class="form-control form-control-sm font-monospace" placeholder="e.g. OR-88190">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Expense Slip</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Replenish Revolving Fund -->
<div class="modal fade" id="replenishFundModal" tabindex="-1" aria-labelledby="replenishFundModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-light">
        <h5 class="modal-title font-weight-bold text-primary"><i class="ph ph-arrows-clockwise me-2"></i>Replenish Petty Cash Revolving Fund</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('disbursement.petty-cash.replenish') }}">
        @csrf
        <input type="hidden" name="fund_id" value="{{ $fund->id }}">
        <div class="modal-body p-4">
          <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Total Unreplenished Expense Slips:</span>
              <span class="font-monospace fw-bold text-danger fs-6">₱{{ number_format((float) $unreplenishedTotal, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted small">Target Float Restoration:</span>
              <span class="font-monospace fw-bold text-success fs-6">₱{{ number_format((float) $fund->float_limit, 2) }}</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Disbursing Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" class="form-select form-select-sm" required>
              <option value="">-- Select Bank Account --</option>
              @foreach($bankAccounts as $b)
                <option value="{{ $b->id }}">{{ $b->bank_name }} ({{ $b->account_number }}) - Bal: ₱{{ number_format((float) $b->balance, 2) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-text fs-xs text-muted">A reimbursement disbursement voucher and balanced General Ledger entry will be generated automatically.</div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Authorize Replenishment</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection
