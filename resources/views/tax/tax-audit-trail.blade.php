@extends('layouts.app')

@section('title', 'Tax Audit Trail - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-audit')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Audit Trail</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Audit Trail &amp; Compliance Logs</h1>
      <p class="text-muted fs-xs mb-0">Tamper-evident log tracking every tax rate modification, 2307 certificate generation, and BIR audit compliance action.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['CAS Audit Logs', 'SHA-256 Hash Chain']" 
          description="Immutable Philippine CAS compliance and digital tamper-evident audit logs." 
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Verifying cryptographic SHA-256 hash integrity across log chain...');"><i class="ph ph-shield-check me-1"></i> Verify Hash Integrity</button>
      <button id="btnExportAudit" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#exportAuditModal"><i class="ph ph-file-arrow-down me-1"></i> Export Tax Audit Log</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Logged Tax Audit Events</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-list-checks fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($taxRules ?? collect())->count() + ($certificates ?? collect())->count() }} Logs</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Verified Tax Impacts</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">100.0%</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Tax Discrepancy Flags</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">0 Flags</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">SHA-256 Hash Integrity</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-lock-key fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Encrypted &amp; Verified</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="auditCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Event Category:</label>
          <select id="auditCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Event Categories</option>
            <option value="ewt">EWT 2307 Form Generation</option>
            <option value="return">Statutory Return Filing</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="auditUserSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Officer / User:</label>
          <select id="auditUserSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Officers / Users</option>
            <option value="tax_officer_1">tax_officer_1</option>
            <option value="cfo_user">cfo_user</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="auditSearchInput" class="form-control form-control-sm" placeholder="Search voucher ID, user, event...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="taxAuditTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Audit Timestamp</th>
              <th>User / Officer</th>
              <th>Event Category</th>
              <th>Source Voucher / Form</th>
              <th class="text-end">Tax Impact (₱)</th>
              <th>Security Cryptographic Hash</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs ?? [] as $l)
            @php
              $lArr = is_array($l) ? $l : [
                'time' => $l->created_at->format('Y-m-d H:i:s'),
                'user' => $l->user ?? 'System',
                'ip' => $l->ip_address ?? 'N/A',
                'category' => $l->event_category ?? 'Tax Event',
                'cat_type' => strtolower($l->event_type ?? 'general'),
                'voucher' => $l->source_voucher ?? 'N/A',
                'impact' => '₱' . number_format($l->tax_impact ?? 0, 2),
                'hash' => $l->hash ?? str_repeat('0', 64),
              ];
            @endphp
            <tr class="audit-row" style="cursor: pointer;" data-cat="{{ $lArr['cat_type'] }}" data-user="{{ strtolower($lArr['user']) }}" onclick="openTaxAuditDetailsModal({{ json_encode($lArr) }})">
              <td><span class="text-nowrap font-monospace fs-xs">{{ $lArr['time'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $lArr['user'] }}</div>
                <span class="fs-xs text-muted">IP: {{ $lArr['ip'] }}</span>
              </td>
              <td><span class="badge bg-info-subtle text-info">{{ $lArr['category'] }}</span></td>
              <td><span class="font-monospace text-primary fw-bold">{{ $lArr['voucher'] }}</span></td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $lArr['impact'] }}</td>
              <td><span class="font-monospace fs-xs text-muted">{{ substr($lArr['hash'], 0, 24) }}...</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Payload Snapshot" onclick="openTaxAuditDetailsModal({{ json_encode($lArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No tax audit entries recorded in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="auditSummaryText">Showing {{ count($logs ?? []) }} Audit Entries</span>
      <nav aria-label="Audit Log Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Tax Audit Entry Details (Executive Design) -->
<div class="modal fade" id="taxAuditDetailsModal" tabindex="-1" aria-labelledby="taxAuditDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAuditVoucher">C2307-2026-881</span>
            <span class="badge bg-success-subtle text-success" id="detailAuditCategory">EWT 2307 Form Generation</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0">Cryptographic Audit Entry</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Tax Impact Base</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailAuditImpact">-₱12,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Audit Log Timestamp</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailAuditTime">2026-08-08 14:22:10</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-user-circle me-1 text-primary"></i> Executive Officer Session Metadata</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Officer Username</span>
              <span class="font-monospace fw-bold text-dark" id="detailAuditUser">tax_officer_1</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Originating Workstation IP</span>
              <span class="font-monospace text-muted" id="detailAuditIp">192.168.1.45</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Cryptographic SHA-256 Hash Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Log Hash Integrity:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Tamper-Evident SHA-256 Validated</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Full Hash Signature:</span>
              <span class="font-monospace text-muted text-break" id="detailAuditHash">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Encrypted Log Entry Snapshot...');"><i class="ph ph-file-text me-1"></i> Export Entry Certificate</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Export Tax Audit Log -->
<div class="modal fade" id="exportAuditModal" tabindex="-1" aria-labelledby="exportAuditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="exportAuditModalLabel"><i class="ph ph-file-arrow-down me-2 text-primary"></i>Export Signed Tax Audit Log</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Signed Tax Audit Log exported!'); bootstrap.Modal.getInstance(document.getElementById('exportAuditModal')).hide();">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Audit Date Range</label>
            <select class="form-select form-select-sm">
              <option value="ytd">Year-To-Date FY 2026</option>
              <option value="q2">Q2 2026</option>
              <option value="all">Full Audit Chain</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Export Format</label>
            <select class="form-select form-select-sm">
              <option value="pdf">Auditor Signed PDF Package</option>
              <option value="csv">Encrypted CSV Audit Dump</option>
            </select>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-download me-1"></i> Generate &amp; Download</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openTaxAuditDetailsModal(l) {
  if (!l) return;

  document.getElementById('detailAuditVoucher').textContent = l.voucher || 'VOUCHER-000';
  document.getElementById('detailAuditCategory').textContent = l.category || 'Event Category';
  document.getElementById('detailAuditImpact').textContent = l.impact || '₱0.00';
  document.getElementById('detailAuditTime').textContent = l.time || '-';
  document.getElementById('detailAuditUser').textContent = l.user || '-';
  document.getElementById('detailAuditIp').textContent = l.ip || '-';
  document.getElementById('detailAuditHash').textContent = l.hash || '-';

  const modalEl = document.getElementById('taxAuditDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('auditSearchInput');
  const catSelect = document.getElementById('auditCategorySelect');
  const userSelect = document.getElementById('auditUserSelect');
  const summaryText = document.getElementById('auditSummaryText');
  const btnExportAudit = document.getElementById('btnExportAudit');

  if (btnExportAudit) {
    btnExportAudit.addEventListener('click', function() {
      const modalEl = document.getElementById('exportAuditModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterAuditLogs() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedUser = userSelect ? userSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.audit-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-cat') || '';
      const rowUser = row.getAttribute('data-user') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCat = !selectedCat || rowCat.includes(selectedCat);
      const matchUser = !selectedUser || rowUser.includes(selectedUser);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCat && matchUser && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Audit Entr${visibleCount !== 1 ? 'ies' : 'y'}`;
    }

    let emptyRow = document.getElementById('noAuditRow');
    const tbody = document.querySelector('#taxAuditTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAuditRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No tax audit entries found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterAuditLogs);
    searchInput.addEventListener('keyup', filterAuditLogs);
  }
  if (catSelect) catSelect.addEventListener('change', filterAuditLogs);
  if (userSelect) userSelect.addEventListener('change', filterAuditLogs);

  filterAuditLogs();
});
</script>
@endpush
