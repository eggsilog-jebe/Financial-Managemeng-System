@extends('layouts.app')

@section('title', 'Supplier Payment Approvals & Disbursement - Accounts Payable | FMS')
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
          <li class="breadcrumb-item active">Payment Approvals</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Supplier Payment Approvals &amp; Release</h1>
      <p class="text-muted fs-xs mb-0">Review verified disbursement vouchers, authorize supplier payments, and release checks or electronic bank transfers (EFT).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoices & Vouchers', 'Bank Accounts', 'General Ledger']" 
          :tables="['disbursement_vouchers', 'vendor_invoices', 'bank_accounts', 'journal_entries']"
          glImpact="DR 2010 AP Vendors / CR 1020 Cash in Bank + CR 2110 EWT Payable"
          description="Executive workstation to authorize supplier vouchers and execute bank check/EFT payouts." 
      />
      <a href="{{ route('ap.invoices') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-receipt me-1"></i> Invoices &amp; Vouchers Hub
      </a>
      <!-- 1. Header Utility Buttons -->
      <a href="{{ route('ap.payment-approvals.export-bank-batch', ['status' => request('status') ?? 'ALL']) }}" class="btn btn-outline-primary btn-sm" id="btn-export-bank-batch">
        <i class="ph ph-export me-1"></i> 📤 Export Bank EFT Batch
      </a>
      <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-bulk-approve" disabled onclick="bulkApproveVouchers()">
        <i class="ph ph-check-circle me-1"></i> ✓ Authorize Selected Vouchers (<span id="selected-count">0</span>)
      </button>
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
        <span class="fs-xs text-muted">Awaiting executive authorization</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Approved Vouchers (Ready for Release)</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) $totalApproved, 2) }}</h4>
        <span class="fs-xs text-muted">Authorized for check/EFT issuance</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Released / Disbursed</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $totalReleased, 2) }}</h4>
        <span class="fs-xs text-muted">Settled and posted to General Ledger</span>
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
            <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Pending Approval (Draft)</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved (Ready to Release)</option>
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
        <table class="table table-hover align-middle mb-0 fs-xs" id="apApprovalsTable">
          <thead class="table-light">
            <tr>
              <!-- 2. Multi-Select Column Header -->
              <th style="width: 40px;" class="text-center">
                <input type="checkbox" id="selectAllApprovals" class="form-check-input" onchange="toggleSelectAllApprovals(this)">
              </th>
              <th>Voucher Number</th>
              <th>Payee Legal Name &amp; Bill Ref</th>
              <th>Bank Account</th>
              <th>Payment Method</th>
              <th>Voucher Date</th>
              <th class="text-end">Disbursed Amount</th>
              <th>Status</th>
              <!-- 2. ACTIONS Column Header -->
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vouchers as $v)
            @php
              $amt = (float) $v->net_disbursed_amount;
              $gross = (float) $v->gross_amount;
              $taxWithheld = (float) $v->withheld_tax_amount;

              $statusBadge = match($v->status) {
                'RELEASED' => 'bg-success-subtle text-success border border-success-subtle',
                'APPROVED' => 'bg-info-subtle text-info border border-info-subtle',
                'CANCELLED'=> 'bg-danger-subtle text-danger border border-danger-subtle',
                default    => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
              };

              $vendor = $v->purchaseBill?->vendor;
              $match = $v->purchaseBill?->threeWayMatch;

              $approvalData = [
                'id'                   => $v->id,
                'voucher_number'       => $v->voucher_number,
                'payee_name'           => $v->payee_name,
                'payee_tin'            => $vendor?->tin ?? '—',
                'registered_address'   => $vendor?->registered_address ?? '—',
                'bank_name'            => $vendor?->bank_name ?? '—',
                'bank_account_number'  => $vendor?->bank_account_number ?? '—',
                'bank_account_name'    => $vendor?->bank_account_name ?? $v->payee_name,
                'payment_method'       => $v->payment_method,
                'check_or_eft_ref'     => $v->check_or_eft_ref,
                'voucher_date'         => $v->voucher_date ? $v->voucher_date->format('M d, Y') : '—',
                'gross_amount'         => '₱' . number_format($gross, 2),
                'ewt_amount'           => '₱' . number_format($taxWithheld, 2),
                'net_amount'           => '₱' . number_format($amt, 2),
                'status'               => $v->status,
                'source_bank_id'       => $v->bank_account_id,
                'source_bank_name'     => $v->bankAccount?->name ?? 'Operating Account',
                'bill_number'          => $v->purchaseBill?->bill_number ?? 'Manual Request',
                'vendor_invoice'       => $match?->vendor_invoice_number ?? $v->purchaseBill?->vendor_invoice_number ?? '—',
                'po_number'            => $match?->po_number ?? '—',
                'grn_number'           => $match?->grn_number ?? '—',
                'approver_name'        => $v->approver?->name ?? 'Pending Authorization',
              ];
            @endphp
            <tr>
              <!-- 2. Row Multi-Select Checkbox -->
              <td class="text-center">
                @if($v->status === 'DRAFT')
                  <input type="checkbox" class="form-check-input approval-checkbox" value="{{ $v->id }}" data-status="{{ $v->status }}" onchange="updateSelectedCount()">
                @else
                  <input type="checkbox" class="form-check-input" disabled>
                @endif
              </td>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $v->voucher_number }}</span>
                @if($v->check_or_eft_ref)
                  <div class="fs-xs text-muted font-monospace">Ref: {{ $v->check_or_eft_ref }}</div>
                @endif
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $v->payee_name }}</div>
                <div class="fs-xs text-muted font-monospace">Bill: {{ $v->purchaseBill?->bill_number ?? 'Manual Request' }}</div>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $v->bankAccount?->name ?? 'Operating Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $v->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', $v->payment_method) }}</span>
              </td>
              <td>{{ $v->voucher_date ? $v->voucher_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold fs-6 text-dark">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">
                  <i class="ph {{ $v->status === 'RELEASED' ? 'ph-check-circle' : ($v->status === 'APPROVED' ? 'ph-stamp' : 'ph-clock') }} me-1"></i>
                  {{ $v->status === 'DRAFT' ? 'PENDING APPROVAL' : $v->status }}
                </span>
              </td>
              <!-- 2. Contextual Action Buttons -->
              <td class="text-end">
                <div class="d-flex justify-content-end align-items-center gap-1">
                  @if($v->status === 'DRAFT')
                    <!-- For status === 'DRAFT' (Pending Approval): Approve, Reject, Inspect -->
                    <form method="POST" action="{{ route('ap.payment-approvals.approve', $v->id) }}" class="d-inline" onsubmit="return confirm('Authorize payment approval for voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success py-1 px-2 fs-xs" title="Executive Approval">
                        <i class="ph ph-check me-1"></i> Approve
                      </button>
                    </form>
                    <form method="POST" action="{{ route('ap.payment-approvals.reject', $v->id) }}" class="d-inline" onsubmit="return confirm('Reject and cancel disbursement voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 fs-xs" title="Reject Voucher">
                        <i class="ph ph-x me-1"></i> Reject
                      </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" title="Inspect Voucher" onclick="openApprovalDrawer({{ json_encode($approvalData) }})">
                      <i class="ph ph-eye me-1"></i> Inspect
                    </button>
                  @elseif($v->status === 'APPROVED')
                    <!-- For status === 'APPROVED': Release & Disburse, Print Check / 2307 -->
                    <button type="button" class="btn btn-sm text-white py-1 px-2 fs-xs fw-semibold" style="background-color: #0d9488;" title="Release Payment & Issue Check" onclick="openApprovalDrawer({{ json_encode($approvalData) }}, true)">
                      <i class="ph ph-paper-plane-tilt me-1"></i> Release &amp; Disburse
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" title="Print Check / BIR 2307" onclick="openPrintCheckModal({{ json_encode($approvalData) }})">
                      <i class="ph ph-printer me-1"></i> Print Check / 2307
                    </button>
                  @else
                    <!-- For status === 'RELEASED': View Receipt / GL -->
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" onclick="openApprovalDrawer({{ json_encode($approvalData) }})">
                      <i class="ph ph-receipt me-1"></i> View Receipt / GL
                    </button>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="ph ph-credit-card fs-1 d-block mb-2 text-secondary"></i>
                No disbursement vouchers found matching filter.
              </td>
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

<!-- ========================================================= -->
<!-- 3. Slide-Over Drawer: Approval Review & Disbursement Release -->
<!-- ========================================================= -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="approvalReviewDrawer" style="width: 860px; max-width: 95vw;" aria-labelledby="approvalReviewDrawerLabel">
  <div class="offcanvas-header border-bottom bg-light py-3 px-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-1 fs-xs" id="drawerVoucherNum">DV-0000</span>
        <span class="badge" id="drawerStatusBadge">PENDING APPROVAL</span>
      </div>
      <h5 class="offcanvas-title font-weight-bold text-dark mb-0" id="approvalReviewDrawerLabel">Supplier Disbursement Voucher Authorization &amp; Release</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-4 bg-light-subtle">
    <!-- Voucher Header Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h5 class="fw-bold text-dark mb-1 fs-6" id="drawerPayeeName">Supplier Legal Name</h5>
          <span class="fs-xs font-monospace text-muted d-block" id="drawerPayeeTin">TIN: -</span>
          <span class="fs-xs text-muted d-block mt-1" id="drawerPayeeAddress"><i class="ph ph-map-pin me-1"></i>-</span>
        </div>
        <div class="text-end">
          <span class="badge bg-light text-dark border font-monospace mb-1 d-block" id="drawerPayMethod">CHECK</span>
          <span class="fs-xs text-muted" id="drawerVoucherDate">Jan 20, 2026</span>
        </div>
      </div>

      <div class="row g-2 pt-2 border-top fs-xs">
        <div class="col-md-6">
          <span class="text-muted d-block">Settlement Destination Bank</span>
          <strong class="text-dark" id="drawerDestBank">—</strong>
        </div>
        <div class="col-md-6">
          <span class="text-muted d-block">Bank Account Number</span>
          <strong class="text-primary font-monospace" id="drawerDestAcc">—</strong>
        </div>
      </div>
    </div>

    <!-- Matched Documents Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <span class="fs-xs text-uppercase fw-bold text-primary d-flex align-items-center gap-1 mb-2 pb-1 border-bottom">
        <i class="ph ph-link fs-5"></i> 3-Way Matched Audit Trail
      </span>
      <div class="row g-2 fs-xs">
        <div class="col-md-3">
          <span class="text-muted d-block">Purchase Bill:</span>
          <strong class="font-monospace text-dark" id="drawerBillRef">-</strong>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Supplier Invoice #:</span>
          <strong class="font-monospace text-dark" id="drawerInvRef">-</strong>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Purchase Order (PSM):</span>
          <strong class="font-monospace text-dark" id="drawerPoRef">-</strong>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Goods Receipt (SWS):</span>
          <strong class="font-monospace text-dark" id="drawerGrnRef">-</strong>
        </div>
      </div>
    </div>

    <!-- Accounting & Tax Distribution Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1 mb-2 pb-1 border-bottom">
        <i class="ph ph-scales fs-5 text-secondary"></i> Accounting &amp; BIR 2307 Tax Distribution
      </span>

      <div class="bg-light p-3 rounded-3 fs-xs mb-3">
        <div class="d-flex justify-content-between mb-1">
          <span>Gross Authorized Voucher Amount:</span>
          <span class="font-monospace fw-bold" id="drawerGrossAmt">₱0.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1 text-danger">
          <span>Less: BIR Form 2307 EWT Withheld:</span>
          <span class="font-monospace" id="drawerEwtAmt">-₱0.00</span>
        </div>
        <div class="d-flex justify-content-between border-top pt-2 mt-2">
          <span class="fw-bold fs-6">Net Payment Disbursed:</span>
          <span class="font-monospace fw-bold fs-6 text-success" id="drawerNetAmt">₱0.00</span>
        </div>
      </div>

      <!-- General Ledger Double-Entry Impact -->
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Account Code &amp; Title</th>
              <th class="text-end">Debit (₱)</th>
              <th class="text-end">Credit (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace fw-bold text-primary">2010</span> - Accounts Payable (Trade)</td>
              <td class="text-end font-monospace fw-bold text-dark" id="glDrAp">₱0.00</td>
              <td class="text-end font-monospace text-muted">—</td>
            </tr>
            <tr>
              <td><span class="font-monospace fw-bold text-primary" id="glBankCode">1020</span> - <span id="glBankName">Cash in Bank</span></td>
              <td class="text-end font-monospace text-muted">—</td>
              <td class="text-end font-monospace fw-bold text-dark" id="glCrBank">₱0.00</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Release Form / Certification Form -->
    <div id="releaseSectionCard" class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <h6 class="fw-bold text-dark mb-2 fs-6 d-flex align-items-center gap-1">
        <i class="ph ph-bank fs-5 text-teal" style="color: #0d9488;"></i> Disbursement Fund Source &amp; Issuance
      </h6>

      <form id="drawerReleaseForm" method="POST" action="">
        @csrf
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Disbursement Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" id="drawerSourceBank" class="form-select form-select-sm" required>
              @foreach($bankAccounts as $ba)
                <option value="{{ $ba->id }}">{{ $ba->gl_code }} - {{ $ba->bank_name }} ({{ $ba->name }}) - ₱{{ number_format((float) $ba->balance, 2) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Check / EFT Settlement Number</label>
            <input type="text" name="check_number" id="drawerCheckNumber" class="form-control form-control-sm font-monospace" placeholder="e.g. CHK-2026-9901">
          </div>
        </div>

        <div class="p-3 bg-light rounded-3 border mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="authCertification" required>
            <label class="form-check-label small fw-semibold text-dark" for="authCertification">
              I certify that these goods/services were verified and comply with hospital financial controls, BIR withholding rules, and GAAP standards.
            </label>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2" id="drawerFormButtons">
          <!-- Dynamic action buttons -->
        </div>
      </form>
    </div>
  </div>

  <div class="offcanvas-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="offcanvas">Close</button>
    <div id="drawerBottomActions">
      <!-- Dynamic bottom buttons -->
    </div>
  </div>
</div>

<!-- Modal: Quick Release / Issue Check -->
<div class="modal fade" id="releaseVoucherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-success-subtle py-3 px-4">
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
            <div class="fw-bold font-monospace text-success fs-5" id="relAmount">₱0.00</div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Disbursement Bank Account</label>
            <select name="bank_account_id" class="form-select form-select-sm">
              @foreach($bankAccounts as $ba)
                <option value="{{ $ba->id }}">{{ $ba->gl_code }} - {{ $ba->bank_name }} ({{ $ba->name }})</option>
              @endforeach
            </select>
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

<!-- Bulk Approval Hidden Form -->
<form id="bulkApproveForm" method="POST" action="{{ route('ap.payment-approvals.bulk-approve') }}" class="d-none">
  @csrf
  <div id="bulkVoucherInputs"></div>
</form>
@endsection

@push('scripts')
<script>
function toggleSelectAllApprovals(masterCheckbox) {
  const checkboxes = document.querySelectorAll('.approval-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = masterCheckbox.checked;
  });
  updateSelectedCount();
}

function updateSelectedCount() {
  const selected = document.querySelectorAll('.approval-checkbox:checked');
  const count = selected.length;
  document.getElementById('selected-count').textContent = count;
  const btn = document.getElementById('btn-bulk-approve');
  if (btn) {
    btn.disabled = count === 0;
  }
}

function bulkApproveVouchers() {
  const selected = document.querySelectorAll('.approval-checkbox:checked');
  if (selected.length === 0) return;

  if (confirm(`Authorize executive payment approval for ${selected.length} selected disbursement voucher(s)?`)) {
    const container = document.getElementById('bulkVoucherInputs');
    container.innerHTML = '';
    selected.forEach(cb => {
      container.innerHTML += `<input type="hidden" name="voucher_ids[]" value="${cb.value}">`;
    });
    document.getElementById('bulkApproveForm').submit();
  }
}

function openApprovalDrawer(data, openForRelease = false) {
  if (!data) return;

  document.getElementById('drawerVoucherNum').textContent = data.voucher_number;
  document.getElementById('drawerPayeeName').textContent = data.payee_name;
  document.getElementById('drawerPayeeTin').textContent = 'TIN: ' + data.payee_tin;
  document.getElementById('drawerPayeeAddress').innerHTML = '<i class="ph ph-map-pin me-1"></i>' + data.registered_address;
  document.getElementById('drawerPayMethod').textContent = data.payment_method;
  document.getElementById('drawerVoucherDate').textContent = data.voucher_date;

  document.getElementById('drawerDestBank').textContent = data.bank_name;
  document.getElementById('drawerDestAcc').textContent = data.bank_account_number;

  document.getElementById('drawerBillRef').textContent = data.bill_number;
  document.getElementById('drawerInvRef').textContent = data.vendor_invoice;
  document.getElementById('drawerPoRef').textContent = data.po_number;
  document.getElementById('drawerGrnRef').textContent = data.grn_number;

  document.getElementById('drawerGrossAmt').textContent = data.gross_amount;
  document.getElementById('drawerEwtAmt').textContent = '-' + data.ewt_amount;
  document.getElementById('drawerNetAmt').textContent = data.net_amount;

  document.getElementById('glDrAp').textContent = data.net_amount;
  document.getElementById('glCrBank').textContent = data.net_amount;
  document.getElementById('glBankName').textContent = data.source_bank_name;

  const statusBadge = document.getElementById('drawerStatusBadge');
  statusBadge.textContent = data.status === 'DRAFT' ? 'PENDING APPROVAL' : data.status;
  if (data.status === 'RELEASED') {
    statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
  } else if (data.status === 'APPROVED') {
    statusBadge.className = 'badge bg-info-subtle text-info border border-info-subtle';
  } else {
    statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
  }

  const form = document.getElementById('drawerReleaseForm');
  form.action = `/accounts-payable/payment-approvals/${data.id}/release`;

  if (data.source_bank_id) {
    document.getElementById('drawerSourceBank').value = data.source_bank_id;
  }
  document.getElementById('drawerCheckNumber').value = data.check_or_eft_ref || ('CHK-' + Math.floor(100000 + Math.random() * 900000));

  const formButtons = document.getElementById('drawerFormButtons');
  const bottomActions = document.getElementById('drawerBottomActions');

  if (data.status === 'APPROVED' || openForRelease) {
    document.getElementById('releaseSectionCard').style.display = 'block';
    formButtons.innerHTML = `
      <button type="submit" class="btn btn-sm text-white px-4 fw-semibold" style="background-color: #0d9488;">
        <i class="ph ph-paper-plane-tilt me-1"></i> Release &amp; Disburse Payment
      </button>
    `;
    bottomActions.innerHTML = '';
  } else if (data.status === 'DRAFT') {
    document.getElementById('releaseSectionCard').style.display = 'none';
    formButtons.innerHTML = '';
    bottomActions.innerHTML = `
      <form method="POST" action="/accounts-payable/payment-approvals/${data.id}/approve" class="d-inline">
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
        <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold">
          <i class="ph ph-check me-1"></i> Authorize &amp; Approve Voucher
        </button>
      </form>
    `;
  } else {
    document.getElementById('releaseSectionCard').style.display = 'none';
    formButtons.innerHTML = '';
    bottomActions.innerHTML = `
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Official Voucher Receipt
      </button>
    `;
  }

  const drawerEl = document.getElementById('approvalReviewDrawer');
  if (drawerEl && window.bootstrap) {
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    drawerInstance.show();
  }
}

function openPrintCheckModal(data) {
  if (confirm(`Print Check Register and BIR 2307 Certificate for Voucher [${data.voucher_number}] to ${data.payee_name}?`)) {
    window.print();
  }
}
</script>
@endpush
