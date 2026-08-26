@extends('layouts.app')

@section('title', 'Cashier Desk & POS Collection Counter | FMS')
@section('module', 'collection')
@section('page', 'cashier-desk')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('collection.receipts') }}">Collection Management</a></li>
          <li class="breadcrumb-item active">Cashier Desk</li>
        </ol>
      </nav>
      <h1 class="h3 mb-1 font-weight-bold">Cashier Desk &amp; POS Collection Counter</h1>
      <p class="text-muted mb-0 fs-xs">Patient Copay Settlements &bull; Multi-Terminal Shifts &bull; BIR Official Receipts &bull; Drawer Balancing</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['BDMS (Billing)', 'IBMS (Inpatient Beds)', 'TOCS (Outpatient)']" 
          :internalModules="['Collection Management', 'General Ledger']"
          :tables="['cashier_shifts', 'payments', 'official_receipts', 'journal_entries']"
          glImpact="DR 1011 (Cashier Float) / CR 1110/1120 (AR Patient Copay)"
          description="Ingests finalized copay invoices from BDMS and settles patient balances."
      />
      <button class="btn btn-outline-secondary btn-sm" type="button" onclick="location.reload()"><i class="ph ph-arrow-counter-clockwise me-1"></i> Refresh</button>
      @if($activeShift)
        <div class="bg-success-subtle border border-success-subtle text-success px-3 py-1 rounded-pill small fw-semibold">
          <i class="ph ph-circle-wavy-check me-1"></i> Shift: <span class="font-monospace">{{ $activeShift->shift_code }}</span> (OPEN)
        </div>
        <button type="button" class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
          <i class="ph ph-scales me-1"></i> Close Shift &amp; Turnover
        </button>
      @else
        <div class="bg-warning-subtle border border-warning-subtle text-warning px-3 py-1 rounded-pill small fw-semibold">
          <i class="ph ph-warning me-1"></i> No Active Shift Opened
        </div>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#openShiftModal">
          <i class="ph ph-play-circle me-1"></i> Open Terminal Shift
        </button>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph ph-check-circle fs-5 me-2 align-middle"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph ph-warning-circle fs-5 me-2 align-middle"></i>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Active Shift Drawer Metrics Summary Cards -->
  @if($activeShift)
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Assigned Station / Terminal</span>
          <h5 class="fw-bold mb-0 text-dark">{{ $activeShift->terminal_name }}</h5>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Opening Cash Float</span>
          <h5 class="fw-bold mb-0 font-monospace text-secondary">₱{{ number_format((float) $activeShift->opening_cash_float, 2) }}</h5>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Expected Cash in Drawer</span>
          <h5 class="fw-bold mb-0 font-monospace text-success">₱{{ number_format((float) $activeShift->expected_cash, 2) }}</h5>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3">
          <span class="text-muted fs-xs text-uppercase fw-semibold mb-1">Digital Inflows (Card/QR)</span>
          <h5 class="fw-bold mb-0 font-monospace text-primary">₱{{ number_format((float) $activeShift->total_digital_collections, 2) }}</h5>
        </div>
      </div>
    </div>
  @endif

  <!-- Main POS Counter Interface: 2-Column Responsive Layout -->
  <div class="row g-4 mb-4">
    <!-- Left Column: Outstanding Patient Copays Queue -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-receipt text-primary me-2"></i>Outstanding Patient Copays</h5>
            <span class="badge bg-primary-subtle text-primary">{{ $pendingInvoices->total() }} Records</span>
          </div>

          <!-- Filters & Search Bar -->
          <form method="GET" action="{{ route('collection.cashier-desk') }}" class="d-flex flex-column gap-2 mb-2">
            <div class="d-flex justify-content-between align-items-center gap-2">
              <!-- Admission Type Pill Filters -->
              <div class="btn-group btn-group-sm" role="group" aria-label="Admission Type Filter">
                <a href="{{ route('collection.cashier-desk', array_merge(request()->query(), ['admission_type' => ''])) }}" 
                   class="btn {{ empty($admissionType) ? 'btn-primary' : 'btn-outline-secondary' }}">
                  All
                </a>
                <a href="{{ route('collection.cashier-desk', array_merge(request()->query(), ['admission_type' => 'Inpatient'])) }}" 
                   class="btn {{ $admissionType === 'Inpatient' ? 'btn-primary' : 'btn-outline-secondary' }}">
                  Inpatient
                </a>
                <a href="{{ route('collection.cashier-desk', array_merge(request()->query(), ['admission_type' => 'Outpatient'])) }}" 
                   class="btn {{ $admissionType === 'Outpatient' ? 'btn-primary' : 'btn-outline-secondary' }}">
                  Outpatient
                </a>
              </div>

              <!-- Hide Zero-Balance Bills Toggle -->
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="hideZeroToggle" name="hide_zero" value="1" 
                       {{ ($hideZero ?? true) ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label fs-xs fw-semibold text-muted" for="hideZeroToggle">Hide Zero-Balance Bills</label>
              </div>
            </div>

            <!-- Search Input -->
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
              <input type="text" name="q" value="{{ $search }}" class="form-control bg-light border-start-0" placeholder="Search by Patient Name, MRN #, or Invoice #...">
              <button type="submit" class="btn btn-primary px-3">Search</button>
              @if($search || $admissionType)
                <a href="{{ route('collection.cashier-desk') }}" class="btn btn-outline-secondary">Reset</a>
              @endif
            </div>
          </form>
        </div>

        <div class="table-responsive p-3">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="fs-xs text-muted text-uppercase">
                <th>Invoice #</th>
                <th>Patient Details</th>
                <th>Admission</th>
                <th class="text-end">Patient Copay</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingInvoices as $inv)
                @php
                  $copay = (float) $inv->patient_payable;
                  $isZeroCopay = ($copay <= 0.0001);
                @endphp
                <tr class="{{ $isZeroCopay ? 'table-light text-muted' : '' }}">
                  <td>
                    <span class="badge bg-light text-dark font-monospace border">{{ $inv->invoice_number }}</span>
                    <small class="d-block text-muted">{{ $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : '-' }}</small>
                  </td>
                  <td>
                    <strong class="d-block {{ $isZeroCopay ? 'text-muted' : 'text-dark' }}">{{ $inv->patientAccount?->full_name ?? 'Patient' }}</strong>
                    <span class="fs-xs text-muted font-monospace">{{ $inv->patientAccount?->patient_id_number ?? 'MRN-N/A' }}</span>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $inv->patientAccount?->admission_type ?? 'Outpatient' }}</span>
                  </td>
                  <td class="text-end font-monospace fw-bold {{ $isZeroCopay ? 'text-success' : 'text-primary' }}">
                    ₱{{ number_format($copay, 2) }}
                  </td>
                  <td class="text-center">
                    @if($isZeroCopay)
                      <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 fs-xs">
                        <i class="ph ph-check-circle me-1"></i> Cleared / ₱0.00
                      </span>
                    @else
                      <button type="button" class="btn btn-sm btn-primary px-3 fw-semibold" 
                              data-bs-toggle="modal" data-bs-target="#payModal{{ $inv->id }}">
                        <i class="ph ph-coins me-1"></i> Settle
                      </button>
                    @endif
                  </td>
                </tr>

                @if(! $isZeroCopay)
                  <!-- Settle Payment Modal -->
                  <div class="modal fade" id="payModal{{ $inv->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <form method="POST" action="{{ route('collection.cashier-desk.collect') }}">
                          @csrf
                          <input type="hidden" name="invoice_id" value="{{ $inv->id }}">
                          @if($activeShift)
                            <input type="hidden" name="cashier_shift_id" value="{{ $activeShift->id }}">
                          @endif
                          
                          <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                            <h5 class="modal-title fw-bold mb-0">
                              <i class="ph ph-receipt me-2"></i>Cashier Counter Settlement
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>

                          <div class="modal-body p-4">
                            <div class="p-3 bg-light rounded-3 mb-3">
                              <div class="d-flex justify-content-between text-muted fs-xs mb-1">
                                <span>Patient Name:</span>
                                <strong class="text-dark">{{ $inv->patientAccount?->full_name ?? 'Patient' }}</strong>
                              </div>
                              <div class="d-flex justify-content-between text-muted fs-xs mb-1">
                                <span>Invoice Number:</span>
                                <strong class="text-dark font-monospace">{{ $inv->invoice_number }}</strong>
                              </div>
                              <div class="d-flex justify-content-between text-muted fs-xs">
                                <span>Total Gross Amount:</span>
                                <span>₱{{ number_format((float) $inv->total_amount, 2) }}</span>
                              </div>
                              <hr class="my-2">
                              <div class="d-flex justify-content-between fs-sm fw-bold text-dark">
                                <span>Net Patient Copay Due:</span>
                                <span class="text-primary fs-5">₱{{ number_format($copay, 2) }}</span>
                              </div>
                            </div>

                            <div class="mb-3">
                              <label class="form-label small fw-semibold">Settlement Amount (₱) <span class="text-danger">*</span></label>
                              <input type="number" step="0.01" min="0.01" max="{{ $copay }}" name="amount" id="payAmount{{ $inv->id }}" class="form-control font-monospace fw-bold" 
                                     value="{{ $copay }}" required oninput="calcChange('{{ $inv->id }}')">
                            </div>

                            <div class="mb-3">
                              <label class="form-label small fw-semibold">Payment Channel <span class="text-danger">*</span></label>
                              <select name="payment_method" id="payMethod{{ $inv->id }}" class="form-select fw-medium" required onchange="toggleChannelFields('{{ $inv->id }}')">
                                <option value="CASH">💵 Cash (Cash Drawer)</option>
                                <option value="GCASH">📱 GCash E-Wallet</option>
                                <option value="MAYA">📱 Maya Digital Wallet</option>
                                <option value="QR_PH">💳 QR Ph Interoperable</option>
                                <option value="CREDIT_CARD">💳 Credit Card (POS Terminal)</option>
                                <option value="DEBIT_CARD">💳 Debit Card (POS Terminal)</option>
                                <option value="CHECK">📜 Bank Manager's Check</option>
                              </select>
                            </div>

                            <div class="row g-2 mb-3" id="cashInputs{{ $inv->id }}">
                              <div class="col-md-6">
                                <label class="form-label small fw-semibold">Amount Tendered (₱)</label>
                                <input type="number" step="0.01" min="0" name="tendered_amount" id="tendered{{ $inv->id }}" class="form-control font-monospace" placeholder="0.00" oninput="calcChange('{{ $inv->id }}')">
                              </div>
                              <div class="col-md-6">
                                <label class="form-label small fw-semibold">Change to Customer (₱)</label>
                                <input type="text" id="change{{ $inv->id }}" class="form-control bg-light font-monospace text-success fw-bold" value="₱0.00" readonly>
                              </div>
                            </div>

                            <div class="mb-3" id="transRefGroup{{ $inv->id }}" style="display: none;">
                              <label class="form-label small fw-semibold">Transaction / Auth Code (Digital / Card)</label>
                              <input type="text" name="gateway_transaction_id" class="form-control font-monospace" placeholder="e.g. GCash Ref # or POS Terminal Auth Code">
                            </div>

                            <div class="mb-3">
                              <label class="form-label small fw-semibold">Payor Name (for BIR OR)</label>
                              <input type="text" name="payor_name" class="form-control form-control-sm" value="{{ $inv->patientAccount?->full_name }}" placeholder="e.g. Maria Santos / Self">
                            </div>

                            <div class="mb-0">
                              <label class="form-label small fw-semibold">Official Receipt Memo</label>
                              <input type="text" name="notes" class="form-control form-control-sm" placeholder="Counter settlement memo...">
                            </div>
                          </div>

                          <div class="modal-footer bg-light border-0 py-3 px-4">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                              <i class="ph ph-printer me-1"></i> Post &amp; Issue BIR Receipt
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                @endif
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No outstanding patient copays found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="p-3 border-top">
          {{ $pendingInvoices->links() }}
        </div>
      </div>
    </div>

    <!-- Right Column: Recent Official Receipts -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
          <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-check-circle text-success me-2"></i>Issued Official Receipts (OR)</h5>
          <small class="text-muted">Real-time BIR EOPT Official Receipt series register</small>
        </div>

        <div class="table-responsive p-3">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="fs-xs text-muted text-uppercase">
                <th>OR Number</th>
                <th>Patient / Payor</th>
                <th>Channel</th>
                <th class="text-end">Amount</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($todayPayments as $pay)
                <tr>
                  <td>
                    <span class="badge bg-success-subtle text-success font-monospace border border-success-subtle">
                      {{ $pay->officialReceipt?->or_number ?? 'OR-PENDING' }}
                    </span>
                    <small class="d-block text-muted">{{ $pay->payment_date ? $pay->payment_date->format('M d, Y') : '-' }}</small>
                  </td>
                  <td>
                    <strong class="d-block text-dark">{{ $pay->officialReceipt?->payor_name ?: ($pay->patientAccount?->full_name ?? 'Patient') }}</strong>
                    <span class="fs-xs text-muted font-monospace">{{ $pay->payment_reference }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border">{{ $pay->payment_method }}</span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-success">
                    ₱{{ number_format((float) $pay->amount, 2) }}
                  </td>
                  <td class="text-center">
                    <a href="{{ route('collection.receipts.print', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 px-2" title="Print BIR EOPT Official Receipt">
                      <i class="ph ph-printer"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No payments recorded today.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Section: All Terminal Shifts Supervision -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
      <h6 class="fw-bold mb-0 text-dark"><i class="ph ph-desktop me-2 text-primary"></i>All Hospital POS Stations &amp; Terminal Shifts</h6>
      <span class="badge bg-primary-subtle text-primary">{{ count($shifts ?? []) }} Stations</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Shift Code</th>
              <th>Station / Terminal</th>
              <th>Cashier Officer</th>
              <th class="text-end">Opening Float (₱)</th>
              <th class="text-end">Drawer Expected / Counted</th>
              <th>Shift Start</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($shifts ?? [] as $t)
            @php
              $tId = $t->shift_code;
              $loc = $t->terminal_name;
              $cashier = $t->cashier?->name ?? 'Cashier';
              $float = '₱' . number_format((float) $t->opening_cash_float, 2);
              $cash = ($t->status === 'OPEN') 
                  ? '₱' . number_format((float) $t->expected_cash, 2)
                  : '₱' . number_format((float) $t->actual_cash_counted, 2);
              $start = $t->opened_at ? $t->opened_at->format('M d, h:i A') : '-';
              $status = $t->status;
              $badge = match($status) {
                  'OPEN' => 'bg-success-subtle text-success',
                  'CLOSED' => 'bg-secondary-subtle text-secondary',
                  'RECONCILED' => 'bg-primary-subtle text-primary',
                  default => 'bg-light text-dark'
              };
            @endphp
            <tr>
              <td><span class="font-monospace text-primary fw-bold">{{ $tId }}</span></td>
              <td>
                <div class="fw-semibold text-dark">{{ $loc }}</div>
                <span class="fs-xs text-muted">Hospital POS Counter</span>
              </td>
              <td class="fw-semibold text-dark">{{ $cashier }}</td>
              <td class="text-end text-muted font-monospace">{{ $float }}</td>
              <td class="text-end text-success fw-bold font-monospace">{{ $cash }}</td>
              <td><span class="text-nowrap font-monospace fs-xs">{{ $start }}</span></td>
              <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
              <td class="text-end">
                @if($status === 'CLOSED')
                  <form method="POST" action="{{ route('collection.shifts.reconcile', $t->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-primary btn-xs" type="submit" title="Reconcile as Supervisor"><i class="ph ph-shield-check me-1"></i> Reconcile</button>
                  </form>
                @elseif($status === 'RECONCILED')
                  <span class="badge bg-light text-muted border py-1 px-2"><i class="ph ph-lock me-1"></i> Reconciled</span>
                @else
                  <span class="badge bg-success-subtle text-success py-1 px-2"><i class="ph ph-activity me-1"></i> Active</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No cashier shift records available.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Open Terminal Shift -->
<div class="modal fade" id="openShiftModal" tabindex="-1" aria-labelledby="openShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="openShiftModalLabel"><i class="ph ph-play-circle me-2 text-primary"></i>Open Cashier Terminal Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('collection.shifts.open') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Select POS Station <span class="text-danger">*</span></label>
            <select name="terminal_name" class="form-select form-select-sm" required>
              <option value="POS-MAIN-01 (Main Lobby)">POS-MAIN-01 (Main Lobby)</option>
              <option value="POS-ER-01 (Emergency Room)">POS-ER-01 (Emergency Room)</option>
              <option value="POS-PHARM-01 (Pharmacy Central)">POS-PHARM-01 (Pharmacy Central)</option>
              <option value="POS-OPD-01 (Outpatient Consultation)">POS-OPD-01 (Outpatient Consultation)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Opening Cash Float (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="opening_cash_float" class="form-control form-control-sm text-end font-monospace" value="5000.00" required>
            <span class="fs-xs text-muted">Amount of physical petty cash drawer assigned at start of shift.</span>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Start Shift Now</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Close Shift & Turnover (Real-Time Variance Calculator) -->
@if($activeShift)
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning-subtle text-warning-emphasis border-0 pb-2">
        <h5 class="modal-title font-weight-bold" id="closeShiftModalLabel"><i class="ph ph-scales me-2"></i>Close Shift &amp; Drawer Turnover</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('collection.shifts.close') }}">
          @csrf
          <input type="hidden" name="shift_id" value="{{ $activeShift->id }}">

          <!-- Shift Metrics Breakdown -->
          <div class="p-3 bg-light rounded-3 mb-3 fs-xs">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Terminal Station:</span>
              <strong class="text-dark">{{ $activeShift->terminal_name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Shift Code:</span>
              <span class="font-monospace text-primary fw-bold">{{ $activeShift->shift_code }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Opening Cash Float:</span>
              <span class="font-monospace">₱{{ number_format((float) $activeShift->opening_cash_float, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Digital / Card Collections:</span>
              <span class="font-monospace">₱{{ number_format((float) $activeShift->total_digital_collections, 2) }}</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fs-sm fw-bold">
              <span>System Expected Cash:</span>
              <span class="text-success font-monospace">₱{{ number_format((float) $activeShift->expected_cash, 2) }}</span>
              <input type="hidden" id="modalExpectedCash" value="{{ (float) $activeShift->expected_cash }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Actual Physical Cash Counted (₱) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="actual_cash_counted" id="modalActualCash" class="form-control form-control-sm text-end font-monospace fw-bold" required oninput="calcShiftVariance()">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Calculated Drawer Variance</label>
            <input type="text" id="modalVarianceDisplay" class="form-control form-control-sm bg-light text-end font-monospace fw-bold" value="₱0.00" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Variance Reason / Explanation (Optional)</label>
            <textarea name="variance_reason" rows="2" class="form-control form-control-sm" placeholder="Explain any cash overage or shortage..."></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-warning"><i class="ph ph-lock me-1"></i> Close &amp; Generate Turnover</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Printable Shift Turnover / Bag Tag Summary Modal -->
@if(session('turnover_summary'))
@php $t = session('turnover_summary'); @endphp
<div class="modal fade show d-block" id="turnoverPrintModal" tabindex="-1" style="background: rgba(0,0,0,0.5);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-3 px-4">
        <h5 class="modal-title fw-bold mb-0"><i class="ph ph-tag me-2"></i>Physical Cash Turnover Bag Tag</h5>
        <a href="{{ route('collection.cashier-desk') }}" class="btn-close btn-close-white"></a>
      </div>
      <div class="modal-body p-4" id="printableTurnoverArea">
        <div class="text-center border-bottom pb-2 mb-3">
          <h6 class="fw-bold mb-0 text-uppercase">St. Jude Metropolitan Medical Center</h6>
          <small class="text-muted fs-xs">Cashier Shift Custody Turnover Slip &bull; BIR CAS Audited</small>
        </div>
        <div class="fs-xs d-flex flex-column gap-1 mb-3">
          <div class="d-flex justify-content-between"><span class="text-muted">Shift Code:</span><strong class="font-monospace">{{ $t['shift_code'] }}</strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted">Terminal:</span><strong>{{ $t['terminal_name'] }}</strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted">Cashier Officer:</span><strong>{{ $t['cashier_name'] }}</strong></div>
          <div class="d-flex justify-content-between"><span class="text-muted">Closed Timestamp:</span><span>{{ $t['closed_at'] }}</span></div>
        </div>
        <table class="table table-sm table-bordered fs-xs mb-3">
          <tbody>
            <tr><td>Opening Cash Float</td><td class="text-end font-monospace">₱{{ $t['opening_float'] }}</td></tr>
            <tr><td>Expected Cash in Drawer</td><td class="text-end font-monospace">₱{{ $t['expected_cash'] }}</td></tr>
            <tr class="table-light fw-bold"><td>Physical Cash Counted</td><td class="text-end font-monospace text-success">₱{{ $t['actual_cash'] }}</td></tr>
            <tr><td>Drawer Cash Variance</td><td class="text-end font-monospace fw-bold {{ (float) str_replace(',', '', $t['cash_variance']) == 0 ? 'text-success' : 'text-danger' }}">₱{{ $t['cash_variance'] }}</td></tr>
            <tr><td>Total Digital Collections</td><td class="text-end font-monospace">₱{{ $t['digital_collections'] }}</td></tr>
            <tr class="table-primary fw-bold"><td>Total Shift Revenue</td><td class="text-end font-monospace">₱{{ $t['total_collections'] }}</td></tr>
          </tbody>
        </table>
        <div class="border p-2 rounded text-muted fs-xs mb-3">
          <strong>Variance Reason:</strong> {{ $t['variance_reason'] }}
        </div>
        <div class="row text-center fs-xs pt-3 border-top">
          <div class="col-6">
            <div class="border-bottom pb-3 mb-1"></div>
            <span>Remitting Cashier Signature</span>
          </div>
          <div class="col-6">
            <div class="border-bottom pb-3 mb-1"></div>
            <span>Vault / Treasury Receiver</span>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light border-0 py-2 px-4">
        <a href="{{ route('collection.cashier-desk') }}" class="btn btn-sm btn-light border">Close</a>
        <button type="button" class="btn btn-sm btn-dark" onclick="window.print()"><i class="ph ph-printer me-1"></i> Print Turnover Tag</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleChannelFields(id) {
  const method = document.getElementById('payMethod' + id)?.value;
  const cashGroup = document.getElementById('cashInputs' + id);
  const transGroup = document.getElementById('transRefGroup' + id);

  if (method === 'CASH') {
    if (cashGroup) cashGroup.style.display = '';
    if (transGroup) transGroup.style.display = 'none';
  } else {
    if (cashGroup) cashGroup.style.display = 'none';
    if (transGroup) transGroup.style.display = '';
  }
}

function calcChange(id) {
  const amt = parseFloat(document.getElementById('payAmount' + id)?.value || 0);
  const tendered = parseFloat(document.getElementById('tendered' + id)?.value || 0);
  const change = Math.max(0, tendered - amt);
  const disp = document.getElementById('change' + id);
  if (disp) disp.value = '₱' + change.toFixed(2);
}

function calcShiftVariance() {
  const expected = parseFloat(document.getElementById('modalExpectedCash')?.value || 0);
  const actual = parseFloat(document.getElementById('modalActualCash')?.value || 0);
  const variance = actual - expected;
  const disp = document.getElementById('modalVarianceDisplay');
  if (disp) {
    disp.value = (variance >= 0 ? '+' : '') + '₱' + variance.toFixed(2);
    disp.className = 'form-control form-control-sm bg-light text-end font-monospace fw-bold ' + 
                     (variance === 0 ? 'text-success' : (variance < 0 ? 'text-danger' : 'text-primary'));
  }
}
</script>
@endpush
