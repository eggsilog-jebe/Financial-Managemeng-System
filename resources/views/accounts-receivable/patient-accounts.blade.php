@extends('layouts.app')

@section('title', 'Patient & Customer Accounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'customers')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Patient Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Payor Accounts</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Patient Accounts Master List...');"><i class="ph ph-download-simple me-1"></i> Export Master List</button>
      <button id="btnCreateAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createAccountModal"><i class="ph ph-plus me-1"></i> Create Billing Account</button>
    </div>
  </div>

  <!-- Primary Executive Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Payor Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-users fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ count($accounts ?? []) }} Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Patient Receivable</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Pending Guarantees</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱0.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">PhilHealth Claims Filed</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-first-aid-kit fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="patientAccountTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Account Code &amp; Patient Name</th>
              <th>Payor Category</th>
              <th>HMO Policy / Coverage</th>
              <th>Coverage Limit</th>
              <th class="text-end">Balance Due (₱)</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts ?? [] as $acc)
            @php
              $code = is_array($acc) ? $acc['acc'] : $acc->account_number;
              $name = is_array($acc) ? $acc['name'] : $acc->patient_name;
              $bal = is_array($acc) ? $acc['balance'] : ('₱' . number_format($acc->current_balance, 2));
              $status = is_array($acc) ? $acc['status'] : $acc->status;
              $aData = [
                'acc' => $code,
                'name' => $name,
                'sub' => 'Patient Account',
                'category' => 'Patient Payor',
                'policy' => 'Standard Policy',
                'cap' => '₱0.00',
                'balance' => $bal,
                'status' => $status
              ];
            @endphp
            <tr class="account-row" style="cursor: pointer;" onclick="openPatientDetailsModal({{ json_encode($aData) }})">
              <td>
                <div class="fw-bold text-dark">{{ $name }}</div>
                <span class="fs-xs font-monospace text-muted">{{ $code }}</span>
              </td>
              <td><span class="badge bg-primary-subtle text-primary">Patient Payor</span></td>
              <td>Standard Policy</td>
              <td>₱0.00</td>
              <td class="text-end font-monospace fw-bold text-danger">{{ $bal }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> {{ $status }}</span></td>
              <td class="text-end"><button class="btn btn-sm btn-icon btn-outline-secondary" onclick="openPatientDetailsModal({{ json_encode($aData) }})"><i class="ph ph-eye"></i></button></td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No patient/payor accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="patientSummaryText">Showing {{ count($accounts ?? []) }} Payor Accounts</span>
      <nav aria-label="Accounts Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Patient & Payor Account Details (Clean & Executive Design) -->
<div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-labelledby="patientDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <!-- Header -->
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailAccCode">AR-PAT-881</span>
            <span class="badge bg-primary-subtle text-primary" id="detailAccCategory">Inpatient Self-Pay</span>
            <span class="badge bg-warning-subtle text-warning" id="detailAccStatus"><i class="ph ph-bed"></i> Active Admission</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAccName">Juan Dela Cruz</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Financial Metric Summary -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Outstanding Balance Due</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailAccBalance">₱24,500.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Approved HMO / Credit Cap</span>
              <h4 class="fw-bold text-dark mb-0 font-monospace" id="detailAccCap">₱200,000.00</h4>
            </div>
          </div>
        </div>

        <!-- Admission & Sub Details -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-user me-1 text-primary"></i> Patient Admission &amp; Ward Profile</h6>
          <p class="small text-dark fw-medium mb-0" id="detailAccSub">Patient #10429 — Room 402 (Inpatient Ward A)</p>
        </div>

        <!-- Master Info Grid -->
        <div class="row g-3">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
              <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-primary"></i> Insurance &amp; Guarantee</h6>
              <div class="d-flex flex-column gap-2 fs-xs">
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">Policy / Guarantee</span>
                  <span class="fw-bold text-dark" id="detailAccPolicy">Maxicare HMO</span>
                </div>
                <div class="d-flex justify-content-between pt-1">
                  <span class="text-muted">Primary Guarantor</span>
                  <span class="fw-semibold text-dark" id="detailAccGuarantor">Self / Maxicare</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
              <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-phone me-1 text-primary"></i> Contact &amp; Registry</h6>
              <div class="d-flex flex-column gap-2 fs-xs">
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">Contact Phone</span>
                  <span class="font-monospace text-dark" id="detailAccPhone">+63 917 882 1090</span>
                </div>
                <div class="d-flex justify-content-between pt-1">
                  <span class="text-muted">Admission Date</span>
                  <span class="font-monospace text-dark" id="detailAccAdmissionDate">2026-08-01</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ar.billing') }}" class="btn btn-sm btn-primary"><i class="ph ph-receipt me-1"></i> Issue Patient Invoice</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create New Billing Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="createAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Register Patient Billing Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="createAccountForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Account Reference <span class="text-danger">*</span></label>
              <input type="text" id="modalAccCode" class="form-control form-control-sm font-monospace" placeholder="e.g. AR-PAT-890" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Patient / Guarantor Full Name <span class="text-danger">*</span></label>
              <input type="text" id="modalAccName" class="form-control form-control-sm" placeholder="e.g. Gabriel Ramos" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payor Category <span class="text-danger">*</span></label>
              <select id="modalAccCategory" class="form-select form-select-sm" required>
                <option value="Inpatient Self-Pay">Inpatient Self-Pay</option>
                <option value="Commercial HMO">Commercial HMO</option>
                <option value="Government Guarantor">Government Guarantor (PhilHealth)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Ward / Room Location Details <span class="text-danger">*</span></label>
              <input type="text" id="modalAccSub" class="form-control form-control-sm" placeholder="e.g. Patient #10462 — Room 310" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Insurance / Policy Guarantee</label>
              <input type="text" id="modalAccPolicy" class="form-control form-control-sm" placeholder="e.g. Maxicare HMO (POL-10293)">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Approved Credit Cap (₱)</label>
              <input type="text" id="modalAccCap" class="form-control form-control-sm font-monospace" placeholder="e.g. ₱150,000.00" value="₱150,000.00">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Initial Balance Due (₱)</label>
              <input type="number" id="modalAccBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="0.00">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Contact Phone Number</label>
              <input type="text" id="modalAccPhone" class="form-control form-control-sm font-monospace" placeholder="+63 917 000 0000">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openPatientDetailsModal(acc) {
  if (!acc) return;

  document.getElementById('detailAccCode').textContent = acc.acc || 'AR-PAT-000';
  document.getElementById('detailAccName').textContent = acc.name || 'Patient Name';
  document.getElementById('detailAccCategory').textContent = acc.category || 'Self-Pay';
  document.getElementById('detailAccSub').textContent = acc.sub || 'Inpatient Admission Ward';
  document.getElementById('detailAccPolicy').textContent = acc.policy || 'None';
  document.getElementById('detailAccCap').textContent = acc.cap || '₱0.00';
  document.getElementById('detailAccBalance').textContent = acc.balance || '₱0.00';
  document.getElementById('detailAccPhone').textContent = acc.phone || '+63 900 000 0000';
  document.getElementById('detailAccGuarantor').textContent = acc.guarantor || 'Self Pay';
  document.getElementById('detailAccAdmissionDate').textContent = acc.admission_date || '-';

  const statusEl = document.getElementById('detailAccStatus');
  if (statusEl) {
    statusEl.textContent = acc.status;
    statusEl.className = 'badge ' + (acc.status_badge || 'bg-warning-subtle text-warning');
  }

  const modalEl = document.getElementById('patientDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('patientSearchInput');
  const summaryText = document.getElementById('patientSummaryText');
  const btnCreateAccount = document.getElementById('btnCreateAccount');
  const tabPills = document.querySelectorAll('#payorTabPills button');
  let currentFilter = 'all';

  if (btnCreateAccount) {
    btnCreateAccount.addEventListener('click', function() {
      const modalEl = document.getElementById('createAccountModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  const payorCategorySelect = document.getElementById('payorCategorySelect');
  if (payorCategorySelect) {
    payorCategorySelect.addEventListener('change', function() {
      currentFilter = this.value || 'all';
      filterPatients();
    });
  }

  function filterPatients() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.patient-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-category') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCategory = currentFilter === 'all' || rowCat.includes(currentFilter);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCategory && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Payor Account${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noPatientsRow');
    const tbody = document.querySelector('#patientTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noPatientsRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No patient or payor accounts found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterPatients);
    searchInput.addEventListener('keyup', filterPatients);
  }

  const createAccountForm = document.getElementById('createAccountForm');
  if (createAccountForm) {
    createAccountForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const codeVal = document.getElementById('modalAccCode').value;
      const nameVal = document.getElementById('modalAccName').value;
      const catVal = document.getElementById('modalAccCategory').value;
      const subVal = document.getElementById('modalAccSub').value;
      const policyVal = document.getElementById('modalAccPolicy').value || 'Self Pay';
      const capVal = document.getElementById('modalAccCap').value || '₱100,000.00';
      const rawBalance = parseFloat(document.getElementById('modalAccBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const phoneVal = document.getElementById('modalAccPhone').value || '+63 917 000 0000';

      let catBadge = 'bg-primary-subtle text-primary';
      if (catVal === 'Commercial HMO') catBadge = 'bg-info-subtle text-info';
      else if (catVal === 'Government Guarantor') catBadge = 'bg-success-subtle text-success';

      const accountObj = {
        acc: codeVal,
        name: nameVal,
        sub: subVal,
        category: catVal,
        cat_badge: catBadge,
        policy: policyVal,
        cap: capVal,
        balance: formattedBalance,
        status: 'Active Admission',
        status_badge: 'bg-warning-subtle text-warning',
        status_icon: 'ph-bed',
        phone: phoneVal,
        guarantor: nameVal,
        admission_date: new Date().toISOString().split('T')[0]
      };

      const tbody = document.querySelector('#patientTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'patient-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-category', catVal.toLowerCase());

        newRow.onclick = function() { openPatientDetailsModal(accountObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${codeVal}</span></td>
          <td>
            <div class="fw-semibold text-dark">${nameVal}</div>
            <div class="text-muted fs-xs">${subVal}</div>
          </td>
          <td><span class="badge ${catBadge}">${catVal}</span></td>
          <td>${policyVal}</td>
          <td class="font-monospace fs-xs">${capVal}</td>
          <td class="text-end fw-bold text-danger font-monospace">${formattedBalance}</td>
          <td><span class="badge bg-warning-subtle text-warning"><i class="ph ph-bed"></i> Active Admission</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Patient Ledger"><i class="ph ph-file-text"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Patient Ledger"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openPatientDetailsModal(accountObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('createAccountModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      createAccountForm.reset();
      filterPatients();
    });
  }

  filterPatients();
});
</script>
@endpush
