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
      <h1 class="h3 mb-1 font-weight-bold">Cashier POS Desk &amp; Payment Counter</h1>
      <p class="text-muted mb-0 fs-xs">Accept patient payments, calculate change, issue BIR Official Receipts (OR), and balance daily shift cash drawers.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="external" 
          :systems="['BDMS (Billing)', 'IBMS (Inpatient Beds)', 'TOCS (Outpatient)']" 
          :internalModules="['Collection Management', 'General Ledger']"
          :tables="['cashier_shifts', 'payments', 'official_receipts', 'journal_entries']"
          glImpact="DR 1011 (Cashier Float) / CR 1110/1120 (AR Patient Copay)"
          description="Ingests finalized copay invoices from billing and settles patient balances."
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
                  $copayFormatted = number_format((float) $inv->patient_payable, 2, '.', '');
                  $copayVal = (float) $copayFormatted;
                  $isZeroCopay = ($copayVal <= 0.00);
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
                    ₱{{ number_format($copayVal, 2) }}
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
                  <!-- Settle Payment Modal (Minimalist Fintech Design) -->
                  <div class="modal fade" id="payModal{{ $inv->id }}" tabindex="-1" aria-hidden="true"
                       x-data="{
                           settlementAmount: '{{ $copayFormatted }}',
                           paymentMethod: 'CASH',
                           amountTendered: '{{ $copayFormatted }}',
                           splitCashAmount: '0.00',
                           splitCashTendered: '0.00',
                           splitDigitalAmount: '0.00',
                           splitDigitalChannel: 'CREDIT_CARD',
                           get changeAmount() {
                               if (this.paymentMethod === 'SPLIT_PAYMENT') {
                                   const c = parseFloat(this.splitCashAmount) || 0;
                                   const t = parseFloat(this.splitCashTendered) || 0;
                                   return (t >= c ? (t - c) : 0).toFixed(2);
                               }
                               if (this.paymentMethod !== 'CASH') return '0.00';
                               const s = parseFloat(this.settlementAmount) || 0;
                               const t = parseFloat(this.amountTendered) || 0;
                               return (t >= s ? (t - s) : 0).toFixed(2);
                           },
                           get splitTotalSum() {
                               const c = parseFloat(this.splitCashAmount) || 0;
                               const d = parseFloat(this.splitDigitalAmount) || 0;
                               return c + d;
                           },
                           get isSplitValid() {
                               if (this.paymentMethod !== 'SPLIT_PAYMENT') return true;
                               const s = parseFloat(this.settlementAmount) || 0;
                               const sum = this.splitTotalSum;
                               return Math.abs(sum - s) < 0.01 && sum > 0;
                           },
                           get isUnderTendered() {
                               if (this.paymentMethod === 'SPLIT_PAYMENT') {
                                   return ! this.isSplitValid;
                               }
                               if (this.paymentMethod !== 'CASH') return false;
                               const s = parseFloat(this.settlementAmount) || 0;
                               const t = parseFloat(this.amountTendered) || 0;
                               return t < s;
                           },
                           get formattedChange() {
                               const c = parseFloat(this.changeAmount) || 0;
                               return '₱ ' + c.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                           },
                           onChannelChange() {
                               if (this.paymentMethod === 'SPLIT_PAYMENT') {
                                   const s = parseFloat(this.settlementAmount) || 0;
                                   const half = (s / 2).toFixed(2);
                                   this.splitCashAmount = half;
                                   this.splitDigitalAmount = (s - parseFloat(half)).toFixed(2);
                                   this.splitCashTendered = half;
                               } else if (this.paymentMethod !== 'CASH') {
                                   this.amountTendered = this.settlementAmount;
                               }
                           }
                       }">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <form method="POST" action="{{ route('collection.cashier-desk.collect') }}">
                          @csrf
                          <input type="hidden" name="invoice_id" value="{{ $inv->id }}">
                          @if($activeShift)
                            <input type="hidden" name="cashier_shift_id" value="{{ $activeShift->id }}">
                          @endif
                          
                          <!-- Minimal Header -->
                          <div class="modal-header bg-white border-bottom border-light-subtle py-3 px-4 align-items-center">
                            <h6 class="modal-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                              <i class="ph ph-receipt text-primary fs-5"></i> Counter Settlement
                            </h6>
                            <button type="button" class="btn-close fs-xs" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>

                          <div class="modal-body p-4">
                            <!-- Patient Summary Card -->
                            <div class="p-3 bg-light border border-light-subtle rounded-3 mb-3">
                              <div class="d-flex justify-content-between text-muted fs-xs mb-1">
                                <span>Patient:</span>
                                <strong class="text-dark">{{ $inv->patientAccount?->full_name ?? 'Patient' }}</strong>
                              </div>
                              <div class="d-flex justify-content-between text-muted fs-xs mb-2">
                                <span>Invoice Reference:</span>
                                <span class="font-monospace text-secondary fw-semibold">{{ $inv->invoice_number }}</span>
                              </div>
                              <div class="d-flex justify-content-between align-items-baseline pt-2 border-top border-light-subtle">
                                <span class="fs-xs fw-semibold text-muted text-uppercase">Net Copay Due</span>
                                <span class="fs-4 font-monospace fw-bold text-dark">₱ {{ number_format($copayVal, 2) }}</span>
                              </div>
                            </div>

                            <div class="mb-3">
                              <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-0">Settlement Amount (₱) <span class="text-danger">*</span></label>
                                <span class="badge bg-light text-secondary border font-monospace fs-xs fw-normal">Partial Allowed</span>
                              </div>
                              <input type="number" step="0.01" min="0.01" max="{{ $copayFormatted }}" name="amount" id="payAmount{{ $inv->id }}" class="form-control form-control-sm font-monospace fw-bold" 
                                     x-model="settlementAmount" required>
                              <small class="text-primary fs-xs mt-1 d-block" x-show="parseFloat(settlementAmount || 0) < {{ $copayFormatted }} && parseFloat(settlementAmount || 0) > 0">
                                <i class="ph ph-info me-1"></i>Partial payment of ₱<span x-text="parseFloat(settlementAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>. Remaining balance will be ₱<span x-text="({{ $copayFormatted }} - parseFloat(settlementAmount || 0)).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>.
                              </small>
                            </div>

                            <div class="mb-3">
                              <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Payment Channel <span class="text-danger">*</span></label>
                              <select name="payment_method" id="payMethod{{ $inv->id }}" class="form-select form-select-sm fw-medium" required x-model="paymentMethod" @change="onChannelChange()">
                                <option value="CASH">Cash (Cash Drawer)</option>
                                <option value="GCASH">GCash E-Wallet</option>
                                <option value="MAYA">Maya Digital Wallet</option>
                                <option value="QR_PH">QR Ph Interoperable</option>
                                <option value="CREDIT_CARD">Credit Card (POS Terminal)</option>
                                <option value="DEBIT_CARD">Debit Card (POS Terminal)</option>
                                <option value="BANK_TRANSFER">Bank Transfer / EFT</option>
                                <option value="CHECK">Bank Manager's Check</option>
                                <option value="SPLIT_PAYMENT">Split / Multiple Tender (Cash + Card/Digital)</option>
                              </select>
                            </div>

                            <!-- Single Cash Channel Row -->
                            <div class="row g-2 mb-3" x-show="paymentMethod === 'CASH'">
                              <div class="col-md-6">
                                <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Amount Tendered (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="tendered_amount" id="tendered{{ $inv->id }}" class="form-control form-control-sm font-monospace" placeholder="0.00" x-model="amountTendered">
                                <template x-if="isUnderTendered && paymentMethod === 'CASH'">
                                  <div class="mt-2">
                                    <small class="text-danger fw-semibold d-block mb-1 fs-xs">
                                      <i class="ph ph-warning-circle me-1"></i>Tendered (₱<span x-text="parseFloat(amountTendered || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>) is less than settlement (₱<span x-text="parseFloat(settlementAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>).
                                    </small>
                                    <button type="button" @click="settlementAmount = amountTendered" class="btn btn-outline-danger btn-xs py-1 px-2 fs-xs">
                                      <i class="ph ph-check me-1"></i>Set Settlement to ₱<span x-text="parseFloat(amountTendered || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                    </button>
                                  </div>
                                </template>
                              </div>
                              <div class="col-md-6">
                                <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Change (₱)</label>
                                <input type="text" id="change{{ $inv->id }}" class="form-control form-control-sm bg-light font-monospace text-success fw-bold" :value="formattedChange" readonly>
                              </div>
                            </div>

                            <!-- Multi-Tender / Split Payment Breakdown Card -->
                            <div class="border border-light-subtle bg-light-subtle rounded-3 p-3 mb-3" x-show="paymentMethod === 'SPLIT_PAYMENT'">
                              <div class="fw-semibold text-secondary fs-xs text-uppercase mb-2 d-flex align-items-center gap-1">
                                <i class="ph ph-arrows-split"></i> Multi-Tender Breakdown
                              </div>
                              
                              <!-- Tender 1: Cash Portion -->
                              <div class="row g-2 mb-2 pb-2 border-bottom border-light-subtle">
                                <div class="col-md-5">
                                  <label class="form-label fs-xs text-muted mb-1">Tender 1 (Cash)</label>
                                  <input type="text" class="form-control form-control-sm bg-white" value="Cash (Cash Drawer)" readonly>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label fs-xs text-muted mb-1">Amount (₱)</label>
                                  <input type="number" step="0.01" min="0" name="split_cash_amount" class="form-control form-control-sm font-monospace" placeholder="0.00" x-model="splitCashAmount">
                                </div>
                                <div class="col-md-4">
                                  <label class="form-label fs-xs text-muted mb-1">Tendered (₱)</label>
                                  <input type="number" step="0.01" min="0" class="form-control form-control-sm font-monospace" placeholder="0.00" x-model="splitCashTendered">
                                </div>
                              </div>

                              <!-- Tender 2: Digital / Card Portion -->
                              <div class="row g-2 mb-2">
                                <div class="col-md-5">
                                  <label class="form-label fs-xs text-muted mb-1">Tender 2 (Digital/Card)</label>
                                  <select name="split_digital_channel" class="form-select form-select-sm fw-medium" x-model="splitDigitalChannel">
                                    <option value="CREDIT_CARD">Credit Card POS</option>
                                    <option value="DEBIT_CARD">Debit Card POS</option>
                                    <option value="GCASH">GCash E-Wallet</option>
                                    <option value="MAYA">Maya Digital Wallet</option>
                                    <option value="BANK_TRANSFER">Bank Transfer / EFT</option>
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label fs-xs text-muted mb-1">Amount (₱)</label>
                                  <input type="number" step="0.01" min="0" name="split_digital_amount" class="form-control form-control-sm font-monospace" placeholder="0.00" x-model="splitDigitalAmount">
                                </div>
                                <div class="col-md-4">
                                  <label class="form-label fs-xs text-muted mb-1">Auth / Ref #</label>
                                  <input type="text" name="split_digital_ref" class="form-control form-control-sm font-monospace" placeholder="Ref #">
                                </div>
                              </div>

                              <!-- Cash Change display for Split Cash portion -->
                              <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle fs-xs">
                                <span class="text-muted">Cash Change: <strong class="text-success font-monospace" x-text="formattedChange"></strong></span>
                                <span :class="isSplitValid ? 'text-success fw-semibold' : 'text-danger fw-semibold'">
                                  <i :class="isSplitValid ? 'ph ph-check-circle me-1' : 'ph ph-warning-circle me-1'"></i>
                                  <span x-text="isSplitValid ? 'Split Sum Matches' : 'Sum (₱' + splitTotalSum.toFixed(2) + ') ≠ Settlement (₱' + (parseFloat(settlementAmount)||0).toFixed(2) + ')'"></span>
                                </span>
                              </div>
                            </div>

                            <div class="mb-3" x-show="paymentMethod !== 'CASH' && paymentMethod !== 'SPLIT_PAYMENT'">
                              <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Transaction / Auth Code</label>
                              <input type="text" name="gateway_transaction_id" class="form-control form-control-sm font-monospace" placeholder="e.g. GCash Ref # or POS Auth Code">
                            </div>

                            <div class="mb-3">
                              <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Payor Name (for BIR Receipt)</label>
                              <input type="text" name="payor_name" class="form-control form-control-sm" value="{{ $inv->patientAccount?->full_name }}" placeholder="e.g. Payor Full Name">
                            </div>

                            <div class="mb-0">
                              <label class="form-label fs-xs fw-semibold text-muted text-uppercase mb-1">Receipt Remarks</label>
                              <input type="text" name="notes" class="form-control form-control-sm" placeholder="Settlement memo...">
                            </div>
                          </div>

                          <div class="modal-footer bg-white border-top border-light-subtle py-3 px-4 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3 fw-medium shadow-sm" :disabled="isUnderTendered">
                              <i class="ph ph-check-circle me-1"></i> Post &amp; Issue BIR Receipt
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
