@extends('layouts.app')

@section('title', 'Patient & Customer Accounts - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'customers')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Patient Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Patient &amp; HMO Accounts</h1>
      <p class="text-muted small mb-0">Master financial accounts for admitted patients, outpatient accounts, and HMO guarantors.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add Account</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Account No</th><th>Patient / HMO Guarantor</th><th>Account Type</th><th>Insurance Policy</th><th class="text-end">Balance Due (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">AR-PAT-881</span></td>
            <td>Juan Dela Cruz (Patient #10429)</td>
            <td>Inpatient Ward A</td>
            <td>Maxicare HMO (POL-9921)</td>
            <td class="text-end fw-semibold">₱24,500.00</td>
            <td><span class="badge bg-warning-subtle text-warning">Active Inpatient</span></td>
          </tr>
          <tr>
            <td><span class="font-monospace text-primary">AR-HMO-004</span></td>
            <td>PhilHealth Insurance Corp</td>
            <td>Government HMO</td>
            <td>National Coverage</td>
            <td class="text-end fw-semibold">₱820,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Active Payor</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
