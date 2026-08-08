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
      <p class="text-muted small mb-0">Monitor cash buffer adequacy, Days Cash on Hand (DCOH), and working capital solvency ratios.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-shield-check me-1"></i> Solvency Audit</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#adjustReserveModal"><i class="ph ph-sliders me-1"></i> Adjust Reserve Level</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Days Cash on Hand (DCOH)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">48.2 Days</h4>
        <span class="fs-xs text-muted">Exceeds 40-Day Statutory Minimum</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Quick Solvency Ratio</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2.41x</h4>
        <span class="fs-xs text-muted">Liquid Assets vs Short-Term AP</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unrestricted Free Cash Pool</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱5,840,000.00</h4>
        <span class="fs-xs text-muted">Immediately deployable cash</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Emergency Reserve Quota</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-shield fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱2,000,000.00</h4>
        <span class="fs-xs text-muted">Minimum locked safety floor</span>
      </div>
    </div>
  </div>

  <!-- Liquidity Indicators Data Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-light fw-bold">Hospital Working Capital Ratios &amp; Buffer Analysis</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
            <tr>
              <td>
                <div class="fw-bold text-dark">Days Cash on Hand (DCOH)</div>
                <span class="fs-xs text-muted">Operating cash divided by daily hospital burn rate</span>
              </td>
              <td>&gt; 40.0 Days</td>
              <td class="text-end font-monospace fw-bold text-success">48.2 Days</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Compliant</span></td>
              <td class="text-end"><button class="btn btn-sm btn-light border p-1"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Quick Ratio (Acid-Test)</div>
                <span class="fs-xs text-muted">(Cash + AR) divided by Current Liabilities</span>
              </td>
              <td>&gt; 1.5x</td>
              <td class="text-end font-monospace fw-bold text-success">2.41x</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Compliant</span></td>
              <td class="text-end"><button class="btn btn-sm btn-light border p-1"><i class="ph ph-eye"></i></button></td>
            </tr>
          </tbody>
        </table>
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
