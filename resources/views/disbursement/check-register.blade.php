@extends('layouts.app')

@section('title', 'Check Register - Disbursement | FMS')
@section('module', 'disbursement')
@section('page', 'check-register')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Disbursement</li>
          <li class="breadcrumb-item active">Check Register</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Check Register</h1>
      <p class="text-muted small mb-0">Official log of physical checks written, signed, issued, or cleared.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Check No</th><th>Issue Date</th><th>Payee</th><th>Bank Account</th><th class="text-end">Amount (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">CHK-004812</span></td>
            <td>2026-08-04</td>
            <td>PharmaCorp Philippines</td>
            <td>Metrobank Operating #1020</td>
            <td class="text-end fw-semibold">₱120,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Cleared</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
