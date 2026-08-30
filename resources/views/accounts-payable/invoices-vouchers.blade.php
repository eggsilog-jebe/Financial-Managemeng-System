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
      <!-- Header Action Buttons -->
      <a href="{{ route('ap.invoices.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm" title="Download CSV register of all supplier invoices">
        <i class="ph ph-file-csv me-1"></i> Export AP Register (CSV)
      </a>
      <a href="{{ route('ap.invoices.batch-2307', request()->query()) }}" class="btn btn-outline-teal btn-sm" style="color: #0d9488; border-color: #0d9488;" title="Generate batch certificates of withholding tax">
        <i class="ph ph-file-text me-1"></i> Generate BIR 2307 Batch
      </a>
      <a href="{{ route('ap.purchase-bills') }}" class="btn btn-primary btn-sm">
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
        <span class="fs-xs text-muted">Audited &amp; Ingested AP</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Gross Invoiced</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) $totalBilled, 2) }}</h4>
        <span class="fs-xs text-muted">Cumulative gross billing</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Pending Settlement</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) $totalPending, 2) }}</h4>
        <span class="fs-xs text-muted">Awaiting check/EFT release</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Disbursement Vouchers</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalVouchers }} Vouchers</h4>
        <span class="fs-xs text-muted">Vouchers in clearing stream</span>
      </div>
    </div>
  </div>

  <!-- Invoices Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <!-- Filter Bar: Date Range, Vendor Quick-Filter, Status, and Search -->
      <form method="GET" action="{{ route('ap.invoices') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <!-- Date Range -->
          <div class="d-flex align-items-center gap-1">
            <span class="fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-calendar me-1"></i> Bill Date:</span>
            <input type="date" name="start_date" class="form-control form-control-sm" style="width: 135px;" value="{{ request('start_date', $startDate ?? '') }}" placeholder="Start Date">
            <span class="text-muted fs-xs">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm" style="width: 135px;" value="{{ request('end_date', $endDate ?? '') }}" placeholder="End Date">
          </div>

          <!-- Vendor Quick-Filter -->
          <div class="d-flex align-items-center gap-1">
            <span class="fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-buildings me-1"></i> Vendor:</span>
            <select name="vendor_id" class="form-select form-select-sm bg-light" style="max-width: 180px;">
              <option value="">All Vendors</option>
              @foreach($vendors ?? [] as $vnd)
                <option value="{{ $vnd->id }}" {{ (string) request('vendor_id', $vendorId ?? '') === (string) $vnd->id ? 'selected' : '' }}>
                  {{ $vnd->name }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Status -->
          <div class="d-flex align-items-center gap-1">
            <span class="fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</span>
            <select name="status" class="form-select form-select-sm bg-light" style="width: 130px;">
              <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
              <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
              <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Unpaid / Pending</option>
              <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Partially Paid</option>
              <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Fully Settled</option>
            </select>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2">
          <div class="search-box" style="width: 220px;">
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search bill #, inv #, vendor..." value="{{ request('search') }}">
          </div>
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-magnifying-glass me-1"></i> Filter</button>
          @if(request()->hasAny(['status', 'vendor_id', 'start_date', 'end_date', 'search']))
            <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-light border" title="Clear Filters"><i class="ph ph-x"></i></a>
          @endif
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="voucherTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>APV Ref # &amp; Supplier Invoice</th>
              <th>Vendor Legal Name &amp; TIN</th>
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

              $isVatRegistered = ($bill->vendor?->tax_type ?? 'VAT_REGISTERED') === 'VAT_REGISTERED';
              $netBase = $isVatRegistered ? round($gross / 1.12, 2) : $gross;
              $inputVat = $isVatRegistered ? round($gross - $netBase, 2) : 0.00;

              $statusBadge = match($bill->status) {
                'PAID'     => 'bg-success-subtle text-success border border-success-subtle',
                'APPROVED' => 'bg-info-subtle text-info border border-info-subtle',
                'PARTIAL'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                default    => 'bg-secondary-subtle text-secondary border',
              };

              $cert = $bill->birCertificate;
              $certData = $cert ? [
                'id'           => $cert->id,
                'cert_no'      => $cert->certificate_number,
                'payee'        => $cert->payee_name,
                'tin'          => $cert->payee_tin,
                'atc'          => $cert->atc_code,
                'tax_base'     => '₱' . number_format((float) $cert->tax_base_amount, 2),
                'rate'         => number_format((float) $cert->tax_rate * 100, 1) . '%',
                'tax_withheld' => '₱' . number_format((float) $cert->tax_withheld, 2),
                'period'       => $cert->period_from?->format('M d, Y') . ' — ' . $cert->period_to?->format('M d, Y'),
                'print_url'    => route('accounting.print.bir2307', $cert->id),
              ] : null;

              $match = $bill->threeWayMatch;
              $voucherData = [
                'id'                  => $bill->id,
                'bill_number'         => $bill->bill_number,
                'vendor_invoice'      => $bill->vendor_invoice_number,
                'vendor_name'         => $bill->vendor?->name ?? 'Unknown Vendor',
                'vendor_tin'          => $bill->vendor?->tin ?? 'N/A',
                'vendor_address'      => $bill->vendor?->registered_address ?? '—',
                'vendor_bank'         => $bill->vendor?->bank_name ?? '—',
                'vendor_bank_acc'     => $bill->vendor?->bank_account_number ?? '—',
                'vendor_tax_type'     => $isVatRegistered ? 'VAT-Registered (12%)' : 'Non-VAT',
                'bill_date'           => $bill->bill_date?->format('M d, Y') ?? '—',
                'due_date'            => $bill->due_date?->format('M d, Y') ?? '—',
                'gross'               => '₱' . number_format($gross, 2),
                'net_base'            => '₱' . number_format($netBase, 2),
                'input_vat'           => '₱' . number_format($inputVat, 2),
                'ewt'                 => '₱' . number_format($ewt, 2),
                'net_payable'         => '₱' . number_format($net, 2),
                'paid'                => '₱' . number_format($paid, 2),
                'balance'             => '₱' . number_format($openBal, 2),
                'balance_raw'         => $openBal,
                'status'              => $bill->status,
                'po_number'           => $match?->po_number ?? '—',
                'grn_number'          => $match?->grn_number ?? '—',
                'po_amount'           => '₱' . number_format((float) ($match?->po_amount ?? $gross), 2),
                'grn_amount'          => '₱' . number_format((float) ($match?->grn_amount ?? $gross), 2),
                'match_status'        => $match?->match_status ?? 'MATCHED',
                'approver_name'       => $match?->approver?->name ?? 'Finance Approver',
                'approved_at'         => $match?->approved_at?->format('M d, Y H:i') ?? ($bill->status === 'APPROVED' ? 'Approved' : 'Pending Review'),
                'cert_data'           => $certData,
                'disbursement_vouchers' => $bill->disbursementVouchers->map(fn ($dv) => [
                    'voucher_number' => $dv->voucher_number,
                    'amount'         => '₱' . number_format((float) $dv->net_disbursed_amount, 2),
                    'method'         => $dv->payment_method,
                    'bank'           => $dv->bankAccount?->name ?? 'Bank Account',
                    'status'         => $dv->status,
                ])->toArray(),
              ];
            @endphp
            <tr>
              <td>
                <!-- 1. APV Reference in bold + Supplier Invoice underneath -->
                <div class="fw-bold text-primary font-monospace fs-6">{{ $bill->bill_number }}</div>
                <div class="fs-xs text-muted d-flex align-items-center gap-1">
                  <i class="ph ph-receipt"></i> Inv: <span class="font-monospace fw-semibold text-dark">{{ $bill->vendor_invoice_number }}</span>
                </div>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $bill->vendor?->name ?? 'Unknown Vendor' }}</div>
                <div class="fs-xs text-muted font-monospace">TIN: {{ $bill->vendor?->tin ?? 'N/A' }}</div>
              </td>
              <td><span class="fs-xs text-dark">{{ $bill->bill_date ? $bill->bill_date->format('M d, Y') : '—' }}</span></td>
              <td><span class="fs-xs text-muted">{{ $bill->due_date ? $bill->due_date->format('M d, Y') : '—' }}</span></td>
              <td class="text-end font-monospace">₱{{ number_format($gross, 2) }}</td>
              <td class="text-end font-monospace text-muted">
                ₱{{ number_format($ewt, 2) }}
                @if($certData)
                  <span class="badge bg-warning-subtle text-warning-emphasis font-monospace d-inline-block" style="font-size: 10px;">
                    {{ $cert->atc_code ?? 'WC158' }}
                  </span>
                @endif
              </td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format($net, 2) }}</td>
              <td class="text-end font-monospace text-success">₱{{ number_format($paid, 2) }}</td>
              <td><span class="badge {{ $statusBadge }}">{{ $bill->status }}</span></td>
              <td class="text-end">
                <!-- 3. ACTIONS Column -->
                <div class="d-flex justify-content-end align-items-center gap-1">
                  <!-- View Voucher (Slide-over drawer) -->
                  <button type="button" class="btn btn-sm btn-icon btn-outline-primary" title="View Voucher & Accounting Distribution" onclick="openVoucherDrawer({{ json_encode($voucherData) }})">
                    <i class="ph ph-eye"></i>
                  </button>

                  <!-- Print BIR 2307 -->
                  @if($certData)
                    <a href="{{ $certData['print_url'] }}" target="_blank" class="btn btn-sm btn-icon btn-outline-teal" style="color: #0d9488; border-color: #0d9488;" title="Print BIR Form 2307 Certificate">
                      <i class="ph ph-printer"></i>
                    </a>
                  @else
                    <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="View BIR 2307 Details" onclick="openVoucherDrawer({{ json_encode($voucherData) }})">
                      <i class="ph ph-receipt"></i>
                    </button>
                  @endif

                  <!-- Quick Approve / Route to Payment -->
                  @if($bill->status === 'UNPAID')
                    <form method="POST" action="{{ route('ap.invoices.quick-approve', $bill->id) }}" class="d-inline" onsubmit="return confirm('Approve Purchase Bill [{{ $bill->bill_number }}] for disbursement routing?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success py-1 px-2 fs-xs" title="Quick Approve & Route to Payment">
                        <i class="ph ph-check-circle me-1"></i> Quick Approve
                      </button>
                    </form>
                  @elseif($openBal > 0)
                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 fs-xs" title="Prepare Disbursement Voucher" onclick="openPrepareVoucherModal({{ $bill->id }}, '{{ $bill->bill_number }}', '{{ addslashes($bill->vendor?->name ?? '') }}', {{ $openBal }})">
                      <i class="ph ph-credit-card me-1"></i> Prepare Voucher
                    </button>
                  @else
                    <span class="badge bg-light text-muted border fs-xs">Disbursed</span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="ph ph-receipt fs-1 d-block mb-2 text-secondary"></i>
                No purchase invoices found matching filter criteria.
              </td>
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

<!-- ========================================================= -->
<!-- Slide-Over Drawer: Voucher Accounting Distribution & Audit -->
<!-- ========================================================= -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="voucherDetailsDrawer" style="width: 820px; max-width: 95vw;" aria-labelledby="voucherDetailsDrawerLabel">
  <div class="offcanvas-header border-bottom bg-light py-3 px-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-1 fs-xs" id="drawerBillNumber">APV-0000</span>
        <span class="badge" id="drawerStatusBadge">APPROVED</span>
      </div>
      <h5 class="offcanvas-title font-weight-bold text-dark mb-0" id="voucherDetailsDrawerLabel">Accounts Payable Voucher Inspection</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-4 bg-light-subtle">
    <!-- Header Vendor Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h6 class="fw-bold text-dark mb-1 fs-6" id="drawerVendorName">Supplier Legal Name</h6>
          <span class="fs-xs font-monospace text-muted d-block" id="drawerVendorTin">TIN: -</span>
          <span class="fs-xs text-muted d-block mt-1" id="drawerVendorAddress"><i class="ph ph-map-pin me-1"></i>-</span>
        </div>
        <div class="text-end">
          <span class="badge bg-secondary-subtle text-secondary font-monospace d-block mb-1" id="drawerInvoiceNumber">Inv: -</span>
          <span class="badge bg-light text-dark border" id="drawerTaxType">VAT-Registered</span>
        </div>
      </div>

      <div class="row g-2 pt-2 border-top fs-xs">
        <div class="col-md-3">
          <span class="text-muted d-block">Bill Date</span>
          <span class="fw-semibold text-dark font-monospace" id="drawerBillDate">-</span>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Due Date</span>
          <span class="fw-semibold text-dark font-monospace" id="drawerDueDate">-</span>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Gross Invoiced</span>
          <span class="fw-bold text-dark font-monospace" id="drawerGrossAmount">₱0.00</span>
        </div>
        <div class="col-md-3">
          <span class="text-muted d-block">Current Balance Due</span>
          <span class="fw-bold text-danger font-monospace" id="drawerBalanceDue">₱0.00</span>
        </div>
      </div>
    </div>

    <!-- Section 1: Accounting Distribution (GAAP Double-Entry Journal) -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-primary d-flex align-items-center gap-1">
          <i class="ph ph-books fs-5"></i> Accounting Distribution (GAAP Double-Entry Breakdown)
        </span>
        <span class="badge bg-success-subtle text-success border border-success-subtle fs-xs">Balanced (DR = CR)</span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Account Code</th>
              <th>Account Title</th>
              <th class="text-end">Debit (₱)</th>
              <th class="text-end">Credit (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace fw-bold text-primary">5010</span></td>
              <td>Medical Supplies &amp; Operating Expenses (Net Base)</td>
              <td class="text-end font-monospace fw-semibold text-dark" id="distDrExpense">₱0.00</td>
              <td class="text-end font-monospace text-muted">—</td>
            </tr>
            <tr id="distVatRow">
              <td><span class="font-monospace fw-bold text-primary">1130</span></td>
              <td>Input VAT (12% Withheld on Purchases)</td>
              <td class="text-end font-monospace fw-semibold text-dark" id="distDrVat">₱0.00</td>
              <td class="text-end font-monospace text-muted">—</td>
            </tr>
            <tr>
              <td><span class="font-monospace fw-bold text-danger">2020</span></td>
              <td>Expanded Withholding Tax Payable (BIR 2307 / 1601-EQ)</td>
              <td class="text-end font-monospace text-muted">—</td>
              <td class="text-end font-monospace fw-semibold text-danger" id="distCrEwt">₱0.00</td>
            </tr>
            <tr>
              <td><span class="font-monospace fw-bold text-dark">2010</span></td>
              <td>Accounts Payable — Trade (Net Liability to Vendor)</td>
              <td class="text-end font-monospace text-muted">—</td>
              <td class="text-end font-monospace fw-bold text-dark" id="distCrAp">₱0.00</td>
            </tr>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="2" class="text-end">Total Distribution:</td>
              <td class="text-end font-monospace text-primary" id="distTotalDebit">₱0.00</td>
              <td class="text-end font-monospace text-primary" id="distTotalCredit">₱0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Section 2: Procurement 3-Way Match Verification -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1">
          <i class="ph ph-git-merge fs-5 text-info"></i> Procurement 3-Way Match Verification
        </span>
        <span class="badge bg-info-subtle text-info border border-info-subtle fs-xs" id="drawerMatchStatus">MATCHED</span>
      </div>

      <div class="row g-3 fs-xs">
        <div class="col-md-4">
          <div class="p-2 border rounded bg-light-subtle">
            <span class="text-muted d-block">Purchase Order (PO)</span>
            <span class="fw-bold font-monospace text-dark" id="drawerPoNumber">PO-2026-000</span>
            <span class="d-block text-muted font-monospace mt-1" id="drawerPoAmount">₱0.00</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-2 border rounded bg-light-subtle">
            <span class="text-muted d-block">Goods Receipt (GRN)</span>
            <span class="fw-bold font-monospace text-dark" id="drawerGrnNumber">GRN-2026-000</span>
            <span class="d-block text-muted font-monospace mt-1" id="drawerGrnAmount">₱0.00</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-2 border rounded bg-light-subtle">
            <span class="text-muted d-block">Supplier Invoice</span>
            <span class="fw-bold font-monospace text-dark" id="drawerInvRef">INV-000</span>
            <span class="d-block text-muted font-monospace mt-1" id="drawerInvAmount">₱0.00</span>
          </div>
        </div>
      </div>
      <div class="fs-xs text-muted mt-2 pt-2 border-top d-flex justify-content-between">
        <span>Verified by: <strong class="text-dark" id="drawerApprover">Finance Approver</strong></span>
        <span>Reconciliation Date: <strong class="text-dark" id="drawerApprovedAt">-</strong></span>
      </div>
    </div>

    <!-- Section 3: BIR 2307 Tax Withholding Summary -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1">
          <i class="ph ph-certificate fs-5 text-teal" style="color: #0d9488;"></i> BIR Form 2307 Tax Certificate
        </span>
        <span class="badge bg-teal-subtle text-teal border fs-xs" style="color: #0d9488; background-color: #ccfbf1;" id="drawerCertStatus">Generated</span>
      </div>

      <div class="row g-2 fs-xs">
        <div class="col-md-6">
          <span class="text-muted">Certificate Number:</span>
          <span class="fw-bold font-monospace text-dark ms-1" id="drawerCertNo">-</span>
        </div>
        <div class="col-md-6">
          <span class="text-muted">ATC Code:</span>
          <span class="badge bg-primary-subtle text-primary font-monospace ms-1" id="drawerCertAtc">WC158</span>
        </div>
        <div class="col-md-6">
          <span class="text-muted">Tax Base:</span>
          <span class="font-monospace text-dark fw-semibold ms-1" id="drawerCertTaxBase">₱0.00</span>
        </div>
        <div class="col-md-6">
          <span class="text-muted">Tax Withheld:</span>
          <span class="font-monospace text-danger fw-bold ms-1" id="drawerCertWithheld">₱0.00</span>
        </div>
      </div>
    </div>

    <!-- Section 4: Settlement & Disbursement History -->
    <div class="card border rounded-3 p-3 bg-white shadow-sm">
      <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
        <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1">
          <i class="ph ph-credit-card fs-5 text-success"></i> Settlement &amp; Bank Details
        </span>
        <span class="badge bg-light text-dark border fs-xs" id="drawerSettlementCount">0 Vouchers</span>
      </div>

      <div class="fs-xs mb-2">
        <span class="text-muted">Settlement Bank:</span> <strong class="text-dark" id="drawerBankName">—</strong>
        <span class="text-muted ms-3">Account #:</span> <strong class="text-primary font-monospace" id="drawerBankAcc">—</strong>
      </div>

      <div id="drawerVouchersList" class="d-flex flex-column gap-2 mt-2">
        <!-- Dynamically rendered vouchers -->
      </div>
    </div>
  </div>

  <div class="offcanvas-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="offcanvas">Close Drawer</button>
    <div class="d-flex gap-2">
      <a href="#" id="drawerPrint2307Btn" target="_blank" class="btn btn-sm btn-outline-teal" style="color: #0d9488; border-color: #0d9488; display: none;">
        <i class="ph ph-printer me-1"></i> Print BIR 2307
      </a>
      <button type="button" id="drawerPrepareBtn" class="btn btn-sm btn-primary" onclick="triggerDrawerPrepare()">
        <i class="ph ph-credit-card me-1"></i> Prepare Payment Voucher
      </button>
    </div>
  </div>
</div>

<!-- Modal: Prepare Disbursement Voucher -->
<div class="modal fade" id="prepareVoucherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-light border-bottom py-3 px-4">
        <h5 class="modal-title font-weight-bold mb-0"><i class="ph ph-credit-card me-2 text-primary"></i>Prepare Disbursement Voucher</h5>
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
        <div class="modal-footer bg-light border-top py-2 px-4">
          <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold"><i class="ph ph-check me-1"></i> Submit Voucher for Approval</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let currentDrawerData = null;

function openVoucherDrawer(data) {
  if (!data) return;
  currentDrawerData = data;

  document.getElementById('drawerBillNumber').textContent = data.bill_number;
  document.getElementById('drawerInvoiceNumber').textContent = 'Inv: ' + (data.vendor_invoice || 'N/A');
  document.getElementById('drawerVendorName').textContent = data.vendor_name;
  document.getElementById('drawerVendorTin').textContent = 'TIN: ' + (data.vendor_tin || 'N/A');
  document.getElementById('drawerVendorAddress').innerHTML = '<i class="ph ph-map-pin me-1"></i>' + (data.vendor_address || 'Registered Address not configured');
  document.getElementById('drawerTaxType').textContent = data.vendor_tax_type;
  document.getElementById('drawerBillDate').textContent = data.bill_date;
  document.getElementById('drawerDueDate').textContent = data.due_date;
  document.getElementById('drawerGrossAmount').textContent = data.gross;
  document.getElementById('drawerBalanceDue').textContent = data.balance;

  const statusBadge = document.getElementById('drawerStatusBadge');
  statusBadge.textContent = data.status;
  statusBadge.className = 'badge ' + (data.status === 'PAID' ? 'bg-success text-white' : (data.status === 'APPROVED' ? 'bg-info text-dark' : 'bg-secondary text-white'));

  // Accounting Distribution
  document.getElementById('distDrExpense').textContent = data.net_base;
  document.getElementById('distDrVat').textContent = data.input_vat;
  document.getElementById('distCrEwt').textContent = data.ewt;
  document.getElementById('distCrAp').textContent = data.net_payable;
  document.getElementById('distTotalDebit').textContent = data.gross;
  document.getElementById('distTotalCredit').textContent = data.gross;

  // 3-Way Match
  document.getElementById('drawerPoNumber').textContent = data.po_number;
  document.getElementById('drawerGrnNumber').textContent = data.grn_number;
  document.getElementById('drawerInvRef').textContent = data.vendor_invoice;
  document.getElementById('drawerPoAmount').textContent = data.po_amount;
  document.getElementById('drawerGrnAmount').textContent = data.grn_amount;
  document.getElementById('drawerInvAmount').textContent = data.gross;
  document.getElementById('drawerMatchStatus').textContent = data.match_status;
  document.getElementById('drawerApprover').textContent = data.approver_name;
  document.getElementById('drawerApprovedAt').textContent = data.approved_at;

  // BIR 2307 Certificate
  const certBtn = document.getElementById('drawerPrint2307Btn');
  if (data.cert_data) {
    document.getElementById('drawerCertNo').textContent = data.cert_data.cert_no || 'BIR-2307-AUTO';
    document.getElementById('drawerCertAtc').textContent = data.cert_data.atc || 'WC158';
    document.getElementById('drawerCertTaxBase').textContent = data.cert_data.tax_base;
    document.getElementById('drawerCertWithheld').textContent = data.cert_data.tax_withheld;
    document.getElementById('drawerCertStatus').textContent = 'Generated & Linked';
    certBtn.href = data.cert_data.print_url;
    certBtn.style.display = 'inline-flex';
  } else {
    document.getElementById('drawerCertNo').textContent = 'Pending Certification';
    document.getElementById('drawerCertAtc').textContent = 'WC158';
    document.getElementById('drawerCertTaxBase').textContent = data.gross;
    document.getElementById('drawerCertWithheld').textContent = data.ewt;
    document.getElementById('drawerCertStatus').textContent = 'Tax Computed';
    certBtn.style.display = 'none';
  }

  // Settlement & Bank
  document.getElementById('drawerBankName').textContent = data.vendor_bank;
  document.getElementById('drawerBankAcc').textContent = data.vendor_bank_acc;

  const vouchersContainer = document.getElementById('drawerVouchersList');
  vouchersContainer.innerHTML = '';
  if (data.disbursement_vouchers && data.disbursement_vouchers.length > 0) {
    document.getElementById('drawerSettlementCount').textContent = data.disbursement_vouchers.length + ' Vouchers';
    data.disbursement_vouchers.forEach(v => {
      vouchersContainer.innerHTML += `
        <div class="p-2 border rounded bg-light-subtle d-flex justify-content-between align-items-center">
          <div>
            <span class="font-monospace fw-bold text-primary">${v.voucher_number}</span>
            <span class="text-muted ms-2">${v.bank} (${v.method})</span>
          </div>
          <div class="text-end">
            <span class="fw-bold text-dark font-monospace">${v.amount}</span>
            <span class="badge bg-info-subtle text-info border ms-1">${v.status}</span>
          </div>
        </div>
      `;
    });
  } else {
    document.getElementById('drawerSettlementCount').textContent = '0 Vouchers';
    vouchersContainer.innerHTML = '<span class="text-muted fs-xs">No disbursement vouchers generated yet.</span>';
  }

  const prepareBtn = document.getElementById('drawerPrepareBtn');
  if (data.balance_raw > 0) {
    prepareBtn.style.display = 'inline-flex';
  } else {
    prepareBtn.style.display = 'none';
  }

  const drawerEl = document.getElementById('voucherDetailsDrawer');
  if (drawerEl && window.bootstrap) {
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    drawerInstance.show();
  }
}

function triggerDrawerPrepare() {
  if (!currentDrawerData) return;
  const drawerEl = document.getElementById('voucherDetailsDrawer');
  if (drawerEl && window.bootstrap) {
    const drawerInstance = bootstrap.Offcanvas.getInstance(drawerEl);
    if (drawerInstance) drawerInstance.hide();
  }
  openPrepareVoucherModal(currentDrawerData.id, currentDrawerData.bill_number, currentDrawerData.vendor_name, currentDrawerData.balance_raw);
}

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
</script>
@endpush
