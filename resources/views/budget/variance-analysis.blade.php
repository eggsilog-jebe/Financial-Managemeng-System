@extends('layouts.app')

@section('title', 'Variance Analysis - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'variance-analysis')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Variance Analysis</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget vs. Actual Variance Analysis</h1>
      <p class="text-muted small mb-0">Comparison of projected budget allocations against actual hospital spending and revenue.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Category</th><th class="text-end">Budgeted (₱)</th><th class="text-end">Actual (₱)</th><th class="text-end">Variance (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-medium">Pharmacy Medical Supplies</td>
            <td class="text-end">₱2,500,000.00</td>
            <td class="text-end">₱2,280,000.00</td>
            <td class="text-end text-success fw-bold">+₱220,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Favorable (Under Budget)</span></td>
          </tr>
          <tr>
            <td class="fw-medium">Facility Utility &amp; Power Fees</td>
            <td class="text-end">₱600,000.00</td>
            <td class="text-end">₱645,000.00</td>
            <td class="text-end text-danger fw-bold">-₱45,000.00</td>
            <td><span class="badge bg-danger-subtle text-danger">Unfavorable (Over Budget)</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
