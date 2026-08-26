@extends('layouts.app')

@section('title', 'Period-End Closing - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'period-end-closing')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('gl.journal-entries') }}">General Ledger</a></li>
          <li class="breadcrumb-item active">Period-End Closing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">Period-End Financial Closing &amp; GL Locking</h1>
      <p class="text-muted fs-xs mb-0">Manage monthly and annual accounting cutoffs, lock transactions to prevent backdating, and close fiscal periods with full audit integrity.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Fiscal Periods', 'Audit Logs']" 
          description="Fiscal period cutoff lock and hard-close compliance control." 
      />
      <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#initYearModal">
        <i class="ph ph-calendar-plus me-1"></i> Start New Fiscal Year
      </button>
      <a href="{{ route('gl.trial-balance') }}" class="btn btn-outline-secondary btn-sm" title="Verify all debits equal credits before closing">
        <i class="ph ph-shield-check me-1"></i> Pre-Closing Trial Balance
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-warning-circle me-1"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- 3-Step Closing Workflow Guide -->
  <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-body p-3">
      <div class="row g-3 align-items-center">
        <div class="col-md-4 d-flex align-items-start gap-2 border-end-md">
          <span class="badge bg-primary rounded-circle p-2 fs-xs"><i class="ph ph-check"></i></span>
          <div>
            <strong class="d-block text-dark fs-xs">Step 1: Check Pre-Closing Balance</strong>
            <span class="fs-xs text-muted">Verify all bills, receipts, and entries are posted and balanced.</span>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-start gap-2 border-end-md">
          <span class="badge bg-warning rounded-circle p-2 fs-xs"><i class="ph ph-lock-key"></i></span>
          <div>
            <strong class="d-block text-dark fs-xs">Step 2: Lock Period (Soft Freeze)</strong>
            <span class="fs-xs text-muted">Blocks staff from posting new entries into this month.</span>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-start gap-2">
          <span class="badge bg-danger rounded-circle p-2 fs-xs"><i class="ph ph-scales"></i></span>
          <div>
            <strong class="d-block text-dark fs-xs">Step 3: CFO Final Close (Hard Close)</strong>
            <span class="fs-xs text-muted">Transfers net surplus into Retained Earnings and finalizes reports.</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Operating Period</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-calendar-blank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $activePeriod ?? date('F Y') }}</h4>
        <span class="fs-xs text-muted">Current Month Ledger</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unposted Draft Entries</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $unpostedEntriesCount ?? 0 }} {{ Str::plural('Entry', $unpostedEntriesCount ?? 0) }}</h4>
        <span class="fs-xs text-muted">Must be posted before close</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Ledger Entries</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-receipt fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalEntriesCount ?? 0 }} Entries</h4>
        <span class="fs-xs text-muted">Lifetime transactions recorded</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">GL Closing Integrity</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-lock-key fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ ($unpostedEntriesCount ?? 0) === 0 ? 'Ready for Close' : 'Drafts Pending' }}</h4>
        <span class="fs-xs text-muted">BIR CAS Chain Verified</span>
      </div>
    </div>
  </div>

  <!-- Periods Management Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('gl.period-end-closing') }}" class="d-flex align-items-center gap-2">
          <label for="fySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Fiscal Year:</label>
          <select name="fiscal_year" id="fySelect" class="form-select form-select-sm bg-light" style="min-width: 140px;" onchange="this.form.submit()">
            @foreach($allYears as $yr)
              <option value="{{ $yr }}" {{ $yr === $selectedYear ? 'selected' : '' }}>FY {{ $yr }}</option>
            @endforeach
          </select>
        </form>
      </div>

      <div class="fs-xs text-muted">
        <i class="ph ph-shield-check text-success me-1"></i> Segregation of Duties: CFO Hard Close Required for Retained Earnings Rollover
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 130px;">Period Code</th>
              <th style="width: 80px;">Month #</th>
              <th>Date Range</th>
              <th>Period Status</th>
              <th>Closed By</th>
              <th>Closed At</th>
              <th>Closing Journal Entry Ref</th>
              <th class="text-end text-nowrap" style="width: 240px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($periods as $period)
            @php
              $isClosed = $period->isClosed();
              $isLocked = $period->isLocked();
              $isOpen   = $period->isOpen();

              $statusBadge = match($period->status) {
                'AUDITED', 'CLOSED' => 'bg-dark text-white',
                'LOCKED'            => 'bg-warning-subtle text-warning border border-warning',
                default             => 'bg-success-subtle text-success border border-success',
              };

              $monthName = \Carbon\Carbon::createFromDate((int)$period->fiscal_year, $period->period_number, 1)->format('F');
            @endphp
            <tr>
              <td>
                <span class="badge bg-secondary-subtle text-dark font-monospace fs-xs px-2 py-1">
                  {{ $period->period_code }}
                </span>
                <span class="d-block fs-xs text-muted mt-1">{{ $monthName }} {{ $period->fiscal_year }}</span>
              </td>
              <td><span class="fw-bold font-monospace">M{{ str_pad((string)$period->period_number, 2, '0', STR_PAD_LEFT) }}</span></td>
              <td>
                <span class="fs-xs font-monospace text-muted">
                  {{ $period->start_date->format('Y-m-d') }} &rarr; {{ $period->end_date->format('Y-m-d') }}
                </span>
              </td>
              <td>
                @if($isOpen)
                  <span class="d-inline-flex align-items-center gap-1.5 font-monospace text-nowrap" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 600; background: #ecfdf5; border: 1px solid rgba(16, 185, 129, 0.35); color: #047857;">
                    <i class="ph ph-check fs-6"></i>
                    <span>OPEN</span>
                  </span>
                @elseif($isLocked)
                  <span class="d-inline-flex align-items-center gap-1.5 font-monospace text-nowrap" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 600; background: #fffbeb; border: 1px solid rgba(217, 119, 6, 0.35); color: #b45309;">
                    <i class="ph ph-lock fs-6"></i>
                    <span>LOCKED</span>
                  </span>
                @else
                  <span class="d-inline-flex align-items-center gap-1.5 font-monospace text-nowrap" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 600; background: #0f172a; border: 1px solid #0f172a; color: #ffffff;">
                    <i class="ph ph-shield-check fs-6 text-success"></i>
                    <span>AUDITED</span>
                  </span>
                @endif
              </td>
              <td><span class="fs-xs text-muted">{{ $period->closedByUser?->name ?? '-' }}</span></td>
              <td><span class="fs-xs text-muted">{{ $period->closed_at ? $period->closed_at->format('Y-m-d H:i') : '-' }}</span></td>
              <td>
                @if($period->closingJournalEntry)
                  <a href="{{ route('gl.journal-entries', ['q' => $period->closingJournalEntry->reference_number]) }}" class="badge bg-primary-subtle text-primary text-decoration-none font-monospace fs-xs">
                    {{ $period->closingJournalEntry->reference_number }}
                  </a>
                @else
                  <span class="fs-xs text-muted">-</span>
                @endif
              </td>
              <td class="text-end text-nowrap">
                <div class="d-inline-flex align-items-center gap-2">
                  @if($isOpen)
                    <!-- Soft Lock Action -->
                    <form action="{{ route('gl.period-end-closing.lock', $period->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-warning text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 550;" title="Lock Period (Freeze regular entries)">
                        <i class="ph ph-lock fs-6"></i>
                        <span>Soft Lock</span>
                      </button>
                    </form>
                  @endif

                  @if(!$isClosed)
                    <!-- Hard Close & Rollover Action (CFO only) -->
                    <button type="button" class="btn btn-sm btn-danger text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 550; background-color: #dc2626 !important; border-color: #dc2626 !important; color: #ffffff !important;" title="Hard Close Period & Rollover Nominal Balances to Retained Earnings" onclick="openHardCloseModal({{ $period->id }}, '{{ $period->period_code }}', '{{ $monthName }} {{ $period->fiscal_year }}')">
                      <i class="ph ph-lock-key fs-6"></i>
                      <span>Hard Close (CFO)</span>
                    </button>
                  @else
                    <span class="d-inline-flex align-items-center gap-1.5 text-nowrap" style="font-size: 11.5px; padding: 4px 10px; border-radius: 7px; font-weight: 550; background: #0f172a; border: 1px solid #0f172a; color: #ffffff;">
                      <i class="ph ph-shield-check fs-6 text-success"></i>
                      <span>Audited &amp; Closed</span>
                    </span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                No fiscal periods found for FY {{ $selectedYear }}. Click "Initialize Fiscal Year" to populate 12 months.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-between align-items-center">
      <span class="text-muted fs-xs">Fiscal Year {{ $selectedYear }} Periods Registry</span>
      <span class="fs-xs text-muted">BIR CAS Compliant Period Lock Engine</span>
    </div>
  </div>
</div>

<!-- Modal: Initialize Fiscal Year -->
<div class="modal fade" id="initYearModal" tabindex="-1" aria-labelledby="initYearModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-primary text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="initYearModalLabel"><i class="ph ph-calendar-plus me-2"></i>Initialize Fiscal Year Periods</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('gl.period-end-closing.initialize') }}" method="POST">
        @csrf
        <div class="modal-body p-4">
          <p class="fs-sm text-muted">
            This action will generate 12 monthly fiscal period rows (M01 through M12) with status <strong>OPEN</strong> for the selected fiscal calendar year.
          </p>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Fiscal Year (YYYY) <span class="text-danger">*</span></label>
            <input type="text" name="fiscal_year" class="form-control form-control-sm font-monospace" placeholder="e.g. 2026" value="{{ date('Y') }}" required pattern="\d{4}">
          </div>
        </div>
        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-check me-1"></i> Initialize Periods</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Hard Close & Rollover Modal (CFO Segregation of Duties) -->
<div class="modal fade" id="hardCloseModal" tabindex="-1" aria-labelledby="hardCloseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-danger text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="hardCloseModalLabel"><i class="ph ph-lock-key me-2"></i>Execute Fiscal Hard Close &amp; Rollover</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="hardCloseForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
          <div class="alert alert-warning rounded-3 fs-xs mb-3">
            <i class="ph ph-warning-octagon me-1"></i>
            <strong>CFO Authorization Required:</strong> Hard-closing period <strong id="closePeriodCode">2026-M01</strong> will permanently lock all transactions for <span id="closePeriodMonth">January 2026</span> and generate a closing journal entry zeroing all REVENUE &amp; EXPENSE accounts into <strong>3020 Retained Earnings</strong>.
          </div>
          <p class="fs-xs text-muted mb-0">
            Once closed, no further transactions, corrections, or adjustments can be posted to this period date range.
          </p>
        </div>
        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-danger px-4 fw-semibold"><i class="ph ph-lock-key me-1"></i> Execute Hard Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openHardCloseModal(periodId, periodCode, periodMonth) {
  document.getElementById('closePeriodCode').textContent = periodCode;
  document.getElementById('closePeriodMonth').textContent = periodMonth;

  const form = document.getElementById('hardCloseForm');
  form.action = "{{ url('/general-ledger/period-end-closing') }}/" + periodId + "/close";

  const modalEl = document.getElementById('hardCloseModal');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}
</script>
@endpush
