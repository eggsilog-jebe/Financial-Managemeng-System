@extends('layouts.app')

@section('title', 'Withholding Tax - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'withholding-tax')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Withholding Tax</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Withholding Tax Certificates (BIR Form 2307 / 2306)</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting BIR DAT E-Submission file...');"><i class="ph ph-file-arrow-down me-1"></i> Export BIR E-Submission</button>
      <button id="btnIssueCert" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#issueCertModal"><i class="ph ph-plus-circle me-1"></i> Issue 2307 Certificate</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Form 2307 Issued (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-file-text fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">42 Certificates</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Withheld (EWT)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-scissors fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱384,500.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Gross Income Base</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱3,845,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Remittance Status</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Due Aug 10</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="certFormSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Form Type:</label>
          <select id="certFormSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Form Types</option>
            <option value="2307">BIR Form 2307</option>
            <option value="2306">BIR Form 2306</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="certPayeeTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Payee Category:</label>
          <select id="certPayeeTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Payee Types</option>
            <option value="doctor">Medical Consultants</option>
            <option value="supplier">Suppliers &amp; Vendors</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="certSearchInput" class="form-control form-control-sm" placeholder="Search cert no, payee, TIN...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="certTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cert Number</th>
              <th>Payee / Doctor Name</th>
              <th>TIN Number</th>
              <th>ATC Code</th>
              <th class="text-end">Gross Income (₱)</th>
              <th class="text-end">Tax Withheld (₱)</th>
              <th>Form Type</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $certs = [
                [
                  'num' => 'C2307-2026-881',
                  'payee' => 'Dr. Roberto Gomez',
                  'role' => 'Visiting Cardiology Consultant',
                  'payee_type' => 'doctor',
                  'tin' => '102-391-441-000',
                  'atc' => 'WI010 (10%)',
                  'gross' => '₱120,000.00',
                  'tax' => '₱12,000.00',
                  'form' => 'BIR Form 2307',
                  'form_type' => '2307'
                ],
                [
                  'num' => 'C2307-2026-880',
                  'payee' => 'Metro Pharma Distributors Corp',
                  'role' => 'Medical Consumables Supplier',
                  'payee_type' => 'supplier',
                  'tin' => '008-992-101-000',
                  'atc' => 'WC158 (1%)',
                  'gross' => '₱450,000.00',
                  'tax' => '₱4,500.00',
                  'form' => 'BIR Form 2307',
                  'form_type' => '2307'
                ],
              ];
            @endphp

            @foreach($certs as $c)
            <tr class="cert-row" style="cursor: pointer;" data-form="{{ $c['form_type'] }}" data-payee="{{ $c['payee_type'] }}" onclick="openCertDetailsModal({{ json_encode($c) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $c['num'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $c['payee'] }}</div>
                <span class="fs-xs text-muted">{{ $c['role'] }}</span>
              </td>
              <td><span class="font-monospace text-muted">{{ $c['tin'] }}</span></td>
              <td><span class="badge bg-light text-dark border font-monospace">{{ $c['atc'] }}</span></td>
              <td class="text-end font-monospace fw-semibold">{{ $c['gross'] }}</td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $c['tax'] }}</td>
              <td><span class="badge bg-primary-subtle text-primary">{{ $c['form'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Certificate Details" onclick="openCertDetailsModal({{ json_encode($c) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="certSummaryText">Showing {{ count($certs) }} Tax Certificates</span>
      <nav aria-label="Certificate Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Certificate Details (Executive Design) -->
<div class="modal fade" id="certDetailsModal" tabindex="-1" aria-labelledby="certDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailCertNum">C2307-2026-881</span>
            <span class="badge bg-primary-subtle text-primary" id="detailCertForm">BIR Form 2307</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailCertPayee">Dr. Roberto Gomez</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Gross Income Base</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailCertGross">₱120,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Creditable Tax Withheld</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailCertTax">₱12,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-identification-card me-1 text-primary"></i> Taxpayer &amp; ATC Details</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Taxpayer Identification Number (TIN)</span>
              <span class="font-monospace fw-bold text-dark" id="detailCertTin">102-391-441-000</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Alphanumeric Tax Code (ATC)</span>
              <span class="font-monospace text-primary fw-bold" id="detailCertAtc">WI010 (10%)</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Payee Professional Category</span>
              <span class="text-muted" id="detailCertRole">Visiting Cardiology Consultant</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; BIR Form 2307 Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Electronic Signature &amp; Stamp:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Signed by Hospital Tax Compliance Officer</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-2307-2026-881 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Official BIR Form 2307 PDF...');"><i class="ph ph-printer me-1"></i> Print 2307 PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Form 2307 Certificate -->
<div class="modal fade" id="issueCertModal" tabindex="-1" aria-labelledby="issueCertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="issueCertModalLabel"><i class="ph ph-file-text me-2 text-primary"></i>Issue BIR Form 2307 Withholding Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="issueCertForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payee Name (Doctor / Supplier) <span class="text-danger">*</span></label>
              <input type="text" id="modalCertPayee" class="form-control form-control-sm" placeholder="e.g. Dr. Alejandro Santos" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Taxpayer Identification Number (TIN) <span class="text-danger">*</span></label>
              <input type="text" id="modalCertTin" class="form-control form-control-sm font-monospace" placeholder="000-000-000-000" value="105-882-991-000" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Alphanumeric Tax Code (ATC) <span class="text-danger">*</span></label>
              <select id="modalCertAtc" class="form-select form-select-sm" required>
                <option value="WI010 (10%)">WI010 - Professional Fees (10%)</option>
                <option value="WI011 (15%)">WI011 - Professional Fees (15%)</option>
                <option value="WC158 (1%)">WC158 - Purchase of Medical Goods (1%)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Gross Income Payment (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalCertGross" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="150000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-printer me-1"></i> Generate &amp; Sign 2307 PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openCertDetailsModal(c) {
  if (!c) return;

  document.getElementById('detailCertPayee').textContent = c.payee || 'Payee Name';
  document.getElementById('detailCertNum').textContent = c.num || 'C2307-000';
  document.getElementById('detailCertForm').textContent = c.form || 'Form 2307';
  document.getElementById('detailCertTin').textContent = c.tin || '000-000-000-000';
  document.getElementById('detailCertAtc').textContent = c.atc || 'WI000';
  document.getElementById('detailCertGross').textContent = c.gross || '₱0.00';
  document.getElementById('detailCertTax').textContent = c.tax || '₱0.00';
  document.getElementById('detailCertRole').textContent = c.role || '-';

  const modalEl = document.getElementById('certDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('certSearchInput');
  const formSelect = document.getElementById('certFormSelect');
  const payeeSelect = document.getElementById('certPayeeTypeSelect');
  const summaryText = document.getElementById('certSummaryText');
  const btnIssueCert = document.getElementById('btnIssueCert');

  if (btnIssueCert) {
    btnIssueCert.addEventListener('click', function() {
      const modalEl = document.getElementById('issueCertModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterCerts() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedForm = formSelect ? formSelect.value.toLowerCase() : '';
    const selectedPayee = payeeSelect ? payeeSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.cert-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowForm = row.getAttribute('data-form') || '';
      const rowPayee = row.getAttribute('data-payee') || '';
      const rowText = row.textContent.toLowerCase();

      const matchForm = !selectedForm || rowForm.includes(selectedForm);
      const matchPayee = !selectedPayee || rowPayee.includes(selectedPayee);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchForm && matchPayee && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Tax Certificate${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noCertRow');
    const tbody = document.querySelector('#certTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noCertRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No tax certificates found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterCerts);
    searchInput.addEventListener('keyup', filterCerts);
  }
  if (formSelect) formSelect.addEventListener('change', filterCerts);
  if (payeeSelect) payeeSelect.addEventListener('change', filterCerts);

  const issueCertForm = document.getElementById('issueCertForm');
  if (issueCertForm) {
    issueCertForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const payeeVal = document.getElementById('modalCertPayee').value;
      const tinVal = document.getElementById('modalCertTin').value;
      const atcVal = document.getElementById('modalCertAtc').value;
      const rawGross = parseFloat(document.getElementById('modalCertGross').value || 0);
      const ratePct = atcVal.includes('10%') ? 0.10 : (atcVal.includes('15%') ? 0.15 : 0.01);
      const rawTax = rawGross * ratePct;

      const formattedGross = '₱' + rawGross.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedTax = '₱' + rawTax.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextNum = 'C2307-2026-' + Math.floor(882 + Math.random() * 10);

      const certObj = {
        num: nextNum,
        payee: payeeVal,
        role: 'Consultant / Vendor',
        payee_type: 'doctor',
        tin: tinVal,
        atc: atcVal,
        gross: formattedGross,
        tax: formattedTax,
        form: 'BIR Form 2307',
        form_type: '2307'
      };

      const tbody = document.querySelector('#certTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'cert-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-form', '2307');
        newRow.setAttribute('data-payee', 'doctor');

        newRow.onclick = function() { openCertDetailsModal(certObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextNum}</span></td>
          <td>
            <div class="fw-semibold text-dark">${payeeVal}</div>
            <span class="fs-xs text-muted">Consultant / Vendor</span>
          </td>
          <td><span class="font-monospace text-muted">${tinVal}</span></td>
          <td><span class="badge bg-light text-dark border font-monospace">${atcVal}</span></td>
          <td class="text-end font-monospace fw-semibold">${formattedGross}</td>
          <td class="text-end text-danger fw-bold font-monospace">${formattedTax}</td>
          <td><span class="badge bg-primary-subtle text-primary">BIR Form 2307</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Certificate Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Certificate Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openCertDetailsModal(certObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('issueCertModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      issueCertForm.reset();
      filterCerts();
    });
  }

  filterCerts();
});
</script>
@endpush
