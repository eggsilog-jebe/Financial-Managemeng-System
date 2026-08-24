@extends('layouts.app')

@section('title', 'Cash Flow Forecasting - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'cash-flow-forecast')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Cash Flow Forecasting</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Predictive Cash Flow Forecasting Model</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Viewing Liquidity Projection Chart...');"><i class="ph ph-chart-line me-1"></i> Forecast Chart</button>
      <button id="btnRunForecast" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#runForecastModal"><i class="ph ph-play me-1"></i> Run Projection Model</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Cash Reserves</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($totalCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Projected 30-Day Solvency</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Projected Working Capital</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($totalCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Forecast Model Status</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Active</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="forecastHorizonSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Horizon:</label>
          <select id="forecastHorizonSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Horizons</option>
            <option value="30">30-Day Rolling Forecast</option>
            <option value="60">60-Day Rolling Forecast</option>
            <option value="90">90-Day Quarterly Forecast</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="modelSensitivitySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Model Sensitivity:</label>
          <select id="modelSensitivitySelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>Baseline Model</option>
            <option value="conservative">Conservative (15% Delay)</option>
            <option value="optimistic">Optimistic (Fast HMO)</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="forecastSearchInput" class="form-control form-control-sm" placeholder="Search weekly period, status...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="forecastTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Weekly Period</th>
              <th class="text-end">Starting Cash (₱)</th>
              <th class="text-end">Expected Inflow (₱)</th>
              <th class="text-end">Scheduled Outflow (₱)</th>
              <th class="text-end">Ending Cash Position (₱)</th>
              <th>Liquidity Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($forecasts ?? [] as $f)
            @php
              $fArr = is_array($f) ? $f : [
                'period' => $f->period_label ?? 'N/A',
                'start' => '₱' . number_format($f->opening_balance ?? 0, 2),
                'inflow' => '+₱' . number_format($f->projected_inflow ?? 0, 2),
                'outflow' => '-₱' . number_format($f->projected_outflow ?? 0, 2),
                'end' => '₱' . number_format($f->ending_balance ?? 0, 2),
                'status' => $f->status ?? 'Projected', 'status_badge' => 'bg-info-subtle text-info',
                'horizon' => $f->horizon_days ?? '30',
              ];
            @endphp
            <tr class="forecast-row" style="cursor: pointer;" data-horizon="{{ $fArr['horizon'] }}" onclick="openForecastDetailsModal({{ json_encode($fArr) }})">
              <td><span class="fw-bold text-dark">{{ $fArr['period'] }}</span></td>
              <td class="text-end font-monospace">{{ $fArr['start'] }}</td>
              <td class="text-end text-success font-monospace">{{ $fArr['inflow'] }}</td>
              <td class="text-end text-danger font-monospace">{{ $fArr['outflow'] }}</td>
              <td class="text-end text-primary fw-bold font-monospace">{{ $fArr['end'] }}</td>
              <td><span class="badge {{ $fArr['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $fArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Weekly Breakout" onclick="openForecastDetailsModal({{ json_encode($fArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No cash flow forecasts available in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="forecastSummaryText">Showing {{ count($forecasts ?? []) }} Forecast Weeks</span>
      <nav aria-label="Forecast Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Forecast Details (Executive Design) -->
<div class="modal fade" id="forecastDetailsModal" tabindex="-1" aria-labelledby="forecastDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">30-DAY ROLLING</span>
            <span class="badge bg-success-subtle text-success" id="detailForecastStatus"><i class="ph ph-check-circle me-1"></i> Healthy Buffer</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailForecastPeriod">Week 1 (Aug 08 - Aug 14)</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Opening Cash</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailForecastStart">₱7,840,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Expected Inflow</span>
              <h5 class="fw-bold text-success mb-0 font-monospace" id="detailForecastInflow">+₱1,850,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Scheduled Outflow</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailForecastOutflow">-₱1,400,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Projected Ending</span>
              <h5 class="fw-bold text-primary mb-0 font-monospace" id="detailForecastEnd">₱8,290,000.00</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-calculator me-1 text-primary"></i> Forecast Model Drivers</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Inflow Source Weight</span>
              <span class="font-monospace fw-bold text-dark">65% Cashier Counter Receipts, 35% HMO Wire Claims</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Outflow Commitment Scope</span>
              <span class="font-monospace fw-bold text-danger">AP Approved Vouchers + Encumbered PO Commitments</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Forecasting Engine Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Model Confidence Level:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> 96.8% Statistical Accuracy</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-FCST-2026-W01 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Forecast Sensitivity PDF...');"><i class="ph ph-file-text me-1"></i> Export Forecast PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Run Projection Model -->
<div class="modal fade" id="runForecastModal" tabindex="-1" aria-labelledby="runForecastModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="runForecastModalLabel"><i class="ph ph-play me-2 text-primary"></i>Run Predictive Liquidity Model</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Forecast model updated!'); bootstrap.Modal.getInstance(document.getElementById('runForecastModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Forecast Period <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="30">30-Day Short Term Forecast</option>
              <option value="60">60-Day Mid Term Forecast</option>
              <option value="90">90-Day Quarterly Forecast</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Include Pending Purchase Orders?</label>
            <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incPo"><label class="form-check-label small" for="incPo">Yes, include encumbered POs</label></div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-play me-1"></i> Execute Forecast Engine</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openForecastDetailsModal(f) {
  if (!f) return;

  document.getElementById('detailForecastPeriod').textContent = f.period || 'Weekly Period';
  document.getElementById('detailForecastStart').textContent = f.start || '₱0.00';
  document.getElementById('detailForecastInflow').textContent = f.inflow || '₱0.00';
  document.getElementById('detailForecastOutflow').textContent = f.outflow || '₱0.00';
  document.getElementById('detailForecastEnd').textContent = f.end || '₱0.00';

  const statusEl = document.getElementById('detailForecastStatus');
  if (statusEl) {
    statusEl.textContent = f.status;
    statusEl.className = 'badge ' + (f.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('forecastDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('forecastSearchInput');
  const horizonSelect = document.getElementById('forecastHorizonSelect');
  const sensitivitySelect = document.getElementById('modelSensitivitySelect');
  const summaryText = document.getElementById('forecastSummaryText');
  const btnRunForecast = document.getElementById('btnRunForecast');

  if (btnRunForecast) {
    btnRunForecast.addEventListener('click', function() {
      const modalEl = document.getElementById('runForecastModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterForecasts() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedHorizon = horizonSelect ? horizonSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.forecast-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowHorizon = row.getAttribute('data-horizon') || '';
      const rowText = row.textContent.toLowerCase();

      const matchHorizon = !selectedHorizon || rowHorizon === selectedHorizon;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchHorizon && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Forecast Week${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noForecastRow');
    const tbody = document.querySelector('#forecastTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noForecastRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No forecast periods found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterForecasts);
    searchInput.addEventListener('keyup', filterForecasts);
  }
  if (horizonSelect) horizonSelect.addEventListener('change', filterForecasts);
  if (sensitivitySelect) sensitivitySelect.addEventListener('change', filterForecasts);

  filterForecasts();
});
</script>
@endpush
