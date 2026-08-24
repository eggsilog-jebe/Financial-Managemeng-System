@extends('layouts.app')

@section('title', 'Executive Reports - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'executive-reports')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Executive Summaries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Executive Financial Packs &amp; Board Briefs</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Opening Report Archive...');"><i class="ph ph-folder-open me-1"></i> Report Archive</button>
      <button id="btnCompileReport" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#compileReportModal"><i class="ph ph-plus-circle me-1"></i> Compile Board Pack Draft</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Published Board Packs</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-file-pdf fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($reports ?? []) }} Reports</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Cash Reserves</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($cashPool ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total YTD Net Margin</span>
          <span class="badge bg-{{ ((float) ($netIncome ?? 0)) >= 0 ? 'success' : 'danger' }}-subtle text-{{ ((float) ($netIncome ?? 0)) >= 0 ? 'success' : 'danger' }} p-2 rounded-2"><i class="ph ph-trend-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-{{ ((float) ($netIncome ?? 0)) >= 0 ? 'success' : 'danger' }}">₱{{ number_format((float) ($netIncome ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">External Audit Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Compliant</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="reportYearSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Year:</label>
          <select id="reportYearSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Reporting Years</option>
            <option value="2026">FY 2026 Reports</option>
            <option value="2025">FY 2025 Reports</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="reportStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="reportStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Statuses</option>
            <option value="published">Published to Board</option>
            <option value="draft">Under Draft</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="reportSearchInput" class="form-control form-control-sm" placeholder="Search report title, officer...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="executiveReportTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Executive Report Title</th>
              <th>Reporting Period</th>
              <th>Author / Officer</th>
              <th>Generated Date</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports ?? [] as $rep)
            @php
              $repData = [
                'title' => $rep['title'] ?? 'Executive Board Pack',
                'sub' => $rep['sub'] ?? 'Comprehensive Financial Review',
                'period' => $rep['period'] ?? 'FY 2026',
                'author' => $rep['author'] ?? 'Office of the CFO',
                'date' => $rep['date'] ?? date('Y-m-d'),
                'status' => $rep['status'] ?? 'Published',
                'status_badge' => $rep['status_badge'] ?? 'bg-success-subtle text-success',
                'resolution' => $rep['resolution'] ?? 'BOARD-RES-2026-Q2',
              ];
            @endphp
            <tr class="report-row" style="cursor: pointer;" onclick="openExecutiveReportDetailsModal({{ json_encode($repData) }})">
              <td>
                <div class="fw-bold text-dark">{{ $repData['title'] }}</div>
                <span class="fs-xs text-muted">{{ $repData['sub'] }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $repData['period'] }}</td>
              <td class="fs-xs text-muted">{{ $repData['author'] }}</td>
              <td class="font-monospace fs-xs">{{ $repData['date'] }}</td>
              <td><span class="badge {{ $repData['status_badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $repData['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Board Pack Details" onclick="openExecutiveReportDetailsModal({{ json_encode($repData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No executive reports generated in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="reportSummaryText">Showing {{ count($reports ?? []) }} Executive Reports</span>
      <nav aria-label="Report Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Executive Board Pack Details (Executive Design) -->
<div class="modal fade" id="executiveReportDetailsModal" tabindex="-1" aria-labelledby="executiveReportDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailReportRes">BOARD-RES-2026-Q2</span>
            <span class="badge bg-success-subtle text-success" id="detailReportStatus"><i class="ph ph-check-circle me-1"></i> Published to Board</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailReportTitle">Q2 2026 Executive Financial Performance Pack</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Reporting Period</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailReportPeriod">Apr 01 - Jun 30, 2026</h5>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Author / Executive Body</span>
              <h5 class="fw-bold text-primary mb-0 font-monospace" id="detailReportAuthor">Office of the CFO</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-files me-1 text-primary"></i> Included Financial Statements</h6>
          <div class="d-flex flex-wrap gap-2 fs-xs">
            <span class="badge bg-light text-dark border p-2"><i class="ph ph-check me-1 text-success"></i> Audited Balance Sheet</span>
            <span class="badge bg-light text-dark border p-2"><i class="ph ph-check me-1 text-success"></i> Income Statement (P&amp;L)</span>
            <span class="badge bg-light text-dark border p-2"><i class="ph ph-check me-1 text-success"></i> Statement of Cash Flows</span>
            <span class="badge bg-light text-dark border p-2"><i class="ph ph-check me-1 text-success"></i> Healthcare KPI Brief</span>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Board Presentation Stamp</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Board Approval Resolution:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Approved by Board of Directors</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-BOARD-2026-Q2 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Downloading Board Pack PDF...');"><i class="ph ph-download-simple me-1"></i> Download PDF Pack</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Compile Board Pack Draft -->
<div class="modal fade" id="compileReportModal" tabindex="-1" aria-labelledby="compileReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="compileReportModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Compile Executive Board Pack</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="compileReportForm">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Report Package Title <span class="text-danger">*</span></label>
              <input type="text" id="modalReportTitle" class="form-control form-control-sm" placeholder="e.g. Q3 2026 Executive Financial Performance Pack" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Reporting Year <span class="text-danger">*</span></label>
              <input type="number" id="modalReportYear" class="form-control form-control-sm" value="2026" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Author / Executive Officer</label>
              <input type="text" id="modalReportAuthor" class="form-control form-control-sm" value="Office of the Chief Financial Officer">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Presentation Date</label>
              <input type="date" id="modalReportDate" class="form-control form-control-sm" value="2026-09-15">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">CFO Executive Summary &amp; Management Notes <span class="text-danger">*</span></label>
              <input type="text" id="modalReportNotes" class="form-control form-control-sm" placeholder="Add executive commentary..." required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-file-pdf me-1"></i> Compile &amp; Publish Package</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openExecutiveReportDetailsModal(rep) {
  if (!rep) return;

  document.getElementById('detailReportTitle').textContent = rep.title || 'Board Pack Title';
  document.getElementById('detailReportPeriod').textContent = rep.period || '-';
  document.getElementById('detailReportAuthor').textContent = rep.author || 'CFO Office';
  document.getElementById('detailReportRes').textContent = rep.resolution || 'BOARD-RES-000';

  const statusEl = document.getElementById('detailReportStatus');
  if (statusEl) {
    statusEl.textContent = rep.status;
    statusEl.className = 'badge ' + (rep.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('executiveReportDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('reportSearchInput');
  const yearSelect = document.getElementById('reportYearSelect');
  const statusSelect = document.getElementById('reportStatusSelect');
  const summaryText = document.getElementById('reportSummaryText');
  const btnCompileReport = document.getElementById('btnCompileReport');

  if (btnCompileReport) {
    btnCompileReport.addEventListener('click', function() {
      const modalEl = document.getElementById('compileReportModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterReports() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedYear = yearSelect ? yearSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.report-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowYear = row.getAttribute('data-year') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchYear = !selectedYear || rowYear === selectedYear;
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchYear && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Executive Report${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReportRow');
    const tbody = document.querySelector('#executiveReportTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReportRow';
        emptyRow.innerHTML = `<td colspan="6" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No executive reports found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterReports);
    searchInput.addEventListener('keyup', filterReports);
  }
  if (yearSelect) yearSelect.addEventListener('change', filterReports);
  if (statusSelect) statusSelect.addEventListener('change', filterReports);

  const compileReportForm = document.getElementById('compileReportForm');
  if (compileReportForm) {
    compileReportForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const titleVal = document.getElementById('modalReportTitle').value;
      const yearVal = document.getElementById('modalReportYear').value;
      const authorVal = document.getElementById('modalReportAuthor').value;
      const dateVal = document.getElementById('modalReportDate').value;
      const nextRes = 'BOARD-RES-2026-' + Math.floor(10 + Math.random() * 90);

      const repObj = {
        title: titleVal,
        sub: 'Includes Balance Sheet, P&L, Cash Flow & KPI Analytics',
        period: `Jul 01 - Sep 30, ${yearVal}`,
        year: yearVal,
        author: authorVal,
        date: dateVal,
        status: 'Published to Board',
        status_badge: 'bg-success-subtle text-success',
        resolution: nextRes
      };

      const tbody = document.querySelector('#executiveReportTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'report-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-year', yearVal);
        newRow.setAttribute('data-status', 'published to board');

        newRow.onclick = function() { openExecutiveReportDetailsModal(repObj); };

        newRow.innerHTML = `
          <td>
            <div class="fw-bold text-dark">${titleVal}</div>
            <span class="fs-xs text-muted">Includes Balance Sheet, P&amp;L, Cash Flow &amp; KPI Analytics</span>
          </td>
          <td class="font-monospace fs-xs">Jul 01 - Sep 30, ${yearVal}</td>
          <td class="fs-xs text-muted">${authorVal}</td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Published to Board</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Board Pack Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Board Pack Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openExecutiveReportDetailsModal(repObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('compileReportModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      compileReportForm.reset();
      filterReports();
    });
  }

  filterReports();
});
</script>
@endpush
