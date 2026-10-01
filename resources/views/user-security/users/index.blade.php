@extends('layouts.app')

@section('title', 'User Accounts — User & Security Management | FMS')
@section('module', 'user-security')
@section('page', 'users')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Personnel &amp; User Accounts
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('user-security.audit-trail') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-clock-countdown text-blue-600"></i>
        <span>Audit Trail</span>
      </a>
      <a 
        href="{{ route('user-security.users.create') }}" 
        id="btn-add-user"
        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 ring-1 ring-blue-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-user-plus"></i>
        <span>Add Personnel User</span>
      </a>
    </div>
  </div>

  <!-- Session Alerts -->
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-check-circle text-lg text-emerald-600"></i>
        <span>{!! session('success') !!}</span>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-xl bg-rose-50 p-4 text-xs font-semibold text-rose-800 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-warning-circle text-lg text-rose-600"></i>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Total Accounts" 
      :value="$metrics['total'] ?? (method_exists($users, 'total') ? $users->total() : $users->count())" 
      :isCurrency="false"
      icon="ph-users" 
      color="blue" 
      subtitle="Registered hospital personnel"
    />
    <x-stat-card 
      title="Active Accounts" 
      :value="$metrics['active'] ?? 0" 
      :isCurrency="false"
      icon="ph-check-circle" 
      color="emerald" 
      subtitle="Authorized for system login"
    />
    <x-stat-card 
      title="Suspended Personnel" 
      :value="$metrics['suspended'] ?? 0" 
      :isCurrency="false"
      icon="ph-lock" 
      color="rose" 
      subtitle="Access temporarily locked"
    />
    <x-stat-card 
      title="Pending Password Reset" 
      :value="$metrics['pending_reset'] ?? 0" 
      :isCurrency="false"
      icon="ph-key" 
      color="amber" 
      subtitle="Forced reset on next login"
    />
  </div>

  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <!-- User Table -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Personnel User</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Role / Access</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Status</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Last Login</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Last Known IP</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($users as $user)
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-3">
                <div 
                  class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl font-bold text-white text-xs shadow-sm"
                  style="background: {{ $user->role === 'CFO' ? '#7c3aed' : ($user->role === 'Auditor' ? '#db2777' : ($user->role === 'FinanceManager' ? '#2563eb' : '#059669')) }};"
                >
                  {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                  <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                  <div class="font-mono text-xs text-slate-400">{{ $user->email }}</div>
                  @if($user->must_change_password)
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 mt-0.5">
                      <i class="ph-bold ph-warning"></i>
                      <span>Must change password</span>
                    </span>
                  @endif
                </div>
              </div>
            </td>
            <td class="py-3.5 px-4">
              @php
                $roleBadge = match($user->role) {
                  'CFO' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                  'FinanceManager' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                  'StaffAccountant' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                  'BillingClerk' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                  'Cashier' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                  'Auditor' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                  default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                };
              @endphp
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $roleBadge }}">
                {{ $user->roleLabel() }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-center">
              @if($user->isActive())
                <x-status-badge status="Active" color="emerald" />
              @else
                <x-status-badge status="Suspended" color="rose" />
              @endif
            </td>
            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
              {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
            </td>
            <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-400">
              {{ $user->last_login_ip ?? '—' }}
            </td>
            <td class="py-3.5 px-4 text-right">
              <div class="inline-flex items-center justify-end gap-1.5">
                <a 
                  href="{{ route('user-security.users.edit', $user) }}"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer" 
                  title="Edit user"
                >
                  <i class="ph ph-pencil text-sm"></i>
                </a>

                <form 
                  method="POST" 
                  action="{{ route('user-security.users.reset-password', $user) }}"
                  onsubmit="return confirm('Reset password for {{ $user->name }}? They will be required to change it on next login.')"
                  class="inline"
                >
                  @csrf
                  <button 
                    type="submit" 
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-600 hover:bg-amber-50 hover:border-amber-200 dark:border-slate-700 dark:hover:bg-amber-950/30 cursor-pointer" 
                    title="Reset password"
                  >
                    <i class="ph ph-key text-sm"></i>
                  </button>
                </form>

                @if($user->id !== auth()->id())
                <form 
                  method="POST" 
                  action="{{ route('user-security.users.toggle-status', $user) }}"
                  onsubmit="return confirm('{{ $user->isActive() ? 'Suspend' : 'Activate' }} account for {{ $user->name }}?')"
                  class="inline"
                >
                  @csrf
                  <button 
                    type="submit" 
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 {{ $user->isActive() ? 'text-rose-600 hover:bg-rose-50 hover:border-rose-200 dark:hover:bg-rose-950/30' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 dark:hover:bg-emerald-950/30' }} dark:border-slate-700 cursor-pointer"
                    title="{{ $user->isActive() ? 'Suspend' : 'Activate' }} account"
                  >
                    <i class="ph {{ $user->isActive() ? 'ph-lock' : 'ph-lock-open' }} text-sm"></i>
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-users text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No users found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer & Pagination -->
    @if(method_exists($users, 'links'))
      <div class="border-t border-slate-200 p-4 dark:border-slate-800">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>
@endsection

