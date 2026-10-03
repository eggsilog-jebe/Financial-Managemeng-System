<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Security Verification &mdash; Two-Factor Authentication &mdash; HIMS &bull; FMS</title>
  <meta name="description" content="Enter the 6-digit code from Google Authenticator on your mobile phone to proceed.">
  <x-favicon />

  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Immediate Theme Boot (Default: Light, Synced with fms_theme & colorScheme) -->
  <script>
    (function() {
      const savedTheme = localStorage.getItem('fms_theme');
      if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
      }
    })();
  </script>

  <!-- Tailwind CSS v4 & Alpine.js Asset Bundle -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Browser Autofill Styling */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus, 
    input:-webkit-autofill:active {
      -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
      -webkit-text-fill-color: #0f172a !important;
      box-shadow: 0 0 0 1000px #ffffff inset !important;
      caret-color: #0f172a !important;
      color: #0f172a !important;
      transition: background-color 5000s ease-in-out 0s, color 5000s ease-in-out 0s !important;
    }

    html.dark input:-webkit-autofill,
    html.dark input:-webkit-autofill:hover, 
    html.dark input:-webkit-autofill:focus, 
    html.dark input:-webkit-autofill:active {
      -webkit-box-shadow: 0 0 0 1000px #0f172a inset !important;
      -webkit-text-fill-color: #f8fafc !important;
      box-shadow: 0 0 0 1000px #0f172a inset !important;
      caret-color: #f8fafc !important;
      color: #f8fafc !important;
      transition: background-color 5000s ease-in-out 0s, color 5000s ease-in-out 0s !important;
    }

    /* ─── Elevated Verification Card ───────────────────────────────────── */
    .login-card {
      width: 100%;
      max-width: 440px;
      padding: 32px 30px;
      border-radius: 16px;
      border: 1px solid #eef2f6;
      background: #ffffff;
      box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.07), 0 4px 6px -2px rgba(15, 23, 42, 0.03);
    }
    html.dark .login-card {
      background: #0f172a;
      border-color: #1e293b;
      box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
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
    html.dark .hospital-header-seal {
      border-bottom-color: #1e293b;
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
    html.dark .hospital-seal-text h4 {
      color: #f8fafc;
    }
    .hospital-seal-text p {
      margin: 0;
      font-size: 0.68rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    html.dark .hospital-seal-text p {
      color: #94a3b8;
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
    html.dark .badge-2fa-pill {
      background: rgba(6, 78, 59, 0.3);
      color: #6ee7b7;
      border-color: rgba(5, 150, 105, 0.4);
    }

    /* ─── Main Headings ──────────────────────────────────────────────────── */
    .verification-title {
      font-size: 1.65rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.025em;
      line-height: 1.25;
    }
    html.dark .verification-title {
      color: #f8fafc;
    }
    .verification-subtitle {
      font-size: 0.875rem;
      color: #64748b;
      line-height: 1.5;
    }
    html.dark .verification-subtitle {
      color: #94a3b8;
    }

    /* ─── Workstation Notice Banner (Hospital Card) ─────────────────────── */
    .workstation-banner {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 12px;
      padding: 13px 16px;
    }
    html.dark .workstation-banner {
      background: rgba(6, 78, 59, 0.25);
      border-color: rgba(5, 150, 105, 0.35);
    }
    .workstation-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: #059669;
      flex-shrink: 0;
      box-shadow: 0 0 0 2.5px rgba(5, 150, 105, 0.2);
    }
    html.dark .workstation-dot {
      background: #10b981;
      box-shadow: 0 0 0 2.5px rgba(16, 185, 129, 0.25);
    }
    .workstation-banner-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: #065f46;
      line-height: 1.3;
    }
    html.dark .workstation-banner-title {
      color: #6ee7b7;
    }
    .workstation-banner-desc {
      font-size: 0.8rem;
      color: #047857;
      margin-top: 2px;
      line-height: 1.35;
    }
    html.dark .workstation-banner-desc {
      color: #a7f3d0;
    }

    /* ─── Authenticator Code Label Row ──────────────────────────────────── */
    .auth-code-label {
      font-size: 0.78rem;
      font-weight: 700;
      color: #1f2937;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    html.dark .auth-code-label {
      color: #f1f5f9;
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
    html.dark .totp-refresh-timer strong {
      color: #f8fafc;
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
      font-family: 'JetBrains Mono', monospace;
      font-variant-numeric: tabular-nums;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.1s;
      color: #0f172a;
      background: #f8fafc;
      caret-color: transparent;
    }
    html.dark .otp-digit {
      background: #0b1120;
      border-color: #334155;
      color: #f8fafc;
    }
    .otp-digit:focus {
      border-color: #059669;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
      background: #ffffff;
      transform: translateY(-1px);
    }
    html.dark .otp-digit:focus {
      background: #0f172a;
      border-color: #10b981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
    }
    .otp-digit.filled {
      border-color: #059669;
      background: #f0fdf4;
      color: #065f46;
    }
    html.dark .otp-digit.filled {
      border-color: #10b981;
      background: rgba(6, 78, 59, 0.3);
      color: #6ee7b7;
    }
    .otp-digit.error {
      border-color: #ef4444;
      background: #fef2f2;
      color: #dc2626;
      animation: shake 0.35s ease-in-out;
    }
    html.dark .otp-digit.error {
      border-color: #f87171;
      background: rgba(127, 29, 29, 0.3);
      color: #fca5a5;
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
    html.dark .totp-progress-track {
      background: #1e293b;
    }
    .totp-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #10b981 0%, #059669 100%);
      border-radius: 999px;
      width: 100%;
      will-change: width;
      /* Strictly NO CSS transition so requestAnimationFrame is 100% smooth without jitter */
    }

    /* ─── Action Button: Verify & Enter Workspace → ──────────────────────── */
    .login-submit-primary {
      width: 100%;
      height: 48px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      border: none;
      border-radius: 12px;
      font-size: 0.95rem;
      font-weight: 600;
      box-shadow: 0 4px 14px rgba(5, 150, 105, 0.28);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      cursor: pointer;
      text-decoration: none;
    }
    .login-submit-primary:hover:not(:disabled) {
      background: linear-gradient(135deg, #047857 0%, #065f46 100%);
      box-shadow: 0 6px 18px rgba(5, 150, 105, 0.38);
      transform: translateY(-1px);
      color: #ffffff;
    }
    .login-submit-primary:disabled {
      background: #cbd5e1;
      color: #94a3b8;
      box-shadow: none;
      cursor: not-allowed;
      transform: none;
      opacity: 0.75;
    }
    html.dark .login-submit-primary:disabled {
      background: #334155;
      color: #64748b;
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
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .back-to-login-link:hover {
      color: #059669;
      text-decoration: underline;
    }
    html.dark .back-to-login-link {
      color: #94a3b8;
    }
    html.dark .back-to-login-link:hover {
      color: #34d399;
    }
  </style>
</head>
<body 
  x-data="{ 
    helpdeskOpen: false,
    darkMode: document.documentElement.classList.contains('dark')
  }" 
  class="h-full bg-slate-50 dark:bg-slate-950 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-emerald-500 selection:text-white"
>

  <div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Left Hero & Security Authority Column (Universal Standard) -->
    <x-auth-hero />

    <!-- Right Sign In / Verification Panel Column -->
    <div class="relative flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-16">
      
      <!-- Top Actions (Theme Toggle & Helpdesk trigger) -->
      <div class="flex items-center justify-end gap-3">
        <button 
          type="button" 
          @click="
            darkMode = !darkMode;
            if (darkMode) {
              document.documentElement.classList.add('dark');
              document.documentElement.style.colorScheme = 'dark';
              localStorage.setItem('fms_theme', 'dark');
            } else {
              document.documentElement.classList.remove('dark');
              document.documentElement.style.colorScheme = 'light';
              localStorage.setItem('fms_theme', 'light');
            }
          " 
          class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white transition-colors cursor-pointer"
          :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <i class="ph-bold ph-moon text-base block dark:hidden"></i>
          <i class="ph-bold ph-sun text-base hidden dark:block"></i>
        </button>

        <button 
          type="button" 
          @click="helpdeskOpen = true" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <i class="ph-bold ph-headset text-sm text-emerald-600 dark:text-emerald-400"></i>
          <span>IT Helpdesk</span>
        </button>
      </div>

      <!-- Center Auth Form Container with Elevated Card -->
      <div class="my-auto mx-auto w-full flex flex-col items-center py-6">
        
        <div class="login-card">

          <!-- Hospital Header Seal (System Brand) -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon">
              <i class="ph-bold ph-hospital"></i>
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
          <p class="verification-subtitle mb-4">
            Enter the 6&ndash;digit code from Google Authenticator on your mobile phone to proceed.
          </p>

          @if($errors->any())
            <div class="mb-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2" role="alert" id="error-alert">
              <i class="ph-bold ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
              <span>{{ $errors->first() }}</span>
            </div>
          @endif

          <!-- 3. Workstation Banner (Hospital Card) -->
          <div class="workstation-banner mb-4">
            <div class="flex items-start gap-2.5">
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
              <div class="flex justify-between items-center mb-2.5">
                <label class="auth-code-label mb-0" for="otp-d1">AUTHENTICATOR CODE</label>
                <span class="totp-refresh-timer font-mono">
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

              <!-- Silky Smooth 60fps TOTP 30-Second Countdown Progress Bar (Exact Previous Smooth Animation) -->
              <div class="totp-progress-track">
                <div class="totp-progress-bar" id="totp-progress-bar"></div>
              </div>

              <!-- 5. Action Button: Verify & Enter Workspace → -->
              <button id="verify-btn" class="login-submit-primary mb-3" type="submit" disabled>
                <span>Verify &amp; Enter Workspace</span>
                <i class="ph-bold ph-arrow-right text-base" aria-hidden="true"></i>
              </button>
            </form>

            <!-- 6. Back to Login Navigation -->
            <div class="text-center mt-3">
              <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="back-to-login-link">
                  <i class="ph-bold ph-arrow-left"></i>
                  <span>Back to Login</span>
                </button>
              </form>
            </div>

          </div>

        </div>

      </div>

      <!-- Bottom Mini Footer -->
      <div class="text-center text-xs text-slate-400 dark:text-slate-500">
        Hospital Financial Management System &bull; Secure Terminal Gateway
      </div>
    </div>

  </div>

  <!-- IT Helpdesk Support Directory Modal -->
  <x-auth-helpdesk-modal />

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

    // ─── Silky Smooth 60fps TOTP Progress Bar (Non-Ticking, Non-Lagging) ───────────────
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
