@extends('layouts.app')

@section('title', 'Invoices & Vouchers - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'invoices')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Invoices &amp; Vouchers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable Vouchers (3-Way Matching)</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting AP Vouchers report...');"><i class="ph ph-download-simple me-1"></i> Export Vouchers</button>
      <button id="btnCreateVoucher" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createVoucherModal"><i class="ph ph-plus me-1"></i> Create AP Voucher</button>
    </div>
  </div>

  <!-- Summary Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Vouchers Pending</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">18 Vouchers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">3-Way Matched (Ready)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">14 Vouchers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">3-Way Mismatched</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 Vouchers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Net EWT Withheld</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,500.00</h4>
      </div>
    </div>
  </div>

  <!-- Vouchers Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Status Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="voucherStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Voucher Status:</label>
          <select id="voucherStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Statuses</option>
            <option value="approved">Approved</option>
            <option value="pending review">Pending Review</option>
            <option value="on hold">On Hold</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="voucherSearchInput" class="form-control form-control-sm" placeholder="Search voucher ref, vendor, PO...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="voucherTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref</th>
              <th>Vendor Name</th>
              <th>PO &amp; GRN Ref</th>
              <th>Invoice Date</th>
              <th>Due Date</th>
              <th class="text-end">Gross Amount (₱)</th>
              <th class="text-end">EWT (₱)</th>
              <th class="text-end">Net Payable (₱)</th>
              <th>3-Way Match</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vouchers ?? [] as $v)
            @php
              $vArr = is_array($v) ? $v : [
                'ref' => $v->reference_number ?? 'APV-N/A', 'vendor' => $v->vendor_name ?? 'N/A',
                'po' => $v->purchase_order_number ?? 'N/A', 'grn' => $v->grn_number ?? 'N/A',
                'inv_date' => $v->invoice_date ? $v->invoice_date->format('Y-m-d') : 'N/A',
                'due_date' => $v->due_date ? $v->due_date->format('Y-m-d') : 'N/A',
                'gross' => '₱' . number_format($v->gross_amount ?? 0, 2),
                'ewt' => '-₱' . number_format($v->ewt_amount ?? 0, 2),
                'net' => '₱' . number_format($v->net_payable ?? 0, 2),
                'match' => $v->match_status ?? 'Pending Match', 'match_badge' => 'bg-warning-subtle text-warning',
                'match_icon' => 'ph-clock', 'status' => $v->status ?? 'Pending',
                'status_badge' => 'bg-warning-subtle text-warning', 'particulars' => $v->particulars ?? 'N/A',
                'po_amt' => '₱' . number_format($v->po_amount ?? 0, 2), 'grn_qty' => $v->grn_quantity ?? 'N/A',
              ];
            @endphp
            <tr class="voucher-row" style="cursor: pointer;" data-status="{{ strtolower($vArr['status']) }}" onclick="openVoucherDetailsModal({{ json_encode($vArr) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $vArr['ref'] }}</span></td>
              <td class="fw-semibold text-dark">{{ $vArr['vendor'] }}</td>
              <td>
                <div class="fs-xs"><span class="font-monospace text-primary">{{ $vArr['po'] }}</span> / <span class="font-monospace text-muted">{{ $vArr['grn'] }}</span></div>
              </td>
              <td class="font-monospace fs-xs">{{ $vArr['inv_date'] }}</td>
              <td class="font-monospace fs-xs">{{ $vArr['due_date'] }}</td>
              <td class="text-end font-monospace">{{ $vArr['gross'] }}</td>
              <td class="text-end text-muted font-monospace">{{ $vArr['ewt'] }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $vArr['net'] }}</td>
              <td><span class="badge {{ $vArr['match_badge'] }}"><i class="ph {{ $vArr['match_icon'] }}"></i> {{ $vArr['match'] }}</span></td>
              <td><span class="badge {{ $vArr['status_badge'] }}">{{ $vArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View 3-Way Match Details" onclick="openVoucherDetailsModal({{ json_encode($vArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="11" class="text-center py-4 text-muted">No AP vouchers recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="voucherSummaryText">Showing {{ count($vouchers ?? []) }} AP Vouchers</span>
      <nav aria-label="Vouchers Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Voucher 3-Way Match Details (Clean & Executive Design) -->
<div class="modal fade" id="voucherDetailsModal" tabindex="-1" aria-labelledby="voucherDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <!-- Header -->
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailVoucherRef">APV-2026-091</span>
            <span class="badge bg-success-subtle text-success" id="detailVoucherMatch"><i class="ph ph-check-double"></i> 3-Way Matched</span>
            <span class="badge bg-primary-subtle text-primary" id="detailVoucherStatus">Approved</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailVoucherVendor">PharmaCorp Philippines</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Gross Invoice Amount</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailGrossAmount">₱145,000.00</h5>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">BIR EWT Withheld (1%)</span>
              <h5 class="fw-bold text-muted mb-0 font-monospace" id="detailEwtAmount">-₱1,450.00</h5>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Net Payable Amount</span>
              <h5 class="fw-bold text-success mb-0 font-monospace" id="detailNetAmount">₱143,550.00</h5>
            </div>
          </div>
        </div>

        <!-- Particulars Description -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Voucher Particulars</h6>
          <p class="small text-muted mb-0 lh-base" id="detailParticulars">Bulk IV Fluids &amp; Antibiotics Injectables Delivery</p>
        </div>

        <!-- 3-Way Matching Verification Audit Box -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-scales me-1 text-primary"></i> 3-Way Match Verification Audit</h6>
          <div class="row g-3 fs-xs">
            <div class="col-md-4 border-end">
              <span class="text-muted d-block mb-1">1. Purchase Order (PO)</span>
              <span class="font-monospace fw-bold text-primary d-block" id="detailPoRef">PO-88210</span>
              <span class="text-dark fw-medium" id="detailPoAmt">Valued at ₱145,000.00</span>
            </div>
            <div class="col-md-4 border-end">
              <span class="text-muted d-block mb-1">2. Receiving Note (GRN)</span>
              <span class="font-monospace fw-bold text-dark d-block" id="detailGrnRef">GRN-4410</span>
              <span class="text-success fw-medium" id="detailGrnQty">100% Received</span>
            </div>
            <div class="col-md-4">
              <span class="text-muted d-block mb-1">3. Vendor Invoice Date</span>
              <span class="font-monospace fw-bold text-dark d-block" id="detailInvDate">2026-08-01</span>
              <span class="text-muted" id="detailDueDate">Due: 2026-08-31</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ap.ap-approvals') }}" class="btn btn-sm btn-primary"><i class="ph ph-shield-check me-1"></i> Go to Payment Approvals</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create New AP Voucher -->
<div class="modal fade" id="createVoucherModal" tabindex="-1" aria-labelledby="createVoucherModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createVoucherModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create New AP Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createVoucherForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Voucher Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalVoucherRef" class="form-control form-control-sm font-monospace" placeholder="e.g. APV-2026-095" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Vendor Name <span class="text-danger">*</span></label>
              <select id="modalVoucherVendor" class="form-select form-select-sm" required>
                <option value="PharmaCorp Philippines">PharmaCorp Philippines</option>
                <option value="MedTech Diagnostics">MedTech Diagnostics</option>
                <option value="Linde Medical Gases">Linde Medical Gases</option>
                <option value="Surgical Supplies & Implants">Surgical Supplies &amp; Implants</option>
                <option value="Meralco Power Distribution">Meralco Power Distribution</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Purchase Order (PO Ref) <span class="text-danger">*</span></label>
              <input type="text" id="modalVoucherPo" class="form-control form-control-sm font-monospace" placeholder="e.g. PO-88240" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Goods Received Note (GRN Ref) <span class="text-danger">*</span></label>
              <input type="text" id="modalVoucherGrn" class="form-control form-control-sm font-monospace" placeholder="e.g. GRN-4462" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Invoice Date <span class="text-danger">*</span></label>
              <input type="date" id="modalVoucherInvDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Due Date <span class="text-danger">*</span></label>
              <input type="date" id="modalVoucherDueDate" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Gross Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalVoucherGross" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="10000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR EWT Rate <span class="text-danger">*</span></label>
              <select id="modalVoucherEwtRate" class="form-select form-select-sm" required>
                <option value="0.01">1% Goods EWT</option>
                <option value="0.02">2% Services EWT</option>
                <option value="0.00">0% Exempt</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-semibold">Voucher Particulars / Description <span class="text-danger">*</span></label>
              <input type="text" id="modalVoucherParticulars" class="form-control form-control-sm" placeholder="e.g. ICU Supplies Purchase" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit AP Voucher</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openVoucherDetailsModal(v) {
  if (!v) return;

  document.getElementById('detailVoucherRef').textContent = v.ref || 'APV-000';
  document.getElementById('detailVoucherVendor').textContent = v.vendor || 'Vendor Name';
  document.getElementById('detailGrossAmount').textContent = v.gross || '₱0.00';
  document.getElementById('detailEwtAmount').textContent = v.ewt || '-₱0.00';
  document.getElementById('detailNetAmount').textContent = v.net || '₱0.00';
  document.getElementById('detailParticulars').textContent = v.particulars || 'No description provided';
  document.getElementById('detailPoRef').textContent = v.po || 'PO-000';
  document.getElementById('detailPoAmt').textContent = 'Valued at ' + (v.po_amt || v.gross);
  document.getElementById('detailGrnRef').textContent = v.grn || 'GRN-000';
  document.getElementById('detailGrnQty').textContent = v.grn_qty || 'Received';
  document.getElementById('detailInvDate').textContent = v.inv_date || '-';
  document.getElementById('detailDueDate').textContent = 'Due: ' + (v.due_date || '-');

  const matchEl = document.getElementById('detailVoucherMatch');
  if (matchEl) {
    matchEl.textContent = v.match;
    matchEl.className = 'badge ' + (v.match_badge || 'bg-success-subtle text-success');
  }

  const statusEl = document.getElementById('detailVoucherStatus');
  if (statusEl) {
    statusEl.textContent = v.status;
    statusEl.className = 'badge ' + (v.status_badge || 'bg-primary-subtle text-primary');
  }

  const modalEl = document.getElementById('voucherDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const statusSelect = document.getElementById('voucherStatusSelect');
  const searchInput = document.getElementById('voucherSearchInput');
  const summaryText = document.getElementById('voucherSummaryText');
  const btnCreateVoucher = document.getElementById('btnCreateVoucher');

  if (btnCreateVoucher) {
    btnCreateVoucher.addEventListener('click', function() {
      const modalEl = document.getElementById('createVoucherModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterVouchers() {
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.voucher-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchStatus = !selectedStatus || rowStatus === selectedStatus;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} AP Voucher${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noVouchersRow');
    const tbody = document.querySelector('#voucherTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noVouchersRow';
        emptyRow.innerHTML = `<td colspan="11" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No vouchers found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (statusSelect) statusSelect.addEventListener('change', filterVouchers);
  if (searchInput) {
    searchInput.addEventListener('input', filterVouchers);
    searchInput.addEventListener('keyup', filterVouchers);
  }

  const createVoucherForm = document.getElementById('createVoucherForm');
  if (createVoucherForm) {
    createVoucherForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const refVal = document.getElementById('modalVoucherRef').value;
      const vendorVal = document.getElementById('modalVoucherVendor').value;
      const poVal = document.getElementById('modalVoucherPo').value;
      const grnVal = document.getElementById('modalVoucherGrn').value;
      const invDateVal = document.getElementById('modalVoucherInvDate').value;
      const dueDateVal = document.getElementById('modalVoucherDueDate').value;
      const grossRaw = parseFloat(document.getElementById('modalVoucherGross').value || 0);
      const ewtRate = parseFloat(document.getElementById('modalVoucherEwtRate').value || 0);

      const ewtRaw = grossRaw * ewtRate;
      const netRaw = grossRaw - ewtRaw;

      const formattedGross = '₱' + grossRaw.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedEwt = '-₱' + ewtRaw.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedNet = '₱' + netRaw.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const particularsVal = document.getElementById('modalVoucherParticulars').value;

      const voucherObj = {
        ref: refVal,
        vendor: vendorVal,
        po: poVal,
        grn: grnVal,
        inv_date: invDateVal,
        due_date: dueDateVal,
        gross: formattedGross,
        ewt: formattedEwt,
        net: formattedNet,
        match: '3-Way Matched',
        match_badge: 'bg-success-subtle text-success',
        match_icon: 'ph-check-double',
        status: 'Pending Review',
        status_badge: 'bg-warning-subtle text-warning',
        particulars: particularsVal,
        po_amt: formattedGross,
        grn_qty: '100% Received'
      };

      const tbody = document.querySelector('#voucherTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'voucher-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-status', 'pending review');

        newRow.onclick = function() { openVoucherDetailsModal(voucherObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${refVal}</span></td>
          <td class="fw-semibold text-dark">${vendorVal}</td>
          <td>
            <div class="fs-xs"><span class="font-monospace text-primary">${poVal}</span> / <span class="font-monospace text-muted">${grnVal}</span></div>
          </td>
          <td class="font-monospace fs-xs">${invDateVal}</td>
          <td class="font-monospace fs-xs">${dueDateVal}</td>
          <td class="text-end font-monospace">${formattedGross}</td>
          <td class="text-end text-muted font-monospace">${formattedEwt}</td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedNet}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-double"></i> 3-Way Matched</span></td>
          <td><span class="badge bg-warning-subtle text-warning">Pending Review</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View 3-Way Match Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View 3-Way Match Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openVoucherDetailsModal(voucherObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createVoucherModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createVoucherForm.reset();
      filterVouchers();
    });
  }

  filterVouchers();
});
</script>
@endpush
