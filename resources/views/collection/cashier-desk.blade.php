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
      <p class="text-muted small mb-0">POS cashier shift management, opening float balances, drawer counting, and shift end remittance.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrow-counter-clockwise me-1"></i> Refresh Terminals</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#openShiftModal"><i class="ph ph-play-circle me-1"></i> Open Terminal Shift</button>
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
        <span class="fs-xs text-muted">ER, OPD, Inpatient &amp; Pharmacy</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Combined Drawer Cash</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱142,400.00</h4>
        <span class="fs-xs text-muted">Physical cash in active POS drawers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Shift Cash Variance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
        <span class="fs-xs text-muted">No overage or shortage flagged</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Remittances</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-tray fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Terminal</h4>
        <span class="fs-xs text-muted">Night shift ready for vault turn-in</span>
      </div>
    </div>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Terminal ID, Cashier, or Station...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Hospital Stations</option>
            <option value="main">Main Discharge Desk</option>
            <option value="er">Emergency Room (ER)</option>
            <option value="pharmacy">Pharmacy POS</option>
            <option value="opd">Outpatient Clinic</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Shift Statuses</option>
            <option value="open">Open Shift</option>
            <option value="closing">Closing In Progress</option>
            <option value="remitted">Closed &amp; Remitted</option>
          </select>
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-sm btn-light border w-100"><i class="ph ph-funnel me-1"></i> Filter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
            <tr>
              <td><span class="font-monospace text-primary fw-bold">TERM-01</span></td>
              <td>
                <div class="fw-semibold text-dark">Main Discharge Desk</div>
                <span class="fs-xs text-muted">Building A - Ground Floor</span>
              </td>
              <td>Anna Reyes</td>
              <td class="text-end text-muted font-monospace">₱5,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱45,200.00</td>
              <td><span class="text-nowrap">07:00 AM (Today)</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-circle-wavy-check me-1"></i> Open Shift</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#closeShiftModal"><i class="ph ph-stop-circle me-1"></i> Close &amp; Remit Shift</button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">TERM-02</span></td>
              <td>
                <div class="fw-semibold text-dark">Emergency Room (ER) Cashier</div>
                <span class="fs-xs text-muted">Emergency Ward Entrance</span>
              </td>
              <td>Mark Morales</td>
              <td class="text-end text-muted font-monospace">₱5,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱38,150.00</td>
              <td><span class="text-nowrap">07:00 AM (Today)</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-circle-wavy-check me-1"></i> Open Shift</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#closeShiftModal"><i class="ph ph-stop-circle me-1"></i> Close &amp; Remit Shift</button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">TERM-03</span></td>
              <td>
                <div class="fw-semibold text-dark">Pharmacy Central Station</div>
                <span class="fs-xs text-muted">Outpatient Pharmacy Annex</span>
              </td>
              <td>Sarah Gomez</td>
              <td class="text-end text-muted font-monospace">₱3,000.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱59,050.00</td>
              <td><span class="text-nowrap">08:00 AM (Today)</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-circle-wavy-check me-1"></i> Open Shift</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#closeShiftModal"><i class="ph ph-stop-circle me-1"></i> Close &amp; Remit Shift</button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">TERM-04</span></td>
              <td>
                <div class="fw-semibold text-dark">Outpatient Consultation Desk</div>
                <span class="fs-xs text-muted">Clinic Building 2F</span>
              </td>
              <td>James Cruz</td>
              <td class="text-end text-muted font-monospace">₱3,000.00</td>
              <td class="text-end text-muted font-monospace">₱0.00</td>
              <td><span class="text-nowrap">Closed</span></td>
              <td><span class="badge bg-secondary-subtle text-secondary"><i class="ph ph-minus-circle me-1"></i> Closed Shift</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#openShiftModal"><i class="ph ph-play me-1"></i> Start Shift</button>
              </td>
            </tr>
          </tbody>
        </table>
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
            <input type="text" class="form-control form-control-sm" value="{{ auth()->user()->name ?? 'Anna Reyes' }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Opening Cash Float (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" value="5000.00" required>
            <span class="fs-xs text-muted">Provided by vault teller for change drawer.</span>
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

<!-- Modal: Close & Remit Shift (Z-Read & Denomination Counter) -->
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

          <h6 class="fw-bold small mb-2 text-uppercase text-muted">Physical Bill &amp; Coin Denomination Count</h6>
          <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light fs-xs">
                <tr><th>Denomination</th><th style="width: 120px;">Bill / Coin Count</th><th class="text-end">Total Amount (₱)</th></tr>
              </thead>
              <tbody class="fs-xs">
                <tr><td>₱1,000 Bill</td><td><input type="number" class="form-control form-control-sm text-center py-0" value="40"></td><td class="text-end font-monospace">₱40,000.00</td></tr>
                <tr><td>₱500 Bill</td><td><input type="number" class="form-control form-control-sm text-center py-0" value="8"></td><td class="text-end font-monospace">₱4,000.00</td></tr>
                <tr><td>₱200 Bill</td><td><input type="number" class="form-control form-control-sm text-center py-0" value="5"></td><td class="text-end font-monospace">₱1,000.00</td></tr>
                <tr><td>₱100 Bill</td><td><input type="number" class="form-control form-control-sm text-center py-0" value="2"></td><td class="text-end font-monospace">₱200.00</td></tr>
                <tr><td>Coins &amp; Small Bills</td><td><input type="number" class="form-control form-control-sm text-center py-0" value="0"></td><td class="text-end font-monospace">₱0.00</td></tr>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <th colspan="2" class="text-end">Total Physical Cash Counted:</th>
                  <th class="text-end font-monospace text-primary fs-6">₱45,200.00</th>
                </tr>
              </tfoot>
            </table>
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
