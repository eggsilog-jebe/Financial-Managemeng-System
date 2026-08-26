@extends('layouts.app')

@section('title', 'Patient & Customer Accounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'customers')

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
          <li class="breadcrumb-item active">Patient Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Payor Directory</h1>
      <p class="text-muted fs-xs mb-0">Master directory of patient billing profiles, HMO health insurances, contact details, and open balances.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['SPRS (Smart Patient Registration System)']" 
          description="Syncs Master Patient Index (MPI) and contact demographics." 
      />
      <button id="btnCreateAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createAccountModal">
        <i class="ph ph-plus me-1"></i> Register Patient Account
      </button>
    </div>
  </div>

  <!-- Primary Executive Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Payor Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-users fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalActive ?? 0 }} Registered</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Open Patient Receivable (AR)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-danger font-monospace">₱{{ number_format((float) ($totalReceivable ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">HMO Guaranteed Portfolio</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-info font-monospace">₱{{ number_format((float) ($hmoGuarantees ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Filters and Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ar.patients.index') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="outstandingOnlySwitch" name="outstanding_only" value="1" {{ request('outstanding_only') ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label small fw-semibold text-dark" for="outstandingOnlySwitch">With Balance Only</label>
          </div>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search MRN, name, HMO..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="patientAccountTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Patient MRN &amp; Name</th>
              <th>Admission Type</th>
              <th>HMO Provider / Guarantee</th>
              <th>Contact Details</th>
              <th class="text-end">Total Billed (₱)</th>
              <th class="text-end">Open Balance (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts as $acc)
            @php
              $mrn = $acc->patient_mrn ?: $acc->patient_id_number;
              $name = $acc->full_name;
              $bal = (float) $acc->current_balance;
              $billed = (float) $acc->total_billed;
            @endphp
            <tr>
              <td>
                <div class="fw-bold text-dark">{{ $name }}</div>
                <span class="fs-xs font-monospace text-primary fw-semibold">{{ $mrn }}</span>
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ $acc->admission_type ?? 'Inpatient' }}</span>
              </td>
              <td>
                @if($acc->hmo_provider)
                  <span class="badge bg-info-subtle text-info"><i class="ph ph-shield me-1"></i> {{ $acc->hmo_provider }}</span>
                @else
                  <span class="badge bg-light text-muted border">Self-Pay (Cash)</span>
                @endif
              </td>
              <td>
                <div class="fs-xs text-dark">{{ $acc->phone ?? '—' }}</div>
                <div class="fs-xs text-muted">{{ $acc->email ?? '' }}</div>
              </td>
              <td class="text-end font-monospace fw-semibold text-dark">₱{{ number_format($billed, 2) }}</td>
              <td class="text-end font-monospace fw-bold {{ $bal > 0 ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format($bal, 2) }}
              </td>
              <td>
                <span class="badge {{ $acc->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                  {{ $acc->status }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('ar.statements', ['patient_id' => $acc->id]) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fs-xs" title="View SOA">
                    <i class="ph ph-file-text me-1"></i> SOA
                  </a>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No patient/payor accounts registered in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $accounts->firstItem() ?? 0 }} - {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} Payor Accounts</span>
      <div>
        {{ $accounts->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Register Patient Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title font-weight-bold"><i class="ph ph-user-plus me-2 text-primary"></i>Register Patient Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('ar.patients.store') }}">
        @csrf
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Patient MRN / ID <span class="text-muted fw-normal">(Leave blank to auto-generate)</span></label>
            <input type="text" name="patient_mrn" class="form-control form-control-sm font-monospace" placeholder="e.g. MRN-2026-88190">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Patient Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control form-control-sm" placeholder="e.g. Maria Corazon Santos" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Admission Type</label>
              <select name="admission_type" class="form-select form-select-sm" required>
                <option value="Inpatient" selected>Inpatient</option>
                <option value="Outpatient">Outpatient</option>
                <option value="Emergency">Emergency</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">HMO Provider</label>
              <input type="text" name="hmo_provider" class="form-control form-control-sm" placeholder="e.g. Maxicare / Intellicare">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Contact Phone</label>
              <input type="text" name="phone" class="form-control form-control-sm" placeholder="e.g. +63 917 123 4567">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Email Address</label>
              <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. patient@email.com">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Home Address</label>
            <input type="text" name="address" class="form-control form-control-sm" placeholder="e.g. 123 Medical Ave, Ortigas Center, Pasig City">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Account</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
