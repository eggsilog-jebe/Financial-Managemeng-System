@extends('layouts.app')

@section('title', 'Cashier Desk - Collection Management | FMS')
@section('module', 'collection')
@section('page', 'cashier-desk')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Collection Management</li>
          <li class="breadcrumb-item active">Cashier Desk</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Cashier Desk Terminal</h1>
      <p class="text-muted small mb-0">POS station shift balances for Inpatient, ER, Outpatient, and Pharmacy cashiers.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Terminal ID</th><th>Station Name</th><th>Cashier Name</th><th class="text-end">Cash Drawer Total (₱)</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">TERM-01</span></td>
            <td>Main Discharge Desk</td>
            <td>Anna Reyes</td>
            <td class="text-end fw-semibold">₱45,200.00</td>
            <td><span class="badge bg-success-subtle text-success">Open Shift</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
