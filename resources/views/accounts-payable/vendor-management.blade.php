@extends('layouts.app')

@section('title', 'Vendor Management - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'vendors')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Vendor Management</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Vendor Directory</h1>
      <p class="text-muted small mb-0">Master records of pharmaceutical suppliers, medical equipment vendors, and utility providers.</p>
    </div>
    <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add Vendor</button>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Vendor Code</th>
            <th>Vendor / Supplier Name</th>
            <th>Category</th>
            <th>TIN Number</th>
            <th class="text-end">Balance Due (₱)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="font-monospace text-primary">VEND-001</span></td>
            <td>PharmaCorp Philippines</td>
            <td>Pharmaceuticals</td>
            <td>102-481-992-000</td>
            <td class="text-end fw-semibold">₱420,000.00</td>
            <td><span class="badge bg-success-subtle text-success">Active</span></td>
          </tr>
          <tr>
            <td><span class="font-monospace text-primary">VEND-002</span></td>
            <td>MedTech Diagnostics Inc</td>
            <td>Medical Equipment</td>
            <td>204-819-331-000</td>
            <td class="text-end fw-semibold">₱310,500.00</td>
            <td><span class="badge bg-success-subtle text-success">Active</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
