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
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting vendor master list...');"><i class="ph ph-download-simple me-1"></i> Export Vendors</button>
      <button id="btnAddVendor" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addVendorModal"><i class="ph ph-plus me-1"></i> Add New Vendor</button>
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
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total AP Liabilities</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱1,240,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Avg Payment Terms</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Net 30 Days</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Tracked EWT Withholding</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱124,000.00</h4>
      </div>
    </div>
  </div>

  <!-- Vendors Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Category Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="vendorCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Supplier Category:</label>
          <select id="vendorCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Categories</option>
            <option value="pharmaceuticals">Pharmaceuticals</option>
            <option value="medical equipment">Medical Equipment</option>
            <option value="medical gases">Medical Gases</option>
            <option value="utilities & services">Utilities &amp; Services</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="vendorSearchInput" class="form-control form-control-sm" placeholder="Search vendor name, TIN, code...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="vendorTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Vendor Code</th>
              <th>Supplier Name</th>
              <th>Category</th>
              <th>TIN Number</th>
              <th>Payment Terms</th>
              <th>EWT Rate</th>
              <th class="text-end">Balance Due (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $vendors = [
                [
                  'code' => 'VEND-PHARM-01',
                  'name' => 'PharmaCorp Philippines',
                  'category' => 'Pharmaceuticals',
                  'tin' => '102-481-992-000',
                  'terms' => '2/10 Net 30',
                  'ewt' => '1% Goods EWT',
                  'balance' => '₱420,000.00',
                  'status' => 'Active',
                  'cat_badge' => 'bg-primary-subtle text-primary',
                  'desc' => 'Primary hospital contractor for bulk IV fluid solutions, ICU injectable antibiotics, emergency cardiac epinephrine, and seasonal influenza vaccines. Certified under FDA License No. CDRR-2026-9041.',
                  'contact' => 'Maria Santos (Key Account Director)',
                  'phone' => '+63 (02) 8842-1090',
                  'email' => 'accounts.payable@pharmacorp.ph',
                  'address' => 'PharmaCorp Hub, 45 Medical City Blvd, Ortigas Center, Pasig City',
                  'credit_limit' => '₱1,500,000.00'
                ],
                [
                  'code' => 'VEND-MED-02',
                  'name' => 'MedTech Diagnostics Inc',
                  'category' => 'Medical Equipment',
                  'tin' => '204-819-331-000',
                  'terms' => 'Net 60',
                  'ewt' => '2% Services EWT',
                  'balance' => '₱310,500.00',
                  'status' => 'Active',
                  'cat_badge' => 'bg-info-subtle text-info',
                  'desc' => 'Authorized distributor and service technician for MRI/CT scan contrast reagents, digital X-Ray film cassettes, ultrasound probe sanitizers, and diagnostic imaging calibration.',
                  'contact' => 'Engr. Roberto Cruz (Medical Devices Lead)',
                  'phone' => '+63 (02) 8631-4400',
                  'email' => 'service@medtechdiagnostics.ph',
                  'address' => 'Unit 1204 MedTech Tower, Chino Roces Ave, Makati City',
                  'credit_limit' => '₱2,000,000.00'
                ],
                [
                  'code' => 'VEND-GAS-03',
                  'name' => 'Linde Medical Gases Philippines',
                  'category' => 'Medical Gases',
                  'tin' => '301-992-114-000',
                  'terms' => 'Net 30',
                  'ewt' => '1% Goods EWT',
                  'balance' => '₱54,000.00',
                  'status' => 'Active',
                  'cat_badge' => 'bg-warning-subtle text-warning',
                  'desc' => 'High-purity medical oxygen cylinder refill services, ICU manifold piping maintenance, liquid nitrogen tanks, and nitrous oxide gas supply for operating theaters.',
                  'contact' => 'Dennis Villamin (Industrial Sales Representative)',
                  'phone' => '+63 (02) 8520-7711',
                  'email' => 'orders.ph@linde-med.com',
                  'address' => 'Linde Industrial Estate, Sta. Rosa-Tagaytay Road, Laguna',
                  'credit_limit' => '₱500,000.00'
                ],
                [
                  'code' => 'VEND-SURG-04',
                  'name' => 'Surgical Supplies & Implants Co.',
                  'category' => 'Medical Equipment',
                  'tin' => '405-112-990-000',
                  'terms' => 'Net 45',
                  'ewt' => '1% Goods EWT',
                  'balance' => '₱18,500.00',
                  'status' => 'Active',
                  'cat_badge' => 'bg-info-subtle text-info',
                  'desc' => 'Specialized titanium orthopedic screws, sterile surgical sutures, laparoscopic trocars, sterile drapes, and single-use surgical blade cartridges.',
                  'contact' => 'Dr. Arlene Reyes (Surgical Consultant Liaison)',
                  'phone' => '+63 (02) 8712-3090',
                  'email' => 'sales@surgicalsupplies.ph',
                  'address' => '88 BioTech Plaza, Fairview, Quezon City',
                  'credit_limit' => '₱800,000.00'
                ],
                [
                  'code' => 'VEND-UTIL-05',
                  'name' => 'Meralco Power Distribution',
                  'category' => 'Utilities & Services',
                  'tin' => '509-330-100-000',
                  'terms' => 'Net 15',
                  'ewt' => '2% Services EWT',
                  'balance' => '₱437,000.00',
                  'status' => 'Active',
                  'cat_badge' => 'bg-secondary-subtle text-secondary',
                  'desc' => 'Primary high-voltage electrical grid provider for 24/7 hospital emergency rooms, intensive care units, HVAC cooling towers, and facility infrastructure.',
                  'contact' => 'Key Accounts Enterprise Desk',
                  'phone' => '16211 (Corporate Meralco)',
                  'email' => 'enterprise@meralco.com.ph',
                  'address' => 'Meralco Center, Ortigas Avenue, Pasig City',
                  'credit_limit' => '₱3,000,000.00'
                ],
              ];
            @endphp

            @foreach($vendors as $v)
            <tr class="vendor-row" style="cursor: pointer;" data-category="{{ strtolower($v['category']) }}" onclick="openVendorDetailsModal({{ json_encode($v) }})">
              <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">{{ $v['code'] }}</span></td>
              <td><div class="fw-semibold text-dark">{{ $v['name'] }}</div></td>
              <td><span class="badge {{ $v['cat_badge'] }}">{{ $v['category'] }}</span></td>
              <td><span class="font-monospace fs-xs">{{ $v['tin'] }}</span></td>
              <td><span class="badge bg-light text-dark border">{{ $v['terms'] }}</span></td>
              <td><span class="badge bg-info-subtle text-info">{{ $v['ewt'] }}</span></td>
              <td class="text-end fw-bold text-danger">{{ $v['balance'] }}</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> {{ $v['status'] }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <div class="d-flex justify-content-end gap-1">
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Details" onclick="openVendorDetailsModal({{ json_encode($v) }})"><i class="ph ph-eye"></i></button>
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor" onclick="alert('Edit Vendor modal for {{ $v['code'] }}');"><i class="ph ph-pencil-simple"></i></button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="vendorSummaryText">Showing {{ count($vendors) }} Active Suppliers</span>
      <nav aria-label="Vendors Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Vendor Details (Clean & Executive Design) -->
<div class="modal fade" id="vendorDetailsModal" tabindex="-1" aria-labelledby="vendorDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <!-- Header -->
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailVendorCode">VEND-PHARM-01</span>
            <span class="badge bg-primary-subtle text-primary" id="detailVendorCategory">Pharmaceuticals</span>
            <span class="badge bg-success-subtle text-success" id="detailVendorStatus"><i class="ph ph-check"></i> Active</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailVendorName">PharmaCorp Philippines</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Key Financial Summary Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Balance Due</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailVendorBalance">₱420,000.00</h5>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Approved Credit Limit</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailVendorCreditLimit">₱1,500,000.00</h5>
            </div>
          </div>
          <div class="col-md-4">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Payment Terms</span>
              <h5 class="fw-bold text-primary mb-0" id="detailVendorTerms">2/10 Net 30</h5>
            </div>
          </div>
        </div>

        <!-- Supplier Overview & Description -->
        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-2 fs-xs text-uppercase"><i class="ph ph-info me-1 text-primary"></i> Supplies &amp; Services Overview</h6>
          <p class="small text-muted mb-0 lh-base" id="detailVendorDesc">Primary hospital contractor for bulk IV fluid solutions, ICU injectable antibiotics, emergency cardiac epinephrine, and seasonal influenza vaccines.</p>
        </div>

        <!-- Two-Column Master Info -->
        <div class="row g-3 mb-4">
          <!-- Tax & Financial Info -->
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
              <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-receipt me-1 text-primary"></i> Tax &amp; Master Data</h6>
              <div class="d-flex flex-column gap-2 fs-xs">
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">BIR TIN Number</span>
                  <span class="font-monospace fw-bold text-dark" id="detailVendorTin">102-481-992-000</span>
                </div>
                <div class="d-flex justify-content-between pt-1">
                  <span class="text-muted">BIR EWT Tax Rate</span>
                  <span class="badge bg-info-subtle text-info" id="detailVendorEwt">1% Goods EWT</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact & Corporate Info -->
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 h-100">
              <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-user-address me-1 text-primary"></i> Contact &amp; Location</h6>
              <div class="d-flex flex-column gap-2 fs-xs">
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">Representative</span>
                  <span class="fw-semibold text-dark" id="detailVendorContact">Maria Santos</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">Phone Number</span>
                  <span class="font-monospace text-dark" id="detailVendorPhone">+63 (02) 8842-1090</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2">
                  <span class="text-muted">Email Address</span>
                  <span class="text-primary font-monospace" id="detailVendorEmail">ap@pharmacorp.ph</span>
                </div>
                <div class="d-flex flex-column gap-1 pt-1">
                  <span class="text-muted">Business Address</span>
                  <span class="fw-medium text-dark" id="detailVendorAddress">45 Medical City Blvd, Pasig City</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Open Accounts Payable Vouchers -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-file-text me-1 text-primary"></i> Associated Open AP Vouchers</h6>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-xs">
              <thead class="table-light">
                <tr>
                  <th>Voucher Ref</th>
                  <th>Particulars</th>
                  <th class="text-end">Amount (₱)</th>
                  <th>Due Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="detailVouchersTbody">
                <tr>
                  <td><span class="font-monospace text-primary fw-bold">APV-2026-091</span></td>
                  <td>Bulk IV Fluids &amp; Antibiotics Delivery</td>
                  <td class="text-end fw-bold font-monospace">₱143,550.00</td>
                  <td>2026-08-25</td>
                  <td><span class="badge bg-warning-subtle text-warning">Pending Approval</span></td>
                </tr>
                <tr>
                  <td><span class="font-monospace text-primary fw-bold">APV-2026-078</span></td>
                  <td>Emergency ICU Oxygen &amp; Vaccines</td>
                  <td class="text-end fw-bold font-monospace">₱276,450.00</td>
                  <td>2026-09-02</td>
                  <td><span class="badge bg-info-subtle text-info">Authorized for Release</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ap.invoices') }}" class="btn btn-sm btn-primary"><i class="ph ph-plus me-1"></i> Create AP Voucher</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add New Vendor -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addVendorModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Register New Supplier Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="addVendorForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Vendor Code <span class="text-danger">*</span></label>
              <input type="text" id="modalVendorCode" class="form-control form-control-sm font-monospace" placeholder="e.g. VEND-PHARM-06" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Supplier Legal Name <span class="text-danger">*</span></label>
              <input type="text" id="modalVendorName" class="form-control form-control-sm" placeholder="e.g. B. Braun Medical Supplies Inc" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Supplier Category <span class="text-danger">*</span></label>
              <select id="modalVendorCategory" class="form-select form-select-sm" required>
                <option value="Pharmaceuticals">Pharmaceuticals</option>
                <option value="Medical Equipment">Medical Equipment</option>
                <option value="Medical Gases">Medical Gases</option>
                <option value="Utilities & Services">Utilities &amp; Services</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">TIN Number <span class="text-danger">*</span></label>
              <input type="text" id="modalVendorTin" class="form-control form-control-sm font-monospace" placeholder="e.g. 402-192-881-000" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Terms <span class="text-danger">*</span></label>
              <select id="modalVendorTerms" class="form-select form-select-sm" required>
                <option value="2/10 Net 30">2/10 Net 30</option>
                <option value="Net 30">Net 30</option>
                <option value="Net 45">Net 45</option>
                <option value="Net 60">Net 60</option>
                <option value="Net 15">Net 15</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">BIR EWT Tax Rate <span class="text-danger">*</span></label>
              <select id="modalVendorEwt" class="form-select form-select-sm" required>
                <option value="1% Goods EWT">1% Goods EWT</option>
                <option value="2% Services EWT">2% Services EWT</option>
                <option value="Exempt">Tax Exempt</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-semibold">Initial Balance Due (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalVendorBalance" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="0.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Register Supplier</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openVendorDetailsModal(vendor) {
  if (!vendor) return;

  document.getElementById('detailVendorCode').textContent = vendor.code || 'VEND-000';
  document.getElementById('detailVendorName').textContent = vendor.name || 'Supplier Name';
  document.getElementById('detailVendorCategory').textContent = vendor.category || 'General';
  document.getElementById('detailVendorDesc').textContent = vendor.desc || 'Supplier profile registered in the FMS Accounts Payable directory.';
  document.getElementById('detailVendorTin').textContent = vendor.tin || '-';
  document.getElementById('detailVendorTerms').textContent = vendor.terms || '-';
  document.getElementById('detailVendorEwt').textContent = vendor.ewt || '-';
  document.getElementById('detailVendorCreditLimit').textContent = vendor.credit_limit || '₱1,000,000.00';
  document.getElementById('detailVendorBalance').textContent = vendor.balance || '₱0.00';
  document.getElementById('detailVendorContact').textContent = vendor.contact || 'Key Account Manager';
  document.getElementById('detailVendorPhone').textContent = vendor.phone || '+63 (02) 8000-0000';
  document.getElementById('detailVendorEmail').textContent = vendor.email || 'ap@supplier.ph';
  document.getElementById('detailVendorAddress').textContent = vendor.address || 'Metro Manila, Philippines';

  const modalEl = document.getElementById('vendorDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const categorySelect = document.getElementById('vendorCategorySelect');
  const searchInput = document.getElementById('vendorSearchInput');
  const summaryText = document.getElementById('vendorSummaryText');
  const btnAddVendor = document.getElementById('btnAddVendor');

  if (btnAddVendor) {
    btnAddVendor.addEventListener('click', function() {
      const modalEl = document.getElementById('addVendorModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  function filterVendors() {
    const selectedCategory = categorySelect ? categorySelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.vendor-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-category') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCategory = !selectedCategory || rowCat === selectedCategory;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCategory && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Active Supplier${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noVendorsRow');
    const tbody = document.querySelector('#vendorTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noVendorsRow';
        emptyRow.innerHTML = `<td colspan="9" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No suppliers found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (categorySelect) categorySelect.addEventListener('change', filterVendors);
  if (searchInput) {
    searchInput.addEventListener('input', filterVendors);
    searchInput.addEventListener('keyup', filterVendors);
  }

  const addVendorForm = document.getElementById('addVendorForm');
  if (addVendorForm) {
    addVendorForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const codeVal = document.getElementById('modalVendorCode').value;
      const nameVal = document.getElementById('modalVendorName').value;
      const categoryVal = document.getElementById('modalVendorCategory').value;
      const tinVal = document.getElementById('modalVendorTin').value;
      const termsVal = document.getElementById('modalVendorTerms').value;
      const ewtVal = document.getElementById('modalVendorEwt').value;
      const rawBalance = parseFloat(document.getElementById('modalVendorBalance').value || 0);
      const formattedBalance = '₱' + rawBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      let catBadge = 'bg-primary-subtle text-primary';
      if (categoryVal === 'Medical Equipment') catBadge = 'bg-info-subtle text-info';
      else if (categoryVal === 'Medical Gases') catBadge = 'bg-warning-subtle text-warning';
      else if (categoryVal === 'Utilities & Services') catBadge = 'bg-secondary-subtle text-secondary';

      const vendorObj = {
        code: codeVal,
        name: nameVal,
        category: categoryVal,
        tin: tinVal,
        terms: termsVal,
        ewt: ewtVal,
        balance: formattedBalance,
        status: 'Active',
        cat_badge: catBadge,
        desc: 'Newly registered supplier profile in the FMS Accounts Payable directory.',
        contact: 'Primary Contact Person',
        phone: '+63 (02) 8000-0000',
        email: 'ap@supplier.ph',
        address: 'Metro Manila, Philippines',
        credit_limit: '₱1,000,000.00'
      };

      const tbody = document.querySelector('#vendorTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'vendor-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-category', categoryVal.toLowerCase());

        newRow.onclick = function() { openVendorDetailsModal(vendorObj); };

        newRow.innerHTML = `
          <td><span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1">${codeVal}</span></td>
          <td><div class="fw-semibold text-dark">${nameVal}</div></td>
          <td><span class="badge ${catBadge}">${categoryVal}</span></td>
          <td><span class="font-monospace fs-xs">${tinVal}</span></td>
          <td><span class="badge bg-light text-dark border">${termsVal}</span></td>
          <td><span class="badge bg-info-subtle text-info">${ewtVal}</span></td>
          <td class="text-end fw-bold text-danger">${formattedBalance}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Active</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <div class="d-flex justify-content-end gap-1">
              <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Supplier Details"><i class="ph ph-eye"></i></button>
              <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vendor"><i class="ph ph-pencil-simple"></i></button>
            </div>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Supplier Details"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(e) {
            e.stopPropagation();
            openVendorDetailsModal(vendorObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('addVendorModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      addVendorForm.reset();
      filterVendors();
    });
  }

  filterVendors();
});
</script>
@endpush
