@extends('layouts.app')

@section('title', 'Credit Notes & Discounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

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

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Credit Notes &amp; Discounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Credit Notes &amp; Statutory Discounts</h1>
      <p class="text-muted fs-xs mb-0">Apply mandatory Senior Citizen (20%) &amp; PWD statutory discounts, charity medical subsidies, courtesy price adjustments, and billing corrections.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoicing & Billing', 'General Ledger']" 
          :tables="['credit_notes', 'invoices', 'journal_entries']"
          glImpact="DR 5010 Sales Discounts (Senior/PWD) / CR 1110/1120 AR Patient Copay"
          description="Applies statutory 20% Senior/PWD discounts and charity subsidies to patient bills." 
      />
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createCreditNoteModal">
        <i class="ph ph-plus me-1"></i> Issue Credit Adjustment
      </button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Approved &amp; Posted Credit Adjustments</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $totalCreditValue, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Management Approval (Drafts)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hourglass fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) $totalPendingApproval, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Credit Notes Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.credit-notes') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft (Pending Approval)</option>
            <option value="POSTED" {{ request('status') === 'POSTED' ? 'selected' : '' }}>Posted / Applied</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search CN #, invoice, patient..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Credit Note #</th>
              <th>Applied Invoice &amp; Patient</th>
              <th>Issue Date</th>
              <th>Adjustment Reason</th>
              <th class="text-end">Credit Amount</th>
              <th>Status</th>
              <th>Approved By</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($creditNotes as $cn)
            @php
              $amt = (float) $cn->amount;
              $statusBadge = match($cn->status) {
                'POSTED', 'APPLIED', 'APPROVED' => 'bg-success-subtle text-success',
                default                         => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $cn->credit_note_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $cn->patientAccount?->full_name ?? ($cn->invoice?->patientAccount?->full_name ?? 'Patient') }}</div>
                <div class="fs-xs text-muted font-monospace">Invoice: {{ $cn->invoice?->invoice_number ?? 'N/A' }}</div>
              </td>
              <td>{{ $cn->issue_date ? $cn->issue_date->format('M d, Y') : '—' }}</td>
              <td>
                <span class="badge bg-light text-dark border">{{ $cn->reason }}</span>
              </td>
              <td class="text-end font-monospace fw-bold text-danger fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $cn->status }}</span>
              </td>
              <td>
                <span class="fs-xs text-muted">{{ $cn->approver?->name ?? '—' }}</span>
              </td>
              <td class="text-end">
                @if($cn->status === 'DRAFT')
                  <form method="POST" action="{{ route('ar.credit-notes.approve', $cn->id) }}" onsubmit="return confirm('Authorize and post credit note {{ $cn->credit_note_number }} to General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fs-xs" title="Finance Manager Approval">
                      <i class="ph ph-stamp me-1"></i> Approve &amp; Post
                    </button>
                  </form>
                @else
                  <span class="badge bg-light text-muted border">
                    <i class="ph ph-check-double text-success me-1"></i> Settled
                  </span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No credit notes found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $creditNotes->firstItem() ?? 0 }} - {{ $creditNotes->lastItem() ?? 0 }} of {{ $creditNotes->total() }} Records</span>
      <div>
        {{ $creditNotes->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Credit Note -->
<div class="modal fade" id="createCreditNoteModal" tabindex="-1" aria-labelledby="createCreditNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-light-subtle border-bottom py-3 px-4">
        <div class="d-flex align-items-center gap-2">
          <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center">
            <i class="ph ph-receipt-x fs-4"></i>
          </span>
          <div>
            <h5 class="modal-title font-weight-bold mb-0">Issue Credit Note &amp; Statutory Discount</h5>
            <span class="fs-xs text-muted">Apply Senior Citizen / PWD 20% discount, charity relief, or balance write-offs</span>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" action="{{ route('ar.credit-notes.store') }}" id="creditNoteForm">
        @csrf
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label small fw-semibold" for="target_invoice_select">Target Open Invoice <span class="text-danger">*</span></label>
              <select name="invoice_id" id="target_invoice_select" class="form-select form-select-sm" required>
                <option value="" data-gross="0" data-balance="0" data-has-statutory="0" data-statutory-type="" data-statutory-ref="">-- Choose Open Patient Invoice --</option>
                @foreach($openInvoices as $inv)
                  @php
                    $existingStatCN = $inv->creditNotes->whereIn('reason', ['SENIOR_CITIZEN_DISCOUNT', 'PWD_DISCOUNT', 'SENIOR_CITIZEN', 'PWD'])->whereIn('status', ['POSTED', 'APPLIED', 'DRAFT'])->first();
                    $hasStatutory = $inv->statutoryDiscounts->isNotEmpty() || $existingStatCN !== null;
                    $statutoryType = $existingStatCN ? $existingStatCN->reason : ($inv->statutoryDiscounts->isNotEmpty() ? $inv->statutoryDiscounts->first()->discount_type : '');
                    $statutoryRef = $existingStatCN ? $existingStatCN->credit_note_number : ($inv->statutoryDiscounts->isNotEmpty() ? 'Intake RA 9994/10754' : '');
                  @endphp
                  <option value="{{ $inv->id }}" 
                          data-gross="{{ $inv->gross_total }}" 
                          data-balance="{{ $inv->patient_copay_balance }}"
                          data-has-statutory="{{ $hasStatutory ? '1' : '0' }}"
                          data-statutory-type="{{ $statutoryType }}"
                          data-statutory-ref="{{ $statutoryRef }}">
                      {{ $inv->invoice_number }} — {{ $inv->patient_name }} (Open Copay: ₱{{ number_format((float) $inv->patient_copay_balance, 2) }}){{ $hasStatutory ? ' [Statutory Applied]' : '' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-5">
              <label class="form-label small fw-semibold" for="credit_reason_select">Adjustment Reason / Type <span class="text-danger">*</span></label>
              <select name="reason" id="credit_reason_select" class="form-select form-select-sm" required>
                <option value="SENIOR_CITIZEN_DISCOUNT">Statutory Senior Citizen Discount (20%)</option>
                <option value="PWD_DISCOUNT">Person with Disability (PWD) Discount (20%)</option>
                <option value="CHARITY_SUBSIDY">Medical Social Service Charity Subsidy</option>
                <option value="EMPLOYEE_SUBSIDY">Hospital Employee &amp; Dependent Subsidy</option>
                <option value="BILLING_ADJUSTMENT">Disputed Item / Procedure Cancellation</option>
              </select>
            </div>
          </div>

          <!-- Statutory Discount Alert Banner -->
          <div class="alert alert-warning border border-warning-subtle d-flex align-items-start gap-2 py-2 px-3 mb-3 rounded-3" id="statutoryWarningAlert" style="display: none;">
            <i class="ph ph-info fs-4 text-warning flex-shrink-0 mt-1"></i>
            <div class="fs-xs">
              <strong>Statutory Discount Already Applied:</strong> A statutory discount is already applied to this invoice (<span id="statutoryRefDisplay" class="font-monospace fw-bold"></span>). To change or re-apply statutory relief, please void/reverse the existing credit note first. Non-statutory adjustments (Charity, Billing Corrections) are still allowed.
            </div>
          </div>

          <!-- Dynamic Discount Calculation Card -->
          <div class="card border border-primary-subtle bg-light-subtle rounded-3 p-3 mb-3" id="discountCalculationCard" style="display: none;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="fs-xs text-uppercase fw-bold text-primary d-flex align-items-center gap-1">
                <i class="ph ph-calculator fs-5"></i> Dynamic Discount Calculation &amp; Balance Forecast
              </span>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-xs" id="invoiceTag">INV</span>
            </div>

            <div class="row g-2 mb-3 text-center">
              <div class="col-4">
                <div class="bg-white p-2 rounded-2 border">
                  <span class="text-muted fs-xs d-block">Gross Billed Total</span>
                  <strong class="font-monospace text-dark fs-6" id="dispGross">₱0.00</strong>
                </div>
              </div>
              <div class="col-4">
                <div class="bg-white p-2 rounded-2 border">
                  <span class="text-muted fs-xs d-block">Open Copay Balance</span>
                  <strong class="font-monospace text-danger fs-6" id="dispBalance">₱0.00</strong>
                </div>
              </div>
              <div class="col-4">
                <div class="bg-white p-2 rounded-2 border" id="dispForecastCard">
                  <span class="text-muted fs-xs d-block">Forecasted Remaining</span>
                  <strong class="font-monospace text-success fs-6" id="dispRemaining">₱0.00</strong>
                </div>
              </div>
            </div>

            <!-- Quick Auto-Fill Preset Buttons -->
            <div>
              <span class="fs-xs text-muted fw-semibold d-block mb-1">Quick Auto-Fill Presets:</span>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 fs-xs" id="btn-preset-statutory-20">
                  <i class="ph ph-percent me-1"></i> Apply 20% Statutory Discount
                </button>
                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fs-xs" id="btnApply100Pct">
                  <i class="ph ph-check-circle me-1"></i> 100% Full Balance Write-Off
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 fs-xs" id="btnApply50Pct">
                  <i class="ph ph-divide me-1"></i> 50% Charity Subsidy
                </button>
              </div>
            </div>
          </div>

          <!-- Input Fields -->
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label small fw-semibold" for="credit_amount_input">Credit Amount (₱) <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text font-monospace fw-bold">₱</span>
                <input type="number" step="0.01" min="0.01" name="amount" id="credit_amount_input" class="form-control font-monospace fw-bold text-danger fs-6" placeholder="0.00" required>
              </div>
              <div class="form-text fs-xs text-muted mt-1" id="amountFeedback">
                Credit amount cannot exceed the target invoice's open patient copay balance.
              </div>
            </div>

            <div class="col-md-5">
              <label class="form-label small fw-semibold" for="issue_date_input">Issue Date</label>
              <input type="date" name="issue_date" id="issue_date_input" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <div class="form-check form-switch mt-3 pt-2 border-top">
            <input type="hidden" name="save_as_draft" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="save_as_draft" name="save_as_draft" value="1" checked>
            <label class="form-check-label fs-xs text-muted" for="save_as_draft">
              Save as Draft for Management Approval (Uncheck for immediate posting if authorized)
            </label>
          </div>
        </div>

        <div class="modal-footer bg-light-subtle border-top py-2 px-4">
          <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold" id="submitCreditNoteBtn">
            <i class="ph ph-check-circle me-1"></i> Submit Credit Adjustment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const invoiceSelect = document.getElementById('target_invoice_select');
  const reasonSelect = document.getElementById('credit_reason_select');
  const amountInput = document.getElementById('credit_amount_input') || document.getElementById('credit_amount');
  const calcCard = document.getElementById('discountCalculationCard');
  const dispGross = document.getElementById('dispGross');
  const dispBalance = document.getElementById('dispBalance');
  const dispRemaining = document.getElementById('dispRemaining');
  const invoiceTag = document.getElementById('invoiceTag');
  const amountFeedback = document.getElementById('amountFeedback');
  const submitBtn = document.getElementById('submitCreditNoteBtn');
  const statutoryWarningAlert = document.getElementById('statutoryWarningAlert');
  const statutoryRefDisplay = document.getElementById('statutoryRefDisplay');

  const btnApply20Pct = document.getElementById('btn-preset-statutory-20') || document.getElementById('btnApply20Pct');
  const btnApply100Pct = document.getElementById('btnApply100Pct');
  const btnApply50Pct = document.getElementById('btnApply50Pct');

  function getSelectedInvoiceData() {
    const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
      return null;
    }

    const gross = parseFloat(selectedOption.getAttribute('data-gross')) || 0;
    const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
    const hasStatutory = selectedOption.getAttribute('data-has-statutory') === '1';
    const statutoryType = selectedOption.getAttribute('data-statutory-type') || '';
    const statutoryRef = selectedOption.getAttribute('data-statutory-ref') || '';
    const label = selectedOption.textContent.split('—')[0].trim();

    return { gross, balance, hasStatutory, statutoryType, statutoryRef, label };
  }

  function syncStatutoryOptions(hasStatutory, statutoryType, statutoryRef) {
    const optSenior = reasonSelect.querySelector('option[value="SENIOR_CITIZEN_DISCOUNT"]');
    const optPwd = reasonSelect.querySelector('option[value="PWD_DISCOUNT"]');

    if (hasStatutory) {
      if (statutoryWarningAlert) {
        statutoryWarningAlert.style.display = 'flex';
        if (statutoryRefDisplay) {
          statutoryRefDisplay.textContent = statutoryRef || 'Active Record';
        }
      }

      const refText = statutoryRef ? `Currently Applied: ${statutoryRef}` : 'Currently Applied';

      if (statutoryType === 'PWD_DISCOUNT' || statutoryType === 'PWD') {
        if (optPwd) {
          optPwd.disabled = true;
          optPwd.textContent = `Person with Disability (PWD) Discount (20%) — (${refText})`;
        }
        if (optSenior) {
          optSenior.disabled = true;
          optSenior.textContent = 'Statutory Senior Citizen Discount (20%) — (Disabled: 1 Statutory Discount Limit)';
        }
      } else {
        // Senior Citizen was applied
        if (optSenior) {
          optSenior.disabled = true;
          optSenior.textContent = `Statutory Senior Citizen Discount (20%) — (${refText})`;
        }
        if (optPwd) {
          optPwd.disabled = true;
          optPwd.textContent = 'Person with Disability (PWD) Discount (20%) — (Disabled: 1 Statutory Discount Limit)';
        }
      }

      if (btnApply20Pct) {
        btnApply20Pct.disabled = true;
        btnApply20Pct.classList.add('disabled', 'opacity-50');
      }

      if (reasonSelect.value === 'SENIOR_CITIZEN_DISCOUNT' || reasonSelect.value === 'PWD_DISCOUNT') {
        reasonSelect.value = 'CHARITY_SUBSIDY';
      }
    } else {
      if (statutoryWarningAlert) statutoryWarningAlert.style.display = 'none';
      if (optSenior) {
        optSenior.disabled = false;
        optSenior.textContent = 'Statutory Senior Citizen Discount (20%)';
      }
      if (optPwd) {
        optPwd.disabled = false;
        optPwd.textContent = 'Person with Disability (PWD) Discount (20%)';
      }
      if (btnApply20Pct) {
        btnApply20Pct.disabled = false;
        btnApply20Pct.classList.remove('disabled', 'opacity-50');
      }
    }
  }

  function updateCalculationCard() {
    const data = getSelectedInvoiceData();
    if (!data) {
      calcCard.style.display = 'none';
      if (statutoryWarningAlert) statutoryWarningAlert.style.display = 'none';
      dispGross.textContent = '₱0.00';
      dispBalance.textContent = '₱0.00';
      dispRemaining.textContent = '₱0.00';
      return;
    }

    syncStatutoryOptions(data.hasStatutory, data.statutoryType, data.statutoryRef);
    calcCard.style.display = 'block';
    invoiceTag.textContent = data.label;
    dispGross.textContent = '₱' + data.gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    dispBalance.textContent = '₱' + data.balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const enteredAmount = parseFloat(amountInput.value) || 0;
    const remaining = Math.max(0, data.balance - enteredAmount);
    dispRemaining.textContent = '₱' + remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (enteredAmount > data.balance + 0.0001) {
      amountInput.classList.add('is-invalid');
      amountFeedback.className = 'form-text fs-xs text-danger fw-bold';
      amountFeedback.textContent = '⚠️ Credit amount (₱' + enteredAmount.toFixed(2) + ') exceeds open copay balance (₱' + data.balance.toFixed(2) + ').';
      submitBtn.disabled = true;
    } else {
      amountInput.classList.remove('is-invalid');
      amountFeedback.className = 'form-text fs-xs text-muted';
      amountFeedback.textContent = 'Amount will reduce patient copay from ₱' + data.balance.toFixed(2) + ' to ₱' + remaining.toFixed(2) + '.';
      submitBtn.disabled = false;
    }
  }

  function applyStatutory20Preset(grossAmount, balanceAmount) {
    // 1. Compute VAT-Exempt base (gross / 1.12)
    const vatExemptBase = grossAmount / 1.12;

    // 2. Compute 12% VAT waived
    const vatRelief = grossAmount - vatExemptBase;

    // 3. Compute 20% Statutory discount on the VAT-exempt base
    const statutoryDiscount = vatExemptBase * 0.20;

    // 4. Total Credit Note deduction (VAT Exemption + 20% Discount)
    const totalStatutoryCredit = vatRelief + statutoryDiscount; // 28,571.43 for 100k

    // Set form input value
    const creditAmountInput = document.getElementById('credit_amount_input') || document.getElementById('credit_amount');
    if (creditAmountInput) {
      const finalCredit = (typeof balanceAmount === 'number' && balanceAmount > 0)
        ? Math.min(balanceAmount, parseFloat(totalStatutoryCredit.toFixed(2)))
        : parseFloat(totalStatutoryCredit.toFixed(2));
      creditAmountInput.value = finalCredit.toFixed(2);
    }

    // Update live forecast card
    updateCalculationCard();
  }

  function autoFillDiscount(reason) {
    const data = getSelectedInvoiceData();
    if (!data) return;

    if (reason === 'SENIOR_CITIZEN_DISCOUNT' || reason === 'PWD_DISCOUNT') {
      if (data.hasStatutory) return;
      const baseGross = data.gross > 0 ? data.gross : data.balance;
      applyStatutory20Preset(baseGross, data.balance);
    } else if (reason === 'CHARITY_SUBSIDY') {
      amountInput.value = data.balance.toFixed(2);
      updateCalculationCard();
    } else if (reason === 'EMPLOYEE_SUBSIDY') {
      const discount = Math.min(data.balance, parseFloat((data.gross * 0.20).toFixed(2)));
      amountInput.value = discount.toFixed(2);
      updateCalculationCard();
    }
  }

  invoiceSelect.addEventListener('change', function () {
    const data = getSelectedInvoiceData();
    if (data) {
      syncStatutoryOptions(data.hasStatutory, data.statutoryType, data.statutoryRef);
      autoFillDiscount(reasonSelect.value);
    }
    updateCalculationCard();
  });

  reasonSelect.addEventListener('change', function () {
    autoFillDiscount(this.value);
  });

  amountInput.addEventListener('input', function () {
    updateCalculationCard();
  });

  if (btnApply20Pct) {
    btnApply20Pct.addEventListener('click', function () {
      const data = getSelectedInvoiceData();
      if (!data || data.hasStatutory) return;
      const baseGross = data.gross > 0 ? data.gross : data.balance;
      applyStatutory20Preset(baseGross, data.balance);
    });
  }

  const btnPresetStatutory20 = document.getElementById('btn-preset-statutory-20');
  if (btnPresetStatutory20 && btnPresetStatutory20 !== btnApply20Pct) {
    btnPresetStatutory20.addEventListener('click', function () {
      const data = getSelectedInvoiceData();
      if (!data || data.hasStatutory) return;
      const baseGross = data.gross > 0 ? data.gross : data.balance;
      applyStatutory20Preset(baseGross, data.balance);
    });
  }

  btnApply100Pct.addEventListener('click', function () {
    const data = getSelectedInvoiceData();
    if (!data) return;
    amountInput.value = data.balance.toFixed(2);
    updateCalculationCard();
  });

  btnApply50Pct.addEventListener('click', function () {
    const data = getSelectedInvoiceData();
    if (!data) return;
    amountInput.value = (data.balance * 0.50).toFixed(2);
    updateCalculationCard();
  });
});
</script>
@endpush
@endsection

