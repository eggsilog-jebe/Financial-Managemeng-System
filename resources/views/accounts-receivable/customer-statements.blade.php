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
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Statements of Account (SOA)</h1>
      <p class="text-muted fs-xs mb-0">Generate official, itemized Statements of Account (SOA) showing all clinical charges, cashier payments, discounts, and final balance due.</p>
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
      <form method="GET" action="{{ route('ar.statements') }}" class="row g-3 align-items-end" id="soaFilterForm">
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-buildings me-1 text-primary"></i> Care Setting / Admission:</label>
          <select name="admission_type" id="admissionTypeFilter" class="form-select form-select-sm bg-light">
            <option value="" {{ empty($admissionType) ? 'selected' : '' }}>All Care Settings</option>
            <option value="OUTPATIENT" {{ strtoupper((string)($admissionType ?? '')) === 'OUTPATIENT' ? 'selected' : '' }}>Outpatient (OPD)</option>
            <option value="INPATIENT" {{ strtoupper((string)($admissionType ?? '')) === 'INPATIENT' ? 'selected' : '' }}>Inpatient (IPD)</option>
            <option value="EMERGENCY" {{ strtoupper((string)($admissionType ?? '')) === 'EMERGENCY' ? 'selected' : '' }}>Emergency (ER)</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-user me-1 text-primary"></i> Select Patient / Debtor Account:</label>
          <select name="patient_id" id="patientSelect" class="form-select form-select-sm bg-light" required>
            <option value="" data-admission="">-- Choose Patient Account --</option>
            @foreach($accounts as $acc)
              @php
                $typeUpper = strtoupper((string)($acc->admission_type ?? 'INPATIENT'));
                $badgeText = match($typeUpper) {
                  'OUTPATIENT' => 'OPD',
                  'EMERGENCY'  => 'ER',
                  default      => 'IPD',
                };
              @endphp
              <option value="{{ $acc->id }}" 
                      data-admission="{{ $typeUpper }}"
                      data-name="{{ strtolower($acc->full_name) }}"
                      data-mrn="{{ strtolower($acc->patient_id_number) }}"
                      {{ (string)$patientId === (string)$acc->id ? 'selected' : '' }}>
                [{{ $badgeText }}] {{ $acc->full_name }} ({{ $acc->patient_id_number }}) — Open: ₱{{ number_format((float) $acc->current_balance, 2) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-calendar me-1 text-primary"></i> Period Start Date:</label>
          <input type="date" name="start_date" class="form-control form-control-sm bg-light" value="{{ $startDate }}">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark mb-1"><i class="ph ph-calendar me-1 text-primary"></i> Period End Date:</label>
          <input type="date" name="end_date" class="form-control form-control-sm bg-light" value="{{ $endDate }}">
        </div>
        <div class="col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" title="Generate Statement"><i class="ph ph-magnifying-glass"></i> View</button>
          @if($patientId || $admissionType)
            <a href="{{ route('ar.statements') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="ph ph-arrow-counter-clockwise"></i></a>
          @endif
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
        <div class="d-flex align-items-center gap-2 mb-1">
          <h6 class="fw-bold text-dark mb-0">Statement Ledger for {{ $selectedAccount->full_name }}</h6>
          @php
            $selType = strtoupper((string)($selectedAccount->admission_type ?? 'INPATIENT'));
            $badgeColor = match($selType) {
              'OUTPATIENT' => 'bg-info-subtle text-info border-info-subtle',
              'EMERGENCY'  => 'bg-danger-subtle text-danger border-danger-subtle',
              default      => 'bg-primary-subtle text-primary border-primary-subtle',
            };
          @endphp
          <span class="badge {{ $badgeColor }} border fs-xs">{{ $selectedAccount->admission_type ?? 'Inpatient' }}</span>
        </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const admissionFilter = document.getElementById('admissionTypeFilter');
  const patientSelect = document.getElementById('patientSelect');

  function filterPatientDropdown() {
    if (!admissionFilter || !patientSelect) return;
    const selectedAdmission = admissionFilter.value.toUpperCase();
    const options = patientSelect.querySelectorAll('option');

    let currentOptionStillValid = false;

    options.forEach(opt => {
      if (!opt.value) return; // Keep placeholder
      const optAdmission = (opt.getAttribute('data-admission') || '').toUpperCase();
      if (!selectedAdmission || optAdmission === selectedAdmission) {
        opt.hidden = false;
        opt.disabled = false;
        if (opt.selected) currentOptionStillValid = true;
      } else {
        opt.hidden = true;
        opt.disabled = true;
        if (opt.selected) opt.selected = false;
      }
    });

    if (!currentOptionStillValid && patientSelect.value && selectedAdmission) {
      // Find first visible option or reset to placeholder
      const firstVisible = Array.from(options).find(opt => opt.value && !opt.hidden);
      if (firstVisible) {
        firstVisible.selected = true;
      } else {
        patientSelect.value = '';
      }
    }
  }

  if (admissionFilter) {
    admissionFilter.addEventListener('change', filterPatientDropdown);
  }
});
</script>
@endpush
@endsection
