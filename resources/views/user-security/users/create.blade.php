@extends('layouts.app')

@section('title', 'Add New User — User & Security Management')

@section('content')
<div class="container-fluid px-4 py-4">

  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">

      {{-- Breadcrumb --}}
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('user-security.users') }}">User Accounts</a></li>
          <li class="breadcrumb-item active">Add New User</li>
        </ol>
      </nav>

      <h1 class="h3 fw-bold mb-1">Add New User</h1>
      <p class="text-muted fs-xs mb-4">Create a new hospital system user with an assigned role and temporary password.</p>

      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
          <form method="POST" action="{{ route('user-security.users.store') }}" id="form-create-user">
            @csrf

            {{-- Full Name --}}
            <div class="mb-3">
              <label for="name" class="form-label fw-semibold fs-sm">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror"
                     id="name" name="name" value="{{ old('name') }}"
                     placeholder="e.g. Maria Santos" required autocomplete="name">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label fw-semibold fs-sm">Hospital Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control @error('email') is-invalid @enderror"
                     id="email" name="email" value="{{ old('email') }}"
                     placeholder="e.g. accountant@hospital.gov.ph" required autocomplete="email">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Role --}}
            <div class="mb-3">
              <label for="role" class="form-label fw-semibold fs-sm">Assigned Role <span class="text-danger">*</span></label>
              <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>— Select a role —</option>
                <option value="FinanceManager"  {{ old('role') === 'FinanceManager'  ? 'selected' : '' }}>Finance Manager</option>
                <option value="StaffAccountant" {{ old('role') === 'StaffAccountant' ? 'selected' : '' }}>Staff Accountant</option>
                <option value="BillingClerk"    {{ old('role') === 'BillingClerk'    ? 'selected' : '' }}>Billing Clerk</option>
                <option value="Cashier"         {{ old('role') === 'Cashier'         ? 'selected' : '' }}>Cashier</option>
                <option value="Auditor"         {{ old('role') === 'Auditor'         ? 'selected' : '' }}>Internal Auditor</option>
                <option value="CFO"             {{ old('role') === 'CFO'             ? 'selected' : '' }}>CFO (Admin)</option>
              </select>
              @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label fw-semibold fs-sm">Temporary Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control @error('password') is-invalid @enderror"
                     id="password" name="password" required autocomplete="new-password"
                     placeholder="Min 8 chars, letters and numbers">
              <div class="form-text fs-xs text-muted">
                <i class="ph ph-info me-1"></i>The user will be forced to change this password on their first login.
              </div>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Password Confirmation --}}
            <div class="mb-4">
              <label for="password_confirmation" class="form-label fw-semibold fs-sm">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control"
                     id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                     placeholder="Re-enter the password">
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('user-security.users') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary" id="btn-submit-create-user">
                <i class="ph ph-user-plus me-1"></i>Create User
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
