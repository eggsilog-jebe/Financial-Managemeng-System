@extends('layouts.app')

@section('title', 'Payment Gateway Logs - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'payment-gateways')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Payment Gateway Logs</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Payment Gateway &amp; E-Wallet Logs</h1>
      <p class="text-muted small mb-0">Digital payment transaction audit for patient online portal, credit cards, GCash, and PayMaya integrations.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-arrow-clockwise me-1"></i> Sync Webhook Statuses</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#gatewayDetailModal"><i class="ph ph-globe me-1"></i> View API Gateway Details</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Inflows (Today)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-globe-hemisphere-west fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱42,800.00</h4>
        <span class="fs-xs text-muted">16 Online Patient Portal Payments</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Gateway Success Rate</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">98.5%</h4>
        <span class="fs-xs text-muted">Successful Webhook callbacks</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Failed / Timed-Out Txns</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning-octagon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Txn</h4>
        <span class="fs-xs text-muted">Expired e-wallet QR session</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Merchant Payout</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-device-mobile fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱38,500.00</h4>
        <span class="fs-xs text-muted">PayMaya / GCash T+1 Settlement</span>
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
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Gateway Txn ID, Patient Account, or Ref...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Gateway Providers</option>
            <option value="gcash">GCash Direct API</option>
            <option value="paymaya">PayMaya Checkout</option>
            <option value="stripe">Stripe / Credit Card</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Gateway Statuses</option>
            <option value="settled">Settled / Paid</option>
            <option value="pending">Pending Settlement</option>
            <option value="failed">Failed / Cancelled</option>
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
              <th>Gateway Txn ID</th>
              <th>Provider</th>
              <th>Patient Account</th>
              <th>Timestamp</th>
              <th class="text-end">Gross Amount (₱)</th>
              <th class="text-end">Gateway Fee (₱)</th>
              <th class="text-end">Net Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">GW-PAY-98124</span></td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-device-mobile me-1"></i> PayMaya / GCash</span></td>
              <td>
                <div class="fw-semibold text-dark">AR-PAT-881</div>
                <span class="fs-xs text-muted">Outpatient Consultation</span>
              </td>
              <td>2026-08-08 14:15</td>
              <td class="text-end font-monospace">₱2,500.00</td>
              <td class="text-end font-monospace text-muted">₱37.50</td>
              <td class="text-end text-success fw-bold font-monospace">₱2,462.50</td>
              <td><span class="badge bg-success-subtle text-success">Settled</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Payload Logs" data-bs-toggle="modal" data-bs-target="#gatewayDetailModal"><i class="ph ph-code"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Re-sync Status"><i class="ph ph-arrow-clockwise"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">GW-PAY-98123</span></td>
              <td><span class="badge bg-info-subtle text-info"><i class="ph ph-credit-card me-1"></i> Credit Card Gateway</span></td>
              <td>
                <div class="fw-semibold text-dark">AR-PAT-992</div>
                <span class="fs-xs text-muted">Inpatient Partial Deposit</span>
              </td>
              <td>2026-08-08 11:30</td>
              <td class="text-end font-monospace">₱15,000.00</td>
              <td class="text-end font-monospace text-muted">₱225.00</td>
              <td class="text-end text-success fw-bold font-monospace">₱14,775.00</td>
              <td><span class="badge bg-success-subtle text-success">Settled</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Payload Logs" data-bs-toggle="modal" data-bs-target="#gatewayDetailModal"><i class="ph ph-code"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Re-sync Status"><i class="ph ph-arrow-clockwise"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="font-monospace text-primary fw-bold">GW-PAY-98122</span></td>
              <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-device-mobile me-1"></i> GCash QR</span></td>
              <td>
                <div class="fw-semibold text-dark">AR-PAT-771</div>
                <span class="fs-xs text-muted">Pharmacy Outpatient</span>
              </td>
              <td>2026-08-08 09:05</td>
              <td class="text-end font-monospace text-muted text-decoration-line-through">₱1,200.00</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td><span class="badge bg-danger-subtle text-danger">Failed / Expired</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Error Log" data-bs-toggle="modal" data-bs-target="#gatewayDetailModal"><i class="ph ph-warning"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Gateway Detail & Webhook Payload -->
<div class="modal fade" id="gatewayDetailModal" tabindex="-1" aria-labelledby="gatewayDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="gatewayDetailModalLabel"><i class="ph ph-code me-2 text-primary"></i>Gateway API Transaction &amp; Webhook Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <span class="text-muted small d-block">Transaction ID</span>
            <span class="font-monospace text-primary fw-bold">GW-PAY-98124</span>
          </div>
          <div class="col-md-6">
            <span class="text-muted small d-block">Gateway Merchant ID</span>
            <span class="font-monospace text-dark">MERCHANT-HOSPITAL-09</span>
          </div>
        </div>
        <label class="form-label small fw-semibold">Webhook JSON Payload Data</label>
        <pre class="bg-dark text-light p-3 rounded-3 fs-xs font-monospace"><code>{
  "event": "payment.settled",
  "transaction_id": "GW-PAY-98124",
  "amount": 2500.00,
  "currency": "PHP",
  "provider": "paymaya",
  "patient_ref": "AR-PAT-881",
  "settlement_status": "SUCCESS",
  "timestamp": "2026-08-08T14:15:22+08:00"
}</code></pre>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-sm btn-primary" onclick="alert('Status re-synced with provider gateway!');"><i class="ph ph-arrows-clockwise me-1"></i> Force Webhook Re-Sync</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
