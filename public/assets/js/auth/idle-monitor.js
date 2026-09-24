/**
 * idle-monitor.js — Hospital FMS Idle Session Timeout Controller
 *
 * Tracks user activity and enforces a 15-minute idle timeout.
 * - At T-2 minutes: shows a warning countdown modal
 * - At T-0: submits a logout POST to invalidate the server session
 * - On activity: pings /session/heartbeat (max once per 60s) to keep server session alive
 *
 * The server-side IdleSessionTimeout middleware is the hard enforcement mechanism.
 * This JS provides the graceful UX layer on top.
 */
(function () {
  'use strict';

  // ── Configuration ──────────────────────────────────────────────────────────
  const IDLE_TIMEOUT_MS   = 8 * 60 * 60 * 1000; // 8 hours (standard shift)
  const WARNING_BEFORE_MS = 5 * 60 * 1000;      // warn 5 minutes before expiry
  const HEARTBEAT_INTERVAL_MS = 60 * 1000;      // max one server ping per 60s

  // Read CSRF token from meta tag
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  // ── State ──────────────────────────────────────────────────────────────────
  let idleTimer      = null;
  let warningTimer   = null;
  let countdownTimer = null;
  let lastHeartbeat  = 0;
  let warningShown   = false;

  // ── DOM References (injected by app.blade.php) ─────────────────────────────
  const modal         = document.getElementById('idleTimeoutModal');
  const countdownEl   = document.getElementById('idle-countdown-seconds');
  const stayBtn       = document.getElementById('idle-stay-logged-in');
  const logoutBtn     = document.getElementById('idle-logout-now');
  const logoutForm    = document.getElementById('idle-logout-form');

  if (! modal) return; // Guard: only run on authenticated pages

  // Bootstrap modal instance
  const bsModal = window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modal, { backdrop: 'static', keyboard: false }) : null;

  // ── Heartbeat & Displacement Detection ──────────────────────────────────────
  function sendHeartbeat(force = false) {
    const now = Date.now();
    if (!force && (now - lastHeartbeat < HEARTBEAT_INTERVAL_MS)) return;
    lastHeartbeat = now;

    fetch('/session/heartbeat', {
      method:  'POST',
      headers: {
        'Content-Type':     'application/json',
        'X-CSRF-TOKEN':     csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':           'application/json',
      },
      credentials: 'same-origin',
    }).then(response => {
      // If displaced by another concurrent login or terminated by admin, immediately redirect to login
      if (response.status === 401) {
        return response.json().then(data => {
          window.location.href = data.redirect_url || '/login?displaced=1';
        }).catch(() => {
          window.location.href = '/login?displaced=1';
        });
      }
    }).catch(() => {
      // Silently swallow network errors — server-side guard will handle expiry
    });
  }

  // Periodic displacement check every 8 seconds (kicks previous session out in real-time)
  setInterval(() => {
    sendHeartbeat(true);
  }, 8000);

  // ── Countdown Display ──────────────────────────────────────────────────────
  function startCountdown(durationSeconds) {
    let remaining = durationSeconds;

    const update = () => {
      if (countdownEl) countdownEl.textContent = String(remaining);
    };

    update();
    countdownTimer = setInterval(() => {
      remaining -= 1;
      update();

      if (remaining <= 0) {
        clearInterval(countdownTimer);
        forceLogout();
      }
    }, 1000);
  }

  function stopCountdown() {
    if (countdownTimer) {
      clearInterval(countdownTimer);
      countdownTimer = null;
    }
  }

  // ── Warning Modal ──────────────────────────────────────────────────────────
  function showWarningModal() {
    if (warningShown) return;
    warningShown = true;

    if (bsModal) bsModal.show();
    startCountdown(WARNING_BEFORE_MS / 1000);
  }

  function hideWarningModal() {
    warningShown = false;
    stopCountdown();
    if (bsModal) bsModal.hide();
  }

  // ── Force Logout ───────────────────────────────────────────────────────────
  function forceLogout() {
    hideWarningModal();
    if (logoutForm) {
      logoutForm.submit();
    } else {
      // Fallback: redirect to login
      window.location.href = '/login';
    }
  }

  // ── Reset Idle Timer ───────────────────────────────────────────────────────
  function resetIdleTimer() {
    clearTimeout(idleTimer);
    clearTimeout(warningTimer);

    // Only dismiss warning if user actively interacts (not just a timer reset)
    if (warningShown) {
      hideWarningModal();
      sendHeartbeat();
    }

    // Set warning timer (fires 2 min before expiry)
    warningTimer = setTimeout(showWarningModal, IDLE_TIMEOUT_MS - WARNING_BEFORE_MS);

    // Set hard logout timer (fires at expiry)
    idleTimer = setTimeout(forceLogout, IDLE_TIMEOUT_MS);
  }

  // ── Activity Event Listeners ───────────────────────────────────────────────
  const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'];

  // Debounce to avoid firing on every pixel of mouse movement
  let activityDebounce = null;

  function onActivity() {
    if (activityDebounce) return;
    activityDebounce = setTimeout(() => {
      activityDebounce = null;
      sendHeartbeat();
      resetIdleTimer();
    }, 500);
  }

  ACTIVITY_EVENTS.forEach(event => {
    document.addEventListener(event, onActivity, { passive: true });
  });

  // ── Stay Logged In Button ──────────────────────────────────────────────────
  if (stayBtn) {
    stayBtn.addEventListener('click', () => {
      sendHeartbeat();
      hideWarningModal();
      resetIdleTimer();
    });
  }

  // ── Logout Now Button ──────────────────────────────────────────────────────
  if (logoutBtn) {
    logoutBtn.addEventListener('click', forceLogout);
  }

  // ── Init ───────────────────────────────────────────────────────────────────
  resetIdleTimer();

})();
