<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Set Up Google Authenticator &bull; Two-Factor Authentication &bull; HIMS &bull; FMS</title>
  <meta name="description" content="Scan the QR code with your authenticator app and enter the 6-digit code to activate 2FA.">
  <x-favicon />

  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Immediate Theme Boot -->
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
    /* ─── Browser Autofill Styling ────────────────────────────────────────── */
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

    /* ─── 6-Digit OTP Box Styling ─────────────────────────────────────────── */
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
      border-color: #ef4444 !important;
      background: #fef2f2 !important;
      color: #dc2626 !important;
      animation: shake 0.35s ease-in-out;
    }
    html.dark .otp-digit.error {
      border-color: #f87171 !important;
      background: rgba(127, 29, 29, 0.3) !important;
      color: #fca5a5 !important;
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
      margin-bottom: 20px;
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
    }

    /* ─── QR Code Container ──────────────────────────────────────────────── */
    .qr-card-surface svg {
      display: block;
      margin: 0 auto;
      max-width: 180px;
      max-height: 180px;
      border-radius: 8px;
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

    <!-- Right Verification & Setup Column -->
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

      <!-- Center Auth Form Container -->
      <div class="my-auto mx-auto w-full max-w-md py-6">
        
        <!-- Hospital Header Seal & Department Badge -->
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-200 dark:border-slate-800">
          <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30">
            <i class="ph-bold ph-hospital text-lg"></i>
          </div>
          <div>
            <h2 class="text-xs font-bold text-slate-900 dark:text-white tracking-wide uppercase">Republic of the Philippines</h2>
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Department of Health &bull; Hospital Network</p>
          </div>
        </div>

        <!-- Top Badge -->
        <div class="mb-2.5">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/60 dark:text-emerald-300 font-mono tracking-wider uppercase">
            <i class="ph-bold ph-shield-check text-xs"></i>
            TWO-FACTOR ENROLLMENT
          </span>
        </div>

        <!-- Heading & Description -->
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" id="setup-title">
            Set Up Google Authenticator
          </h2>
          <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Scan the QR code with your authenticator app, then enter the 6-digit code to activate 2FA.
          </p>
        </div>

        @if(session('error'))
          <div class="mt-4 rounded-xl border border-rose-200/80 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2" role="alert">
            <i class="ph-bold ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
          </div>
        @endif

        @if($errors->any())
          <div class="mt-4 rounded-xl border border-rose-200/80 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 flex items-center gap-2" role="alert" id="error-banner">
            <i class="ph-bold ph-warning-circle text-base text-rose-600 dark:text-rose-400 shrink-0"></i>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <!-- ─── Clean QR Code Card Surface ───────────────────────────────────── -->
        <div class="mt-5 rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-sm ring-1 ring-slate-200/90 dark:ring-slate-800">
          
          <div class="text-center">
            <!-- QR Code Presentation Box -->
            <div class="qr-card-surface inline-block p-3 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700" id="qr-container">
              {!! $qrCodeSvg !!}
            </div>

            <!-- Email & User Identifier -->
            <div class="mt-3 flex items-center justify-center gap-1.5 text-xs font-mono text-slate-500 dark:text-slate-400">
              <i class="ph-bold ph-envelope-simple text-slate-400"></i>
              <span>{{ $userEmail }}</span>
            </div>

            <!-- Regenerate QR Code Link -->
            <div class="mt-1.5">
              <button 
                type="button" 
                class="inline-flex items-center gap-1 text-[11.5px] font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors cursor-pointer" 
                id="regen-btn" 
                onclick="regenerateQr()"
              >
                <i class="ph-bold ph-arrows-clockwise"></i>
                <span>Regenerate QR code</span>
              </button>
              <span id="regen-spinner" class="hidden text-xs text-slate-400 items-center gap-1">
                <i class="ph-bold ph-circle-notch animate-spin text-emerald-600"></i> Generating...
              </span>
            </div>
          </div>

          <!-- Divider -->
          <div class="my-5 border-t border-slate-100 dark:border-slate-800"></div>

          <!-- ─── 6-Digit Verification Form with 30s Live Timer ─────────────────── -->
          <form method="POST" action="{{ route('two-factor.setup.confirm') }}" id="confirm-form" novalidate>
            @csrf

            <!-- Header Row: Label & 30s Countdown Timer -->
            <div class="flex justify-between items-center mb-2.5">
              <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider" for="otp-d1">
                Authenticator Code
              </label>
              <span class="text-xs font-mono text-slate-400 dark:text-slate-500">
                Refreshes in <strong id="totp-countdown-sec" class="text-slate-900 dark:text-white font-bold">30</strong>s
              </span>
            </div>

            <!-- 6 Digit Input Group -->
            <div class="otp-input-group" role="group" aria-label="6-digit TOTP code">
              <input class="otp-digit" id="otp-d1" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
              <input class="otp-digit" id="otp-d2" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
              <input class="otp-digit" id="otp-d3" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
              <input class="otp-digit" id="otp-d4" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
              <input class="otp-digit" id="otp-d5" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
              <input class="otp-digit" id="otp-d6" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
            </div>
            <input type="hidden" name="code" id="otp-assembled">

            <!-- Silky Smooth 60fps 30-Second TOTP Progress Bar -->
            <div class="totp-progress-track">
              <div class="totp-progress-bar" id="totp-progress-bar"></div>
            </div>

            <!-- Activate Submit Button -->
            <button 
              id="confirm-btn" 
              class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 px-4 text-sm font-semibold text-white shadow-md shadow-emerald-900/20 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer" 
              type="submit" 
              disabled
            >
              <i class="ph-bold ph-shield-check text-base" aria-hidden="true"></i>
              <span>Activate 2FA &amp; Enter Workspace</span>
              <i class="ph-bold ph-arrow-right text-sm" aria-hidden="true"></i>
            </button>

          </form>

        </div>

        <!-- Cancel and Sign Out Link -->
        <div class="mt-4 text-center">
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors cursor-pointer">
              <i class="ph-bold ph-arrow-left"></i>
              <span>Cancel and sign out</span>
            </button>
          </form>
        </div>

      </div>

      <!-- Bottom Mini Footer -->
      <div class="text-center text-xs text-slate-400 dark:text-slate-500 py-2">
        Hospital Financial Management System &bull; Secure Terminal Gateway &bull; DOH Network
      </div>
    </div>

  </div>

  <!-- IT Helpdesk Support Directory Modal -->
  <x-auth-helpdesk-modal />

  <script>
    // ─── 1. OTP Digit Handling & Auto-Advance ────────────────────────────────
    const digits     = Array.from(document.querySelectorAll('.otp-digit'));
    const assembled  = document.getElementById('otp-assembled');
    const confirmBtn = document.getElementById('confirm-btn');

    function syncAssembled() {
      const code     = digits.map(d => d.value).join('');
      if (assembled) assembled.value = code;
      const complete = code.length === 6 && /^\d{6}$/.test(code);
      if (confirmBtn) confirmBtn.disabled = !complete;
      digits.forEach(d => d.classList.toggle('filled', d.value !== ''));
    }

    digits.forEach((digit, i) => {
      digit.addEventListener('input', () => {
        digit.value = digit.value.replace(/\D/g, '').slice(-1);
        if (digit.value && i < digits.length - 1) digits[i + 1].focus();
        syncAssembled();
      });

      digit.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !digit.value && i > 0) {
          digits[i - 1].focus();
          digits[i - 1].value = '';
          syncAssembled();
        }
        if (e.key === 'ArrowLeft' && i > 0) digits[i - 1].focus();
        if (e.key === 'ArrowRight' && i < digits.length - 1) digits[i + 1].focus();
      });

      digit.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((char, idx) => {
          if (digits[idx]) digits[idx].value = char;
        });
        if (pasted.length) digits[Math.min(pasted.length, digits.length - 1)].focus();
        syncAssembled();
      });
    });

    // Auto-focus first digit on load
    window.addEventListener('DOMContentLoaded', () => digits[0]?.focus());

    // Shake animation on error
    @if($errors->any())
      digits.forEach(d => d.classList.add('error'));
      setTimeout(() => digits.forEach(d => d.classList.remove('error')), 600);
    @endif

    // Prevent submitting with incomplete code
    document.getElementById('confirm-form')?.addEventListener('submit', (e) => {
      syncAssembled();
      const code = assembled ? assembled.value : '';
      if (code.length !== 6 || !/^\d{6}$/.test(code)) {
        e.preventDefault();
      }
    });

    // ─── 2. Silky Smooth 60fps TOTP 30-Second Refresh Timer ────────────────
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

    // ─── 3. Regenerate QR Code ─────────────────────────────────────────────
    async function regenerateQr() {
      const btn = document.getElementById('regen-btn');
      const spinner = document.getElementById('regen-spinner');
      const container = document.getElementById('qr-container');

      if (btn) btn.classList.add('hidden');
      if (spinner) spinner.classList.remove('hidden');

      try {
        const response = await fetch('{{ route('two-factor.setup.store') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
        });

        if (!response.ok) throw new Error('Failed to regenerate secret');

        const data = await response.json();
        if (data.qr_svg && container) {
          container.innerHTML = data.qr_svg;
        }

        // Reset input fields
        digits.forEach(d => { d.value = ''; d.classList.remove('filled'); });
        if (assembled) assembled.value = '';
        if (confirmBtn) confirmBtn.disabled = true;
        digits[0]?.focus();
      } catch (err) {
        console.error('QR Regeneration failed:', err);
      } finally {
        if (btn) btn.classList.remove('hidden');
        if (spinner) spinner.classList.add('hidden');
      }
    }
  </script>
</body>
</html>
