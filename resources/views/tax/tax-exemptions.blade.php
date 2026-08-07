@extends('layouts.app')

@section('title', 'Tax Exemptions - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-exemptions')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Exemptions</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Exemptions Register</h1>
      <p class="text-muted small mb-0">Record of VAT-exempt prescription medicine sales, statutory patient exemptions, and non-profit hospital grants.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Exemption Class</th><th>Legal Basis / Authority</th><th class="text-end">YTD Exempt Gross (₱)</th><th class="text-end">Tax Saved (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">RA 11534 (CREATE Act - Essential Medicines)</td>
            <td>BIR Revenue Regulation 04-2021</td>
            <td class="text-end">₱1,450,000.00</td>
            <td class="text-end text-success fw-bold">₱174,000.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
