<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Two-Factor Authentication - HIMS</title>
  <meta name="description" content="Enter your Google Authenticator code to securely access the Hospital Financial Management System.">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    .login-panel { padding: 20px 24px !important; }
    .login-card {
      padding: 24px 28px !important; border-radius: 14px !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 10px 25px -5px rgba(15,23,42,0.06), 0 8px 10px -6px rgba(15,23,42,0.04) !important;
    }
    .hospital-header-seal { display: flex; align-items: center; gap: 12px; padding-bottom: 10px; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; }
    .hospital-seal-icon { width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(5,150,105,0.2); }
    .hospital-seal-text h4 { margin: 0; font-size: 0.82rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em; }
    .hospital-seal-text p  { margin: 0; font-size: 0.67rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
    .shield-icon-wrap { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #059669 0%, #047857 100%); display: flex; align-items: center; justify-content: center; font-size: 1.7rem; color: #ffffff; box-shadow: 0 8px 20px rgba(5,150,105,0.25); margin: 4px auto 12px; }
    .security-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 20px; padding: 4px 12px; letter-spacing: 0.01em; }
    .login-card-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
    .login-card-desc  { font-size: 0.82rem; color: #64748b; line-height: 1.45; }

    /* TOTP info box */
    .totp-hint-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 14px; margin-top: 14px; margin-bottom: 16px; }
    .totp-hint-card .totp-app-name { font-weight: 700; color: #065f46; }

    /* OTP Digit Input Boxes */
    .otp-input-group { display: flex; gap: 8px; justify-content: center; }
    .otp-digit {
      width: 48px; height: 56px; text-align: center;
      font-size: 1.55rem; font-weight: 700; font-family: 'Poppins', sans-serif; font-variant-numeric: tabular-nums;
      border: 1.5px solid #cbd5e1; border-radius: 12px; outline: none;
      transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.1s;
      color: #0f172a; background: #ffffff; caret-color: transparent;
    }
    .otp-digit:focus { border-color: #059669; box-shadow: 0 0 0 3.5px rgba(5,150,105,0.16); background: #ffffff; transform: translateY(-1px); }
    .otp-digit.filled { border-color: #059669; background: #f0fdf4; color: #065f46; }
    .otp-digit.error  { border-color: #ef4444; background: #fef2f2; color: #dc2626; animation: shake 0.35s ease-in-out; }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 20% { transform: translateX(-6px); } 60% { transform: translateX(6px); } }

    .login-submit {
      width: 100%; min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; color: #ffffff !important;
      border: none !important; border-radius: 10px !important; font-size: 0.88rem !important; font-weight: 600 !important;
      box-shadow: 0 4px 12px rgba(5,150,105,0.25) !important; transition: all 0.2s cubic-bezier(0.16,1,0.3,1) !important; cursor: pointer;
    }
    .login-submit:hover:not(:disabled) { background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important; box-shadow: 0 6px 16px rgba(5,150,105,0.35) !important; transform: translateY(-1px); color: #ffffff !important; }
    .login-submit:disabled { background: #cbd5e1 !important; color: #94a3b8 !important; box-shadow: none !important; cursor: not-allowed; transform: none !important; opacity: 0.7; }

    .mode-link { background: none; border: none; color: #059669; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: color 0.15s; padding: 0; text-decoration: none; }
    .mode-link:hover { color: #047857; text-decoration: underline; }
    .account-switch-btn { background: none; border: none; color: #64748b; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: color 0.15s; padding: 0; text-decoration: none; }
    .account-switch-btn:hover { color: #0f172a; text-decoration: underline; }
    .recovery-input { font-family: 'Courier New', monospace; font-size: 1rem; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; text-align: center; }
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
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Google Authenticator time-based codes</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>RA 10173 &amp; HIPAA-compliant access gate</span></div>
        </div>
      </div>
      <footer class="login-brand-footer"><span>HIMS Command Center</span><span>Version 0.9</span></footer>
    </section>

    <!-- Challenge Panel -->
    <section class="login-panel" aria-labelledby="2fa-title">
      <div class="login-panel-container d-flex flex-column align-items-center w-100" style="max-width: 440px;">
        <div class="login-card w-100">

          <!-- Official Hospital / DOH Seal -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon" aria-hidden="true"><i class="ph-fill ph-hospital"></i></div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>Department of Health • Public Hospital Network</p>
            </div>
          </div>

          <!-- Hero Icon & Title -->
          <div class="text-center mb-2">
            <div class="shield-icon-wrap" aria-hidden="true">
              <i class="ph-fill ph-device-mobile"></i>
            </div>
            <span class="security-badge mb-2">
              <i class="ph-fill ph-lock-key"></i> Authenticator Verification Required
            </span>
            <h2 class="login-card-title mt-2 mb-1" id="2fa-title">Enter Authenticator Code</h2>
            <p class="login-card-desc mb-0">Open <strong>Google Authenticator</strong> on your phone and enter the 6-digit code for this account.</p>
          </div>

          @if($errors->any())
            <div class="alert alert-danger rounded-3 py-2 px-3 border-0 mb-3" role="alert" style="font-size: 0.82rem;" id="error-alert">
              <i class="ph ph-warning-circle me-1"></i>
              {{ $errors->first() }}
            </div>
          @endif

          <!-- TOTP Panel (default) -->
          <div id="totp-panel">
            <!-- Hint card -->
            <div class="totp-hint-card">
              <div class="d-flex align-items-center gap-2">
                <i class="ph-fill ph-device-mobile-camera fs-5" style="color: #059669;"></i>
                <span style="font-size: 0.82rem; color: #475569;">
                  Open <span class="totp-app-name">Google Authenticator</span> and enter the code shown for
                  <strong>{{ config('app.name', 'Hospital FMS') }}</strong>
                </span>
              </div>
            </div>

            <!-- 6 Digit Boxes -->
            <form id="totp-form" method="POST" action="{{ route('two-factor.challenge.verify') }}" novalidate>
              @csrf
              <div class="mb-3">
                <label class="form-label text-center d-block fw-semibold" style="font-size: 0.82rem; color: #475569;" for="otp-d1">
                  Enter your 6-digit code
                </label>
                <div class="otp-input-group" role="group" aria-label="6-digit TOTP code">
                  <input class="otp-digit" id="otp-d1" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
                  <input class="otp-digit" id="otp-d2" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
                  <input class="otp-digit" id="otp-d3" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
                  <input class="otp-digit" id="otp-d4" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
                  <input class="otp-digit" id="otp-d5" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
                  <input class="otp-digit" id="otp-d6" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
                </div>
                <input type="hidden" name="code" id="otp-assembled">
              </div>

              <button id="verify-btn" class="login-submit" type="submit" disabled>
                <i class="ph-fill ph-shield-check" aria-hidden="true"></i>
                Verify &amp; Sign In
              </button>
              <p class="text-center mt-2 mb-0" style="font-size: 0.74rem; color: #94a3b8;">
                <i class="ph ph-clock me-1"></i> Codes refresh every 30 seconds
              </p>
            </form>
          </div>

          <!-- Recovery Code Panel (hidden) -->
          <div id="recovery-panel" class="d-none">
            <form method="POST" action="{{ route('two-factor.challenge.verify') }}" novalidate>
              @csrf
              <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;" for="recovery-input">
                  Recovery Code
                </label>
                <input id="recovery-input" class="form-control recovery-input rounded-3" name="recovery_code"
                  type="text" maxlength="11" placeholder="XXXXX-XXXXX" autocomplete="off" spellcheck="false" style="min-height: 48px;">
                <div class="form-text mt-1" style="font-size: 0.75rem;">
                  <i class="ph ph-info me-1" style="color: #059669;"></i>
                  Each recovery code can only be used once.
                </div>
              </div>
              <button class="login-submit" type="submit">
                <i class="ph ph-key" aria-hidden="true"></i>
                Use Recovery Code
              </button>
            </form>
          </div>

          <!-- Toggle to recovery -->
          <div class="text-center mt-3">
            <button type="button" class="mode-link" id="toggle-recovery" onclick="toggleRecovery()">
              <i class="ph ph-key me-1" id="toggle-icon"></i>
              <span id="toggle-text">Lost access to your authenticator? Use a recovery code</span>
            </button>
          </div>

          <!-- Back to login -->
          <div class="text-center mt-3 pt-2" style="border-top: 1px solid #f1f5f9;">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
              @csrf
              <button type="submit" class="account-switch-btn">
                <i class="ph ph-arrow-left me-1"></i> Sign in with a different account
              </button>
            </form>
          </div>

        </div>

        <!-- Compliance Footer -->
        <div class="login-compliance-footer w-100">
          <p>
            <i class="ph-fill ph-shield-check me-1 text-secondary align-middle"></i>
            <strong>HIPAA / RA 10173 Compliant:</strong> TOTP authentication ensures only authorized hospital personnel access financial records.
          </p>
          <div class="login-compliance-meta">
            <span>Session Secured</span><span>&bull;</span><span>FMS Transaction Core</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const digits        = Array.from(document.querySelectorAll('.otp-digit'));
    const assembled     = document.getElementById('otp-assembled');
    const verifyBtn     = document.getElementById('verify-btn');

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

    // Recovery code toggle
    let recoveryMode = false;
    window.toggleRecovery = function() {
      recoveryMode = !recoveryMode;
      document.getElementById('totp-panel').classList.toggle('d-none', recoveryMode);
      document.getElementById('recovery-panel').classList.toggle('d-none', !recoveryMode);
      document.getElementById('toggle-text').textContent = recoveryMode
        ? 'Use Google Authenticator instead'
        : 'Lost access to your authenticator? Use a recovery code';
      document.getElementById('toggle-icon').className = recoveryMode ? 'ph ph-device-mobile me-1' : 'ph ph-key me-1';
      if (recoveryMode) document.getElementById('recovery-input')?.focus();
      else digits[0]?.focus();
    };

    // Prevent browser bfcache restoration
    window.addEventListener('pageshow', function (event) {
      if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
        window.location.replace('{{ match (auth()->user()?->role) { 'Cashier' => route('collection.cashier-desk'), default => route('accounting.dashboard') } }}');
      }
    });
  </script>
</body>
</html>
