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
      <h1 class="h3 mb-0 font-weight-bold text-dark">Double-Entry Journal Entries</h1>
      <p class="text-muted fs-xs mb-0">Record, verify, post, and reverse GAAP/IFRS balanced transactions in the hospital transaction ledger.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Subledgers (AP, AR, Cash, Disbursement, Collection)']" 
          description="Central double-entry posting engine and general journal register." 
      />
      <a href="{{ route('gl.ledger-books.export') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-arrow-down me-1"></i> Export Master GL
      </a>
      <button id="btnNewJournal" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newJournalModal">
        <i class="ph ph-plus-circle me-1"></i> New Journal Entry
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <i class="ph ph-warning-circle me-1"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 fs-sm" role="alert">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Monthly Debit Volume</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrow-up-right fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($monthlyDebitTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Monthly Credit Volume</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-arrow-down-left fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format($monthlyCreditTotal ?? 0, 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Posted to Ledger</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-check-circle fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $postedCount ?? 0 }} {{ Str::plural('Entry', $postedCount ?? 0) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Draft / Pending Entries</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-clock fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $draftCount ?? 0 }} {{ Str::plural('Draft', $draftCount ?? 0) }}</h4>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3">
      <form method="GET" action="{{ route('gl.journal-entries') }}">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <label for="journalStatusSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap"><i class="ph ph-funnel me-1"></i> Status:</label>
            <select name="status" id="journalStatusSelect" class="form-select form-select-sm bg-light" style="min-width: 160px;" onchange="this.form.submit()">
              <option value="" {{ empty($selectedStatus) ? 'selected' : '' }}>All Statuses</option>
              <option value="POSTED" {{ ($selectedStatus ?? '') === 'POSTED' ? 'selected' : '' }}>POSTED</option>
              <option value="DRAFT" {{ ($selectedStatus ?? '') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
              <option value="REVERSED" {{ ($selectedStatus ?? '') === 'REVERSED' ? 'selected' : '' }}>REVERSED</option>
            </select>
          </div>
          <div class="d-flex align-items-center gap-2">
            <label for="journalTypeSelect" class="form-label mb-0 fs-xs text-muted fw-semibold text-nowrap">Type:</label>
            <select name="type" id="journalTypeSelect" class="form-select form-select-sm bg-light" style="min-width: 160px;" onchange="this.form.submit()">
              <option value="" {{ empty($selectedType) ? 'selected' : '' }}>All Types</option>
              <option value="GENERAL" {{ ($selectedType ?? '') === 'GENERAL' ? 'selected' : '' }}>General Journal</option>
              <option value="ADJUSTING" {{ ($selectedType ?? '') === 'ADJUSTING' ? 'selected' : '' }}>Adjusting Entry</option>
              <option value="CLOSING" {{ ($selectedType ?? '') === 'CLOSING' ? 'selected' : '' }}>Closing Entry</option>
            </select>
          </div>
          <div class="search-box ms-auto" style="width: 260px;">
            <i class="ph ph-magnifying-glass"></i>
            <input type="search" name="q" id="journalSearchInput" class="form-control form-control-sm" placeholder="Search entry ref, description..." value="{{ $search ?? '' }}">
          </div>
        </div>
      </form>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="journalTable" class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;"></th>
              <th>Entry Ref #</th>
              <th>Date</th>
              <th>Type</th>
              <th>Narrative / Description</th>
              <th class="text-end">Total Debit (₱)</th>
              <th class="text-end">Total Credit (₱)</th>
              <th>Status</th>
              <th>Created By</th>
              <th class="text-end" style="width: 160px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($entries as $entry)
            @php
              $totalDebit = (float) $entry->lines->sum('debit');
              $totalCredit = (float) $entry->lines->sum('credit');
              $statusClass = match($entry->status) {
                'POSTED'   => 'bg-success-subtle text-success',
                'DRAFT'    => 'bg-warning-subtle text-warning',
                'REVERSED' => 'bg-danger-subtle text-danger',
                default    => 'bg-secondary-subtle text-secondary',
              };
              $collapseId = 'linesCollapse-' . $entry->id;
            @endphp
            <!-- Main Row -->
            <tr class="align-middle">
              <td>
                <button class="btn btn-sm btn-icon btn-light border-0 py-0" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}" title="Expand/Collapse Lines">
                  <i class="ph ph-caret-down"></i>
                </button>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-dark font-monospace fs-xs px-2 py-1">{{ $entry->reference_number }}</span>
              </td>
              <td><span class="fs-xs font-monospace text-muted">{{ $entry->entry_date->format('Y-m-d') }}</span></td>
              <td><span class="badge bg-light text-dark border fs-xs">{{ $entry->type }}</span></td>
              <td>
                <div class="fw-semibold text-dark fs-sm">{{ $entry->description }}</div>
                @if($entry->reversed_by_entry_id && $entry->reversedByEntry)
                  <span class="fs-xs text-danger d-block">
                    <i class="ph ph-arrow-u-down-left me-1"></i>Reversed by {{ $entry->reversedByEntry->reference_number }}
                  </span>
                @endif
              </td>
              <td class="text-end fw-bold text-dark font-monospace fs-sm">₱{{ number_format($totalDebit, 2) }}</td>
              <td class="text-end fw-bold text-dark font-monospace fs-sm">₱{{ number_format($totalCredit, 2) }}</td>
              <td>
                <span class="badge {{ $statusClass }} font-monospace fs-xs px-2 py-1">
                  {{ $entry->status }}
                </span>
              </td>
              <td><span class="fs-xs text-muted">{{ $entry->creator?->name ?? 'System User' }}</span></td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  @if($entry->status === 'DRAFT')
                    <form action="{{ route('gl.journal-entries.post', $entry->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success py-1 px-2" title="Post to General Ledger">
                        <i class="ph ph-check me-1"></i> Post
                      </button>
                    </form>
                  @elseif($entry->status === 'POSTED')
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" title="Reverse Journal Entry" onclick="openReverseModal({{ $entry->id }}, '{{ $entry->reference_number }}')">
                      <i class="ph ph-arrow-u-down-left me-1"></i> Reverse
                    </button>
                  @else
                    <span class="badge bg-secondary-subtle text-secondary fs-xs">VOIDED</span>
                  @endif
                </div>
              </td>
            </tr>

            <!-- Nested Debit/Credit Lines (Expandable Accordion) -->
            <tr class="p-0 border-0">
              <td colspan="10" class="p-0 border-0">
                <div class="collapse" id="{{ $collapseId }}">
                  <div class="p-3 bg-light-subtle border-start border-4 border-primary ms-3 me-3 my-2 rounded-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-bold fs-xs text-uppercase text-secondary">
                        <i class="ph ph-list-numbers me-1 text-primary"></i> Journal Entry Lines Breakdown (#{{ $entry->reference_number }})
                      </span>
                      <span class="fs-xs text-muted">Posted: {{ $entry->posted_at ? $entry->posted_at->format('Y-m-d H:i') : 'Pending Post' }}</span>
                    </div>
                    <table class="table table-sm table-bordered bg-white mb-0 fs-xs">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 120px;">Account Code</th>
                          <th>Account Title</th>
                          <th>Line Memo</th>
                          <th class="text-end" style="width: 140px;">Debit (₱)</th>
                          <th class="text-end" style="width: 140px;">Credit (₱)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($entry->lines as $line)
                        <tr>
                          <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $line->account->code ?? 'N/A' }}</span></td>
                          <td class="fw-semibold text-dark">{{ $line->account->name ?? 'Unknown Account' }}</td>
                          <td class="text-muted">{{ $line->memo ?? '-' }}</td>
                          <td class="text-end font-monospace {{ (float)$line->debit > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                            {{ (float)$line->debit > 0 ? '₱' . number_format((float)$line->debit, 2) : '-' }}
                          </td>
                          <td class="text-end font-monospace {{ (float)$line->credit > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                            {{ (float)$line->credit > 0 ? '₱' . number_format((float)$line->credit, 2) : '-' }}
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot class="table-light fw-bold">
                        <tr>
                          <td colspan="3" class="text-end">Balance Invariance Assertion:</td>
                          <td class="text-end font-monospace text-success">₱{{ number_format($totalDebit, 2) }}</td>
                          <td class="text-end font-monospace text-success">₱{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="ph ph-receipt-x fs-2 d-block mb-2 text-secondary"></i>
                No journal entries found matching criteria.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $entries->count() }} of {{ $entries->total() }} entries</span>
      {{ $entries->links() }}
    </div>
  </div>
</div>

<!-- Modal: New Manual Journal Entry Builder (Dynamic Repeater + Real-Time BCMath Invariance Counter) -->
<div class="modal fade" id="newJournalModal" tabindex="-1" aria-labelledby="newJournalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-primary text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="newJournalModalLabel"><i class="ph ph-receipt me-2"></i>Create Manual Journal Entry</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('gl.journal-entries.store') }}" method="POST" id="journalEntryForm">
        @csrf
        <div class="modal-body p-4">
          <!-- Header Information -->
          <div class="row g-3 mb-4 p-3 bg-light-subtle rounded-3 border">
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Entry Date <span class="text-danger">*</span></label>
              <input type="date" name="entry_date" id="entryDateInput" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
              <div class="form-text fs-xs">Must fall in an OPEN fiscal period.</div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Journal Type <span class="text-danger">*</span></label>
              <select name="type" class="form-select form-select-sm" required>
                <option value="GENERAL">GENERAL</option>
                <option value="ADJUSTING">ADJUSTING</option>
                <option value="CLOSING">CLOSING</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Description / Narrative <span class="text-danger">*</span></label>
              <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Monthly Accrual of Biomedical Oxygen Supplies" required>
            </div>
          </div>

          <!-- Line Items Repeater -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0 text-dark fs-sm text-uppercase"><i class="ph ph-list-plus me-1 text-primary"></i> Debit &amp; Credit Line Items</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddLineBtn" onclick="addJournalLineRow()">
              <i class="ph ph-plus me-1"></i> Add Line
            </button>
          </div>

          <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle mb-0" id="linesTable">
              <thead class="table-light">
                <tr>
                  <th style="width: 32%;">General Ledger Account <span class="text-danger">*</span></th>
                  <th style="width: 28%;">Line Memo / Reference</th>
                  <th style="width: 18%;" class="text-end">Debit (₱)</th>
                  <th style="width: 18%;" class="text-end">Credit (₱)</th>
                  <th style="width: 4%;"></th>
                </tr>
              </thead>
              <tbody id="linesTableBody">
                <!-- Line Row 1 (Debit default) -->
                <tr class="line-row">
                  <td>
                    <select name="lines[0][account_id]" class="form-select form-select-sm account-selector" required>
                      <option value="" disabled selected>Select Account...</option>
                      @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->category }})</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="text" name="lines[0][memo]" class="form-control form-control-sm" placeholder="Line memo..."></td>
                  <td><input type="number" step="0.01" min="0" name="lines[0][debit]" class="form-control form-control-sm text-end debit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
                  <td><input type="number" step="0.01" min="0" name="lines[0][credit]" class="form-control form-control-sm text-end credit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
                  <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeJournalLine(this)"><i class="ph ph-trash"></i></button></td>
                </tr>

                <!-- Line Row 2 (Credit default) -->
                <tr class="line-row">
                  <td>
                    <select name="lines[1][account_id]" class="form-select form-select-sm account-selector" required>
                      <option value="" disabled selected>Select Account...</option>
                      @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->category }})</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="text" name="lines[1][memo]" class="form-control form-control-sm" placeholder="Line memo..."></td>
                  <td><input type="number" step="0.01" min="0" name="lines[1][debit]" class="form-control form-control-sm text-end debit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
                  <td><input type="number" step="0.01" min="0" name="lines[1][credit]" class="form-control form-control-sm text-end credit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
                  <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeJournalLine(this)"><i class="ph ph-trash"></i></button></td>
                </tr>
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold">
                  <td colspan="2" class="text-end">Double-Entry Totals:</td>
                  <td class="text-end font-monospace" id="totalDebitsDisplay">₱0.00</td>
                  <td class="text-end font-monospace" id="totalCreditsDisplay">₱0.00</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Real-Time Invariance Status Banner -->
          <div id="balanceStatusBanner" class="p-3 rounded-3 border d-flex align-items-center justify-content-between bg-danger-subtle text-danger">
            <div class="d-flex align-items-center gap-2">
              <i class="ph ph-warning-circle fs-4" id="bannerIcon"></i>
              <div>
                <span class="fw-bold d-block" id="bannerStatusTitle">Unbalanced Journal Entry</span>
                <span class="fs-xs" id="bannerStatusSubtitle">Total debits must strictly equal total credits before posting.</span>
              </div>
            </div>
            <div class="text-end">
              <span class="fs-xs text-muted d-block">Variance:</span>
              <span class="fw-bold font-monospace fs-5" id="varianceAmount">₱0.00</span>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="auto_post" value="1" id="autoPostSwitch" checked>
              <label class="form-check-label small fw-semibold" for="autoPostSwitch">Immediately Post to General Ledger</label>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold" id="btnSubmitJournal" disabled>
              <i class="ph ph-check me-1"></i> Submit Journal Entry
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Reverse Journal Entry (Reason justification for CAS audit) -->
<div class="modal fade" id="reverseModal" tabindex="-1" aria-labelledby="reverseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-danger text-white p-3 px-4">
        <h5 class="modal-title fw-bold" id="reverseModalLabel"><i class="ph ph-arrow-u-down-left me-2"></i>Reverse Journal Entry</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="reverseForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
          <p class="fs-sm text-muted">
            You are about to reverse posted journal entry <strong id="reverseEntryRef" class="font-monospace text-dark">JE-0000</strong>. This will generate an automated counter-balancing journal entry swapping all debits and credits, mark the original entry as <strong>REVERSED</strong>, and record an immutable BIR CAS audit trail.
          </p>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Justification Reason for Reversal <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="Provide reason for adjustment / correction..." required></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light p-3">
          <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-danger px-3"><i class="ph ph-arrow-u-down-left me-1"></i> Confirm Reversal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let lineIndex = 2;

const accountsData = {!! json_encode($accounts->map(fn ($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name, 'category' => $a->category])) !!};

function addJournalLineRow() {
  const tbody = document.getElementById('linesTableBody');
  const tr = document.createElement('tr');
  tr.className = 'line-row';

  let optionsHtml = '<option value="" disabled selected>Select Account...</option>';
  accountsData.forEach(acc => {
    optionsHtml += `<option value="${acc.id}">${acc.code} - ${acc.name} (${acc.category})</option>`;
  });

  tr.innerHTML = `
    <td>
      <select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm account-selector" required>
        ${optionsHtml}
      </select>
    </td>
    <td><input type="text" name="lines[${lineIndex}][memo]" class="form-control form-control-sm" placeholder="Line memo..."></td>
    <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][debit]" class="form-control form-control-sm text-end debit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
    <td><input type="number" step="0.01" min="0" name="lines[${lineIndex}][credit]" class="form-control form-control-sm text-end credit-input font-monospace" placeholder="0.00" value="0.00" oninput="calculateBalance()"></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeJournalLine(this)"><i class="ph ph-trash"></i></button></td>
  `;

  tbody.appendChild(tr);
  lineIndex++;
  calculateBalance();
}

function removeJournalLine(btn) {
  const rows = document.querySelectorAll('.line-row');
  if (rows.length <= 2) {
    alert('A double-entry journal entry requires at least 2 lines (debit and credit).');
    return;
  }
  btn.closest('tr').remove();
  calculateBalance();
}

function calculateBalance() {
  let totalDebit = 0;
  let totalCredit = 0;

  document.querySelectorAll('.debit-input').forEach(input => {
    const val = parseFloat(input.value) || 0;
    totalDebit += val;
  });

  document.querySelectorAll('.credit-input').forEach(input => {
    const val = parseFloat(input.value) || 0;
    totalCredit += val;
  });

  document.getElementById('totalDebitsDisplay').textContent = '₱' + totalDebit.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  document.getElementById('totalCreditsDisplay').textContent = '₱' + totalCredit.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const diff = Math.abs(totalDebit - totalCredit);
  document.getElementById('varianceAmount').textContent = '₱' + diff.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const banner = document.getElementById('balanceStatusBanner');
  const icon = document.getElementById('bannerIcon');
  const title = document.getElementById('bannerStatusTitle');
  const subtitle = document.getElementById('bannerStatusSubtitle');
  const submitBtn = document.getElementById('btnSubmitJournal');

  const isBalanced = diff < 0.001 && totalDebit > 0;

  if (isBalanced) {
    banner.className = 'p-3 rounded-3 border d-flex align-items-center justify-content-between bg-success-subtle text-success';
    icon.className = 'ph ph-check-circle fs-4';
    title.textContent = 'Double-Entry Invariance Satisfied: BALANCED';
    subtitle.textContent = 'Total Debits equal Total Credits (₱' + totalDebit.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '). Ready for posting.';
    submitBtn.disabled = false;
  } else {
    banner.className = 'p-3 rounded-3 border d-flex align-items-center justify-content-between bg-danger-subtle text-danger';
    icon.className = 'ph ph-warning-circle fs-4';
    title.textContent = 'Unbalanced Journal Entry';
    subtitle.textContent = totalDebit === 0 ? 'Enter non-zero debit and credit amounts.' : 'Total debits must strictly equal total credits before posting.';
    submitBtn.disabled = true;
  }
}

function openReverseModal(entryId, refNumber) {
  document.getElementById('reverseEntryRef').textContent = refNumber;
  const form = document.getElementById('reverseForm');
  form.action = "{{ url('/general-ledger/journal-entries') }}/" + entryId + "/reverse";

  const modalEl = document.getElementById('reverseModal');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  calculateBalance();
});
</script>
@endpush
