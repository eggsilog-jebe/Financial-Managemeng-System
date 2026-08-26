@extends('layouts.app')

@section('title', 'Fiscal Year Planning - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'fiscal-planning')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Fiscal Year Planning</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Fiscal Year Planning &amp; Target Setting</h1>
      <p class="text-muted fs-xs mb-0">Plan hospital-wide annual financial budgets, establish department spending limits, and track total fund pool allocations.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Master Fiscal Plan...');"><i class="ph ph-download-simple me-1"></i> Export Master Plan</button>
      <button id="btnCreatePlan" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createPlanModal"><i class="ph ph-plus-circle me-1"></i> Create Plan Draft</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Master Allocated Budget</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-chart-line-up fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalAllocated ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Master Expensed / Spent</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-calculator fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format((float) ($totalSpent ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Remaining Budget Pool</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format((float) ($totalRemaining ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Fiscal Plans</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($budgets ?? collect())->count() }} {{ Str::plural('Plan', ($budgets ?? collect())->count()) }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="fiscalYearSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Fiscal Year:</label>
          <select id="fiscalYearSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Fiscal Years</option>
            <option value="2026">FY 2026 (Current)</option>
            <option value="2027">FY 2027 (Next)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="planStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Plan Status:</label>
          <select id="planStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Plan Statuses</option>
            <option value="active master budget">Active Master Budget</option>
            <option value="under review">Under Review</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="planSearchInput" class="form-control form-control-sm" placeholder="Search plan title, period, status...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="planTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Plan Title</th>
              <th>Fiscal Period</th>
              <th class="text-end">Revenue Target (₱)</th>
              <th class="text-end">Expense Budget (₱)</th>
              <th class="text-end">Target Net Margin (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($plans ?? [] as $p)
            @php
              $pArr = is_array($p) ? $p : [
                'title' => $p->plan_name ?? 'N/A', 'sub' => $p->description ?? 'N/A',
                'period' => ($p->start_date ?? 'N/A') . ' - ' . ($p->end_date ?? 'N/A'),
                'year' => $p->fiscal_year ?? date('Y'),
                'revenue' => '₱' . number_format($p->projected_revenue ?? 0, 2),
                'expense' => '₱' . number_format($p->projected_expense ?? 0, 2),
                'margin' => '₱' . number_format(($p->projected_revenue ?? 0) - ($p->projected_expense ?? 0), 2),
                'status' => $p->status ?? 'Draft', 'status_badge' => 'bg-warning-subtle text-warning',
                'status_icon' => 'ph-clock', 'resolution' => $p->resolution_number ?? 'N/A',
              ];
            @endphp
            <tr class="plan-row" style="cursor: pointer;" data-year="{{ $pArr['year'] }}" data-status="{{ strtolower($pArr['status']) }}" onclick="openFiscalPlanDetailsModal({{ json_encode($pArr) }})">
              <td>
                <div class="fw-bold text-dark">{{ $pArr['title'] }}</div>
                <span class="fs-xs text-muted">{{ $pArr['sub'] }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $pArr['period'] }}</td>
              <td class="text-end text-success font-monospace">{{ $pArr['revenue'] }}</td>
              <td class="text-end text-danger font-monospace">{{ $pArr['expense'] }}</td>
              <td class="text-end text-primary fw-bold font-monospace">{{ $pArr['margin'] }}</td>
              <td><span class="badge {{ $pArr['status_badge'] }}"><i class="ph {{ $pArr['status_icon'] }} me-1"></i> {{ $pArr['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Plan Details" onclick="openFiscalPlanDetailsModal({{ json_encode($pArr) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No fiscal plans available in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="planSummaryText">Showing {{ count($plans ?? []) }} Fiscal Plans</span>
      <nav aria-label="Fiscal Plan Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Fiscal Plan Details (Executive Design) -->
<div class="modal fade" id="fiscalPlanDetailsModal" tabindex="-1" aria-labelledby="fiscalPlanDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailPlanYear">FY 2026</span>
            <span class="badge bg-success-subtle text-success" id="detailPlanStatus"><i class="ph ph-check-circle me-1"></i> Active Master Budget</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailPlanTitle">FY 2026 Approved Operating Master Plan</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Revenue Target</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailPlanRevenue">₱120,000,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Expense Budget Cap</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailPlanExpense">₱85,000,000.00</h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Projected Operating Margin</span>
              <h4 class="fw-bold text-primary mb-0 font-monospace" id="detailPlanMargin">₱35,000,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-calendar me-1 text-primary"></i> Fiscal Schedule &amp; Governance</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Effective Fiscal Period</span>
              <span class="font-monospace fw-bold text-dark" id="detailPlanPeriod">Jan 01, 2026 - Dec 31, 2026</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Board Resolution Reference</span>
              <span class="font-monospace fw-bold text-primary" id="detailPlanResolution">RES-2025-99</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Board Approval Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Board of Trustees Sign-off:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Unanimously Approved</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Log Stamp:</span>
              <span class="font-monospace text-muted">LOG-PLAN-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Master Fiscal Plan PDF...');"><i class="ph ph-file-pdf me-1"></i> Export Master Plan PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Plan Draft -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createPlanModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Create Fiscal Year Plan Draft</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createPlanForm">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Master Plan Title <span class="text-danger">*</span></label>
              <input type="text" id="modalPlanTitle" class="form-control form-control-sm" placeholder="e.g. FY 2027 Master Operating Plan" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Fiscal Year <span class="text-danger">*</span></label>
              <input type="number" id="modalPlanYear" class="form-control form-control-sm" value="2027" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Revenue (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalPlanRevenue" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="130000000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Approved Expense Budget Cap (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalPlanExpense" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="90000000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Save Plan Draft</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openFiscalPlanDetailsModal(p) {
  if (!p) return;

  document.getElementById('detailPlanYear').textContent = 'FY ' + (p.year || '2026');
  document.getElementById('detailPlanTitle').textContent = p.title || 'Plan Title';
  document.getElementById('detailPlanRevenue').textContent = p.revenue || '₱0.00';
  document.getElementById('detailPlanExpense').textContent = p.expense || '₱0.00';
  document.getElementById('detailPlanMargin').textContent = p.margin || '₱0.00';
  document.getElementById('detailPlanPeriod').textContent = p.period || '-';
  document.getElementById('detailPlanResolution').textContent = p.resolution || 'RES-000';

  const statusEl = document.getElementById('detailPlanStatus');
  if (statusEl) {
    statusEl.textContent = p.status;
    statusEl.className = 'badge ' + (p.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('fiscalPlanDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('planSearchInput');
  const fiscalYearSelect = document.getElementById('fiscalYearSelect');
  const planStatusSelect = document.getElementById('planStatusSelect');
  const summaryText = document.getElementById('planSummaryText');
  const btnCreatePlan = document.getElementById('btnCreatePlan');

  if (btnCreatePlan) {
    btnCreatePlan.addEventListener('click', function() {
      const modalEl = document.getElementById('createPlanModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterPlans() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedYear = fiscalYearSelect ? fiscalYearSelect.value.toLowerCase() : '';
    const selectedStatus = planStatusSelect ? planStatusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.plan-row');
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
      summaryText.textContent = `Showing ${visibleCount} Fiscal Plan${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noPlanRow');
    const tbody = document.querySelector('#planTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noPlanRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No fiscal plans found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterPlans);
    searchInput.addEventListener('keyup', filterPlans);
  }
  if (fiscalYearSelect) fiscalYearSelect.addEventListener('change', filterPlans);
  if (planStatusSelect) planStatusSelect.addEventListener('change', filterPlans);

  const createPlanForm = document.getElementById('createPlanForm');
  if (createPlanForm) {
    createPlanForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const titleVal = document.getElementById('modalPlanTitle').value;
      const yearVal = document.getElementById('modalPlanYear').value;
      const rawRev = parseFloat(document.getElementById('modalPlanRevenue').value || 0);
      const rawExp = parseFloat(document.getElementById('modalPlanExpense').value || 0);
      const rawMargin = rawRev - rawExp;
      const formattedRev = '₱' + rawRev.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedExp = '₱' + rawExp.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const formattedMargin = '₱' + rawMargin.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const periodVal = `Jan 01, ${yearVal} - Dec 31, ${yearVal}`;

      const planObj = {
        title: titleVal,
        sub: 'Preliminary Board Proposal',
        period: periodVal,
        year: yearVal,
        revenue: formattedRev,
        expense: formattedExp,
        margin: formattedMargin,
        status: 'Under Review',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-clock',
        resolution: 'DRAFT-NEW-' + Math.floor(10 + Math.random() * 90)
      };

      const tbody = document.querySelector('#planTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'plan-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-year', yearVal);
        newRow.setAttribute('data-status', 'under review');

        newRow.onclick = function() { openFiscalPlanDetailsModal(planObj); };

        newRow.innerHTML = `
          <td>
            <div class="fw-bold text-dark">${titleVal}</div>
            <span class="fs-xs text-muted">Preliminary Board Proposal</span>
          </td>
          <td class="font-monospace fs-xs">${periodVal}</td>
          <td class="text-end text-success font-monospace">${formattedRev}</td>
          <td class="text-end text-danger font-monospace">${formattedExp}</td>
          <td class="text-end text-primary fw-bold font-monospace">${formattedMargin}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Under Review</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Plan Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Plan Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openFiscalPlanDetailsModal(planObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createPlanModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createPlanForm.reset();
      filterPlans();
    });
  }

  filterPlans();
});
</script>
@endpush
