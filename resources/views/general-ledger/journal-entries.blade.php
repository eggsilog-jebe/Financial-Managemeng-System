@extends('layouts.app')

@section('title', 'Journal Entries - General Ledger | FMS')
@section('module', 'gl')
@section('page', 'journal-entries')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">General Ledger</li>
          <li class="breadcrumb-item active">Journal Entries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Double-Entry Journal Entries</h1>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-file-arrow-down me-1"></i> Export Journal Log</button>
      <button id="btnNewJournal" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newJournalModal"><i class="ph ph-plus-circle me-1"></i> New Journal Entry</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Debit Volume (Month)</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,850,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Volume (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱14,850,000.00</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Posted Journal Entries</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">142 Entries</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unposted Draft Entries</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">2 Drafts</h4>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0" id="searchBtnIcon" style="cursor: pointer;" title="Click to Search"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" id="journalSearchInput" class="form-control bg-light border-start-0" placeholder="Search Entry Ref, Description, or Account...">
          </div>
        </div>
        <div class="col-md-3">
          <select id="journalStatusSelect" class="form-select form-select-sm bg-light">
            <option value="">All Posting Statuses</option>
            <option value="posted">Posted to Ledger</option>
            <option value="draft">Draft Entry</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="journalTypeSelect" class="form-select form-select-sm bg-light">
            <option value="">All Journal Types</option>
            <option value="general">General Journal</option>
            <option value="adjusting">Adjusting Entry</option>
            <option value="closing">Closing Entry</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="journalTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Entry Ref</th>
              <th>Date</th>
              <th>Journal Description</th>
              <th class="text-end">Debit Amount (₱)</th>
              <th class="text-end">Credit Amount (₱)</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @php
              $entries = [
                [
                  'ref' => 'JE-2026-0045',
                  'date' => '2026-08-10',
                  'title' => 'Outpatient Lab Consultation & Testing Revenue Settlement',
                  'debit' => '₱350,000.00',
                  'credit' => '₱350,000.00',
                  'status' => 'Posted',
                  'type' => 'general',
                  'badge' => 'bg-success-subtle text-success',
                  'icon' => 'ph-check-circle'
                ],
                [
                  'ref' => 'JE-2026-0044',
                  'date' => '2026-08-09',
                  'title' => 'Bi-Weekly Medical Nursing & Administrative Staff Payroll Adjustment',
                  'debit' => '₱2,450,000.00',
                  'credit' => '₱2,450,000.00',
                  'status' => 'Posted',
                  'type' => 'general',
                  'badge' => 'bg-success-subtle text-success',
                  'icon' => 'ph-check-circle'
                ],
                [
                  'ref' => 'JE-2026-0043',
                  'date' => '2026-08-08',
                  'title' => 'Surgical Gloves & Oxygen Tank Emergency Stock Purchase',
                  'debit' => '₱85,400.00',
                  'credit' => '₱85,400.00',
                  'status' => 'Posted',
                  'type' => 'general',
                  'badge' => 'bg-success-subtle text-success',
                  'icon' => 'ph-check-circle'
                ],
                [
                  'ref' => 'JE-2026-0042',
                  'date' => '2026-08-07',
                  'title' => 'Pharmacy Inventory Bulk Replenishment Payout',
                  'debit' => '₱120,000.00',
                  'credit' => '₱120,000.00',
                  'status' => 'Posted',
                  'type' => 'general',
                  'badge' => 'bg-success-subtle text-success',
                  'icon' => 'ph-check-circle'
                ],
                [
                  'ref' => 'JE-2026-0041',
                  'date' => '2026-08-06',
                  'title' => 'MRI Scanner & Radiology Depreciation Expense (Monthly)',
                  'debit' => '₱125,000.00',
                  'credit' => '₱125,000.00',
                  'status' => 'Posted',
                  'type' => 'adjusting',
                  'badge' => 'bg-success-subtle text-success',
                  'icon' => 'ph-check-circle'
                ],
                [
                  'ref' => 'JE-2026-0040',
                  'date' => '2026-08-05',
                  'title' => 'Hospital Facility Utility & Electric Power Generator Settlement',
                  'debit' => '₱215,600.00',
                  'credit' => '₱215,600.00',
                  'status' => 'Draft',
                  'type' => 'general',
                  'badge' => 'bg-warning-subtle text-warning',
                  'icon' => 'ph-clock'
                ],
                [
                  'ref' => 'JE-2026-0039',
                  'date' => '2026-07-31',
                  'title' => 'Fiscal Period Revenue & Expense Account Closing Entry',
                  'debit' => '₱5,240,000.00',
                  'credit' => '₱5,240,000.00',
                  'status' => 'Posted',
                  'type' => 'closing',
                  'badge' => 'bg-info-subtle text-info',
                  'icon' => 'ph-check-circle'
                ],
              ];
            @endphp

            @foreach($entries as $entry)
            <tr class="journal-row" data-status="{{ strtolower($entry['status']) }}" data-type="{{ strtolower($entry['type']) }}">
              <td><span class="font-monospace text-primary fw-bold">{{ $entry['ref'] }}</span></td>
              <td>{{ $entry['date'] }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $entry['title'] }}</div>
              </td>
              <td class="text-end text-success fw-bold font-monospace">{{ $entry['debit'] }}</td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $entry['credit'] }}</td>
              <td><span class="badge {{ $entry['badge'] }}"><i class="ph {{ $entry['icon'] }} me-1"></i> {{ $entry['status'] }}</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-light border p-1" title="View Double-Entry Voucher"><i class="ph ph-eye"></i></button>
                <button class="btn btn-sm btn-light border p-1" title="Print Voucher"><i class="ph ph-printer"></i></button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Double-Entry Journal Modal -->
<div class="modal fade" id="newJournalModal" tabindex="-1" aria-labelledby="newJournalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newJournalModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>New Double-Entry Journal Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="newJournalForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Entry Date <span class="text-danger">*</span></label>
              <input type="date" id="modalEntryDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Journal Type <span class="text-danger">*</span></label>
              <select id="modalJournalType" class="form-select form-select-sm" required>
                <option value="general">General Operating Journal</option>
                <option value="adjusting">Month-End Adjusting Entry</option>
                <option value="closing">Fiscal Closing Entry</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Debit Account <span class="text-danger">*</span></label>
              <select id="modalDebitAccount" class="form-select form-select-sm" required>
                <option value="1300">1300 - Pharmacy Inventory Asset</option>
                <option value="5100">5100 - Doctor Salaries Expense</option>
                <option value="5200">5200 - Medical Equipment Depreciation</option>
                <option value="4010">4010 - Inpatient Care Revenue</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Credit Account <span class="text-danger">*</span></label>
              <select id="modalCreditAccount" class="form-select form-select-sm" required>
                <option value="1010">1010 - Metrobank Operating Cash</option>
                <option value="2100">2100 - Accounts Payable Liability</option>
                <option value="1590">1590 - Accumulated Depreciation</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transaction Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalAmount" step="0.01" min="0.01" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Source Document / Reference</label>
              <input type="text" id="modalReference" class="form-control form-control-sm" placeholder="e.g. Inv #99201 or Depr Schedule">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Journal Description / Particulars <span class="text-danger">*</span></label>
              <textarea id="modalDescription" class="form-control form-control-sm" rows="2" placeholder="Record purchase of emergency room antibiotics..." required></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Double-Entry</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const statusSelect = document.getElementById('journalStatusSelect');
  const typeSelect = document.getElementById('journalTypeSelect');
  const searchInput = document.getElementById('journalSearchInput');
  const filterBtn = document.getElementById('journalFilterBtn');
  const searchIcon = document.getElementById('searchBtnIcon');

  function filterJournals() {
    const statusVal = statusSelect ? statusSelect.value.toLowerCase() : '';
    const typeVal = typeSelect ? typeSelect.value.toLowerCase() : '';
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.journal-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowStatus = row.getAttribute('data-status') || ''; // posted, draft
      const rowType = row.getAttribute('data-type') || ''; // general, adjusting, closing
      const rowText = row.textContent.toLowerCase();

      const matchStatus = !statusVal || rowStatus === statusVal;
      const matchType = !typeVal || rowType === typeVal;
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchStatus && matchType && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    let emptyRow = document.getElementById('noJournalsRow');
    const tbody = document.querySelector('#journalTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noJournalsRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No journal entries found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (statusSelect) statusSelect.addEventListener('change', filterJournals);
  if (typeSelect) typeSelect.addEventListener('change', filterJournals);
  if (searchInput) {
    searchInput.addEventListener('input', filterJournals);
    searchInput.addEventListener('keyup', filterJournals);
  }
  const btnNewJournal = document.getElementById('btnNewJournal');
  if (btnNewJournal) {
    btnNewJournal.addEventListener('click', function() {
      const modalEl = document.getElementById('newJournalModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
      }
    });
  }

  // New Journal Entry Modal Submit Handler
  const newJournalForm = document.getElementById('newJournalForm');
  if (newJournalForm) {
    newJournalForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const dateVal = document.getElementById('modalEntryDate').value;
      const typeVal = document.getElementById('modalJournalType').value;
      const rawAmount = parseFloat(document.getElementById('modalAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const descVal = document.getElementById('modalDescription').value;

      // Auto-generate reference code
      const nextRef = 'JE-2026-00' + (Math.floor(Math.random() * 90) + 46);

      const tbody = document.querySelector('#journalTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'journal-row';
        newRow.setAttribute('data-status', 'posted');
        newRow.setAttribute('data-type', typeVal.toLowerCase());

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td>${dateVal}</td>
          <td><div class="fw-semibold text-dark">${descVal}</div></td>
          <td class="text-end text-success fw-bold font-monospace">${formattedAmount}</td>
          <td class="text-end text-danger fw-bold font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Posted</span></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light border p-1" title="View Double-Entry Voucher"><i class="ph ph-eye"></i></button>
            <button class="btn btn-sm btn-light border p-1" title="Print Voucher"><i class="ph ph-printer"></i></button>
          </td>
        `;

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      // Close Modal
      const modalEl = document.getElementById('newJournalModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      // Reset Form
      newJournalForm.reset();

      // Re-run filter to incorporate new entry
      filterJournals();
    });
  }

  // Initial filter run
  filterJournals();
});
</script>
@endpush
