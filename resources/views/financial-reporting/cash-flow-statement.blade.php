@extends('layouts.app')

@section('title', 'Statement of Cash Flows - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'cash-flow-statement')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Cash Flow Statement</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Statement of Cash Flows</h1>
      <p class="text-muted small mb-0">Analysis of cash generated and utilized across Operating, Investing, and Financing activities.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Activity Classification</th><th class="text-end">Net Cash Provided / (Used) (₱)</th></tr>
        </thead>
        <tbody>
          <tr><td class="fw-semibold">Cash Flow from Operating Activities</td><td class="text-end text-success fw-bold">+₱4,200,000.00</td></tr>
          <tr><td class="fw-semibold">Cash Flow from Investing Activities (Equipment Purchasing)</td><td class="text-end text-danger fw-bold">-₱1,500,000.00</td></tr>
          <tr><td class="fw-semibold">Cash Flow from Financing Activities (Loan Amortization)</td><td class="text-end text-muted fw-bold">-₱500,000.00</td></tr>
          <tr class="table-primary fw-bold"><td>Net Increase in Hospital Cash &amp; Equivalents</td><td class="text-end text-primary">+₱2,200,000.00</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
