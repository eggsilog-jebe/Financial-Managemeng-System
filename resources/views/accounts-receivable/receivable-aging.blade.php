@extends('layouts.app')

@section('title', 'Receivable Aging Schedule - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'ar-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Receivable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Receivable Aging</h1>
      <p class="text-muted fs-xs mb-0">Track all unpaid patient balances and pending HMO insurance claims categorized by aging brackets (Current, 31–60 Days, 61–90 Days, 90+ Days).</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoicing & Billing', 'Credit Notes']" 
          :tables="['invoices', 'hmo_claims', 'credit_notes']"
          description="Scans open patient bills and claims to calculate aging intervals (<30d, 31-60d, 61-90d, 90d+)." 
      />
      <a href="{{ route('ar.ar-aging.export', ['as_of_date' => $asOfDate ?? date('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-download-simple me-1"></i> Export Aging CSV
      </a>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current (&lt;30 Days)</span>
        <h5 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) ($totalCurrent ?? 0), 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">31 - 60 Days Overdue</span>
        <h5 class="fw-bold mb-0 text-primary font-monospace">₱{{ number_format((float) ($total31To60 ?? 0), 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">61 - 90 Days Overdue</span>
        <h5 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) ($total61To90 ?? 0), 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">91 - 120 Days</span>
        <h5 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) ($total91To120 ?? 0), 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">120+ Days Overdue</span>
        <h5 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) ($total120Plus ?? 0), 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-danger-subtle">
        <span class="text-danger fs-xs text-uppercase fw-bold d-block mb-1">Grand Total AR</span>
        <h5 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) ($grandTotalAR ?? 0), 2) }}</h5>
      </div>
    </div>
  </div>

  <!-- Payor Type Segment Tabs -->
  <div class="mb-3">
    <div class="btn-group btn-group-sm w-100 shadow-sm rounded-3 overflow-hidden" role="group">
      <a href="{{ route('ar.ar-aging', array_merge(request()->query(), ['payor_type' => 'ALL'])) }}" 
         class="btn py-2 {{ ($payorType ?? 'ALL') === 'ALL' ? 'btn-primary fw-bold' : 'btn-white text-secondary border-light-subtle' }}">
        <i class="ph ph-squares-four me-1"></i> All Receivables
      </a>
      <a href="{{ route('ar.ar-aging', array_merge(request()->query(), ['payor_type' => 'PATIENT'])) }}" 
         class="btn py-2 {{ ($payorType ?? '') === 'PATIENT' ? 'btn-primary fw-bold' : 'btn-white text-secondary border-light-subtle' }}">
        <i class="ph ph-user me-1"></i> Patient Copays
      </a>
      <a href="{{ route('ar.ar-aging', array_merge(request()->query(), ['payor_type' => 'HMO'])) }}" 
         class="btn py-2 {{ ($payorType ?? '') === 'HMO' ? 'btn-primary fw-bold' : 'btn-white text-secondary border-light-subtle' }}">
        <i class="ph ph-shield me-1"></i> HMO Corporate Guarantees
      </a>
      <a href="{{ route('ar.ar-aging', array_merge(request()->query(), ['payor_type' => 'PHILHEALTH'])) }}" 
         class="btn py-2 {{ ($payorType ?? '') === 'PHILHEALTH' ? 'btn-primary fw-bold' : 'btn-white text-secondary border-light-subtle' }}">
        <i class="ph ph-crosshair me-1"></i> PhilHealth Claims
      </a>
    </div>
  </div>

  <!-- Aging Schedule Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.ar-aging') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <input type="hidden" name="payor_type" value="{{ $payorType ?? 'ALL' }}">
        
        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 820px;">
          <div style="min-width: 220px;" class="flex-grow-1">
            <input type="search" name="search" class="form-control form-control-sm" placeholder="Search MRN, Patient, or HMO..." value="{{ request('search') }}">
          </div>

          <div style="min-width: 170px;">
            <select name="admission_type" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="ALL" {{ request('admission_type', 'ALL') === 'ALL' ? 'selected' : '' }}>All Admission Types</option>
              <option value="INPATIENT" {{ request('admission_type') === 'INPATIENT' ? 'selected' : '' }}>Inpatient</option>
              <option value="OUTPATIENT" {{ request('admission_type') === 'OUTPATIENT' ? 'selected' : '' }}>Outpatient</option>
              <option value="EMERGENCY" {{ request('admission_type') === 'EMERGENCY' ? 'selected' : '' }}>Emergency</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">As-Of Date:</label>
            <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate ?? date('Y-m-d') }}" onchange="this.form.submit()" style="width: 140px;">
            <button type="submit" class="btn btn-sm btn-primary px-3 text-nowrap">
              <i class="ph ph-magnifying-glass me-1"></i> Filter
            </button>
          </div>
        </div>

        <div class="text-muted fs-xs text-nowrap ms-auto">
          Showing <strong>{{ $totalDebtors ?? count($debtors ?? []) }}</strong> Debtor Accounts
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Patient MRN &amp; Name</th>
              <th>Admission Type</th>
              <th>HMO Provider</th>
              <th class="text-end">Current (&lt;30d)</th>
              <th class="text-end">31 - 60 Days</th>
              <th class="text-end">61 - 90 Days</th>
              <th class="text-end">91 - 120 Days</th>
              <th class="text-end">120+ Days</th>
              <th class="text-end">Total Due (₱)</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($debtors as $d)
            <tr>
              <td>
                <div class="fw-bold text-dark">{{ $d['debtor_name'] }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $d['debtor_code'] }} &bull; <span class="text-primary fw-semibold">{{ $d['debtor_type'] }}</span></div>
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ $d['admission'] }}</span>
                @if(isset($d['statutory_category']))
                  @if($d['statutory_category'] === 'SENIOR_CITIZEN')
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1"><i class="ph ph-heart me-1"></i>Senior 20%</span>
                  @elseif($d['statutory_category'] === 'PWD')
                    <span class="badge bg-teal-subtle text-teal border border-teal-subtle ms-1" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4 !important;"><i class="ph ph-wheelchair me-1"></i>PWD 20%</span>
                  @elseif($d['statutory_category'] === 'EMPLOYEE' || $d['statutory_category'] === 'EMPLOYEE_SUBSIDY')
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Employee</span>
                  @elseif($d['statutory_category'] === 'CHARITY' || $d['statutory_category'] === 'CHARITY_SUBSIDY')
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">Charity</span>
                  @endif
                @endif
              </td>
              <td>
                @if($d['hmo'] !== 'Self-Pay' && $d['hmo'] !== 'None')
                  <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $d['hmo'] }}</span>
                @else
                  <span class="badge bg-light text-muted border">Self-Pay</span>
                @endif
              </td>
              <td class="text-end font-monospace">{{ (float)$d['current'] > 0 ? '₱' . number_format((float)$d['current'], 2) : '—' }}</td>
              <td class="text-end font-monospace text-primary">{{ (float)$d['days_31_60'] > 0 ? '₱' . number_format((float)$d['days_31_60'], 2) : '—' }}</td>
              <td class="text-end font-monospace text-warning">{{ (float)$d['days_61_90'] > 0 ? '₱' . number_format((float)$d['days_61_90'], 2) : '—' }}</td>
              <td class="text-end font-monospace text-danger">{{ (float)$d['days_91_120'] > 0 ? '₱' . number_format((float)$d['days_91_120'], 2) : '—' }}</td>
              <td class="text-end font-monospace text-danger fw-bold">{{ (float)$d['days_120_plus'] > 0 ? '₱' . number_format((float)$d['days_120_plus'], 2) : '—' }}</td>
              <td class="text-end font-monospace fw-bold text-danger fs-6">₱{{ number_format((float)$d['total_due'], 2) }}</td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs text-nowrap" 
                        data-bs-toggle="offcanvas" data-bs-target="#breakdownDrawer{{ $loop->index }}" title="View Invoice Breakdown">
                  <i class="ph ph-eye me-1"></i> View Breakdown
                </button>

                <!-- Invoice Breakdown Slide-Over Drawer -->
                <div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="breakdownDrawer{{ $loop->index }}" style="width: 750px; max-width: 90vw;">
                  <div class="offcanvas-header bg-dark text-white py-3 px-4">
                    <div>
                      <h6 class="offcanvas-title fw-bold mb-0 text-white d-flex align-items-center gap-2">
                        <i class="ph ph-receipt fs-4 text-primary"></i> {{ $d['debtor_name'] }}
                      </h6>
                      <span class="fs-xs text-muted font-monospace">{{ $d['debtor_code'] }} &bull; {{ $d['debtor_type'] }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>

                  <div class="offcanvas-body p-4 text-start">
                    <!-- Summary Header Card -->
                    <div class="card border border-light-subtle rounded-3 p-3 mb-4 bg-light-subtle">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <span class="text-muted fs-xs d-block">Debtor Classification:</span>
                          <strong class="text-dark fs-sm">{{ $d['debtor_type'] }}</strong>
                        </div>
                        <div class="text-end">
                          <span class="text-muted fs-xs d-block">Total Debt Outstanding:</span>
                          <strong class="text-danger font-monospace fs-5">₱{{ number_format((float)$d['total_due'], 2) }}</strong>
                        </div>
                      </div>
                    </div>

                    <!-- Itemized Invoices Table -->
                    <div class="fw-bold text-uppercase fs-xs text-secondary mb-2 d-flex justify-content-between align-items-center">
                      <span><i class="ph ph-list-numbers me-1"></i> Itemized Unpaid Invoices ({{ count($d['invoices'] ?? []) }})</span>
                      <span class="badge bg-light text-muted border">As-Of: {{ $asOfDate }}</span>
                    </div>

                    <div class="table-responsive border rounded-3 bg-white mb-4">
                      <table class="table table-sm table-hover align-middle mb-0 fs-xs">
                        <thead class="table-light">
                          <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Overdue</th>
                            <th class="text-end">Amount (₱)</th>
                            <th class="text-end">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($d['invoices'] ?? [] as $inv)
                            <tr>
                              <td>
                                <div class="font-monospace fw-bold text-primary">{{ $inv['invoice_number'] }}</div>
                                <div class="fs-xs text-muted">{{ $inv['claim_type'] }}</div>
                              </td>
                              <td class="text-muted">{{ $inv['invoice_date'] }}</td>
                              <td>
                                @if($inv['days_overdue'] <= 30)
                                  <span class="badge bg-success-subtle text-success fs-xs"><i class="ph ph-clock me-1"></i> {{ $inv['days_overdue'] }}d (Current)</span>
                                @elseif($inv['days_overdue'] <= 60)
                                  <span class="badge bg-primary-subtle text-primary fs-xs"><i class="ph ph-clock me-1"></i> {{ $inv['days_overdue'] }}d (31-60d)</span>
                                @elseif($inv['days_overdue'] <= 90)
                                  <span class="badge bg-warning-subtle text-warning-emphasis fs-xs"><i class="ph ph-clock me-1"></i> {{ $inv['days_overdue'] }}d (61-90d)</span>
                                @else
                                  <span class="badge bg-danger-subtle text-danger fs-xs"><i class="ph ph-warning me-1"></i> {{ $inv['days_overdue'] }}d (90d+)</span>
                                @endif
                              </td>
                              <td class="text-end font-monospace fw-bold text-dark">
                                ₱{{ number_format((float) $inv['amount_due'], 2) }}
                              </td>
                              <td class="text-end">
                                <a href="{{ route('collection.cashier-desk') }}" class="btn btn-xs btn-outline-success py-1 px-2" title="Settle at Cashier Desk">
                                  <i class="ph ph-cash-register me-1"></i> Settle
                                </a>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="5" class="text-center py-3 text-muted">No itemized invoices found.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>

                    <!-- Quick Action Footer inside Drawer -->
                    <div class="d-flex justify-content-between gap-2 pt-2 border-top">
                      <a href="{{ route('ar.credit-notes') }}" class="btn btn-sm btn-outline-danger w-50 fw-medium">
                        <i class="ph ph-minus-circle me-1"></i> Issue Credit Note / Write-Off
                      </a>
                      <a href="{{ route('collection.cashier-desk') }}" class="btn btn-sm btn-primary w-50 fw-medium">
                        <i class="ph ph-check-circle me-1"></i> Cashier Settlement Desk
                      </a>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">No outstanding accounts receivable found for this cutoff date.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold font-monospace">
            <tr>
              <td colspan="3">TOTALS:</td>
              <td class="text-end text-success">₱{{ number_format((float) ($totalCurrent ?? 0), 2) }}</td>
              <td class="text-end text-primary">₱{{ number_format((float) ($total31To60 ?? 0), 2) }}</td>
              <td class="text-end text-warning">₱{{ number_format((float) ($total61To90 ?? 0), 2) }}</td>
              <td class="text-end text-danger">₱{{ number_format((float) ($total91To120 ?? 0), 2) }}</td>
              <td class="text-end text-danger">₱{{ number_format((float) ($total120Plus ?? 0), 2) }}</td>
              <td class="text-end text-danger fs-6">₱{{ number_format((float) ($grandTotalAR ?? 0), 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
