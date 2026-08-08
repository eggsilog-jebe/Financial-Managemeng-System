@extends('layouts.app')

@section('title', 'Deposit Slips - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'deposit-slips')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Deposit Slips</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Batch Deposit Slips</h1>
      <p class="text-muted small mb-0">Consolidate cashier shift cash and bank checks into official batch deposit slips for armored pickup.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-pdf me-1"></i> Download Manifest</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createDepositSlipModal"><i class="ph ph-plus-circle me-1"></i> Create Deposit Slip</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Slips Prepared Today</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-path fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">3 Batch Slips</h4>
        <span class="fs-xs text-muted">Total Deposit Value: <strong class="text-primary">₱215,400.00</strong></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cash for Vault Pickup</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-money fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱140,400.00</h4>
        <span class="fs-xs text-muted">Bundled in sealed tamper-proof bags</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Checks Pending Clearance</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱75,000.00</h4>
        <span class="fs-xs text-muted">2 Commercial Checks</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">In-Transit Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-truck fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Armored Pickup</h4>
        <span class="fs-xs text-muted">Scheduled Today at 04:30 PM</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Slip Ref, Bank Name, or Account...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Bank Destinations</option>
            <option value="metrobank">Metrobank #1020 (Operating)</option>
            <option value="bdo">BDO Unibank #2384 (Collections)</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Transit Statuses</option>
            <option value="ready">Ready for Transport</option>
            <option value="in_transit">In-Transit (Armored Car)</option>
            <option value="deposited">Deposited at Branch</option>
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
              <th>Slip Ref</th>
              <th>Batch Date</th>
              <th>Source Remittances</th>
              <th>Target Bank Account</th>
              <th class="text-end">Cash Amount (₱)</th>
              <th class="text-end">Check Amount (₱)</th>
              <th class="text-end">Total Deposit (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">SLIP-2026-081</span></td>
              <td>2026-08-08</td>
              <td><span class="badge bg-light text-dark border">TERM-01, TERM-02</span></td>
              <td>
                <div class="fw-semibold text-dark">Metrobank - Main</div>
                <span class="fs-xs font-monospace text-muted">1020-8841-99</span>
              </td>
              <td class="text-end font-monospace">₱35,000.00</td>
              <td class="text-end font-monospace">₱10,200.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱45,200.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-truck me-1"></i> Ready for Transport</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print Slip PDF"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Details"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">SLIP-2026-080</span></td>
              <td>2026-08-07</td>
              <td><span class="badge bg-light text-dark border">TERM-03 (Pharmacy)</span></td>
              <td>
                <div class="fw-semibold text-dark">BDO Unibank - Collections</div>
                <span class="fs-xs font-monospace text-muted">0091-2384-12</span>
              </td>
              <td class="text-end font-monospace">₱105,400.00</td>
              <td class="text-end font-monospace">₱64,800.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱170,200.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Deposited at Branch</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="Print Slip PDF"><i class="ph ph-printer"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="View Verification"><i class="ph ph-file-check"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Deposit Slip -->
<div class="modal fade" id="createDepositSlipModal" tabindex="-1" aria-labelledby="createDepositSlipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createDepositSlipModalLabel"><i class="ph ph-path me-2 text-primary"></i>Create Batch Deposit Slip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Batch deposit slip created successfully!'); bootstrap.Modal.getInstance(document.getElementById('createDepositSlipModal')).hide();">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Hospital Bank Account <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="1">Metrobank Main Branch (Acc #1020-8841-99)</option>
                <option value="2">BDO Unibank Collections (Acc #0091-2384-12)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Armored Pickup Schedule Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Cash to Bundle (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Checks Included (₱)</label>
              <input type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Check References / Bank List</label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. BDO Check #44910 (₱65,000.00), BPI Check #1002 (₱10,000.00)">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Vault Security Bag Seal Tag Number</label>
              <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. SEAL-BAG-99201">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Generate &amp; Seal Batch Slip</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
