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
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="alert('Exporting Journal Entry Log PDF...');"><i class="ph ph-file-arrow-down me-1"></i> Export Journal Log</button>
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
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($monthlyDebitTotal, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Credit Volume (Month)</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($monthlyCreditTotal, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Posted Journal Entries</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $postedCount }} {{ Str::plural('Entry', $postedCount) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Unposted Draft Entries</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $draftCount }} {{ Str::plural('Draft', $draftCount) }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label for="journalStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
          <select id="journalStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Posting Statuses</option>
            <option value="posted">Posted to Ledger</option>
            <option value="draft">Draft Entry</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2">
          <label for="journalTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Type:</label>
          <select id="journalTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 180px;">
            <option value="" selected>All Journal Types</option>
            <option value="general">General Journal</option>
            <option value="adjusting">Adjusting Entry</option>
            <option value="closing">Closing Entry</option>
          </select>
        </div>
        <div class="search-box ms-auto" style="width: 260px;">
          <i class="ph ph-magnifying-glass"></i>
          <input type="search" id="journalSearchInput" class="form-control form-control-sm" placeholder="Search entry ref, description...">
        </div>
      </div>
    </div>
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
            @forelse($entries ?? [] as $je)
            @php
              $ref       = $je->reference_number;
              $date      = $je->entry_date->format('Y-m-d');
              $title     = $je->description;
              $status    = ucfirst(strtolower($je->status));
              $type      = strtolower($je->type);
              $debitSum  = '₱' . number_format($je->lines->sum('debit'), 2);
              $creditSum = '₱' . number_format($je->lines->sum('credit'), 2);
              $jeData = [
                'ref'    => $ref,
                'date'   => $date,
                'title'  => $title,
                'debit'  => $debitSum,
                'credit' => $creditSum,
                'status' => $status,
                'type'   => $type,
                'badge'  => $status === 'Posted' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning',
              ];
            @endphp
            <tr class="journal-row" style="cursor: pointer;" data-status="{{ strtolower($status) }}" data-type="{{ strtolower($type) }}" onclick="openJournalDetailsModal({{ json_encode($jeData) }})">
              <td><span class="font-monospace text-primary fw-bold">{{ $ref }}</span></td>
              <td class="font-monospace fs-xs">{{ $date }}</td>
              <td>
                <div class="fw-semibold text-dark">{{ $title }}</div>
              </td>
              <td class="text-end text-success fw-bold font-monospace">{{ $debitSum }}</td>
              <td class="text-end text-danger fw-bold font-monospace">{{ $creditSum }}</td>
              <td><span class="badge {{ $jeData['badge'] }}"><i class="ph ph-check-circle me-1"></i> {{ $status }}</span></td>
              <td class="text-end" onclick="event.stopPropagation();">
                <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Journal Voucher" onclick="openJournalDetailsModal({{ json_encode($jeData) }})"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No journal entries posted in database.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs" id="journalSummaryText">Showing {{ count($entries ?? []) }} Journal Entries</span>
      <nav aria-label="Journal Pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Journal Details (Executive Design) -->
<div class="modal fade" id="journalDetailsModal" tabindex="-1" aria-labelledby="journalDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-white border-bottom p-4 pb-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-secondary-subtle text-secondary font-monospace px-2 py-1" id="detailJeRef">JE-2026-0045</span>
            <span class="badge bg-success-subtle text-success" id="detailJeStatus"><i class="ph ph-check-circle me-1"></i> Posted to Ledger</span>
          </div>
          <h4 class="modal-title fw-bold text-dark mb-0" id="detailJeTitle">Outpatient Lab Consultation &amp; Testing Revenue Settlement</h4>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light-subtle">
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Debit Amount</span>
              <h4 class="fw-bold text-success mb-0 font-monospace" id="detailJeDebit">₱350,000.00</h4>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-white border rounded-3 p-3 text-center">
              <span class="text-muted fs-xs text-uppercase fw-semibold d-block mb-1">Total Credit Amount</span>
              <h4 class="fw-bold text-danger mb-0 font-monospace" id="detailJeCredit">₱350,000.00</h4>
            </div>
          </div>
        </div>

        <div class="bg-white border rounded-3 p-3 mb-4">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-book-open me-1 text-primary"></i> Journal Entry Metadata</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">Journal Voucher Posting Date</span>
              <span class="font-monospace fw-bold text-dark" id="detailJeDate">2026-08-10</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">Double-Entry Accounting Rule</span>
              <span class="font-monospace text-primary fw-bold">Balanced Debit / Credit Double Entry</span>
            </div>
          </div>
        </div>

        <!-- Audit Trail & Segregation of Duties -->
        <div class="bg-white border rounded-3 p-3">
          <h6 class="fw-bold text-dark mb-3 fs-xs text-uppercase"><i class="ph ph-shield-check me-1 text-success"></i> Audit Trail &amp; General Ledger Verification</h6>
          <div class="d-flex flex-column gap-2 fs-xs">
            <div class="d-flex justify-content-between border-bottom pb-2">
              <span class="text-muted">General Ledger Posting Verification:</span>
              <span class="badge bg-success-subtle text-success"><i class="ph ph-check me-1"></i> Verified by Chief Accountant</span>
            </div>
            <div class="d-flex justify-content-between pt-1">
              <span class="text-muted">System Audit Stamp:</span>
              <span class="font-monospace text-muted">LOG-JE-2026-0045 | {{ date('Y-m-d H:i:s') }} PST</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white border-top p-3">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="alert('Exporting Journal Voucher PDF...');"><i class="ph ph-printer me-1"></i> Print Journal Voucher</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: New Journal Entry -->
<div class="modal fade" id="newJournalModal" tabindex="-1" aria-labelledby="newJournalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newJournalModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Post Double-Entry Journal Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="newJournalForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Journal Entry Date <span class="text-danger">*</span></label>
              <input type="date" id="modalEntryDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Journal Type <span class="text-danger">*</span></label>
              <select id="modalJournalType" class="form-select form-select-sm" required>
                <option value="General">General Journal</option>
                <option value="Adjusting">Adjusting Entry</option>
                <option value="Closing">Closing Entry</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Balanced Debit / Credit Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" id="modalAmount" step="0.01" min="0" class="form-control form-control-sm text-end font-monospace" placeholder="0.00" value="150000.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Debit Account <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" value="1010 - Cash on Hand - Main Vault" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Journal Explanation / Remarks <span class="text-danger">*</span></label>
              <input type="text" id="modalDescription" class="form-control form-control-sm" placeholder="e.g. Settlement of outpatient consultation fees..." required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Post Journal Entry</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openJournalDetailsModal(je) {
  if (!je) return;

  document.getElementById('detailJeTitle').textContent = je.title || 'Journal Description';
  document.getElementById('detailJeRef').textContent = je.ref || 'JE-000';
  document.getElementById('detailJeDate').textContent = je.date || '-';
  document.getElementById('detailJeDebit').textContent = je.debit || '₱0.00';
  document.getElementById('detailJeCredit').textContent = je.credit || '₱0.00';

  const statusEl = document.getElementById('detailJeStatus');
  if (statusEl) {
    statusEl.textContent = je.status;
    statusEl.className = 'badge ' + (je.badge || 'bg-success-subtle text-success');
  }

  const modalEl = document.getElementById('journalDetailsModal');
  if (modalEl && window.bootstrap) {
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('journalSearchInput');
  const statusSelect = document.getElementById('journalStatusSelect');
  const typeSelect = document.getElementById('journalTypeSelect');
  const summaryText = document.getElementById('journalSummaryText');
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

  function filterJournals() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const selectedType = typeSelect ? typeSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.journal-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowStatus = row.getAttribute('data-status') || '';
      const rowType = row.getAttribute('data-type') || '';
      const rowText = row.textContent.toLowerCase();

      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchType = !selectedType || rowType.includes(selectedType);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchStatus && matchType && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Journal Entr${visibleCount !== 1 ? 'ies' : 'y'}`;
    }

    let emptyRow = document.getElementById('noJournalRow');
    const tbody = document.querySelector('#journalTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noJournalRow';
        emptyRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted"><i class="ph ph-magnifying-glass fs-3 d-block mb-2"></i>No journal entries found matching the current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterJournals);
    searchInput.addEventListener('keyup', filterJournals);
  }
  if (statusSelect) statusSelect.addEventListener('change', filterJournals);
  if (typeSelect) typeSelect.addEventListener('change', filterJournals);

  const newJournalForm = document.getElementById('newJournalForm');
  if (newJournalForm) {
    newJournalForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const dateVal = document.getElementById('modalEntryDate').value;
      const typeVal = document.getElementById('modalJournalType').value;
      const descVal = document.getElementById('modalDescription').value;
      const rawAmount = parseFloat(document.getElementById('modalAmount').value || 0);
      const formattedAmount = '₱' + rawAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const nextRef = 'JE-2026-00' + Math.floor(46 + Math.random() * 50);

      const jeObj = {
        ref: nextRef,
        date: dateVal,
        title: descVal,
        debit: formattedAmount,
        credit: formattedAmount,
        status: 'Posted',
        type: typeVal.toLowerCase(),
        badge: 'bg-success-subtle text-success'
      };

      const tbody = document.querySelector('#journalTable tbody');
      if (tbody) {
        const newRow = document.createElement('tr');
        newRow.className = 'journal-row';
        newRow.style.cursor = 'pointer';
        newRow.setAttribute('data-status', 'posted');
        newRow.setAttribute('data-type', typeVal.toLowerCase());

        newRow.onclick = function() { openJournalDetailsModal(jeObj); };

        newRow.innerHTML = `
          <td><span class="font-monospace text-primary fw-bold">${nextRef}</span></td>
          <td class="font-monospace fs-xs">${dateVal}</td>
          <td><div class="fw-semibold text-dark">${descVal}</div></td>
          <td class="text-end text-success fw-bold font-monospace">${formattedAmount}</td>
          <td class="text-end text-danger fw-bold font-monospace">${formattedAmount}</td>
          <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Posted</span></td>
          <td class="text-end" onclick="event.stopPropagation();">
            <button class="btn btn-sm btn-icon btn-outline-secondary" title="View Journal Voucher"><i class="ph ph-eye"></i></button>
          </td>
        `;

        const eyeBtn = newRow.querySelector('button[title="View Journal Voucher"]');
        if (eyeBtn) {
          eyeBtn.onclick = function(ex) {
            ex.stopPropagation();
            openJournalDetailsModal(jeObj);
          };
        }

        tbody.insertBefore(newRow, tbody.firstChild);
      }

      const modalEl = document.getElementById('newJournalModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      if (modalInstance) modalInstance.hide();

      newJournalForm.reset();
      filterJournals();
    });
  }

  filterJournals();
});
</script>
@endpush
