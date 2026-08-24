@extends('layouts.app')

@section('title', 'Financial KPI Dashboard - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'kpi-dashboard')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Financial KPI Dashboard</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Healthcare Financial Analytics &amp; KPI Dashboard</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Refreshed real-time analytics data!');"><i class="ph ph-arrow-clockwise me-1"></i> Refresh Analytics</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('KPI Performance Brief downloaded!');"><i class="ph ph-download-simple me-1"></i> Export KPI Brief</button>
    </div>
  </div>

  <!-- Primary Executive Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Sales Outstanding (DSO)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">{{ $dso }} Days</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Profit Margin</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">{{ $operatingProfitMargin }}%</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Revenue</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-currency-circle-dollar fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱{{ number_format($totalRevenue, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Current Working Ratio</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">{{ $currentRatio }}x</h4>
      </div>
    </div>
  </div>

  <!-- Data Table & Analytics Filter Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="kpiCatSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> KPI Category:</label>
          <select id="kpiCatSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All KPI Categories</option>
            <option value="collection">Collection &amp; Credit Efficiency</option>
            <option value="operating">Operational Productivity</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="kpiStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="kpiStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="optimal">Optimal / Target Met</option>
            <option value="compliant">Compliant</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="kpiSearchInput" class="form-control form-control-sm" placeholder="Search KPI metric name...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="kpiTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Healthcare Key Performance Indicator</th>
              <th>Category</th>
              <th>Benchmark Target</th>
              <th class="text-end">Current Value</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kpis ?? [] as $kpi)
            <tr class="kpi-row" style="cursor: pointer;">
              <td><div class="fw-bold text-dark">{{ $kpi['name'] }}</div></td>
              <td><span class="badge bg-info-subtle text-info">{{ $kpi['category'] }}</span></td>
              <td class="font-monospace fs-xs">{{ $kpi['target'] }}</td>
              <td class="text-end font-monospace fw-bold text-primary">{{ $kpi['value'] }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> {{ $kpi['status'] }}</span></td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No KPI metrics registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="kpiSummaryText">Showing {{ count($kpis ?? []) }} Analytics Metrics</span>
      <nav aria-label="KPI Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth KPI Details (Executive Design) -->
<div class="modal fade" id="kpiDetailsModal" tabindex="-1" aria-labelledby="kpiDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailKpiCat">Collection &amp; Credit</span>
            <span class="badge bg-success-subtle text-success" id="detailKpiStatus"><i class="ph ph-check-circle me-1"></i> Optimal / Target Met</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailKpiName">HMO Collection Efficiency Rate</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Target Benchmark</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailKpiTarget">&gt; 90.0%</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Realized Metric</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailKpiValue">94.2%</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-chart-bar me-1 text-primary"></i> Business Intelligence Definition</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Scope &amp; Data Source</span>
              <span class="fw-semibold text-dark" id="detailKpiDesc">Maxicare, Intellicare &amp; PhilHealth claims paid vs filed</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Calculation Frequency</span>
              <span class="font-monospace text-primary fw-bold">Real-time Daily Rollup</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Benchmark Target Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Executive Board Compliance:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Target Achieved</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-KPI-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting KPI Analytics PDF...');"><i class="ph ph-file-text me-1"></i> Export KPI Report</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openKpiDetailsModal(k) {
  if (!k) return;

  document.getElementById('detailKpiName').textContent = k.name || 'Metric Name';
  document.getElementById('detailKpiCat').textContent = k.category || 'Category';
  document.getElementById('detailKpiDesc').textContent = k.desc || '-';
  document.getElementById('detailKpiTarget').textContent = k.target || '0';
  document.getElementById('detailKpiValue').textContent = k.value || '0';

  const statusEl = document.getElementById('detailKpiStatus');
  if (statusEl) {
    statusEl.textContent = k.status;
    statusEl.className = 'badge ' + (k.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('kpiDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('kpiSearchInput');
  const catSelect = document.getElementById('kpiCatSelect');
  const statusSelect = document.getElementById('kpiStatusSelect');
  const summaryText = document.getElementById('kpiSummaryText');

  function filterKpis() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.kpi-row');
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
      summaryText.textContent = `Showing ${visibleCount} Analytics Metric${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noKpiRow');
    const tbody = document.querySelector('#kpiTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noKpiRow';
        emptyRow.innerHTML = `<td colspan="6" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No KPI analytics found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterKpis);
    searchInput.addEventListener('keyup', filterKpis);
  }
  if (catSelect) catSelect.addEventListener('change', filterKpis);
  if (statusSelect) statusSelect.addEventListener('change', filterKpis);

  filterKpis();
});
</script>
@endpush
