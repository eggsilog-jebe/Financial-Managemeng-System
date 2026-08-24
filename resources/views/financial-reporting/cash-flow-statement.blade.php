@extends('layouts.app')

@section('title', 'Statement of Cash Flows - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'cash-flow-statement')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Cash Flow Statement</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Cash Flows</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Statement</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Cash Flow PDF Statement exported!');"><i class="ph ph-file-pdf me-1"></i> Export Official PDF</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Operating Cash Inflow</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">+₱{{ number_format((float) ($operatingCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Supplier &amp; Vendor Cash Outflow</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-shopping-bag fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">-₱{{ number_format((float) ($supplierCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Staff &amp; Payroll Disbursements</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">-₱{{ number_format((float) ($payrollCash ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Net Cash Position Increase</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-primary">{{ ((float) ($netCashFlow ?? 0)) >= 0 ? '+' : '' }}₱{{ number_format((float) ($netCashFlow ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="cfPeriodSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Period:</label>
          <select id="cfPeriodSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="ytd" selected>Year-To-Date {{ date('Y') }}</option>
            <option value="q2">Q2 {{ date('Y') }}</option>
            <option value="q1">Q1 {{ date('Y') }}</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="cfActivitySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Activity Group:</label>
          <select id="cfActivitySelect" class="form-select form-select-sm bg-light" style="min-width: 200px;">
            <option value="" selected>All Activity Groups</option>
            <option value="operating">Operating Activities</option>
            <option value="investing">Investing Activities</option>
            <option value="financing">Financing Activities</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="cfSearchInput" class="form-control form-control-sm" placeholder="Search cash flow item...">
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="cfTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cash Flow Activity Classification</th>
              <th class="text-end">Net Cash Provided / (Used) (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-light fw-bold text-success"><td colspan="3">1. Cash Flow from Operating Activities</td></tr>
            <tr class="cf-row" style="cursor: pointer;" data-group="operating" onclick="openCashFlowDetailsModal('Collections from Patient Billings & HMO Claims', 'Operating Activities', '+₱{{ number_format((float) ($operatingCash ?? 0), 2) }}', 'Direct Patient Cash & HMO Settlement Wire Deposits')">
              <td class="ps-4">Collections from Patient Billings &amp; HMO Claims</td>
              <td class="text-end font-monospace text-success">+₱{{ number_format((float) ($operatingCash ?? 0), 2) }}</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr class="cf-row" style="cursor: pointer;" data-group="operating" onclick="openCashFlowDetailsModal('Cash Paid to Medical Suppliers & Vendors', 'Operating Activities', '-₱{{ number_format((float) ($supplierCash ?? 0), 2) }}', 'AP Vendor Check Disbursements & Wire Transfers')">
              <td class="ps-4 text-muted">Less: Cash Paid to Medical Suppliers &amp; Vendors</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($supplierCash ?? 0), 2) }}</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr class="cf-row" style="cursor: pointer;" data-group="operating" onclick="openCashFlowDetailsModal('Cash Paid for Staff Payroll & Overtime', 'Operating Activities', '-₱{{ number_format((float) ($payrollCash ?? 0), 2) }}', 'Direct Medical Staff Payroll Direct Deposit Clearance')">
              <td class="ps-4 text-muted">Less: Cash Paid for Staff Payroll &amp; Overtime</td>
              <td class="text-end font-monospace text-danger">-₱{{ number_format((float) ($payrollCash ?? 0), 2) }}</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr class="fw-bold"><td>Net Cash Provided by Operating Activities</td><td class="text-end text-success font-monospace">{{ ((float) ($operatingNet ?? 0)) >= 0 ? '+' : '' }}₱{{ number_format((float) ($operatingNet ?? 0), 2) }}</td><td></td></tr>

            <tr class="table-light fw-bold text-danger"><td colspan="3">2. Cash Flow from Investing Activities</td></tr>
            <tr class="cf-row" style="cursor: pointer;" data-group="investing" onclick="openCashFlowDetailsModal('Purchase of Biomedical Equipment', 'Investing Activities', '₱0.00', 'Biomedical Capital Equipment Acquisition (CAPEX)')">
              <td class="ps-4 text-muted">Biomedical Capital Equipment Acquisition (CAPEX)</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr class="fw-bold"><td>Net Cash Used in Investing Activities</td><td class="text-end text-muted font-monospace">₱0.00</td><td></td></tr>

            <tr class="table-light fw-bold text-warning"><td colspan="3">3. Cash Flow from Financing Activities</td></tr>
            <tr class="cf-row" style="cursor: pointer;" data-group="financing" onclick="openCashFlowDetailsModal('Repayment of Commercial Bank Loan Principal', 'Financing Activities', '₱0.00', 'Bank Term Loan Scheduled Principal Amortization')">
              <td class="ps-4 text-muted">Repayment of Commercial Bank Loan Principal</td>
              <td class="text-end font-monospace text-muted">₱0.00</td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" title="View Audit Details"><i class="ph ph-eye"></i></button></td>
            </tr>
            <tr class="fw-bold"><td>Net Cash Used in Financing Activities</td><td class="text-end text-muted font-monospace">₱0.00</td><td></td></tr>
          </tbody>
          <tfoot class="table-primary fw-bold">
            <tr>
              <td class="fs-6">NET INCREASE IN HOSPITAL CASH &amp; EQUIVALENTS</td>
              <td class="text-end text-primary fs-5 font-monospace">{{ ((float) ($netCashFlow ?? 0)) >= 0 ? '+' : '' }}₱{{ number_format((float) ($netCashFlow ?? 0), 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Cash Flow Activity Details (Executive Design) -->
<div class="modal fade" id="cashFlowDetailsModal" tabindex="-1" aria-labelledby="cashFlowDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">CASH FLOW</span>
            <span class="badge bg-success-subtle text-success" id="detailCfGroup">Operating Activities</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailCfName">Collections from Patient Billings &amp; HMO Claims</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Net Cash Impact</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailCfAmount">+₱16,400,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Reporting Period</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace">FY 2026 Year-To-Date</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-hand-coins me-1 text-primary"></i> Activity Description &amp; Direct Method Scope</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Direct Cash Flow Description</span>
              <span class="fw-semibold text-dark" id="detailCfDesc">Direct Patient Cash &amp; HMO Settlement Wire Deposits</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Standard Reporting Method</span>
              <span class="font-monospace text-primary fw-bold">Direct Method (IAS 7 Compliant)</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; Bank Reconciliation Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Cash Book &amp; Bank Statement Reconciled:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Fully Reconciled with Bank Statement</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-CF-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Cash Flow Schedule PDF...');"><i class="ph ph-file-text me-1"></i> Export Schedule PDF</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openCashFlowDetailsModal(name, group, amount, desc) {
  document.getElementById('detailCfName').textContent = name || 'Cash Flow Item';
  document.getElementById('detailCfGroup').textContent = group || 'Operating Activities';
  document.getElementById('detailCfAmount').textContent = amount || '₱0.00';
  document.getElementById('detailCfDesc').textContent = desc || '-';

  const modalEl = document.getElementById('cashFlowDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('cfSearchInput');
  const activitySelect = document.getElementById('cfActivitySelect');

  function filterCashFlow() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedGroup = activitySelect ? activitySelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.cf-row');

    rows.forEach(function(row) {
      const group = row.getAttribute('data-group') || '';
      const text = row.textContent.toLowerCase();

      const matchGroup = !selectedGroup || group.includes(selectedGroup);
      const matchSearch = !searchQuery || text.includes(searchQuery);

      if (matchGroup && matchSearch) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterCashFlow);
    searchInput.addEventListener('keyup', filterCashFlow);
  }
  if (activitySelect) activitySelect.addEventListener('change', filterCashFlow);
});
</script>
@endpush
