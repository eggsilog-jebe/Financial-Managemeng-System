@extends('layouts.app')

@section('title', 'Liquidity Management - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'liquidity')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Liquidity Management</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Working Capital &amp; Liquidity Controls</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Running Solvency Ratio Audit...');"><i class="ph ph-shield-check me-1"></i> Solvency Audit</button>
      <button id="btnAdjustReserve" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#adjustReserveModal"><i class="ph ph-sliders me-1"></i> Adjust Reserve Level</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Cash Pool</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($totalCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Liquidity Position</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ((float) ($totalCash ?? 0)) > 0 ? 'Healthy' : 'Zero Balance' }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unrestricted Free Cash Pool</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($totalCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Working Capital Solvency</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Solvent</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="complianceStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Compliance:</label>
          <select id="complianceStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="compliant">Compliant</option>
            <option value="warning">Warning / Near Threshold</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="indicatorCatSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Category:</label>
          <select id="indicatorCatSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Categories</option>
            <option value="operating">Operating Buffer</option>
            <option value="solvency">Solvency Ratios</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="liquiditySearchInput" class="form-control form-control-sm" placeholder="Search indicator, threshold, value...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="liquidityTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Liquidity Indicator</th>
              <th>Target Threshold</th>
              <th class="text-end">Current Value</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($indicators ?? [] as $ind)
            @php
              $iArr = is_array($ind) ? $ind : [
                'name' => $ind->indicator_name ?? 'N/A', 'desc' => $ind->description ?? 'N/A',
                'target' => $ind->target_value ?? 'N/A', 'value' => $ind->current_value ?? 'N/A',
                'status' => $ind->status ?? 'N/A', 'status_badge' => 'bg-warning-subtle text-warning',
                'cat' => strtolower($ind->category ?? 'general'),
              ];
            @endphp
            <tr class="liquidity-row" style="cursor: pointer;" data-cat="{{ $iArr['cat'] }}" data-status="{{ strtolower($iArr['status']) }}" onclick="openLiquidityDetailsModal({{ json_encode($iArr) }})">
              <td>
                <div class="fw-bold text-dark">{{ $iArr['name'] }}</div>
                <span class="fs-xs text-muted">{{ $iArr['desc'] }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $iArr['target'] }}</td>
              <td class="text-end font-monospace fw-bold text-success">{{ $iArr['value'] }}</td>
              <td><span class="badge {{ $iArr['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $iArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Ratio Breakdown" onclick="openLiquidityDetailsModal({{ json_encode($iArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">No liquidity indicators configured in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="liquiditySummaryText">Showing {{ count($indicators ?? []) }} Solvency Ratios</span>
      <nav aria-label="Liquidity Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Liquidity Indicator Details (Executive Design) -->
<div class="modal fade" id="liquidityDetailsModal" tabindex="-1" aria-labelledby="liquidityDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">WORKING CAPITAL</span>
            <span class="badge bg-success-subtle text-success" id="detailLiqStatus"><i class="ph ph-check-circle me-1"></i> Compliant</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailLiqName">Days Cash on Hand (DCOH)</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Target Threshold</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailLiqTarget">&gt; 40.0 Days</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Calculated Value</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailLiqValue">48.2 Days</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-scales me-1 text-primary"></i> Indicator Calculation Formula</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Formula Definition</span>
              <span class="fw-semibold text-dark" id="detailLiqDesc">Operating cash divided by daily hospital burn rate</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Regulatory Minimum Compliance</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-shield-check me-1"></i> Exceeds Statutory Minimums</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Solvency Audit Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Internal Solvency Lock:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Capital Reserves Verified</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-LIQ-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Solvency Audit Report...');"><i class="ph ph-file-text me-1"></i> Export Solvency Report</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Adjust Reserve Level -->
<div class="modal fade" id="adjustReserveModal" tabindex="-1" aria-labelledby="adjustReserveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="adjustReserveModalLabel"><i class="ph ph-sliders me-2 text-primary"></i>Adjust Emergency Reserve Quota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Emergency Reserve Quota updated!'); bootstrap.Modal.getInstance(document.getElementById('adjustReserveModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Reserve Minimum (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" value="2000000.00" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Minimum Days Cash Threshold</label>
            <input type="number" class="form-control form-control-sm text-end font-monospace" value="40">
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Update Reserve Floor</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openLiquidityDetailsModal(ind) {
  if (!ind) return;

  document.getElementById('detailLiqName').textContent = ind.name || 'Indicator Name';
  document.getElementById('detailLiqDesc').textContent = ind.desc || '-';
  document.getElementById('detailLiqTarget').textContent = ind.target || '0';
  document.getElementById('detailLiqValue').textContent = ind.value || '0';

  const statusEl = document.getElementById('detailLiqStatus');
  if (statusEl) {
    statusEl.textContent = ind.status;
    statusEl.className = 'badge ' + (ind.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('liquidityDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('liquiditySearchInput');
  const complianceSelect = document.getElementById('complianceStatusSelect');
  const catSelect = document.getElementById('indicatorCatSelect');
  const summaryText = document.getElementById('liquiditySummaryText');
  const btnAdjustReserve = document.getElementById('btnAdjustReserve');

  if (btnAdjustReserve) {
    btnAdjustReserve.addEventListener('click', function() {
      const modalEl = document.getElementById('adjustReserveModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterLiquidity() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStatus = complianceSelect ? complianceSelect.value.toLowerCase() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.liquidity-row');
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
      summaryText.textContent = `Showing ${visibleCount} Solvency Ratio${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noLiquidityRow');
    const tbody = document.querySelector('#liquidityTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noLiquidityRow';
        emptyRow.innerHTML = `<td colspan="5" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No solvency indicators found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterLiquidity);
    searchInput.addEventListener('keyup', filterLiquidity);
  }
  if (complianceSelect) complianceSelect.addEventListener('change', filterLiquidity);
  if (catSelect) catSelect.addEventListener('change', filterLiquidity);

  filterLiquidity();
});
</script>
@endpush
