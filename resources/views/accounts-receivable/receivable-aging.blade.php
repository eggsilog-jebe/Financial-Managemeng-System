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
      <h1 class="h3 mb-0 font-weight-bold">Accounts Receivable (AR) Aging Schedule</h1>
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

  <!-- Aging Schedule Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.ar-aging') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold">As-Of Cutoff Date:</label>
          <input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate ?? date('Y-m-d') }}" onchange="this.form.submit()">
        </div>
        <div class="text-muted fs-xs">
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
            </tr>
          </thead>
          <tbody>
            @forelse($debtors as $d)
            <tr>
              <td>
                <div class="fw-bold text-dark">{{ $d['debtor_name'] }}</div>
                <div class="fs-xs text-muted font-monospace">{{ $d['debtor_code'] }}</div>
              </td>
              <td><span class="badge bg-light text-dark border">{{ $d['admission'] }}</span></td>
              <td>
                @if($d['hmo'] !== 'Self-Pay' && $d['hmo'] !== 'None')
                  <span class="badge bg-info-subtle text-info">{{ $d['hmo'] }}</span>
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
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No outstanding accounts receivable found for this cutoff date.</td>
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
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
