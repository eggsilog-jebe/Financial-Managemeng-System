@extends('layouts.app')

@section('title', 'Departmental Budgets - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'departmental-budgets')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Departmental Budgets</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Operating Budgets</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Viewing Department Summary Chart...');"><i class="ph ph-chart-pie-slice me-1"></i> Department Summary</button>
      <button id="btnEditDept" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editDepartmentModal"><i class="ph ph-pencil-line me-1"></i> Edit Department Cap</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Department Units</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Departments</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Department Caps</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱85,000,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Consumed</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱42,500,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Average Hospital Burn Rate</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-gauge fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">50.0%</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="wingSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Hospital Wing:</label>
          <select id="wingSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Hospital Wings</option>
            <option value="cardiology">Clinical (Cardiology &amp; ICU)</option>
            <option value="pharmacy">Pharmacy &amp; Therapeutics</option>
            <option value="emergency">Emergency Operations</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="burnRateSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Burn Status:</label>
          <select id="burnRateSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Burn Statuses</option>
            <option value="normal">Normal (&lt; 75%)</option>
            <option value="high">High Burn (75% - 90%)</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="deptSearchInput" class="form-control form-control-sm" placeholder="Search department, head, code...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="deptTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Department Name</th>
              <th>Head of Department</th>
              <th class="text-end">Annual Cap (₱)</th>
              <th class="text-end">YTD Spent (₱)</th>
              <th class="text-end">Available Quota (₱)</th>
              <th style="min-width: 180px;">Burn Rate %</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($departments ?? [] as $d)
            @php
              $dArr = is_array($d) ? $d : [
                'code' => $d->department_code ?? 'N/A', 'name' => $d->name ?? 'N/A',
                'head' => $d->department_head ?? 'N/A',
                'cap' => '₱' . number_format($d->budget_cap ?? 0, 2),
                'spent' => '₱' . number_format($d->amount_spent ?? 0, 2),
                'available' => '₱' . number_format(($d->budget_cap ?? 0) - ($d->amount_spent ?? 0), 2),
                'burn' => number_format($d->burn_rate ?? 0, 1) . '%',
                'burn_val' => $d->burn_rate ?? 0, 'burn_class' => 'bg-info', 'burn_status' => 'normal',
              ];
            @endphp
            <tr class="dept-row" style="cursor: pointer;" data-wing="{{ strtolower($dArr['name']) }}" data-burn="{{ $dArr['burn_status'] }}" onclick="openDepartmentDetailsModal({{ json_encode($dArr) }})">
              <td>
                <div class="fw-bold text-dark">{{ $dArr['name'] }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $dArr['code'] }}</span>
              </td>
              <td>{{ $dArr['head'] }}</td>
              <td class="text-end font-monospace fw-semibold">{{ $dArr['cap'] }}</td>
              <td class="text-end text-primary font-monospace">{{ $dArr['spent'] }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $dArr['available'] }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar {{ $dArr['burn_class'] }}" style="width: {{ $dArr['burn_val'] }}%;"></div>
                  </div>
                  <span class="fs-xs fw-semibold">{{ $dArr['burn'] }}</span>
                </div>
              </td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Department Details" onclick="openDepartmentDetailsModal({{ json_encode($dArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No departmental budgets in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="deptSummaryText">Showing {{ count($departments ?? []) }} Departmental Budgets</span>
      <nav aria-label="Department Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Department Details (Executive Design) -->
<div class="modal fade" id="departmentDetailsModal" tabindex="-1" aria-labelledby="departmentDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailDeptCode">DEPT-ICU-01</span>
            <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active Department Unit</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailDeptName">Cardiology &amp; ICU Care</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Annual Cap Limit</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailDeptCap">₱22,000,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">YTD Spent</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailDeptSpent">₱12,700,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Available Quota</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailDeptAvailable">₱9,300,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-user-gear me-1 text-primary"></i> Department Head &amp; Quota Utilization</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Head of Department</span>
              <span class="fw-bold text-dark" id="detailDeptHead">Dr. Alejandro Santos</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Burn Rate Percentage</span>
              <span class="font-monospace fw-bold text-primary" id="detailDeptBurn">57.7%</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Spending Cap Control</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Over-Budget Lock Protection:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Automatic PO Block Active</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Log:</span>
              <span class="font-monospace text-muted">LOG-DEPT-2026-ICU | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Departmental Ledger PDF...');"><i class="ph ph-file-pdf me-1"></i> Department Ledger PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Departmental Budget Cap -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="editDepartmentModalLabel"><i class="ph ph-pencil-line me-2 text-primary"></i>Adjust Departmental Budget Cap</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="editDeptForm">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Department <span class="text-danger">*</span></label>
            <select id="modalDeptSelect" class="form-select form-select-sm" required>
              <option value="Cardiology & ICU Care">Cardiology &amp; ICU Care (Dr. Alejandro Santos)</option>
              <option value="Pharmacy & Medical Therapeutics">Pharmacy &amp; Medical Therapeutics (Pharm. Elena Rostova)</option>
              <option value="Emergency Room Operations">Emergency Room Operations (Dr. Marcus Vance)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">New Annual Budget Cap (₱) <span class="text-danger">*</span></label>
            <input type="number" id="modalDeptCap" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" value="28000000.00" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Update Annual Cap</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openDepartmentDetailsModal(d) {
  if (!d) return;

  document.getElementById('detailDeptCode').textContent = d.code || 'DEPT-000';
  document.getElementById('detailDeptName').textContent = d.name || 'Department Name';
  document.getElementById('detailDeptHead').textContent = d.head || '-';
  document.getElementById('detailDeptCap').textContent = d.cap || '₱0.00';
  document.getElementById('detailDeptSpent').textContent = d.spent || '₱0.00';
  document.getElementById('detailDeptAvailable').textContent = d.available || '₱0.00';
  document.getElementById('detailDeptBurn').textContent = d.burn || '0%';

  const modalEl = document.getElementById('departmentDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('deptSearchInput');
  const wingSelect = document.getElementById('wingSelect');
  const burnRateSelect = document.getElementById('burnRateSelect');
  const summaryText = document.getElementById('deptSummaryText');
  const btnEditDept = document.getElementById('btnEditDept');

  if (btnEditDept) {
    btnEditDept.addEventListener('click', function() {
      const modalEl = document.getElementById('editDepartmentModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterDepartments() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedWing = wingSelect ? wingSelect.value.toLowerCase() : '';
    const selectedBurn = burnRateSelect ? burnRateSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.dept-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowWing = row.getAttribute('data-wing') || '';
      const rowBurn = row.getAttribute('data-burn') || '';
      const rowText = row.textContent.toLowerCase();

      const matchWing = !selectedWing || rowWing.includes(selectedWing);
      const matchBurn = !selectedBurn || rowBurn.includes(selectedBurn);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchWing && matchBurn && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Departmental Budget${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noDeptRow');
    const tbody = document.querySelector('#deptTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noDeptRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No departmental budgets found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterDepartments);
    searchInput.addEventListener('keyup', filterDepartments);
  }
  if (wingSelect) wingSelect.addEventListener('change', filterDepartments);
  if (burnRateSelect) burnRateSelect.addEventListener('change', filterDepartments);

  const editDeptForm = document.getElementById('editDeptForm');
  if (editDeptForm) {
    editDeptForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const nameVal = document.getElementById('modalDeptSelect').value;
      const rawCap = parseFloat(document.getElementById('modalDeptCap').value || 0);
      const formattedCap = '₱' + rawCap.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      const rows = document.querySelectorAll('.dept-row');
      rows.forEach(function(r) {
        if (r.textContent.includes(nameVal)) {
          const capTd = r.children[2];
          if (capTd) capTd.textContent = formattedCap;
        }
      });

      const modalEl = document.getElementById('editDepartmentModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      alert(`Department budget cap for ${nameVal} updated to ${formattedCap}!`);
    });
  }

  filterDepartments();
});
</script>
@endpush
