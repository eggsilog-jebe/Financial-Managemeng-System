@extends('layouts.app')

@section('title', 'Check Register & Issuance - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'check-register')

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
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Check Register</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Check Register &amp; Printing Hub</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Disbursement Vouchers', 'Bank Accounts']" 
          description="Manages physical check serials and bank clearing states." 
      />
      <button id="btnIssueCheck" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueCheckModal">
        <i class="ph ph-plus me-1"></i> Issue Bank Check
      </button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Total Checks Logged</span>
        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ $totalIssued ?? 0 }} Checks</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Issued / Released (Uncleared)</span>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) ($totalReleased ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Cleared via Bank Recon</span>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalCleared ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Void / Stale Checks</span>
        <h4 class="fw-bold mb-0 text-secondary font-monospace">₱{{ number_format((float) ($totalVoid ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Check Register Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('disbursement.check-register') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Check Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="ISSUED" {{ request('status') === 'ISSUED' ? 'selected' : '' }}>Issued</option>
            <option value="RELEASED" {{ request('status') === 'RELEASED' ? 'selected' : '' }}>Released</option>
            <option value="CLEARED" {{ request('status') === 'CLEARED' ? 'selected' : '' }}>Cleared</option>
            <option value="VOID" {{ request('status') === 'VOID' ? 'selected' : '' }}>Void</option>
          </select>
        </div>
        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search check #, payee, voucher..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Check Number</th>
              <th>Payee Legal Name</th>
              <th>Voucher Ref #</th>
              <th>Bank Account</th>
              <th>Check Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($checks as $chk)
            @php
              $amt = (float) $chk->amount;
              $statusBadge = match($chk->status) {
                'CLEARED'  => 'bg-success-subtle text-success',
                'RELEASED' => 'bg-info-subtle text-info',
                'VOID'     => 'bg-secondary-subtle text-secondary',
                default    => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $chk->check_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $chk->payee_name }}</div>
              </td>
              <td>
                <span class="fs-xs font-monospace text-muted">{{ $chk->disbursementVoucher?->voucher_number ?? 'N/A' }}</span>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $chk->bankAccount?->bank_name ?? 'Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $chk->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>{{ $chk->check_date ? $chk->check_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $chk->status }}</span>
                @if($chk->cleared_at)
                  <div class="fs-xs text-muted">Cleared: {{ $chk->cleared_at->format('M d') }}</div>
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('disbursement.check-register.print', $chk->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" title="Print Check Template">
                    <i class="ph ph-printer me-1"></i> Print
                  </a>
                  @if($chk->status !== 'CLEARED' && $chk->status !== 'VOID')
                    <form method="POST" action="{{ route('disbursement.check-register.clear', $chk->id) }}" onsubmit="return confirm('Mark check {{ $chk->check_number }} as CLEARED?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 fs-xs" title="Bank Clearing">
                        <i class="ph ph-check"></i> Clear
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No check register records found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $checks->firstItem() ?? 0 }} - {{ $checks->lastItem() ?? 0 }} of {{ $checks->total() }} Checks</span>
      <div>
        {{ $checks->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Check -->
<div class="modal fade" id="issueCheckModal" tabindex="-1" aria-labelledby="issueCheckModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-money me-2 text-primary"></i>Issue Bank Check</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('disbursement.check-register.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Disbursement Voucher <span class="text-danger">*</span></label>
            <select name="disbursement_voucher_id" id="issueVoucherSelect" class="form-select form-select-sm" required onchange="updateCheckFormFromVoucher(this)">
              <option value="">-- Select Approved Disbursement Voucher --</option>
              @foreach($approvedVouchers as $av)
                <option value="{{ $av->id }}" data-bank="{{ $av->bank_account_id }}" data-payee="{{ $av->payee_name }}" data-amount="{{ $av->net_disbursed_amount }}">
                  {{ $av->voucher_number }} — {{ $av->payee_name }} (₱{{ number_format((float) $av->net_disbursed_amount, 2) }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" id="issueBankSelect" class="form-select form-select-sm" required>
              @foreach($bankAccounts as $ba)
                <option value="{{ $ba->id }}">{{ $ba->bank_name }} ({{ $ba->account_number }})</option>
              @endforeach
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Number <span class="text-danger">*</span></label>
              <input type="text" name="check_number" id="issueCheckNum" class="form-control form-control-sm font-monospace" placeholder="e.g. CHK-2026-9901" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Check Date <span class="text-danger">*</span></label>
              <input type="date" name="check_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Payee Name <span class="text-danger">*</span></label>
            <input type="text" name="payee_name" id="issuePayeeName" class="form-control form-control-sm" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Amount (₱) <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" name="amount" id="issueAmount" class="form-control font-monospace" placeholder="0.00" required>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Check</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function updateCheckFormFromVoucher(selectEl) {
  const opt = selectEl.options[selectEl.selectedIndex];
  if (!opt || !opt.value) return;

  const bankId = opt.getAttribute('data-bank');
  const payee = opt.getAttribute('data-payee');
  const amount = opt.getAttribute('data-amount');

  if (bankId) document.getElementById('issueBankSelect').value = bankId;
  if (payee) document.getElementById('issuePayeeName').value = payee;
  if (amount) document.getElementById('issueAmount').value = parseFloat(amount).toFixed(2);
  document.getElementById('issueCheckNum').value = 'CHK-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000);
}
</script>
@endpush
