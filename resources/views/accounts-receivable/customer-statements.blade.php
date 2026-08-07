@extends('layouts.app')

@section('title', 'Customer Statements - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'statements')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Customer Statements</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Customer &amp; HMO Statements</h1>
      <p class="text-muted small mb-0">Periodic statement summaries issued to health maintenance organizations and insurance payors.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Statement ID</th><th>Payor</th><th>Statement Period</th><th class="text-end">Balance Forward (₱)</th><th>Action</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">STM-2026-08</span></td>
            <td>PhilHealth Insurance Corp</td>
            <td>July 01 - July 31, 2026</td>
            <td class="text-end fw-bold">₱820,000.00</td>
            <td><button class="btn btn-outline-secondary btn-sm"><i class="ph ph-printer me-1"></i> Print Statement</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
