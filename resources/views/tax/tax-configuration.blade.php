@extends('layouts.app')

@section('title', 'Tax Configuration - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-config')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Configuration</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Rates &amp; Statutory Configuration</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="standalone" 
          description="Master system accounting and statutory tax setup." 
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Syncing tax rates with BIR online portal...');"><i class="ph ph-arrow-counter-clockwise me-1"></i> Sync Tax Rates</button>
      <button id="btnAddTaxRule" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addTaxRuleModal"><i class="ph ph-plus-circle me-1"></i> Add Tax Rate Rule</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Configured Tax Rules</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($taxRules ?? collect())->count() }} Active Rules</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Tax Categories</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-user-stethoscope fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($taxRules ?? collect())->pluck('tax_type')->unique()->count() }} Categories</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Statutory Compliance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">BIR Compliant</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tax Engine Status</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Active &amp; Ready</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="taxCatSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Tax Category:</label>
          <select id="taxCatSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Tax Categories</option>
            <option value="ewt">Expanded Withholding Tax (EWT)</option>
            <option value="vat">Value Added Tax (VAT)</option>
            <option value="cit">Corporate Income Tax (CIT)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="taxStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="taxStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="active">Active Rules</option>
            <option value="inactive">Inactive Rules</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="taxSearchInput" class="form-control form-control-sm" placeholder="Search tax code, ATC, scope...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="taxRuleTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Tax Code &amp; Name</th>
              <th>ATC Code</th>
              <th>Category</th>
              <th class="text-end">Tax Rate (%)</th>
              <th>Applicable Scope</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($taxRules ?? [] as $r)
            @php
              $code = is_array($r) ? $r['code'] : $r->tax_code;
              $name = is_array($r) ? $r['name'] : $r->name;
              $atc = is_array($r) ? $r['atc'] : $r->atc_code;
              $category = is_array($r) ? $r['category'] : $r->category;
              $catType = is_array($r) ? $r['cat_type'] : $r->cat_type;
              $rate = is_array($r) ? $r['rate'] : number_format($r->rate, 1) . '%';
              $scope = is_array($r) ? $r['scope'] : $r->scope;
              $status = is_array($r) ? $r['status'] : $r->status;
              $rData = [
                'code' => $code,
                'name' => $name,
                'atc' => $atc,
                'category' => $category,
                'cat_type' => $catType,
                'rate' => $rate,
                'scope' => $scope,
                'status' => $status,
                'status_badge' => 'bg-success-subtle text-success'
              ];
            @endphp
            <tr class="tax-row" style="cursor: pointer;" onclick="openTaxRuleDetailsModal({{ json_encode($rData) }})">
              <td>
                <div class="fw-bold text-dark">{{ $name }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $code }}</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">{{ $atc }}</span></td>
              <td><span class="badge bg-info-subtle text-info">{{ $category }}</span></td>
              <td class="text-end font-monospace fw-bold text-danger">{{ $rate }}</td>
              <td class="fs-xs text-muted">{{ $scope }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> {{ $status }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Tax Rule Details" onclick="openTaxRuleDetailsModal({{ json_encode($rData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No tax rules configured in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="taxSummaryText">Showing {{ count($taxRules ?? []) }} Tax Rules</span>
      <nav aria-label="Tax Rule Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Tax Rule Details (Executive Design) -->
<div class="modal fade" id="taxRuleDetailsModal" tabindex="-1" aria-labelledby="taxRuleDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailTaxCode">TAX-EWT-DOC10</span>
            <span class="badge bg-success-subtle text-success" id="detailTaxStatus"><i class="ph ph-check-circle me-1"></i> Active Statutory Rule</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailTaxName">EWT - Professional Fees (Medical Consultants)</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">BIR ATC Code</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailTaxAtc">WI010</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Configured Tax Rate</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailTaxRate">10.0%</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-percent me-1 text-primary"></i> Category &amp; Regulatory Scope</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Tax Category</span>
              <span class="badge bg-info-subtle text-info" id="detailTaxCategory">Expanded Withholding Tax</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Applicable Statutory Scope</span>
              <span class="fw-semibold text-dark" id="detailTaxScope">Visiting Doctors &amp; Medical Consultants</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; BIR Regulations Compliance</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bureau of Internal Revenue Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> BIR Revenue Regulations RR 11-2018 Compliant</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-TAX-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Tax Rule Configuration Schedule...');"><i class="ph ph-file-text me-1"></i> Export Rule Audit</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Tax Rate Rule -->
<div class="modal fade" id="addTaxRuleModal" tabindex="-1" aria-labelledby="addTaxRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addTaxRuleModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add Statutory Tax Rate Rule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="addTaxRuleForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Rule Name <span class="text-danger">*</span></label>
              <input type="text" id="modalTaxName" class="form-control form-control-sm" placeholder="e.g. EWT - Medical Services 15%" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR ATC Code <span class="text-danger">*</span></label>
              <input type="text" id="modalTaxAtc" class="form-control form-control-sm font-monospace" placeholder="e.g. WI011" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Category <span class="text-danger">*</span></label>
              <select id="modalTaxCategory" class="form-select form-select-sm" required>
                <option value="ewt">Expanded Withholding Tax (EWT)</option>
                <option value="vat">Value Added Tax (VAT)</option>
                <option value="cit">Corporate Income Tax (CIT)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Rate Percentage (%) <span class="text-danger">*</span></label>
              <input type="number" id="modalTaxRate" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="10.00" value="15.00" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Applicable Scope / Regulatory Description <span class="text-danger">*</span></label>
              <input type="text" id="modalTaxScope" class="form-control form-control-sm" placeholder="e.g. Applicable to medical consultants with gross income > P3M" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Tax Rule</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openTaxRuleDetailsModal(r) {
  if (!r) return;

  document.getElementById('detailTaxName').textContent = r.name || 'Tax Rule Name';
  document.getElementById('detailTaxCode').textContent = r.code || 'TAX-000';
  document.getElementById('detailTaxAtc').textContent = r.atc || 'WI000';
  document.getElementById('detailTaxRate').textContent = r.rate || '0.0%';
  document.getElementById('detailTaxCategory').textContent = r.category || 'Tax Category';
  document.getElementById('detailTaxScope').textContent = r.scope || '-';

  const statusEl = document.getElementById('detailTaxStatus');
  if (statusEl) {
    statusEl.textContent = r.status;
    statusEl.className = 'badge ' + (r.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('taxRuleDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('taxSearchInput');
  const catSelect = document.getElementById('taxCatSelect');
  const statusSelect = document.getElementById('taxStatusSelect');
  const summaryText = document.getElementById('taxSummaryText');
  const btnAddTaxRule = document.getElementById('btnAddTaxRule');

  if (btnAddTaxRule) {
    btnAddTaxRule.addEventListener('click', function() {
      const modalEl = document.getElementById('addTaxRuleModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterTaxRules() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.tax-row');
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
      summaryText.textContent = `Showing ${visibleCount} Tax Rule${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noTaxRow');
    const tbody = document.querySelector('#taxRuleTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTaxRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No tax rules found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTaxRules);
    searchInput.addEventListener('keyup', filterTaxRules);
  }
  if (catSelect) catSelect.addEventListener('change', filterTaxRules);
  if (statusSelect) statusSelect.addEventListener('change', filterTaxRules);

  const addTaxRuleForm = document.getElementById('addTaxRuleForm');
  if (addTaxRuleForm) {
    addTaxRuleForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const nameVal = document.getElementById('modalTaxName').value;
      const atcVal = document.getElementById('modalTaxAtc').value;
      const catVal = document.getElementById('modalTaxCategory').value;
      const scopeVal = document.getElementById('modalTaxScope').value;
      const rawRate = parseFloat(document.getElementById('modalTaxRate').value || 0);
      const formattedRate = rawRate.toFixed(1) + '%';
      const nextCode = 'TAX-EWT-' + Math.floor(100 + Math.random() * 900);

      const ruleObj = {
        code: nextCode,
        name: nameVal,
        atc: atcVal,
        category: 'Expanded Withholding Tax',
        cat_type: catVal,
        rate: formattedRate,
        scope: scopeVal,
        status: 'Active',
        status_badge: 'bg-success-subtle text-success'
      };

      const tbody = document.querySelector('#taxRuleTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'tax-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-cat', catVal.toLowerCase());
        newRow.setAttribute('data-status', 'active');

        newRow.onclick = function() { openTaxRuleDetailsModal(ruleObj); };

        newRow.innerHTML = `
          <td>
            <div class="fw-bold text-dark">${nameVal}</div>
            <span class="fs-xs font-monospace text-muted">${nextCode}</span>
          </td>
          <td><span class="font-monospace text-primary fw-bold">${atcVal}</span></td>
          <td><span class="badge bg-info-subtle text-info">Expanded Withholding Tax</span></td>
          <td class="text-end font-monospace fw-bold text-danger">${formattedRate}</td>
          <td class="fs-xs text-muted">${scopeVal}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Tax Rule Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Tax Rule Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openTaxRuleDetailsModal(ruleObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('addTaxRuleModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      addTaxRuleForm.reset();
      filterTaxRules();
    });
  }

  filterTaxRules();
});
</script>
@endpush
