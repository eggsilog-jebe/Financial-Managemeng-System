@extends('layouts.app')

@section('title', 'Tax Returns & Filing - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-returns')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Tax Management</li>
          <li class="breadcrumb-item active">Tax Returns &amp; Filing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Tax Returns &amp; Government Filings</h1>
      <p class="text-muted small mb-0">Monthly and quarterly statutory returns (BIR Form 2550M/Q, Form 1601EQ, Corporate Income Tax).</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Form Code</th><th>Description</th><th>Tax Period</th><th class="text-end">Tax Payable (₱)</th><th>Filing Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">FORM 2550Q</span></td>
            <td>Quarterly Value Added Tax Return</td>
            <td>Q2 2026</td>
            <td class="text-end fw-bold">₱215,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Filed &amp; Paid</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
