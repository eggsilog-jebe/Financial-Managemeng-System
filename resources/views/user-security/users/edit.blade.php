@extends('layouts.app')

@section('title', "Edit User: {$user->name} — User & Security Management")

@section('content')
<div class="space-y-6">

  {{-- Breadcrumbs & Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">
        <a href="{{ route('accounting.dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Overview</a>
        <i class="ph ph-caret-right text-[10px]"></i>
        <a href="{{ route('user-security.users') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">User Accounts</a>
        <i class="ph ph-caret-right text-[10px]"></i>
        <span class="text-slate-900 dark:text-slate-200 font-semibold">Edit User</span>
      </nav>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Edit User Profile
      </h1>
    </div>
    <div>
      <a href="{{ route('user-security.users') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 transition-all">
        <i class="ph ph-arrow-left"></i>
        Back to Users
      </a>
    </div>
  </div>

  {{-- Form Card --}}
  <div class="max-w-2xl mx-auto rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">

    {{-- Current Info Badge --}}
    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4 mb-6 dark:border-slate-800 dark:bg-slate-950/60">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow-sm text-sm">
        {{ strtoupper(substr($user->name, 0, 2)) }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</div>
        <div class="text-xs font-mono text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</div>
        <div class="mt-1.5 flex items-center gap-2">
          <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-950/60 dark:text-blue-300 dark:ring-blue-500/20">
            <i class="ph ph-shield-check text-[10px]"></i>
            {{ $user->roleLabel() }}
          </span>
          <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-mono">
            ID #{{ $user->id }}
          </span>
        </div>
      </div>
    </div>

    {{-- Validation Errors Summary Alert --}}
    @if($errors->any())
      <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-xs text-rose-800 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 mb-6" role="alert">
        <div class="flex items-start gap-3">
          <i class="ph-bold ph-warning-circle text-lg text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
          <div class="space-y-1">
            <strong class="font-bold text-sm block">Unable to update user profile:</strong>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('user-security.users.update', $user) }}" id="form-edit-user" class="space-y-5">
      @csrf
      @method('PATCH')

      {{-- Full Name --}}
      <div>
        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
          Full Name <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="ph ph-user"></i>
          </div>
          <input type="text" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600 @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                 id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        @error('name')
          <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
        @enderror
      </div>

      {{-- Hospital Email --}}
      <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
          Hospital Email Address <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="ph ph-envelope-simple"></i>
          </div>
          <input type="email" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-xs font-mono text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600 @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                 id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        @error('email')
          <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
        @enderror
      </div>

      {{-- Assigned Role --}}
      <div>
        <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
          Assigned System Role <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="ph ph-shield-star"></i>
          </div>
          <select class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-8 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white @error('role') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" id="role" name="role" required>
            <option value="FinanceManager"  {{ old('role', $user->role) === 'FinanceManager'  ? 'selected' : '' }}>Finance Manager</option>
            <option value="StaffAccountant" {{ old('role', $user->role) === 'StaffAccountant' ? 'selected' : '' }}>Staff Accountant</option>
            <option value="BillingClerk"    {{ old('role', $user->role) === 'BillingClerk'    ? 'selected' : '' }}>Billing Clerk</option>
            <option value="Cashier"         {{ old('role', $user->role) === 'Cashier'         ? 'selected' : '' }}>Cashier</option>
            <option value="Auditor"         {{ old('role', $user->role) === 'Auditor'         ? 'selected' : '' }}>Internal Auditor</option>
            <option value="CFO"             {{ old('role', $user->role) === 'CFO'             ? 'selected' : '' }}>CFO (Executive Administrator)</option>
          </select>
        </div>
        @error('role')
          <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
        @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
        <a href="{{ route('user-security.users') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 transition-all">
          Cancel
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all" id="btn-submit-edit-user">
          <i class="ph ph-floppy-disk text-sm"></i>
          Save Changes
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
