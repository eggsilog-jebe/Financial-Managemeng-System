<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Workstation Authorization Pending &mdash; HIMS</title>
  <meta name="description" content="This workstation requires authorization from a Super Administrator to access the Hospital Financial Management System.">
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

    /* Animated radar pulse for pending device */
    .radar-container {
      position: relative;
      width: 68px; height: 68px;
      margin: 8px auto 14px;
      display: flex; align-items: center; justify-content: center;
    }
    .radar-core {
      width: 52px; height: 52px; border-radius: 16px;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      display: flex; align-items: center; justify-content: center;
      color: #ffffff; font-size: 1.6rem; position: relative; z-index: 2;
      box-shadow: 0 4px 14px rgba(217,119,6,0.3);
    }
    .radar-ring {
      position: absolute;
      width: 100%; height: 100%; border-radius: 20px;
      border: 2px solid #f59e0b;
      animation: radar-pulse 2s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
      opacity: 0;
    }
    .radar-ring-2 {
      animation-delay: 0.6s;
    }
    @keyframes radar-pulse {
      0%   { transform: scale(0.8); opacity: 0.8; }
      100% { transform: scale(1.4); opacity: 0; }
    }

    .status-badge-pending {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 0.74rem; font-weight: 600; color: #b45309;
      background: #fef3c7; border: 1px solid #fde68a;
      border-radius: 20px; padding: 4px 12px;
    }
    .pulse-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: #d97706;
      animation: dot-blink 1.2s infinite ease-in-out alternate;
    }
    @keyframes dot-blink {
      from { opacity: 0.3; transform: scale(0.8); }
      to   { opacity: 1; transform: scale(1.1); }
    }

    .device-spec-box {
      background: #f8fafc; border: 1px solid #e2e8f0;
      border-radius: 12px; padding: 14px;
      margin-top: 14px; margin-bottom: 16px;
    }
    .device-spec-row {
      display: flex; justify-content: space-between; align-items: center;
      font-size: 0.78rem; padding: 4px 0;
      border-bottom: 1px dashed #f1f5f9;
    }
    .device-spec-row:last-child { border-bottom: none; }
    .device-spec-label { color: #64748b; font-weight: 500; }
    .device-spec-value { color: #0f172a; font-weight: 600; font-family: 'Poppins', sans-serif; }

    .account-switch-btn {
      background: none; border: none; color: #64748b; font-size: 0.8rem; font-weight: 500;
      cursor: pointer; transition: color 0.15s; padding: 0; text-decoration: none;
    }
    .account-switch-btn:hover { color: #0f172a; text-decoration: underline; }
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
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Hardware-bound computer authentication</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Locked to 1&ndash;3 designated hospital workstations</span></div>
          <div class="d-flex align-items-center gap-2"><i class="ph-fill ph-check-circle" style="color: #34d399;"></i><span>Super Admin real-time authorization gate</span></div>
        </div>
      </div>
      <footer class="login-brand-footer"><span>HIMS Command Center</span><span>Version 0.9</span></footer>
    </section>

    <!-- Holding Panel -->
    <section class="login-panel" aria-labelledby="workstation-title">
      <div class="login-panel-container d-flex flex-column align-items-center w-100" style="max-width: 440px;">
        <div class="login-card w-100">

          <!-- Official Hospital / DOH Seal -->
          <div class="hospital-header-seal">
            <div class="hospital-seal-icon" aria-hidden="true"><i class="ph-fill ph-hospital"></i></div>
            <div class="hospital-seal-text">
              <h4>Republic of the Philippines</h4>
              <p>Department of Health &bull; Public Hospital Network</p>
            </div>
          </div>

          <!-- Radar Icon & Title -->
          <div class="text-center mb-2">
            <div class="radar-container" aria-hidden="true">
              <div class="radar-ring"></div>
              <div class="radar-ring radar-ring-2"></div>
              <div class="radar-core"><i class="ph-fill ph-desktop"></i></div>
            </div>
            <span class="status-badge-pending mb-2">
              <span class="pulse-dot"></span> Awaiting Super Admin Approval
            </span>
            <h2 class="h5 fw-bold text-dark mt-2 mb-1" id="workstation-title">Unrecognized Workstation Detected</h2>
            <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
              This computer is not yet authorized for user <strong>{{ $user->name }}</strong>.
              An authorization request has been dispatched in real-time to the Super Administrator.
            </p>
          </div>

          <div id="rejection-alert" class="alert alert-danger rounded-3 py-2 px-3 border-0 mb-3 d-none" role="alert" style="font-size: 0.82rem;">
            <i class="ph ph-x-circle me-1"></i>
            <span id="rejection-text">Access was rejected by the administrator.</span>
          </div>

          <!-- Device Specification Box -->
          <div class="device-spec-box">
            <div class="device-spec-row">
              <span class="device-spec-label"><i class="ph ph-user me-1"></i>Personnel</span>
              <span class="device-spec-value">{{ $user->name }} ({{ $user->role }})</span>
            </div>
            <div class="device-spec-row">
              <span class="device-spec-label"><i class="ph ph-laptop me-1"></i>Operating System</span>
              <span class="device-spec-value">{{ $platform }}</span>
            </div>
            <div class="device-spec-row">
              <span class="device-spec-label"><i class="ph ph-globe me-1"></i>Browser</span>
              <span class="device-spec-value">{{ $browser }}</span>
            </div>
            <div class="device-spec-row">
              <span class="device-spec-label"><i class="ph ph-map-pin me-1"></i>IP Address</span>
              <span class="device-spec-value">{{ $ip }}</span>
            </div>
            <div class="device-spec-row">
              <span class="device-spec-label"><i class="ph ph-fingerprint me-1"></i>Device Fingerprint</span>
              <span class="device-spec-value text-secondary font-monospace" style="font-size: 0.72rem;">{{ substr($deviceUuid, 0, 16) }}...</span>
            </div>
          </div>

          <!-- Live Polling Indicator -->
          <div class="text-center py-2 px-3 rounded-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <div class="d-flex align-items-center justify-content-center gap-2" style="font-size: 0.78rem; color: #166534;">
              <i class="ph ph-broadcast" style="animation: spin 3s linear infinite;"></i>
              <span id="polling-text">Monitoring Super Admin decision in real-time&hellip;</span>
            </div>
          </div>

          <!-- Cancel / Sign out -->
          <div class="text-center mt-3 pt-2" style="border-top: 1px solid #f1f5f9;">
            <form method="POST" action="{{ route('workstation.cancel') }}" style="display:inline;">
              @csrf
              <button type="submit" class="account-switch-btn">
                <i class="ph ph-arrow-left me-1"></i> Cancel request and sign out
              </button>
            </form>
          </div>

        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Real-Time Polling for Super Admin Approval
    const pollUrl = '{{ route('workstation.status') }}';
    const pollText = document.getElementById('polling-text');
    let pollInterval = null;

    async function checkApprovalStatus() {
      try {
        const response = await fetch(pollUrl, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          }
        });

        if (!response.ok) return;

        const data = await response.json();

        if (data.status === 'approved') {
          clearInterval(pollInterval);
          pollText.textContent = '✅ Workstation authorized! Redirecting...';
          pollText.parentElement.style.background = '#dcfce7';
          setTimeout(() => {
            window.location.href = data.redirect_url;
          }, 800);
        } else if (data.status === 'rejected') {
          clearInterval(pollInterval);
          document.getElementById('rejection-alert').classList.remove('d-none');
          if (data.message) {
            document.getElementById('rejection-text').textContent = data.message;
          }
          setTimeout(() => {
            window.location.href = data.redirect_url || '{{ route('login') }}';
          }, 3500);
        } else if (data.status === 'revoked') {
          clearInterval(pollInterval);
          window.location.href = '{{ route('login') }}';
        }
      } catch (err) {
        // network retry
      }
    }

    // Poll every 2.5 seconds
    pollInterval = setInterval(checkApprovalStatus, 2500);
  </script>
</body>
</html>
