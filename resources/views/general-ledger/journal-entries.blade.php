@extends('layouts.app')

@section('title', 'Journal Entries - General Ledger | FMS')
@section('module', 'finance')
@section('page', 'journal-entries')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Journal Entries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Journal Entries</h1>
      <p class="text-muted small mb-0">Record and review day-to-day double-entry accounting transactions.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> New Journal Entry</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Entry Ref</th>
            <th>Date</th>
            <th>Description</th>
            <th class="text-end">Debit (₱)</th>
            <th class="text-end">Credit (₱)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">JE-2026-0041</span></td>
            <td>2026-08-07</td>
            <td>Pharmacy Inventory Bulk Replenishment Payout</td>
            <td class="text-end text-success fw-semibold">₱120,000.00</td>
            <td class="text-end text-danger fw-semibold">₱120,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Posted</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
