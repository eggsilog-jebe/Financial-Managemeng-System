@extends('layouts.app')

@section('title', 'Tax Returns & Filing - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-returns')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Returns &amp; Filing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Returns &amp; Statutory Filings</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Sales/Revenue Ledger', 'Purchase/Disbursement Ledger', 'BIR 2550Q']" 
          description="Generates statutory VAT, income tax, and withholding tax filings." 
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Opening BIR Tax Calendar Deadlines...');"><i class="ph ph-calendar-check me-1"></i> BIR Tax Calendar</button>
      <button id="btnFileReturn" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#fileReturnModal"><i class="ph ph-file-arrow-up me-1"></i> File Statutory Return</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Filed Returns (This Year)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($returns ?? []) }} Returns</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Tax Paid (YTD)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">₱{{ number_format(($returns ?? collect())->where('status', 'PAID')->sum('tax_due'), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Tax Payable</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱{{ number_format(($returns ?? collect())->where('status', 'DRAFT')->sum('tax_due'), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">eFPS Portal Connection</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-globe fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Connected</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="returnFormSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Form Type:</label>
          <select id="returnFormSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All BIR Form Types</option>
            <option value="2550">BIR Form 2550Q (Quarterly VAT)</option>
            <option value="1601">BIR Form 1601EQ (Withholding Tax)</option>
            <option value="1702">BIR Form 1702 (Corporate Tax)</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="returnStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Status:</label>
          <select id="returnStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Statuses</option>
            <option value="filed">Filed &amp; Remitted</option>
            <option value="pending">Pending Payment</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="returnSearchInput" class="form-control form-control-sm" placeholder="Search form code, title, ref...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="taxReturnTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Form Code</th>
              <th>Description / Return Name</th>
              <th>Tax Period</th>
              <th>Due Date</th>
              <th class="text-end">Tax Payable (₱)</th>
              <th>Filing Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($returns ?? [] as $ret)
            @php
              $code = is_array($ret) ? $ret['code'] : ('BIR FORM ' . $ret->form_type);
              $formType = is_array($ret) ? $ret['form_type'] : $ret->form_type;
              $title = is_array($ret) ? $ret['title'] : ($ret->form_type . ' Tax Return');
              $ref = is_array($ret) ? $ret['ref'] : ('Ref: ' . $ret->return_number);
              $period = is_array($ret) ? $ret['period'] : $ret->period_covered;
              $due = is_array($ret) ? $ret['due'] : ($ret->filing_date ? $ret->filing_date->format('Y-m-d') : 'N/A');
              $payable = is_array($ret) ? $ret['payable'] : ('₱' . number_format($ret->tax_due, 2));
              $status = is_array($ret) ? $ret['status'] : $ret->status;
              $retData = [
                'code' => $code,
                'form_type' => $formType,
                'title' => $title,
                'ref' => $ref,
                'period' => $period,
                'due' => $due,
                'payable' => $payable,
                'status' => $status,
                'status_badge' => 'bg-success-subtle text-success'
              ];
            @endphp
            <tr class="return-row" style="cursor: pointer;" onclick="openTaxReturnDetailsModal({{ json_encode($retData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $code }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $title }}</div>
                <span class="fs-xs text-muted">{{ $ref }}</span>
              </td>
              <td class="font-monospace fs-xs">{{ $period }}</td>
              <td class="font-monospace fs-xs">{{ $due }}</td>
              <td class="text-end font-monospace fw-bold text-danger">{{ $payable }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> {{ $status }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Return Details" onclick="openTaxReturnDetailsModal({{ json_encode($retData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No tax returns filed in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="returnSummaryText">Showing {{ count($returns ?? []) }} Statutory Returns</span>
      <nav aria-label="Return Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Tax Return Details (Executive Design) -->
<div class="modal fade" id="taxReturnDetailsModal" tabindex="-1" aria-labelledby="taxReturnDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailReturnCode">BIR FORM 2550Q</span>
            <span class="badge bg-warning-subtle text-warning" id="detailReturnStatus"><i class="ph ph-clock me-1"></i> Pending Payment</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailReturnTitle">Quarterly Value Added Tax Return</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Net Tax Payable</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailReturnPayable">₱215,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Statutory Filing Due Date</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailReturnDue">2026-08-25</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-file-text me-1 text-primary"></i> Filing Metadata &amp; Period Scope</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Tax Period Covered</span>
              <span class="font-monospace fw-bold text-dark" id="detailReturnPeriod">Q2 2026</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">BIR eFPS Reference</span>
              <span class="font-monospace text-primary fw-bold" id="detailReturnRef">eFPS Confirmation Ref: 9940129</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; BIR eFPS Transmission Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Bureau of Internal Revenue Status:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Transmitted &amp; Electronic Ack Received</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-RET-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Downloading BIR Filing PDF Brief...');"><i class="ph ph-file-pdf me-1"></i> Download Return PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: File Statutory Tax Return -->
<div class="modal fade" id="fileReturnModal" tabindex="-1" aria-labelledby="fileReturnModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="fileReturnModalLabel"><i class="ph ph-file-arrow-up me-2 text-primary"></i>Record Statutory Tax Return Filing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="fileReturnForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR Form Code <span class="text-danger">*</span></label>
              <select id="modalReturnForm" class="form-select form-select-sm" required>
                <option value="BIR FORM 2550Q">BIR Form 2550Q (Quarterly VAT Return)</option>
                <option value="BIR FORM 1601EQ">BIR Form 1601EQ (Quarterly EWT Return)</option>
                <option value="BIR FORM 1702-EX">BIR Form 1702-EX (Corporate Income Tax)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Tax Period Covered <span class="text-danger">*</span></label>
              <input type="text" id="modalReturnPeriod" class="form-control form-control-sm" placeholder="e.g. Q3 2026 (Jul - Sep)" value="Q3 2026" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Statutory Due Date <span class="text-danger">*</span></label>
              <input type="date" id="modalReturnDue" class="form-control form-control-sm" value="2026-10-25" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Total Net Tax Payable (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalReturnPayable" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace text-danger fw-bold" placeholder="0.00" value="185000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Statutory Return</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openTaxReturnDetailsModal(ret) {
  if (!ret) return;

  document.getElementById('detailReturnCode').textContent = ret.code || 'BIR FORM';
  document.getElementById('detailReturnTitle').textContent = ret.title || 'Return Name';
  document.getElementById('detailReturnPeriod').textContent = ret.period || '-';
  document.getElementById('detailReturnDue').textContent = ret.due || '-';
  document.getElementById('detailReturnPayable').textContent = ret.payable || '₱0.00';
  document.getElementById('detailReturnRef').textContent = ret.ref || '-';

  const statusEl = document.getElementById('detailReturnStatus');
  if (statusEl) {
    statusEl.textContent = ret.status;
    statusEl.className = 'badge ' + (ret.status_badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('taxReturnDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('returnSearchInput');
  const formSelect = document.getElementById('returnFormSelect');
  const statusSelect = document.getElementById('returnStatusSelect');
  const summaryText = document.getElementById('returnSummaryText');
  const btnFileReturn = document.getElementById('btnFileReturn');

  if (btnFileReturn) {
    btnFileReturn.addEventListener('click', function() {
      const modalEl = document.getElementById('fileReturnModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterReturns() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedForm = formSelect ? formSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.return-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowForm = row.getAttribute('data-form') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchForm = !selectedForm || rowForm.includes(selectedForm);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchForm && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Statutory Return${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noReturnRow');
    const tbody = document.querySelector('#taxReturnTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noReturnRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No tax returns found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterReturns);
    searchInput.addEventListener('keyup', filterReturns);
  }
  if (formSelect) formSelect.addEventListener('change', filterReturns);
  if (statusSelect) statusSelect.addEventListener('change', filterReturns);

  const fileReturnForm = document.getElementById('fileReturnForm');
  if (fileReturnForm) {
    fileReturnForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const codeVal = document.getElementById('modalReturnForm').value;
      const periodVal = document.getElementById('modalReturnPeriod').value;
      const dueVal = document.getElementById('modalReturnDue').value;
      const rawPayable = parseFloat(document.getElementById('modalReturnPayable').value || 0);
      const formattedPayable = '₱' + rawPayable.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextRef = 'eFPS Confirmation Ref: ' + Math.floor(9000000 + Math.random() * 999999);

      const retObj = {
        code: codeVal,
        form_type: codeVal.includes('2550') ? '2550' : '1601',
        title: 'Statutory Tax Return',
        ref: nextRef,
        period: periodVal,
        due: dueVal,
        payable: formattedPayable,
        status: 'Pending Payment',
        status_badge: 'bg-warning-subtle text-warning'
      };

      const tbody = document.querySelector('#taxReturnTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'return-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-form', retObj.form_type);
        newRow.setAttribute('data-status', 'pending payment');

        newRow.onclick = function() { openTaxReturnDetailsModal(retObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${codeVal}</span></td>
          <td>
            <div class="fw-semibold text-dark">Statutory Tax Return</div>
            <span class="fs-xs text-muted">${nextRef}</span>
          </td>
          <td class="font-monospace fs-xs">${periodVal}</td>
          <td class="font-monospace fs-xs">${dueVal}</td>
          <td class="text-end font-monospace fw-bold text-danger">${formattedPayable}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-clock me-1"></i> Pending Payment</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Return Details"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Return Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openTaxReturnDetailsModal(retObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('fileReturnModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      fileReturnForm.reset();
      filterReturns();
    });
  }

  filterReturns();
});
</script>
@endpush
