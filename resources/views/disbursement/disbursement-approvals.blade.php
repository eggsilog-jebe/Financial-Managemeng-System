@extends('layouts.app')

@section('title', 'Disbursement Approvals - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'disbursement-approval')

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
          <li class="breadcrumb-item active">Approvals &amp; Release Workstation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Executive Disbursement Approvals &amp; Release</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Bank Accounts', 'Vendor Invoices', 'GL 1020']" 
          description="Authorizes final fund releases." 
      />
      <a href="{{ route('disbursement.payment-requests') }}" class="btn btn-outline-primary btn-sm"><i class="ph ph-receipt me-1"></i> Payment Requests Hub</a>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Prepared (Pending Audit)</span>
        <h4 class="fw-bold mb-0 text-secondary font-monospace">₱{{ number_format((float) ($totalPrepared ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Audited (Pending Approval)</span>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) ($totalAudited ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Approved (Ready for Release)</span>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) ($totalApproved ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted small fw-medium">Total Released Payouts</span>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalReleased ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Approvals Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('disbursement.disbursement-approval') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="AUDITED" {{ request('status') === 'AUDITED' ? 'selected' : '' }}>Audited (Pending Approval)</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved (Ready for Release)</option>
            <option value="PREPARED" {{ request('status') === 'PREPARED' ? 'selected' : '' }}>Prepared</option>
            <option value="RELEASED" {{ request('status') === 'RELEASED' ? 'selected' : '' }}>Released</option>
          </select>
        </div>
        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search voucher #, payee..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref #</th>
              <th>Payee &amp; Description</th>
              <th>Bank Account</th>
              <th>Method</th>
              <th>Voucher Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vouchers as $v)
            @php
              $amt = (float) $v->net_disbursed_amount;
              $statusBadge = match($v->status) {
                'RELEASED' => 'bg-success-subtle text-success',
                'APPROVED' => 'bg-info-subtle text-info',
                'AUDITED'  => 'bg-primary-subtle text-primary',
                default    => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $v->voucher_number }}</span>
                @if($v->check_or_eft_ref)
                  <div class="fs-xs text-muted font-monospace">Ref: {{ $v->check_or_eft_ref }}</div>
                @endif
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $v->payee_name }}</div>
                <div class="fs-xs text-muted">{{ $v->description ?? ($v->purchaseBill ? "Bill {$v->purchaseBill->bill_number}" : 'Disbursement Requisition') }}</div>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $v->bankAccount?->bank_name ?? 'Operating Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $v->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', $v->payment_method) }}</span>
              </td>
              <td>{{ $v->voucher_date ? $v->voucher_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $v->status }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($v->status === 'AUDITED' || $v->status === 'PREPARED' || $v->status === 'DRAFT')
                    <form method="POST" action="{{ route('disbursement.disbursement-approvals.approve', $v->id) }}" onsubmit="return confirm('Approve disbursement voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fs-xs" title="Finance Management Approval">
                        <i class="ph ph-stamp me-1"></i> Approve
                      </button>
                    </form>
                  @elseif($v->status === 'APPROVED')
                    <button type="button" class="btn btn-sm btn-success py-1 px-2 fs-xs" onclick="openDisburseReleaseModal({{ $v->id }}, '{{ $v->voucher_number }}', '{{ addslashes($v->payee_name) }}', '{{ $v->payment_method }}', {{ $amt }})">
                      <i class="ph ph-paper-plane-tilt me-1"></i> Release
                    </button>
                  @else
                    <span class="badge bg-light text-muted border">
                      <i class="ph ph-check-double text-success me-1"></i> Settled
                    </span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No disbursement vouchers found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $vouchers->firstItem() ?? 0 }} - {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} Vouchers</span>
      <div>
        {{ $vouchers->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Release Disbursement Payout -->
<div class="modal fade" id="disburseReleaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-success-subtle">
        <h5 class="modal-title font-weight-bold text-success"><i class="ph ph-check-circle me-2"></i>Executive Payout Release</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="disburseReleaseForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Voucher Reference</label>
            <div class="fw-bold font-monospace text-primary fs-6" id="drelVoucherRef">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Payee Legal Name</label>
            <div class="fw-semibold text-dark" id="drelPayeeName">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Net Disbursed Amount</label>
            <div class="fw-bold font-monospace text-dark fs-5" id="drelAmount">₱0.00</div>
          </div>

          <div id="drelCheckSection" class="p-3 bg-light rounded-3 mb-3">
            <label class="form-label small fw-semibold text-primary"><i class="ph ph-pencil-simple-line me-1"></i>Check Number (Check Register)</label>
            <input type="text" name="check_number" id="drelCheckNumber" class="form-control form-control-sm font-monospace" placeholder="e.g. CHK-10299401">
            <div class="mt-2">
              <label class="form-label small fw-semibold">Check Issuance Date</label>
              <input type="date" name="check_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div id="drelEftSection" class="p-3 bg-light rounded-3 mb-3" style="display: none;">
            <label class="form-label small fw-semibold text-primary"><i class="ph ph-bank me-1"></i>EFT Reference / Trace Number</label>
            <input type="text" name="eft_reference" id="drelEftReference" class="form-control form-control-sm font-monospace" placeholder="e.g. EFT-PN-20260826-091">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Treasury Settlement Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Released across Treasury Counter / EFT Cleared">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success"><i class="ph ph-check me-1"></i> Confirm Executive Release</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openDisburseReleaseModal(voucherId, voucherRef, payee, paymentMethod, amount) {
  const form = document.getElementById('disburseReleaseForm');
  form.action = `/disbursement-management/disbursement-approvals/${voucherId}/release`;

  document.getElementById('drelVoucherRef').textContent = voucherRef;
  document.getElementById('drelPayeeName').textContent = payee;
  document.getElementById('drelAmount').textContent = '₱' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const checkSection = document.getElementById('drelCheckSection');
  const eftSection = document.getElementById('drelEftSection');
  const checkInput = document.getElementById('drelCheckNumber');
  const eftInput = document.getElementById('drelEftReference');

  if (paymentMethod === 'CHECK') {
    checkSection.style.display = 'block';
    eftSection.style.display = 'none';
    checkInput.value = 'CHK-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000);
    eftInput.value = '';
  } else {
    checkSection.style.display = 'none';
    eftSection.style.display = 'block';
    checkInput.value = '';
    eftInput.value = 'EFT-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000);
  }

  const modalEl = document.getElementById('disburseReleaseModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}
</script>
@endpush
