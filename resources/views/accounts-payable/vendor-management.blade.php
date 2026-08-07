@extends('layouts.app')

@section('title', 'Vendor Management - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'vendors')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Vendor Directory</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Vendor Directory &amp; Supplier Master</h1>
      <p class="text-muted small mb-0">Master profiles of pharmaceutical suppliers, medical device manufacturers, and utility providers.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm"><i class="ph ph-download-simple me-1"></i> Export Vendors</button>
      <button class="btn btn-primary btn-sm"><i class="ph ph-plus me-1"></i> Add New Vendor</button>
    </div>
  </div>

  <!-- Summary Cards Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Active Vendors</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">48 Suppliers</h4>
        <span class="fs-xs text-muted">Across 6 Operational Categories</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total AP Liabilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,240,000.00</h4>
        <span class="fs-xs text-muted">18 Pending AP Vouchers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Avg Payment Terms</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Net 30 Days</h4>
        <span class="fs-xs text-muted">2/10 Early Pay Discount Available</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Tracked EWT Withholding</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱124,000.00</h4>
        <span class="fs-xs text-muted">Form 2307 Eligible</span>
      </div>
    </div>
  </div>

  <!-- Vendors Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <ul class="nav nav-pills flex-grow-1">
          <li class="nav-item"><button class="nav-link active btn-sm py-1 px-3 me-1 fw-semibold">All Suppliers (48)</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Pharmaceuticals</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Medical Equipment</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Medical Gases</button></li>
          <li class="nav-item"><button class="nav-link btn-sm py-1 px-3 me-1">Utilities &amp; Services</button></li>
        </ul>
        <div class="search-box" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" class="form-control form-control-sm" placeholder="Search vendor name, TIN, code...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Vendor Code</th>
              <th>Supplier Name &amp; Category</th>
              <th>TIN Number</th>
              <th>Payment Terms</th>
              <th>EWT Rate</th>
              <th class="text-end">Balance Due (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">VEND-PHARM-01</span></td>
              <td>
                <div class="fw-semibold text-dark">PharmaCorp Philippines</div>
                <div class="text-muted fs-xs">Bulk IV Solutions, Antibiotics &amp; Vaccine Supplies</div>
              </td>
              <td><span class="font-monospace fs-xs">102-481-992-000</span></td>
              <td><span class="badge bg-light text-dark border">2/10 Net 30</span></td>
              <td><span class="badge bg-info-subtle text-info">1% Goods EWT</span></td>
              <td class="text-end fw-bold text-danger">₱420,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Profile"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor"><i class="ph ph-pencil-simple"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">VEND-MED-02</span></td>
              <td>
                <div class="fw-semibold text-dark">MedTech Diagnostics Inc</div>
                <div class="text-muted fs-xs">MRI &amp; CT Scan Reagents, X-Ray Film Consumables</div>
              </td>
              <td><span class="font-monospace fs-xs">204-819-331-000</span></td>
              <td><span class="badge bg-light text-dark border">Net 60</span></td>
              <td><span class="badge bg-info-subtle text-info">2% Services EWT</span></td>
              <td class="text-end fw-bold text-danger">₱310,500.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Profile"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor"><i class="ph ph-pencil-simple"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">VEND-GAS-03</span></td>
              <td>
                <div class="fw-semibold text-dark">Linde Medical Gases Philippines</div>
                <div class="text-muted fs-xs">ICU Oxygen Cylinders, Nitrous Oxide &amp; Liquid Nitrogen</div>
              </td>
              <td><span class="font-monospace fs-xs">301-992-114-000</span></td>
              <td><span class="badge bg-light text-dark border">Net 30</span></td>
              <td><span class="badge bg-info-subtle text-info">1% Goods EWT</span></td>
              <td class="text-end fw-bold text-dark">₱54,000.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Profile"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor"><i class="ph ph-pencil-simple"></i></button>
              </td>
            </tr>
            <tr>
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">VEND-SURG-04</span></td>
              <td>
                <div class="fw-semibold text-dark">Surgical Supplies &amp; Implants Co.</div>
                <div class="text-muted fs-xs">Orthopedic Screws, Surgical Sutures, Sterilized Gloves</div>
              </td>
              <td><span class="font-monospace fs-xs">405-112-990-000</span></td>
              <td><span class="badge bg-light text-dark border">Net 45</span></td>
              <td><span class="badge bg-info-subtle text-info">1% Goods EWT</span></td>
              <td class="text-end fw-bold text-dark">₱18,500.00</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Profile"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor"><i class="ph ph-pencil-simple"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing 4 of 48 Active Vendors</span>
      <nav aria-label="Vendors Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>
@endsection
