@extends('layouts.app')

@section('title', 'Withholding Tax - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'withholding-tax')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Withholding Tax</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Withholding Tax Certificates (Form 2307 / 2306)</h1>
      <p class="text-muted small mb-0">Creditable withholding tax certificates generated for consultant physicians and suppliers.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Issue 2307 Certificate</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Cert No</th><th>Payee Name</th><th>TIN Number</th><th class="text-end">Gross Income (₱)</th><th class="text-end">Tax Withheld (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">C2307-2026-881</span></td>
            <td>Dr. Roberto Gomez (Visiting Consultant)</td>
            <td>102-391-441-000</td>
            <td class="text-end">₱120,000.00</td>
            <td class="text-end text-danger fw-bold">₱12,000.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
