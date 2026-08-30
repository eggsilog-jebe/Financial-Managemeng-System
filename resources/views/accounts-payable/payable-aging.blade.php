@extends('layouts.app')

@section('title', 'Payable Aging Schedule - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'payable-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable Aging Schedule</h1>
      <p class="text-muted fs-xs mb-0">Track all outstanding supplier bills and monitor upcoming payment deadlines grouped by age (Current, 1–30 Days, 31–60 Days, 61–90 Days, 90+ Days Overdue).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoices & Vouchers', 'Vendor Management']" 
          :tables="['vendor_invoices', 'vendors']"
          description="Tracks outstanding supplier payables categorized by vendor credit terms and aging brackets." 
      />
      <a href="{{ route('ap.payable-aging.export', ['as_of_date' => $asOfDate, 'aging_basis' => $agingBasis ?? 'due_date']) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Aging CSV
      </a>
      <button class="btn btn-secondary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Schedule
      </button>
    </div>
  </div>

  <!-- Aging Metric Cards (5 Buckets) -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Current (Not Due)</span>
          <span class="badge bg-success-subtle text-success p-1 rounded-2"><i class="ph ph-check-circle fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-dark mb-0 font-monospace">₱{{ number_format((float) $totalCurrent, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">1 - 30 Days</span>
          <span class="badge bg-info-subtle text-info p-1 rounded-2"><i class="ph ph-clock fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-dark mb-0 font-monospace">₱{{ number_format((float) $total1To30, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">31 - 60 Days</span>
          <span class="badge bg-warning-subtle text-warning p-1 rounded-2"><i class="ph ph-hourglass fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-warning mb-0 font-monospace">₱{{ number_format((float) $total31To60, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">61 - 90 Days</span>
          <span class="badge bg-danger-subtle text-danger p-1 rounded-2"><i class="ph ph-warning fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-danger mb-0 font-monospace">₱{{ number_format((float) $total61To90, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">90+ Days Overdue</span>
          <span class="badge bg-dark-subtle text-dark p-1 rounded-2"><i class="ph ph-shield-warning fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-danger mb-0 font-monospace">₱{{ number_format((float) $total90Plus, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-primary-subtle text-primary border-primary">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="fw-semibold small">Grand Total AP</span>
          <span class="badge bg-primary text-white p-1 rounded-2"><i class="ph ph-trend-down fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-primary mb-0 font-monospace">₱{{ number_format((float) $grandTotalPayable, 2) }}</h5>
      </div>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('ap.payable-aging') }}" id="agingFilterForm" class="row g-3 align-items-end">
        <!-- As-Of Cutoff Date -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold mb-1">As-Of Cutoff Date:</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><i class="ph ph-calendar"></i></span>
            <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}" onchange="this.form.submit()">
          </div>
        </div>

        <!-- 1. Segmented Aging Basis Toggle: Due Date (Default) vs Bill Date -->
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-1 d-flex align-items-center gap-1">
            <i class="ph ph-sliders-horizontal"></i> Aging Basis:
          </label>
          <div class="btn-group btn-group-sm w-100" role="group" aria-label="Aging Basis">
            <input type="radio" class="btn-check" name="aging_basis" id="basisDueDate" value="due_date" {{ ($agingBasis ?? 'due_date') === 'due_date' ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="btn btn-outline-primary" for="basisDueDate">Due Date (Default)</label>

            <input type="radio" class="btn-check" name="aging_basis" id="basisBillDate" value="bill_date" {{ ($agingBasis ?? 'due_date') === 'bill_date' ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="btn btn-outline-primary" for="basisBillDate">Bill Date (Invoice)</label>
          </div>
        </div>

        <!-- Filter by Vendor -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold mb-1">Filter by Vendor:</label>
          <select name="vendor_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- All Vendors &amp; Suppliers --</option>
            @foreach($allVendors as $v)
              <option value="{{ $v->id }}" {{ $selectedVendorId == $v->id ? 'selected' : '' }}>{{ $v->name }} ({{ $v->code }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2 text-end">
          <a href="{{ route('ap.payable-aging') }}" class="btn btn-sm btn-light border w-100" title="Reset to Today"><i class="ph ph-arrow-counter-clockwise me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Aging Schedule Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="agingTable" class="table table-hover align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Vendor Code</th>
              <th>Supplier Legal Name</th>
              <th>TIN</th>
              <th>Terms</th>
              <th class="text-end">Current (₱)</th>
              <th class="text-end">1 - 30 Days (₱)</th>
              <th class="text-end">31 - 60 Days (₱)</th>
              <th class="text-end">61 - 90 Days (₱)</th>
              <th class="text-end">90+ Days (₱)</th>
              <th class="text-end fw-bold">Total Due (₱)</th>
              <!-- 1. ACTIONS Column Header -->
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vendors as $v)
            <tr>
              <td><span class="font-monospace fw-bold text-primary">{{ $v['vendor_code'] }}</span></td>
              <td>
                <div class="fw-bold text-dark">{{ $v['vendor_name'] }}</div>
                <div class="text-muted fs-xs">{{ $v['bills_count'] }} open invoice(s)</div>
              </td>
              <td><span class="font-monospace text-muted">{{ $v['tin'] }}</span></td>
              <td><span class="badge bg-light text-dark border">Net {{ $v['terms'] }} Days</span></td>
              <td class="text-end font-monospace text-success">₱{{ number_format((float) $v['current'], 2) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $v['days_1_30'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_31_60'] > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">₱{{ number_format((float) $v['days_31_60'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_61_90'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">₱{{ number_format((float) $v['days_61_90'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_90_plus'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">₱{{ number_format((float) $v['days_90_plus'], 2) }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format((float) $v['total_due'], 2) }}</td>
              <td class="text-end">
                <!-- 1. View Breakdown Action Button -->
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs text-nowrap" onclick="openVendorAgingDrawer({{ json_encode($v) }})">
                  <i class="ph ph-magnifying-glass me-1"></i> View Breakdown
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="11" class="text-center py-5 text-muted">
                <i class="ph ph-check-circle fs-1 d-block mb-2 text-success"></i>
                No open Accounts Payable records found for this cutoff date.
              </td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="4" class="text-uppercase">Total AP Liabilities:</td>
              <td class="text-end font-monospace text-success">₱{{ number_format((float) $totalCurrent, 2) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $total1To30, 2) }}</td>
              <td class="text-end font-monospace text-warning">₱{{ number_format((float) $total31To60, 2) }}</td>
              <td class="text-end font-monospace text-danger">₱{{ number_format((float) $total61To90, 2) }}</td>
              <td class="text-end font-monospace text-danger">₱{{ number_format((float) $total90Plus, 2) }}</td>
              <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) $grandTotalPayable, 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Reporting {{ count($vendors) }} Vendor Accounts as of {{ $asOfDate }} (Basis: {{ ($agingBasis ?? 'due_date') === 'bill_date' ? 'Invoice Bill Date' : 'Payment Due Date' }})</span>
    </div>
  </div>
</div>

<!-- ========================================================= -->
<!-- 2. Slide-Over Drawer: Vendor Aging Breakdown & Action Queue -->
<!-- ========================================================= -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="vendorAgingDrawer" style="width: 860px; max-width: 95vw;" aria-labelledby="vendorAgingDrawerLabel">
  <div class="offcanvas-header border-bottom bg-light py-3 px-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-1 fs-xs" id="drawerVendorCode">VND-0000</span>
        <span class="badge bg-light text-dark border" id="drawerTermsBadge">Net 30 Days</span>
      </div>
      <h5 class="offcanvas-title font-weight-bold text-dark mb-0" id="vendorAgingDrawerLabel">Supplier Payable Aging Breakdown</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body p-4 bg-light-subtle">
    <!-- Vendor Master Summary Card -->
    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h5 class="fw-bold text-dark mb-1 fs-6" id="drawerVendorName">Supplier Legal Name</h5>
          <span class="fs-xs font-monospace text-muted d-block" id="drawerVendorTin">TIN: -</span>
          <span class="fs-xs text-muted d-block mt-1" id="drawerVendorAddress"><i class="ph ph-map-pin me-1"></i>-</span>
        </div>
        <div class="text-end">
          <span class="fs-xs text-muted d-block">Total Outstanding Liability</span>
          <h4 class="fw-bold text-danger font-monospace mb-0" id="drawerTotalDue">₱0.00</h4>
        </div>
      </div>

      <div class="row g-2 pt-2 border-top fs-xs">
        <div class="col-md-4">
          <span class="text-muted d-block">Tax Registration</span>
          <span class="badge bg-primary-subtle text-primary border" id="drawerTaxType">VAT-Registered</span>
        </div>
        <div class="col-md-4">
          <span class="text-muted d-block">Settlement Bank</span>
          <strong class="text-dark" id="drawerBankName">—</strong>
        </div>
        <div class="col-md-4">
          <span class="text-muted d-block">Bank Account Number</span>
          <strong class="text-primary font-monospace" id="drawerBankAcc">—</strong>
        </div>
      </div>
    </div>

    <!-- Aging Distribution Matrix Card -->
    <div class="row g-2 mb-3">
      <div class="col">
        <div class="bg-white border rounded p-2 text-center">
          <span class="text-muted d-block" style="font-size: 10px;">CURRENT</span>
          <span class="fw-bold text-success font-monospace fs-xs" id="drawerBucketCurrent">₱0.00</span>
        </div>
      </div>
      <div class="col">
        <div class="bg-white border rounded p-2 text-center">
          <span class="text-muted d-block" style="font-size: 10px;">1 - 30 DAYS</span>
          <span class="fw-bold text-dark font-monospace fs-xs" id="drawerBucket30">₱0.00</span>
        </div>
      </div>
      <div class="col">
        <div class="bg-white border rounded p-2 text-center">
          <span class="text-muted d-block" style="font-size: 10px;">31 - 60 DAYS</span>
          <span class="fw-bold text-warning font-monospace fs-xs" id="drawerBucket60">₱0.00</span>
        </div>
      </div>
      <div class="col">
        <div class="bg-white border rounded p-2 text-center">
          <span class="text-muted d-block" style="font-size: 10px;">61 - 90 DAYS</span>
          <span class="fw-bold text-danger font-monospace fs-xs" id="drawerBucket90">₱0.00</span>
        </div>
      </div>
      <div class="col">
        <div class="bg-white border rounded p-2 text-center">
          <span class="text-muted d-block" style="font-size: 10px;">90+ DAYS</span>
          <span class="fw-bold text-danger font-monospace fs-xs" id="drawerBucket90p">₱0.00</span>
        </div>
      </div>
    </div>

    <!-- Itemized Constituent Unpaid Vouchers Table Card -->
    <div class="card border rounded-3 bg-white shadow-sm overflow-hidden mb-3">
      <div class="card-header bg-light border-bottom p-3 d-flex justify-content-between align-items-center">
        <span class="fs-xs text-uppercase fw-bold text-dark d-flex align-items-center gap-1">
          <i class="ph ph-receipt fs-5 text-primary"></i> Itemized Unpaid Constituent Vouchers
        </span>
        <span class="badge bg-secondary-subtle text-secondary fs-xs" id="drawerBillsCount">0 Vouchers</span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0 fs-xs" id="drawerVouchersTable">
          <thead class="table-light">
            <tr>
              <th style="width: 35px;" class="text-center">
                <input type="checkbox" id="selectAllVouchers" class="form-check-input" onchange="toggleSelectAllVouchers(this)">
              </th>
              <th>APV # &amp; Supplier Invoice</th>
              <th>Bill &amp; Due Date</th>
              <th>Days Overdue</th>
              <th class="text-end">Gross (₱)</th>
              <th class="text-end">EWT (₱)</th>
              <th class="text-end fw-bold">Balance Due (₱)</th>
            </tr>
          </thead>
          <tbody id="drawerBillsTbody">
            <!-- Dynamic vouchers inserted by JavaScript -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Drawer Footer Actions -->
  <div class="offcanvas-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="offcanvas">Close</button>
    <div class="d-flex gap-2 align-items-center">
      <button type="button" class="btn btn-secondary btn-sm" onclick="printVendorStatement()">
        <i class="ph ph-printer me-1"></i> Print Statement
      </button>
      <button type="button" class="btn btn-sm text-white px-3 fw-semibold" style="background-color: #0d9488;" onclick="queueSelectedForPayment()">
        <i class="ph ph-credit-card me-1"></i> Queue Selected for Payment Approval
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let currentVendorDrawer = null;

function openVendorAgingDrawer(vendor) {
  if (!vendor) return;
  currentVendorDrawer = vendor;

  document.getElementById('drawerVendorCode').textContent = vendor.vendor_code || 'VND-000';
  document.getElementById('drawerVendorName').textContent = vendor.vendor_name || 'Supplier Legal Name';
  document.getElementById('drawerVendorTin').textContent = 'TIN: ' + (vendor.tin || 'N/A');
  document.getElementById('drawerVendorAddress').innerHTML = '<i class="ph ph-map-pin me-1"></i>' + (vendor.registered_address || 'Official registered address not set');
  document.getElementById('drawerTermsBadge').textContent = 'Net ' + (vendor.terms || 30) + ' Days';
  document.getElementById('drawerTaxType').textContent = (vendor.tax_type === 'NON_VAT') ? 'Non-VAT' : 'VAT-Registered';
  document.getElementById('drawerBankName').textContent = vendor.bank_name || '—';
  document.getElementById('drawerBankAcc').textContent = vendor.bank_account_number || '—';

  const totalDueNum = parseFloat(vendor.total_due || 0);
  document.getElementById('drawerTotalDue').textContent = '₱' + totalDueNum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  document.getElementById('drawerBucketCurrent').textContent = '₱' + parseFloat(vendor.current || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('drawerBucket30').textContent = '₱' + parseFloat(vendor.days_1_30 || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('drawerBucket60').textContent = '₱' + parseFloat(vendor.days_31_60 || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('drawerBucket90').textContent = '₱' + parseFloat(vendor.days_61_90 || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('drawerBucket90p').textContent = '₱' + parseFloat(vendor.days_90_plus || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  const tbody = document.getElementById('drawerBillsTbody');
  tbody.innerHTML = '';

  const bills = vendor.bills || [];
  document.getElementById('drawerBillsCount').textContent = bills.length + ' Voucher' + (bills.length !== 1 ? 's' : '');

  if (bills.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No unpaid vouchers for this supplier.</td></tr>';
  } else {
    bills.forEach(b => {
      let overdueBadge = '';
      if (b.days_overdue <= 0) {
        overdueBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Current (Not Due)</span>';
      } else if (b.days_overdue <= 30) {
        overdueBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">' + b.days_overdue + 'D Overdue</span>';
      } else {
        overdueBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">' + b.days_overdue + 'D Overdue</span>';
      }

      tbody.innerHTML += `
        <tr>
          <td class="text-center">
            <input type="checkbox" class="form-check-input voucher-select-item" data-id="${b.id}" data-ref="${b.bill_number}" data-balance="${b.balance_due}" checked>
          </td>
          <td>
            <div class="fw-bold font-monospace text-primary">${b.bill_number}</div>
            <div class="text-muted" style="font-size: 10px;">Inv: ${b.vendor_invoice_number || 'N/A'}</div>
          </td>
          <td>
            <div>${b.bill_date}</div>
            <div class="text-muted" style="font-size: 10px;">Due: ${b.due_date}</div>
          </td>
          <td>${overdueBadge}</td>
          <td class="text-end font-monospace">₱${parseFloat(b.gross_amount).toFixed(2)}</td>
          <td class="text-end font-monospace text-muted">₱${parseFloat(b.ewt_amount).toFixed(2)}</td>
          <td class="text-end font-monospace fw-bold text-dark">₱${parseFloat(b.balance_due).toFixed(2)}</td>
        </tr>
      `;
    });
  }

  const selectAll = document.getElementById('selectAllVouchers');
  if (selectAll) selectAll.checked = true;

  const drawerEl = document.getElementById('vendorAgingDrawer');
  if (drawerEl && window.bootstrap) {
    const drawerInstance = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    drawerInstance.show();
  }
}

function toggleSelectAllVouchers(masterCheckbox) {
  const checkboxes = document.querySelectorAll('.voucher-select-item');
  checkboxes.forEach(cb => {
    cb.checked = masterCheckbox.checked;
  });
}

function queueSelectedForPayment() {
  const selected = document.querySelectorAll('.voucher-select-item:checked');
  if (selected.length === 0) {
    alert('Please select at least one voucher to queue for payment approval.');
    return;
  }

  const billIds = Array.from(selected).map(cb => cb.getAttribute('data-id'));
  const totalQueuedAmt = Array.from(selected).reduce((acc, cb) => acc + parseFloat(cb.getAttribute('data-balance') || 0), 0);

  const confirmMsg = `Queue ${selected.length} voucher(s) totaling ₱${totalQueuedAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} for payment disbursement approval?`;
  if (confirm(confirmMsg)) {
    // Route to Invoices & Vouchers Hub or Payment Approvals
    window.location.href = "{{ route('ap.invoices') }}?search=" + encodeURIComponent(currentVendorDrawer?.vendor_name || '');
  }
}

function printVendorStatement() {
  window.print();
}
</script>
@endpush
