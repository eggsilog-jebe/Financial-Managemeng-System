@extends('layouts.app')

@section('title', 'Ledger Books - General Ledger | FMS')
@section('module', 'finance')
@section('page', 'ledger-books')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active" aria-current="page">Ledger Books</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Ledger Books</h1>
      <p class="text-muted small mb-0">Detailed transaction history and financial log per hospital department.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Ledger</button>
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export CSV</button>
    </div>
  </div>

  <!-- Department Selector Header -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row align-items-center g-3">
        <div class="col-md-5">
          <label class="form-label small font-weight-bold mb-1">Select Hospital Department</label>
          <select class="form-select form-select-sm">
            <option value="pharmacy" selected>Pharmacy Department</option>
            <option value="laboratory">Laboratory &amp; Diagnostics Unit</option>
            <option value="wards">Inpatient Wards</option>
            <option value="surgery">Surgery &amp; Operating Room</option>
            <option value="hr">HR &amp; Administration</option>
            <option value="er">Emergency Room (ER)</option>
            <option value="icu">ICU &amp; Cardiology Unit</option>
            <option value="outpatient">Outpatient Clinic</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small font-weight-bold mb-1">Date Range</label>
          <input type="text" class="form-control form-control-sm" value="Aug 01, 2026 - Aug 07, 2026">
        </div>
        <div class="col-md-4 text-md-end">
          <span class="text-muted small d-block">Department Net Ledger Balance</span>
          <h3 class="fw-bold text-dark mb-0">₱3,420,000.00</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Ledger Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-0 p-3 pb-0 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0">Transactions Log for <span class="text-primary">Pharmacy Department</span></h6>
      <span class="badge bg-secondary-subtle text-secondary fs-xs">Cost Center Ref: DEPT-PHARM-01</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">Date</th>
              <th scope="col">Ref / Voucher</th>
              <th scope="col">Department / Particulars</th>
              <th scope="col" class="text-end">Debit (₱)</th>
              <th scope="col" class="text-end">Credit (₱)</th>
              <th scope="col" class="text-end">Running Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-secondary">
              <td colspan="3" class="fw-bold fs-xs">Beginning Balance (Aug 01, 2026)</td>
              <td class="text-end fw-bold">—</td>
              <td class="text-end fw-bold">—</td>
              <td class="text-end fw-bold">₱3,150,000.00</td>
            </tr>
            <tr>
              <td>2026-08-01</td>
              <td><span class="font-monospace text-primary">COL-2026-088</span></td>
              <td>Pharmacy Store Revenue Collection - Outpatient Prescriptions</td>
              <td class="text-end text-success fw-semibold">₱450,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end fw-semibold">₱3,600,000.00</td>
            </tr>
            <tr>
              <td>2026-08-02</td>
              <td><span class="font-monospace text-primary">DISB-2026-042</span></td>
              <td>Pharmacy - PharmaCorp IV Solutions &amp; Antibiotics Bulk Restock</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-danger fw-semibold">₱120,000.00</td>
              <td class="text-end fw-semibold">₱3,480,000.00</td>
            </tr>
            <tr>
              <td>2026-08-04</td>
              <td><span class="font-monospace text-primary">DISB-2026-045</span></td>
              <td>Pharmacy - Cold Storage Refrigeration System Maintenance</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-danger fw-semibold">₱60,000.00</td>
              <td class="text-end fw-semibold">₱3,420,000.00</td>
            </tr>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="3" class="text-end">Department Totals:</td>
              <td class="text-end text-success">₱450,000.00</td>
              <td class="text-end text-danger">₱180,000.00</td>
              <td class="text-end text-primary">₱3,420,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
