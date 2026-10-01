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
  <div class="max-w-2xl mx-auto rounded-2xl bg-white p-6 sm:p-8 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800"
       x-data="{
         pwd: '',
         pwdConfirm: '',
         showPwd: false,
         showConfirm: false,
         submitting: false
       }">

    {{-- Validation Errors Summary Alert --}}
    @if($errors->any())
      <div class="rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-xs text-rose-800 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 mb-6" role="alert">
        <div class="flex items-start gap-3">
          <i class="ph-bold ph-warning-circle text-lg text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
          <div class="space-y-1">
            <strong class="font-bold text-sm block">Unable to create user account:</strong>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('user-security.users.store') }}" id="form-create-user" class="space-y-5" @submit="submitting = true">
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
                 placeholder="e.g. m.santos@hospital.gov.ph" required autocomplete="email">
        </div>
        <p class="mt-1 text-[11px] text-slate-400">Must be a unique hospital domain email address.</p>
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
            <input :type="showPwd ? 'text' : 'password'" 
                   x-model="pwd"
                   class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600 @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                   id="password" name="password" required autocomplete="new-password"
                   placeholder="Min 8 chars, letters &amp; numbers">
            <button 
              type="button" 
              @click="showPwd = !showPwd" 
              :aria-label="showPwd ? 'Hide password' : 'Show password'"
              class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
            >
              <i class="ph text-base" :class="showPwd ? 'ph-eye-slash' : 'ph-eye'"></i>
            </button>
          </div>
          <div class="mt-1.5 space-y-0.5 text-[11px] text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-1.5" :class="pwd.length >= 8 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : ''">
              <i class="ph-bold" :class="pwd.length >= 8 ? 'ph-check-circle' : 'ph-circle text-slate-300 dark:text-slate-600'"></i>
              <span>At least 8 characters</span>
            </div>
            <div class="flex items-center gap-1.5" :class="/[a-zA-Z]/.test(pwd) && /[0-9]/.test(pwd) ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : ''">
              <i class="ph-bold" :class="/[a-zA-Z]/.test(pwd) && /[0-9]/.test(pwd) ? 'ph-check-circle' : 'ph-circle text-slate-300 dark:text-slate-600'"></i>
              <span>Contains both letters and numbers</span>
            </div>
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
            <input :type="showConfirm ? 'text' : 'password'" 
                   x-model="pwdConfirm"
                   class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-600 @if($errors->has('password') && str_contains($errors->first('password'), 'confirmation')) border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @endif"
                   id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                   placeholder="Re-enter temporary password">
            <button 
              type="button" 
              @click="showConfirm = !showConfirm" 
              :aria-label="showConfirm ? 'Hide password' : 'Show password'"
              class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer"
            >
              <i class="ph text-base" :class="showConfirm ? 'ph-eye-slash' : 'ph-eye'"></i>
            </button>
          </div>
          <template x-if="pwdConfirm.length > 0">
            <p class="mt-1.5 text-[11px] flex items-center gap-1.5 font-medium"
               :class="pwd === pwdConfirm ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
              <i class="ph-bold" :class="pwd === pwdConfirm ? 'ph-check-circle' : 'ph-x-circle'"></i>
              <span x-text="pwd === pwdConfirm ? 'Passwords match' : 'Passwords do not match'"></span>
            </p>
          </template>
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
        <button type="submit" 
                :disabled="submitting"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed transition-all cursor-pointer" 
                id="btn-submit-create-user">
          <template x-if="!submitting">
            <span class="inline-flex items-center gap-2">
              <i class="ph ph-user-plus text-sm"></i>
              <span>Create User</span>
            </span>
          </template>
          <template x-if="submitting">
            <span class="inline-flex items-center gap-2">
              <i class="ph-bold ph-spinner animate-spin text-sm"></i>
              <span>Creating User...</span>
            </span>
          </template>
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
