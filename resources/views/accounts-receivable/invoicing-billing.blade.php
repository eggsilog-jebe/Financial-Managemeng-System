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

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Invoicing &amp; Billing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient Invoicing &amp; Clinical Billing Hub</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['BDMS', 'LIS (Lab)', 'RIS (Imaging)', 'PMS (Pharmacy)', 'IBMS', 'HICS (HMO Claims)']" 
          glImpact="DR 1110/1130 (AR) / CR 4000-series (Revenue)" 
          description="Central billing aggregator converting clinical orders into accounting receivables." 
      />
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
        <i class="ph ph-plus me-1"></i> Create Patient Invoice
      </button>
    </div>
  </div>

  <!-- Summary Cards -->
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

  <!-- Invoices Table Card -->
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
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $inv->invoice_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $inv->patientAccount?->full_name ?? 'Unknown Patient' }}</div>
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
                  <a href="{{ route('ar.invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-xs" title="Print Billing Statement">
                    <i class="ph ph-printer me-1"></i> Print
                  </a>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No patient invoices found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }} Invoices</span>
      <div>
        {{ $invoices->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Create Patient Invoice -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-receipt me-2 text-primary"></i>Generate Patient Discharge &amp; Encounter Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ar.invoices.store') }}" id="createInvoiceForm">
        @csrf
        <div class="modal-body p-4">
          <!-- Patient & Master Details -->
          <div class="row g-3 mb-4">
            <div class="col-md-5">
              <label class="form-label small fw-semibold">Select Patient <span class="text-danger">*</span></label>
              <select name="patient_account_id" class="form-select form-select-sm" required>
                <option value="">-- Choose Patient Account --</option>
                @foreach($patients as $p)
                  <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_id_number }}) - {{ $p->admission_type }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Invoice Date <span class="text-danger">*</span></label>
              <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Statutory Discount (RA 9994 / RA 10754)</label>
              <select name="discount_type" class="form-select form-select-sm">
                <option value="">None (Standard Rate)</option>
                <option value="SENIOR_CITIZEN">Senior Citizen (20% + 12% VAT Relief)</option>
                <option value="PWD">Person with Disability (20% + 12% VAT Relief)</option>
                <option value="EMPLOYEE">Hospital Employee Subsidy</option>
                <option value="CHARITY">Social Service / Charity Subsidy</option>
              </select>
            </div>
          </div>

          <!-- Insurance & Claims Section -->
          <div class="p-3 bg-light rounded-3 mb-4">
            <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-primary"></i> Third-Party Coverage (PhilHealth &amp; HMO Guarantees)</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small text-muted">PhilHealth Primary Case Rate Amount</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">₱</span>
                  <input type="number" step="0.01" name="philhealth_primary_case_rate_amount" class="form-control" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-muted">HMO Provider Name</label>
                <input type="text" name="hmo_provider" class="form-control form-control-sm" placeholder="e.g. Maxicare / Intellicare">
              </div>
              <div class="col-md-4">
                <label class="form-label small text-muted">HMO Approved Limit</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">₱</span>
                  <input type="number" step="0.01" name="hmo_approved_limit" class="form-control" placeholder="0.00">
                </div>
              </div>
            </div>
          </div>

          <!-- Itemized Departmental Charges -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-dark mb-0 fs-xs text-uppercase"><i class="ph ph-list-numbers me-1 text-primary"></i> Departmental Billable Items</h6>
            <button type="button" class="btn btn-xs btn-outline-primary" onclick="addInvoiceLineItem()">
              <i class="ph ph-plus me-1"></i> Add Line Item
            </button>
          </div>

          <div class="table-responsive mb-3 border rounded">
            <table class="table table-sm align-middle mb-0" id="invoiceItemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width: 15%;">Department</th>
                  <th style="width: 40%;">Description / Procedure</th>
                  <th style="width: 15%;">Quantity</th>
                  <th style="width: 15%;">Unit Price (₱)</th>
                  <th style="width: 15%;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="invoiceItemsBody">
                <tr>
                  <td>
                    <select name="items[0][department]" class="form-select form-select-sm">
                      <option value="CLINICAL">Clinical / Ward</option>
                      <option value="PHARMACY">Pharmacy</option>
                      <option value="LIS">Laboratory (LIS)</option>
                      <option value="RIS">Radiology (RIS)</option>
                      <option value="SURGERY">Operating Room</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="e.g. Inpatient Room Board 3 Days" required>
                  </td>
                  <td>
                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm" value="1" min="1" step="1" required>
                  </td>
                  <td>
                    <input type="number" name="items[0][unit_price]" class="form-control form-control-sm" placeholder="0.00" step="0.01" required>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="this.closest('tr').remove()"><i class="ph ph-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Invoice to General Ledger</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
function addInvoiceLineItem() {
  const tbody = document.getElementById('invoiceItemsBody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="items[${lineIndex}][department]" class="form-select form-select-sm">
        <option value="CLINICAL">Clinical / Ward</option>
        <option value="PHARMACY">Pharmacy</option>
        <option value="LIS">Laboratory (LIS)</option>
        <option value="RIS">Radiology (RIS)</option>
        <option value="SURGERY">Operating Room</option>
      </select>
    </td>
    <td>
      <input type="text" name="items[${lineIndex}][description]" class="form-control form-control-sm" placeholder="e.g. CBC / Chest X-Ray / Meds" required>
    </td>
    <td>
      <input type="number" name="items[${lineIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" step="1" required>
    </td>
    <td>
      <input type="number" name="items[${lineIndex}][unit_price]" class="form-control form-control-sm" placeholder="0.00" step="0.01" required>
    </td>
    <td class="text-end">
      <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="this.closest('tr').remove()"><i class="ph ph-trash"></i></button>
    </td>
  `;
  tbody.appendChild(tr);
  lineIndex++;
}
</script>
@endpush
