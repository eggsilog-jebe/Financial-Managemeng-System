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
      <!-- Header Actions: Sync External PSM/SWS & Record Supplier Bill -->
      <form method="POST" action="{{ route('ap.purchase-bills.sync') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-teal btn-sm" style="color: #0d9488; border-color: #0d9488;" title="Sync latest procurement purchase orders and warehouse receipts">
          <i class="ph ph-arrows-clockwise me-1"></i> Sync External PSM/SWS Data
        </button>
      </form>
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
        <span class="fs-xs text-muted">Audited Procurement Invoices</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Unpaid Balance</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-clock-afternoon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) $totalUnpaid, 2) }}</h4>
        <span class="fs-xs text-muted">Open Accounts Payable</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Paid (Settled)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) $totalPaid, 2) }}</h4>
        <span class="fs-xs text-muted">Disbursed supplier payouts</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending 3-Way Approvals</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning">{{ $pendingCount }} Bills</h4>
        <span class="fs-xs text-muted">Awaiting reconciliation approval</span>
      </div>
    </div>
  </div>

  <!-- 3-Way Matching Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ap.purchase-bills') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3">
          <!-- 2. Variance Status Quick-Filter -->
          <div class="d-flex align-items-center gap-2">
            <label for="varianceStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-git-merge me-1"></i> Variance Status:</label>
            <select id="varianceStatusSelect" name="variance_status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
              <option value="" {{ request('variance_status') === null || request('variance_status') === '' ? 'selected' : '' }}>All Variance Statuses</option>
              <option value="MATCHED" {{ request('variance_status') === 'MATCHED' ? 'selected' : '' }}>Matched (0.00 Variance)</option>
              <option value="VARIANCE" {{ request('variance_status') === 'VARIANCE' ? 'selected' : '' }}>Discrepancy / Variance</option>
              <option value="PENDING_GRN" {{ request('variance_status') === 'PENDING_GRN' ? 'selected' : '' }}>Pending GRN</option>
            </select>
          </div>

          <!-- Bill Status Filter -->
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

        <div class="d-flex align-items-center gap-2">
          <div class="search-box" style="width: 280px;">
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search bill #, PO, GRN, supplier..." value="{{ request('search') }}">
          </div>
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-magnifying-glass me-1"></i> Filter</button>
          @if(request()->hasAny(['variance_status', 'status', 'search']))
            <a href="{{ route('ap.purchase-bills') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="ph ph-x"></i></a>
          @endif
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

              $billBadge = match($inv->status) {
                'PAID'     => 'bg-success-subtle text-success border border-success-subtle',
                'APPROVED' => 'bg-info-subtle text-info border border-info-subtle',
                'PARTIAL'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                default    => 'bg-secondary-subtle text-secondary border',
              };

              $inspectionData = [
                'id'                 => $inv->id,
                'bill_number'        => $inv->bill_number,
                'vendor_invoice'     => $inv->vendor_invoice_number,
                'vendor_name'        => $inv->vendor?->name ?? 'Unknown Vendor',
                'vendor_tin'         => $inv->vendor?->tin ?? 'N/A',
                'bill_date'          => $inv->bill_date?->format('M d, Y') ?? '—',
                'due_date'           => $inv->due_date?->format('M d, Y') ?? '—',
                'po_number'          => $m?->po_number ?? '—',
                'grn_number'         => $m?->grn_number ?? '—',
                'po_amount'          => '₱' . number_format($poAmt, 2),
                'grn_amount'         => '₱' . number_format($grnAmt, 2),
                'invoice_amount'     => '₱' . number_format($invAmt, 2),
                'variance'           => '₱' . number_format($variance, 2),
                'variance_raw'       => $variance,
                'match_status'       => $matchStatus,
                'bill_status'        => $inv->status,
                'approver_name'      => $m?->approver?->name ?? 'Finance Approver',
                'approved_at'        => $m?->approved_at?->format('M d, Y H:i') ?? ($inv->status === 'APPROVED' ? 'Approved' : 'Pending Review'),
                'items'              => $inv->items->map(fn($it) => [
                    'description'    => $it->description,
                    'expense_type'   => $it->expense_type,
                    'atc_code'       => $it->atc_code,
                    'quantity'       => $it->quantity,
                    'unit_price'     => '₱' . number_format((float) $it->unit_price, 2),
                    'gross'          => '₱' . number_format((float) $it->gross_amount, 2),
                    'ewt'            => '₱' . number_format((float) $it->ewt_amount, 2),
                    'net'            => '₱' . number_format((float) $it->net_payable, 2),
                ])->toArray(),
                'tax_withheld'       => '₱' . number_format((float) $inv->withholding_tax_amount, 2),
                'net_payable'        => '₱' . number_format((float) $inv->net_payable_amount, 2),
              ];
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
                <!-- 3. Table Row Badging -->
                @if($matchStatus === 'MATCHED' && abs($variance) == 0)
                  <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                    <i class="ph ph-check-circle me-1"></i>✓ 3-WAY MATCHED
                  </span>
                @elseif($matchStatus === 'PENDING_GRN')
                  <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                    <i class="ph ph-clock me-1"></i>PENDING GRN
                  </span>
                @else
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace">
                    <i class="ph ph-warning me-1"></i>⚠️ VARIANCE (₱{{ number_format(abs($variance), 2) }})
                  </span>
                @endif
              </td>
              <td><span class="badge {{ $billBadge }}">{{ $inv->status }}</span></td>
              <td class="text-end">
                <!-- 3. Table Row Actions -->
                <div class="d-flex justify-content-end align-items-center gap-1">
                  <!-- Inspect Match Button (Side Drawer) -->
                  <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="Inspect PO vs GRN vs Invoice Match" onclick="openMatchDrawer({{ json_encode($inspectionData) }})">
                    <i class="ph ph-eye"></i>
                  </button>

                  <!-- Convert to AP Voucher / Approve Action -->
                  @if($inv->status !== 'APPROVED' && $inv->status !== 'PAID')
                    <form method="POST" action="{{ route('ap.purchase-bills.approve', $inv->id) }}" class="d-inline" onsubmit="return confirm('Authorize 3-Way matching approval for {{ $inv->bill_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success py-1 px-2 fs-xs" title="Approve 3-Way Match & Convert to AP Voucher">
                        <i class="ph ph-check-circle me-1"></i> Convert to AP Voucher
                      </button>
                    </form>
                  @else
                    <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="View Invoices Hub">
                      <i class="ph ph-receipt me-1"></i> Voucher Hub
                    </a>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="ph ph-receipt fs-1 d-block mb-2 text-secondary"></i>
                No purchase bills found matching current filter.
              </td>
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

<!-- ========================================================= -->
<!-- Slide-Over Drawer: 3-Way Match Verification & Breakdown -->
<!-- ========================================================= -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="matchInspectionDrawer" style="width: 840px; max-width: 95vw;" aria-labelledby="matchInspectionDrawerLabel">
  <div class="offcanvas-header border-bottom bg-light py-3 px-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-1 fs-xs" id="drawerBillNo">BILL-0000</span>
        <span class="badge" id="drawerMatchBadge">✓ 3-WAY MATCHED</span>
      </div>
      <h5 class="offcanvas-title font-weight-bold text-dark mb-0" id="matchInspectionDrawerLabel">Procurement 3-Way Match Audit &amp; Inspection</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-4 bg-light-subtle">
    <!-- Supplier & Bill Overview -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h6 class="fw-bold text-dark mb-1 fs-6" id="drawerVendorTitle">Supplier Legal Name</h6>
          <span class="fs-xs font-monospace text-muted" id="drawerVendorTinText">TIN: -</span>
        </div>
        <div class="text-end">
          <span class="badge bg-secondary-subtle text-secondary font-monospace d-block mb-1" id="drawerInvoiceText">Inv: -</span>
          <span class="badge bg-light text-dark border" id="drawerBillStatusBadge">UNPAID</span>
        </div>
      </div>
      <div class="row g-2 pt-2 border-top fs-xs">
        <div class="col-md-6">
          <span class="text-muted">Bill Date:</span> <strong class="text-dark font-monospace ms-1" id="drawerBillDateText">-</strong>
        </div>
        <div class="col-md-6">
          <span class="text-muted">Payment Due Date:</span> <strong class="text-dark font-monospace ms-1" id="drawerDueDateText">-</strong>
        </div>
      </div>
    </div>

    <!-- 3-Way Comparison Matrix Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-primary d-flex align-items-center gap-1">
          <i class="ph ph-scales fs-5"></i> 3-Way Matching Comparison Matrix
        </span>
        <span class="badge" id="drawerMatrixBadge">MATCHED</span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Document Stage</th>
              <th>Reference #</th>
              <th class="text-end">Authorized Amount</th>
              <th class="text-end">Variance vs Invoice</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-primary-subtle text-primary">1. Purchase Order (PSM)</span></td>
              <td><span class="font-monospace fw-bold text-dark" id="drawerPoRef">-</span></td>
              <td class="text-end font-monospace fw-semibold" id="drawerPoAmt">-</td>
              <td class="text-end font-monospace text-muted" id="drawerPoVar">—</td>
            </tr>
            <tr>
              <td><span class="badge bg-info-subtle text-info">2. Goods Receipt (SWS)</span></td>
              <td><span class="font-monospace fw-bold text-dark" id="drawerGrnRef">-</span></td>
              <td class="text-end font-monospace fw-semibold" id="drawerGrnAmt">-</td>
              <td class="text-end font-monospace text-muted" id="drawerGrnVar">—</td>
            </tr>
            <tr class="table-light fw-bold">
              <td><span class="badge bg-dark text-white">3. Supplier Invoice (AP)</span></td>
              <td><span class="font-monospace text-dark" id="drawerInvRefText">-</span></td>
              <td class="text-end font-monospace text-primary fs-6" id="drawerInvAmtText">-</td>
              <td class="text-end font-monospace fs-6" id="drawerTotalVarText">₱0.00</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="fs-xs text-muted mt-2 pt-2 border-top d-flex justify-content-between">
        <span>Verified by: <strong class="text-dark" id="drawerApproverText">Finance Approver</strong></span>
        <span>Reconciliation: <strong class="text-dark" id="drawerApprovedAtText">-</strong></span>
      </div>
    </div>

    <!-- Line Item Breakdown Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1">
          <i class="ph ph-list-numbers fs-5 text-secondary"></i> Invoiced Line Items &amp; Withholding Tax
        </span>
        <span class="fs-xs text-muted" id="drawerItemCount">0 Items</span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Description</th>
              <th>Classification</th>
              <th>ATC</th>
              <th class="text-end">Qty</th>
              <th class="text-end">Unit Price</th>
              <th class="text-end">Gross (₱)</th>
            </tr>
          </thead>
          <tbody id="drawerItemsTbody">
            <!-- Dynamic Items -->
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="5" class="text-end">Total Gross Invoiced:</td>
              <td class="text-end font-monospace text-dark" id="drawerSummaryGross">₱0.00</td>
            </tr>
            <tr>
              <td colspan="5" class="text-end text-danger">Total Estimated EWT (2307):</td>
              <td class="text-end font-monospace text-danger" id="drawerSummaryEwt">₱0.00</td>
            </tr>
            <tr class="border-top border-dark">
              <td colspan="5" class="text-end text-primary fs-6">Net Accounts Payable:</td>
              <td class="text-end font-monospace text-primary fs-6" id="drawerSummaryNet">₱0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="offcanvas-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="offcanvas">Close</button>
    <div id="drawerActionBtnContainer">
      <!-- Dynamic Approval Button -->
    </div>
  </div>
</div>

<!-- ========================================================= -->
<!-- Modal: Ingest Purchase Bill with Live 3-Way Match Calculator -->
<!-- ========================================================= -->
<div class="modal fade" id="createBillModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-light-subtle border-bottom py-3 px-4">
        <div class="d-flex align-items-center gap-2">
          <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center">
            <i class="ph ph-file-plus fs-4"></i>
          </span>
          <div>
            <h5 class="modal-title font-weight-bold mb-0">Ingest Purchase Bill &amp; 3-Way Match</h5>
            <span class="fs-xs text-muted">Verify incoming supplier sales invoice against Purchase Orders and Warehouse Goods Receipts</span>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" action="{{ route('ap.purchase-bills.store') }}" enctype="multipart/form-data" id="ingestBillForm">
        @csrf
        <div class="modal-body p-4">
          <!-- 1. Dynamic PO / GRN Preset Selector -->
          <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle mb-3">
            <div class="row align-items-center g-2">
              <div class="col-md-4">
                <label class="form-label small fw-bold text-primary mb-0 d-flex align-items-center gap-1">
                  <i class="ph ph-lightning fs-5"></i> Quick-Fill from Active Procurement:
                </label>
              </div>
              <div class="col-md-8">
                <select id="poPresetSelector" class="form-select form-select-sm bg-white" onchange="applyPoPreset(this.value)">
                  <option value="">-- Select Active PO from PSM (Procurement) or Enter Custom --</option>
                  <option value="PO_01" data-vendor="1" data-po="PO-2026-0881" data-grn="GRN-2026-0881" data-amt="85000" data-item="Pharmaceutical Ampoules & Syringes" data-qty="50" data-price="1700" data-atc="WI158">
                    PO-2026-0881 | MedTech Pharma Inc. | ₱85,000.00 (Supplies)
                  </option>
                  <option value="PO_02" data-vendor="2" data-po="PO-2026-0912" data-grn="GRN-2026-0912" data-amt="120000" data-item="Dialysis Filters & Medical Tubing" data-qty="60" data-price="2000" data-atc="WI158">
                    PO-2026-0912 | B. Braun Medical | ₱120,000.00 (Equipment)
                  </option>
                  <option value="PO_03" data-vendor="3" data-po="PO-2026-0955" data-grn="GRN-2026-0955" data-amt="45000" data-item="Bio-Hazard Sterilization Maintenance" data-qty="1" data-price="45000" data-atc="WI160">
                    PO-2026-0955 | Metro Bio-Pharma | ₱45,000.00 (Services)
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Master Header Row -->
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Supplier / Vendor <span class="text-danger">*</span></label>
              <select name="vendor_id" id="modalVendorSelect" class="form-select form-select-sm" required>
                <option value="">-- Select Vendor --</option>
                @foreach($vendors as $v)
                  <option value="{{ $v->id }}" data-tax-type="{{ $v->tax_type ?? 'VAT_REGISTERED' }}" data-ewt="{{ $v->default_ewt_rate ?? '1.00' }}" data-atc="{{ $v->default_atc_code ?? 'WC158' }}">
                    {{ $v->name }} ({{ $v->code }})
                  </option>
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
          <div class="card border rounded-3 p-3 bg-light-subtle mb-3">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Purchase Order (PO #)</label>
                <input type="text" name="po_number" id="modalPoNumber" class="form-control form-control-sm font-monospace" placeholder="e.g. PO-2026-0044" value="PO-{{ date('Ymd') }}-{{ rand(100,999) }}">
                <div class="mt-1">
                  <input type="number" step="0.01" min="0" name="po_amount" id="modalPoAmount" class="form-control form-control-sm font-monospace text-end" placeholder="PO Authorized ₱" oninput="recalculateBillTotals()">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Goods Receipt Note (GRN #)</label>
                <input type="text" name="grn_number" id="modalGrnNumber" class="form-control form-control-sm font-monospace" placeholder="e.g. GRN-2026-0092" value="GRN-{{ date('Ymd') }}-{{ rand(100,999) }}">
                <div class="mt-1">
                  <input type="number" step="0.01" min="0" name="grn_amount" id="modalGrnAmount" class="form-control form-control-sm font-monospace text-end" placeholder="GRN Received ₱" oninput="recalculateBillTotals()">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Vendor Sales Invoice # <span class="text-danger">*</span></label>
                <input type="text" name="vendor_invoice_number" id="modalVendorInvoice" class="form-control form-control-sm font-monospace" placeholder="e.g. SI-88992211" required>
                <div class="fs-xs text-muted mt-1">Supplier actual billing reference</div>
              </div>
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
                  <td><input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" placeholder="Item description..." required></td>
                  <td>
                    <select name="items[0][expense_type]" class="form-select form-select-sm item-expense" onchange="autoSelectAtc(this)">
                      <option value="GOODS_INVENTORY" data-atc="WI158">Goods / Inventory (1%)</option>
                      <option value="SERVICES_MAINTENANCE" data-atc="WI160">Services & Maintenance (2%)</option>
                      <option value="SPACE_RENTAL" data-atc="WC100">Space Rental (5%)</option>
                      <option value="DOCTOR_PROFESSIONAL_FEE" data-atc="WI010">Doctor PF (10%)</option>
                      <option value="EXEMPT" data-atc="EXEMPT">Non-Taxable / Exempt (0%)</option>
                    </select>
                  </td>
                  <td>
                    <select name="items[0][atc_code]" class="form-select form-select-sm item-atc" onchange="recalculateBillTotals()">
                      <option value="WI158" data-rate="0.01">WI158 (Goods 1%)</option>
                      <option value="WI160" data-rate="0.02">WI160 (Services 2%)</option>
                      <option value="WC100" data-rate="0.05">WC100 (Rental 5%)</option>
                      <option value="WI010" data-rate="0.10">WI010 (Doctor PF 10%)</option>
                      <option value="EXEMPT" data-rate="0.00">EXEMPT (0%)</option>
                    </select>
                  </td>
                  <td><input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm text-end item-qty" value="1" oninput="recalculateBillTotals()" required></td>
                  <td><input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm text-end item-price" value="0.00" oninput="recalculateBillTotals()" required></td>
                  <td class="text-end font-monospace fw-bold item-line-gross">₱0.00</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 2. Live 3-Way Match Calculator Card & Total Tax Breakdown -->
          <div class="row g-3">
            <div class="col-md-7">
              <!-- Live 3-Way Match Verification Card -->
              <div class="card border rounded-3 p-3 h-100 bg-light-subtle">
                <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1 mb-2 pb-1 border-bottom">
                  <i class="ph ph-scales fs-5 text-primary"></i> Live 3-Way Match Calculator &amp; Variance Engine
                </span>
                <div class="row g-2 fs-xs">
                  <div class="col-4">
                    <span class="text-muted d-block">PO Total:</span>
                    <strong class="font-monospace fs-6 text-dark" id="livePoTotal">₱0.00</strong>
                  </div>
                  <div class="col-4">
                    <span class="text-muted d-block">GRN Total:</span>
                    <strong class="font-monospace fs-6 text-dark" id="liveGrnTotal">₱0.00</strong>
                  </div>
                  <div class="col-4">
                    <span class="text-muted d-block">Vendor Invoiced:</span>
                    <strong class="font-monospace fs-6 text-primary" id="liveInvTotal">₱0.00</strong>
                  </div>
                </div>

                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                  <span class="fs-xs fw-semibold text-muted">Computed Variance:</span>
                  <div id="liveMatchBadge">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-xs font-monospace">
                      <i class="ph ph-check-circle me-1"></i> ₱0.00 [3-Way Match Passed]
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-5">
              <!-- Total Calculation Summary -->
              <div class="card border rounded-3 p-3 bg-light h-100 fs-xs">
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

          <!-- 4. Document Attachment Box -->
          <div class="mt-3">
            <label class="form-label small fw-semibold text-muted mb-1"><i class="ph ph-paperclip me-1"></i> Supporting Documents (Scanned Sales Invoice / Delivery Receipt)</label>
            <div class="border border-dashed rounded-3 p-3 text-center bg-white">
              <input type="file" name="attachment" id="billAttachment" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
              <span class="fs-xs text-muted d-block mt-1">Accepts PDF, JPG, PNG attachments up to 10MB</span>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light-subtle border-top py-2 px-4">
          <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold"><i class="ph ph-check-circle me-1"></i> Post Purchase Bill &amp; 3-Way Match</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let billLineIndex = 1;

function applyPoPreset(preset) {
  if (!preset) return;
  const select = document.getElementById('poPresetSelector');
  const opt = select.options[select.selectedIndex];
  if (!opt) return;

  const vendorId = opt.getAttribute('data-vendor');
  const poNum = opt.getAttribute('data-po');
  const grnNum = opt.getAttribute('data-grn');
  const amt = parseFloat(opt.getAttribute('data-amt') || 0);
  const itemDesc = opt.getAttribute('data-item') || '';
  const qty = opt.getAttribute('data-qty') || '1';
  const price = opt.getAttribute('data-price') || amt;
  const atc = opt.getAttribute('data-atc') || 'WI158';

  if (vendorId) document.getElementById('modalVendorSelect').value = vendorId;
  if (poNum) document.getElementById('modalPoNumber').value = poNum;
  if (grnNum) document.getElementById('modalGrnNumber').value = grnNum;
  if (amt) {
    document.getElementById('modalPoAmount').value = amt.toFixed(2);
    document.getElementById('modalGrnAmount').value = amt.toFixed(2);
  }
  document.getElementById('modalVendorInvoice').value = 'SI-' + poNum.replace('PO-', '');

  const tbody = document.getElementById('billItemsTbody');
  tbody.innerHTML = `
    <tr>
      <td><input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" value="${itemDesc}" required></td>
      <td>
        <select name="items[0][expense_type]" class="form-select form-select-sm item-expense" onchange="autoSelectAtc(this)">
          <option value="GOODS_INVENTORY" ${atc === 'WI158' ? 'selected' : ''}>Goods / Inventory (1%)</option>
          <option value="SERVICES_MAINTENANCE" ${atc === 'WI160' ? 'selected' : ''}>Services & Maintenance (2%)</option>
          <option value="SPACE_RENTAL" ${atc === 'WC100' ? 'selected' : ''}>Space Rental (5%)</option>
          <option value="DOCTOR_PROFESSIONAL_FEE" ${atc === 'WI010' ? 'selected' : ''}>Doctor PF (10%)</option>
          <option value="EXEMPT" ${atc === 'EXEMPT' ? 'selected' : ''}>Non-Taxable / Exempt (0%)</option>
        </select>
      </td>
      <td>
        <select name="items[0][atc_code]" class="form-select form-select-sm item-atc" onchange="recalculateBillTotals()">
          <option value="WI158" data-rate="0.01" ${atc === 'WI158' ? 'selected' : ''}>WI158 (Goods 1%)</option>
          <option value="WI160" data-rate="0.02" ${atc === 'WI160' ? 'selected' : ''}>WI160 (Services 2%)</option>
          <option value="WC100" data-rate="0.05" ${atc === 'WC100' ? 'selected' : ''}>WC100 (Rental 5%)</option>
          <option value="WI010" data-rate="0.10" ${atc === 'WI010' ? 'selected' : ''}>WI010 (Doctor PF 10%)</option>
          <option value="EXEMPT" data-rate="0.00" ${atc === 'EXEMPT' ? 'selected' : ''}>EXEMPT (0%)</option>
        </select>
      </td>
      <td><input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm text-end item-qty" value="${qty}" oninput="recalculateBillTotals()" required></td>
      <td><input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm text-end item-price" value="${parseFloat(price).toFixed(2)}" oninput="recalculateBillTotals()" required></td>
      <td class="text-end font-monospace fw-bold item-line-gross">₱${amt.toFixed(2)}</td>
    </tr>
  `;

  recalculateBillTotals();
}

function autoSelectAtc(expenseSelect) {
  const selectedOption = expenseSelect.options[expenseSelect.selectedIndex];
  const targetAtc = selectedOption ? selectedOption.getAttribute('data-atc') : 'WI158';
  const row = expenseSelect.closest('tr');
  if (row) {
    const atcSelect = row.querySelector('.item-atc');
    if (atcSelect) {
      atcSelect.value = targetAtc;
    }
  }
  recalculateBillTotals();
}

function addBillLineItem() {
  const tbody = document.getElementById('billItemsTbody');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><input type="text" name="items[${billLineIndex}][description]" class="form-control form-control-sm item-desc" placeholder="Item description..." required></td>
    <td>
      <select name="items[${billLineIndex}][expense_type]" class="form-select form-select-sm item-expense" onchange="autoSelectAtc(this)">
        <option value="GOODS_INVENTORY" data-atc="WI158">Goods / Inventory (1%)</option>
        <option value="SERVICES_MAINTENANCE" data-atc="WI160">Services & Maintenance (2%)</option>
        <option value="SPACE_RENTAL" data-atc="WC100">Space Rental (5%)</option>
        <option value="DOCTOR_PROFESSIONAL_FEE" data-atc="WI010">Doctor PF (10%)</option>
        <option value="EXEMPT" data-atc="EXEMPT">Non-Taxable / Exempt (0%)</option>
      </select>
    </td>
    <td>
      <select name="items[${billLineIndex}][atc_code]" class="form-select form-select-sm item-atc" onchange="recalculateBillTotals()">
        <option value="WI158" data-rate="0.01">WI158 (Goods 1%)</option>
        <option value="WI160" data-rate="0.02">WI160 (Services 2%)</option>
        <option value="WC100" data-rate="0.05">WC100 (Rental 5%)</option>
        <option value="WI010" data-rate="0.10">WI010 (Doctor PF 10%)</option>
        <option value="EXEMPT" data-rate="0.00">EXEMPT (0%)</option>
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
    const atcSelect = row.querySelector('.item-atc');
    const atc = atcSelect?.value || 'WI158';

    const gross = qty * price;
    row.querySelector('.item-line-gross').textContent = '₱' + gross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    let rate = 0.01;
    if (atc === 'WI160') rate = 0.02;
    else if (atc === 'WC100') rate = 0.05;
    else if (atc === 'WI010') rate = 0.10;
    else if (atc === 'EXEMPT') rate = 0.00;

    const ewt = gross * rate;

    totalGross += gross;
    totalEwt += ewt;
  });

  const totalNet = totalGross - totalEwt;

  document.getElementById('lblTotalGross').textContent = '₱' + totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblTotalEwt').textContent = '₱' + totalEwt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('lblTotalNet').textContent = '₱' + totalNet.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  // Live 3-Way Match Calculator
  const poInput = document.getElementById('modalPoAmount');
  const grnInput = document.getElementById('modalGrnAmount');

  const poVal = parseFloat(poInput?.value || 0);
  const grnVal = parseFloat(grnInput?.value || 0);

  document.getElementById('livePoTotal').textContent = '₱' + poVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('liveGrnTotal').textContent = '₱' + grnVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('liveInvTotal').textContent = '₱' + totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const variance = totalGross - poVal;
  const badgeContainer = document.getElementById('liveMatchBadge');

  if (Math.abs(variance) < 0.001 && poVal > 0) {
    badgeContainer.innerHTML = `
      <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-xs font-monospace">
        <i class="ph ph-check-circle me-1"></i> ₱0.00 [✓ 3-Way Match Passed]
      </span>
    `;
  } else if (poVal > 0) {
    badgeContainer.innerHTML = `
      <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-xs font-monospace">
        <i class="ph ph-warning me-1"></i> ⚠️ ₱${Math.abs(variance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} Variance Flagged
      </span>
    `;
  } else {
    badgeContainer.innerHTML = `
      <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 fs-xs font-monospace">
        Enter PO &amp; GRN Amounts
      </span>
    `;
  }
}

function openMatchDrawer(data) {
  if (!data) return;

  document.getElementById('drawerBillNo').textContent = data.bill_number;
  document.getElementById('drawerVendorTitle').textContent = data.vendor_name;
  document.getElementById('drawerVendorTinText').textContent = 'TIN: ' + data.vendor_tin;
  document.getElementById('drawerInvoiceText').textContent = 'Inv: ' + data.vendor_invoice;
  document.getElementById('drawerBillStatusBadge').textContent = data.bill_status;

  document.getElementById('drawerBillDateText').textContent = data.bill_date;
  document.getElementById('drawerDueDateText').textContent = data.due_date;

  // Comparison Matrix
  document.getElementById('drawerPoRef').textContent = data.po_number;
  document.getElementById('drawerPoAmt').textContent = data.po_amount;
  document.getElementById('drawerGrnRef').textContent = data.grn_number;
  document.getElementById('drawerGrnAmt').textContent = data.grn_amount;
  document.getElementById('drawerInvRefText').textContent = data.vendor_invoice;
  document.getElementById('drawerInvAmtText').textContent = data.invoice_amount;
  document.getElementById('drawerTotalVarText').textContent = data.variance;

  const isMatched = Math.abs(data.variance_raw) < 0.001;
  const matchBadge = document.getElementById('drawerMatchBadge');
  const matrixBadge = document.getElementById('drawerMatrixBadge');

  if (isMatched) {
    matchBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
    matchBadge.textContent = '✓ 3-WAY MATCHED';
    matrixBadge.className = 'badge bg-success-subtle text-success border border-success-subtle fs-xs';
    matrixBadge.textContent = 'MATCHED (0.00 Variance)';
  } else {
    matchBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
    matchBadge.textContent = '⚠️ VARIANCE';
    matrixBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle fs-xs';
    matrixBadge.textContent = 'DISCREPANCY (Variance ' + data.variance + ')';
  }

  document.getElementById('drawerApproverText').textContent = data.approver_name;
  document.getElementById('drawerApprovedAtText').textContent = data.approved_at;

  // Items Table
  const itemsTbody = document.getElementById('drawerItemsTbody');
  itemsTbody.innerHTML = '';
  document.getElementById('drawerItemCount').textContent = data.items.length + ' Item' + (data.items.length !== 1 ? 's' : '');

  data.items.forEach(it => {
    itemsTbody.innerHTML += `
      <tr>
        <td class="fw-semibold text-dark">${it.description}</td>
        <td><span class="badge bg-light text-dark border">${it.expense_type}</span></td>
        <td><span class="badge bg-primary-subtle text-primary font-monospace">${it.atc_code}</span></td>
        <td class="text-end font-monospace">${it.quantity}</td>
        <td class="text-end font-monospace">${it.unit_price}</td>
        <td class="text-end font-monospace fw-bold text-dark">${it.gross}</td>
      </tr>
    `;
  });

  document.getElementById('drawerSummaryGross').textContent = data.invoice_amount;
  document.getElementById('drawerSummaryEwt').textContent = data.tax_withheld;
  document.getElementById('drawerSummaryNet').textContent = data.net_payable;

  // Actions
  const btnContainer = document.getElementById('drawerActionBtnContainer');
  if (data.bill_status !== 'APPROVED' && data.bill_status !== 'PAID') {
    btnContainer.innerHTML = `
      <form method="POST" action="/accounts-payable/purchase-bills/${data.id}/approve" class="d-inline">
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
        <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold">
          <i class="ph ph-check-circle me-1"></i> Authorize &amp; Convert to AP Voucher
        </button>
      </form>
    `;
  } else {
    btnContainer.innerHTML = `
      <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-primary px-3">
        <i class="ph ph-receipt me-1"></i> Open in Voucher Hub
      </a>
    `;
  }

  const drawerEl = document.getElementById('matchInspectionDrawer');
  if (drawerEl && window.bootstrap) {
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    drawerInstance.show();
  }
}
</script>
@endpush
