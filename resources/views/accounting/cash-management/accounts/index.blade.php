@extends('layouts.app')

@section('title', 'Bank Accounts Directory - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-accounts')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Cash Management</li>
          <li class="breadcrumb-item active">Bank Accounts Directory</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Hospital Bank Accounts Master Register</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :systems="['Chart of Accounts (GL 1020)']" 
          description="Master depository and operating account registry." 
      />
      <a href="{{ route('cash.fund-transfers') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-arrows-left-right me-1"></i> Inter-Account Transfers</a>
      <button id="btnAddAccount" class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="ph ph-plus-circle me-1"></i> Add Bank Account</button>
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
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Active Bank Accounts</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-bank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">{{ $activeCount ?? count($bankAccounts ?? []) }} Accounts</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Total Ledger Cash Balance</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-vault fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-success">₱{{ number_format((float) ($totalBalance ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Main Operating Account</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-credit-card fs-5"></i></span>
        </div>
        @php $mainAccount = ($bankAccounts ?? collect())->where('purpose', 'like', '%Operations%')->first(); @endphp
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($mainAccount?->balance ?? 0), 2) }}</h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Collections Account</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-hand-coins fs-5"></i></span>
        </div>
        @php $collectionsAccount = ($bankAccounts ?? collect())->where('purpose', 'like', '%Collections%')->first(); @endphp
        <h4 class="fw-bold mb-0 text-dark">₱{{ number_format((float) ($collectionsAccount?->balance ?? 0), 2) }}</h4>
      </div>
    </div>
  </div>

  <!-- Filter & Search Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('cash.bank-accounts') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
          <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by bank name, account number, purpose, or GL code...">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">All Account Statuses</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="Frozen" {{ request('status') === 'Frozen' ? 'selected' : '' }}>Frozen</option>
          </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="ph ph-magnifying-glass me-1"></i> Filter</button>
          <a href="{{ route('cash.bank-accounts') }}" class="btn btn-light border btn-sm"><i class="ph ph-x me-1"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Bank Name &amp; Account Name</th>
              <th>Account Number</th>
              <th>Linked GL Code</th>
              <th>Designated Purpose</th>
              <th class="text-end">Opening Balance (₱)</th>
              <th class="text-end">Current Balance (₱)</th>
              <th class="text-center">Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bankAccounts ?? [] as $acc)
            @php
              $isActive = ($acc->status === 'Active' && $acc->is_active);
              $isBelowFloor = (bccomp((string) $acc->balance, (string) $acc->minimum_balance, 4) < 0);
            @endphp
            <tr class="{{ ! $isActive ? 'table-light text-muted' : '' }}">
              <td>
                <div class="fw-bold text-dark">{{ $acc->name }}</div>
                <span class="fs-xs text-muted">{{ $acc->bank_name }}</span>
              </td>
              <td><span class="font-monospace text-primary fw-bold">{{ $acc->account_number }}</span></td>
              <td>
                <span class="badge bg-primary-subtle text-primary font-monospace border border-primary-subtle">
                  {{ $acc->gl_code }}
                </span>
                @if($acc->glAccount)
                  <small class="d-block text-muted fs-xs">{{ $acc->glAccount->name }}</small>
                @endif
              </td>
              <td class="fs-xs text-muted">{{ $acc->purpose }}</td>
              <td class="text-end font-monospace fs-xs text-muted">₱{{ number_format((float) $acc->opening_balance, 2) }}</td>
              <td class="text-end font-monospace fw-bold {{ $isBelowFloor ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format((float) $acc->balance, 2) }}
                @if($isBelowFloor)
                  <span class="badge bg-danger-subtle text-danger d-block fs-xs fw-normal mt-1">Below Min (₱{{ number_format((float) $acc->minimum_balance, 2) }})</span>
                @endif
              </td>
              <td class="text-center">
                @if($isActive)
                  <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Active</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary"><i class="ph ph-prohibit me-1"></i> {{ $acc->status }}</span>
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex justify-content-end gap-1">
                  <button class="btn btn-sm btn-outline-primary p-1 px-2" type="button" title="Edit Bank Account" onclick="openEditModal({{ json_encode($acc) }})">
                    <i class="ph ph-pencil-simple"></i>
                  </button>
                  <form method="POST" action="{{ route('cash.bank-accounts.toggle', $acc->id) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-sm {{ $isActive ? 'btn-outline-warning' : 'btn-outline-success' }} p-1 px-2" type="submit" title="{{ $isActive ? 'Deactivate Account' : 'Activate Account' }}">
                      <i class="ph {{ $isActive ? 'ph-power' : 'ph-check-circle' }}"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No hospital bank accounts found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Total Registered: {{ count($bankAccounts ?? []) }} Bank Accounts</span>
    </div>
  </div>
</div>

<!-- Modal: Add Bank Account -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="addAccountModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Add Hospital Bank Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="{{ route('cash.bank-accounts.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Display Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Main Operating & Payroll Fund" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank &amp; Branch Name <span class="text-danger">*</span></label>
            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="e.g. Metrobank - Pasig Medical Plaza Branch" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Number <span class="text-danger">*</span></label>
            <input type="text" name="account_number" class="form-control form-control-sm font-monospace" placeholder="1029-9940-11" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">General Ledger Account Link <span class="text-danger">*</span></label>
            <select name="gl_account_id" class="form-select form-select-sm" required>
              @foreach($glAccounts ?? [] as $gla)
                <option value="{{ $gla->id }}">{{ $gla->code }} - {{ $gla->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Purpose <span class="text-danger">*</span></label>
            <input type="text" name="purpose" class="form-control form-control-sm" placeholder="e.g. Daily Operations, HMO Settlements, Vendor Payouts" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Opening Balance (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="opening_balance" class="form-control form-control-sm text-end font-monospace" value="0.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Minimum Safety Floor (₱) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" name="minimum_balance" class="form-control form-control-sm text-end font-monospace" value="50000.00" required>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Create Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Bank Account -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="editAccountModalLabel"><i class="ph ph-pencil-simple me-2 text-primary"></i>Edit Bank Account Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="editAccountForm" method="POST" action="">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Display Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editName" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Bank &amp; Branch Name <span class="text-danger">*</span></label>
            <input type="text" name="bank_name" id="editBankName" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Number <span class="text-danger">*</span></label>
            <input type="text" name="account_number" id="editAccountNumber" class="form-control form-control-sm font-monospace" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">General Ledger Account Link <span class="text-danger">*</span></label>
            <select name="gl_account_id" id="editGlAccountId" class="form-select form-select-sm" required>
              @foreach($glAccounts ?? [] as $gla)
                <option value="{{ $gla->id }}">{{ $gla->code }} - {{ $gla->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Account Purpose <span class="text-danger">*</span></label>
            <input type="text" name="purpose" id="editPurpose" class="form-control form-control-sm" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Minimum Safety Floor (₱)</label>
              <input type="number" step="0.01" min="0" name="minimum_balance" id="editMinimumBalance" class="form-control form-control-sm text-end font-monospace" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" id="editStatus" class="form-select form-select-sm">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Frozen">Frozen</option>
              </select>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-check me-1"></i> Update Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function openEditModal(acc) {
  const form = document.getElementById('editAccountForm');
  form.action = `/cash-management/bank-accounts/${acc.id}`;

  document.getElementById('editName').value = acc.name || '';
  document.getElementById('editBankName').value = acc.bank_name || '';
  document.getElementById('editAccountNumber').value = acc.account_number || '';
  document.getElementById('editPurpose').value = acc.purpose || '';
  document.getElementById('editMinimumBalance').value = parseFloat(acc.minimum_balance || 50000).toFixed(2);
  document.getElementById('editStatus').value = acc.status || 'Active';

  if (acc.gl_account_id) {
    document.getElementById('editGlAccountId').value = acc.gl_account_id;
  }

  const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
  modal.show();
}
</script>
@endpush
@endsection
