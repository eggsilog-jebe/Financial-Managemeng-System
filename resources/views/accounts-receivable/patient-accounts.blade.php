@extends('layouts.app')

@section('title', 'Patient & Customer Accounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'customers')

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
          <li class="breadcrumb-item active">Patient Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Payor Directory</h1>
      <p class="text-muted fs-xs mb-0">Master directory of patient billing profiles, HMO health insurances, contact details, and open balances.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['SPRS (Smart Patient Registration System)']" 
          description="Syncs Master Patient Index (MPI) and contact demographics." 
      />
      <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fs-xs fw-semibold">
        <i class="ph ph-plugs-connected me-1"></i> SPRS Integration Active
      </span>
      <a href="#" class="btn btn-outline-secondary btn-sm" onclick="alert('Exporting Payor Directory CSV...'); return false;">
        <i class="ph ph-download-simple me-1"></i> Export Payor Directory CSV
      </a>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPatientModal">
        <i class="ph ph-user-plus me-1"></i> Register Patient Account
      </button>
    </div>
  </div>

  <!-- Primary Executive Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Payor Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-users fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalActive ?? 0 }} Registered</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Open Patient Receivable (AR)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) ($totalReceivable ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Guaranteed Portfolio</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-info font-monospace">₱{{ number_format((float) ($hmoGuarantees ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Filters and Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.patients.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="outstandingOnlySwitch" name="outstanding_only" value="1" {{ request('outstanding_only') ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label small fw-semibold text-dark" for="outstandingOnlySwitch">With Balance Only</label>
          </div>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search MRN, name, HMO..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="patientAccountTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Patient MRN &amp; Name</th>
              <th>Admission &amp; Category</th>
              <th>HMO Provider / Guarantee</th>
              <th>Contact Details</th>
              <th class="text-end">Total Billed (₱)</th>
              <th class="text-end">Open Balance (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts as $acc)
            @php
              $mrn = $acc->patient_mrn ?: $acc->patient_id_number;
              $name = $acc->full_name;
              $bal = (float) $acc->current_balance;
              $billed = (float) $acc->total_billed;
              $discount = $acc->effective_discount_category ?? 'NONE';
            @endphp
            <tr>
              <td>
                <div class="fw-bold text-dark text-uppercase d-flex align-items-center gap-1">
                  {{ $name }}
                  @if($acc->gender)
                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 10px;">
                      {{ $acc->gender === 'Female' ? '♀' : ($acc->gender === 'Male' ? '♂' : '') }} {{ $acc->gender }}
                    </span>
                  @endif
                </div>
                <div class="fs-xs text-muted d-flex align-items-center gap-2 mt-1">
                  <span class="font-monospace text-primary fw-semibold">{{ $mrn }}</span>
                  @if($acc->date_of_birth)
                    <span>&bull; DOB: <strong>{{ \Carbon\Carbon::parse($acc->date_of_birth)->format('M d, Y') }}</strong></span>
                  @endif
                  @if($acc->id_card_number)
                    <span class="badge bg-light text-muted border font-monospace fs-xs" title="Statutory ID Card Number">ID: {{ $acc->id_card_number }}</span>
                  @endif
                </div>
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ $acc->admission_type ?? 'Inpatient' }}</span>
                @if($discount === 'SENIOR_CITIZEN')
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1"><i class="ph ph-identification-card me-1"></i>Senior 20%</span>
                @elseif($discount === 'PWD')
                  <span class="badge bg-teal-subtle text-teal border border-teal-subtle ms-1" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4 !important;"><i class="ph ph-wheelchair me-1"></i>PWD 20%</span>
                @elseif($discount === 'EMPLOYEE_SUBSIDY' || $discount === 'EMPLOYEE')
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Employee</span>
                @elseif($discount === 'CHARITY' || $discount === 'CHARITY_SUBSIDY')
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">Charity</span>
                @endif
              </td>
              <td>
                <div class="d-flex flex-column gap-1">
                  <div>
                    @if($acc->hmo_provider)
                      @foreach(explode(',', $acc->hmo_provider) as $hmoItem)
                        <span class="badge bg-info-subtle text-info border border-info-subtle d-inline-block text-truncate mb-1" style="max-width: 220px;" title="{{ trim($hmoItem) }}">
                          <i class="ph ph-shield me-1"></i>{{ trim($hmoItem) }}
                        </span>
                      @endforeach
                    @else
                      <span class="badge bg-light text-muted border">Self-Pay (Cash)</span>
                    @endif
                  </div>
                  <div>
                    @php
                      $latestPhic = $acc->invoices->pluck('philhealthClaim')->filter()->first();
                    @endphp
                    @if($latestPhic && (float) $latestPhic->total_case_rate_amount > 0)
                      <span class="badge bg-success-subtle text-success border border-success-subtle fs-xs">
                        <i class="ph ph-check-circle me-1"></i> PHIC: Active (₱{{ number_format((float)$latestPhic->total_case_rate_amount, 2) }})
                      </span>
                    @else
                      <span class="badge bg-light text-secondary border fs-xs">PHIC: None</span>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                <div class="fs-xs text-dark font-monospace"><i class="ph ph-phone text-muted me-1"></i>{{ $acc->phone ?? '—' }}</div>
                <div class="fs-xs text-muted"><i class="ph ph-envelope-simple me-1"></i>{{ $acc->email ?? '—' }}</div>
                @if($acc->address)
                  <div class="fs-xs text-secondary text-truncate" style="max-width: 180px;" title="{{ $acc->address }}"><i class="ph ph-map-pin me-1"></i>{{ $acc->address }}</div>
                @endif
              </td>
              <td class="text-end font-monospace fw-semibold text-dark">₱{{ number_format($billed, 2) }}</td>
              <td class="text-end font-monospace fw-bold {{ $bal > 0 ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format($bal, 2) }}
              </td>
              <td>
                <span class="badge {{ $acc->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                  {{ $acc->status }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" 
                          data-bs-toggle="offcanvas" data-bs-target="#profileDrawer{{ $acc->id }}" title="View Patient Demographics & Financial Profile">
                    <i class="ph ph-eye me-1"></i> View Profile
                  </button>
                  <a href="{{ route('ar.statements', ['patient_id' => $acc->id]) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="View SOA">
                    <i class="ph ph-file-text me-1"></i> SOA
                  </a>
                </div>

                <!-- Slide-Over Drawer for Patient Profile & Invoices -->
                <div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="profileDrawer{{ $acc->id }}" style="width: 750px; max-width: 90vw;">
                  <div class="offcanvas-header bg-dark text-white py-3 px-4">
                    <div>
                      <h6 class="offcanvas-title fw-bold mb-0 text-white d-flex align-items-center gap-2">
                        <i class="ph ph-user-circle fs-4 text-primary"></i> {{ $name }}
                      </h6>
                      <span class="fs-xs text-muted font-monospace">{{ $mrn }} &bull; {{ $acc->admission_type ?? 'Inpatient' }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>

                  <div class="offcanvas-body p-4 text-start">
                    <!-- Demographics & Contact Information -->
                    <div class="card border border-light-subtle rounded-3 p-3 mb-4 bg-light-subtle">
                      <div class="fw-bold text-uppercase fs-xs text-secondary mb-2 border-bottom pb-1">
                        <i class="ph ph-address-book me-1"></i> Patient Identity &amp; Demographics
                      </div>
                      <div class="row g-2 fs-xs">
                        <div class="col-6">
                          <span class="text-muted d-block">Full Legal Name:</span>
                          <strong class="text-dark">{{ $name }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">Sex / Gender:</span>
                          <strong class="text-dark">{{ $acc->gender ?? 'Female' }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">Date of Birth:</span>
                          <strong class="text-dark">{{ $acc->date_of_birth ? \Carbon\Carbon::parse($acc->date_of_birth)->format('M d, Y') : '1985-06-15' }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">Phone Contact:</span>
                          <strong class="text-dark">{{ $acc->phone ?? '—' }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">Email Address:</span>
                          <strong class="text-dark">{{ $acc->email ?? '—' }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">Statutory Category:</span>
                          @if($discount === 'SENIOR_CITIZEN')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-xs"><i class="ph ph-identification-card me-1"></i>Senior Citizen (RA 9994)</span>
                          @elseif($discount === 'PWD')
                            <span class="badge bg-teal-subtle border fs-xs" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4 !important;"><i class="ph ph-wheelchair me-1"></i>PWD (RA 10754)</span>
                          @elseif($discount === 'EMPLOYEE_SUBSIDY' || $discount === 'EMPLOYEE')
                            <span class="badge bg-primary-subtle text-primary border fs-xs">Employee</span>
                          @elseif($discount === 'CHARITY' || $discount === 'CHARITY_SUBSIDY')
                            <span class="badge bg-secondary-subtle text-secondary border fs-xs">Charity</span>
                          @else
                            <strong class="text-muted">NONE</strong>
                          @endif
                        </div>
                        <div class="col-6">
                          <span class="text-muted d-block">HMO Provider:</span>
                          <strong class="text-info">{{ $acc->hmo_provider ?? 'Self-Pay' }}</strong>
                        </div>
                        <div class="col-12 mt-1">
                          <span class="text-muted d-block">Complete Residential Address:</span>
                          <span class="text-dark">{{ $acc->address ?? 'Metro Manila, Philippines' }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Lifetime Financial Summary Cards -->
                    <div class="row g-2 mb-4">
                      <div class="col-4">
                        <div class="border rounded-3 p-2 text-center bg-white">
                          <span class="text-muted fs-xs text-uppercase d-block mb-1">Total Billed</span>
                          <span class="fw-bold font-monospace text-dark fs-xs">₱{{ number_format((float) $acc->total_billed, 2) }}</span>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="border rounded-3 p-2 text-center bg-white">
                          <span class="text-muted fs-xs text-uppercase d-block mb-1">Total Settled</span>
                          <span class="fw-bold font-monospace text-success fs-xs">₱{{ number_format((float) ($acc->total_billed - $acc->current_balance), 2) }}</span>
                        </div>
                      </div>
                      <div class="col-4">
                        <div class="border rounded-3 p-2 text-center bg-white">
                          <span class="text-muted fs-xs text-uppercase d-block mb-1">Open Balance</span>
                          <span class="fw-bold font-monospace {{ $acc->current_balance > 0 ? 'text-danger' : 'text-success' }} fs-xs">₱{{ number_format((float) $acc->current_balance, 2) }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Linked Encounter Invoices & Cashier Receipts -->
                    <div class="fw-bold text-uppercase fs-xs text-secondary mb-2 d-flex justify-content-between align-items-center">
                      <span><i class="ph ph-receipt me-1"></i> Linked Invoices ({{ $acc->invoices->count() }})</span>
                      <a href="{{ route('ar.statements', ['patient_id' => $acc->id]) }}" class="fs-xs text-primary text-decoration-none">Full Statement &rarr;</a>
                    </div>

                    <div class="table-responsive border rounded-3 bg-white mb-3">
                      <table class="table table-sm table-hover align-middle mb-0 fs-xs">
                        <thead class="table-light">
                          <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th class="text-end">Copay Due</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($acc->invoices as $inv)
                            <tr>
                              <td>
                                <span class="font-monospace fw-semibold text-primary">{{ $inv->invoice_number }}</span>
                              </td>
                              <td class="text-muted">{{ $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : '-' }}</td>
                              <td class="text-end font-monospace fw-bold">₱{{ number_format((float) $inv->patient_payable, 2) }}</td>
                              <td>
                                <span class="badge {{ $inv->status === 'SETTLED' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis' }}">
                                  {{ $inv->status }}
                                </span>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="4" class="text-center py-3 text-muted">No invoices generated for this patient.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>

                    <!-- Quick Action Footer inside Drawer -->
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                      <a href="{{ route('ar.statements', ['patient_id' => $acc->id]) }}" class="btn btn-sm btn-primary w-100 fw-medium">
                        <i class="ph ph-file-text me-1"></i> Generate Complete Statement of Account (SOA)
                      </a>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No patient/payor accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $accounts->firstItem() ?? 0 }} - {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} Payor Accounts</span>
      <div>
        {{ $accounts->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Register Patient Account -->
<x-modal 
    id="createPatientModal" 
    title="Register Patient Billing Profile"
    subtitle="Create an active patient billing account for clinical charges, statutory discounts & HMO tracking."
    icon="ph-user-plus"
    iconVariant="primary"
    size="lg"
    :scrollable="true"
    :centered="true"
    formAction="{{ route('ar.patients.store') }}"
    formMethod="POST"
    submitText="Save & Register Patient"
    submitIcon="ph-check"
>
  <div class="row g-3 mb-3">
    <div class="col-md-7">
      <label class="form-label small fw-semibold text-dark">Patient Full Name <span class="text-danger">*</span></label>
      <input type="text" name="full_name" class="form-control form-control-sm" placeholder="e.g. Juan Dela Cruz" required>
    </div>
    <div class="col-md-5">
      <label class="form-label small fw-semibold text-dark">Patient MRN / ID Number</label>
      <input type="text" name="patient_mrn" class="form-control form-control-sm font-monospace" placeholder="e.g. MRN-2026-00451 (Leave blank to auto-generate)">
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label small fw-semibold text-dark">Admission / Care Type <span class="text-danger">*</span></label>
      <select name="admission_type" class="form-select form-select-sm" required>
        <option value="Inpatient" selected>Inpatient (Admitted Ward/ICU)</option>
        <option value="Outpatient">Outpatient (Clinic/Consultation)</option>
        <option value="Emergency">Emergency (ER Room)</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label small fw-semibold text-dark">Statutory Discount Category</label>
      <select name="discount_category" class="form-select form-select-sm">
        <option value="NONE" selected>None / Regular</option>
        <option value="SENIOR_CITIZEN">Senior Citizen (RA 9994: 20% + VAT Exemption)</option>
        <option value="PWD">PWD (RA 10754: 20% + VAT Exemption)</option>
        <option value="EMPLOYEE">Hospital Employee / Dependent Subsidy</option>
        <option value="CHARITY">Charity / Medical Social Services</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label small fw-semibold text-dark">Statutory ID Card Number</label>
      <input type="text" name="id_card_number" class="form-control form-control-sm font-monospace" placeholder="e.g. OSCA-98741 or PWD-5542">
    </div>
  </div>

  <!-- HMO Coverage (Supports Primary & Secondary Dual HMO / COB) -->
  <div class="card border border-light-subtle bg-light-subtle p-3 rounded-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="fw-bold text-dark fs-xs text-uppercase mb-0">
        <i class="ph ph-shield-check me-1 text-primary"></i> Health Insurance Coverage (Dual HMO / COB)
      </h6>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-xxs">Select up to 2 HMOs</span>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-dark">Primary HMO Provider</label>
        <select id="patientPrimaryHmoSelect" class="form-select form-select-sm" onchange="syncPatientDualHmo()">
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
        <input type="text" id="patientPrimaryHmoOther" class="form-control form-control-sm mt-1" placeholder="Specify Primary HMO name..." style="display: none;" oninput="syncPatientDualHmo()">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold text-dark">
          Secondary HMO <span class="text-muted fw-normal fs-xs">(Optional - Cross Coverage)</span>
        </label>
        <select id="patientSecondaryHmoSelect" class="form-select form-select-sm" onchange="syncPatientDualHmo()">
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
        <input type="text" id="patientSecondaryHmoOther" class="form-control form-control-sm mt-1" placeholder="Specify Secondary HMO name..." style="display: none;" oninput="syncPatientDualHmo()">
      </div>
    </div>
    <input type="hidden" name="hmo_provider" id="patientHmoFinal" value="">
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label small fw-semibold text-dark">Contact Phone Number</label>
      <input type="text" name="phone" class="form-control form-control-sm" placeholder="e.g. 0917-123-4567">
    </div>
    <div class="col-md-6">
      <label class="form-label small fw-semibold text-dark">Email Address</label>
      <input type="email" name="email" class="form-control form-control-sm" placeholder="patient@example.com">
    </div>
  </div>

  <div class="mb-1">
    <label class="form-label small fw-semibold text-dark">Home / Billing Address</label>
    <input type="text" name="address" class="form-control form-control-sm" placeholder="Barangay, City / Municipality, Province">
  </div>
</x-modal>
@endsection

@push('scripts')
<script>
function syncPatientDualHmo() {
  const pSelect = document.getElementById('patientPrimaryHmoSelect');
  const pOther = document.getElementById('patientPrimaryHmoOther');
  const sSelect = document.getElementById('patientSecondaryHmoSelect');
  const sOther = document.getElementById('patientSecondaryHmoOther');
  const finalInput = document.getElementById('patientHmoFinal');

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
</script>
@endpush
