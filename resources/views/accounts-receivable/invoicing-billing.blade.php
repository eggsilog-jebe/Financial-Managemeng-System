@extends('layouts.app')

@section('title', 'Invoicing & Patient Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'invoicing')

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

  <!-- Header & Toolbar -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient Billing &amp; Invoicing Hub</h1>
      <p class="text-muted fs-xs mb-0">Automated ingestion dashboard for BDMS/SPRS clinical encounters, PhilHealth case rates, and HMO guarantees.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <x-integration-badge 
          type="external" 
          :systems="['BDMS', 'LIS (Lab)', 'RIS (Imaging)', 'PMS (Pharmacy)', 'IBMS', 'HICS (HMO Claims)']" 
          glImpact="DR 1110 (AR Copay) / DR 1120 (PhilHealth) / DR 1130 (HMO) / DR 4910 (Discounts) / CR 4010-4060 (Revenue)" 
          description="Central billing engine converting clinical orders into accounting receivables." 
      />
      <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fs-xs fw-semibold">
        <i class="ph ph-plugs-connected me-1"></i> BDMS / SPRS Ingestion Active
      </span>
      <a href="{{ route('ar.invoices.index') }}" class="btn btn-outline-primary btn-sm" title="Refresh Ingested Encounters">
        <i class="ph ph-arrows-clockwise me-1"></i> Sync Ingestion Queue
      </a>
      <a href="#" class="btn btn-outline-secondary btn-sm" onclick="alert('Exporting Billing Register CSV...'); return false;">
        <i class="ph ph-download-simple me-1"></i> Export Billing Register CSV
      </a>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
        <i class="ph ph-receipt me-1"></i> New Patient Invoice
      </button>
    </div>
  </div>

  <!-- Executive Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Billed Encounters</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark font-monospace">₱{{ number_format((float) $totalBilled, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Patient Copay</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) $totalPending, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Settled / Paid</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $totalPaid, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Invoices Monitoring Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.invoices.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="statusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Invoice Status:</label>
          <select id="statusSelect" name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Unpaid / Open</option>
            <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Partial</option>
            <option value="SETTLED" {{ request('status') === 'SETTLED' ? 'selected' : '' }}>Settled</option>
            <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Paid</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search invoice #, patient, MRN..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Invoice #</th>
              <th>Patient MRN &amp; Name</th>
              <th>Invoice Date</th>
              <th class="text-end">Gross Total</th>
              <th class="text-end">Insurance / Claims</th>
              <th class="text-end">Statutory Disc</th>
              <th class="text-end">Patient Copay</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $inv)
            @php
              $gross = (float) $inv->total_amount;
              $insurance = (float) $inv->insurance_covered;
              $disc = (float) $inv->discount_amount;
              $copay = (float) $inv->patient_payable;
              $statusBadge = match($inv->status) {
                'PAID', 'SETTLED' => 'bg-success-subtle text-success',
                'PARTIAL'         => 'bg-warning-subtle text-warning',
                default           => 'bg-danger-subtle text-danger',
              };

              $drawerPayload = [
                'invoice_number' => $inv->invoice_number,
                'patient_name'   => $inv->patientAccount?->full_name ?? 'Unknown Patient',
                'patient_mrn'    => $inv->patientAccount?->patient_id_number ?? 'MRN-UNSET',
                'admission_type' => strtoupper($inv->patientAccount?->admission_type ?? 'INPATIENT'),
                'invoice_date'   => $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : '—',
                'status'         => $inv->status,
                'gross_total'        => $gross,
                'insurance'          => $insurance,
                'discount'           => $disc,
                'copay'              => $copay,
                'statutory_category' => $inv->effective_discount_category,
                'gl_reference'       => 'JE-REV-' . $inv->invoice_number,
                'gl_url'             => url('/general-ledger/journal-entries') . '?search=JE-REV-' . $inv->invoice_number,
                'items'          => $inv->items->map(fn($i) => [
                  'department'   => $i->department ?? $i->revenue_category ?? 'CLINICAL',
                  'description'  => $i->description,
                  'quantity'     => (float) $i->quantity,
                  'unit_price'   => (float) $i->unit_price,
                  'gross_amount' => (float) $i->gross_amount,
                ])->values()->toArray(),
                'philhealth'     => $inv->philhealthClaim ? [
                  'series_no'    => $inv->philhealthClaim->claim_series_number,
                  'member_pin'   => $inv->philhealthClaim->member_pin ?? 'N/A',
                  'icd_code'     => $inv->philhealthClaim->primary_icd_code ?? 'N/A',
                  'case_code'    => $inv->philhealthClaim->primary_case_code ?? 'N/A',
                  'amount'       => (float) $inv->philhealthClaim->total_case_rate_amount,
                ] : null,
                'hmo'            => $inv->hmoClaims->first() ? [
                  'provider'     => $inv->hmoClaims->first()->hmo_provider,
                  'limit'        => (float) $inv->hmoClaims->first()->approved_limit,
                  'claimed'      => (float) $inv->hmoClaims->first()->claimed_amount,
                  'loa_number'   => $inv->hmoClaims->first()->loa_number ?? 'N/A',
                ] : null,
                'statutory'      => $inv->statutoryDiscounts->first() ? [
                  'type'         => $inv->statutoryDiscounts->first()->discount_type,
                  'id_number'    => $inv->statutoryDiscounts->first()->id_card_number ?? 'N/A',
                  'amount'       => (float) $inv->statutoryDiscounts->first()->discount_amount,
                ] : null,
              ];
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $inv->invoice_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark d-flex align-items-center gap-1">
                  {{ $inv->patientAccount?->full_name ?? 'Unknown Patient' }}
                  @if($inv->effective_discount_category === 'SENIOR_CITIZEN')
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 10px;"><i class="ph ph-identification-card me-1"></i>Senior 20%</span>
                  @elseif($inv->effective_discount_category === 'PWD')
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle ms-1" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4 !important; font-size: 10px;"><i class="ph ph-wheelchair me-1"></i>PWD 20%</span>
                  @endif
                </div>
                <div class="fs-xs text-muted font-monospace">{{ $inv->patientAccount?->patient_id_number ?? 'MRN' }}</div>
              </td>
              <td>{{ $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : '—' }}</td>
              <td class="text-end font-monospace text-dark fw-semibold">₱{{ number_format($gross, 2) }}</td>
              <td class="text-end font-monospace text-info">₱{{ number_format($insurance, 2) }}</td>
              <td class="text-end font-monospace text-muted">₱{{ number_format($disc, 2) }}</td>
              <td class="text-end font-monospace fw-bold text-danger fs-6">₱{{ number_format($copay, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $inv->status }}</span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="View Inspection Drawer" onclick="openInvoiceDetailsDrawer(this)" data-invoice="{{ json_encode($drawerPayload) }}">
                    <i class="ph ph-eye me-1"></i> View Details
                  </button>
                  <a href="{{ route('ar.invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" title="Print Billing Statement">
                    <i class="ph ph-printer me-1"></i> Statement
                  </a>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No ingested patient encounter invoices found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} Ingested Invoices</span>
      <div>
        {{ $invoices->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas Inspection Drawer: Encounter Details -->
<div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="invoiceDetailsDrawer" style="width: 850px; max-width: 92vw;" aria-labelledby="invoiceDetailsDrawerLabel">
  <div class="offcanvas-header border-bottom bg-light">
    <div>
      <h5 class="offcanvas-title font-weight-bold text-dark" id="invoiceDetailsDrawerLabel">
        <i class="ph ph-file-text me-2 text-primary"></i>Encounter Inspection Drawer
      </h5>
      <span class="font-monospace text-primary fw-bold fs-6" id="drawerInvoiceNumber"></span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-4">
    <!-- Patient Profile Header -->
    <div class="p-3 bg-light rounded-3 border mb-4">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <h6 class="fw-bold mb-0 text-dark" id="drawerPatientName"></h6>
          <span class="fs-xs font-monospace text-muted" id="drawerPatientMrn"></span>
        </div>
        <div class="text-end">
          <div class="d-flex align-items-center justify-content-end gap-1 mb-1">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="drawerAdmissionType"></span>
            <span id="drawerStatutoryBadge"></span>
          </div>
          <div>
            <span class="badge bg-secondary-subtle text-dark" id="drawerStatus"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Section A: Clinical Line Items Table -->
    <div class="mb-4">
      <h6 class="fw-bold text-dark mb-2"><i class="ph ph-list-numbers me-1 text-primary"></i> Ingested Clinical Charge Sheet</h6>
      <div class="table-responsive border rounded-3">
        <table class="table table-sm table-striped align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th class="text-nowrap">Dept</th>
              <th class="text-nowrap">Description / Particulars</th>
              <th class="text-center text-nowrap">Qty</th>
              <th class="text-end text-nowrap">Unit Price</th>
              <th class="text-end text-nowrap">Gross (₱)</th>
            </tr>
          </thead>
          <tbody id="drawerItemsBody">
            <!-- Dynamic Rows populated by JS -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Section B: Third-Party & Statutory Coverage Breakdown -->
    <div class="mb-4">
      <h6 class="fw-bold text-dark mb-2"><i class="ph ph-shield-check me-1 text-primary"></i> Insurance &amp; Statutory Coverage Breakdown</h6>
      <div class="row g-2">
        <!-- PhilHealth Card -->
        <div class="col-md-6">
          <div class="p-3 border rounded-3 bg-white h-100">
            <span class="badge bg-info-subtle text-info fw-semibold mb-2"><i class="ph ph-hospital me-1"></i> PhilHealth Case Rate</span>
            <div id="drawerPhilhealthInfo" class="fs-xs text-muted">
              <span class="text-muted">No PhilHealth deduction applied.</span>
            </div>
          </div>
        </div>

        <!-- HMO Guarantee Card -->
        <div class="col-md-6">
          <div class="p-3 border rounded-3 bg-white h-100">
            <span class="badge bg-primary-subtle text-primary fw-semibold mb-2"><i class="ph ph-shield me-1"></i> HMO Guarantee Letter</span>
            <div id="drawerHmoInfo" class="fs-xs text-muted">
              <span class="text-muted">No HMO guarantee applied.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Statutory Discount Banner -->
      <div id="drawerStatutoryBanner" class="mt-2 p-2.5 bg-warning-subtle border border-warning-subtle rounded-3 text-warning-emphasis fs-xs" style="display: none;">
        <i class="ph ph-tag me-1"></i> <span id="drawerStatutoryText"></span>
      </div>
    </div>

    <!-- Section C: Financial Summary Card -->
    <div class="card border-0 bg-light p-3 rounded-3 mb-4">
      <div class="d-flex justify-content-between mb-1 fs-xs text-muted">
        <span>Gross Billable Charges:</span>
        <span class="font-monospace text-dark fw-semibold" id="drawerGross"></span>
      </div>
      <div class="d-flex justify-content-between mb-1 fs-xs text-muted">
        <span>Statutory Discount (20% + VAT Relief):</span>
        <span class="font-monospace text-muted" id="drawerDiscount"></span>
      </div>
      <div class="d-flex justify-content-between mb-1 fs-xs text-muted">
        <span>Insurance Coverage (PhilHealth + HMO):</span>
        <span class="font-monospace text-info" id="drawerInsurance"></span>
      </div>
      <hr class="my-2 border-secondary-subtle">
      <div class="d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark fs-sm">NET PATIENT COPAY:</span>
        <span class="font-monospace fw-bold text-danger fs-5" id="drawerCopay"></span>
      </div>
    </div>

    <!-- Section D: Linked General Ledger Journal Entry -->
    <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between">
      <div>
        <div class="fs-xs text-muted">Linked GL Journal Entry Reference:</div>
        <div class="font-monospace fw-bold text-primary" id="drawerGlReference"></div>
      </div>
      <a id="drawerGlLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary fs-xs">
        <i class="ph ph-arrow-square-out me-1"></i> View Journal Entry in GL
      </a>
    </div>
  </div>
</div>

<!-- Modal: Create Patient Invoice -->
<x-modal 
    id="createInvoiceModal" 
    title="Generate Patient Discharge Billing Statement"
    subtitle="Audit clinical charges, PhilHealth case rates, dual HMO limits & generate official billing statement."
    icon="ph-receipt"
    iconVariant="primary"
    size="xl"
    :scrollable="true"
    :centered="true"
    formAction="{{ route('ar.invoices.store') }}"
    formId="formCreateInvoice"
    formMethod="POST"
    submitText="Generate & Post Patient Invoice"
    submitIcon="ph-check"
>
  <!-- Step 1: Patient Selection & Dates -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label small fw-semibold text-dark">Patient Account Profile <span class="text-danger">*</span></label>
      <select name="patient_account_id" id="invPatientSelect" class="form-select form-select-sm" required onchange="handlePatientSelectForInvoice(this)">
        <option value="">-- Choose Registered Patient Profile --</option>
        @foreach($patients as $p)
          <option value="{{ $p->id }}" 
                  data-discount="{{ $p->discount_category }}" 
                  data-idcard="{{ $p->id_card_number }}" 
                  data-hmo="{{ $p->hmo_provider }}">
            {{ $p->full_name }} (MRN: {{ $p->patient_id_number }}) &bull; {{ $p->admission_type }} [{{ $p->discount_category ?? 'NONE' }}]
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small fw-semibold text-dark">Invoice / Billing Date <span class="text-danger">*</span></label>
      <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
    </div>
    <div class="col-md-3">
      <label class="form-label small fw-semibold text-dark">Payment Due Date</label>
      <input type="date" name="due_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
    </div>
  </div>

  <!-- Step 2: Statutory & Insurance Adjustments -->
  <div class="card border border-light-subtle bg-light-subtle p-3 rounded-3 mb-3">
    <h6 class="fw-bold text-dark fs-xs text-uppercase mb-2">
      <i class="ph ph-scales me-1 text-primary"></i> Statutory Deductions &amp; Third-Party Coverage
    </h6>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">Statutory Discount Category</label>
        <select name="discount_type" id="invDiscountType" class="form-select form-select-sm">
          <option value="NONE" selected>None / Regular</option>
          <option value="SENIOR_CITIZEN">Senior Citizen (RA 9994 20%)</option>
          <option value="PWD">PWD (RA 10754 20%)</option>
          <option value="EMPLOYEE">Hospital Employee Subsidy</option>
          <option value="CHARITY">Charity / Indigent Relief</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">Statutory ID Card Number</label>
        <input type="text" name="id_card_number" id="invIdCard" class="form-control form-control-sm font-monospace" placeholder="OSCA / PWD ID">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">PhilHealth Primary Case Code</label>
        <input type="text" name="philhealth_primary_case_code" class="form-control form-control-sm font-monospace" placeholder="e.g. J18.9 or RVS-47562">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted">PhilHealth Case Rate (₱)</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text">₱</span>
          <input type="number" step="0.01" min="0" name="philhealth_primary_case_rate_amount" class="form-control font-monospace" placeholder="0.00">
        </div>
      </div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-muted">Primary HMO Provider</label>
        <select id="invPrimaryHmoSelect" class="form-select form-select-sm" onchange="syncInvoiceDualHmo()">
          <option value="" selected>None / Direct Self-Pay</option>
          <option value="Maxicare Healthcare Corporation">Maxicare Healthcare Corporation</option>
          <option value="Intellicare (Asalus Corporation)">Intellicare (Asalus Corporation)</option>
          <option value="Medicard Philippines, Inc.">Medicard Philippines, Inc.</option>
          <option value="PhilCare (PhilhealthCare, Inc.)">PhilCare (PhilhealthCare, Inc.)</option>
          <option value="Cocolife Healthcare">Cocolife Healthcare</option>
          <option value="Etiqa Life &amp; General Insurance">Etiqa Life &amp; General Insurance</option>
          <option value="ValuCare Health Systems, Inc.">ValuCare Health Systems, Inc.</option>
          <option value="Pacific Cross Philippines">Pacific Cross Philippines</option>
          <option value="InLife Health Care">InLife Health Care (Insular)</option>
          <option value="CareHealth Plus Systems">CareHealth Plus Systems</option>
          <option value="Eastwest Healthcare">Eastwest Healthcare</option>
          <option value="Generali Life Assurance">Generali Life Assurance</option>
          <option value="__OTHER__">Other / Corporate Payor (Specify)</option>
        </select>
        <input type="text" id="invPrimaryHmoOther" class="form-control form-control-sm mt-1" placeholder="Specify Primary HMO..." style="display: none;" oninput="syncInvoiceDualHmo()">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-muted">Secondary HMO (Cross-Coverage)</label>
        <select id="invSecondaryHmoSelect" class="form-select form-select-sm" onchange="syncInvoiceDualHmo()">
          <option value="" selected>None / No Secondary HMO</option>
          <option value="Maxicare Healthcare Corporation">Maxicare Healthcare Corporation</option>
          <option value="Intellicare (Asalus Corporation)">Intellicare (Asalus Corporation)</option>
          <option value="Medicard Philippines, Inc.">Medicard Philippines, Inc.</option>
          <option value="PhilCare (PhilhealthCare, Inc.)">PhilCare (PhilhealthCare, Inc.)</option>
          <option value="Cocolife Healthcare">Cocolife Healthcare</option>
          <option value="Etiqa Life &amp; General Insurance">Etiqa Life &amp; General Insurance</option>
          <option value="ValuCare Health Systems, Inc.">ValuCare Health Systems, Inc.</option>
          <option value="Pacific Cross Philippines">Pacific Cross Philippines</option>
          <option value="InLife Health Care">InLife Health Care (Insular)</option>
          <option value="CareHealth Plus Systems">CareHealth Plus Systems</option>
          <option value="Eastwest Healthcare">Eastwest Healthcare</option>
          <option value="Generali Life Assurance">Generali Life Assurance</option>
          <option value="__OTHER__">Other / Corporate Payor (Specify)</option>
        </select>
        <input type="text" id="invSecondaryHmoOther" class="form-control form-control-sm mt-1" placeholder="Specify Secondary HMO..." style="display: none;" oninput="syncInvoiceDualHmo()">
      </div>
      <div class="col-md-12">
        <label class="form-label small fw-semibold text-muted">Total HMO Approved Benefit Limit (₱)</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text">₱</span>
          <input type="number" step="0.01" min="0" name="hmo_approved_limit" class="form-control font-monospace" placeholder="0.00">
        </div>
      </div>
    </div>
    <input type="hidden" name="hmo_provider" id="invHmoFinal" value="">
  </div>

  <!-- Step 3: Itemized Hospital Charges -->
  <div class="mb-1">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="fw-bold text-dark fs-xs text-uppercase mb-0">
        <i class="ph ph-list-numbers me-1 text-primary"></i> Itemized Clinical &amp; Hospital Charges
      </h6>
      <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 fs-xs" onclick="addModalInvoiceRow()">
        <i class="ph ph-plus me-1"></i> Add Line Charge
      </button>
    </div>

    <div class="table-responsive border rounded-3">
      <table class="table table-sm align-middle mb-0 fs-xs" id="tableModalInvoiceItems">
        <thead class="table-light">
          <tr>
            <th style="width: 40%;">Description of Service / Medication <span class="text-danger">*</span></th>
            <th style="width: 20%;">Department</th>
            <th style="width: 12%;" class="text-center">Qty <span class="text-danger">*</span></th>
            <th style="width: 15%;" class="text-end">Unit Price (₱) <span class="text-danger">*</span></th>
            <th style="width: 13%;" class="text-end">Gross (₱)</th>
            <th style="width: 50px;"></th>
          </tr>
        </thead>
        <tbody id="modalInvoiceItemsBody">
          <tr>
            <td>
              <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="e.g. Inpatient Room &amp; Board Accommodation" required>
              <input type="hidden" name="items[0][is_vatable]" value="1">
              <input type="hidden" name="items[0][is_senior_pwd_eligible]" value="1">
            </td>
            <td>
              <select name="items[0][department]" class="form-select form-select-sm">
                <option value="CLINICAL" selected>Clinical / Ward</option>
                <option value="PHARMACY">Pharmacy</option>
                <option value="LABORATORY">Laboratory</option>
                <option value="IMAGING">Radiology</option>
                <option value="OR">Operating Room</option>
              </select>
            </td>
            <td>
              <input type="number" step="1" min="1" name="items[0][quantity]" class="form-control form-control-sm text-center item-qty" value="1" required oninput="recalcInvoiceModalRow(this)">
            </td>
            <td>
              <input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm text-end font-monospace item-price" placeholder="0.00" required oninput="recalcInvoiceModalRow(this)">
            </td>
            <td class="text-end font-monospace fw-bold text-dark item-line-gross">₱ 0.00</td>
            <td class="text-center text-muted">
              <i class="ph ph-lock small" title="At least one charge required"></i>
            </td>
          </tr>
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="4" class="text-end">Estimated Incurred Gross Total:</td>
            <td class="text-end font-monospace text-primary fs-6" id="modalInvoiceGrossTotal">₱ 0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</x-modal>
@endsection

@push('scripts')
<script>
function formatCurrency(val) {
  return '₱ ' + (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function openInvoiceDetailsDrawer(btn) {
  const payloadRaw = btn.getAttribute('data-invoice');
  if (!payloadRaw) return;

  try {
    const data = JSON.parse(payloadRaw);

    // Master Info
    document.getElementById('drawerInvoiceNumber').textContent = data.invoice_number;
    document.getElementById('drawerPatientName').textContent = data.patient_name;
    document.getElementById('drawerPatientMrn').textContent = 'MRN: ' + data.patient_mrn + ' | Date: ' + data.invoice_date;
    document.getElementById('drawerAdmissionType').textContent = data.admission_type;
    document.getElementById('drawerStatus').textContent = data.status;

    // Statutory Badge in Header
    const statBadgeEl = document.getElementById('drawerStatutoryBadge');
    if (statBadgeEl) {
      if (data.statutory_category === 'PWD') {
        statBadgeEl.innerHTML = `<span class="badge bg-teal-subtle text-teal border border-teal-subtle ms-1" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4 !important;"><i class="ph ph-wheelchair me-1"></i>♿ PWD 20%</span>`;
      } else if (data.statutory_category === 'SENIOR_CITIZEN') {
        statBadgeEl.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1"><i class="ph ph-identification-card me-1"></i>Senior 20%</span>`;
      } else {
        statBadgeEl.innerHTML = '';
      }
    }

    // Line Items Table
    const tbody = document.getElementById('drawerItemsBody');
    tbody.innerHTML = '';
    if (data.items && data.items.length > 0) {
      data.items.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="text-nowrap"><span class="badge bg-light text-dark border">${item.department}</span></td>
          <td class="fw-semibold text-dark text-nowrap">${item.description}</td>
          <td class="text-center font-monospace text-nowrap">${item.quantity}</td>
          <td class="text-end font-monospace text-nowrap">${formatCurrency(item.unit_price)}</td>
          <td class="text-end font-monospace fw-bold text-dark text-nowrap">${formatCurrency(item.gross_amount)}</td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center py-2 text-muted text-nowrap">No line items recorded.</td></tr>`;
    }

    // PhilHealth Info
    const phicEl = document.getElementById('drawerPhilhealthInfo');
    if (data.philhealth && data.philhealth.amount > 0) {
      phicEl.innerHTML = `
        <div class="fw-bold text-dark text-nowrap">Deduction: ${formatCurrency(data.philhealth.amount)}</div>
        <div class="text-nowrap">Series #: <span class="font-monospace">${data.philhealth.series_no}</span></div>
        <div class="text-nowrap">Member PIN: <span class="font-monospace">${data.philhealth.member_pin}</span></div>
      `;
    } else {
      phicEl.innerHTML = `<span class="text-muted text-nowrap">No PhilHealth deduction applied.</span>`;
    }

    // HMO Info
    const hmoEl = document.getElementById('drawerHmoInfo');
    if (data.hmo && data.hmo.claimed > 0) {
      hmoEl.innerHTML = `
        <div class="fw-bold text-dark text-nowrap">${data.hmo.provider}</div>
        <div class="text-nowrap">Coverage: ${formatCurrency(data.hmo.claimed)} / ${formatCurrency(data.hmo.limit)}</div>
        <div class="text-nowrap">LOA #: <span class="font-monospace">${data.hmo.loa_number}</span></div>
      `;
    } else {
      hmoEl.innerHTML = `<span class="text-muted text-nowrap">No HMO guarantee applied.</span>`;
    }

    // Statutory Discount Banner
    const statBanner = document.getElementById('drawerStatutoryBanner');
    const statText = document.getElementById('drawerStatutoryText');
    if (data.statutory && data.statutory.amount > 0) {
      statBanner.style.display = 'block';
      statText.textContent = `Applied ${data.statutory.type} statutory discount of ${formatCurrency(data.statutory.amount)} (ID #: ${data.statutory.id_number})`;
    } else {
      statBanner.style.display = 'none';
    }

    // Financial Totals
    document.getElementById('drawerGross').textContent = formatCurrency(data.gross_total);
    document.getElementById('drawerDiscount').textContent = (data.discount > 0 ? '- ' : '') + formatCurrency(data.discount);
    document.getElementById('drawerInsurance').textContent = (data.insurance > 0 ? '- ' : '') + formatCurrency(data.insurance);
    document.getElementById('drawerCopay').textContent = formatCurrency(data.copay);

    // GL Link
    document.getElementById('drawerGlReference').textContent = data.gl_reference;
    document.getElementById('drawerGlLink').href = data.gl_url;

    // Show Offcanvas Drawer
    const drawerEl = document.getElementById('invoiceDetailsDrawer');
    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
    bsOffcanvas.show();
  } catch (err) {
    console.error('Failed to parse invoice drawer data:', err);
  }
}

// Modal Form Interactivity
function handlePatientSelectForInvoice(sel) {
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) return;

  const discount = opt.getAttribute('data-discount') || 'NONE';
  const idcard = opt.getAttribute('data-idcard') || '';
  const hmo = opt.getAttribute('data-hmo') || '';
  const parts = hmo.split(',').map(s => s.trim()).filter(Boolean);
  const primaryVal = parts[0] || '';
  const secondaryVal = parts[1] || '';

  const pSelect = document.getElementById('invPrimaryHmoSelect');
  const pOther = document.getElementById('invPrimaryHmoOther');
  const sSelect = document.getElementById('invSecondaryHmoSelect');
  const sOther = document.getElementById('invSecondaryHmoOther');

  setDropdownOrOther(pSelect, pOther, primaryVal);
  setDropdownOrOther(sSelect, sOther, secondaryVal);
  syncInvoiceDualHmo();
}

function setDropdownOrOther(selectEl, otherEl, value) {
  if (!selectEl) return;
  if (!value) {
    selectEl.selectedIndex = 0;
    if (otherEl) { otherEl.style.display = 'none'; otherEl.value = ''; }
    return;
  }
  let matched = false;
  for (let i = 0; i < selectEl.options.length; i++) {
    if (selectEl.options[i].value === value) {
      selectEl.selectedIndex = i;
      matched = true;
      break;
    }
  }
  if (!matched) {
    selectEl.value = '__OTHER__';
    if (otherEl) {
      otherEl.style.display = 'block';
      otherEl.value = value;
    }
  } else {
    if (otherEl) {
      otherEl.style.display = 'none';
      otherEl.value = '';
    }
  }
}

function syncInvoiceDualHmo() {
  const pSelect = document.getElementById('invPrimaryHmoSelect');
  const pOther = document.getElementById('invPrimaryHmoOther');
  const sSelect = document.getElementById('invSecondaryHmoSelect');
  const sOther = document.getElementById('invSecondaryHmoOther');
  const finalInput = document.getElementById('invHmoFinal');

  if (!pSelect || !sSelect || !finalInput) return;

  let primary = pSelect.value;
  if (pSelect.value === '__OTHER__') {
    pOther.style.display = 'block';
    primary = pOther.value.trim();
  } else {
    pOther.style.display = 'none';
    pOther.value = '';
  }

  let secondary = sSelect.value;
  if (sSelect.value === '__OTHER__') {
    sOther.style.display = 'block';
    secondary = sOther.value.trim();
  } else {
    sOther.style.display = 'none';
    sOther.value = '';
  }

  const providers = [];
  if (primary) providers.push(primary);
  if (secondary) providers.push(secondary);

  finalInput.value = providers.join(', ');
}

let modalInvoiceRowIndex = 1;
function addModalInvoiceRow() {
  const tbody = document.getElementById('modalInvoiceItemsBody');
  const tr = document.createElement('tr');
  const idx = modalInvoiceRowIndex++;

  tr.innerHTML = `
    <td>
      <input type="text" name="items[${idx}][description]" class="form-control form-control-sm" placeholder="e.g. Diagnostic Laboratory / Surgery Fee / Medications" required>
      <input type="hidden" name="items[${idx}][is_vatable]" value="1">
      <input type="hidden" name="items[${idx}][is_senior_pwd_eligible]" value="1">
    </td>
    <td>
      <select name="items[${idx}][department]" class="form-select form-select-sm">
        <option value="CLINICAL">Clinical / Ward</option>
        <option value="PHARMACY">Pharmacy</option>
        <option value="LABORATORY">Laboratory</option>
        <option value="IMAGING">Radiology</option>
        <option value="OR">Operating Room</option>
      </select>
    </td>
    <td>
      <input type="number" step="1" min="1" name="items[${idx}][quantity]" class="form-control form-control-sm text-center item-qty" value="1" required oninput="recalcInvoiceModalRow(this)">
    </td>
    <td>
      <input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="form-control form-control-sm text-end font-monospace item-price" placeholder="0.00" required oninput="recalcInvoiceModalRow(this)">
    </td>
    <td class="text-end font-monospace fw-bold text-dark item-line-gross">₱ 0.00</td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeModalInvoiceRow(this)" title="Remove item">
        <i class="ph ph-trash fs-6"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
  recalcInvoiceModalTotal();
}

function removeModalInvoiceRow(btn) {
  const tr = btn.closest('tr');
  if (tr) {
    tr.remove();
    recalcInvoiceModalTotal();
  }
}

function recalcInvoiceModalRow(input) {
  const tr = input.closest('tr');
  if (!tr) return;

  const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
  const price = parseFloat(tr.querySelector('.item-price')?.value) || 0;
  const lineGross = qty * price;

  const grossCell = tr.querySelector('.item-line-gross');
  if (grossCell) {
    grossCell.textContent = '₱ ' + lineGross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  recalcInvoiceModalTotal();
}

function recalcInvoiceModalTotal() {
  let total = 0;
  const rows = document.querySelectorAll('#modalInvoiceItemsBody tr');
  rows.forEach(r => {
    const qty = parseFloat(r.querySelector('.item-qty')?.value) || 0;
    const price = parseFloat(r.querySelector('.item-price')?.value) || 0;
    total += (qty * price);
  });

  const totalEl = document.getElementById('modalInvoiceGrossTotal');
  if (totalEl) {
    totalEl.textContent = '₱ ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
}
</script>
@endpush
