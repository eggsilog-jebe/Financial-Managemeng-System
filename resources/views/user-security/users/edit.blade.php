@extends('layouts.app')

@section('title', "Edit User: {$user->name} — User & Security Management")

@section('content')
<div class="container-fluid px-4 py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">

      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item"><a href="{{ route('user-security.users') }}">User Accounts</a></li>
          <li class="breadcrumb-item active">Edit User</li>
        </ol>
      </nav>

      <h1 class="h3 fw-bold mb-1">Edit User</h1>
      <p class="text-muted fs-xs mb-4">Modify the name, email, or role for this hospital system account.</p>

      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">

          {{-- Current Info Badge --}}
          <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                 style="width:46px;height:46px;background:#0d6efd;font-size:14px;">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
              <div class="fw-semibold">{{ $user->name }}</div>
              <div class="fs-xs font-monospace text-muted">{{ $user->email }}</div>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1">{{ $user->roleLabel() }}</span>
            </div>
          </div>

          <form method="POST" action="{{ route('user-security.users.update', $user) }}" id="form-edit-user">
            @csrf
            @method('PATCH')

            <div class="mb-3">
              <label for="name" class="form-label fw-semibold fs-sm">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror"
                     id="name" name="name" value="{{ old('name', $user->name) }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-semibold fs-sm">Hospital Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control @error('email') is-invalid @enderror"
                     id="email" name="email" value="{{ old('email', $user->email) }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
              <label for="role" class="form-label fw-semibold fs-sm">Assigned Role <span class="text-danger">*</span></label>
              <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                <option value="FinanceManager"  {{ old('role', $user->role) === 'FinanceManager'  ? 'selected' : '' }}>Finance Manager</option>
                <option value="StaffAccountant" {{ old('role', $user->role) === 'StaffAccountant' ? 'selected' : '' }}>Staff Accountant</option>
                <option value="BillingClerk"    {{ old('role', $user->role) === 'BillingClerk'    ? 'selected' : '' }}>Billing Clerk</option>
                <option value="Cashier"         {{ old('role', $user->role) === 'Cashier'         ? 'selected' : '' }}>Cashier</option>
                <option value="Auditor"         {{ old('role', $user->role) === 'Auditor'         ? 'selected' : '' }}>Internal Auditor</option>
                <option value="CFO"             {{ old('role', $user->role) === 'CFO'             ? 'selected' : '' }}>CFO (Admin)</option>
              </select>
              @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('user-security.users') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary" id="btn-submit-edit-user">
                <i class="ph ph-floppy-disk me-1"></i>Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
