@extends('layouts.app')

@section('title', 'Invoices & Vouchers Hub - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'invoices')

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
          <li class="breadcrumb-item active">Invoices &amp; Vouchers Hub</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Vendor Invoices &amp; Payment Vouchers</h1>
      <p class="text-muted fs-xs mb-0">Review verified supplier bills, track tax deductions (BIR 2307), and prepare payment vouchers ready for management release.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Purchase Bills (3-Way Match)', 'AP Payment Approvals', 'Withholding Tax (2307)']" 
          :tables="['vendor_invoices', 'disbursement_vouchers', 'bir2307_certificates']"
          description="Holds approved vendor liabilities and generates disbursement payment vouchers." 
      />
      <a href="{{ route('ap.purchase-bills') }}" class="btn btn-outline-primary btn-sm">
        <i class="ph ph-plus-circle me-1"></i> Record New Supplier Bill
      </a>
    </div>
  </div>

  <!-- Summary Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Open Bills</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $invoices->total() }} Invoices</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Gross Invoiced</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) $totalBilled, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Pending Settlement</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) $totalPending, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Disbursement Vouchers</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalVouchers }} Vouchers</h4>
      </div>
    </div>
  </div>

  <!-- Invoices Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ap.invoices') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="statusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Bill Status:</label>
          <select id="statusSelect" name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
            <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Unpaid / Pending</option>
            <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Partially Paid</option>
            <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Fully Settled</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search bill #, vendor, invoice..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="voucherTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bill Number &amp; Vendor Invoice</th>
              <th>Vendor Legal Name</th>
              <th>Bill Date</th>
              <th>Due Date</th>
              <th class="text-end">Gross (₱)</th>
              <th class="text-end">Withheld EWT (₱)</th>
              <th class="text-end">Net Payable (₱)</th>
              <th class="text-end">Paid Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $bill)
            @php
              $gross = (float) $bill->total_amount;
              $ewt = (float) $bill->withholding_tax_amount;
              $net = (float) $bill->net_payable_amount;
              $paid = (float) $bill->paid_amount;
              $openBal = (float) $bill->balance_due;

              $statusBadge = match($bill->status) {
                'PAID'     => 'bg-success-subtle text-success',
                'APPROVED' => 'bg-info-subtle text-info',
                'PARTIAL'  => 'bg-warning-subtle text-warning',
                default    => 'bg-secondary-subtle text-secondary',
              };

              $cert = $bill->birCertificate;
              $certData = $cert ? [
                'cert_no'   => $cert->certificate_number,
                'payee'     => $cert->payee_name,
                'tin'       => $cert->payee_tin,
                'atc'       => $cert->atc_code,
                'tax_base'  => '₱' . number_format((float) $cert->tax_base_amount, 2),
                'rate'      => number_format((float) $cert->tax_rate * 100, 1) . '%',
                'tax_withheld' => '₱' . number_format((float) $cert->tax_withheld, 2),
                'period'    => $cert->period_from->format('M d, Y') . ' — ' . $cert->period_to->format('M d, Y'),
              ] : null;
            @endphp
            <tr>
              <td>
                <div class="fw-bold text-primary font-monospace">{{ $bill->bill_number }}</div>
                <div class="fs-xs text-muted">Inv: {{ $bill->vendor_invoice_number }}</div>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $bill->vendor?->name ?? 'Unknown Vendor' }}</div>
                <div class="fs-xs text-muted">TIN: {{ $bill->vendor?->tin ?? 'N/A' }}</div>
              </td>
              <td>{{ $bill->bill_date ? $bill->bill_date->format('M d, Y') : '—' }}</td>
              <td>{{ $bill->due_date ? $bill->due_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace">₱{{ number_format($gross, 2) }}</td>
              <td class="text-end font-monospace text-muted">
                ₱{{ number_format($ewt, 2) }}
                @if($certData)
                  <button type="button" class="btn btn-link p-0 fs-xs text-primary d-block" onclick="openBirCertModal({{ json_encode($certData) }})">
                    <i class="ph ph-certificate"></i> BIR 2307
                  </button>
                @endif
              </td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format($net, 2) }}</td>
              <td class="text-end font-monospace text-success">₱{{ number_format($paid, 2) }}</td>
              <td><span class="badge {{ $statusBadge }}">{{ $bill->status }}</span></td>
              <td class="text-end">
                @if($openBal > 0)
                  <button type="button" class="btn btn-sm btn-primary py-1 px-2 fs-xs" onclick="openPrepareVoucherModal({{ $bill->id }}, '{{ $bill->bill_number }}', '{{ addslashes($bill->vendor?->name ?? '') }}', {{ $openBal }})">
                    <i class="ph ph-plus-circle me-1"></i> Prepare Voucher
                  </button>
                @else
                  <span class="badge bg-light text-muted border">Fully Disbursed</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">No purchase invoices found matching filter criteria.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} Records</span>
      <div>
        {{ $invoices->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Prepare Disbursement Voucher -->
<div class="modal fade" id="prepareVoucherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-credit-card me-2 text-primary"></i>Prepare Disbursement Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ap.invoices.prepare-voucher') }}">
        @csrf
        <input type="hidden" name="purchase_bill_id" id="modalBillId">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Purchase Bill Reference</label>
            <div class="fw-bold font-monospace text-primary fs-6" id="modalBillRef">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted mb-0">Payee Legal Name</label>
            <div class="fw-semibold text-dark" id="modalPayeeName">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Disbursement Bank Account <span class="text-danger">*</span></label>
            <select name="bank_account_id" class="form-select form-select-sm" required>
              @foreach($bankAccounts as $bank)
                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->name }} ({{ $bank->account_number }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method" class="form-select form-select-sm" required>
              <option value="CHECK">Bank Check</option>
              <option value="PESONET_EFT">PESONet EFT</option>
              <option value="INSTAPAY">InstaPay</option>
              <option value="PETTY_CASH">Petty Cash</option>
              <option value="TELEGRAPHIC_TRANSFER">Telegraphic Transfer</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Amount to Disburse (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" name="amount" id="modalDisburseAmount" class="form-control form-control-sm font-monospace text-end" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Voucher Date</label>
            <input type="date" name="voucher_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit Voucher for Approval</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: BIR Form 2307 Certificate View -->
<div class="modal fade" id="birCertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-light">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-certificate me-2 text-primary"></i>BIR Form 2307 Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="border rounded-3 p-3 bg-light-subtle mb-3">
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">Certificate #</span>
            <span class="font-monospace fw-bold text-dark fs-xs" id="certNo">-</span>
          </div>
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">Payee Name</span>
            <span class="fw-semibold text-dark fs-xs" id="certPayee">-</span>
          </div>
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">Payee TIN</span>
            <span class="font-monospace text-dark fs-xs" id="certTin">-</span>
          </div>
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">ATC Code</span>
            <span class="badge bg-primary-subtle text-primary font-monospace" id="certAtc">-</span>
          </div>
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">Tax Base (Gross)</span>
            <span class="font-monospace text-dark fs-xs" id="certTaxBase">-</span>
          </div>
          <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span class="text-muted fs-xs">Applicable Tax Rate</span>
            <span class="font-monospace text-dark fs-xs" id="certRate">-</span>
          </div>
          <div class="d-flex justify-content-between pt-1">
            <span class="text-muted fs-xs fw-bold">Total Tax Withheld</span>
            <span class="font-monospace text-danger fw-bold fs-6" id="certWithheld">-</span>
          </div>
        </div>
      </div>
      <div class="modal-footer border-top">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openPrepareVoucherModal(billId, billRef, payeeName, openBalance) {
  document.getElementById('modalBillId').value = billId;
  document.getElementById('modalBillRef').textContent = billRef;
  document.getElementById('modalPayeeName').textContent = payeeName;
  document.getElementById('modalDisburseAmount').value = parseFloat(openBalance).toFixed(2);

  const modalEl = document.getElementById('prepareVoucherModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

function openBirCertModal(data) {
  if (!data) return;
  document.getElementById('certNo').textContent = data.cert_no;
  document.getElementById('certPayee').textContent = data.payee;
  document.getElementById('certTin').textContent = data.tin;
  document.getElementById('certAtc').textContent = data.atc;
  document.getElementById('certTaxBase').textContent = data.tax_base;
  document.getElementById('certRate').textContent = data.rate;
  document.getElementById('certWithheld').textContent = data.tax_withheld;

  const modalEl = document.getElementById('birCertModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}
</script>
@endpush
