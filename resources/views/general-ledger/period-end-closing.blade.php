@extends('layouts.app')

@section('title', 'Period End Closing - General Ledger | FMS')
@section('module', 'finance')
@section('page', 'period-closing')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Period End Closing</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Period End Closing</h1>
      <p class="text-muted small mb-0">Month-end and fiscal year-end accounting period locking and automated rollover.</p>
    </div>
    <button class="btn btn-warning btn-sm"><i class="ph ph-lock me-1"></i> Lock Period</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Fiscal Period</th>
            <th>Closing Status</th>
            <th class="text-end">Depreciation Expense (₱)</th>
            <th class="text-end">Retained Earnings (₱)</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">July 2026 Monthly Closing</td>
            <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Closed</span></td>
            <td class="text-end">₱450,000.00</td>
            <td class="text-end fw-bold">₱1,820,000.00</td>
            <td><button class="btn btn-outline-secondary btn-sm" disabled>Locked</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
