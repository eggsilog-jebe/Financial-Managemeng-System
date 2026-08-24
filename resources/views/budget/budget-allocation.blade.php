@extends('layouts.app')

@section('title', 'Budget Allocation - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'budget-allocation')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Allocation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Category Allocation</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Viewing Allocation Matrix...');"><i class="ph ph-sliders me-1"></i> Allocation Matrix</button>
      <button id="btnAllocateBudget" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#allocateBudgetModal"><i class="ph ph-plus-circle me-1"></i> Allocate Funds</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Fiscal Budget Cap</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Encumbered Commitments (POs)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-lock-key fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Actual Expended Funds</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Remaining Liquid Capacity</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="costCenterSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Cost Center:</label>
          <select id="costCenterSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Cost Centers</option>
            <option value="cc-101">CC-101 (Pharmacy)</option>
            <option value="cc-102">CC-102 (ICU Care)</option>
            <option value="cc-104">CC-104 (Facilities)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="expenditureCatSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Category:</label>
          <select id="expenditureCatSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Categories</option>
            <option value="medical">Medical Supplies</option>
            <option value="equipment">Equipment Maintenance</option>
            <option value="utilities">Electric &amp; Power</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="allocationSearchInput" class="form-control form-control-sm" placeholder="Search cost center, category...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="allocationTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cost Center</th>
              <th>Expenditure Category</th>
              <th class="text-end">Initial Cap (₱)</th>
              <th class="text-end">Encumbered POs (₱)</th>
              <th class="text-end">Actual Expended (₱)</th>
              <th class="text-end">Available Balance (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($allocations ?? [] as $a)
            @php
              $cc = is_array($a) ? $a['cc'] : $a->cost_center;
              $cat = is_array($a) ? $a['cat'] : $a->category;
              $sub = is_array($a) ? $a['sub'] : 'Category';
              $initial = is_array($a) ? $a['initial'] : ('₱' . number_format($a->allocated_amount, 2));
              $encumbered = is_array($a) ? $a['encumbered'] : ('₱' . number_format($a->encumbered_amount, 2));
              $expended = is_array($a) ? $a['expended'] : ('₱' . number_format($a->expended_amount, 2));
              $available = is_array($a) ? $a['available'] : ('₱' . number_format($a->allocated_amount - $a->expended_amount - $a->encumbered_amount, 2));
              $aData = [
                'cc' => $cc,
                'cat' => $cat,
                'sub' => $sub,
                'initial' => $initial,
                'encumbered' => $encumbered,
                'expended' => $expended,
                'available' => $available
              ];
            @endphp
            <tr class="allocation-row" style="cursor: pointer;" onclick="openAllocationDetailsModal({{ json_encode($aData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $cc }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $cat }}</div>
                <span class="fs-xs text-muted">{{ $sub }}</span>
              </td>
              <td class="text-end font-monospace fw-semibold">{{ $initial }}</td>
              <td class="text-end text-warning font-monospace">{{ $encumbered }}</td>
              <td class="text-end text-danger font-monospace">{{ $expended }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $available }}</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" onclick="openAllocationDetailsModal({{ json_encode($aData) }})"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No budget allocations configured in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="allocationSummaryText">Showing {{ count($allocations ?? []) }} Budget Allocations</span>
      <nav aria-label="Allocation Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Allocation Details (Executive Design) -->
<div class="modal fade" id="allocationDetailsModal" tabindex="-1" aria-labelledby="allocationDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAllocCc">CC-101</span>
            <span class="badge bg-primary-subtle text-primary"><i class="ph ph-sliders me-1"></i> Active Cost Center Allocation</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAllocCat">Pharmacy Medical Supplies</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Initial Cap</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailAllocInitial">₱25,000,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">PO Encumbered</span>
              <h5 class="fw-bold text-warning mb-0 font-monospace" id="detailAllocEncumbered">₱6,500,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Actual Expended</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailAllocExpended">₱12,800,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Free Balance</span>
              <h5 class="fw-bold text-success mb-0 font-monospace" id="detailAllocAvailable">₱5,700,000.00</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-sliders me-1 text-primary"></i> Sub-Account Breakdown</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Account Classification</span>
              <span class="font-monospace fw-bold text-dark" id="detailAllocSub">Medical Supplies &amp; Outpatient Drugs</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Fiscal Year Assignment</span>
              <span class="font-monospace fw-bold text-primary">FY 2026 Master Budget</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Encumbrance Control</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Commitment Control Lock:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Auto-Encumbrance Active</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Log:</span>
              <span class="font-monospace text-muted">LOG-ALLOC-CC-101 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Generating Encumbrance Audit Report...');"><i class="ph ph-file-text me-1"></i> Export Allocation Report</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Allocate Category Budget -->
<div class="modal fade" id="allocateBudgetModal" tabindex="-1" aria-labelledby="allocateBudgetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="allocateBudgetModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Allocate Budget to Cost Center</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="allocateBudgetForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Cost Center <span class="text-danger">*</span></label>
              <select id="modalAllocCc" class="form-select form-select-sm" required>
                <option value="CC-101">CC-101: Pharmacy &amp; Therapeutics</option>
                <option value="CC-102">CC-102: Emergency &amp; ICU Care</option>
                <option value="CC-104">CC-104: Facilities &amp; Utilities</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expenditure Category <span class="text-danger">*</span></label>
              <input type="text" id="modalAllocCat" class="form-control form-control-sm" placeholder="e.g. Medical Supplies & Consumables" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Allocation Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalAllocAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="15000000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Effective Period</label>
              <select class="form-select form-select-sm">
                <option value="annual">Full Fiscal Year 2026</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Allocation</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openAllocationDetailsModal(a) {
  if (!a) return;

  document.getElementById('detailAllocCc').textContent = a.cc || 'CC-000';
  document.getElementById('detailAllocCat').textContent = a.cat || 'Category';
  document.getElementById('detailAllocSub').textContent = a.sub || '-';
  document.getElementById('detailAllocInitial').textContent = a.initial || '₱0.00';
  document.getElementById('detailAllocEncumbered').textContent = a.encumbered || '₱0.00';
  document.getElementById('detailAllocExpended').textContent = a.expended || '₱0.00';
  document.getElementById('detailAllocAvailable').textContent = a.available || '₱0.00';

  const modalEl = document.getElementById('allocationDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('allocationSearchInput');
  const costCenterSelect = document.getElementById('costCenterSelect');
  const expenditureCatSelect = document.getElementById('expenditureCatSelect');
  const summaryText = document.getElementById('allocationSummaryText');
  const btnAllocateBudget = document.getElementById('btnAllocateBudget');

  if (btnAllocateBudget) {
    btnAllocateBudget.addEventListener('click', function() {
      const modalEl = document.getElementById('allocateBudgetModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterAllocations() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCc = costCenterSelect ? costCenterSelect.value.toLowerCase() : '';
    const selectedCat = expenditureCatSelect ? expenditureCatSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.allocation-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCc = row.getAttribute('data-cc') || '';
      const rowCat = row.getAttribute('data-cat') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCc = !selectedCc || rowCc.includes(selectedCc);
      const matchCat = !selectedCat || rowCat.includes(selectedCat);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCc && matchCat && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Budget Allocation${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noAllocationRow');
    const tbody = document.querySelector('#allocationTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAllocationRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No budget allocations found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterAllocations);
    searchInput.addEventListener('keyup', filterAllocations);
  }
  if (costCenterSelect) costCenterSelect.addEventListener('change', filterAllocations);
  if (expenditureCatSelect) expenditureCatSelect.addEventListener('change', filterAllocations);

  const allocateBudgetForm = document.getElementById('allocateBudgetForm');
  if (allocateBudgetForm) {
    allocateBudgetForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const ccVal = document.getElementById('modalAllocCc').value;
      const catVal = document.getElementById('modalAllocCat').value;
      const rawInitial = parseFloat(document.getElementById('modalAllocAmount').value || 0);
      const formattedInitial = '₱' + rawInitial.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedZero = '₱0.00';

      const allocObj = {
        cc: ccVal,
        cat: catVal,
        sub: 'Operational Expenditure Account',
        initial: formattedInitial,
        encumbered: formattedZero,
        expended: formattedZero,
        available: formattedInitial
      };

      const tbody = document.querySelector('#allocationTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'allocation-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-cc', ccVal.toLowerCase());
        newRow.setAttribute('data-cat', catVal.toLowerCase());

        newRow.onclick = function() { openAllocationDetailsModal(allocObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${ccVal}</span></td>
          <td>
            <div class="fw-semibold text-dark">${catVal}</div>
            <span class="fs-xs text-muted">Operational Expenditure Account</span>
          </td>
          <td class="text-end font-monospace fw-semibold">${formattedInitial}</td>
          <td class="text-end text-warning font-monospace">${formattedZero}</td>
          <td class="text-end text-danger font-monospace">${formattedZero}</td>
          <td class="text-end text-success fw-bold font-monospace">${formattedInitial}</td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Allocation Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Allocation Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openAllocationDetailsModal(allocObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('allocateBudgetModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      allocateBudgetForm.reset();
      filterAllocations();
    });
  }

  filterAllocations();
});
</script>
@endpush
