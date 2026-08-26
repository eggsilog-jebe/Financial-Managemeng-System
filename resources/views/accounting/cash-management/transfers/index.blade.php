@extends('layouts.app')

@section('title', 'Fund Transfers - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'fund-transfers')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Fund Transfers</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Inter-Account Fund Transfers &amp; Liquidity Routing</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Bank Accounts', 'General Ledger']" 
          :tables="['bank_accounts', 'journal_entries', 'journal_entry_lines']"
          glImpact="DR Destination Bank GL / CR Source Bank GL"
          description="Sweeps cash balances between internal depository, payroll, and operating bank accounts." 
      />
      <a href="{{ route('cash.bank-accounts') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-bank me-1"></i> Bank Accounts</a>
      <button id="btnNewTransfer" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#newTransferModal">
        <i class="ph ph-arrows-left-right me-1"></i> Execute Fund Transfer
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
      <i class="ph ph-check-circle fs-4 me-2"></i>
      <div>{{ session('success') }}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert">
      <i class="ph ph-warning-circle fs-4 me-2"></i>
      <div>{{ session('error') }}</div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Cumulative Transferred Volume</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-arrows-clockwise fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($totalTransferVolume ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Transfer Transactions</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-files fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $transfers->total() ?? count($transfers ?? []) }} Transfers</h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Available Transfer Channels</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-globe-hemisphere-west fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">PESONet &bull; InstaPay &bull; Internal Book</h4>
      </div>
    </div>
  </div>

  <!-- Filter & Search Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('cash.fund-transfers') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search reference #, bank, memo...">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="From Date">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="To Date">
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-magnifying-glass me-1"></i> Filter</button>
          <a href="{{ route('cash.fund-transfers') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Transfers Ledger Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Transfer Ref #</th>
              <th>Date</th>
              <th>Source Account (Debit Outflow)</th>
              <th>Destination Account (Credit Inflow)</th>
              <th>Method</th>
              <th>General Ledger Entry</th>
              <th class="text-end">Amount (₱)</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transfers ?? [] as $t)
            <tr>
              <td>
                <span class="font-monospace text-primary fw-bold">{{ $t->reference_number }}</span>
                @if($t->memo)
                  <small class="d-block text-muted">{{ $t->memo }}</small>
                @endif
              </td>
              <td class="font-monospace fs-xs">{{ $t->transfer_date ? $t->transfer_date->format('M d, Y') : '-' }}</td>
              <td>
                <strong class="d-block text-dark">{{ $t->sourceBank?->name ?? $t->source_account }}</strong>
                <span class="fs-xs text-muted font-monospace">{{ $t->sourceBank?->account_number ?? $t->source_number }}</span>
              </td>
              <td>
                <strong class="d-block text-dark">{{ $t->destinationBank?->name ?? $t->destination_account }}</strong>
                <span class="fs-xs text-muted font-monospace">{{ $t->destinationBank?->account_number ?? $t->destination_number }}</span>
              </td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $t->transfer_method }}</span></td>
              <td>
                @if($t->journalEntry)
                  <a href="{{ route('gl.journal-entries') }}?search={{ $t->journalEntry->reference_number }}" 
                     class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none">
                    <i class="ph ph-link me-1"></i> {{ $t->journalEntry->reference_number }}
                  </a>
                @else
                  <span class="badge bg-light text-muted border">JE-POSTED</span>
                @endif
              </td>
              <td class="text-end font-monospace fw-bold text-success">₱{{ number_format((float) $t->amount, 2) }}</td>
              <td class="text-center">
                <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> {{ $t->status }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No fund transfers recorded.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Showing {{ $transfers->count() }} of {{ $transfers->total() }} Fund Transfers</span>
      {{ $transfers->links() }}
    </div>
  </div>
</div>

<!-- Modal: Execute Fund Transfer -->
<div class="modal fade" id="newTransferModal" tabindex="-1" aria-labelledby="newTransferModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="newTransferModalLabel"><i class="ph ph-arrows-left-right me-2 text-primary"></i>Execute Inter-Account Fund Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('cash.fund-transfers.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Source Bank Account (From) <span class="text-danger">*</span></label>
            <select name="source_bank_account_id" id="sourceBankSelect" class="form-select form-select-sm" required onchange="validateDifferentBanks()">
              <option value="">-- Select Source Bank Account --</option>
              @foreach($bankAccounts ?? [] as $ba)
                <option value="{{ $ba->id }}" data-balance="{{ (float) $ba->balance }}">
                  {{ $ba->bank_name }} - {{ $ba->name }} (₱{{ number_format((float) $ba->balance, 2) }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Destination Bank Account (To) <span class="text-danger">*</span></label>
            <select name="destination_bank_account_id" id="destBankSelect" class="form-select form-select-sm" required onchange="validateDifferentBanks()">
              <option value="">-- Select Destination Bank Account --</option>
              @foreach($bankAccounts ?? [] as $ba)
                <option value="{{ $ba->id }}">
                  {{ $ba->bank_name }} - {{ $ba->name }} ({{ $ba->account_number }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Amount (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm text-end font-monospace fw-bold" placeholder="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Transfer Date <span class="text-danger">*</span></label>
              <input type="date" name="transfer_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Transfer Protocol / Channel</label>
            <select name="transfer_method" class="form-select form-select-sm">
              <option value="INSTAPAY_PESONET">PESONet / InstaPay Commercial Routing</option>
              <option value="INTERNAL_BOOK_TRANSFER">Internal Bank Intragroup Book Transfer</option>
              <option value="RTGS_DIRECT">RTGS High-Value Treasury Transfer</option>
              <option value="MANAGER_CHECK_DEPOSIT">Manager's Check Inter-Branch Deposit</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Transfer Memo &amp; Justification</label>
            <input type="text" name="memo" class="form-control form-control-sm" placeholder="e.g. Funding payroll account for 15th cutoff">
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" id="btnSubmitTransfer" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Authorize &amp; Post Transfer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function validateDifferentBanks() {
  const src = document.getElementById('sourceBankSelect')?.value;
  const dst = document.getElementById('destBankSelect')?.value;
  const btn = document.getElementById('btnSubmitTransfer');

  if (src && dst && src === dst) {
    alert('Source and Destination bank accounts must be different!');
    document.getElementById('destBankSelect').value = '';
  }
}
</script>
@endpush
@endsection
