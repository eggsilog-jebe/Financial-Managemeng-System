<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password — Hospital Financial Management System</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    body {
      background-color: #f8fafc;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 1.5rem;
    }
    .auth-card {
      width: 100%;
      max-width: 460px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
      padding: 2.25rem;
    }
    .hospital-header-seal {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 16px;
      margin-bottom: 20px;
      border-bottom: 1px solid #f1f5f9;
    }
    .hospital-seal-icon {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      flex-shrink: 0;
      box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25);
    }
    .hospital-seal-text h4 {
      margin: 0;
      font-size: 0.88rem;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.01em;
    }
    .hospital-seal-text p {
      margin: 0;
      font-size: 0.7rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
  </style>
</head>
<body>

<div class="auth-card">
  <!-- Hospital Seal Header -->
  <div class="hospital-header-seal">
    <div class="hospital-seal-icon">
      <i class="ph ph-shield-check"></i>
    </div>
    <div class="hospital-seal-text">
      <h4>Hospital Financial Management System</h4>
      <p>Security Credential Management</p>
    </div>
  </div>

  <div class="text-center mb-4">
    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle mb-2"
         style="width:52px;height:52px;">
      <i class="ph ph-key fs-3 text-warning"></i>
    </div>
    <h1 class="h4 fw-bold mb-1 text-dark">Password Reset Required</h1>
    <p class="text-muted fs-xs mb-0">
      Your account password has been updated by an administrator. Please establish a new secure password to continue.
    </p>
  </div>

  @if(session('warning'))
    <div class="alert alert-warning border-0 rounded-3 fs-xs mb-3 py-2 px-3 d-flex align-items-center gap-2">
      <i class="ph ph-warning fs-5"></i>
      <span>{{ session('warning') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger border-0 rounded-3 fs-xs mb-3 py-2 px-3 d-flex align-items-center gap-2">
      <i class="ph ph-warning-circle fs-5"></i>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('password.change.update') }}" id="form-change-password">
    @csrf

    <div class="mb-3">
      <label for="password" class="form-label fw-semibold fs-xs text-dark">
        New Password <span class="text-danger">*</span>
      </label>
      <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
             id="password" name="password" required autocomplete="new-password"
             placeholder="Min 8 characters, mixed case, numbers">
      @error('password')
        <div class="invalid-feedback fs-xs">{{ $message }}</div>
      @enderror
      <div class="form-text fs-xxs text-muted mt-1">
        Must contain uppercase, lowercase, numbers, and be at least 8 characters long.
      </div>
    </div>

    <div class="mb-4">
      <label for="password_confirmation" class="form-label fw-semibold fs-xs text-dark">
        Confirm New Password <span class="text-danger">*</span>
      </label>
      <input type="password" class="form-control form-control-sm"
             id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
             placeholder="Re-enter your new password">
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2" id="btn-change-password">
      <i class="ph ph-lock-key me-1"></i> Set New Password &amp; Continue
    </button>
  </form>

  <div class="text-center mt-3 pt-3 border-top">
    <form method="POST" action="{{ route('logout') }}" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-link text-muted fs-xs p-0 border-0 text-decoration-none">
        <i class="ph ph-sign-out me-1"></i> Log out instead
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
