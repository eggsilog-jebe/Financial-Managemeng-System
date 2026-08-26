@extends('layouts.app')

@section('title', 'AP Payment Approvals & Disbursement - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'ap-approvals')

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
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payment Approvals &amp; Disbursement</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">AP Payment Approvals &amp; Disbursement Release</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoices & Vouchers', 'Bank Accounts', 'General Ledger']" 
          :tables="['disbursement_vouchers', 'vendor_invoices', 'bank_accounts', 'journal_entries']"
          glImpact="DR 2010 AP Vendors / CR 1020 Cash in Bank + CR 2110 EWT Payable"
          description="Executive authorization workstation to approve vendor vouchers and execute disbursements." 
      />
      <a href="{{ route('ap.invoices') }}" class="btn btn-outline-primary btn-sm"><i class="ph ph-receipt me-1"></i> Invoices &amp; Vouchers Hub</a>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Prepared Vouchers (Pending Approval)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hourglass fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) $totalPrepared, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Approved Vouchers (Ready for Release)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) $totalApproved, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Released / Disbursed</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $totalReleased, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Approvals Queue Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ap.payment-approvals.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="statusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Voucher Status:</label>
          <select id="statusSelect" name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Prepared / Draft</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
            <option value="RELEASED" {{ request('status') === 'RELEASED' ? 'selected' : '' }}>Released &amp; Settled</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search voucher #, payee, check ref..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="apApprovalsTable">
          <thead class="table-light">
            <tr>
              <th>Voucher Number</th>
              <th>Payee Name &amp; Bill Ref</th>
              <th>Bank Account</th>
              <th>Payment Method</th>
              <th>Voucher Date</th>
              <th class="text-end">Disbursed Amount</th>
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
                <div class="fs-xs text-muted">Bill: {{ $v->purchaseBill?->bill_number ?? 'Manual Request' }}</div>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $v->bankAccount?->bank_name ?? 'Operating Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $v->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', $v->payment_method) }}</span>
              </td>
              <td>{{ $v->voucher_date ? $v->voucher_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold fs-6 text-dark">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">
                  <i class="ph {{ $v->status === 'RELEASED' ? 'ph-check-circle' : 'ph-clock' }} me-1"></i>
                  {{ $v->status }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($v->status === 'DRAFT')
                    <form method="POST" action="{{ route('ap.payment-approvals.approve', $v->id) }}" onsubmit="return confirm('Authorize payment approval for voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fs-xs" title="Finance Manager Approval">
                        <i class="ph ph-stamp me-1"></i> Approve
                      </button>
                    </form>
                  @elseif($v->status === 'APPROVED')
                    <button type="button" class="btn btn-sm btn-success py-1 px-2 fs-xs" onclick="openReleaseModal({{ $v->id }}, '{{ $v->voucher_number }}', '{{ addslashes($v->payee_name) }}', '{{ $v->payment_method }}', {{ $amt }})">
                      <i class="ph ph-paper-plane-tilt me-1"></i> Release
                    </button>
                  @else
                    <span class="badge bg-light text-muted border">
                      <i class="ph ph-check-double me-1 text-success"></i> Disbursed
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

<!-- Modal: Release Disbursement Voucher -->
<div class="modal fade" id="releaseVoucherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-success-subtle">
        <h5 class="modal-title font-weight-bold text-success"><i class="ph ph-check-circle me-2"></i>Release Payment &amp; Issue Check</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="releaseVoucherForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Voucher Reference</label>
            <div class="fw-bold font-monospace text-primary fs-6" id="relVoucherRef">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Payee Legal Name</label>
            <div class="fw-semibold text-dark" id="relPayeeName">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Net Disbursed Amount</label>
            <div class="fw-bold font-monospace text-dark fs-5" id="relAmount">₱0.00</div>
          </div>

          <div id="checkDetailsSection" class="p-3 bg-light rounded-3 mb-3">
            <label class="form-label small fw-semibold text-primary"><i class="ph ph-pencil-simple-line me-1"></i>Check Number (Check Register)</label>
            <input type="text" name="check_number" id="relCheckNumber" class="form-control form-control-sm font-monospace" placeholder="e.g. CHK-10299401">
            <div class="mt-2">
              <label class="form-label small fw-semibold">Check Issuance Date</label>
              <input type="date" name="check_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Treasury Settlement Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Released across Treasury Counter / EFT Cleared">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success"><i class="ph ph-check me-1"></i> Confirm Payment Release</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openReleaseModal(voucherId, voucherRef, payee, paymentMethod, amount) {
  const form = document.getElementById('releaseVoucherForm');
  form.action = `/accounts-payable/payment-approvals/${voucherId}/release`;

  document.getElementById('relVoucherRef').textContent = voucherRef;
  document.getElementById('relPayeeName').textContent = payee;
  document.getElementById('relAmount').textContent = '₱' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const checkSection = document.getElementById('checkDetailsSection');
  const checkInput = document.getElementById('relCheckNumber');

  if (paymentMethod === 'CHECK') {
    checkSection.style.display = 'block';
    checkInput.value = 'CHK-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000);
  } else {
    checkSection.style.display = 'none';
    checkInput.value = '';
  }

  const modalEl = document.getElementById('releaseVoucherModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}
</script>
@endpush
