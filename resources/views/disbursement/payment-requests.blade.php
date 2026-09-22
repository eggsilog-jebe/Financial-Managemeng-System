@extends('layouts.app')

@section('title', 'Disbursement Vouchers & Requests - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'payment-requests')

@section('content')
<div class="container-fluid p-4">
  <!-- Alerts -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-check-circle fs-4 me-2"></i>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning-circle fs-4 me-2"></i>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-start">
        <i class="ph ph-warning-circle fs-4 me-2 mt-1"></i>
        <div>
          <strong class="d-block mb-1">Validation Errors Encountered:</strong>
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement Management</li>
          <li class="breadcrumb-item active">Disbursement Vouchers &amp; Requests</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Disbursement Vouchers &amp; Payment Requisitions</h1>
      <p class="text-muted fs-xs mb-0">Create and monitor payment requests for departmental operating expenses, physician honorariums, supplier bills, and employee reimbursements.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['HRMS (Payroll Runs)', 'Vendor Invoices']" 
          description="Ingests payroll obligations and operational payment requests." 
      />
      <button id="btnEncodePayroll" class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#encodePayrollModal">
        <i class="ph ph-users-three me-1"></i> Encode Payroll Cutoff Run
      </button>
      <button id="btnCreateRequest" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createRequestModal">
        <i class="ph ph-plus me-1"></i> New Payment Request
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Requisitions</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-file-text fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalRequests ?? 0 }} Vouchers</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Pending Audit / Approval</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) ($pendingApproval ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Approved for Release</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-stamp fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-info font-monospace">₱{{ number_format((float) ($approvedAmount ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Released Payments</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalReleased ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Requisitions Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('disbursement.payment-requests') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="PREPARED" {{ request('status') === 'PREPARED' ? 'selected' : '' }}>Prepared (Pending Audit)</option>
            <option value="AUDITED" {{ request('status') === 'AUDITED' ? 'selected' : '' }}>Audited (Ready for Approval)</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved (Ready for Release)</option>
            <option value="RELEASED" {{ request('status') === 'RELEASED' ? 'selected' : '' }}>Released / Disbursed</option>
            <option value="VOIDED" {{ request('status') === 'VOIDED' ? 'selected' : '' }}>Voided</option>
          </select>
        </div>
        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search voucher #, payee, desc..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Voucher Ref #</th>
              <th>Payee &amp; Particulars</th>
              <th>Bank Account</th>
              <th>Payment Method</th>
              <th>Voucher Date</th>
              <th class="text-end">Amount (₱)</th>
              <th>Status</th>
              <th>Preparer</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vouchers as $v)
            @php
              $amt = (float) $v->net_disbursed_amount;
              $statusBadge = match($v->status) {
                'RELEASED' => 'bg-success-subtle text-success',
                'APPROVED' => 'bg-info-subtle text-info',
                'AUDITED'  => 'bg-primary-subtle text-primary',
                'VOIDED'   => 'bg-secondary-subtle text-secondary',
                default    => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $v->voucher_number }}</span>
                @if($v->check_or_eft_ref)
                  <div class="fs-xs text-muted font-monospace">Ref: {{ $v->check_or_eft_ref }}</div>
                @endif
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $v->payee_name }}</div>
                <div class="fs-xs text-muted">{{ $v->description ?? ($v->purchaseBill ? "Bill {$v->purchaseBill->bill_number}" : 'Departmental Requisition') }}</div>
              </td>
              <td>
                <div class="fs-xs fw-medium text-dark">{{ $v->bankAccount?->bank_name ?? 'Operating Bank' }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $v->bankAccount?->account_number ?? 'Acc' }}</div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">{{ str_replace('_', ' ', $v->payment_method) }}</span>
              </td>
              <td>{{ $v->voucher_date ? $v->voucher_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $v->status }}</span>
              </td>
              <td>
                <span class="fs-xs text-muted">{{ $v->preparer?->name ?? 'Staff' }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($v->status === 'PREPARED' || $v->status === 'DRAFT')
                    <form method="POST" action="{{ route('disbursement.payment-requests.audit', $v->id) }}" onsubmit="return confirm('Audit and verify voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="Internal Audit Verification">
                        <i class="ph ph-magnifying-glass me-1"></i> Audit
                      </button>
                    </form>
                    <form method="POST" action="{{ route('disbursement.payment-requests.void', $v->id) }}" onsubmit="return confirm('Void voucher {{ $v->voucher_number }}?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 fs-xs" title="Void Request">
                        <i class="ph ph-x"></i>
                      </button>
                    </form>
                  @elseif($v->status === 'AUDITED' || $v->status === 'APPROVED')
                    <a href="{{ route('disbursement.disbursement-approval') }}" class="btn btn-sm btn-primary py-1 px-2 fs-xs">
                      <i class="ph ph-shield-check me-1"></i> Workstation
                    </a>
                  @else
                    <span class="badge bg-light text-muted border">
                      <i class="ph ph-check-double text-success me-1"></i> Disbursed
                    </span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No disbursement vouchers found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $vouchers->firstItem() ?? 0 }} - {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} Requisitions</span>
      <div>
        {{ $vouchers->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Payment Request -->
<x-modal 
    id="createRequestModal" 
    title="Create Payment Request / Voucher"
    subtitle="Requisition payment for operating expenses, professional honorariums, or supplier bills."
    icon="ph-receipt"
    iconVariant="primary"
    size="lg"
    :scrollable="true"
    :centered="true"
    formAction="{{ route('disbursement.payment-requests.store') }}"
    formMethod="POST"
    submitText="Submit Payment Voucher"
    submitIcon="ph-check"
>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Disbursing Bank Account <span class="text-danger">*</span></label>
      <select name="bank_account_id" class="form-select form-select-sm" required>
        <option value="">-- Choose Operating Bank --</option>
        @foreach($bankAccounts as $b)
          <option value="{{ $b->id }}">{{ $b->bank_name }} ({{ $b->account_number }}) - Bal: ₱{{ number_format((float) $b->balance, 2) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
      <select name="payment_method" class="form-select form-select-sm" required>
        <option value="CHECK" selected>Bank Check (Standard)</option>
        <option value="PESONET_EFT">PESONet Electronic Bank Transfer</option>
        <option value="INSTAPAY">InstaPay Real-Time Transfer</option>
        <option value="TELEGRAPHIC_TRANSFER">Telegraphic Transfer (TT / Wire)</option>
        <option value="PETTY_CASH">Petty Cash Voucher</option>
      </select>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Payee Legal Name <span class="text-danger">*</span></label>
      <input type="text" name="payee_name" class="form-control form-control-sm" placeholder="e.g. Metro Medical Supplies Inc" required>
    </div>
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Voucher Date <span class="text-danger">*</span></label>
      <input type="date" name="voucher_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Gross Amount (₱) <span class="text-danger">*</span></label>
      <div class="input-group input-group-sm">
        <span class="input-group-text">₱</span>
        <input type="number" step="0.01" name="gross_amount" class="form-control font-monospace" placeholder="0.00" required>
      </div>
    </div>
    <div class="col-md-6">
      <label class="form-label small fw-semibold">Withheld Tax Amount (EWT / 1601-C)</label>
      <div class="input-group input-group-sm">
        <span class="input-group-text">₱</span>
        <input type="number" step="0.01" name="withheld_tax_amount" class="form-control font-monospace" placeholder="0.00" value="0.00">
      </div>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label small fw-semibold">Particulars / Payment Purpose</label>
    <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Biomedical equipment quarterly maintenance payment">
  </div>

  <!-- Link to AP Purchase Bill or Payroll Run -->
  <div class="p-3 bg-light rounded-3 mb-1">
    <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-link me-1 text-primary"></i> Link to AP Bill or Payroll (Optional)</h6>
    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label small text-muted mb-0">Link Purchase Bill</label>
        <select name="purchase_bill_id" class="form-select form-select-sm">
          <option value="">-- None (Manual Request) --</option>
          @foreach($openBills as $ob)
            <option value="{{ $ob->id }}">{{ $ob->bill_number }} - {{ $ob->vendor?->name }} (Due: ₱{{ number_format((float) $ob->balance_due, 2) }})</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label small text-muted mb-0">Link Payroll Run</label>
        <select name="payroll_run_id" class="form-select form-select-sm">
          <option value="">-- None --</option>
          @foreach($openPayrolls as $pr)
            <option value="{{ $pr->id }}">{{ $pr->payroll_run_number }} (Net Pay: ₱{{ number_format((float) $pr->total_net_pay, 2) }})</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
</x-modal>

<!-- Modal: Encode Payroll Cutoff Run -->
<x-modal 
    id="encodePayrollModal" 
    title="Encode Employee Payroll Cutoff Run"
    subtitle="Standalone hospital compensation entry with automated statutory deductions (SSS, PhilHealth, Pag-IBIG, 1601-C) & PFRS GL posting."
    icon="ph-users-three"
    iconVariant="primary"
    size="xl"
    :scrollable="true"
    :centered="true"
    formAction="{{ route('disbursement.payroll.store') }}"
    formId="formEncodePayroll"
    formMethod="POST"
    submitText="Post & Disburse Payroll Run"
    submitIcon="ph-check-circle"
>
  <!-- Cutoff Period & Disbursing Bank -->
  <div class="card border border-light-subtle rounded-3 p-3 mb-3 bg-light-subtle">
    <div class="fw-bold text-uppercase fs-xs text-secondary mb-2 border-bottom pb-1">
      <i class="ph ph-calendar-check me-1"></i> Payroll Cutoff Schedule &amp; Disbursing Bank
    </div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-dark">Cutoff Start <span class="text-danger">*</span></label>
        <input type="date" name="cutoff_start" id="payrollCutoffStart" class="form-control form-control-sm" value="{{ old('cutoff_start', date('Y-m-01')) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-dark">Cutoff End <span class="text-danger">*</span></label>
        <input type="date" name="cutoff_end" id="payrollCutoffEnd" class="form-control form-control-sm" value="{{ old('cutoff_end', date('Y-m-15')) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-dark">Payout Date <span class="text-danger">*</span></label>
        <input type="date" name="payout_date" id="payrollPayoutDate" class="form-control form-control-sm" value="{{ old('payout_date', date('Y-m-15')) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-dark">Disbursing Bank Account <span class="text-danger">*</span></label>
        <select name="disbursement_bank_account_id" id="payrollBankSelect" class="form-select form-select-sm" required onchange="updatePayrollBankBalance()">
          <option value="">-- Choose Bank Account --</option>
          @foreach($bankAccounts as $b)
            <option value="{{ $b->id }}" data-balance="{{ $b->balance }}" {{ old('disbursement_bank_account_id') == $b->id ? 'selected' : ($loop->first ? 'selected' : '') }}>
              {{ $b->bank_name }} ({{ $b->account_number }}) - ₱{{ number_format((float) $b->balance, 2) }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <!-- Statutory Deduction Notice -->
  <div class="alert alert-info py-2 px-3 fs-xs rounded-3 mb-3 d-flex align-items-center">
    <i class="ph ph-info me-2 fs-5 flex-shrink-0"></i>
    <div>
      <strong>Automated Double-Entry Posting:</strong> Submitting this run automatically calculates Philippine statutory contributions (SSS EE/ER, PhilHealth EE/ER, Pag-IBIG EE/ER), BIR Form 1601-C withholding tax, and posts balanced journal lines (Salaries Expense <code>6010</code>, Statutory Payables <code>2120-2150</code>, Cash in Bank <code>1020</code>).
    </div>
  </div>

  <!-- Employees Table Section -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="fw-bold mb-0 text-dark fs-xs text-uppercase">
      <i class="ph ph-user-list me-1 text-primary"></i> Personnel Compensation Roster
    </h6>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-xs btn-outline-secondary" onclick="loadHospitalSampleRoster()">
        <i class="ph ph-arrows-clockwise me-1"></i> Reset Sample Roster
      </button>
      <button type="button" class="btn btn-xs btn-outline-primary" onclick="addPayrollEmployeeRow()">
        <i class="ph ph-user-plus me-1"></i> Add Personnel Line
      </button>
    </div>
  </div>

  <div class="table-responsive border rounded-3 mb-3" style="max-height: 380px;">
    <table class="table table-sm table-hover align-middle mb-0" id="payrollEmployeesTable">
      <thead class="table-light text-muted fs-xs text-uppercase sticky-top">
        <tr>
          <th style="width: 40px;">#</th>
          <th style="min-width: 130px;">Employee ID <span class="text-danger">*</span></th>
          <th style="min-width: 180px;">Employee Full Name <span class="text-danger">*</span></th>
          <th style="min-width: 160px;">Department <span class="text-danger">*</span></th>
          <th style="min-width: 120px;">Basic Salary (₱) <span class="text-danger">*</span></th>
          <th style="min-width: 100px;">Overtime (₱)</th>
          <th style="min-width: 100px;">Allowances (₱)</th>
          <th style="min-width: 110px;" class="text-end">Est. Gross (₱)</th>
          <th style="min-width: 130px;">Bank / Ref #</th>
          <th style="width: 50px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody id="payrollEmployeesBody">
        <!-- Dynamically populated via JS -->
      </tbody>
    </table>
  </div>

  <!-- Payroll Run Financial Summary -->
  <div class="card border rounded-3 p-3 bg-light">
    <div class="row g-2 text-center text-md-start align-items-center">
      <div class="col-md-3">
        <span class="text-muted fs-xs text-uppercase d-block">Personnel Count</span>
        <span class="fw-bold fs-6 text-dark font-monospace" id="displayPersonnelCount">0</span>
        <span class="text-muted fs-xs">staff</span>
      </div>
      <div class="col-md-3">
        <span class="text-muted fs-xs text-uppercase d-block">Total Basic Salary</span>
        <span class="fw-bold fs-6 text-dark font-monospace" id="displayTotalBasic">₱0.00</span>
      </div>
      <div class="col-md-3">
        <span class="text-muted fs-xs text-uppercase d-block">Total Gross Compensation</span>
        <span class="fw-bold fs-6 text-primary font-monospace" id="displayTotalGross">₱0.00</span>
      </div>
      <div class="col-md-3">
        <span class="text-muted fs-xs text-uppercase d-block">Selected Bank Available Bal.</span>
        <span class="fw-bold fs-6 font-monospace text-dark" id="displayBankBalance">₱0.00</span>
      </div>
    </div>
    <div id="payrollBalanceWarning" class="alert alert-danger py-1 px-2 mt-2 mb-0 fs-xs d-none">
      <i class="ph ph-warning-circle me-1"></i> <strong>Warning:</strong> Total gross payroll exceeds the available balance of the selected bank account!
    </div>
  </div>
</x-modal>
@endsection

@push('scripts')
<script>
const standardHospitalStaff = [
  {
    id: 'EMP-2026-001',
    name: 'Dr. Roberto Mendoza, MD',
    dept: 'Medical / Physicians',
    basic: 65000.00,
    ot: 5000.00,
    allowances: 10000.00,
    bank: 'BDO-001293847'
  },
  {
    id: 'EMP-2026-002',
    name: 'Ma. Elena Reyes, RN',
    dept: 'Nursing',
    basic: 32000.00,
    ot: 3500.00,
    allowances: 2000.00,
    bank: 'BPI-992384712'
  },
  {
    id: 'EMP-2026-003',
    name: 'Juan Carlos Santos, RPh',
    dept: 'Pharmacy',
    basic: 28000.00,
    ot: 1200.00,
    allowances: 1500.00,
    bank: 'MBTC-382910482'
  }
];

const hospitalDepts = [
  'Medical / Physicians',
  'Nursing',
  'Pharmacy',
  'Laboratory',
  'Radiology & Imaging',
  'Emergency Medicine',
  'Administration & Finance',
  'Dietary & Nutrition',
  'Biomedical Engineering'
];

function buildDeptOptions(selectedDept = '') {
  return hospitalDepts.map(dept => {
    const isSelected = (dept === selectedDept) ? 'selected' : '';
    return `<option value="${dept}" ${isSelected}>${dept}</option>`;
  }).join('');
}

function addPayrollEmployeeRow(data = null) {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (!tbody) return;

  const rowIndex = tbody.children.length;
  const d = data || {
    id: `EMP-${String(rowIndex + 1).padStart(3, '0')}`,
    name: '',
    dept: 'Nursing',
    basic: 25000.00,
    ot: 0.00,
    allowances: 0.00,
    bank: ''
  };

  const tr = document.createElement('tr');
  tr.className = 'payroll-emp-row';
  tr.innerHTML = `
    <td class="text-center text-muted fs-xs row-num">${rowIndex + 1}</td>
    <td>
      <input type="text" name="employees[${rowIndex}][employee_id_number]" class="form-control form-control-sm font-monospace emp-id-input" value="${d.id}" placeholder="EMP-001" required>
    </td>
    <td>
      <input type="text" name="employees[${rowIndex}][employee_name]" class="form-control form-control-sm emp-name-input" value="${d.name}" placeholder="Full Name" required>
    </td>
    <td>
      <select name="employees[${rowIndex}][department]" class="form-select form-select-sm emp-dept-input" required>
        ${buildDeptOptions(d.dept)}
      </select>
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="employees[${rowIndex}][basic_salary]" class="form-control form-control-sm font-monospace text-end emp-basic-input" value="${Number(d.basic).toFixed(2)}" required oninput="calcPayrollTotals()">
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="employees[${rowIndex}][overtime_pay]" class="form-control form-control-sm font-monospace text-end emp-ot-input" value="${Number(d.ot || 0).toFixed(2)}" oninput="calcPayrollTotals()">
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="employees[${rowIndex}][allowances]" class="form-control form-control-sm font-monospace text-end emp-allowance-input" value="${Number(d.allowances || 0).toFixed(2)}" oninput="calcPayrollTotals()">
    </td>
    <td class="text-end font-monospace fw-bold fs-xs emp-gross-cell">
      ₱0.00
    </td>
    <td>
      <input type="text" name="employees[${rowIndex}][bank_account_number]" class="form-control form-control-sm font-monospace emp-bank-input" value="${d.bank || ''}" placeholder="Account / Ref">
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-link text-danger p-0" onclick="removePayrollEmployeeRow(this)" title="Remove line">
        <i class="ph ph-trash fs-5"></i>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  reindexPayrollRows();
  calcPayrollTotals();
}

function removePayrollEmployeeRow(btn) {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (!tbody) return;
  if (tbody.children.length <= 1) {
    alert('At least one employee line item is required for a payroll run.');
    return;
  }
  const tr = btn.closest('tr');
  tr.remove();
  reindexPayrollRows();
  calcPayrollTotals();
}

function reindexPayrollRows() {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (!tbody) return;

  const rows = tbody.querySelectorAll('tr.payroll-emp-row');
  rows.forEach((row, idx) => {
    const numCell = row.querySelector('.row-num');
    if (numCell) numCell.textContent = idx + 1;

    const idInput = row.querySelector('.emp-id-input');
    if (idInput) idInput.name = `employees[${idx}][employee_id_number]`;

    const nameInput = row.querySelector('.emp-name-input');
    if (nameInput) nameInput.name = `employees[${idx}][employee_name]`;

    const deptInput = row.querySelector('.emp-dept-input');
    if (deptInput) deptInput.name = `employees[${idx}][department]`;

    const basicInput = row.querySelector('.emp-basic-input');
    if (basicInput) basicInput.name = `employees[${idx}][basic_salary]`;

    const otInput = row.querySelector('.emp-ot-input');
    if (otInput) otInput.name = `employees[${idx}][overtime_pay]`;

    const allowInput = row.querySelector('.emp-allowance-input');
    if (allowInput) allowInput.name = `employees[${idx}][allowances]`;

    const bankInput = row.querySelector('.emp-bank-input');
    if (bankInput) bankInput.name = `employees[${idx}][bank_account_number]`;
  });
}

function calcPayrollTotals() {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (!tbody) return;

  const rows = tbody.querySelectorAll('tr.payroll-emp-row');
  let totalBasic = 0;
  let totalGross = 0;

  rows.forEach(row => {
    const basic = parseFloat(row.querySelector('.emp-basic-input')?.value) || 0;
    const ot = parseFloat(row.querySelector('.emp-ot-input')?.value) || 0;
    const allow = parseFloat(row.querySelector('.emp-allowance-input')?.value) || 0;
    const gross = basic + ot + allow;

    const grossCell = row.querySelector('.emp-gross-cell');
    if (grossCell) {
      grossCell.textContent = '₱' + gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    totalBasic += basic;
    totalGross += gross;
  });

  const countDisplay = document.getElementById('displayPersonnelCount');
  if (countDisplay) countDisplay.textContent = rows.length;

  const basicDisplay = document.getElementById('displayTotalBasic');
  if (basicDisplay) {
    basicDisplay.textContent = '₱' + totalBasic.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  const grossDisplay = document.getElementById('displayTotalGross');
  if (grossDisplay) {
    grossDisplay.textContent = '₱' + totalGross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Check against selected bank balance
  updatePayrollBankBalance(totalGross);
}

function updatePayrollBankBalance(totalGross = null) {
  const bankSelect = document.getElementById('payrollBankSelect');
  const bankDisplay = document.getElementById('displayBankBalance');
  const warning = document.getElementById('payrollBalanceWarning');

  if (!bankSelect || !bankDisplay) return;

  const opt = bankSelect.options[bankSelect.selectedIndex];
  const balance = opt ? (parseFloat(opt.getAttribute('data-balance')) || 0) : 0;

  bankDisplay.textContent = '₱' + balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  if (totalGross === null) {
    const grossText = document.getElementById('displayTotalGross')?.textContent || '0';
    totalGross = parseFloat(grossText.replace(/[^0-9.-]+/g, '')) || 0;
  }

  if (warning) {
    if (balance > 0 && totalGross > balance) {
      warning.classList.remove('d-none');
    } else {
      warning.classList.add('d-none');
    }
  }
}

function loadHospitalSampleRoster() {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  standardHospitalStaff.forEach(staff => addPayrollEmployeeRow(staff));
}

document.addEventListener('DOMContentLoaded', function () {
  const tbody = document.getElementById('payrollEmployeesBody');
  if (tbody && tbody.children.length === 0) {
    loadHospitalSampleRoster();
  }
  updatePayrollBankBalance();
});
</script>
@endpush
