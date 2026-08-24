@extends('layouts.app')

@section('title', 'General Ledger & Journal Browser')
@section('module', 'general-ledger')
@section('page', 'journals')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">General Ledger &amp; Journal Browser</h1>
      <p class="text-muted mb-0">Double-Entry Transaction Ledger &bull; Immutable Audit Trail &bull; GAAP/IFRS Standards</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('accounting.reports.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="ph ph-chart-line-up me-1"></i> Trial Balance
      </a>
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

  <!-- Filters Card -->
  <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('accounting.general-ledger.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
          <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search reference # or description...">
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="POSTED" {{ $status === 'POSTED' ? 'selected' : '' }}>POSTED</option>
            <option value="REVERSED" {{ $status === 'REVERSED' ? 'selected' : '' }}>REVERSED</option>
            <option value="DRAFT" {{ $status === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm" placeholder="From Date">
        </div>
        <div class="col-md-2">
          <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm" placeholder="To Date">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-sm btn-primary px-3"><i class="ph ph-funnel me-1"></i> Filter</button>
          <a href="{{ route('accounting.general-ledger.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Journal Entries Table -->
  <div class="card border-0 shadow-sm rounded-3 bg-white">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0 text-dark"><i class="ph ph-books text-primary me-2"></i>Journal Entries Register</h5>
      <span class="badge bg-light text-dark border">{{ $entries->total() }} Total Entries</span>
    </div>

    <div class="table-responsive p-3">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="fs-xs text-muted text-uppercase">
            <th style="width: 40px;"></th>
            <th>Reference #</th>
            <th>Posting Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Total Debit</th>
            <th class="text-end">Total Credit</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $entry)
            <tr>
              <td>
                <button class="btn btn-sm btn-light border p-1 text-muted rounded-circle" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#linesCollapse{{ $entry->id }}" aria-expanded="false">
                  <i class="ph ph-caret-down"></i>
                </button>
              </td>
              <td>
                <span class="badge bg-light text-dark font-monospace border">{{ $entry->reference_number }}</span>
              </td>
              <td>{{ $entry->entry_date->format('M d, Y') }}</td>
              <td>
                <span class="text-dark fw-medium">{{ $entry->description }}</span>
                @if($entry->reversed_by_entry_id)
                  <span class="badge bg-danger-subtle text-danger ms-1">Reversed by #{{ $entry->reversed_by_entry_id }}</span>
                @endif
              </td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $entry->type }}</span></td>
              <td>
                <span class="badge {{ $entry->status === 'POSTED' ? 'bg-success-subtle text-success' : ($entry->status === 'REVERSED' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                  {{ $entry->status }}
                </span>
              </td>
              <td class="text-end font-monospace fw-bold">
                ₱{{ number_format((float) $entry->lines->sum('debit'), 2) }}
              </td>
              <td class="text-end font-monospace fw-bold">
                ₱{{ number_format((float) $entry->lines->sum('credit'), 2) }}
              </td>
              <td class="text-center">
                @if($entry->status === 'POSTED')
                  @can('reverse-journal-entries')
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            data-bs-toggle="modal" data-bs-target="#reverseModal{{ $entry->id }}">
                      <i class="ph ph-arrow-u-down-left me-1"></i> Reverse
                    </button>
                  @else
                    <span class="badge bg-light text-muted fs-xs"><i class="ph ph-eye me-1"></i>Read-Only</span>
                  @endcan
                @else
                  <span class="text-muted fs-xs">Immutable</span>
                @endif
              </td>
            </tr>

            <!-- Expandable Journal Lines Sub-Table -->
            <tr class="collapse bg-light" id="linesCollapse{{ $entry->id }}">
              <td colspan="9" class="p-3">
                <div class="card border rounded-3 p-3 bg-white">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark fs-sm">Balanced Double-Entry Journal Lines</h6>
                    <span class="badge bg-success-subtle text-success fs-xs"><i class="ph ph-check me-1"></i> Balanced &sum;DR == &sum;CR</span>
                  </div>
                  <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                      <tr class="fs-xs text-muted">
                        <th>Account Code &amp; Title</th>
                        <th>Department</th>
                        <th>Memo / Description</th>
                        <th class="text-end" style="width: 140px;">Debit (DR)</th>
                        <th class="text-end" style="width: 140px;">Credit (CR)</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($entry->lines as $line)
                        <tr>
                          <td>
                            <strong class="font-monospace text-primary">{{ $line->account->code }}</strong> - {{ $line->account->name }}
                          </td>
                          <td><span class="badge bg-light text-muted">{{ $line->account->department ?? 'FINANCE' }}</span></td>
                          <td><small class="text-muted">{{ $line->memo ?? '-' }}</small></td>
                          <td class="text-end font-monospace {{ (float) $line->debit > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                            {{ (float) $line->debit > 0 ? '₱' . number_format((float) $line->debit, 2) : '-' }}
                          </td>
                          <td class="text-end font-monospace {{ (float) $line->credit > 0 ? 'fw-bold text-dark' : 'text-muted' }}">
                            {{ (float) $line->credit > 0 ? '₱' . number_format((float) $line->credit, 2) : '-' }}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>

            <!-- Reversal Modal -->
            @if($entry->status === 'POSTED')
              <div class="modal fade" id="reverseModal{{ $entry->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <form method="POST" action="{{ route('accounting.general-ledger.reverse', $entry->id) }}">
                      @csrf
                      <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                        <h5 class="modal-title fw-bold mb-0">
                          <i class="ph ph-warning-circle me-2"></i>Confirm Transaction Reversal
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4">
                        <p class="text-muted mb-3">
                          You are about to reverse Journal Entry <strong class="text-dark font-monospace">{{ $entry->reference_number }}</strong>. 
                          Under GAAP/IFRS and BIR CAS rules, the original record will remain immutable and a mirroring reversal entry will be generated.
                        </p>
                        <div class="mb-0">
                          <label class="form-label small fw-semibold">Reason for Reversal <span class="text-danger">*</span></label>
                          <textarea name="reason" rows="3" class="form-control" placeholder="Specify error correction reason..." required></textarea>
                        </div>
                      </div>
                      <div class="modal-footer bg-light border-0 py-3 px-4">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold">
                          <i class="ph ph-arrow-u-down-left me-1"></i> Execute Reversal
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @endif
          @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">No journal transactions match your search criteria.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3 border-top">
      {{ $entries->links() }}
    </div>
  </div>
</div>
@endsection
