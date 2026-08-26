@extends('layouts.app')

@section('title', 'Statements of Account (SOA) - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'statements')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Statements of Account</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; Customer Statements (SOA)</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoicing & Billing', 'Cashier Desk (Payments)', 'Credit Notes']" 
          :tables="['invoices', 'payments', 'credit_notes', 'hmo_claims']"
          description="Compiles total charges, cashier payments, discounts, and HMO settlements into a running SOA ledger." 
      />
      @if($selectedAccount)
        <a href="{{ route('ar.statements.export', ['patient_id' => $selectedAccount->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-secondary btn-sm">
          <i class="ph ph-download-simple me-1"></i> Export Statement CSV
        </a>
        <a href="{{ route('ar.statements.print', ['patient_id' => $selectedAccount->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-primary btn-sm">
          <i class="ph ph-printer me-1"></i> Print Official SOA
        </a>
      @endif
    </div>
  </div>

  <!-- Statement Filter Builder Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('ar.statements') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-user me-1 text-primary"></i> Select Patient / Debtor Account:</label>
          <select name="patient_id" class="form-select form-select-sm bg-light" required>
            <option value="">-- Choose Patient Account --</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}" {{ (string)$patientId === (string)$acc->id ? 'selected' : '' }}>
                {{ $acc->full_name }} ({{ $acc->patient_id_number }}) — Open Balance: ₱{{ number_format((float) $acc->current_balance, 2) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-calendar me-1 text-primary"></i> Period Start Date:</label>
          <input type="date" name="start_date" class="form-control form-control-sm bg-light" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-calendar me-1 text-primary"></i> Period End Date:</label>
          <input type="date" name="end_date" class="form-control form-control-sm bg-light" value="{{ $endDate }}">
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-sm btn-primary w-100"><i class="ph ph-magnifying-glass"></i> View</button>
        </div>
      </form>
    </div>
  </div>

  @if($statement && $selectedAccount)
  <!-- Financial Summary KPI Bar -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Beginning Balance</span>
        <h5 class="fw-bold mb-0 text-dark font-monospace">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</h5>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Period Invoiced (Debits)</span>
        <h5 class="fw-bold mb-0 text-primary font-monospace">+₱{{ number_format((float) $statement['total_debits'], 2) }}</h5>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Payments &amp; Credits (Credits)</span>
        <h5 class="fw-bold mb-0 text-success font-monospace">-₱{{ number_format((float) $statement['total_credits'], 2) }}</h5>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-danger-subtle">
        <span class="text-danger fs-xs text-uppercase fw-bold d-block mb-1">Ending Balance Due</span>
        <h4 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) $statement['ending_balance'], 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Running-Balance Ledger Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <div>
        <h6 class="fw-bold text-dark mb-0">Statement Ledger for {{ $selectedAccount->full_name }}</h6>
        <span class="fs-xs text-muted font-monospace">MRN: {{ $selectedAccount->patient_id_number }} | {{ $statement['start_date'] }} to {{ $statement['end_date'] }}</span>
      </div>
      <span class="badge bg-light text-dark border">{{ count($statement['movements']) }} Ledger Records</span>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Transaction Type</th>
              <th>Reference #</th>
              <th>Particulars / Description</th>
              <th class="text-end">Charges / Debit (₱)</th>
              <th class="text-end">Payments / Credit (₱)</th>
              <th class="text-end">Running Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-light-subtle text-muted">
              <td>{{ $statement['start_date'] }}</td>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace">FORWARD</span></td>
              <td>—</td>
              <td>Beginning Balance Forwarded</td>
              <td class="text-end font-monospace">—</td>
              <td class="text-end font-monospace">—</td>
              <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</td>
            </tr>

            @forelse($statement['movements'] as $m)
            @php
              $badgeClass = match($m['type']) {
                'INVOICE'     => 'bg-primary-subtle text-primary',
                'PAYMENT'     => 'bg-success-subtle text-success',
                'CREDIT_NOTE' => 'bg-warning-subtle text-warning',
                default       => 'bg-light text-dark',
              };
            @endphp
            <tr>
              <td>{{ $m['date'] }}</td>
              <td><span class="badge {{ $badgeClass }}">{{ $m['type'] }}</span></td>
              <td class="font-monospace fw-bold text-primary">{{ $m['reference'] }}</td>
              <td>{{ $m['description'] }}</td>
              <td class="text-end font-monospace text-primary fw-semibold">
                {{ (float) $m['debit'] > 0 ? '₱' . number_format((float) $m['debit'], 2) : '—' }}
              </td>
              <td class="text-end font-monospace text-success fw-semibold">
                {{ (float) $m['credit'] > 0 ? '₱' . number_format((float) $m['credit'], 2) : '—' }}
              </td>
              <td class="text-end font-monospace fw-bold text-danger fs-6">
                ₱{{ number_format((float) $m['balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No transactions recorded for this patient within selected period.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold font-monospace">
            <tr>
              <td colspan="4">PERIOD TOTALS:</td>
              <td class="text-end text-primary">+₱{{ number_format((float) $statement['total_debits'], 2) }}</td>
              <td class="text-end text-success">-₱{{ number_format((float) $statement['total_credits'], 2) }}</td>
              <td class="text-end text-danger fs-6">₱{{ number_format((float) $statement['ending_balance'], 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  @else
  <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
    <i class="ph ph-user-focus fs-1 mb-2 text-primary"></i>
    <h5 class="fw-bold text-dark">No Patient Selected</h5>
    <p class="small mb-0">Please choose a patient account from the dropdown above to view their Statement of Account (SOA).</p>
  </div>
  @endif
</div>
@endsection
