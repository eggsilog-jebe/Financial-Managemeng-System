@extends('layouts.app')

@section('title', 'Payable Aging - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'payable-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable Aging Analysis</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Aging Report</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Aging Analysis PDF exported!');"><i class="ph ph-file-pdf me-1"></i> Export Aging PDF</button>
    </div>
  </div>

  <!-- Aging Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Current (0-30 Days)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-1">₱680,200.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">31-60 Days</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hourglass fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-1">₱185,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">61-90 Days</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-1">₱45,300.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Over 90 Days</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-1">₱0.00</h4>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Category Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="agingCategorySelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Supplier Category:</label>
          <select id="agingCategorySelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Categories</option>
            <option value="pharmaceuticals">Pharmaceuticals</option>
            <option value="medical devices">Medical Devices</option>
            <option value="medical gases">Medical Gases</option>
            <option value="surgical consumables">Surgical Consumables</option>
            <option value="utilities">Utilities</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="agingSearchInput" class="form-control form-control-sm" placeholder="Search vendor name or category...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="agingTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Vendor Name</th>
              <th>Category</th>
              <th class="text-end">Current (0-30) (₱)</th>
              <th class="text-end">31-60 Days (₱)</th>
              <th class="text-end">61-90 Days (₱)</th>
              <th class="text-end">Over 90 Days (₱)</th>
              <th class="text-end">Total Payable (₱)</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @php
              $agingRecords = [
                [
                  'vendor' => 'PharmaCorp Philippines',
                  'category' => 'Pharmaceuticals',
                  'c0_30' => '₱320,000.00',
                  'c31_60' => '₱100,000.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱420,000.00',
                  'cat_badge' => 'bg-primary-subtle text-primary'
                ],
                [
                  'vendor' => 'MedTech Diagnostics Inc',
                  'category' => 'Medical Devices',
                  'c0_30' => '₱225,200.00',
                  'c31_60' => '₱40,000.00',
                  'c61_90' => '₱45,300.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱310,500.00',
                  'cat_badge' => 'bg-info-subtle text-info'
                ],
                [
                  'vendor' => 'Linde Medical Gases Philippines',
                  'category' => 'Medical Gases',
                  'c0_30' => '₱54,000.00',
                  'c31_60' => '₱0.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱54,000.00',
                  'cat_badge' => 'bg-warning-subtle text-warning'
                ],
                [
                  'vendor' => 'Surgical Supplies & Implants Co.',
                  'category' => 'Surgical Consumables',
                  'c0_30' => '₱18,500.00',
                  'c31_60' => '₱0.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱18,500.00',
                  'cat_badge' => 'bg-info-subtle text-info'
                ],
                [
                  'vendor' => 'Meralco Power Distribution',
                  'category' => 'Utilities',
                  'c0_30' => '₱392,000.00',
                  'c31_60' => '₱45,000.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱437,000.00',
                  'cat_badge' => 'bg-secondary-subtle text-secondary'
                ],
              ];
            @endphp

            @foreach($agingRecords as $a)
            <tr class="aging-row" style="cursor: pointer;" data-category="{{ strtolower($a['category']) }}" onclick="openAgingDetailsModal({{ json_encode($a) }})">
              <td class="fw-semibold text-dark">{{ $a['vendor'] }}</td>
              <td><span class="badge {{ $a['cat_badge'] }}">{{ $a['category'] }}</span></td>
              <td class="text-end @if($a['c0_30'] !== '₱0.00') text-success fw-semibold @else text-muted @endif font-monospace">{{ $a['c0_30'] }}</td>
              <td class="text-end @if($a['c31_60'] !== '₱0.00') text-warning fw-semibold @else text-muted @endif font-monospace">{{ $a['c31_60'] }}</td>
              <td class="text-end @if($a['c61_90'] !== '₱0.00') text-danger fw-semibold @else text-muted @endif font-monospace">{{ $a['c61_90'] }}</td>
              <td class="text-end @if($a['c90_plus'] !== '₱0.00') text-danger fw-bold @else text-muted @endif font-monospace">{{ $a['c90_plus'] }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $a['total'] }}</td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Aging Details" onclick="openAgingDetailsModal({{ json_encode($a) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light font-monospace fw-bold">
            <tr>
              <td colspan="2" class="text-end">Summary Totals:</td>
              <td class="text-end text-success">₱1,009,700.00</td>
              <td class="text-end text-warning">₱185,000.00</td>
              <td class="text-end text-danger">₱45,300.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-primary">₱1,240,000.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Aging Details (Executive Design) -->
<div class="modal fade" id="agingDetailsModal" tabindex="-1" aria-labelledby="agingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary" id="detailAgingCategory">Pharmaceuticals</span>
            <span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Account Current</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailAgingVendor">PharmaCorp Philippines</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Aging Buckets Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Current (0-30)</span>
              <h5 class="fw-bold text-success mb-0 font-monospace" id="detailC030">₱320,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">31-60 Days</span>
              <h5 class="fw-bold text-warning mb-0 font-monospace" id="detailC3160">₱100,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">61-90 Days</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailC6190">₱0.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Over 90 Days</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailC90Plus">₱0.00</h5>
            </div>
          </div>
        </div>

        <!-- Total Outstanding Card -->
        <div class="bg-white border rounded-3 p-3 mb-4 text-center">
          <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Outstanding AP Liability</span>
          <h3 class="fw-bold text-dark mb-0 font-monospace" id="detailTotalPayable">₱420,000.00</h3>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ap.ap-approvals') }}" class="btn btn-sm btn-primary"><i class="ph ph-shield-check me-1"></i> Proceed to Approvals Queue</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openAgingDetailsModal(a) {
  if (!a) return;

  document.getElementById('detailAgingVendor').textContent = a.vendor || 'Supplier Name';
  document.getElementById('detailAgingCategory').textContent = a.category || 'Category';
  document.getElementById('detailC030').textContent = a.c0_30 || '₱0.00';
  document.getElementById('detailC3160').textContent = a.c31_60 || '₱0.00';
  document.getElementById('detailC6190').textContent = a.c61_90 || '₱0.00';
  document.getElementById('detailC90Plus').textContent = a.c90_plus || '₱0.00';
  document.getElementById('detailTotalPayable').textContent = a.total || '₱0.00';

  const modalEl = document.getElementById('agingDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const categorySelect = document.getElementById('agingCategorySelect');
  const searchInput = document.getElementById('agingSearchInput');

  function filterAging() {
    const selectedCategory = categorySelect ? categorySelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.aging-row');
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

    let emptyRow = document.getElementById('noAgingRow');
    const tbody = document.querySelector('#agingTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAgingRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No aging records found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (categorySelect) categorySelect.addEventListener('change', filterAging);
  if (searchInput) {
    searchInput.addEventListener('input', filterAging);
    searchInput.addEventListener('keyup', filterAging);
  }

  filterAging();
});
</script>
@endpush
