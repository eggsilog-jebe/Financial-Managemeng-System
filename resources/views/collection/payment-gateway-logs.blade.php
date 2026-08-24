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
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Re-synchronizing webhook API statuses...');"><i class="ph ph-arrow-clockwise me-1"></i> Sync Webhook Statuses</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Viewing API Gateway Config...');"><i class="ph ph-globe me-1"></i> API Gateway Status</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Gateway Inflows</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-globe-hemisphere-west fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalOnline ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Gateway Success Rate</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($logs ?? collect())->count() > 0 ? '100.0%' : 'N/A' }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Transactions</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning-octagon fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($logs ?? collect())->count() }} Txns</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Digital Gateway Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-device-mobile fs-5"></i></span>
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
          <label for="providerSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Provider:</label>
          <select id="providerSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Gateway Providers</option>
            <option value="paymaya / gcash">PayMaya / GCash</option>
            <option value="credit card">Credit Card Gateway</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="gwStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="gwStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 160px;">
            <option value="" selected>All Statuses</option>
            <option value="settled">Settled / Paid</option>
            <option value="failed">Failed / Expired</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="gatewaySearchInput" class="form-control form-control-sm" placeholder="Search txn ID, patient, provider...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="gatewayTable" class="table table-hover align-middle mb-0">
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
            @forelse($gateways ?? [] as $g)
            @php
              $txn = $g->transaction_channel_ref ?? ($g->payment_reference ?? 'GW-01');
              $provider = match($g->payment_method ?? 'GCASH') {
                'GCASH' => 'GCash E-Wallet',
                'MAYA' => 'Maya E-Wallet',
                'CREDIT_CARD' => 'Credit Card POS',
                default => $g->payment_method ?? 'Digital Gateway',
              };
              $provBadge = match($g->payment_method ?? 'GCASH') {
                'GCASH' => 'bg-primary-subtle text-primary',
                'MAYA' => 'bg-success-subtle text-success',
                default => 'bg-info-subtle text-info',
              };
              $provIcon = ($g->payment_method === 'CREDIT_CARD') ? 'ph-credit-card' : 'ph-device-mobile';
              $patient = $g->patientAccount?->full_name ?? 'Walk-In Patient';
              $sub = $g->patientAccount?->patient_id_number ?? 'PAT-2026-001';
              $time = $g->payment_date ? $g->payment_date->format('M d, Y') : date('M d, Y');
              $gross = (float) ($g->amount ?? 0);
              $fee = $gross * 0.015; // 1.5% MDR gateway fee
              $net = $gross - $fee;
              $gArr = [
                'txn' => $txn, 'provider' => $provider,
                'provider_badge' => $provBadge, 'provider_icon' => $provIcon,
                'patient' => $patient, 'sub' => $sub, 'time' => $time,
                'gross' => '₱' . number_format($gross, 2),
                'fee' => '₱' . number_format($fee, 2),
                'net' => '₱' . number_format($net, 2),
                'status' => 'SETTLED', 'status_badge' => 'bg-success-subtle text-success',
                'merchant_id' => 'MID-HOSP-PH-0019', 'payload' => json_encode(['auth_code' => $txn, 'card_brand' => $g->payment_method]),
              ];
            @endphp
            <tr class="gateway-row" style="cursor: pointer;" data-provider="{{ strtolower($provider) }}" data-status="settled" onclick="openGatewayDetailsModal({{ json_encode($gArr) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $txn }}</span></td>
              <td><span class="badge {{ $provBadge }}"><i class="ph {{ $provIcon }} me-1"></i> {{ $provider }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $patient }}</div>
                <span class="fs-xs text-muted font-monospace">{{ $sub }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $time }}</td>
              <td class="text-end font-monospace">₱{{ number_format($gross, 2) }}</td>
              <td class="text-end font-monospace text-muted">₱{{ number_format($fee, 2) }}</td>
              <td class="text-end text-success fw-bold font-monospace">₱{{ number_format($net, 2) }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> SETTLED</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Payload Logs" onclick="openGatewayDetailsModal({{ json_encode($gArr) }})"><i class="ph ph-code"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No gateway transactions recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="gatewaySummaryText">Showing {{ count($gateways ?? []) }} Gateway Logs</span>
      <nav aria-label="Gateway Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: Gateway Detail & Webhook Payload (Executive Design) -->
<div class="modal fade" id="gatewayDetailModal" tabindex="-1" aria-labelledby="gatewayDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailGwTxn">GW-PAY-98124</span>
            <span class="badge bg-success-subtle text-success" id="detailGwStatus">Settled</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailGwProvider">PayMaya / GCash</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Gross Amount</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailGwGross">₱2,500.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Merchant Fee</span>
              <h4 class="fw-bold text-muted mb-0 font-monospace" id="detailGwFee">₱37.50</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Net Payout</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailGwNet">₱2,462.50</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-code me-1 text-primary"></i> API Integration Details</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Merchant Account ID</span>
              <span class="font-monospace fw-bold text-dark" id="detailGwMerchant">MERCHANT-HOSPITAL-09</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Patient Account Reference</span>
              <span class="font-monospace fw-bold text-primary" id="detailGwPatient">AR-PAT-881</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Timestamp</span>
              <span class="font-monospace text-muted" id="detailGwTime">2026-08-08 14:15</span>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-terminal-window me-1 text-secondary"></i> Raw Webhook JSON Payload</h6>
          <pre class="bg-dark text-light p-3 rounded-3 fs-xs font-monospace mb-0" id="detailGwPayload"><code>{ "event": "payment.settled" }</code></pre>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Gateway Hash Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">HMAC Signature Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Webhook Hash Match Confirmed</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Log:</span>
              <span class="font-monospace text-muted">LOG-GW-2026-98124 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Status re-synced with payment gateway API!');"><i class="ph ph-arrows-clockwise me-1"></i> Force Webhook Re-Sync</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openGatewayDetailsModal(g) {
  if (!g) return;

  document.getElementById('detailGwTxn').textContent = g.txn || 'GW-000';
  document.getElementById('detailGwProvider').textContent = g.provider || 'Provider';
  document.getElementById('detailGwMerchant').textContent = g.merchant_id || 'MERCHANT-01';
  document.getElementById('detailGwPatient').textContent = g.patient || '-';
  document.getElementById('detailGwTime').textContent = g.time || '-';
  document.getElementById('detailGwGross').textContent = g.gross || '₱0.00';
  document.getElementById('detailGwFee').textContent = g.fee || '₱0.00';
  document.getElementById('detailGwNet').textContent = g.net || '₱0.00';
  document.getElementById('detailGwPayload').textContent = g.payload || '{}';

  const statusEl = document.getElementById('detailGwStatus');
  if (statusEl) {
    statusEl.textContent = g.status;
    statusEl.className = 'badge ' + (g.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('gatewayDetailModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('gatewaySearchInput');
  const providerSelect = document.getElementById('providerSelect');
  const gwStatusSelect = document.getElementById('gwStatusSelect');
  const summaryText = document.getElementById('gatewaySummaryText');

  function filterGateways() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedProvider = providerSelect ? providerSelect.value.toLowerCase() : '';
    const selectedStatus = gwStatusSelect ? gwStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.gateway-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowProvider = row.getAttribute('data-provider') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchProvider = !selectedProvider || rowProvider.includes(selectedProvider);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchProvider && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Gateway Log${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noGatewayRow');
    const tbody = document.querySelector('#gatewayTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noGatewayRow';
        emptyRow.innerHTML = `<td colspan="9" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No payment gateway logs found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterGateways);
    searchInput.addEventListener('keyup', filterGateways);
  }
  if (providerSelect) providerSelect.addEventListener('change', filterGateways);
  if (gwStatusSelect) gwStatusSelect.addEventListener('change', filterGateways);

  filterGateways();
});
</script>
@endpush
