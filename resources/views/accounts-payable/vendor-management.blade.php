@extends('layouts.app')

@section('title', 'Vendor Management - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'vendors')

@section('content')
<div class="container-fluid p-4">
  <!-- Alerts -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-check-circle fs-4 me-2"></i>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning-circle fs-4 me-2"></i>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

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
      <p class="text-muted fs-xs mb-0">Register and manage accredited hospital suppliers, pharmaceutical vendors, BIR Tax Identification Numbers (TIN), and credit terms.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['Supplier/Vendor Management', 'PSM (Procurement)']" 
          description="Syncs accredited suppliers, TINs, and payment credit terms with Hospital Procurement." 
      />
      <button id="btnAddVendor" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addVendorModal">
        <i class="ph ph-plus me-1"></i> Register New Vendor
      </button>
    </div>
  </div>

  <!-- Summary Cards Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3" title="Active suppliers accredited to deliver supplies and services">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Active Accredited Suppliers</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-buildings fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $totalActiveVendors }} Vendor{{ $totalActiveVendors !== 1 ? 's' : '' }}</h4>
        <span class="fs-xs text-muted">Ready for Purchase Orders</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3" title="Total unpaid balance owed to all suppliers">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Unpaid AP Balance</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-trend-down fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) $totalApLiability, 2) }}</h4>
        <span class="fs-xs text-muted">Total amount owed to suppliers</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3" title="Standard payment credit window allowed by vendors">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Standard Credit Terms</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Net 30 Days</h4>
        <span class="fs-xs text-muted">Average due date after delivery</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3" title="Expanded Withholding Tax withheld from supplier payouts for BIR">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Total Tax Withheld (EWT)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-percent fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) $totalEwt, 2) }}</h4>
        <span class="fs-xs text-muted">Tracked for BIR Form 1601-EQ</span>
      </div>
    </div>
  </div>

  <!-- Vendors Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('ap.vendors') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="statusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select id="statusSelect" name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>

        <div class="search-box" style="width: 280px;">
          <input type="search" name="search" class="form-control form-control-sm" placeholder="Search vendor name, TIN, code..." value="{{ request('search') }}">
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="vendorTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Vendor Code</th>
              <th>Supplier Legal Name</th>
              <th>TIN</th>
              <th>Contact Person</th>
              <th>Phone / Email</th>
              <th>Payment Terms</th>
              <th class="text-end">Balance Due (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vendors ?? [] as $v)
            @php
              $balance = $v->purchaseBills->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'APPROVED'])->sum(fn ($b) => $b->balance_due);
              $vData = [
                'id'       => $v->id,
                'code'     => $v->code,
                'name'     => $v->name,
                'tin'      => $v->tin ?? 'N/A',
                'contact'  => $v->contact_person ?? 'N/A',
                'phone'    => $v->phone ?? 'N/A',
                'email'    => $v->email ?? 'N/A',
                'terms'    => "Net {$v->payment_terms_days} Days",
                'balance'  => '₱' . number_format((float) $balance, 2),
                'status'   => $v->status,
                'is_active'=> $v->is_active,
              ];
            @endphp
            <tr class="vendor-row">
              <td><span class="font-monospace fw-bold text-primary">{{ $v->code }}</span></td>
              <td>
                <div class="fw-bold text-dark">{{ $v->name }}</div>
              </td>
              <td><span class="font-monospace text-muted">{{ $v->tin ?? 'N/A' }}</span></td>
              <td>{{ $v->contact_person ?? '—' }}</td>
              <td>
                <div class="fs-xs text-dark">{{ $v->phone ?? '—' }}</div>
                <div class="fs-xs text-muted">{{ $v->email ?? '' }}</div>
              </td>
              <td><span class="badge bg-light text-dark border">Net {{ $v->payment_terms_days }} Days</span></td>
              <td class="text-end font-monospace fw-bold {{ $balance > 0 ? 'text-danger' : 'text-muted' }}">₱{{ number_format((float) $balance, 2) }}</td>
              <td>
                <span class="badge {{ $v->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                  <i class="ph ph-check-circle me-1"></i> {{ $v->status }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Details" onclick="openVendorDetailsModal({{ json_encode($vData) }})">
                    <i class="ph ph-eye"></i>
                  </button>
                  <form method="POST" action="{{ route('ap.vendors.toggle-status', $v->id) }}" class="d-inline" onsubmit="return confirm('Toggle status for {{ $v->name }}?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-icon {{ $v->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $v->is_active ? 'Deactivate Vendor' : 'Activate Vendor' }}">
                      <i class="ph {{ $v->is_active ? 'ph-pause-circle' : 'ph-play-circle' }}"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No vendors found in masterfile.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="vendorSummaryText">Total: {{ count($vendors ?? []) }} Registered Supplier{{ count($vendors ?? []) !== 1 ? 's' : '' }}</span>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Vendor Details -->
<div class="modal fade" id="vendorDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailVendorCode">VEND-001</span>
            <span class="badge bg-success-subtle text-success" id="detailVendorStatus"><i class="ph ph-check"></i> Active</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailVendorName">Supplier Name</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current Open Balance Due</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailVendorBalance">₱0.00</h5>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Payment Terms</span>
              <h5 class="fw-bold text-primary mb-0" id="detailVendorTerms">Net 30 Days</h5>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-user-address me-1 text-primary"></i> Master Data &amp; Contact Info</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">BIR TIN Number</span>
              <span class="font-monospace fw-bold text-dark" id="detailVendorTin">-</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Contact Representative</span>
              <span class="fw-semibold text-dark" id="detailVendorContact">-</span>
            </div>
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Phone Number</span>
              <span class="font-monospace text-dark" id="detailVendorPhone">-</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Email Address</span>
              <span class="text-primary font-monospace" id="detailVendorEmail">-</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ap.purchase-bills') }}" class="btn btn-sm btn-primary"><i class="ph ph-file-plus me-1"></i> New Purchase Bill</a>
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
        <form method="POST" action="{{ route('ap.vendors.store') }}">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Vendor Code (Auto-generated if blank)</label>
              <input type="text" name="vendor_code" class="form-control form-control-sm font-monospace" placeholder="e.g. VND-0025">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Supplier Legal Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. B. Braun Medical Supplies Inc" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">TIN Number <span class="text-danger">*</span></label>
              <input type="text" name="tin" class="form-control form-control-sm font-monospace" placeholder="e.g. 402-192-881-000" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Payment Terms (Days) <span class="text-danger">*</span></label>
              <input type="number" name="payment_terms_days" class="form-control form-control-sm" value="30" min="0" max="365" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Contact Person</label>
              <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="e.g. Juan dela Cruz">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Phone Number</label>
              <input type="text" name="phone" class="form-control form-control-sm" placeholder="e.g. +63 (02) 8842-1090">
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-semibold">Email Address</label>
              <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. billing@supplier.ph">
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
  document.getElementById('detailVendorTin').textContent = vendor.tin || '-';
  document.getElementById('detailVendorTerms').textContent = vendor.terms || '-';
  document.getElementById('detailVendorBalance').textContent = vendor.balance || '₱0.00';
  document.getElementById('detailVendorContact').textContent = vendor.contact || '-';
  document.getElementById('detailVendorPhone').textContent = vendor.phone || '-';
  document.getElementById('detailVendorEmail').textContent = vendor.email || '-';

  const modalEl = document.getElementById('vendorDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}
</script>
@endpush
