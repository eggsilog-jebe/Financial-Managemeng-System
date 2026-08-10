@extends('layouts.app')

@section('title', 'Purchase Bills - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'purchase-bills')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Purchase Bills</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Purchase Bills</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Purchase Bills statement...');"><i class="ph ph-download-simple me-1"></i> Export Bills</button>
      <button id="btnLogBill" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#logBillModal"><i class="ph ph-plus me-1"></i> Log Bill</button>
    </div>
  </div>

  <!-- Summary Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Medical Gases &amp; Consumables</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-first-aid-kit fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱124,500.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Bio-Hazard &amp; Environmental Services</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-trash fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱88,000.00</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Facility Power &amp; Water Utilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-lightning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱645,000.00</h4>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Status Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="billStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Payment Status:</label>
          <select id="billStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Statuses</option>
            <option value="unpaid">Unpaid</option>
            <option value="partially paid">Partially Paid</option>
            <option value="paid">Paid</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="billSearchInput" class="form-control form-control-sm" placeholder="Search bill ID, supplier, item...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="purchaseBillsTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bill ID</th>
              <th>Supplier Name</th>
              <th>Supply Category / Item Description</th>
              <th>Bill Date</th>
              <th>Due Date</th>
              <th class="text-end">Total Amount (₱)</th>
              <th>Payment Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @php
              $bills = [
                [
                  'id' => 'BILL-2026-801',
                  'supplier' => 'Linde Medical Gases',
                  'item' => 'ICU Oxygen Cylinder Tank Refill Batch',
                  'date' => '2026-08-04',
                  'due' => '2026-09-03',
                  'amount' => '₱54,000.00',
                  'status' => 'Unpaid',
                  'badge' => 'bg-warning-subtle text-warning',
                  'desc' => 'High-purity liquid oxygen refills for ICU ward manifolds & operating room emergency tanks.'
                ],
                [
                  'id' => 'BILL-2026-802',
                  'supplier' => 'Meralco Power Distribution',
                  'item' => 'Hospital Electrical Grid Substation Utility Bill',
                  'date' => '2026-08-01',
                  'due' => '2026-08-16',
                  'amount' => '₱437,000.00',
                  'status' => 'Unpaid',
                  'badge' => 'bg-warning-subtle text-warning',
                  'desc' => 'Monthly high-voltage power grid usage for 24/7 hospital HVAC, surgical operating lights, and main vault.'
                ],
                [
                  'id' => 'BILL-2026-803',
                  'supplier' => 'BioClean Environmental Services',
                  'item' => 'Bio-Hazardous Medical Waste Disposal & Incineration',
                  'date' => '2026-08-02',
                  'due' => '2026-09-01',
                  'amount' => '₱88,000.00',
                  'status' => 'Paid',
                  'badge' => 'bg-success-subtle text-success',
                  'desc' => 'DENR-compliant hazardous medical waste collection, sharps disposal, and bio-incineration services.'
                ],
                [
                  'id' => 'BILL-2026-804',
                  'supplier' => 'Manila Water Commercial',
                  'item' => 'Medical Complex Filtration & Utility Water Meter',
                  'date' => '2026-08-03',
                  'due' => '2026-08-18',
                  'amount' => '₱208,000.00',
                  'status' => 'Partially Paid',
                  'badge' => 'bg-info-subtle text-info',
                  'desc' => 'Potable water supply & sterile filtration system main pipe utility bill for July 2026 cycle.'
                ],
                [
                  'id' => 'BILL-2026-805',
                  'supplier' => 'Surgical Supplies & Implants Co.',
                  'item' => 'Orthopedic Titanium Screws & Surgical Sutures Batch #4',
                  'date' => '2026-08-05',
                  'due' => '2026-09-04',
                  'amount' => '₱70,500.00',
                  'status' => 'Unpaid',
                  'badge' => 'bg-warning-subtle text-warning',
                  'desc' => 'Sterilized titanium bone screws, joint replacement plates, and surgical suture spools for OR Suite 3.'
                ],
              ];
            @endphp

            @foreach($bills as $b)
            <tr class="bill-row" style="cursor: pointer;" data-status="{{ strtolower($b['status']) }}" onclick="openBillDetailsModal({{ json_encode($b) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $b['id'] }}</span></td>
              <td class="fw-semibold text-dark">{{ $b['supplier'] }}</td>
              <td><span class="text-dark">{{ $b['item'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $b['date'] }}</td>
              <td class="font-monospace fs-xs">{{ $b['due'] }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $b['amount'] }}</td>
              <td><span class="badge {{ $b['badge'] }}">{{ $b['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Bill Details" onclick="openBillDetailsModal({{ json_encode($b) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="billSummaryText">Showing {{ count($bills) }} Purchase Bills</span>
      <nav aria-label="Bills Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Purchase Bill Details (Executive Design) -->
<div class="modal fade" id="billDetailsModal" tabindex="-1" aria-labelledby="billDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailBillId">BILL-2026-801</span>
            <span class="badge bg-warning-subtle text-warning" id="detailBillStatus">Unpaid</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailBillSupplier">Linde Medical Gases</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Bill Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailBillAmount">₱54,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Payment Due Date</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailBillDue">2026-09-03</h4>
            </div>
          </div>
        </div>

        <!-- Particulars Description -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Supply Item &amp; Bill Breakdown</h6>
          <h6 class="fw-bold text-primary mb-2" id="detailBillItem">Oxygen Cylinder Tank Refill Batch</h6>
          <p class="small text-muted mb-0 lh-base" id="detailBillDesc">High-purity liquid oxygen refills for ICU ward manifolds &amp; operating room emergency tanks.</p>
        </div>

        <!-- Master Data -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-receipt me-1 text-primary"></i> Bill Master Information</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bill Issue Date</span>
              <span class="font-monospace fw-bold text-dark" id="detailBillDate">2026-08-04</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Associated PO / Contract</span>
              <span class="font-monospace text-primary fw-bold">PO-88231</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Payment Category</span>
              <span class="badge bg-light text-dark border">Recurring Supply Order</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-primary"><i class="ph ph-receipt me-1"></i> Convert to AP Voucher</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Log New Bill -->
<div class="modal fade" id="logBillModal" tabindex="-1" aria-labelledby="logBillModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="logBillModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Log New Purchase Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="logBillForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Bill ID Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalBillId" class="form-control form-control-sm font-monospace" placeholder="e.g. BILL-2026-806" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Supplier Name <span class="text-danger">*</span></label>
              <select id="modalBillSupplier" class="form-select form-select-sm" required>
                <option value="Linde Medical Gases">Linde Medical Gases</option>
                <option value="Meralco Power Distribution">Meralco Power Distribution</option>
                <option value="BioClean Environmental Services">BioClean Environmental Services</option>
                <option value="Manila Water Commercial">Manila Water Commercial</option>
                <option value="Surgical Supplies & Implants Co.">Surgical Supplies &amp; Implants Co.</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Bill Issue Date <span class="text-danger">*</span></label>
              <input type="date" id="modalBillDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Due Date <span class="text-danger">*</span></label>
              <input type="date" id="modalBillDue" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Supply Item / Particulars <span class="text-danger">*</span></label>
              <input type="text" id="modalBillItem" class="form-control form-control-sm" placeholder="e.g. Oxygen Cylinder Refill" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalBillAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="15000.00" required>
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-semibold">Detailed Description <span class="text-danger">*</span></label>
              <input type="text" id="modalBillDesc" class="form-control form-control-sm" placeholder="e.g. Monthly ICU liquid oxygen refills" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Log Purchase Bill</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openBillDetailsModal(b) {
  if (!b) return;

  document.getElementById('detailBillId').textContent = b.id || 'BILL-000';
  document.getElementById('detailBillSupplier').textContent = b.supplier || 'Supplier Name';
  document.getElementById('detailBillAmount').textContent = b.amount || '₱0.00';
  document.getElementById('detailBillDue').textContent = b.due || '-';
  document.getElementById('detailBillItem').textContent = b.item || 'Supply Item';
  document.getElementById('detailBillDesc').textContent = b.desc || 'No description provided.';
  document.getElementById('detailBillDate').textContent = b.date || '-';

  const statusEl = document.getElementById('detailBillStatus');
  if (statusEl) {
    statusEl.textContent = b.status;
    statusEl.className = 'badge ' + (b.badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('billDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const statusSelect = document.getElementById('billStatusSelect');
  const searchInput = document.getElementById('billSearchInput');
  const summaryText = document.getElementById('billSummaryText');
  const btnLogBill = document.getElementById('btnLogBill');

  if (btnLogBill) {
    btnLogBill.addEventListener('click', function() {
      const modalEl = document.getElementById('logBillModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterBills() {
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.bill-row');
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
      summaryText.textContent = `Showing ${visibleCount} Purchase Bill${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noBillsRow');
    const tbody = document.querySelector('#purchaseBillsTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noBillsRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No purchase bills found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (statusSelect) statusSelect.addEventListener('change', filterBills);
  if (searchInput) {
    searchInput.addEventListener('input', filterBills);
    searchInput.addEventListener('keyup', filterBills);
  }

  const logBillForm = document.getElementById('logBillForm');
  if (logBillForm) {
    logBillForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const idVal = document.getElementById('modalBillId').value;
      const supplierVal = document.getElementById('modalBillSupplier').value;
      const dateVal = document.getElementById('modalBillDate').value;
      const dueVal = document.getElementById('modalBillDue').value;
      const itemVal = document.getElementById('modalBillItem').value;
      const rawAmount = parseFloat(document.getElementById('modalBillAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const descVal = document.getElementById('modalBillDesc').value;

      const billObj = {
        id: idVal,
        supplier: supplierVal,
        item: itemVal,
        date: dateVal,
        due: dueVal,
        amount: formattedAmount,
        status: 'Unpaid',
        badge: 'bg-warning-subtle text-warning',
        desc: descVal
      };

      const tbody = document.querySelector('#purchaseBillsTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'bill-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-status', 'unpaid');

        newRow.onclick = function() { openBillDetailsModal(billObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${idVal}</span></td>
          <td class="fw-semibold text-dark">${supplierVal}</td>
          <td><span class="text-dark">${itemVal}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td class="font-monospace fs-xs">${dueVal}</td>
          <td class="text-end fw-bold text-dark font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-warning-subtle text-warning">Unpaid</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Bill Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Bill Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openBillDetailsModal(billObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('logBillModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      logBillForm.reset();
      filterBills();
    });
  }

  filterBills();
});
</script>
@endpush
