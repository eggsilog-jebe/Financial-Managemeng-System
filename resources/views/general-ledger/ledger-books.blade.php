@extends('layouts.app')

@section('title', 'Ledger Books - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'ledger-books')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('gl.journal-entries') }}">General Ledger</a></li>
          <li class="breadcrumb-item active">Ledger Books</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold text-dark">General Ledger Account Books &amp; T-Accounts</h1>
      <p class="text-muted fs-xs mb-0">Inspect transaction movements, beginning balances, and chronological running balances by account code.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Journal Entries', 'Chart of Accounts']" 
          description="Detailed T-account historical ledgers and running balance books." 
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Statement
      </button>
      <a href="{{ route('gl.ledger-books.export', ['account_id' => $selectedAccountId, 'start_date' => $startDate, 'end_date' => $endDate, 'fiscal_year' => $fiscalYear]) }}" class="btn btn-primary btn-sm">
        <i class="ph ph-file-arrow-down me-1"></i> Export Account Statement (CSV)
      </a>
    </div>
  </div>

  <!-- Account Selector & Filter Bar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('gl.ledger-books') }}">
        <div class="row g-3 align-items-end">
          <!-- Account Selector -->
          <div class="col-md-4">
            <label for="accountSelect" class="form-label small fw-semibold mb-1"><i class="ph ph-book me-1 text-primary"></i> Select GL Account:</label>
            <select name="account_id" id="accountSelect" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
              @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ $acc->id === (int)$selectedAccountId ? 'selected' : '' }}>
                  {{ $acc->code }} - {{ $acc->name }} ({{ $acc->category }})
                </option>
              @endforeach
            </select>
          </div>

          <!-- Date Range: Start Date -->
          <div class="col-md-2">
            <label for="startDateInput" class="form-label small fw-semibold mb-1">Start Date:</label>
            <input type="date" name="start_date" id="startDateInput" class="form-control form-control-sm bg-light" value="{{ $startDate ?? '' }}" onchange="this.form.submit()">
          </div>

          <!-- Date Range: End Date -->
          <div class="col-md-2">
            <label for="endDateInput" class="form-label small fw-semibold mb-1">End Date:</label>
            <input type="date" name="end_date" id="endDateInput" class="form-control form-control-sm bg-light" value="{{ $endDate ?? '' }}" onchange="this.form.submit()">
          </div>

          <!-- Fiscal Year -->
          <div class="col-md-2">
            <label for="fiscalYearSelect" class="form-label small fw-semibold mb-1">Fiscal Year:</label>
            <select name="fiscal_year" id="fiscalYearSelect" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
              <option value="" {{ empty($fiscalYear) ? 'selected' : '' }}>All Years</option>
              @for($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--)
                <option value="{{ $y }}" {{ ($fiscalYear ?? '') == (string)$y ? 'selected' : '' }}>FY {{ $y }}</option>
              @endfor
            </select>
          </div>

          <!-- Reset Button -->
          <div class="col-md-2 d-flex gap-2">
            <a href="{{ route('gl.ledger-books', ['account_id' => $selectedAccountId]) }}" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ph ph-arrow-counter-clockwise me-1"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if($statement && $statement['account'])
  @php
    $acc = $statement['account'];
    $isDebitNormal = strtoupper((string) $acc->normal_balance) === 'DEBIT';
  @endphp

  <!-- Account Header Summary Cards -->
  <div class="row g-3 mb-4">
    <!-- Account Information -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Account Title</span>
          <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $acc->code }}</span>
        </div>
        <h5 class="fw-bold mb-1 text-dark text-truncate">{{ $acc->name }}</h5>
        <div class="d-flex gap-2 fs-xs">
          <span class="badge bg-light text-dark border">{{ $acc->category }}</span>
          <span class="badge bg-light text-dark border">{{ $acc->normal_balance }} NORMAL</span>
        </div>
      </div>
    </div>

    <!-- Beginning Balance -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Beginning Balance</span>
          <span class="badge bg-secondary-subtle text-secondary p-2 rounded-2"><i class="ph ph-clock-counter-clockwise fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark font-monospace">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</h4>
        <span class="fs-xs text-muted">As of {{ $startDate ?? 'Beginning of Records' }}</span>
      </div>
    </div>

    <!-- Period Movements -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Period Movements</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-arrows-left-right fs-5"></i></span>
        </div>
        <div class="d-flex justify-content-between fs-sm mb-1">
          <span class="text-success fw-semibold">Debits: ₱{{ number_format((float) $statement['period_debits'], 2) }}</span>
        </div>
        <div class="d-flex justify-content-between fs-sm">
          <span class="text-danger fw-semibold">Credits: ₱{{ number_format((float) $statement['period_credits'], 2) }}</span>
        </div>
      </div>
    </div>

    <!-- Ending Balance -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Ending Ledger Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-scales fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $statement['ending_balance'], 2) }}</h4>
        <span class="fs-xs text-muted">Cumulative {{ $acc->normal_balance }} Balance</span>
      </div>
    </div>
  </div>

  <!-- Running Balance Statement Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark">
        <i class="ph ph-receipt me-1 text-primary"></i> Chronological Statement of Account Movements
      </h6>
      <span class="badge bg-light text-dark border fs-xs">
        Statement Period: {{ $startDate ?? 'Beginning' }} &rarr; {{ $endDate ?? 'Present' }}
      </span>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="ledgerTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 110px;">Date</th>
              <th style="width: 140px;">Journal Ref #</th>
              <th style="width: 100px;">Type</th>
              <th>Description / Transaction Memo</th>
              <th class="text-end" style="width: 140px;">Debit (₱)</th>
              <th class="text-end" style="width: 140px;">Credit (₱)</th>
              <th class="text-end" style="width: 160px;">Running Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <!-- Beginning Balance Row -->
            <tr class="table-light-subtle fst-italic">
              <td><span class="font-monospace fs-xs text-muted">{{ $startDate ?? date('Y-m-01') }}</span></td>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace">OPENING</span></td>
              <td><span class="badge bg-light text-dark border fs-xs">BEGIN</span></td>
              <td class="text-muted fw-semibold">Beginning Balance Forwarded</td>
              <td class="text-end font-monospace text-muted">-</td>
              <td class="text-end font-monospace text-muted">-</td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</td>
            </tr>

            @forelse($statement['rows'] as $row)
            @php
              $isDebit = (float) $row['debit'] > 0;
              $isCredit = (float) $row['credit'] > 0;
            @endphp
            <tr>
              <td><span class="font-monospace fs-xs text-muted">{{ $row['entry_date'] }}</span></td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary font-monospace fs-xs px-2 py-1">
                  {{ $row['reference_number'] }}
                </span>
              </td>
              <td><span class="badge bg-light text-dark border fs-xs">{{ $row['type'] }}</span></td>
              <td>
                <div class="fw-semibold text-dark fs-sm">{{ $row['memo'] }}</div>
                @if($row['memo'] !== $row['description'])
                  <span class="fs-xs text-muted">{{ $row['description'] }}</span>
                @endif
              </td>
              <td class="text-end font-monospace {{ $isDebit ? 'fw-bold text-dark' : 'text-muted' }}">
                {{ $isDebit ? '₱' . number_format((float) $row['debit'], 2) : '-' }}
              </td>
              <td class="text-end font-monospace {{ $isCredit ? 'fw-bold text-dark' : 'text-muted' }}">
                {{ $isCredit ? '₱' . number_format((float) $row['credit'], 2) : '-' }}
              </td>
              <td class="text-end font-monospace fw-bold text-success fs-sm">
                ₱{{ number_format((float) $row['running_balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                No posted journal transactions recorded for this account within the selected period.
              </td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="4" class="text-end">PERIOD TOTALS &amp; FINAL ENDING BALANCE:</td>
              <td class="text-end font-monospace text-dark">₱{{ number_format((float) $statement['period_debits'], 2) }}</td>
              <td class="text-end font-monospace text-dark">₱{{ number_format((float) $statement['period_credits'], 2) }}</td>
              <td class="text-end font-monospace text-success fs-5">₱{{ number_format((float) $statement['ending_balance'], 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  @else
  <div class="alert alert-info rounded-3">
    Please select a General Ledger account to view its statement of account and T-account running balances.
  </div>
  @endif
</div>
@endsection
