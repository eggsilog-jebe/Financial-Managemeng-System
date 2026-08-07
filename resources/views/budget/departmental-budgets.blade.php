@extends('layouts.app')

@section('title', 'Departmental Budgets - Budget Management | FMS')
@section('module', 'budget')
@section('page', 'departmental-budgets')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Budget Management</li>
          <li class="breadcrumb-item active">Departmental Budgets</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Departmental Budgets</h1>
      <p class="text-muted small mb-0">Detailed breakdown of operational spending budgets assigned to hospital heads of department.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Department</th><th>Head of Dept</th><th class="text-end">Annual Cap (₱)</th><th class="text-end">YTD Spent (₱)</th><th>Burn Rate</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-medium">Cardiology &amp; ICU</td>
            <td>Dr. Alejandro Santos</td>
            <td class="text-end fw-bold">₱3,200,000.00</td>
            <td class="text-end text-primary">₱1,850,000.00</td>
            <td>
              <div class="progress" style="height: 6px; width: 120px;">
                <div class="progress-bar bg-info" style="width: 57.8%;"></div>
              </div>
              <span class="fs-xs text-muted">57.8%</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
