@extends('layouts.app')

@section('title', 'Workstation & Session Security — User & Security Management')

@section('content')
<div class="container-fluid px-4 py-4">

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-check-circle fs-4 me-2 text-success"></i>
        <span>{!! session('success') !!}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning fs-4 me-2 text-warning"></i>
        <span>{!! session('warning') !!}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning-circle fs-4 me-2"></i>
        <span>{{ $errors->first() }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('user-security.users') }}">User &amp; Security</a></li>
          <li class="breadcrumb-item active">Workstation &amp; Session Security</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 fw-bold">Workstation &amp; Session Security</h1>
      <p class="text-muted fs-xs mb-0">Hardware computer binding, real-time workstation authorization, and active session displacement control.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('user-security.users') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-users me-1"></i>User Accounts
      </a>
      <a href="{{ route('user-security.audit-trail') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-clock-countdown me-1"></i>Audit Trail
      </a>
    </div>
  </div>

  {{-- Live Metrics Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100 {{ $metrics['pending_count'] > 0 ? 'border-start border-warning border-4' : '' }}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fs-xs text-muted text-uppercase fw-semibold">Pending Authorizations</span>
            @if($metrics['pending_count'] > 0)
              <span class="badge bg-warning text-dark"><i class="ph ph-bell-ringing me-1"></i>Action Required</span>
            @endif
          </div>
          <div class="h3 mb-0 fw-bold text-warning" id="stat-pending-count">{{ $metrics['pending_count'] }}</div>
          <div class="fs-xs text-muted mt-1">Real-time approval requests</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Bound Workstations</div>
          <div class="h3 mb-0 fw-bold text-success">{{ $metrics['approved_count'] }}</div>
          <div class="fs-xs text-muted mt-1">Active authorized computers</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Live Active Sessions</div>
          <div class="h3 mb-0 fw-bold text-primary" id="stat-active-count">{{ $metrics['active_sessions'] }}</div>
          <div class="fs-xs text-muted mt-1">Enforcing single active session</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Hardware Policy</div>
          <div class="h3 mb-0 fw-bold text-dark">1&ndash;3 <span class="fs-sm fw-normal text-muted">per user</span></div>
          <div class="fs-xs text-muted mt-1">Max 3 bound terminals / personnel</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Section 1: Real-Time Pending Workstations --}}
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
          <i class="ph-fill ph-broadcast text-warning"></i>
          Real-Time Pending Workstation Requests
          <span class="badge bg-warning text-dark fs-xs ms-1" id="badge-pending-count">{{ $pendingWorkstations->count() }}</span>
        </h5>
        <p class="text-muted fs-xs mb-0 mt-1">New or unrecognized workstations awaiting Super Admin authorization before login.</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border py-1 px-2 fs-xs">
          <i class="ph ph-arrows-clockwise me-1" style="animation: spin 3s linear infinite;"></i> Live polling
        </span>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="pending-table">
          <thead class="table-light fs-xs text-uppercase text-muted">
            <tr>
              <th class="ps-4">Personnel</th>
              <th>Workstation / OS</th>
              <th>Browser</th>
              <th>IP Address</th>
              <th>Quota Status</th>
              <th>Requested</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody class="fs-sm" id="pending-tbody">
            @forelse($pendingWorkstations as $pw)
              <tr id="pending-row-{{ $pw->id }}">
                <td class="ps-4">
                  <div class="fw-semibold text-dark">{{ $pw->user?->name }}</div>
                  <div class="text-muted fs-xs">{{ $pw->user?->email }} &bull; <span class="badge bg-secondary-subtle text-secondary py-0">{{ $pw->user?->roleLabel() }}</span></div>
                </td>
                <td>
                  <div class="fw-medium text-dark">{{ $pw->workstation_name }}</div>
                  <div class="text-muted fs-xs font-monospace">{{ $pw->platform ?? 'Unknown OS' }}</div>
                </td>
                <td><span class="badge bg-light text-dark border">{{ $pw->browser ?? 'Web Client' }}</span></td>
                <td><code>{{ $pw->ip_address }}</code></td>
                <td>
                  @php $appCount = $pw->user?->approvedWorkstations()->count() ?? 0; @endphp
                  <span class="badge {{ $appCount >= 3 ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }}">
                    {{ $appCount }} / 3 bound
                  </span>
                </td>
                <td class="text-muted fs-xs">{{ $pw->updated_at->diffForHumans() }}</td>
                <td class="text-end pe-4">
                  <div class="d-inline-flex gap-1">
                    {{-- Approve Form --}}
                    <form method="POST" action="{{ route('user-security.workstations.approve', $pw) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success px-3" title="Authorize this workstation" {{ $appCount >= 3 ? 'disabled' : '' }}>
                        <i class="ph ph-check me-1"></i>Approve
                      </button>
                    </form>
                    {{-- Reject Form --}}
                    <form method="POST" action="{{ route('user-security.workstations.reject', $pw) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Reject authorization">
                        <i class="ph ph-x"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr id="pending-empty-row">
                <td colspan="7" class="text-center py-4 text-muted">
                  <i class="ph ph-shield-check fs-2 text-success d-block mb-1"></i>
                  No pending workstation authorization requests. All client terminals are recognized.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Section 2: Bound Workstations Registry --}}
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
          <i class="ph-fill ph-desktop text-success"></i>
          Bound Workstations Registry
        </h5>
        <p class="text-muted fs-xs mb-0 mt-1">Authorized hospital computers locked to specific personnel accounts.</p>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light fs-xs text-uppercase text-muted">
            <tr>
              <th class="ps-4">Personnel</th>
              <th>Workstation Name / Hardware</th>
              <th>IP Address</th>
              <th>Status</th>
              <th>Authorized By</th>
              <th>Last Seen</th>
              <th class="text-end pe-4">Manage</th>
            </tr>
          </thead>
          <tbody class="fs-sm">
            @forelse($boundWorkstations as $bw)
              <tr>
                <td class="ps-4">
                  <div class="fw-semibold text-dark">{{ $bw->user?->name }}</div>
                  <div class="text-muted fs-xs">{{ $bw->user?->roleLabel() }}</div>
                </td>
                <td>
                  <div class="fw-medium text-dark">{{ $bw->workstation_name }}</div>
                  <div class="text-muted fs-xs">{{ $bw->platform }} &bull; {{ $bw->browser }}</div>
                </td>
                <td><code>{{ $bw->ip_address }}</code></td>
                <td>
                  @if($bw->status === 'approved')
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ph-fill ph-check-circle me-1"></i>Authorized</span>
                  @elseif($bw->status === 'revoked')
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="ph ph-prohibit me-1"></i>Revoked</span>
                  @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ph ph-x me-1"></i>Rejected</span>
                  @endif
                </td>
                <td class="fs-xs text-muted">
                  @if($bw->approver)
                    {{ $bw->approver->name }}<br>
                    <span>{{ $bw->approved_at?->format('M d, Y H:i') }}</span>
                  @else
                    &mdash;
                  @endif
                </td>
                <td class="fs-xs text-muted">{{ $bw->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="text-end pe-4">
                  @if($bw->status === 'approved')
                    <form method="POST" action="{{ route('user-security.workstations.revoke', $bw) }}" class="d-inline" onsubmit="return confirm('Revoke authorization for this workstation? Any active sessions on this computer will be terminated immediately.');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke binding">
                        <i class="ph ph-prohibit me-1"></i>Revoke
                      </button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('user-security.workstations.approve', $bw) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Re-authorize workstation">
                        <i class="ph ph-arrows-clockwise me-1"></i>Re-authorize
                      </button>
                    </form>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  No bound workstations found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Section 3: Active Live Sessions & Displacement Monitor --}}
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
          <i class="ph-fill ph-users-three text-primary"></i>
          Active Live Sessions (Single Active Session Enforcement)
        </h5>
        <p class="text-muted fs-xs mb-0 mt-1">Real-time online sessions. Logging in from another machine terminates previous sessions automatically.</p>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light fs-xs text-uppercase text-muted">
            <tr>
              <th class="ps-4">Personnel</th>
              <th>Workstation</th>
              <th>IP Address</th>
              <th>Session ID</th>
              <th>Login Time</th>
              <th>Last Heartbeat</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody class="fs-sm" id="active-sessions-tbody">
            @forelse($activeSessions as $as)
              <tr>
                <td class="ps-4">
                  <div class="fw-semibold text-dark">{{ $as->user?->name }}</div>
                  <div class="text-muted fs-xs">{{ $as->user?->roleLabel() }}</div>
                </td>
                <td>
                  <div class="fw-medium text-dark">{{ $as->device_name }}</div>
                  <div class="text-muted fs-xs">{{ $as->workstation?->platform ?? 'Terminal' }}</div>
                </td>
                <td><code>{{ $as->ip_address }}</code></td>
                <td><code class="fs-xs text-muted">{{ substr($as->session_id, 0, 16) }}...</code></td>
                <td class="fs-xs text-muted">{{ $as->login_at->format('M d, H:i') }}</td>
                <td>
                  <span class="badge bg-success-subtle text-success py-1 px-2 fs-xs">
                    <i class="ph-fill ph-circle text-success me-1" style="font-size:7px;"></i>{{ $as->last_activity_at->diffForHumans() }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <form method="POST" action="{{ route('user-security.sessions.terminate', $as) }}" class="d-inline" onsubmit="return confirm('Forcibly disconnect this session immediately?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Force Disconnect">
                      <i class="ph ph-sign-out me-1"></i>Disconnect
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  No active live sessions found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

{{-- Real-Time Polling Script for Super Admin Panel --}}
<script>
  const pollUrl = '{{ route('user-security.workstations.poll') }}';
  const badgePending = document.getElementById('badge-pending-count');
  const statPending  = document.getElementById('stat-pending-count');
  const statActive   = document.getElementById('stat-active-count');

  let previousPendingCount = {{ $pendingWorkstations->count() }};

  async function pollSuperAdminSecurity() {
    try {
      const response = await fetch(pollUrl, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });

      if (!response.ok) return;

      const data = await response.json();

      if (statPending)  statPending.textContent = data.pending_count;
      if (badgePending) badgePending.textContent = data.pending_count;
      if (statActive)   statActive.textContent = data.active_count;

      // If pending count changed, reload the page to refresh pending requests seamlessly
      if (data.pending_count !== previousPendingCount) {
        previousPendingCount = data.pending_count;
        window.location.reload();
      }
    } catch (_) {}
  }

  setInterval(pollSuperAdminSecurity, 3500);
</script>
@endsection
