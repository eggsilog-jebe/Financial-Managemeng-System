@extends('layouts.app')

@section('title', 'User Accounts — User & Security Management')

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
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
      <div class="d-flex align-items-center">
        <i class="ph ph-warning-circle fs-4 me-2"></i>
        <span>{{ session('error') }}</span>
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
          <li class="breadcrumb-item">User & Security</li>
          <li class="breadcrumb-item active">User Accounts</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 fw-bold">User Accounts</h1>
      <p class="text-muted fs-xs mb-0">Manage hospital system user accounts, roles, and access credentials.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('user-security.audit-trail') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-clock-countdown me-1"></i>Audit Trail
      </a>
      <a href="{{ route('user-security.users.create') }}" class="btn btn-primary btn-sm" id="btn-add-user">
        <i class="ph ph-user-plus me-1"></i>Add User
      </a>
    </div>
  </div>

  {{-- Stats --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Total Users</div>
          <div class="h4 mb-0 fw-bold">{{ $users->count() }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Active</div>
          <div class="h4 mb-0 fw-bold text-success">{{ $users->where('status', 'active')->count() }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Suspended</div>
          <div class="h4 mb-0 fw-bold text-danger">{{ $users->where('status', 'suspended')->count() }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-3 h-100">
        <div class="card-body">
          <div class="fs-xs text-muted text-uppercase fw-semibold mb-1">Pending Password Reset</div>
          <div class="h4 mb-0 fw-bold text-warning">{{ $users->where('must_change_password', true)->count() }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- User Table --}}
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light border-bottom">
            <tr>
              <th class="ps-4 py-3 fw-semibold fs-xs text-uppercase text-muted">User</th>
              <th class="py-3 fw-semibold fs-xs text-uppercase text-muted">Role</th>
              <th class="py-3 fw-semibold fs-xs text-uppercase text-muted">Status</th>
              <th class="py-3 fw-semibold fs-xs text-uppercase text-muted">Last Login</th>
              <th class="py-3 fw-semibold fs-xs text-uppercase text-muted">Last IP</th>
              <th class="py-3 fw-semibold fs-xs text-uppercase text-muted text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $user)
            <tr>
              <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                       style="width:38px;height:38px;background:{{ $user->role === 'CFO' ? '#6f42c1' : ($user->role === 'Auditor' ? '#d63384' : '#0d6efd') }};font-size:13px;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                  </div>
                  <div>
                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                    <div class="fs-xs text-muted font-monospace">{{ $user->email }}</div>
                    @if($user->must_change_password)
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:10px;">
                        <i class="ph ph-warning me-1"></i>Must change password
                      </span>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                @php
                  $roleColor = match($user->role) {
                    'CFO' => 'bg-purple-subtle text-purple border-purple-subtle',
                    'FinanceManager' => 'bg-primary-subtle text-primary border-primary-subtle',
                    'StaffAccountant' => 'bg-info-subtle text-info border-info-subtle',
                    'BillingClerk' => 'bg-success-subtle text-success border-success-subtle',
                    'Cashier' => 'bg-warning-subtle text-warning border-warning-subtle',
                    'Auditor' => 'bg-danger-subtle text-danger border-danger-subtle',
                    default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                  };
                @endphp
                <span class="badge {{ $roleColor }} border py-1 px-2">{{ $user->roleLabel() }}</span>
              </td>
              <td>
                @if($user->isActive())
                  <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="ph ph-check-circle me-1"></i>Active
                  </span>
                @else
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                    <i class="ph ph-prohibit me-1"></i>Suspended
                  </span>
                @endif
              </td>
              <td class="fs-xs text-muted">
                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
              </td>
              <td class="fs-xs font-monospace text-muted">
                {{ $user->last_login_ip ?? '—' }}
              </td>
              <td class="text-end pe-4">
                <div class="d-flex justify-content-end gap-1">
                  {{-- Edit --}}
                  <a href="{{ route('user-security.users.edit', $user) }}"
                     class="btn btn-outline-secondary btn-sm py-1 px-2" title="Edit user">
                    <i class="ph ph-pencil"></i>
                  </a>

                  {{-- Reset Password --}}
                  <form method="POST" action="{{ route('user-security.users.reset-password', $user) }}"
                        onsubmit="return confirm('Reset password for {{ $user->name }}? They will be required to change it on next login.')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm py-1 px-2" title="Reset password">
                      <i class="ph ph-key"></i>
                    </button>
                  </form>

                  {{-- Suspend / Activate --}}
                  @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('user-security.users.toggle-status', $user) }}"
                        onsubmit="return confirm('{{ $user->isActive() ? 'Suspend' : 'Activate' }} account for {{ $user->name }}?')">
                    @csrf
                    <button type="submit"
                            class="btn btn-sm py-1 px-2 {{ $user->isActive() ? 'btn-outline-danger' : 'btn-outline-success' }}"
                            title="{{ $user->isActive() ? 'Suspend' : 'Activate' }} account">
                      <i class="ph {{ $user->isActive() ? 'ph-lock' : 'ph-lock-open' }}"></i>
                    </button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="ph ph-users fs-1 d-block mb-2 opacity-25"></i>
                No users found.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
