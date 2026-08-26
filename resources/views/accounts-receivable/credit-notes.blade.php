@extends('layouts.app')

@section('title', 'Credit Notes & Discounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'credit-notes')

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
          <li class="breadcrumb-item active">Credit Notes &amp; Discounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Credit Notes &amp; Statutory Discounts</h1>
      <p class="text-muted fs-xs mb-0">Apply mandatory Senior Citizen (20%) &amp; PWD statutory discounts, charity medical subsidies, courtesy price adjustments, and billing corrections.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoicing & Billing', 'General Ledger']" 
          :tables="['credit_notes', 'invoices', 'journal_entries']"
          glImpact="DR 5010 Sales Discounts (Senior/PWD) / CR 1110/1120 AR Patient Copay"
          description="Applies statutory 20% Senior/PWD discounts and charity subsidies to patient bills." 
      />
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createCreditNoteModal">
        <i class="ph ph-plus me-1"></i> Issue Credit Adjustment
      </button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Approved &amp; Posted Credit Adjustments</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success font-monospace">₱{{ number_format((float) $totalCreditValue, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Management Approval (Drafts)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hourglass fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-warning font-monospace">₱{{ number_format((float) $totalPendingApproval, 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Credit Notes Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.credit-notes') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Draft (Pending Approval)</option>
            <option value="POSTED" {{ request('status') === 'POSTED' ? 'selected' : '' }}>Posted / Applied</option>
            <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search CN #, invoice, patient..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Credit Note #</th>
              <th>Applied Invoice &amp; Patient</th>
              <th>Issue Date</th>
              <th>Adjustment Reason</th>
              <th class="text-end">Credit Amount</th>
              <th>Status</th>
              <th>Approved By</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($creditNotes as $cn)
            @php
              $amt = (float) $cn->amount;
              $statusBadge = match($cn->status) {
                'POSTED', 'APPLIED', 'APPROVED' => 'bg-success-subtle text-success',
                default                         => 'bg-warning-subtle text-warning',
              };
            @endphp
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $cn->credit_note_number }}</span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $cn->patientAccount?->full_name ?? ($cn->invoice?->patientAccount?->full_name ?? 'Patient') }}</div>
                <div class="fs-xs text-muted font-monospace">Invoice: {{ $cn->invoice?->invoice_number ?? 'N/A' }}</div>
              </td>
              <td>{{ $cn->issue_date ? $cn->issue_date->format('M d, Y') : '—' }}</td>
              <td>
                <span class="badge bg-light text-dark border">{{ $cn->reason }}</span>
              </td>
              <td class="text-end font-monospace fw-bold text-danger fs-6">₱{{ number_format($amt, 2) }}</td>
              <td>
                <span class="badge {{ $statusBadge }}">{{ $cn->status }}</span>
              </td>
              <td>
                <span class="fs-xs text-muted">{{ $cn->approver?->name ?? '—' }}</span>
              </td>
              <td class="text-end">
                @if($cn->status === 'DRAFT')
                  <form method="POST" action="{{ route('ar.credit-notes.approve', $cn->id) }}" onsubmit="return confirm('Authorize and post credit note {{ $cn->credit_note_number }} to General Ledger?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary py-1 px-2 fs-xs" title="Finance Manager Approval">
                      <i class="ph ph-stamp me-1"></i> Approve &amp; Post
                    </button>
                  </form>
                @else
                  <span class="badge bg-light text-muted border">
                    <i class="ph ph-check-double text-success me-1"></i> Settled
                  </span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No credit notes found matching filter.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $creditNotes->firstItem() ?? 0 }} - {{ $creditNotes->lastItem() ?? 0 }} of {{ $creditNotes->total() }} Records</span>
      <div>
        {{ $creditNotes->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Issue Credit Note -->
<div class="modal fade" id="createCreditNoteModal" tabindex="-1" aria-labelledby="createCreditNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-plus-circle me-2 text-primary"></i>Issue Credit Note Adjustment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ar.credit-notes.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target Open Invoice <span class="text-danger">*</span></label>
            <select name="invoice_id" class="form-select form-select-sm" required>
              <option value="">-- Choose Open Patient Invoice --</option>
              @foreach($openInvoices as $inv)
                <option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ $inv->patientAccount?->full_name }} (Open Copay: ₱{{ number_format((float) $inv->balance_due, 2) }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Adjustment Reason / Type <span class="text-danger">*</span></label>
            <select name="reason" class="form-select form-select-sm" required>
              <option value="SENIOR_CITIZEN_DISCOUNT">Statutory Senior Citizen Discount (20%)</option>
              <option value="PWD_DISCOUNT">Person with Disability (PWD) Discount (20%)</option>
              <option value="CHARITY_SUBSIDY">Medical Social Service Charity Subsidy</option>
              <option value="EMPLOYEE_SUBSIDY">Hospital Employee &amp; Dependent Subsidy</option>
              <option value="BILLING_ADJUSTMENT">Disputed Item / Procedure Cancellation</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Credit Amount (₱) <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" name="amount" class="form-control font-monospace" placeholder="0.00" required>
            </div>
            <div class="form-text fs-xs">Amount cannot exceed the invoice's open patient copay balance.</div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Issue Date</label>
            <input type="date" name="issue_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Submit Credit Note</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
