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
    /* Enterprise Polish & Ergonomic Input Styles */
    .input-icon-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;
    }
    .input-icon-wrapper .input-leading-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 1.15rem;
      pointer-events: none;
      z-index: 2;
      transition: color 0.15s ease-in-out;
      line-height: 1;
    }
    .input-icon-wrapper input {
      padding-left: 42px !important;
      padding-right: 14px;
      width: 100%;
    }
    .input-icon-wrapper.password-field input {
      padding-right: 48px !important;
    }
    .input-icon-wrapper input:focus ~ .input-leading-icon,
    .input-icon-wrapper:focus-within .input-leading-icon {
      color: #059669;
    }
    /* Password Sub-Row (Directly Below Password Field) */
    .password-sub-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      min-height: 22px;
      margin-top: 6px;
    }
    .password-sub-row .forgot-password-link {
      margin-left: auto;
      font-size: 12px;
      font-weight: 500;
      line-height: 1.4;
      color: #059669;
      text-decoration: none;
      transition: color 0.15s ease;
    }
    .password-sub-row .forgot-password-link:hover {
      color: #047857;
      text-decoration: underline;
    }
    .caps-lock-badge {
      display: none;
      align-items: center;
      gap: 6px;
      font-size: 0.74rem;
      color: #b45309;
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 6px;
      padding: 4px 10px;
      margin-top: 6px;
      font-weight: 500;
      animation: fadeIn 0.15s ease-in-out;
    }
    .login-panel {
      padding: 20px 24px !important;
    }
    .login-card {
      padding: 22px 26px !important;
      border-radius: 12px !important;
    }
    .hospital-header-seal {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 10px;
      margin-bottom: 12px;
      border-bottom: 1px solid #f1f5f9;
    }
    .hospital-seal-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      flex-shrink: 0;
      box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
    }
    .hospital-seal-text h4 {
      margin: 0;
      font-size: 0.82rem;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.01em;
    }
    .hospital-seal-text p {
      margin: 0;
      font-size: 0.67rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .login-card h2 {
      font-size: 20px !important;
    }
    .login-card-header .page-kicker {
      font-size: 11px !important;
      margin-bottom: 2px !important;
    }
    .login-help {
      margin: 4px 0 14px !important;
      font-size: 12px !important;
    }
    .form-field {
      margin-bottom: 11px !important;
      gap: 4px !important;
    }
    .form-field label {
      font-size: 12px !important;
    }
    .form-field input {
      min-height: 40px !important;
      font-size: 13.5px !important;
    }
    .login-submit {
      min-height: 40px !important;
      margin-top: 10px !important;
      font-size: 13.5px !important;
    }
    .login-support {
      margin-top: 12px !important;
      padding-top: 10px !important;
    }
    .login-compliance-footer {
      max-width: 440px;
      margin-top: 12px;
      padding: 0 10px;
      text-align: center;
    }
    .login-compliance-footer p {
      font-size: 0.70rem;
      line-height: 1.45;
      color: #64748b;
      margin-bottom: 4px;
    }
    .login-compliance-meta {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 0.67rem;
      color: #94a3b8;
      font-weight: 500;
      letter-spacing: 0.02em;
    }
    .support-trigger-btn {
      background: none;
      border: none;
      padding: 0;
      color: inherit;
      text-align: left;
      font: inherit;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
    }
    .support-trigger-btn:hover span.support-link {
      text-decoration: underline;
      color: #059669;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-2px); }
      to { opacity: 1; transform: translateY(0); }
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
      </div>
      <footer class="login-brand-footer">
        <span>HIMS Command Center</span>
        <span>Version 0.9</span>
      </footer>
    </section>

    <!-- Sign In Panel -->
    <section class="login-panel" aria-labelledby="login-title">
      <div class="login-panel-container d-flex flex-column align-items-center w-100" style="max-width: 440px;">
        <div class="login-card w-100">
          
          <!-- Official Hospital / DOH System Seal -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon" aria-hidden="true">
              <i class="ph-fill ph-hospital"></i>
            </div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>Department of Health • Public Hospital Network</p>
            </div>
          </div>

          <header class="login-card-header">
            <p class="page-kicker">Welcome back</p>
            <h2 id="login-title">Sign in to HIMS</h2>
            <p class="login-help">Use your authorized hospital domain credentials to access the financial portal.</p>
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
              <div class="input-icon-wrapper">
                <input id="login-email" name="email" type="email" autocomplete="email" placeholder="name@hospital.gov.ph" value="{{ old('email') }}" maxlength="255" required autofocus>
                <i class="ph ph-envelope-simple input-leading-icon" aria-hidden="true"></i>
              </div>
            </div>

            <div class="form-field">
              <label for="login-password">Password</label>
              <div class="input-icon-wrapper password-field">
                <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" maxlength="128" required>
                <i class="ph ph-lock-key input-leading-icon" aria-hidden="true"></i>
                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                  <i class="ph ph-eye" aria-hidden="true"></i>
                </button>
              </div>
              <!-- Sub-row below password field: Caps Lock Warning + Forgot Password Link -->
              <div class="password-sub-row">
                <div id="caps-lock-warning" class="caps-lock-badge" role="alert" aria-live="polite">
                  <i class="ph-fill ph-warning"></i>
                  <span>Caps Lock is ON</span>
                </div>
                <a href="#helpdeskModal" data-bs-toggle="modal" class="forgot-password-link">Forgot password?</a>
              </div>
            </div>

            <button class="btn-primary login-submit mt-3" type="submit">
              <i class="ph ph-sign-in" aria-hidden="true"></i>
              Sign in
            </button>
          </form>

          <div class="login-support" aria-label="Sign-in help">
            <button type="button" class="support-trigger-btn" data-bs-toggle="modal" data-bs-target="#helpdeskModal">
              <i class="ph ph-question fs-5 text-muted" aria-hidden="true"></i>
              <p class="mb-0"><strong>Need access help?</strong> <span class="support-link">Contact your hospital system administrator.</span></p>
            </button>
          </div>
        </div>

        <!-- Statutory Compliance & Security Notice (Muted Enterprise Footer) -->
        <div class="login-compliance-footer w-100">
          <p>
            <i class="ph-fill ph-shield-check me-1 text-secondary align-middle"></i>
            <strong>Notice under RA 10173 &amp; RA 10175:</strong> Authorized hospital personnel only. Unauthorized access, disclosure, or alteration of patient financial data is strictly prohibited by law.
          </p>
          <div class="login-compliance-meta">
            <span>Authorized Access Only</span>
            <span>&bull;</span>
            <span>FMS Transaction Core</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Hospital IT / MIS Helpdesk Directory Modal -->
  <div class="modal fade" id="helpdeskModal" tabindex="-1" aria-labelledby="helpdeskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-bottom border-light-subtle pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="hospital-seal-icon" style="width: 32px; height: 32px; font-size: 1.1rem;">
              <i class="ph-fill ph-headset"></i>
            </div>
            <h5 class="modal-title fs-6 fw-bold text-dark mb-0" id="helpdeskModalLabel">Hospital IT & MIS Support Directory</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 fs-sm">
          <p class="text-secondary mb-3">For password resets, account unlocks, or workstation role provisioning, contact the Hospital Management Information System (MIS) office:</p>
          <div class="list-group list-group-flush rounded-3 border mb-3">
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
              <div>
                <strong class="d-block text-dark">MIS Helpdesk (Hospital LAN)</strong>
                <small class="text-muted">Direct VOIP Extension</small>
              </div>
              <span class="badge px-2 py-1 fs-xs fw-semibold" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">Ext. 401 / 402</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
              <div>
                <strong class="d-block text-dark">Emergency Night-Shift Admin</strong>
                <small class="text-muted">On-Duty Network Supervisor</small>
              </div>
              <span class="badge px-2 py-1 fs-xs fw-semibold" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">Ext. 405</span>
            </div>
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
              <div>
                <strong class="d-block text-dark">Official IT Support Email</strong>
                <small class="text-muted">Employee identity verification required</small>
              </div>
              <a href="mailto:it-support@hospital.gov.ph" class="text-success text-decoration-none fw-medium">it-support@hospital.gov.ph</a>
            </div>
          </div>
          <div class="alert alert-light border py-2 px-3 rounded-3 mb-0 d-flex gap-2 align-items-center" style="font-size: 0.78rem; background: #f8fafc;">
            <i class="ph-fill ph-info fs-5 flex-shrink-0 text-primary"></i>
            <span>Password resets require validation of your hospital employee ID and clinical/administrative department assignment.</span>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggle = document.querySelector('[data-password-toggle]');
      const password = document.getElementById('login-password');
      const capsWarning = document.getElementById('caps-lock-warning');
      let maskTimer = null;

      // 1. Password Visibility Toggle with 5-Second Auto-Masking (Anti-Shoulder Surfing)
      if (toggle && password) {
        const revertToPassword = () => {
          password.type = 'password';
          toggle.setAttribute('aria-pressed', 'false');
          toggle.setAttribute('aria-label', 'Show password');
          const icon = toggle.querySelector('i');
          if (icon) {
            icon.className = 'ph ph-eye';
          }
          if (maskTimer) {
            clearTimeout(maskTimer);
            maskTimer = null;
          }
        };

        toggle.addEventListener('click', () => {
          const isCurrentlyPassword = password.type === 'password';
          
          if (isCurrentlyPassword) {
            password.type = 'text';
            toggle.setAttribute('aria-pressed', 'true');
            toggle.setAttribute('aria-label', 'Hide password');
            const icon = toggle.querySelector('i');
            if (icon) {
              icon.className = 'ph ph-eye-slash';
            }

            // Auto-revert back to masked dots after 5 seconds
            if (maskTimer) clearTimeout(maskTimer);
            maskTimer = setTimeout(revertToPassword, 5000);
          } else {
            revertToPassword();
          }
        });

        // Revert instantly on blur (if staff tabs out or clicks away)
        password.addEventListener('blur', () => {
          if (password.type === 'text') {
            revertToPassword();
          }
          if (capsWarning) {
            capsWarning.style.display = 'none';
          }
        });

        // 2. Caps Lock Detection Warning
        const checkCapsLock = (e) => {
          if (e.getModifierState && e.getModifierState('CapsLock')) {
            capsWarning.style.display = 'flex';
          } else {
            capsWarning.style.display = 'none';
          }
        };

        password.addEventListener('keydown', checkCapsLock);
        password.addEventListener('keyup', checkCapsLock);
      }

      // 3. Prevent double-submission and provide feedback
      const form = document.getElementById('login-form');
      const submitBtn = document.querySelector('.login-submit');
      if (form && submitBtn) {
        form.addEventListener('submit', () => {
          if (form.checkValidity()) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Authenticating...';
            form.submit();
          }
        });
      }
    });
  </script>
</body>
</html>
