@extends('layouts.app')

@section('title', 'Invoicing & Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'billing')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; Corporate Billing Invoices</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Billing Log...');"><i class="ph ph-file-arrow-down me-1"></i> Export Billing Log</button>
      <button id="btnCreateInvoice" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createInvoiceModal"><i class="ph ph-plus-circle me-1"></i> Create Patient Invoice</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Invoices Issued Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">28 Invoices</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending HMO Claims</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,220,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">PhilHealth Coverage Claims</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-first-aid fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱650,500.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Paid &amp; Settled Invoices</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">91.4%</h4>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" id="invoiceSearchInput" class="form-control bg-light border-start-0" placeholder="Search Invoice No, Patient Name, or HMO...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="payorClassSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Payor Classes</option>
            <option value="hmo">HMO Corporate Guarantor</option>
            <option value="patient">Direct Patient Cash</option>
            <option value="philhealth">PhilHealth Statutory</option>
          </select>
        </div>
        <div class="col-md-4">
          <select id="invoiceStatusSelect" class="form-select form-select-sm bg-light">
            <option value="" selected>All Invoice Statuses</option>
            <option value="pending claim">Pending Claim</option>
            <option value="paid & cleared">Paid &amp; Cleared</option>
            <option value="partially paid">Partially Paid</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="invoiceTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Invoice Ref</th>
              <th>Date</th>
              <th>Patient / Payor Name</th>
              <th>Payor Type</th>
              <th class="text-end">Total Billed (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $invoices = [
                [
                  'ref' => 'INV-2026-0881',
                  'date' => '2026-08-08',
                  'patient' => 'Juan Dela Cruz',
                  'sub' => 'Patient ID: PAT-88412 — Inpatient Surgery',
                  'payor' => 'Maxicare HMO',
                  'payor_type' => 'hmo',
                  'badge' => 'bg-info-subtle text-info',
                  'billed' => '₱45,800.00',
                  'status' => 'Pending Claim',
                  'status_badge' => 'bg-warning-subtle text-warning',
                  'status_icon' => 'ph-clock',
                  'room' => '₱15,000.00',
                  'or_fee' => '₱18,000.00',
                  'pharma' => '₱12,800.00'
                ],
                [
                  'ref' => 'INV-2026-0882',
                  'date' => '2026-08-07',
                  'patient' => 'PhilHealth Universal Coverage',
                  'sub' => 'Government Statutory Benefit Deductions Pool',
                  'payor' => 'PhilHealth Statutory',
                  'payor_type' => 'philhealth',
                  'badge' => 'bg-success-subtle text-success',
                  'billed' => '₱820,000.00',
                  'status' => 'Pending Claim',
                  'status_badge' => 'bg-warning-subtle text-warning',
                  'status_icon' => 'ph-clock',
                  'room' => '₱300,000.00',
                  'or_fee' => '₱250,000.00',
                  'pharma' => '₱270,000.00'
                ],
                [
                  'ref' => 'INV-2026-0883',
                  'date' => '2026-08-06',
                  'patient' => 'Maria Santos',
                  'sub' => 'Patient ID: PAT-88435 — Surgical Recovery Suite',
                  'payor' => 'Intellicare HMO',
                  'payor_type' => 'hmo',
                  'badge' => 'bg-info-subtle text-info',
                  'billed' => '₱85,200.00',
                  'status' => 'Partially Paid',
                  'status_badge' => 'bg-info-subtle text-info',
                  'status_icon' => 'ph-hourglass',
                  'room' => '₱35,000.00',
                  'or_fee' => '₱30,200.00',
                  'pharma' => '₱20,000.00'
                ],
                [
                  'ref' => 'INV-2026-0884',
                  'date' => '2026-08-05',
                  'patient' => 'Ricardo Reyes',
                  'sub' => 'Patient ID: PAT-88450 — ICU Room 02',
                  'payor' => 'Self-Pay Direct Cash',
                  'payor_type' => 'patient',
                  'badge' => 'bg-primary-subtle text-primary',
                  'billed' => '₱42,000.00',
                  'status' => 'Paid & Cleared',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'room' => '₱20,000.00',
                  'or_fee' => '₱12,000.00',
                  'pharma' => '₱10,000.00'
                ],
              ];
            @endphp

            @foreach($invoices as $inv)
            <tr class="invoice-row" style="cursor: pointer;" data-payor="{{ $inv['payor_type'] }}" data-status="{{ strtolower($inv['status']) }}" onclick="openInvoiceDetailsModal({{ json_encode($inv) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $inv['ref'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $inv['date'] }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $inv['patient'] }}</div>
                <span class="fs-xs text-muted">{{ $inv['sub'] }}</span>
              </td>
              <td><span class="badge {{ $inv['badge'] }}">{{ $inv['payor'] }}</span></td>
              <td class="text-end font-monospace fw-bold text-dark">{{ $inv['billed'] }}</td>
              <td><span class="badge {{ $inv['status_badge'] }}"><i class="ph {{ $inv['status_icon'] }} me-1"></i> {{ $inv['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Invoice Breakdown" onclick="openInvoiceDetailsModal({{ json_encode($inv) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="invoiceSummaryText">Showing {{ count($invoices) }} Billing Invoices</span>
      <nav aria-label="Invoices Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Patient Billing Invoice Details (Executive Design) -->
<div class="modal fade" id="invoiceDetailsModal" tabindex="-1" aria-labelledby="invoiceDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailInvRef">INV-2026-0881</span>
            <span class="badge bg-info-subtle text-info" id="detailInvPayor">Maxicare HMO</span>
            <span class="badge bg-warning-subtle text-warning" id="detailInvStatus"><i class="ph ph-clock me-1"></i> Pending Claim</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailInvPatient">Juan Dela Cruz</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Amounts Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Billed Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailInvBilled">₱45,800.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Invoice Date</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailInvDate">2026-08-08</h4>
            </div>
          </div>
        </div>

        <!-- Particulars Breakdown Table -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-list-numbers me-1 text-primary"></i> Itemized Charges Breakdown</h6>
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle fs-xs mb-0">
              <thead class="table-light">
                <tr>
                  <th>Medical Particular / Department</th>
                  <th class="text-end">Amount (₱)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Room &amp; Board Accommodation Charges</td>
                  <td class="text-end font-monospace" id="detailRoomFee">₱15,000.00</td>
                </tr>
                <tr>
                  <td>Surgical Operating Room &amp; Anesthesia Fee</td>
                  <td class="text-end font-monospace" id="detailOrFee">₱18,000.00</td>
                </tr>
                <tr>
                  <td>Pharmacy Prescription Dispensing &amp; Supplies</td>
                  <td class="text-end font-monospace" id="detailPharmaFee">₱12,800.00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sub Details -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Admission Profile &amp; Reference</h6>
          <p class="small text-muted mb-0" id="detailInvSub">Patient ID: PAT-88412 — Inpatient Surgery</p>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Printing Billing Statement...');"><i class="ph ph-printer me-1"></i> Print Official Statement</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Patient Invoice -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createInvoiceModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Patient Billing Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createInvoiceForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Invoice Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalInvRef" class="form-control form-control-sm font-monospace" placeholder="e.g. INV-2026-0885" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient Name <span class="text-danger">*</span></label>
              <input type="text" id="modalInvPatient" class="form-control form-control-sm" placeholder="e.g. Maria Clara Santos" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient ID / Room Details <span class="text-danger">*</span></label>
              <input type="text" id="modalInvSub" class="form-control form-control-sm" placeholder="e.g. Patient ID: PAT-99201 (Room 304)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payor Classification <span class="text-danger">*</span></label>
              <select id="modalInvPayorClass" class="form-select form-select-sm" required>
                <option value="hmo">HMO Corporate Guarantor</option>
                <option value="patient">Self-Pay Direct Cash</option>
                <option value="philhealth">PhilHealth Coverage</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Invoice Date <span class="text-danger">*</span></label>
              <input type="date" id="modalInvDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Gross Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalInvBilled" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="15000.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Itemized Particulars</label>
              <textarea id="modalInvParticulars" class="form-control form-control-sm" rows="2" placeholder="Room charges, ICU monitoring, lab tests, pharmacy dispensing..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Billing Invoice</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openInvoiceDetailsModal(inv) {
  if (!inv) return;

  document.getElementById('detailInvRef').textContent = inv.ref || 'INV-000';
  document.getElementById('detailInvPatient').textContent = inv.patient || 'Patient Name';
  document.getElementById('detailInvPayor').textContent = inv.payor || 'Payor';
  document.getElementById('detailInvBilled').textContent = inv.billed || '₱0.00';
  document.getElementById('detailInvDate').textContent = inv.date || '-';
  document.getElementById('detailInvSub').textContent = inv.sub || 'Inpatient Admission';
  document.getElementById('detailRoomFee').textContent = inv.room || '₱0.00';
  document.getElementById('detailOrFee').textContent = inv.or_fee || '₱0.00';
  document.getElementById('detailPharmaFee').textContent = inv.pharma || '₱0.00';

  const statusEl = document.getElementById('detailInvStatus');
  if (statusEl) {
    statusEl.textContent = inv.status;
    statusEl.className = 'badge ' + (inv.status_badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('invoiceDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('invoiceSearchInput');
  const payorClassSelect = document.getElementById('payorClassSelect');
  const invoiceStatusSelect = document.getElementById('invoiceStatusSelect');
  const summaryText = document.getElementById('invoiceSummaryText');
  const btnCreateInvoice = document.getElementById('btnCreateInvoice');

  if (btnCreateInvoice) {
    btnCreateInvoice.addEventListener('click', function() {
      const modalEl = document.getElementById('createInvoiceModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterInvoices() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedPayor = payorClassSelect ? payorClassSelect.value.toLowerCase() : '';
    const selectedStatus = invoiceStatusSelect ? invoiceStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.invoice-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowPayor = row.getAttribute('data-payor') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchPayor = !selectedPayor || rowPayor.includes(selectedPayor);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchPayor && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Billing Invoice${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noInvoicesRow');
    const tbody = document.querySelector('#invoiceTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noInvoicesRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No billing invoices found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterInvoices);
    searchInput.addEventListener('keyup', filterInvoices);
  }
  if (payorClassSelect) payorClassSelect.addEventListener('change', filterInvoices);
  if (invoiceStatusSelect) invoiceStatusSelect.addEventListener('change', filterInvoices);

  const createInvoiceForm = document.getElementById('createInvoiceForm');
  if (createInvoiceForm) {
    createInvoiceForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const refVal = document.getElementById('modalInvRef').value;
      const patientVal = document.getElementById('modalInvPatient').value;
      const subVal = document.getElementById('modalInvSub').value;
      const payorClassVal = document.getElementById('modalInvPayorClass').value;
      const dateVal = document.getElementById('modalInvDate').value;
      const rawBilled = parseFloat(document.getElementById('modalInvBilled').value || 0);
      const formattedBilled = '₱' + rawBilled.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let payorLabel = 'Self-Pay Direct Cash';
      let badgeStyle = 'bg-primary-subtle text-primary';
      if (payorClassVal === 'hmo') {
        payorLabel = 'HMO Corporate Guarantor';
        badgeStyle = 'bg-info-subtle text-info';
      } else if (payorClassVal === 'philhealth') {
        payorLabel = 'PhilHealth Statutory';
        badgeStyle = 'bg-success-subtle text-success';
      }

      const invoiceObj = {
        ref: refVal,
        date: dateVal,
        patient: patientVal,
        sub: subVal,
        payor: payorLabel,
        payor_type: payorClassVal,
        badge: badgeStyle,
        billed: formattedBilled,
        status: 'Pending Claim',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock',
        room: '₱' + (rawBilled * 0.4).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
        or_fee: '₱' + (rawBilled * 0.35).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
        pharma: '₱' + (rawBilled * 0.25).toLocaleString('en-PH', { minimumFractionDigits: 2 })
      };

      const tbody = document.querySelector('#invoiceTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'invoice-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-payor', payorClassVal);
        newRow.setAttribute('data-status', 'pending claim');

        newRow.onclick = function() { openInvoiceDetailsModal(invoiceObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${refVal}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td>
            <div class="fw-semibold text-dark">${patientVal}</div>
            <span class="fs-xs text-muted">${subVal}</span>
          </td>
          <td><span class="badge ${badgeStyle}">${payorLabel}</span></td>
          <td class="text-end font-monospace fw-bold text-dark">${formattedBilled}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Claim</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Invoice Breakdown"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Invoice Breakdown"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openInvoiceDetailsModal(invoiceObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createInvoiceModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createInvoiceForm.reset();
      filterInvoices();
    });
  }

  filterInvoices();
});
</script>
@endpush
