@extends('layouts.app')

@section('title', 'Purchase Bills & 3-Way Matching - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'purchase-bills')

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
          <li class="breadcrumb-item active">Purchase Bills &amp; 3-Way Matching</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Supplier Bills &amp; 3-Way Verification</h1>
      <p class="text-muted fs-xs mb-0">Cross-check incoming supplier invoices against approved Purchase Orders (PSM) and actual delivered items (SWS Goods Receipts) before approving payment.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['PSM (Purchase Orders)', 'SWS (Goods Receipt Notes)']" 
          glImpact="DR 1200 (Inventory/Expense) / CR 2010 (AP Vendors) + CR 2110 (EWT 2307)" 
          description="Performs 3-Way matching across Purchase Orders, Goods Delivery Notes, and Supplier Invoices." 
      />
      <button id="btnLogBill" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createBillModal">
        <i class="ph ph-plus me-1"></i> Record New Supplier Bill
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Purchase Bills</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $bills->total() }} Bills</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Unpaid Balance</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-clock-afternoon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) $totalUnpaid, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Paid (Settled)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) $totalPaid, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending 3-Way Approvals</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning">{{ $pendingCount }} Bills</h4>
      </div>
    </div>
  </div>

  <!-- 3-Way Matching Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ap.purchase-bills') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <label for="matchStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-git-merge me-1"></i> Match Status:</label>
            <select id="matchStatusSelect" name="match_status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
              <option value="" {{ request('match_status') === null || request('match_status') === '' ? 'selected' : '' }}>All Match Statuses</option>
              <option value="MATCHED" {{ request('match_status') === 'MATCHED' ? 'selected' : '' }}>Matched (3-Way OK)</option>
              <option value="PRICE_MISMATCH" {{ request('match_status') === 'PRICE_MISMATCH' ? 'selected' : '' }}>Price Variance</option>
              <option value="QTY_MISMATCH" {{ request('match_status') === 'QTY_MISMATCH' ? 'selected' : '' }}>Quantity Variance</option>
              <option value="OVER_BILLED" {{ request('match_status') === 'OVER_BILLED' ? 'selected' : '' }}>Over-Billed</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <label for="billStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Bill Status:</label>
            <select id="billStatusSelect" name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
              <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
              <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
              <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Unpaid</option>
              <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Partial</option>
              <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Paid</option>
            </select>
          </div>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search bill #, PO, GRN, supplier..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="purchaseBillTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bill # &amp; Vendor Invoice</th>
              <th>Supplier Legal Name</th>
              <th>PO Amount</th>
              <th>GRN Amount</th>
              <th class="text-end">Invoice Total</th>
              <th class="text-end">Variance</th>
              <th>3-Way Status</th>
              <th>Bill Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bills as $inv)
            @php
              $m = $inv->threeWayMatch;
              $poAmt = $m ? (float) $m->po_amount : (float) $inv->total_amount;
              $grnAmt = $m ? (float) $m->grn_amount : (float) $inv->total_amount;
              $invAmt = (float) $inv->total_amount;
              $variance = $m ? (float) $m->price_variance : 0.00;
              $matchStatus = $m?->match_status ?? 'MATCHED';

              $matchBadge = match($matchStatus) {
                'MATCHED'        => 'bg-success-subtle text-success',
                'PRICE_MISMATCH', 'OVER_BILLED' => 'bg-danger-subtle text-danger',
                'QTY_MISMATCH'   => 'bg-warning-subtle text-warning',
                default          => 'bg-secondary-subtle text-secondary',
              };

              $billBadge = match($inv->status) {
                'PAID'     => 'bg-success-subtle text-success',
                'APPROVED' => 'bg-info-subtle text-info',
                'PARTIAL'  => 'bg-warning-subtle text-warning',
                default    => 'bg-secondary-subtle text-secondary',
              };
            @endphp
            <tr>
              <td>
                <div class="font-monospace fw-bold text-primary">{{ $inv->bill_number }}</div>
                <div class="fs-xs text-muted">Inv: {{ $inv->vendor_invoice_number }}</div>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $inv->vendor?->name ?? 'Unknown Vendor' }}</div>
                <div class="fs-xs text-muted font-monospace">PO: {{ $m?->po_number ?? 'N/A' }} | GRN: {{ $m?->grn_number ?? 'N/A' }}</div>
              </td>
              <td class="font-monospace fs-xs">₱{{ number_format($poAmt, 2) }}</td>
              <td class="font-monospace fs-xs">₱{{ number_format($grnAmt, 2) }}</td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format($invAmt, 2) }}</td>
              <td class="text-end font-monospace {{ $variance != 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                ₱{{ number_format($variance, 2) }}
              </td>
              <td>
                <span class="badge {{ $matchBadge }}">
                  <i class="ph {{ $matchStatus === 'MATCHED' ? 'ph-check-circle' : 'ph-warning-circle' }} me-1"></i>
                  {{ str_replace('_', ' ', $matchStatus) }}
                </span>
              </td>
              <td><span class="badge {{ $billBadge }}">{{ $inv->status }}</span></td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($inv->status !== 'APPROVED' && $inv->status !== 'PAID')
                    <form method="POST" action="{{ route('ap.purchase-bills.approve', $inv->id) }}" onsubmit="return confirm('Authorize 3-Way matching approval for {{ $inv->bill_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success py-1 px-2 fs-xs" title="Approve 3-Way Match">
                        <i class="ph ph-check-circle me-1"></i> Approve
                      </button>
                    </form>
                  @else
                    <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="View Invoices Hub">
                      <i class="ph ph-receipt me-1"></i> Voucher
                    </a>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No purchase bills found matching current filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $bills->firstItem() ?? 0 }} - {{ $bills->lastItem() ?? 0 }} of {{ $bills->total() }} Bills</span>
      <div>
        {{ $bills->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ingest Purchase Bill with 3-Way Matching -->
<div class="modal fade" id="createBillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-file-plus me-2 text-primary"></i>Ingest Purchase Bill &amp; 3-Way Match</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ap.purchase-bills.store') }}">
        @csrf
        <div class="modal-body p-4">
          <!-- Master Header Row -->
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Supplier / Vendor <span class="text-danger">*</span></label>
              <select name="vendor_id" class="form-select form-select-sm" required>
                <option value="">-- Select Vendor --</option>
                @foreach($vendors as $v)
                  <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->code }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Bill Date <span class="text-danger">*</span></label>
              <input type="date" name="bill_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Payment Due Date <span class="text-danger">*</span></label>
              <input type="date" name="due_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
            </div>
          </div>

          <!-- 3-Way Reference Controls Row -->
          <div class="row g-3 mb-4 p-3 bg-light rounded-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Purchase Order (PO #)</label>
              <input type="text" name="po_number" class="form-control form-control-sm font-monospace" placeholder="e.g. PO-2026-0044" value="PO-{{ date('Ymd') }}-{{ rand(100,999) }}">
              <div class="mt-1">
                <input type="number" step="0.01" min="0" name="po_amount" id="modalPoAmount" class="form-control form-control-sm font-monospace text-end" placeholder="PO Amount ₱">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Goods Receipt Note (GRN #)</label>
              <input type="text" name="grn_number" class="form-control form-control-sm font-monospace" placeholder="e.g. GRN-2026-0092" value="GRN-{{ date('Ymd') }}-{{ rand(100,999) }}">
              <div class="mt-1">
                <input type="number" step="0.01" min="0" name="grn_amount" id="modalGrnAmount" class="form-control form-control-sm font-monospace text-end" placeholder="GRN Amount ₱">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Vendor Sales Invoice # <span class="text-danger">*</span></label>
              <input type="text" name="vendor_invoice_number" class="form-control form-control-sm font-monospace" placeholder="e.g. SI-88992211" required>
            </div>
          </div>

          <!-- Line Items Table -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 small text-uppercase"><i class="ph ph-list-dashes me-1 text-primary"></i>Bill Item Breakdown &amp; Tax Withholding</h6>
            <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick="addBillLineItem()"><i class="ph ph-plus me-1"></i> Add Line</button>
          </div>

          <div class="table-responsive border rounded-3 mb-3">
            <table class="table table-sm align-middle mb-0" id="billItemsTable">
              <thead class="table-light fs-xs">
                <tr>
                  <th style="width: 25%;">Item Description</th>
                  <th style="width: 20%;">Expense Classification</th>
                  <th style="width: 15%;">BIR ATC Withholding</th>
                  <th style="width: 12%;" class="text-end">Qty</th>
                  <th style="width: 13%;" class="text-end">Unit Price (₱)</th>
                  <th style="width: 15%;" class="text-end">Gross (₱)</th>
                </tr>
              </thead>
              <tbody id="billItemsTbody">
                <tr>
                  <td><input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Item description..." required></td>
                  <td>
                    <select name="items[0][expense_type]" class="form-select form-select-sm">
                      <option value="GOODS_INVENTORY">Goods / Inventory</option>
                      <option value="SERVICES_MAINTENANCE">Services & Maintenance</option>
                      <option value="DOCTOR_PROFESSIONAL_FEE">Doctor PF</option>
                      <option value="CAPEX_EQUIPMENT">Capex Equipment</option>
                      <option value="UTILITIES">Utilities</option>
                    </select>
                  </td>
                  <td>
                    <select name="items[0][atc_code]" class="form-select form-select-sm item-atc" onchange="recalculateBillTotals()">
                      <option value="WI158">WI158 (Goods 1%)</option>
                      <option value="WI160">WI160 (Services 2%)</option>
                      <option value="WI010">WI010 (Doctor PF 10%)</option>
                    </select>
                  </td>
                  <td><input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm text-end item-qty" value="1" oninput="recalculateBillTotals()" required></td>
                  <td><input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm text-end item-price" value="0.00" oninput="recalculateBillTotals()" required></td>
                  <td class="text-end font-monospace fw-bold item-line-gross">₱0.00</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Total Calculation Footer -->
          <div class="row justify-content-end">
            <div class="col-md-5">
              <div class="bg-light p-3 rounded-3 fs-xs">
                <div class="d-flex justify-content-between mb-1">
                  <span>Total Gross Invoiced:</span>
                  <span class="font-monospace fw-bold" id="lblTotalGross">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-1 text-muted">
                  <span>Estimated BIR 2307 EWT:</span>
                  <span class="font-monospace text-danger" id="lblTotalEwt">₱0.00</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                  <span class="fw-bold fs-6">Net Accounts Payable:</span>
                  <span class="font-monospace fw-bold fs-6 text-primary" id="lblTotalNet">₱0.00</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Purchase Bill &amp; 3-Way Match</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let billLineIndex = 1;

function addBillLineItem() {
  const tbody = document.getElementById('billItemsTbody');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input type="text" name="items[${billLineIndex}][description]" class="form-control form-control-sm" placeholder="Item description..." required></td>
    <td>
      <select name="items[${billLineIndex}][expense_type]" class="form-select form-select-sm">
        <option value="GOODS_INVENTORY">Goods / Inventory</option>
        <option value="SERVICES_MAINTENANCE">Services & Maintenance</option>
        <option value="DOCTOR_PROFESSIONAL_FEE">Doctor PF</option>
        <option value="CAPEX_EQUIPMENT">Capex Equipment</option>
        <option value="UTILITIES">Utilities</option>
      </select>
    </td>
    <td>
      <select name="items[${billLineIndex}][atc_code]" class="form-select form-select-sm item-atc" onchange="recalculateBillTotals()">
        <option value="WI158">WI158 (Goods 1%)</option>
        <option value="WI160">WI160 (Services 2%)</option>
        <option value="WI010">WI010 (Doctor PF 10%)</option>
      </select>
    </td>
    <td><input type="number" step="1" min="1" name="items[${billLineIndex}][quantity]" class="form-control form-control-sm text-end item-qty" value="1" oninput="recalculateBillTotals()" required></td>
    <td><input type="number" step="0.01" min="0" name="items[${billLineIndex}][unit_price]" class="form-control form-control-sm text-end item-price" value="0.00" oninput="recalculateBillTotals()" required></td>
    <td class="text-end font-monospace fw-bold item-line-gross">₱0.00</td>
  `;
  tbody.appendChild(row);
  billLineIndex++;
}

function recalculateBillTotals() {
  const rows = document.querySelectorAll('#billItemsTbody tr');
  let totalGross = 0;
  let totalEwt = 0;

  rows.forEach(row => {
    const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
    const price = parseFloat(row.querySelector('.item-price')?.value || 0);
    const atc = row.querySelector('.item-atc')?.value || 'WI158';

    const gross = qty * price;
    row.querySelector('.item-line-gross').textContent = '₱' + gross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    let rate = 0.01;
    if (atc === 'WI160') rate = 0.02;
    if (atc === 'WI010') rate = 0.10;

    const ewt = gross * rate;

    totalGross += gross;
    totalEwt += ewt;
  });

  const totalNet = totalGross - totalEwt;

  document.getElementById('lblTotalGross').textContent = '₱' + totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblTotalEwt').textContent = '₱' + totalEwt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblTotalNet').textContent = '₱' + totalNet.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const poInput = document.getElementById('modalPoAmount');
  const grnInput = document.getElementById('modalGrnAmount');
  if (poInput && (!poInput.value || poInput.value == '0.00')) poInput.value = totalGross.toFixed(2);
  if (grnInput && (!grnInput.value || grnInput.value == '0.00')) grnInput.value = totalGross.toFixed(2);
}
</script>
@endpush
