@extends('layouts.app')

@section('title', 'Customer Statements - Accounts Receivable | FMS')
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
          <li class="breadcrumb-item active">Customer Statements</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">HMO &amp; Corporate Statements</h1>
      <p class="text-muted small mb-0">Compiled periodic billing statement summaries issued to insurance companies, HMO partners, and corporate accounts.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Generate Batch Statement</button>
  </div>

  <!-- Statements Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Statement ID</th>
              <th>Payor / HMO Partner</th>
              <th>Statement Period</th>
              <th>Batch Admissions Count</th>
              <th class="text-end">Total Batch Receivable (₱)</th>
              <th>Statement Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">STM-2026-08</span></td>
              <td class="fw-semibold text-dark">PhilHealth Insurance Corp</td>
              <td>July 01 - July 31, 2026</td>
              <td><span class="badge bg-light text-dark border">148 Inpatient Claims</span></td>
              <td class="text-end fw-bold text-dark">₱820,000.00</td>
              <td><span class="badge bg-primary-subtle text-primary"><i class="ph ph-paper-plane"></i> Transmitted to PhilHealth</span></td>
              <td class="text-end">
                <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Statement</button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">STM-2026-09</span></td>
              <td class="fw-semibold text-dark">Maxicare Healthcare Corp</td>
              <td>July 01 - July 31, 2026</td>
              <td><span class="badge bg-light text-dark border">62 HMO Patients</span></td>
              <td class="text-end fw-bold text-dark">₱400,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Remittance Confirmed</span></td>
              <td class="text-end">
                <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Statement</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
