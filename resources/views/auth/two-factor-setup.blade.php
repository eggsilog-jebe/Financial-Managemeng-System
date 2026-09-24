<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Set Up Google Authenticator &mdash; HIMS</title>
  <meta name="description" content="Set up TOTP Two-Factor Authentication with Google Authenticator for your Hospital Financial Management System account.">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
  <style>
    /* -- TOTP Setup Page (FMS Unified Theme) ------------------------- */
    .login-panel { padding: 20px 24px !important; }
    .login-card {
      padding: 24px 28px !important;
      border-radius: 14px !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 10px 25px -5px rgba(15,23,42,0.06), 0 8px 10px -6px rgba(15,23,42,0.04) !important;
    }
    .hospital-header-seal {
      display: flex; align-items: center; gap: 12px;
      padding-bottom: 10px; margin-bottom: 14px;
      border-bottom: 1px solid #f1f5f9;
    }
    .hospital-seal-icon {
      width: 34px; height: 34px; border-radius: 8px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff; display: flex; align-items: center;
      justify-content: center; font-size: 1.15rem; flex-shrink: 0;
      box-shadow: 0 2px 4px rgba(5,150,105,0.2);
    }
    .hospital-seal-text h4 { margin: 0; font-size: 0.82rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em; }
    .hospital-seal-text p  { margin: 0; font-size: 0.67rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
    .shield-icon-wrap {
      width: 56px; height: 56px; border-radius: 16px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.7rem; color: #ffffff;
      box-shadow: 0 8px 20px rgba(5,150,105,0.25);
      margin: 4px auto 12px;
    }
    .login-card-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
    .login-card-desc  { font-size: 0.82rem; color: #64748b; line-height: 1.45; }

    /* Step badges */
    .step-badge {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: 50%;
      background: linear-gradient(135deg, #059669, #047857);
      color: #ffffff; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
      box-shadow: 0 2px 6px rgba(5,150,105,0.25);
    }
    .step-connector {
      width: 2px; height: 22px;
      background: linear-gradient(180deg, rgba(5,150,105,0.35), rgba(5,150,105,0.08));
      margin: 6px auto;
    }

    /* QR Code container */
    .qr-wrap {
      display: flex; flex-direction: column; align-items: center;
      background: #f8fafc; border: 1.5px solid #e2e8f0;
      border-radius: 14px; padding: 16px; gap: 10px;
    }
    .qr-wrap svg { width: 160px; height: 160px; border-radius: 8px; }
    .qr-label { font-size: 0.74rem; color: #64748b; font-weight: 500; text-align: center; }

    /* App badge pills */
    .app-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: #f0fdf4; border: 1px solid #bbf7d0;
      border-radius: 100px; padding: 5px 12px;
      font-size: 0.78rem; font-weight: 600; color: #065f46;
    }

    /* Regenerate link */
    .regen-btn {
      background: none; border: none; color: #059669;
      font-size: 0.78rem; font-weight: 500; cursor: pointer;
      padding: 0; text-decoration: none; transition: color 0.15s;
    }
    .regen-btn:hover { color: #047857; text-decoration: underline; }
    .regen-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    /* OTP digit boxes */
    .otp-input-group { display: flex; gap: 8px; justify-content: center; }
    .otp-digit {
      width: 48px; height: 56px; text-align: center;
      font-size: 1.55rem; font-weight: 700;
      font-family: 'Poppins', sans-serif; font-variant-numeric: tabular-nums;
      border: 1.5px solid #cbd5e1; border-radius: 12px;
      outline: none; transition: border-color 0.15s, box-shadow 0.15s, background 0.15s, transform 0.1s;
      color: #0f172a; background: #ffffff; caret-color: transparent;
    }
    .otp-digit:focus {
      border-color: #059669; box-shadow: 0 0 0 3.5px rgba(5,150,105,0.16);
      background: #ffffff; transform: translateY(-1px);
    }
    .otp-digit.filled { border-color: #059669; background: #f0fdf4; color: #065f46; }
    .otp-digit.error  { border-color: #ef4444; background: #fef2f2; color: #dc2626; animation: shake 0.35s ease-in-out; }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%      { transform: translateX(-6px); }
      60%      { transform: translateX(6px); }
    }
    @keyframes pulse-in {
      0%   { opacity: 0; transform: scale(0.95) translateY(8px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .fade-in { animation: pulse-in 0.35s ease both; }

    /* Submit button */
    .login-submit {
      width: 100%; min-height: 42px;
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
      color: #ffffff !important; border: none !important; border-radius: 10px !important;
      font-size: 0.88rem !important; font-weight: 600 !important;
      box-shadow: 0 4px 12px rgba(5,150,105,0.25) !important;
      transition: all 0.2s cubic-bezier(0.16,1,0.3,1) !important; cursor: pointer;
    }
    .login-submit:hover:not(:disabled) {
      background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
      box-shadow: 0 6px 16px rgba(5,150,105,0.35); transform: translateY(-1px);
      color: #ffffff !important;
    }
    .login-submit:disabled {
      background: #cbd5e1 !important; color: #94a3b8 !important;
      box-shadow: none !important; cursor: not-allowed; transform: none !important; opacity: 0.7;
    }
    .account-switch-btn {
      background: none; border: none; color: #64748b; font-size: 0.8rem; font-weight: 500;
      cursor: pointer; transition: color 0.15s; padding: 0; text-decoration: none;
    }
    .account-switch-btn:hover { color: #0f172a; text-decoration: underline; }
  </style>
</head>
<body class="login-page">

  <main class="login-layout">
    <!-- Brand Section -->
    <section class="login-brand" aria-label="HIMS Main System">
      <div class="login-brand-content">
        <div class="login-brand-mark" aria-hidden="true"><i class="ph-fill ph-cross"></i></div>
        <p class="login-kicker">Hospital Information Management System</p>
        <h1>HIMS Main System</h1>
        <p class="login-brand-description">One connected workspace for hospital operations and financial management modules.</p>
        <div class="mt-4 d-flex flex-column gap-2" style="font-size: 0.83rem; color: rgba(255,255,255,0.82);">
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Google Authenticator TOTP protection</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>No internet required for code generation</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>RA 10173 &amp; HIPAA-compliant access gate</span></div>
        </div>
      </div>
      <footer class="login-brand-footer">
        <span>HIMS Command Center</span><span>Version 0.9</span>
      </footer>
    </section>

    <!-- Setup Panel -->
    <section class="login-panel" aria-labelledby="setup-title" style="overflow-y: auto; max-height: 100vh;">
      <div class="login-panel-container d-flex flex-column align-items-center w-100 py-4" style="max-width: 440px;">
        <div class="login-card w-100">

          <!-- Official Hospital / DOH Seal -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon" aria-hidden="true"><i class="ph-fill ph-hospital"></i></div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>Department of Health &bull; Public Hospital Network</p>
            </div>
          </div>

          <!-- Hero Icon & Title -->
          <div class="text-center mb-3">
            <div class="shield-icon-wrap" aria-hidden="true">
              <i class="ph-fill ph-qr-code"></i>
            </div>
            <h2 id="setup-title" class="login-card-title mb-1">Set Up Google Authenticator</h2>
            <p class="login-card-desc mb-0">Scan the QR code with your authenticator app, then enter the 6-digit code to activate 2FA.</p>
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

          <!-- Step 1: Download App -->
          <div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
              <div class="step-badge">1</div>
              <div class="step-connector"></div>
            </div>
            <div class="w-100 pb-1">
              <p class="fw-semibold mb-2" style="font-size: 0.85rem; color: #1e293b;">Install an authenticator app</p>
              <div class="d-flex gap-2 flex-wrap">
                <span class="app-badge"><i class="ph-fill ph-device-mobile-camera" style="color: #059669;"></i> Google Authenticator</span>
                <span class="app-badge"><i class="ph-fill ph-device-mobile-camera" style="color: #059669;"></i> Microsoft Authenticator</span>
              </div>
              <p class="mt-2 mb-0" style="font-size: 0.75rem; color: #64748b;">Available on the App Store &amp; Google Play</p>
            </div>
          </div>

          <!-- Step 2: Scan QR Code -->
          <div class="d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
              <div class="step-badge">2</div>
              <div class="step-connector"></div>
            </div>
            <div class="w-100 pb-1">
              <p class="fw-semibold mb-2" style="font-size: 0.85rem; color: #1e293b;">Scan this QR code with your app</p>

              <div class="qr-wrap" id="qr-container">
                {!! $qrCodeSvg !!}
                <p class="qr-label mb-0">
                  <i class="ph ph-envelope me-1" style="color: #64748b;"></i>
                  {{ $userEmail }}
                </p>
              </div>

              <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                <button type="button" class="regen-btn" id="regen-btn" onclick="regenerateQr()">
                  <i class="ph ph-arrows-clockwise me-1"></i>Regenerate QR code
                </button>
                <span id="regen-spinner" class="d-none" style="font-size: 0.75rem; color: #64748b;">
                  <i class="ph ph-circle-notch" style="animation: spin 0.8s linear infinite;"></i> Generating...
                </span>
              </div>
            </div>
          </div>

          <!-- Step 3: Enter Code -->
          <div class="d-flex gap-3">
            <div class="d-flex flex-column align-items-center">
              <div class="step-badge">3</div>
            </div>
            <div class="w-100">
              <p class="fw-semibold mb-2" style="font-size: 0.85rem; color: #1e293b;">Enter the 6-digit code from your app</p>

              <form method="POST" action="{{ route('two-factor.setup.confirm') }}" id="confirm-form" novalidate>
                @csrf

                <div class="otp-input-group mb-3" role="group" aria-label="6-digit TOTP code">
                  <input class="otp-digit" id="otp-d1" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
                  <input class="otp-digit" id="otp-d2" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
                  <input class="otp-digit" id="otp-d3" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
                  <input class="otp-digit" id="otp-d4" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
                  <input class="otp-digit" id="otp-d5" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
                  <input class="otp-digit" id="otp-d6" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
                </div>
                <input type="hidden" name="code" id="otp-assembled">

                <button id="confirm-btn" class="login-submit" type="submit" disabled>
                  <i class="ph-fill ph-shield-check me-1" aria-hidden="true"></i>
                  Activate Google Authenticator 2FA
                </button>

                <p class="text-center mt-2 mb-0" style="font-size: 0.74rem; color: #94a3b8;">
                  <i class="ph ph-clock me-1"></i> TOTP codes refresh every 30 seconds
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
            <strong>HIPAA / RA 10173 Compliant:</strong> TOTP-based 2FA provides phishing-resistant authentication for hospital financial records.
          </p>
          <div class="login-compliance-meta">
            <span>Security Provisioning</span><span>&bull;</span><span>FMS Transaction Core</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // -- OTP Digit Wiring ----------------------------------------------------
    const digits     = Array.from(document.querySelectorAll('.otp-digit'));
    const assembled  = document.getElementById('otp-assembled');
    const confirmBtn = document.getElementById('confirm-btn');

    function syncAssembled() {
      const code     = digits.map(d => d.value).join('');
      assembled.value = code;
      const complete  = code.length === 6 && /^\d{6}$/.test(code);
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
        if (e.key === 'ArrowLeft'  && i > 0)               digits[i - 1].focus();
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
      digits[0]?.focus();
    @endif

    // Auto-focus first digit on load
    window.addEventListener('DOMContentLoaded', () => digits[0]?.focus());

    // -- Regenerate QR Code --------------------------------------------------
    window.regenerateQr = async function () {
      const btn      = document.getElementById('regen-btn');
      const spinner  = document.getElementById('regen-spinner');
      const qrWrap   = document.getElementById('qr-container');

      btn.disabled = true;
      btn.classList.add('d-none');
      spinner.classList.remove('d-none');

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

        if (res.ok && data.qr_svg) {
          // Replace QR content preserving email label
          const label = qrWrap.querySelector('.qr-label');
          qrWrap.innerHTML = data.qr_svg;
          if (label) qrWrap.appendChild(label);
          // Clear inputs
          digits.forEach(d => { d.value = ''; d.classList.remove('filled', 'error'); });
          syncAssembled();
          digits[0]?.focus();
        }
      } catch (_) { /* silently fail */ }
      finally {
        btn.disabled = false;
        btn.classList.remove('d-none');
        spinner.classList.add('d-none');
      }
    };

    // Inline spin style
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
    document.head.appendChild(style);

    // Prevent bfcache restoration
    window.addEventListener('pageshow', (event) => {
      if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
        window.location.reload();
      }
    });
  </script>
</body>
</html>
