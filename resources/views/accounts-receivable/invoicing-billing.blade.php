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
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 10px;"><i class="ph ph-heart me-1"></i>Senior 20%</span>
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
        <i class="ph ph-heart-break me-1"></i> <span id="drawerStatutoryText"></span>
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
        statBadgeEl.innerHTML = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1"><i class="ph ph-heart me-1"></i>👴 Senior 20%</span>`;
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
</script>
@endpush
