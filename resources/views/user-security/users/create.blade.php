@extends('layouts.app')

@section('title', 'Add New User — User & Security Management')

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
        <span class="text-slate-900 dark:text-slate-200 font-semibold">Add New User</span>
      </nav>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Add New User
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
    <form method="POST" action="{{ route('user-security.users.store') }}" id="form-create-user" class="space-y-5">
      @csrf

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
                 id="name" name="name" value="{{ old('name') }}"
                 placeholder="e.g. Maria Santos, CPA" required autocomplete="name">
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
                 id="email" name="email" value="{{ old('email') }}"
                 placeholder="e.g. accountant@hospital.gov.ph" required autocomplete="email">
        </div>
        @error('email')
          <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
        @enderror
      </div>

      {{-- Role Selector --}}
      <div>
        <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
          Assigned System Role <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="ph ph-shield-star"></i>
          </div>
          <select class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-8 text-xs text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white @error('role') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" id="role" name="role" required>
            <option value="" disabled {{ old('role') ? '' : 'selected' }}>— Select an authorization role —</option>
            <option value="FinanceManager"  {{ old('role') === 'FinanceManager'  ? 'selected' : '' }}>Finance Manager</option>
            <option value="StaffAccountant" {{ old('role') === 'StaffAccountant' ? 'selected' : '' }}>Staff Accountant</option>
            <option value="BillingClerk"    {{ old('role') === 'BillingClerk'    ? 'selected' : '' }}>Billing Clerk</option>
            <option value="Cashier"         {{ old('role') === 'Cashier'         ? 'selected' : '' }}>Cashier</option>
            <option value="Auditor"         {{ old('role') === 'Auditor'         ? 'selected' : '' }}>Internal Auditor</option>
            <option value="CFO"             {{ old('role') === 'CFO'             ? 'selected' : '' }}>CFO (Executive Administrator)</option>
          </select>
        </div>
        @error('role')
          <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
        @enderror
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
        {{-- Password --}}
        <div>
          <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Temporary Password <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="ph ph-lock-key"></i>
            </div>
            <input type="password" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600 @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                   id="password" name="password" required autocomplete="new-password"
                   placeholder="Min 8 chars, alpha-numeric">
          </div>
          @error('password')
            <p class="mt-1 text-xs text-rose-500 flex items-center gap-1"><i class="ph ph-warning-circle"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
          <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Confirm Password <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
              <i class="ph ph-check-square-offset"></i>
            </div>
            <input type="password" class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600"
                   id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                   placeholder="Re-enter password">
          </div>
        </div>
      </div>

      {{-- Security Notice --}}
      <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3.5 dark:border-blue-900/40 dark:bg-blue-950/20">
        <div class="flex items-start gap-2.5">
          <i class="ph ph-info text-blue-600 dark:text-blue-400 text-base mt-0.5 shrink-0"></i>
          <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
            The user will be required to change their temporary password upon initial sign-in and will be bound to their authorized terminal workstation upon approval.
          </p>
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
        <a href="{{ route('user-security.users') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 transition-all">
          Cancel
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all" id="btn-submit-create-user">
          <i class="ph ph-user-plus text-sm"></i>
          Create User
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
