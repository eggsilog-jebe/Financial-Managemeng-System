@extends('layouts.app')

@section('title', 'Budget Allocation - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'budget-allocation')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Budget Allocation</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Budget Allocation</h1>
      <p class="text-muted small mb-0">Distribution of approved hospital funds to cost centers and medical units.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Cost Center</th><th>Category</th><th class="text-end">Allocated Amount (₱)</th><th class="text-end">Encumbered (₱)</th><th class="text-end">Available Balance (₱)</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-medium">CC-101: Pharmacy &amp; Therapeutics</td>
            <td>Operational Supplies</td>
            <td class="text-end fw-bold">₱4,500,000.00</td>
            <td class="text-end text-warning">₱2,800,000.00</td>
            <td class="text-end text-success fw-bold">₱1,700,000.00</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
