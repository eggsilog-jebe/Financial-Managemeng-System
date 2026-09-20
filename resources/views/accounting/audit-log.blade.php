@extends('layouts.app')

@section('title', 'System Audit Trail & Compliance')
@section('module', 'general-ledger')
@section('page', 'audit-log')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">
        <i class="ph-fill ph-shield-check text-primary me-2 align-middle"></i>System Audit Trail &amp; Compliance Logs
      </h1>
      <p class="text-muted mb-0">
        Immutable Event Telemetry &bull; BIR CAS &amp; COA Regulatory Audit Trail &bull; Executive CFO &amp; Internal Auditor View
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 py-2 px-3">
        <i class="ph-bold ph-lock-key me-1"></i> Restricted: CFO &amp; Auditor Only
      </span>
      <a href="{{ route('accounting.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-arrow-left me-1"></i> Back to Dashboard
      </a>
    </div>
  </div>

  <!-- KPI Overview Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
            <i class="ph-bold ph-list-dashes fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Total Audit Records</div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_logs']) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-success-subtle text-success p-3 rounded-3 me-3">
            <i class="ph-bold ph-sign-in fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Successful Logins Today</div>
            <div class="fs-4 fw-bold text-success">{{ number_format($stats['logins_today']) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-danger-subtle text-danger p-3 rounded-3 me-3">
            <i class="ph-bold ph-warning-octagon fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Failed Logins Today</div>
            <div class="fs-4 fw-bold text-danger">{{ number_format($stats['failed_logins']) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-warning-subtle text-warning p-3 rounded-3 me-3">
            <i class="ph-bold ph-database fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Data Mutations Today</div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['mutations_today']) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('accounting.audit-log') }}" class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-md-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Search description, user, IP..." value="{{ $search }}">
          </div>
        </div>

        <!-- Event Filter -->
        <div class="col-md-2">
          <select name="event" class="form-select form-select-sm">
            <option value="">All Events</option>
            @foreach($events as $ev)
              <option value="{{ $ev }}" @selected($event === $ev)>{{ ucfirst(str_replace('_', ' ', $ev)) }}</option>
            @endforeach
          </select>
        </div>

        <!-- Module Filter -->
        <div class="col-md-2">
          <select name="module" class="form-select form-select-sm">
            <option value="">All Modules</option>
            @foreach($modules as $mod)
              <option value="{{ $mod }}" @selected($module === $mod)>{{ $mod }}</option>
            @endforeach
          </select>
        </div>

        <!-- Role Filter -->
        <div class="col-md-2">
          <select name="role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" @selected($role === $r)>{{ $r }}</option>
            @endforeach
          </select>
        </div>

        <!-- Date Range -->
        <div class="col-md-2">
          <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" placeholder="From date" title="From date">
        </div>

        <!-- Submit & Reset Actions -->
        <div class="col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filters">
            <i class="ph ph-funnel"></i>
          </button>
          <a href="{{ route('accounting.audit-log') }}" class="btn btn-light btn-sm border" title="Reset Filters">
            <i class="ph ph-arrow-counter-clockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Audit Log Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 fw-bold text-dark">
        <i class="ph ph-clock-counter-clockwise text-muted me-1"></i> Audit Trail Records
        <span class="badge bg-light text-dark ms-2 font-monospace">{{ $logs->total() }} total</span>
      </h6>
      <span class="text-muted small">
        <i class="ph ph-info me-1"></i> Records are immutable and digitally time-stamped.
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
        <thead class="table-light">
          <tr>
            <th scope="col" style="width: 160px;">Timestamp (UTC)</th>
            <th scope="col" style="width: 180px;">User &amp; Role</th>
            <th scope="col" style="width: 120px;">Event</th>
            <th scope="col" style="width: 150px;">Module</th>
            <th scope="col">Description</th>
            <th scope="col" style="width: 130px;">IP Address</th>
            <th scope="col" style="width: 80px;" class="text-end">Details</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <!-- Timestamp -->
              <td>
                <div class="fw-semibold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                <div class="text-muted font-monospace small">{{ $log->created_at->format('H:i:s') }}</div>
              </td>

              <!-- User & Role -->
              <td>
                <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;" title="{{ $log->user_name }}">
                  {{ $log->user_name ?? 'System' }}
                </div>
                @php
                  $roleBadge = match($log->user_role) {
                    'CFO' => 'bg-danger-subtle text-danger border-danger-subtle',
                    'FinanceManager' => 'bg-primary-subtle text-primary border-primary-subtle',
                    'StaffAccountant' => 'bg-success-subtle text-success border-success-subtle',
                    'BillingClerk' => 'bg-info-subtle text-info border-info-subtle',
                    'Cashier' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                    'Auditor' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                    default => 'bg-light text-muted border'
                  };
                @endphp
                <span class="badge {{ $roleBadge }} border py-0 px-2" style="font-size: 10px;">
                  {{ $log->user_role ?? 'Guest' }}
                </span>
              </td>

              <!-- Event Badge -->
              <td>
                @php
                  $eventBadge = match($log->event) {
                    'login' => 'bg-success text-white',
                    'logout' => 'bg-secondary text-white',
                    'failed_login' => 'bg-danger text-white',
                    'created' => 'bg-primary text-white',
                    'updated' => 'bg-warning text-dark',
                    'deleted' => 'bg-danger text-white',
                    'posted' => 'bg-info text-white',
                    'reversed' => 'bg-dark text-white',
                    default => 'bg-light text-dark'
                  };
                @endphp
                <span class="badge {{ $eventBadge }} py-1 px-2 font-monospace" style="font-size: 11px;">
                  {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                </span>
              </td>

              <!-- Module -->
              <td>
                <span class="fw-medium text-secondary">
                  <i class="ph ph-folder me-1 text-muted"></i>{{ $log->module }}
                </span>
              </td>

              <!-- Description -->
              <td>
                <div class="text-dark">{{ $log->description }}</div>
                @if($log->auditable_type)
                  <div class="text-muted small font-monospace">
                    Ref: {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                  </div>
                @endif
              </td>

              <!-- IP Address -->
              <td>
                <span class="font-monospace small text-muted">
                  {{ $log->ip_address ?? '127.0.0.1' }}
                </span>
              </td>

              <!-- Diff Details Modal Trigger -->
              <td class="text-end">
                @if(!empty($log->new_values) || !empty($log->old_values))
                  <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#diffModal{{ $log->id }}" title="View State Changes">
                    <i class="ph ph-code"></i>
                  </button>

                  <!-- Modal -->
                  <div class="modal fade" id="diffModal{{ $log->id }}" tabindex="-1" aria-labelledby="diffModalLabel{{ $log->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                      <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                          <h6 class="modal-title fw-bold" id="diffModalLabel{{ $log->id }}">
                            <i class="ph ph-git-diff me-1 text-primary"></i> State Diff: Log #{{ $log->id }} ({{ $log->event }})
                          </h6>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3">
                          <div class="row g-3">
                            <div class="col-md-6">
                              <h6 class="text-muted small fw-bold text-uppercase">Previous Values (Original)</h6>
                              <pre class="bg-light p-3 rounded-3 border small font-monospace text-wrap" style="max-height: 280px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'None (New Record)' }}</pre>
                            </div>
                            <div class="col-md-6">
                              <h6 class="text-muted small fw-bold text-uppercase">New / Modified Values</h6>
                              <pre class="bg-light p-3 rounded-3 border small font-monospace text-wrap" style="max-height: 280px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'None (Deleted Record)' }}</pre>
                            </div>
                          </div>
                          <div class="mt-2 text-muted small">
                            <strong>User Agent:</strong> {{ $log->user_agent ?? 'N/A' }}
                          </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="ph ph-folder-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 fw-medium">No audit log records match the selected filter criteria.</p>
                <a href="{{ route('accounting.audit-log') }}" class="btn btn-link btn-sm mt-2">Reset all filters</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($logs->hasPages())
      <div class="card-footer bg-white py-3 border-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="text-muted small">
            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} records
          </div>
          <div>
            {{ $logs->links() }}
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
