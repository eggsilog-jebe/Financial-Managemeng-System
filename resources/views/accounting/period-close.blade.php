@extends('layouts.app')

@section('title', 'Fiscal Period-End Closing & Hard Locking')
@section('module', 'general-ledger')
@section('page', 'period-close')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Fiscal Period Closing &amp; Hard Locking</h1>
      <p class="text-muted mb-0">Executive Period-End Hard Lock &bull; Retroactive Postings Prevention &bull; CFO Authorization Only</p>
    </div>
    <div class="d-flex gap-2">
      <span class="badge bg-danger fs-6 py-2 px-3 align-self-center">
        <i class="ph ph-lock-key me-1"></i> CFO Exclusive Area
      </span>
      <a href="{{ route('accounting.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph ph-check-circle fs-5 me-2 align-middle"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row g-4">
    <!-- Active Fiscal Period Status Card -->
    <div class="col-md-5">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="fw-bold mb-0 text-dark">Active Fiscal Cycle</h5>
          <span class="badge bg-success-subtle text-success border border-success-subtle">OPEN FOR POSTING</span>
        </div>
        
        <div class="p-3 bg-light rounded-3 mb-4">
          <div class="d-flex justify-content-between text-muted fs-sm mb-2">
            <span>Current Period:</span>
            <strong class="text-dark">{{ $activePeriod }}</strong>
          </div>
          <div class="d-flex justify-content-between text-muted fs-sm mb-2">
            <span>Total Journal Entries:</span>
            <strong class="text-dark font-monospace">{{ $totalEntriesCount }} Transactions</strong>
          </div>
          <div class="d-flex justify-content-between text-muted fs-sm">
            <span>Pending Unposted Drafts:</span>
            <strong class="text-warning font-monospace">{{ $unpostedEntriesCount }} Drafts</strong>
          </div>
        </div>

        <div class="alert alert-warning border-0 rounded-3 mb-4">
          <h6 class="fw-bold text-dark mb-1"><i class="ph ph-warning me-1"></i> Period Hard Locking Rules:</h6>
          <ul class="mb-0 fs-xs ps-3 text-secondary">
            <li>Locks the General Ledger against all past-dated postings and adjustments.</li>
            <li>Enforces immutable compliance under Philippine BIR CAS regulations.</li>
            <li>Reopening a locked period requires external BIR CAS audit re-authorization.</li>
          </ul>
        </div>

        <form method="POST" action="{{ route('accounting.period-close.lock') }}" onsubmit="return confirm('Are you sure you want to hard lock this fiscal period? This action cannot be reversed without CFO override.');">
          @csrf
          <input type="hidden" name="period_name" value="{{ $activePeriod }}">
          <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
            <i class="ph ph-lock-key me-1"></i> Execute Fiscal Hard Lock (Close Period)
          </button>
        </form>
      </div>
    </div>

    <!-- Fiscal Period History -->
    <div class="col-md-7">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
          <h5 class="fw-bold mb-0 text-dark">Fiscal Period History &amp; Audit Logs</h5>
          <small class="text-muted">Historical monthly and annual closing records</small>
        </div>
        <div class="table-responsive p-3">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="fs-xs text-muted text-uppercase">
                <th>Period Code</th>
                <th>Closing Date</th>
                <th>Closed By</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="badge bg-light text-dark font-monospace border">FY-2026-07</span></td>
                <td>Jul 31, 2026</td>
                <td>Dr. Roberto Garcia, CPA (CFO)</td>
                <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-lock me-1"></i> LOCKED</span></td>
              </tr>
              <tr>
                <td><span class="badge bg-light text-dark font-monospace border">FY-2026-06</span></td>
                <td>Jun 30, 2026</td>
                <td>Dr. Roberto Garcia, CPA (CFO)</td>
                <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-lock me-1"></i> LOCKED</span></td>
              </tr>
              <tr>
                <td><span class="badge bg-light text-dark font-monospace border">FY-2026-05</span></td>
                <td>May 31, 2026</td>
                <td>Dr. Roberto Garcia, CPA (CFO)</td>
                <td><span class="badge bg-danger-subtle text-danger"><i class="ph ph-lock me-1"></i> LOCKED</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
