@extends('layouts.app')

@section('title', 'Cashier Desk - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'cashier-desk')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Cashier Desk</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Cashier Desk &amp; POS Station Terminals</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="location.reload()"><i class="ph ph-arrow-counter-clockwise me-1"></i> Refresh Terminals</button>
      <button id="btnOpenShift" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#openShiftModal"><i class="ph ph-play-circle me-1"></i> Open Terminal Shift</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Shift Terminals</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-desktop fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">4 / 5 Open</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Drawer Cash</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱142,400.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Shift Cash Variance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Remittances</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-tray fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Terminal</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="stationSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Station:</label>
          <select id="stationSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Hospital Stations</option>
            <option value="main discharge">Main Discharge Desk</option>
            <option value="emergency room">Emergency Room (ER)</option>
            <option value="pharmacy central">Pharmacy Central Station</option>
            <option value="outpatient">Outpatient Consultation</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="shiftStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Shift Status:</label>
          <select id="shiftStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 160px;">
            <option value="" selected>All Shift Statuses</option>
            <option value="open shift">Open Shift</option>
            <option value="closed shift">Closed Shift</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="terminalSearchInput" class="form-control form-control-sm" placeholder="Search terminal #, cashier, location...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="terminalTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Terminal ID</th>
              <th>Station / Location</th>
              <th>Cashier Name</th>
              <th class="text-end">Opening Float (₱)</th>
              <th class="text-end">Current Drawer Cash (₱)</th>
              <th>Shift Start</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($terminals ?? [] as $t)
            @php
              $tArr = is_array($t) ? $t : [
                'id' => $t->terminal_id ?? 'TERM-N/A', 'location' => $t->location ?? 'N/A',
                'sub' => $t->sub_location ?? 'N/A', 'cashier' => $t->cashier_name ?? 'N/A',
                'float' => '₱' . number_format($t->opening_float ?? 0, 2),
                'cash' => '₱' . number_format($t->current_cash ?? 0, 2),
                'start' => $t->shift_started_at ? $t->shift_started_at->format('h:i A') : 'Closed',
                'status' => $t->status ?? 'Closed', 'status_badge' => 'bg-secondary-subtle text-secondary',
                'status_icon' => 'ph-minus-circle',
              ];
            @endphp
            <tr class="terminal-row" style="cursor: pointer;" data-station="{{ strtolower($tArr['location']) }}" data-status="{{ strtolower($tArr['status']) }}" onclick="openTerminalDetailsModal({{ json_encode($tArr) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $tArr['id'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $tArr['location'] }}</div>
                <span class="fs-xs text-muted">{{ $tArr['sub'] }}</span>
              </td>
              <td class="fw-semibold text-dark">{{ $tArr['cashier'] }}</td>
              <td class="text-end text-muted font-monospace">{{ $tArr['float'] }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $tArr['cash'] }}</td>
              <td><span class="text-nowrap font-monospace fs-xs">{{ $tArr['start'] }}</span></td>
              <td><span class="badge {{ $tArr['status_badge'] }}"><i class="ph {{ $tArr['status_icon'] }} me-1"></i> {{ $tArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#openShiftModal"><i class="ph ph-play me-1"></i> Start Shift</button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No cashier terminals configured in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="terminalSummaryText">Showing {{ count($terminals ?? []) }} Terminals</span>
      <nav aria-label="Terminal Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: Terminal Details (Executive Design) -->
<div class="modal fade" id="terminalDetailsModal" tabindex="-1" aria-labelledby="terminalDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailTermId">TERM-01</span>
            <span class="badge bg-success-subtle text-success" id="detailTermStatus"><i class="ph ph-circle-wavy-check me-1"></i> Open Shift</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailTermLocation">Main Discharge Desk</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Drawer Cash</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailTermCash">₱45,200.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Opening Cash Float</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailTermFloat">₱5,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-desktop me-1 text-primary"></i> Station &amp; Cashier Assignment</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Assigned Cashier Officer</span>
              <span class="fw-semibold text-dark" id="detailTermCashier">Anna Reyes</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Physical Location</span>
              <span class="text-dark" id="detailTermSub">Building A - Ground Floor</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Shift Opened Timestamp</span>
              <span class="font-monospace text-primary" id="detailTermStart">07:00 AM (Today)</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Vault Remittance Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Vault Teller Sign-off:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Float Verified &amp; Cleared</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">System Log Ref:</span>
              <span class="font-monospace text-muted">LOG-TERM-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Discrepancy Status:</span>
              <span class="fw-semibold text-success"><i class="ph ph-check-circle me-1"></i> Zero Variance (Exact Match)</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Open Terminal Shift -->
<div class="modal fade" id="openShiftModal" tabindex="-1" aria-labelledby="openShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="openShiftModalLabel"><i class="ph ph-play-circle me-2 text-primary"></i>Open Cashier Terminal Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Cashier Shift opened successfully!'); bootstrap.Modal.getInstance(document.getElementById('openShiftModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Select POS Station <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" required>
              <option value="TERM-01">TERM-01: Main Discharge Desk</option>
              <option value="TERM-02">TERM-02: Emergency Room (ER) Cashier</option>
              <option value="TERM-03">TERM-03: Pharmacy Central Station</option>
              <option value="TERM-04">TERM-04: Outpatient Consultation Desk</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Assigned Cashier <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" value="Active Cashier Officer" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Opening Cash Float (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" value="5000.00" required>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Start Shift Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Close & Remit Shift -->
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="closeShiftModalLabel"><i class="ph ph-scales me-2 text-warning"></i>Close Shift &amp; Physical Cash Count (Z-Read)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Shift closed and remitted to vault!'); bootstrap.Modal.getInstance(document.getElementById('closeShiftModal')).hide();">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Terminal &amp; Cashier</label>
              <input type="text" class="form-control form-control-sm bg-light" value="TERM-01 | Main Discharge (Anna Reyes)" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">System Expected Cash (₱)</label>
              <input type="text" class="form-control form-control-sm bg-light text-end font-monospace text-success fw-bold" value="₱45,200.00" readonly>
            </div>
          </div>
          <div class="alert alert-success d-flex align-items-center py-2 mb-3 fs-xs">
            <i class="ph ph-check-circle fs-5 me-2"></i>
            <div>Physical Cash drawer matches system receipts perfectly! Zero discrepancy.</div>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-warning"><i class="ph ph-file-arrow-up me-1"></i> Remit Funds to Vault &amp; Close Shift</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openTerminalDetailsModal(t) {
  if (!t) return;

  document.getElementById('detailTermId').textContent = t.id || 'TERM-00';
  document.getElementById('detailTermLocation').textContent = t.location || 'Location';
  document.getElementById('detailTermCashier').textContent = t.cashier || 'Cashier';
  document.getElementById('detailTermSub').textContent = t.sub || 'Sub-location';
  document.getElementById('detailTermFloat').textContent = t.float || '₱0.00';
  document.getElementById('detailTermCash').textContent = t.cash || '₱0.00';
  document.getElementById('detailTermStart').textContent = t.start || '-';

  const statusEl = document.getElementById('detailTermStatus');
  if (statusEl) {
    statusEl.textContent = t.status;
    statusEl.className = 'badge ' + (t.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('terminalDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('terminalSearchInput');
  const stationSelect = document.getElementById('stationSelect');
  const shiftStatusSelect = document.getElementById('shiftStatusSelect');
  const summaryText = document.getElementById('terminalSummaryText');

  function filterTerminals() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStation = stationSelect ? stationSelect.value.toLowerCase() : '';
    const selectedStatus = shiftStatusSelect ? shiftStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.terminal-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowStation = row.getAttribute('data-station') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchStation = !selectedStation || rowStation.includes(selectedStation);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchStation && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Terminal${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noTerminalRow');
    const tbody = document.querySelector('#terminalTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTerminalRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No POS terminals found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTerminals);
    searchInput.addEventListener('keyup', filterTerminals);
  }
  if (stationSelect) stationSelect.addEventListener('change', filterTerminals);
  if (shiftStatusSelect) shiftStatusSelect.addEventListener('change', filterTerminals);

  filterTerminals();
});
</script>
@endpush
