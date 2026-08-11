@extends('layouts.app')

@section('title', 'Variance Analysis - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'variance-analysis')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Variance Analysis</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget vs. Actual Variance Analysis</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Re-calculating real-time budget variances...');"><i class="ph ph-arrows-counter-clockwise me-1"></i> Re-Calculate Variances</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Exporting Variance Audit PDF...');"><i class="ph ph-file-arrow-down me-1"></i> Export Audit Variance PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Favorable Variances (Savings)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">+₱1,420,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unfavorable Variances (Over-Spend)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">-₱345,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Budget Variance</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">+₱1,075,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Over-Budget Flagged Accounts</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-warning-octagon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2 Accounts</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="varianceTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Variance Type:</label>
          <select id="varianceTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Variance Types</option>
            <option value="favorable">Favorable (Under Budget)</option>
            <option value="unfavorable">Unfavorable (Over Budget)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="varianceDeptSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Department:</label>
          <select id="varianceDeptSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Departments</option>
            <option value="pharmacy">Pharmacy</option>
            <option value="facilities">Facilities &amp; Power</option>
            <option value="icu">ICU Care</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="varianceSearchInput" class="form-control form-control-sm" placeholder="Search line item, cost center, status...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="varianceTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Expense Category / Line Item</th>
              <th class="text-end">Budgeted (₱)</th>
              <th class="text-end">Actual Realized (₱)</th>
              <th class="text-end">Variance (₱)</th>
              <th>Variance %</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $variances = [
                [
                  'item' => 'Pharmacy Medical Supplies & Antibiotics',
                  'cc' => 'CC-101 (Pharmacy)',
                  'budget' => '₱2,500,000.00',
                  'actual' => '₱2,280,000.00',
                  'variance' => '+₱220,000.00',
                  'pct' => '+8.8%',
                  'pct_class' => 'text-success',
                  'status' => 'Favorable (Under Budget)',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'type' => 'favorable'
                ],
                [
                  'item' => 'Facility Electric Utility & Generator Power',
                  'cc' => 'CC-104 (Facilities)',
                  'budget' => '₱600,000.00',
                  'actual' => '₱645,000.00',
                  'variance' => '-₱45,000.00',
                  'pct' => '-7.5%',
                  'pct_class' => 'text-danger',
                  'status' => 'Unfavorable (Over Budget)',
                  'status_badge' => 'bg-danger-subtle text-danger',
                  'status_icon' => 'ph-warning-circle',
                  'type' => 'unfavorable'
                ],
                [
                  'item' => 'ICU Surgical Equipment Maintenance',
                  'cc' => 'CC-102 (ICU Care)',
                  'budget' => '₱1,800,000.00',
                  'actual' => '₱1,450,000.00',
                  'variance' => '+₱350,000.00',
                  'pct' => '+19.4%',
                  'pct_class' => 'text-success',
                  'status' => 'Favorable (Under Budget)',
                  'status_badge' => 'bg-success-subtle text-success',
                  'status_icon' => 'ph-check-circle',
                  'type' => 'favorable'
                ],
              ];
            @endphp

            @foreach($variances as $v)
            <tr class="variance-row" style="cursor: pointer;" data-type="{{ $v['type'] }}" data-cc="{{ strtolower($v['cc']) }}" onclick="openVarianceDetailsModal({{ json_encode($v) }})">
              <td>
                <div class="fw-bold text-dark">{{ $v['item'] }}</div>
                <span class="fs-xs text-muted">Cost Center: {{ $v['cc'] }}</span>
              </td>
              <td class="text-end font-monospace">{{ $v['budget'] }}</td>
              <td class="text-end font-monospace">{{ $v['actual'] }}</td>
              <td class="text-end {{ $v['pct_class'] }} fw-bold font-monospace">{{ $v['variance'] }}</td>
              <td><span class="{{ $v['pct_class'] }} fw-semibold">{{ $v['pct'] }}</span></td>
              <td><span class="badge {{ $v['status_badge'] }}"><i class="ph {{ $v['status_icon'] }} me-1"></i> {{ $v['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Variance Details" onclick="openVarianceDetailsModal({{ json_encode($v) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="varianceSummaryText">Showing {{ count($variances) }} Variance Items</span>
      <nav aria-label="Variance Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Variance Details (Executive Design) -->
<div class="modal fade" id="varianceDetailsModal" tabindex="-1" aria-labelledby="varianceDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailVarCc">CC-101 (Pharmacy)</span>
            <span class="badge bg-success-subtle text-success" id="detailVarStatus"><i class="ph ph-check-circle me-1"></i> Favorable (Under Budget)</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailVarItem">Pharmacy Medical Supplies &amp; Antibiotics</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Budget Target</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailVarBudget">₱2,500,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Actual Realized Spend</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailVarActual">₱2,280,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Calculated Variance</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailVarAmount">+₱220,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-percent me-1 text-primary"></i> Variance Percentage &amp; Account Code</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Percentage Variance</span>
              <span class="font-monospace fw-bold text-success" id="detailVarPct">+8.8%</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Reporting Period</span>
              <span class="font-monospace text-muted">FY 2026 Year-To-Date</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Variance Analysis Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Management Action Flag:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Variance within Acceptable Tolerance (&lt; 10%)</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-VAR-2026-CC101 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Line Item Audit Log...');"><i class="ph ph-file-text me-1"></i> Export Line Audit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openVarianceDetailsModal(v) {
  if (!v) return;

  document.getElementById('detailVarItem').textContent = v.item || 'Item Name';
  document.getElementById('detailVarCc').textContent = v.cc || 'CC-000';
  document.getElementById('detailVarBudget').textContent = v.budget || '₱0.00';
  document.getElementById('detailVarActual').textContent = v.actual || '₱0.00';
  document.getElementById('detailVarAmount').textContent = v.variance || '₱0.00';
  document.getElementById('detailVarPct').textContent = v.pct || '0%';

  const statusEl = document.getElementById('detailVarStatus');
  if (statusEl) {
    statusEl.textContent = v.status;
    statusEl.className = 'badge ' + (v.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('varianceDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('varianceSearchInput');
  const varianceTypeSelect = document.getElementById('varianceTypeSelect');
  const varianceDeptSelect = document.getElementById('varianceDeptSelect');
  const summaryText = document.getElementById('varianceSummaryText');

  function filterVariances() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedType = varianceTypeSelect ? varianceTypeSelect.value.toLowerCase() : '';
    const selectedDept = varianceDeptSelect ? varianceDeptSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.variance-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowType = row.getAttribute('data-type') || '';
      const rowCc = row.getAttribute('data-cc') || '';
      const rowText = row.textContent.toLowerCase();

      const matchType = !selectedType || rowType.includes(selectedType);
      const matchDept = !selectedDept || rowCc.includes(selectedDept);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchType && matchDept && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Variance Item${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noVarianceRow');
    const tbody = document.querySelector('#varianceTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noVarianceRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No budget variance items found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterVariances);
    searchInput.addEventListener('keyup', filterVariances);
  }
  if (varianceTypeSelect) varianceTypeSelect.addEventListener('change', filterVariances);
  if (varianceDeptSelect) varianceDeptSelect.addEventListener('change', filterVariances);

  filterVariances();
});
</script>
@endpush
