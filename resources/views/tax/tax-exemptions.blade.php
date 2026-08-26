@extends('layouts.app')

@section('title', 'Tax Exemptions - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-exemptions')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Exemptions</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Exemptions &amp; Special Relief Register</h1>
      <p class="text-muted fs-xs mb-0">Manage VAT and withholding tax exemptions for Senior Citizens, PWDs, government entities, and VAT-exempt prescription medications.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Exemption Audit Log...');"><i class="ph ph-file-arrow-down me-1"></i> Exemption Audit PDF</button>
      <button id="btnRegisterExemption" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#registerExemptionModal"><i class="ph ph-plus-circle me-1"></i> Register Exemption Rule</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Exemption Records</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($certificates ?? collect())->count() }} Certificates</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Base Amount</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($certificates ?? collect())->sum('tax_base'), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Withheld / Waived</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-piggy-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($certificates ?? collect())->sum('tax_withheld'), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Audit Compliance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-square fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100% Valid</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="exemptionCatSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Category:</label>
          <select id="exemptionCatSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Exemption Categories</option>
            <option value="meds">Essential Medicine (RA 11534)</option>
            <option value="senior">Senior Citizen &amp; PWD (RA 9994)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="exemptionStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="exemptionStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="enforced">Active &amp; Enforced</option>
            <option value="review">Under Review</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="exemptionSearchInput" class="form-control form-control-sm" placeholder="Search exemption class, legal basis...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="exemptionTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Exemption Class</th>
              <th>Legal Basis / Statutory Authority</th>
              <th>Certificate Ref</th>
              <th class="text-end">YTD Exempt Gross (₱)</th>
              <th class="text-end">Tax Saved (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($exemptions ?? [] as $e)
            <tr class="exemption-row" style="cursor: pointer;" data-cat="{{ is_array($e) ? $e['cat'] : '' }}" data-status="{{ strtolower(is_array($e) ? $e['status'] : $e->status) }}" onclick="openExemptionDetailsModal({{ json_encode($e) }})">
              <td>
                <div class="fw-bold text-dark">{{ is_array($e) ? $e['name'] : $e->name }}</div>
              </td>
              <td class="fs-xs text-muted">{{ is_array($e) ? $e['basis'] : ($e->legal_basis ?? 'N/A') }}</td>
              <td><span class="font-monospace text-primary fw-bold">{{ is_array($e) ? $e['ref'] : $e->reference_number }}</span></td>
              <td class="text-end font-monospace fw-semibold">{{ is_array($e) ? $e['gross'] : ('₱' . number_format($e->exempt_gross, 2)) }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ is_array($e) ? $e['saved'] : ('₱' . number_format($e->tax_saved, 2)) }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> {{ is_array($e) ? $e['status'] : $e->status }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Exemption Details" onclick="openExemptionDetailsModal({{ json_encode($e) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No tax exemptions registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="exemptionSummaryText">Showing {{ count($exemptions ?? []) }} Tax Exemptions</span>
      <nav aria-label="Exemption Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Exemption Details (Executive Design) -->
<div class="modal fade" id="exemptionDetailsModal" tabindex="-1" aria-labelledby="exemptionDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailExemptionRef">BIR-CERT-2026-EX01</span>
            <span class="badge bg-success-subtle text-success" id="detailExemptionStatus"><i class="ph ph-check-circle me-1"></i> Enforced</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailExemptionName">RA 11534 (CREATE Act - Essential Medicines)</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">YTD Exempt Gross Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailExemptionGross">₱1,450,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Tax Waived / Saved</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailExemptionSaved">₱174,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-primary"></i> Legal Basis &amp; Statutory Authority</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Government Implementing Circular</span>
              <span class="font-monospace fw-bold text-dark" id="detailExemptionBasis">BIR Revenue Regulation 04-2021</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Scope of Medical Exemption</span>
              <span class="text-muted" id="detailExemptionDesc">Diabetes, Hypertension &amp; Oncology Drugs</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; BIR Legal Basis Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Statutory Ruling Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Verified by BIR Tax Auditor</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-EX-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Tax Exemption Ruling Brief...');"><i class="ph ph-file-text me-1"></i> Export Exemption Audit</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Register Tax Exemption Rule -->
<div class="modal fade" id="registerExemptionModal" tabindex="-1" aria-labelledby="registerExemptionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="registerExemptionModalLabel"><i class="ph ph-shield-check me-2 text-primary"></i>Register Statutory Tax Exemption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="registerExemptionForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Exemption Class Title <span class="text-danger">*</span></label>
              <input type="text" id="modalExemptionName" class="form-control form-control-sm" placeholder="e.g. Non-Profit Hospital Income Exemption" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Legal Basis / Statutory Law <span class="text-danger">*</span></label>
              <input type="text" id="modalExemptionBasis" class="form-control form-control-sm" placeholder="e.g. NIRC Section 30(E)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR Exemption Certificate Ref No. <span class="text-danger">*</span></label>
              <input type="text" id="modalExemptionRef" class="form-control form-control-sm font-monospace" placeholder="e.g. BIR-CERT-2026-EX03" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">YTD Exempt Gross Base (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalExemptionGross" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" value="500000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Exemption Rule</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openExemptionDetailsModal(e) {
  if (!e) return;

  document.getElementById('detailExemptionName').textContent = e.name || 'Exemption Name';
  document.getElementById('detailExemptionRef').textContent = e.ref || 'BIR-CERT-000';
  document.getElementById('detailExemptionBasis').textContent = e.basis || '-';
  document.getElementById('detailExemptionDesc').textContent = e.desc || '-';
  document.getElementById('detailExemptionGross').textContent = e.gross || '₱0.00';
  document.getElementById('detailExemptionSaved').textContent = e.saved || '₱0.00';

  const statusEl = document.getElementById('detailExemptionStatus');
  if (statusEl) {
    statusEl.textContent = e.status;
    statusEl.className = 'badge ' + (e.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('exemptionDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('exemptionSearchInput');
  const catSelect = document.getElementById('exemptionCatSelect');
  const statusSelect = document.getElementById('exemptionStatusSelect');
  const summaryText = document.getElementById('exemptionSummaryText');
  const btnRegisterExemption = document.getElementById('btnRegisterExemption');

  if (btnRegisterExemption) {
    btnRegisterExemption.addEventListener('click', function() {
      const modalEl = document.getElementById('registerExemptionModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterExemptions() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.exemption-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-cat') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCat = !selectedCat || rowCat.includes(selectedCat);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCat && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Tax Exemption${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noExemptionRow');
    const tbody = document.querySelector('#exemptionTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noExemptionRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No exemptions found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterExemptions);
    searchInput.addEventListener('keyup', filterExemptions);
  }
  if (catSelect) catSelect.addEventListener('change', filterExemptions);
  if (statusSelect) statusSelect.addEventListener('change', filterExemptions);

  const registerExemptionForm = document.getElementById('registerExemptionForm');
  if (registerExemptionForm) {
    registerExemptionForm.addEventListener('submit', function(ex) {
      ex.preventDefault();

      const nameVal = document.getElementById('modalExemptionName').value;
      const basisVal = document.getElementById('modalExemptionBasis').value;
      const refVal = document.getElementById('modalExemptionRef').value;
      const rawGross = parseFloat(document.getElementById('modalExemptionGross').value || 0);
      const rawSaved = rawGross * 0.12;

      const formattedGross = '₱' + rawGross.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedSaved = '₱' + rawSaved.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      const exObj = {
        name: nameVal,
        desc: 'Statutory VAT Exemption Category',
        cat: 'meds',
        basis: basisVal,
        ref: refVal,
        gross: formattedGross,
        saved: formattedSaved,
        status: 'Enforced',
        status_badge: 'bg-success-subtle text-success'
      };

      const tbody = document.querySelector('#exemptionTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'exemption-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-cat', 'meds');
        newRow.setAttribute('data-status', 'enforced');

        newRow.onclick = function() { openExemptionDetailsModal(exObj); };

        newRow.innerHTML = `
          <td>
            <div class="fw-bold text-dark">${nameVal}</div>
            <span class="fs-xs text-muted">Statutory VAT Exemption Category</span>
          </td>
          <td class="fs-xs text-muted">${basisVal}</td>
          <td><span class="font-monospace text-primary fw-bold">${refVal}</span></td>
          <td class="text-end font-monospace fw-semibold">${formattedGross}</td>
          <td class="text-end text-success fw-bold font-monospace">${formattedSaved}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Enforced</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Exemption Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Exemption Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openExemptionDetailsModal(exObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('registerExemptionModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      registerExemptionForm.reset();
      filterExemptions();
    });
  }

  filterExemptions();
});
</script>
@endpush
