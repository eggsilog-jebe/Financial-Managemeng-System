@extends('layouts.app')

@section('title', 'Payment Requests - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'payment-requests')

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
          <li class="breadcrumb-item active">Payment Requests</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Payment Requisitions &amp; Vouchers</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['HRMS (Payroll Runs)', 'Vendor Invoices']" 
          description="Ingests payroll obligations and operational voucher requests." 
      />
      <button id="btnCreateRequest" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createRequestModal">
        <i class="ph ph-plus me-1"></i> Submit Payment Request
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Requisitions</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-file-text fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalRequests ?? 0 }} Vouchers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending Audit / Approval</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) ($pendingApproval ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Approved for Release</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-info font-monospace">₱{{ number_format((float) ($approvedAmount ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Released Payments</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalReleased ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Requisitions Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('disbursement.payment-requests') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="PREPARED" {{ request('status') === 'PREPARED' ? 'selected' : '' }}>Prepared (Pending Audit)</option>
            <option value="AUDITED" {{ request('status') === 'AUDITED' ? 'selected' : '' }}>Audited (Ready for Approval)</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved (Ready for Release)</option>
            <option value="RELEASED" {{ request('status') === 'RELEASED' ? 'selected' : '' }}>Released / Disbursed</option>
            <option value="VOIDED" {{ request('status') === 'VOIDED' ? 'selected' : '' }}>Voided</option>
          </select>
        </div>
        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search voucher #, payee, desc..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref #</th>
              <th>Payee &amp; Particulars</th>
              <th>Bank Account</th>
              <th>Payment Method</th>
              <th>Voucher Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Status</th>
              <th>Preparer</th>
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
                'VOIDED'   => 'bg-secondary-subtle text-secondary',
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
                <div class="fs-xs text-muted">{{ $v->description ?? ($v->purchaseBill ? "Bill {$v->purchaseBill->bill_number}" : 'Departmental Requisition') }}</div>
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
              <td>
                <span class="fs-xs text-muted">{{ $v->preparer?->name ?? 'Staff' }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($v->status === 'PREPARED' || $v->status === 'DRAFT')
                    <form method="POST" action="{{ route('disbursement.payment-requests.audit', $v->id) }}" onsubmit="return confirm('Audit and verify voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="Internal Audit Verification">
                        <i class="ph ph-magnifying-glass me-1"></i> Audit
                      </button>
                    </form>
                    <form method="POST" action="{{ route('disbursement.payment-requests.void', $v->id) }}" onsubmit="return confirm('Void voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 fs-xs" title="Void Request">
                        <i class="ph ph-x"></i>
                      </button>
                    </form>
                  @elseif($v->status === 'AUDITED' || $v->status === 'APPROVED')
                    <a href="{{ route('disbursement.disbursement-approval') }}" class="btn btn-sm btn-primary py-1 px-2 fs-xs">
                      <i class="ph ph-shield-check me-1"></i> Workstation
                    </a>
                  @else
                    <span class="badge bg-light text-muted border">
                      <i class="ph ph-check-double text-success me-1"></i> Disbursed
                    </span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No disbursement vouchers found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $vouchers->firstItem() ?? 0 }} - {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} Requisitions</span>
      <div>
        {{ $vouchers->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Payment Request -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-receipt me-2 text-primary"></i>Create Payment Request / Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('disbursement.payment-requests.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Disbursing Bank Account <span class="text-danger">*</span></label>
              <select name="bank_account_id" class="form-select form-select-sm" required>
                <option value="">-- Choose Operating Bank --</option>
                @foreach($bankAccounts as $b)
                  <option value="{{ $b->id }}">{{ $b->bank_name }} ({{ $b->account_number }}) - Bal: ₱{{ number_format((float) $b->balance, 2) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select form-select-sm" required>
                <option value="CHECK" selected>Bank Check (Standard)</option>
                <option value="PESONET_EFT">PESONet Electronic Bank Transfer</option>
                <option value="INSTAPAY">InstaPay Real-Time Transfer</option>
                <option value="TELEGRAPHIC_TRANSFER">Telegraphic Transfer (TT / Wire)</option>
                <option value="PETTY_CASH">Petty Cash Voucher</option>
              </select>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee Legal Name <span class="text-danger">*</span></label>
              <input type="text" name="payee_name" class="form-control form-control-sm" placeholder="e.g. Metro Medical Supplies Inc" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Voucher Date <span class="text-danger">*</span></label>
              <input type="date" name="voucher_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Gross Amount (₱) <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" name="gross_amount" class="form-control font-monospace" placeholder="0.00" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Withheld Tax Amount (EWT / 1601-C)</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" name="withheld_tax_amount" class="form-control font-monospace" placeholder="0.00" value="0.00">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Particulars / Payment Purpose</label>
            <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Biomedical equipment quarterly maintenance payment">
          </div>

          <!-- Link to AP Purchase Bill or Payroll Run -->
          <div class="p-3 bg-light rounded-3 mb-3">
            <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-link me-1 text-primary"></i> Link to AP Bill or Payroll (Optional)</h6>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label small text-muted mb-0">Link Purchase Bill</label>
                <select name="purchase_bill_id" class="form-select form-select-sm">
                  <option value="">-- None (Manual Request) --</option>
                  @foreach($openBills as $ob)
                    <option value="{{ $ob->id }}">{{ $ob->bill_number }} - {{ $ob->vendor?->name }} (Due: ₱{{ number_format((float) $ob->balance_due, 2) }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small text-muted mb-0">Link Payroll Run</label>
                <select name="payroll_run_id" class="form-select form-select-sm">
                  <option value="">-- None --</option>
                  @foreach($openPayrolls as $pr)
                    <option value="{{ $pr->id }}">{{ $pr->payroll_run_number }} (Net Pay: ₱{{ number_format((float) $pr->total_net_pay, 2) }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit Payment Voucher</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
