@extends('layouts.app')

@section('title', 'Cashier POS & Collection Counter')
@section('module', 'cashier')
@section('page', 'pos')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">Cashier Desk &amp; POS Collection Counter</h1>
      <p class="text-muted mb-0">Multi-Channel Patient Settlements &bull; BIR Official Receipts Control</p>
    </div>
    <div class="d-flex align-items-center gap-3">
      @if($activeShift)
        <div class="bg-success-subtle border border-success-subtle text-success px-3 py-1 rounded-pill small fw-semibold">
          <i class="ph ph-circle-wavy-check me-1"></i> Shift: {{ $activeShift->shift_code }} (OPEN)
        </div>
      @else
        <div class="bg-warning-subtle border border-warning-subtle text-warning px-3 py-1 rounded-pill small fw-semibold">
          <i class="ph ph-warning me-1"></i> No Active Shift Opened
        </div>
      @endif
      <a href="{{ route('accounting.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph ph-check-circle fs-5 me-2 align-middle"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row g-4">
    <!-- Left Column: Open Patient Bills Table -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-receipt text-primary me-2"></i>Outstanding Patient Copays</h5>
            <span class="badge bg-primary-subtle text-primary">{{ $openInvoices->total() }} Open Invoices</span>
          </div>
          <!-- Search Bar -->
          <form method="GET" action="{{ route('accounting.cashier.index') }}" class="d-flex gap-2">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
              <input type="text" name="q" value="{{ $search }}" class="form-control bg-light border-start-0" placeholder="Search by Patient Name or Invoice #...">
            </div>
            <button type="submit" class="btn btn-primary px-3">Search</button>
            @if($search)
              <a href="{{ route('accounting.cashier.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
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
              @forelse($openInvoices as $inv)
                <tr>
                  <td>
                    <span class="badge bg-light text-dark font-monospace border">{{ $inv->invoice_number }}</span>
                    <small class="d-block text-muted">{{ $inv->invoice_date->format('M d, Y') }}</small>
                  </td>
                  <td>
                    <strong class="d-block text-dark">{{ $inv->patientAccount->full_name }}</strong>
                    <span class="fs-xs text-muted font-monospace">{{ $inv->patientAccount->patient_id_number }}</span>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $inv->patientAccount->admission_type }}</span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-primary">
                    ₱{{ number_format((float) $inv->patient_payable, 2) }}
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary px-3 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#payModal{{ $inv->id }}">
                      <i class="ph ph-coins me-1"></i> Settle
                    </button>
                  </td>
                </tr>

                <!-- Settle Payment Modal -->
                <div class="modal fade" id="payModal{{ $inv->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                      <form method="POST" action="{{ route('accounting.cashier.pay') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $inv->id }}">
                        
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
                              <strong class="text-dark">{{ $inv->patientAccount->full_name }}</strong>
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
                              <span class="text-primary fs-5">₱{{ number_format((float) $inv->patient_payable, 2) }}</span>
                            </div>
                          </div>

                          <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Amount (₱)</label>
                            <input type="number" step="0.01" name="amount" class="form-control font-monospace fw-bold" 
                                   value="{{ (float) $inv->patient_payable }}" max="{{ (float) $inv->patient_payable }}" required>
                          </div>

                          <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Channel</label>
                            <select name="payment_method" class="form-select fw-medium" required>
                              <option value="CASH">💵 Cash (Cash Drawer)</option>
                              <option value="GCASH">📱 GCash E-Wallet</option>
                              <option value="MAYA">📱 Maya Digital Wallet</option>
                              <option value="QR_PH">💳 QR Ph Interoperable</option>
                              <option value="CREDIT_CARD">💳 Credit / Debit Card Terminal</option>
                              <option value="CHECK">📜 Bank Manager's Check</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label small fw-semibold">Transaction / Auth Code (Digital / Card)</label>
                            <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="e.g. GCash Ref # or Card Auth Code">
                          </div>

                          <div class="mb-0">
                            <label class="form-label small fw-semibold">Official Receipt Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Counter settlement memo...">
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
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No outstanding patient copays found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="p-3 border-top">
          {{ $openInvoices->links() }}
        </div>
      </div>
    </div>

    <!-- Right Column: Recent Official Receipts -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
          <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-check-circle text-success me-2"></i>Issued Official Receipts (OR)</h5>
          <small class="text-muted">Real-time BIR Official Receipt series register</small>
        </div>

        <div class="table-responsive p-3">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="fs-xs text-muted text-uppercase">
                <th>OR Number</th>
                <th>Patient / Payor</th>
                <th>Channel</th>
                <th class="text-end">Amount</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPayments as $pay)
                <tr>
                  <td>
                    <span class="badge bg-success-subtle text-success font-monospace border border-success-subtle">
                      {{ $pay->officialReceipt?->or_number ?? 'OR-PENDING' }}
                    </span>
                    <small class="d-block text-muted">{{ $pay->payment_date->format('M d, Y') }}</small>
                  </td>
                  <td>
                    <strong class="d-block text-dark">{{ $pay->patientAccount->full_name }}</strong>
                    <span class="fs-xs text-muted font-monospace">{{ $pay->payment_reference }}</span>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border">{{ $pay->payment_method }}</span>
                  </td>
                  <td class="text-end font-monospace fw-bold text-success">
                    ₱{{ number_format((float) $pay->amount, 2) }}
                  </td>
                  <td class="text-center">
                    <a href="{{ route('accounting.print.or', $pay->id) }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 px-2" title="Print Official Receipt">
                      <i class="ph ph-printer"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">No payments recorded yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
