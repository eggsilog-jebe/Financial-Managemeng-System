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
      padding: 24px 28px !important;
      border-radius: 14px !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
    }
    .hospital-header-seal {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 10px;
      margin-bottom: 14px;
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
      width: 100%;
      min-height: 42px !important;
      margin-top: 10px !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
      color: #ffffff !important;
      border: none !important;
      border-radius: 10px !important;
      font-size: 0.88rem !important;
      font-weight: 600 !important;
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25) !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
      cursor: pointer;
    }
    .login-submit:hover:not(:disabled) {
      background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
      box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35) !important;
      transform: translateY(-1px);
      color: #ffffff !important;
    }
    .login-submit:disabled {
      background: #cbd5e1 !important;
      color: #94a3b8 !important;
      box-shadow: none !important;
      cursor: not-allowed;
      transform: none !important;
      opacity: 0.7;
    }
    .login-compliance-footer {
      max-width: 440px;
      margin-top: 14px;
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
        <div class="mt-4 d-flex flex-column gap-2" style="font-size: 0.83rem; color: rgba(255, 255, 255, 0.82);">
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>Enterprise two-factor identity protection</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>Continuous workstation session auditing</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>RA 10173 &amp; HIPAA-compliant gateway</span>
          </div>
        </div>
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

          @if(session('displacement_warning') || request()->has('displaced'))
            @php
              $dispWarning = session('displacement_warning') ?? '⚠️ Security Displacement Alert: Your account was accessed from another computer or workstation. Only one concurrent session is authorized per hospital personnel. Your previous session has been terminated.';
            @endphp
            <div class="alert rounded-3 py-2 px-3 fs-sm border-0 mb-3" role="alert" style="background: #fef2f2; border-left: 3.5px solid #ef4444 !important; color: #991b1b;">
              <div class="d-flex align-items-start gap-2">
                <i class="ph-fill ph-shield-warning text-danger fs-5 flex-shrink-0 mt-1"></i>
                <div>
                  <div class="fw-bold">Session Displaced</div>
                  <div style="font-size: 0.8rem;">{{ $dispWarning }}</div>
                </div>
              </div>
            </div>
          @endif

          @if(session('session_expired'))
            <div class="alert rounded-3 py-2 px-3 fs-sm border-0 mb-3" role="alert" style="background: #fff7ed; border-left: 3px solid #f59e0b !important; color: #92400e;">
              <i class="ph ph-clock-countdown me-1 align-middle"></i>
              {{ session('session_expired') }}
            </div>
          @endif

          @if($errors->any() && !session('displacement_warning'))
            <div class="alert alert-danger rounded-3 py-2 px-3 fs-sm border-0 mb-3" role="alert">
              <i class="ph ph-warning-circle me-1 align-middle"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <form id="login-form" method="POST" action="{{ route('login.post') }}" novalidate>
            @csrf
            <input type="hidden" name="device_uuid" id="login-device-uuid">
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

  <!-- Hospital IT & MIS Helpdesk Support Directory Modal -->
  <div class="modal fade" id="helpdeskModal" tabindex="-1" aria-labelledby="helpdeskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        
        <!-- Modal Header -->
        <div class="modal-header border-bottom pb-3 pt-4 px-4 align-items-center justify-content-between" style="background: #f8fafc; border-color: #f1f5f9 !important;">
          <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 40px; height: 40px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25); font-size: 1.25rem;">
              <i class="ph-fill ph-headset"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0 text-dark" style="font-size: 1.05rem; letter-spacing: -0.01em;" id="helpdeskModalLabel">Hospital IT &amp; MIS Support Directory</h5>
              <p class="mb-0 text-muted" style="font-size: 0.75rem; font-weight: 500;">Authorized Access &amp; Account Provisioning</p>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body p-4">
          <p class="text-secondary mb-3" style="font-size: 0.82rem; line-height: 1.5;">
            To maintain compliance with RA 10173 and hospital internal audit controls, password resets, account unlocks, and workstation permissions are provisioned by the <strong>Hospital MIS Office</strong>:
          </p>

          <!-- Contact Channels List -->
          <div class="d-flex flex-column gap-2 mb-3">
            
            <!-- 1. MIS Helpdesk VOIP -->
            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border" style="background: #ffffff; border-color: #e2e8f0 !important;">
              <div class="d-flex align-items-center gap-2.5 min-w-0 pe-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; font-size: 1.1rem;">
                  <i class="ph-fill ph-phone-call"></i>
                </div>
                <div class="min-w-0">
                  <strong class="d-block text-dark fw-semibold" style="font-size: 0.84rem; line-height: 1.25;">MIS Helpdesk (Hospital LAN)</strong>
                  <small class="text-muted d-block" style="font-size: 0.72rem;">Direct internal VOIP telephone</small>
                </div>
              </div>
              <span class="badge fw-semibold px-2.5 py-1.5 flex-shrink-0" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.76rem; font-variant-numeric: tabular-nums;">
                Ext. 401 / 402
              </span>
            </div>

            <!-- 2. Emergency Night-Shift Admin -->
            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border" style="background: #ffffff; border-color: #e2e8f0 !important;">
              <div class="d-flex align-items-center gap-2.5 min-w-0 pe-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; font-size: 1.1rem;">
                  <i class="ph-fill ph-shield-check"></i>
                </div>
                <div class="min-w-0">
                  <strong class="d-block text-dark fw-semibold" style="font-size: 0.84rem; line-height: 1.25;">Emergency Night-Shift Admin</strong>
                  <small class="text-muted d-block" style="font-size: 0.72rem;">On-duty network supervisor (24/7)</small>
                </div>
              </div>
              <span class="badge fw-semibold px-2.5 py-1.5 flex-shrink-0" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.76rem; font-variant-numeric: tabular-nums;">
                Ext. 405
              </span>
            </div>

            <!-- 3. Official IT Support Email (No Text Wrapping) -->
            <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border" style="background: #ffffff; border-color: #e2e8f0 !important;">
              <div class="d-flex align-items-center gap-2.5 min-w-0 pe-2">
                <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669; font-size: 1.1rem;">
                  <i class="ph-fill ph-envelope-simple"></i>
                </div>
                <div class="min-w-0">
                  <strong class="d-block text-dark fw-semibold" style="font-size: 0.84rem; line-height: 1.25;">Official IT Support Email</strong>
                  <small class="text-muted d-block" style="font-size: 0.72rem;">Identity verification required</small>
                </div>
              </div>
              <a href="mailto:it-support@hospital.gov.ph" class="btn btn-sm fw-semibold text-nowrap d-inline-flex align-items-center gap-1.5 flex-shrink-0 px-2.5 py-1" style="background: #f0fdf4; color: #047857; border: 1px solid #bbf7d0; font-size: 0.76rem; border-radius: 8px; text-decoration: none;" title="Send email to IT Support">
                <i class="ph-fill ph-paper-plane-tilt"></i>
                <span>it-support@hospital.gov.ph</span>
              </a>
            </div>

          </div>

          <!-- Compliance Notice Footer Alert -->
          <div class="alert rounded-3 py-2.5 px-3 mb-0 d-flex gap-2.5 align-items-center" style="font-size: 0.78rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">
            <i class="ph-fill ph-info fs-5 flex-shrink-0" style="color: #059669;"></i>
            <span>Password resets require validation of your hospital employee ID and clinical/administrative department assignment.</span>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4 justify-content-end">
          <button type="button" class="btn btn-sm px-4 fw-medium rounded-3" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
            Close
          </button>
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

      // 4. Persistent Hardware Workstation UUID Binding
      try {
        let deviceUuid = localStorage.getItem('fms_device_uuid');
        if (!deviceUuid) {
          deviceUuid = 'ws-' + ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
          );
          localStorage.setItem('fms_device_uuid', deviceUuid);
        }
        const uuidInput = document.getElementById('login-device-uuid');
        if (uuidInput) uuidInput.value = deviceUuid;
        document.cookie = 'fms_workstation_token=' + deviceUuid + '; path=/; max-age=157680000; SameSite=Lax';
      } catch (_) {}

      // 5. Prevent browser bfcache restoration if navigating back while authenticated
      window.addEventListener('pageshow', (event) => {
        if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
          window.location.reload();
        }
      });
    });
  </script>
</body>
</html>
