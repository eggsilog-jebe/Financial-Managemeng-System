<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in - HIMS Main System</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    .quick-role-btn {
      display: flex;
      flex-direction: column;
      text-align: left;
      padding: 10px 12px;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      background: var(--color-surface-muted);
      text-decoration: none;
      transition: all var(--transition);
    }
    .quick-role-btn:hover {
      background: var(--color-primary-soft);
      border-color: var(--color-primary);
      transform: translateY(-2px);
    }
  </style>
</head>
<body class="login-page">

  <main class="login-layout">
    <!-- Brand / Hero Section -->
    <section class="login-brand" aria-label="HIMS Main System">
      <div class="login-brand-content">
        <div class="login-brand-mark" aria-hidden="true">
          <i class="ph-fill ph-cross"></i>
        </div>
        <p class="login-kicker">Hospital Information Management System</p>
        <h1>HIMS Main System</h1>
        <p class="login-brand-description">One connected workspace for hospital operations and financial management modules.</p>
        <ul class="login-brand-signals" aria-label="System trust information">
          <li><i class="ph-fill ph-shield-check" aria-hidden="true"></i><span>Secure Authentication</span></li>
          <li><i class="ph-fill ph-identification-card" aria-hidden="true"></i><span>Role-Based Access Control</span></li>
          <li><i class="ph-fill ph-buildings" aria-hidden="true"></i><span>Centralized Hospital Operations</span></li>
        </ul>
      </div>
      <footer class="login-brand-footer">
        <span>HIMS Command Center</span>
        <span>Version 0.9</span>
      </footer>
    </section>

    <!-- Sign In Panel -->
    <section class="login-panel" aria-labelledby="login-title">
      <div class="login-card">
        <header class="login-card-header">
          <p class="page-kicker">Welcome back</p>
          <h2 id="login-title">Sign in to HIMS</h2>
          <p class="login-help">Use your hospital access credentials or select a quick demo profile below.</p>
        </header>

        @if($errors->any())
          <div class="alert alert-danger rounded-3 py-2 px-3 fs-sm border-0 mb-3" role="alert">
            <i class="ph ph-warning-circle me-1 align-middle"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login.post') }}" novalidate>
          @csrf
          <div class="form-field">
            <label for="login-email">Email address</label>
            <input id="login-email" name="email" type="email" autocomplete="email" placeholder="name@hospital.org" value="{{ old('email', 'cfo@hospital.local') }}" required autofocus>
          </div>

          <div class="form-field">
            <label for="login-password">Password</label>
            <div class="password-field">
              <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" value="password" required aria-describedby="password-note">
              <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                <i class="ph ph-eye" aria-hidden="true"></i>
              </button>
            </div>
            <small id="password-note">Default demo password is <strong>password</strong></small>
          </div>

          <label class="remember-field">
            <input id="remember-email" name="remember" type="checkbox" checked>
            <span>Remember session on this device</span>
          </label>

          <button class="btn-primary login-submit" type="submit">
            <i class="ph ph-sign-in" aria-hidden="true"></i>
            Sign in
          </button>
        </form>

        <!-- 1-Click Instant Demo Logins -->
        <div class="mt-4 pt-3 border-top">
          <p class="text-uppercase fw-semibold text-muted mb-2" style="font-size: 11px; letter-spacing: 0.05em;">
            <i class="ph-fill ph-lightning text-warning me-1"></i> Instant 1-Click Demo Login:
          </p>
          <div class="row g-2">
            <div class="col-6">
              <a href="{{ route('login.quick', 'cfo') }}" class="quick-role-btn">
                <strong class="text-dark" style="font-size: 12px;"><i class="ph-bold ph-shield-check text-primary me-1"></i>CFO Executive</strong>
                <span class="text-muted" style="font-size: 10px;">Full access &amp; locks</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'accountant') }}" class="quick-role-btn">
                <strong class="text-dark" style="font-size: 12px;"><i class="ph-bold ph-book-open text-success me-1"></i>Staff Accountant</strong>
                <span class="text-muted" style="font-size: 10px;">GL, AP/AR, Reports</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'cashier') }}" class="quick-role-btn">
                <strong class="text-dark" style="font-size: 12px;"><i class="ph-bold ph-hand-coins text-warning me-1"></i>Cashier Supervisor</strong>
                <span class="text-muted" style="font-size: 10px;">POS Desk &amp; Receipts</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'auditor') }}" class="quick-role-btn">
                <strong class="text-dark" style="font-size: 12px;"><i class="ph-bold ph-file-search text-info me-1"></i>BIR CAS Auditor</strong>
                <span class="text-muted" style="font-size: 10px;">Read-only Audit Log</span>
              </a>
            </div>
          </div>
        </div>

        <div class="login-support" aria-label="Sign-in help">
          <i class="ph ph-question" aria-hidden="true"></i>
          <p><strong>Need access help?</strong><span>Contact your hospital system administrator.</span></p>
        </div>

        <footer class="login-card-footer">
          <span>Authorized personnel only</span>
          <span>IHIMS Transaction Core</span>
        </footer>
      </div>
    </section>
  </main>

  <script>
    // Password toggle script
    document.addEventListener('DOMContentLoaded', () => {
      const toggle = document.querySelector('[data-password-toggle]');
      const password = document.getElementById('login-password');
      if (toggle && password) {
        toggle.addEventListener('click', () => {
          const isPass = password.type === 'password';
          password.type = isPass ? 'text' : 'password';
          toggle.setAttribute('aria-pressed', String(isPass));
          toggle.setAttribute('aria-label', isPass ? 'Hide password' : 'Show password');
          const icon = toggle.querySelector('i');
          if (icon) {
            icon.className = isPass ? 'ph ph-eye-slash' : 'ph ph-eye';
          }
        });
      }
    });
  </script>
</body>
</html>
