@extends('layouts.app')

@section('title', 'Receivable Aging - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'ar-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Receivable</li>
          <li class="breadcrumb-item active">Receivable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Receivable Aging &amp; DSO Analytics</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print AR Aging</button>
      <button class="btn btn-primary btn-sm" type="button" onclick="alert('Exporting AR Aging PDF statement...');"><i class="ph ph-file-pdf me-1"></i> Export Aging PDF</button>
    </div>
  </div>

  <!-- KPI Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Days Sales Outstanding (DSO)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">42.5 Days</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">0-30 Days (Current)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱1,065,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">31-60 Days (Submitted)</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hourglass fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱390,200.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">61-90+ Days (High Risk)</span>
          <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i class="ph ph-warning fs-5"></i></span>
        </div>
        <h4 class="fw-bold text-dark mb-0">₱125,000.00</h4>
      </div>
    </div>
  </div>

  <!-- Aging Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <!-- Payor Filter Dropdown -->
        <div class="d-flex align-items-center gap-2">
          <label for="arPayorTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Payor Type:</label>
          <select id="arPayorTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 220px;">
            <option value="" selected>All Payor Types</option>
            <option value="government guarantor">Government Guarantor</option>
            <option value="commercial hmo">Commercial HMO</option>
            <option value="self-pay patient">Self-Pay Patient</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="search-box" style="width: 280px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="arAgingSearchInput" class="form-control form-control-sm" placeholder="Search payor name or type...">
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="arAgingTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Payor Name &amp; Guarantor</th>
              <th>Payor Type</th>
              <th class="text-end">0-30 Days (₱)</th>
              <th class="text-end">31-60 Days (₱)</th>
              <th class="text-end">61-90 Days (₱)</th>
              <th class="text-end">Over 90 Days (₱)</th>
              <th class="text-end">Total Uncollected (₱)</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @php
              $arAging = [
                [
                  'payor' => 'PhilHealth Insurance Corp',
                  'type' => 'Government Guarantor',
                  'badge' => 'bg-success-subtle text-success',
                  'c0_30' => '₱450,000.00',
                  'c31_60' => '₱250,000.00',
                  'c61_90' => '₱120,000.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱820,000.00'
                ],
                [
                  'payor' => 'Maxicare HMO Philippines',
                  'type' => 'Commercial HMO',
                  'badge' => 'bg-info-subtle text-info',
                  'c0_30' => '₱320,000.00',
                  'c31_60' => '₱80,000.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱400,000.00'
                ],
                [
                  'payor' => 'Intellicare HMO',
                  'type' => 'Commercial HMO',
                  'badge' => 'bg-info-subtle text-info',
                  'c0_30' => '₱185,000.00',
                  'c31_60' => '₱45,200.00',
                  'c61_90' => '₱0.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱230,200.00'
                ],
                [
                  'payor' => 'Inpatient Self-Pay Ward Accounts',
                  'type' => 'Self-Pay Patient',
                  'badge' => 'bg-primary-subtle text-primary',
                  'c0_30' => '₱110,000.00',
                  'c31_60' => '₱15,000.00',
                  'c61_90' => '₱5,000.00',
                  'c90_plus' => '₱0.00',
                  'total' => '₱130,000.00'
                ],
              ];
            @endphp

            @foreach($arAging as $a)
            <tr class="ar-aging-row" style="cursor: pointer;" data-type="{{ strtolower($a['type']) }}" onclick="openArAgingDetailsModal({{ json_encode($a) }})">
              <td class="fw-semibold text-dark">{{ $a['payor'] }}</td>
              <td><span class="badge {{ $a['badge'] }}">{{ $a['type'] }}</span></td>
              <td class="text-end @if($a['c0_30'] !== '₱0.00') text-success fw-semibold @else text-muted @endif font-monospace">{{ $a['c0_30'] }}</td>
              <td class="text-end @if($a['c31_60'] !== '₱0.00') text-warning fw-semibold @else text-muted @endif font-monospace">{{ $a['c31_60'] }}</td>
              <td class="text-end @if($a['c61_90'] !== '₱0.00') text-danger fw-semibold @else text-muted @endif font-monospace">{{ $a['c61_90'] }}</td>
              <td class="text-end @if($a['c90_plus'] !== '₱0.00') text-danger fw-bold @else text-muted @endif font-monospace">{{ $a['c90_plus'] }}</td>
              <td class="text-end fw-bold text-dark font-monospace">{{ $a['total'] }}</td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Aging Details" onclick="openArAgingDetailsModal({{ json_encode($a) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light font-monospace fw-bold">
            <tr>
              <td colspan="2" class="text-end">Total AR Outstanding:</td>
              <td class="text-end text-success">₱1,065,000.00</td>
              <td class="text-end text-warning">₱390,200.00</td>
              <td class="text-end text-danger">₱125,000.00</td>
              <td class="text-end text-muted">₱0.00</td>
              <td class="text-end text-primary">₱1,580,200.00</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: In-Depth AR Aging Details (Executive Design) -->
<div class="modal fade" id="arAgingDetailsModal" tabindex="-1" aria-labelledby="arAgingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary" id="detailArType">Government Guarantor</span>
            <span class="badge bg-success-subtle text-success"><i class="ph ph-check"></i> Account Current</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailArPayor">PhilHealth Insurance Corp</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <!-- Aging Buckets Grid -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">0-30 Days</span>
              <h5 class="fw-bold text-success mb-0 font-monospace" id="detailArC030">₱450,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">31-60 Days</span>
              <h5 class="fw-bold text-warning mb-0 font-monospace" id="detailArC3160">₱250,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">61-90 Days</span>
              <h5 class="fw-bold text-danger mb-0 font-monospace" id="detailArC6190">₱120,000.00</h5>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Over 90 Days</span>
              <h5 class="fw-bold text-dark mb-0 font-monospace" id="detailArC90Plus">₱0.00</h5>
            </div>
          </div>
        </div>

        <!-- Total Outstanding Card -->
        <div class="bg-white border rounded-3 p-3 mb-4 text-center">
          <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Uncollected AR Claim Pool</span>
          <h3 class="fw-bold text-dark mb-0 font-monospace" id="detailArTotal">₱820,000.00</h3>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <a href="{{ route('ar.statements') }}" class="btn btn-sm btn-primary"><i class="ph ph-file-text me-1"></i> Generate Payor Statement</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openArAgingDetailsModal(a) {
  if (!a) return;

  document.getElementById('detailArPayor').textContent = a.payor || 'Payor Name';
  document.getElementById('detailArType').textContent = a.type || 'Payor Type';
  document.getElementById('detailArC030').textContent = a.c0_30 || '₱0.00';
  document.getElementById('detailArC3160').textContent = a.c31_60 || '₱0.00';
  document.getElementById('detailArC6190').textContent = a.c61_90 || '₱0.00';
  document.getElementById('detailArC90Plus').textContent = a.c90_plus || '₱0.00';
  document.getElementById('detailArTotal').textContent = a.total || '₱0.00';

  const modalEl = document.getElementById('arAgingDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const payorTypeSelect = document.getElementById('arPayorTypeSelect');
  const searchInput = document.getElementById('arAgingSearchInput');

  function filterArAging() {
    const selectedType = payorTypeSelect ? payorTypeSelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.ar-aging-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowType = row.getAttribute('data-type') || '';
      const rowText = row.textContent.toLowerCase();

      const matchType = !selectedType || rowType.includes(selectedType);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchType && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    let emptyRow = document.getElementById('noArAgingRow');
    const tbody = document.querySelector('#arAgingTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noArAgingRow';
        emptyRow.innerHTML = `<td colspan="8" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No receivable aging records found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (payorTypeSelect) payorTypeSelect.addEventListener('change', filterArAging);
  if (searchInput) {
    searchInput.addEventListener('input', filterArAging);
    searchInput.addEventListener('keyup', filterArAging);
  }

  filterArAging();
});
</script>
@endpush
