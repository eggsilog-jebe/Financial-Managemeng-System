<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Set Up Two-Factor Authentication – HIMS</title>
  <meta name="description" content="Enable Email OTP Two-Factor Authentication for your Hospital Financial Management System account.">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    /* ── Email OTP Setup Page (FMS Unified Theme) ─────────────────────── */
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
    .shield-icon-wrap {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.7rem;
      color: #ffffff;
      box-shadow: 0 8px 20px rgba(5, 150, 105, 0.25);
      margin: 4px auto 12px;
    }
    .login-card-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.02em;
    }
    .login-card-desc {
      font-size: 0.82rem;
      color: #64748b;
      line-height: 1.45;
    }

    .step-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, #059669, #047857);
      color: #ffffff;
      font-size: 0.8rem;
      font-weight: 700;
      flex-shrink: 0;
      box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
    }
    .step-badge.done {
      background: linear-gradient(135deg, #059669, #047857);
      box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
    }
    .step-connector {
      width: 2px;
      height: 22px;
      background: linear-gradient(180deg, rgba(5, 150, 105, 0.35), rgba(5, 150, 105, 0.08));
      margin: 6px auto;
    }

    /* Email display pill */
    .email-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 100px;
      padding: 7px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #065f46;
    }

    /* Send OTP button */
    .send-otp-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      border: none;
      border-radius: 10px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
      width: 100%;
    }
    .send-otp-btn:hover:not(:disabled) {
      background: linear-gradient(135deg, #047857 0%, #065f46 100%);
      box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
      transform: translateY(-1px);
    }
    .send-otp-btn:disabled {
      background: #cbd5e1;
      color: #94a3b8;
      box-shadow: none;
      cursor: not-allowed;
      transform: none;
    }

    /* Countdown */
    .countdown-row {
      display: none;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 12px;
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      color: #047857;
    }
    .countdown-circle {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #059669;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 700;
    }

    /* OTP digit boxes */
    .otp-input-group {
      display: flex;
      gap: 8px;
      justify-content: center;
    }
    .otp-digit {
      width: 48px;
      height: 56px;
      text-align: center;
      font-size: 1.55rem;
      font-weight: 700;
      font-family: 'Poppins', sans-serif;
      font-variant-numeric: tabular-nums;
      border: 1.5px solid #cbd5e1;
      border-radius: 12px;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.1s;
      color: #0f172a;
      background: #ffffff;
      caret-color: transparent;
    }
    .otp-digit:focus {
      border-color: #059669;
      box-shadow: 0 0 0 3.5px rgba(5, 150, 105, 0.16);
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
      20%      { transform: translateX(-6px); }
      60%      { transform: translateX(6px); }
    }
    @keyframes pulse-in {
      0% { opacity: 0; transform: scale(0.95) translateY(8px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .fade-in { animation: pulse-in 0.35s ease both; }

    /* Submit Button (Unified FMS Primary) */
    .login-submit {
      width: 100%;
      min-height: 42px;
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
      box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
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

    .account-switch-btn {
      background: none;
      border: none;
      color: #64748b;
      font-size: 0.8rem;
      font-weight: 500;
      cursor: pointer;
      transition: color 0.15s;
      padding: 0;
      text-decoration: none;
    }
    .account-switch-btn:hover {
      color: #0f172a;
      text-decoration: underline;
    }
  </style>
</head>
<body class="login-page">

  <main class="login-layout">
    <!-- Brand Section -->
    <section class="login-brand" aria-label="HIMS Main System">
      <div class="login-brand-content">
        <div class="login-brand-mark" aria-hidden="true">
          <i class="ph-fill ph-cross"></i>
        </div>
        <p class="login-kicker">Hospital Information Management System</p>
        <h1>HIMS Main System</h1>
        <p class="login-brand-description">
          One connected workspace for hospital operations and financial management modules.
        </p>
        <div class="mt-4 d-flex flex-column gap-2" style="font-size: 0.83rem; color: rgba(255, 255, 255, 0.82);">
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>Enterprise two-factor identity protection</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>Instant one-time code sent directly to your inbox</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="ph-fill ph-check-circle" style="color: #34d399;"></i>
            <span>RA 10173 &amp; HIPAA-compliant workstation gate</span>
          </div>
        </div>
      </div>
      <footer class="login-brand-footer">
        <span>HIMS Command Center</span>
        <span>Version 0.9</span>
      </footer>
    </section>

    <!-- Setup Panel -->
    <section class="login-panel" aria-labelledby="setup-title" style="overflow-y: auto; max-height: 100vh;">
      <div class="login-panel-container d-flex flex-column align-items-center w-100 py-4" style="max-width: 440px;">
        <div class="login-card w-100">

          <!-- Official Hospital / DOH Seal -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon" aria-hidden="true">
              <i class="ph-fill ph-hospital"></i>
            </div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>Department of Health • Public Hospital Network</p>
            </div>
          </div>

          <!-- Hero Icon & Title -->
          <div class="text-center mb-3">
            <div class="shield-icon-wrap" aria-hidden="true">
              <i class="ph-fill ph-envelope-simple"></i>
            </div>
            <h2 id="setup-title" class="login-card-title mb-1">
              Set Up Email Verification
            </h2>
            <p class="login-card-desc mb-0">
              Secure your account with a one-time code sent to your email.
            </p>
          </div>

          @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2 px-3 border-0 mb-3" role="alert" style="font-size: 0.82rem;">
              <i class="ph ph-warning-circle me-1"></i> {{ session('error') }}
            </div>
          @endif

          @if($errors->any())
            <div class="alert alert-danger rounded-3 py-2 px-3 border-0 mb-3 fade-in" role="alert" style="font-size: 0.82rem;" id="error-banner">
              <i class="ph ph-warning-circle me-1"></i> {{ $errors->first() }}
            </div>
          @endif

          <!-- ── Step 1: Send OTP ──────────────────────────────── -->
          <div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
              <div class="step-badge" id="step1-badge">1</div>
              <div class="step-connector"></div>
            </div>
            <div class="w-100 pb-1">
              <p class="fw-semibold mb-2" style="font-size: 0.85rem; color: #1e293b;">
                Send a verification code to your email
              </p>

              <!-- Email address display -->
              <div class="mb-2">
                <div class="email-pill">
                  <i class="ph-fill ph-envelope-simple" style="color: #059669;"></i>
                  <span id="email-display">{{ $maskedEmail }}</span>
                </div>
              </div>

              <!-- Send Button -->
              <button
                type="button"
                id="send-otp-btn"
                class="send-otp-btn"
                onclick="sendSetupOtp()"
              >
                <i class="ph ph-paper-plane-tilt" id="send-icon"></i>
                <span id="send-text">
                  {{ $hasPendingOtp ? 'Resend Code to Email' : 'Send Code to Email' }}
                </span>
              </button>

              <!-- Countdown (shows after send) -->
              <div class="countdown-row mt-2" id="countdown-row">
                <i class="ph ph-timer"></i>
                <span>Resend available in</span>
                <div class="countdown-circle" id="countdown-num">60</div>
                <span>seconds</span>
              </div>

              <!-- Feedback Messages -->
              <div id="send-success" class="alert alert-success rounded-3 py-2 px-3 mt-2 d-none fade-in" style="font-size: 0.78rem;">
                <i class="ph ph-check-circle me-1"></i>
                Code sent! Check your inbox and enter it below.
              </div>
              <div id="send-error" class="alert alert-danger rounded-3 py-2 px-3 mt-2 d-none" style="font-size: 0.78rem;"></div>

              @if($hasPendingOtp)
                <p class="mt-2 mb-0" style="font-size: 0.75rem; color: #059669;">
                  <i class="ph ph-info me-1"></i>
                  A code was already sent to your email. Enter it below or resend.
                </p>
              @endif
            </div>
          </div>

          <!-- ── Step 2: Enter Code ────────────────────────────── -->
          <div class="d-flex gap-3" id="step2-wrapper">
            <div class="d-flex flex-column align-items-center">
              <div class="step-badge" id="step2-badge">2</div>
            </div>
            <div class="w-100" id="step2-content">
              <p class="fw-semibold mb-2" style="font-size: 0.85rem; color: #1e293b;">
                Enter the 6-digit code from your email
              </p>

              <form method="POST" action="{{ route('two-factor.setup.confirm') }}" id="confirm-form" novalidate>
                @csrf

                <!-- 6-digit OTP input boxes -->
                <div class="otp-input-group mb-3" role="group" aria-label="6-digit verification code">
                  <input class="otp-digit" id="otp-d1" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
                  <input class="otp-digit" id="otp-d2" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
                  <input class="otp-digit" id="otp-d3" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
                  <input class="otp-digit" id="otp-d4" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
                  <input class="otp-digit" id="otp-d5" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
                  <input class="otp-digit" id="otp-d6" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
                </div>
                <input type="hidden" name="code" id="otp-assembled">

                <button
                  id="confirm-btn"
                  class="login-submit"
                  type="submit"
                  disabled
                >
                  <i class="ph-fill ph-shield-check me-1" aria-hidden="true"></i>
                  Activate Email Two-Factor Authentication
                </button>

                <p class="text-center mt-2 mb-0" style="font-size: 0.74rem; color: #94a3b8;">
                  <i class="ph ph-clock me-1"></i> Code expires in 10 minutes · Single use only
                </p>
              </form>
            </div>
          </div>

        </div>

        <!-- Cancel and sign out -->
        <div class="mt-3 text-center">
          <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="account-switch-btn">
              <i class="ph ph-arrow-left me-1"></i> Cancel and sign out
            </button>
          </form>
        </div>

        <!-- Compliance Footer -->
        <div class="login-compliance-footer w-100 mt-3">
          <p>
            <i class="ph-fill ph-shield-check me-1 text-secondary align-middle"></i>
            <strong>HIPAA / RA 10173 Compliant:</strong> Mandatory two-factor authentication for hospital financial management.
          </p>
          <div class="login-compliance-meta">
            <span>Security Provisioning</span>
            <span>&bull;</span>
            <span>FMS Transaction Core</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ── OTP Digit Wiring ──────────────────────────────────────────────────────
    const digits      = Array.from(document.querySelectorAll('.otp-digit'));
    const assembled   = document.getElementById('otp-assembled');
    const confirmBtn  = document.getElementById('confirm-btn');

    function syncAssembled() {
      const code = digits.map(d => d.value).join('');
      assembled.value = code;
      const complete = code.length === 6 && /^\d{6}$/.test(code);
      confirmBtn.disabled = !complete;
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
          digits[i - 1].focus(); digits[i - 1].value = ''; syncAssembled();
        }
        if (e.key === 'ArrowLeft' && i > 0)                digits[i - 1].focus();
        if (e.key === 'ArrowRight' && i < digits.length-1) digits[i + 1].focus();
      });
      digit.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        pasted.split('').forEach((c, idx) => { if (digits[idx]) digits[idx].value = c; });
        if (pasted.length) digits[Math.min(pasted.length, digits.length-1)].focus();
        syncAssembled();
      });
    });

    // Shake on error
    @if($errors->any())
      digits.forEach(d => d.classList.add('error'));
      setTimeout(() => digits.forEach(d => d.classList.remove('error')), 600);
    @endif

    // Auto-focus first digit if there's a pending OTP (already sent)
    @if($hasPendingOtp)
      digits[0]?.focus();
    @endif

    // ── Send OTP via fetch ───────────────────────────────────────────────────
    let countdownTimer = null;

    window.sendSetupOtp = async function () {
      const btn        = document.getElementById('send-otp-btn');
      const icon       = document.getElementById('send-icon');
      const text       = document.getElementById('send-text');
      const successEl  = document.getElementById('send-success');
      const errorEl    = document.getElementById('send-error');
      const cdRow      = document.getElementById('countdown-row');
      const cdNum      = document.getElementById('countdown-num');
      const step1Badge = document.getElementById('step1-badge');

      // Reset
      successEl.classList.add('d-none');
      errorEl.classList.add('d-none');
      btn.disabled = true;
      icon.className = 'ph ph-circle-notch';  // loading indicator
      icon.style.animation = 'spin 1s linear infinite';
      text.textContent = 'Sending…';

      try {
        const res  = await fetch('{{ route('two-factor.setup.store') }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':       'application/json',
            'Content-Type': 'application/json',
          },
        });
        const data = await res.json();

        if (res.ok) {
          successEl.classList.remove('d-none');
          step1Badge.classList.add('done');

          // Unlock step 2
          setTimeout(() => digits[0]?.focus(), 200);

          // Start 60-second countdown
          let secs = 60;
          cdNum.textContent = secs;
          cdRow.style.display = 'flex';
          clearInterval(countdownTimer);
          countdownTimer = setInterval(() => {
            secs--;
            cdNum.textContent = secs;
            if (secs <= 0) {
              clearInterval(countdownTimer);
              cdRow.style.display = 'none';
              btn.disabled = false;
              icon.style.animation = '';
              icon.className = 'ph ph-paper-plane-tilt';
              text.textContent = 'Resend Code to Email';
            }
          }, 1000);

        } else {
          const msg = data.error || data.message || 'Failed to send code. Please try again.';
          errorEl.textContent = msg;
          errorEl.classList.remove('d-none');
          btn.disabled = false;
        }
      } catch (err) {
        errorEl.textContent = 'Network error. Please check your connection.';
        errorEl.classList.remove('d-none');
        btn.disabled = false;
      } finally {
        icon.style.animation = '';
        icon.className = 'ph ph-paper-plane-tilt';
        if (btn.disabled && !countdownTimer) {
          text.textContent = 'Resend Code to Email';
        } else if (!btn.disabled) {
          text.textContent = 'Resend Code to Email';
        }
      }
    };

    // Inline spin keyframe for loading icon
    const style = document.createElement('style');
    style.textContent = `@keyframes spin { to { transform: rotate(360deg); } }`;
    document.head.appendChild(style);

    // Prevent browser bfcache restoration if navigating back while enrolled
    window.addEventListener('pageshow', (event) => {
      if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
        window.location.reload();
      }
    });
  </script>
</body>
</html>
