<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Security Verification &mdash; Two-Factor Authentication &mdash; HIMS</title>
  <meta name="description" content="Enter the 6-digit code from Google Authenticator on your mobile phone to proceed.">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    body.login-page {
      background: #f8fafc;
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .login-panel { padding: 24px !important; }
    .login-card {
      padding: 32px 30px !important;
      border-radius: 16px !important;
      border: 1px solid #eef2f6 !important;
      background: #ffffff !important;
      box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.07), 0 4px 6px -2px rgba(15, 23, 42, 0.03) !important;
    }

    /* ─── Hospital Header Seal ─────────────────────────────────────────── */
    .hospital-header-seal {
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 12px;
      margin-bottom: 16px;
      border-bottom: 1px solid #f1f5f9;
    }
    .hospital-seal-icon {
      width: 36px;
      height: 36px;
      border-radius: 9px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
      box-shadow: 0 2px 5px rgba(5, 150, 105, 0.25);
    }
    .hospital-seal-text h4 {
      margin: 0;
      font-size: 0.84rem;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.01em;
    }
    .hospital-seal-text p {
      margin: 0;
      font-size: 0.68rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    /* ─── Top Badge: TWO-FACTOR AUTHENTICATION ───────────────────────────── */
    .badge-2fa-pill {
      display: inline-flex;
      align-items: center;
      background: #ecfdf5;
      color: #047857;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      padding: 5px 12px;
      border-radius: 6px;
      text-transform: uppercase;
      border: 1px solid #a7f3d0;
    }

    /* ─── Main Headings ──────────────────────────────────────────────────── */
    .verification-title {
      font-size: 1.65rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.025em;
      line-height: 1.25;
    }
    .verification-subtitle {
      font-size: 0.875rem;
      color: #64748b;
      line-height: 1.5;
    }

    /* ─── Workstation Notice Banner (Hospital Card) ─────────────────────── */
    .workstation-banner {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 12px;
      padding: 13px 16px;
    }
    .workstation-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: #059669;
      flex-shrink: 0;
      box-shadow: 0 0 0 2.5px rgba(5, 150, 105, 0.2);
    }
    .workstation-banner-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: #065f46;
      line-height: 1.3;
    }
    .workstation-banner-desc {
      font-size: 0.8rem;
      color: #047857;
      margin-top: 2px;
      line-height: 1.35;
    }

    /* ─── Authenticator Code Label Row ──────────────────────────────────── */
    .auth-code-label {
      font-size: 0.9rem;
      font-weight: 700;
      color: #1f2937;
    }
    .totp-refresh-timer {
      font-size: 0.8rem;
      color: #94a3b8;
      font-variant-numeric: tabular-nums;
    }
    .totp-refresh-timer strong {
      color: #0f172a;
      font-weight: 700;
    }

    /* ─── 6 Digit OTP Input Boxes ────────────────────────────────────────── */
    .otp-input-group {
      display: flex;
      gap: 10px;
      justify-content: space-between;
    }
    .otp-digit {
      width: 50px;
      height: 58px;
      text-align: center;
      font-size: 1.65rem;
      font-weight: 700;
      font-family: 'Poppins', sans-serif;
      font-variant-numeric: tabular-nums;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.1s;
      color: #0f172a;
      background: #f8fafc;
      caret-color: transparent;
    }
    .otp-digit:focus {
      border-color: #059669;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
      background: #ffffff;
      transform: translateY(-1px);
    }
    .otp-digit.filled {
      border-color: #059669;
      background: #f0fdf4;
      color: #065f46;
    }
    .otp-digit.error {
      border-color: #ef4444;
      background: #fef2f2;
      color: #dc2626;
      animation: shake 0.35s ease-in-out;
    }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20% { transform: translateX(-6px); }
      60% { transform: translateX(6px); }
    }

    /* ─── Silky Smooth 60fps TOTP Countdown Progress Bar ─────────────────── */
    .totp-progress-track {
      width: 100%;
      height: 4px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
      margin-top: 12px;
      margin-bottom: 22px;
    }
    .totp-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #10b981 0%, #059669 100%);
      border-radius: 999px;
      width: 100%;
      will-change: width;
    }

    /* ─── Action Button: Verify & Enter Workspace → ──────────────────────── */
    .login-submit-primary {
      width: 100%;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
      color: #ffffff !important;
      border: none !important;
      border-radius: 12px !important;
      font-size: 0.95rem !important;
      font-weight: 600 !important;
      box-shadow: 0 4px 14px rgba(5, 150, 105, 0.28) !important;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
      cursor: pointer;
      text-decoration: none;
    }
    .login-submit-primary:hover:not(:disabled) {
      background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
      box-shadow: 0 6px 18px rgba(5, 150, 105, 0.38) !important;
      transform: translateY(-1px);
      color: #ffffff !important;
    }
    .login-submit-primary:disabled {
      background: #cbd5e1 !important;
      color: #94a3b8 !important;
      box-shadow: none !important;
      cursor: not-allowed;
      transform: none !important;
      opacity: 0.75;
    }

    /* ─── Back to Login Link ─────────────────────────────────────────────── */
    .back-to-login-link {
      background: none;
      border: none;
      color: #64748b;
      font-size: 0.88rem;
      font-weight: 500;
      cursor: pointer;
      padding: 0;
      text-decoration: none;
      transition: color 0.15s;
    }
    .back-to-login-link:hover {
      color: #059669;
      text-decoration: underline;
    }
  </style>
</head>
<body class="login-page">

  <main class="login-layout">
    <!-- Brand / Hero Section -->
    <section class="login-brand" aria-label="HIMS Main System">
      <div class="login-brand-content">
        <div class="login-brand-mark" aria-hidden="true"><i class="ph-fill ph-cross"></i></div>
        <p class="login-kicker">Hospital Information Management System</p>
        <h1>HIMS Main System</h1>
        <p class="login-brand-description">One connected workspace for hospital operations and financial management modules.</p>
        <div class="mt-4 d-flex flex-column gap-2" style="font-size: 0.83rem; color: rgba(255,255,255,0.82);">
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Enterprise TOTP two-factor protection</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Hardware-bound trusted computer terminal</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Single active session enforcement</span></div>
        </div>
      </div>
      <footer class="login-brand-footer"><span>HIMS Command Center</span><span>Version 0.9</span></footer>
    </section>

    <!-- Verification Panel -->
    <section class="login-panel" aria-labelledby="2fa-title">
      <div class="login-panel-container d-flex flex-column align-items-center w-100" style="max-width: 440px;">
        <div class="login-card w-100">

          <!-- Hospital Seal Header (System Brand) -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon">
              <i class="ph-fill ph-hospital"></i>
            </div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>DEPARTMENT OF HEALTH &bull; PUBLIC HOSPITAL NETWORK</p>
            </div>
          </div>

          <!-- 1. Top Badge: TWO-FACTOR AUTHENTICATION -->
          <div class="mb-3">
            <span class="badge-2fa-pill">TWO-FACTOR AUTHENTICATION</span>
          </div>

          <!-- 2. Heading & Subtitle -->
          <h2 class="verification-title mb-1" id="2fa-title">Security Verification</h2>
          <p class="verification-subtitle mb-3">
            Enter the 6&ndash;digit code from Google Authenticator on your mobile phone to proceed.
          </p>

          @if($errors->any())
            <div class="alert alert-danger rounded-3 py-2 px-3 border-0 mb-3" role="alert" style="font-size: 0.82rem;" id="error-alert">
              <i class="ph ph-warning-circle me-1"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <!-- 3. Workstation Banner (Hospital Card) -->
          <div class="workstation-banner mb-3">
            <div class="d-flex align-items-start gap-2">
              <span class="workstation-dot mt-1" aria-hidden="true"></span>
              <div>
                <div class="workstation-banner-title">{{ $workstationTitle ?? 'New Super Admin Workstation' }}</div>
                <div class="workstation-banner-desc">Logging in will securely bind this computer as a trusted terminal.</div>
              </div>
            </div>
          </div>

          <!-- 4. TOTP Form -->
          <div id="totp-panel">
            <form id="totp-form" method="POST" action="{{ route('two-factor.challenge.verify') }}" novalidate>
              @csrf

              <!-- Authenticator Code Label & Smooth Refresh Countdown -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="auth-code-label mb-0" for="otp-d1">Authenticator Code</label>
                <span class="totp-refresh-timer">
                  Refreshes in <strong id="totp-countdown-sec">30</strong>s
                </span>
              </div>

              <!-- 6 Digit Input Boxes -->
              <div class="otp-input-group" role="group" aria-label="6-digit TOTP code">
                <input class="otp-digit" id="otp-d1" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
                <input class="otp-digit" id="otp-d2" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
                <input class="otp-digit" id="otp-d3" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
                <input class="otp-digit" id="otp-d4" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
                <input class="otp-digit" id="otp-d5" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
                <input class="otp-digit" id="otp-d6" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
              </div>
              <input type="hidden" name="code" id="otp-assembled">

              <!-- Silky Smooth 60fps TOTP 30-Second Countdown Progress Bar -->
              <div class="totp-progress-track">
                <div class="totp-progress-bar" id="totp-progress-bar"></div>
              </div>

              <!-- 5. Action Button: Verify & Enter Workspace → -->
              <button id="verify-btn" class="login-submit-primary mb-3" type="submit" disabled>
                <span>Verify &amp; Enter Workspace</span>
                <i class="ph ph-arrow-right fs-5" aria-hidden="true"></i>
              </button>
            </form>
          <!-- 6. Back to Login Navigation -->
          <div class="text-center mt-3">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
              @csrf
              <button type="submit" class="back-to-login-link">
                <i class="ph ph-arrow-left me-1"></i> Back to Login
              </button>
            </form>
          </div>

        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const digits    = Array.from(document.querySelectorAll('.otp-digit'));
    const assembled = document.getElementById('otp-assembled');
    const verifyBtn = document.getElementById('verify-btn');

    function syncAssembled() {
      const code = digits.map(d => d.value).join('');
      if (assembled) assembled.value = code;
      const ok = code.length === 6 && /^\d{6}$/.test(code);
      verifyBtn.disabled = !ok;
      digits.forEach(d => d.classList.toggle('filled', d.value !== ''));
    }

    document.getElementById('totp-form')?.addEventListener('submit', (e) => {
      syncAssembled();
      const code = assembled ? assembled.value : '';
      if (code.length !== 6 || !/^\d{6}$/.test(code)) { e.preventDefault(); }
    });

    digits.forEach((digit, i) => {
      digit.addEventListener('input', () => {
        digit.value = digit.value.replace(/\D/g,'').slice(-1);
        if (digit.value && i < digits.length-1) digits[i+1].focus();
        syncAssembled();
      });
      digit.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !digit.value && i > 0) { digits[i-1].focus(); digits[i-1].value = ''; syncAssembled(); }
        if (e.key === 'ArrowLeft'  && i > 0)               digits[i-1].focus();
        if (e.key === 'ArrowRight' && i < digits.length-1) digits[i+1].focus();
      });
      digit.addEventListener('paste', e => {
        e.preventDefault();
        const p = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        p.split('').forEach((c,j) => { if (digits[j]) digits[j].value = c; });
        if (p.length) digits[Math.min(p.length, digits.length-1)].focus();
        syncAssembled();
      });
    });

    // Shake on validation error
    @if($errors->any())
      digits.forEach(d => d.classList.add('error'));
      setTimeout(() => digits.forEach(d => d.classList.remove('error')), 600);
    @endif

    // Auto-focus first digit on page load
    window.addEventListener('DOMContentLoaded', () => digits[0]?.focus());

    // ─── Silky Smooth 60fps TOTP Progress Bar (Non-Ticking) ───────────────
    function smoothTotpProgress() {
      const nowMs = Date.now();
      const elapsedInWindowMs = nowMs % 30000;
      const remainingMs = 30000 - elapsedInWindowMs;
      const pct = (remainingMs / 30000) * 100;

      const barEl   = document.getElementById('totp-progress-bar');
      const timerEl = document.getElementById('totp-countdown-sec');

      if (barEl) {
        barEl.style.width = pct.toFixed(2) + '%';
      }

      const secondsRemaining = Math.ceil(remainingMs / 1000);
      if (timerEl && timerEl.textContent !== String(secondsRemaining)) {
        timerEl.textContent = secondsRemaining;
      }

      requestAnimationFrame(smoothTotpProgress);
    }

    requestAnimationFrame(smoothTotpProgress);

    // Prevent browser bfcache restoration
    window.addEventListener('pageshow', function (event) {
      if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
        window.location.replace('{{ match (auth()->user()?->role) { 'Cashier' => route('collection.cashier-desk'), default => route('accounting.dashboard') } }}');
      }
    });
  </script>
</body>
</html>
