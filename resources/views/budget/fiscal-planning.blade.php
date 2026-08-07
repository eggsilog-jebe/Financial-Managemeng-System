@extends('layouts.app')

@section('title', 'Fiscal Year Planning - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'fiscal-planning')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Fiscal Year Planning</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Fiscal Year Planning (FY 2026-2027)</h1>
      <p class="text-muted small mb-0">High-level hospital operational and capital expenditure planning target setting.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Create Plan Draft</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Plan Name</th><th>Fiscal Period</th><th class="text-end">Revenue Target (₱)</th><th class="text-end">Expense Budget (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">FY 2026 Approved Operating Master Plan</td>
            <td>Jan 01, 2026 - Dec 31, 2026</td>
            <td class="text-end text-success fw-bold">₱25,000,000.00</td>
            <td class="text-end text-danger fw-bold">₱18,500,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Active Master Budget</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
