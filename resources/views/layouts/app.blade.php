<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Financial Management System (FMS)')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="{{ asset('assets/js/core/theme-boot.js') }}"></script>
    <script src="{{ asset('assets/js/auth/session.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ @filemtime(public_path('assets/css/style.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/modal-design-system.css') }}?v={{ @filemtime(public_path('assets/css/components/modal-design-system.css')) }}">
  </head>
  <body data-module="@yield('module', 'main')" data-page="@yield('page', 'dashboard')">
    <div class="app-shell" data-auth-guard>
      @include('partials.sidebar')
      <div class="sidebar-backdrop" data-sidebar-backdrop aria-hidden="true"></div>

      <main class="main-content" id="main-content">
        @include('partials.headbar')

        <section class="page-wrapper" aria-label="@yield('page-label', 'FMS workspace')">
          @yield('content')
        </section>

        @include('partials.footer')
      </main>
    </div>

    <div id="modal-portal" aria-live="polite"></div>
    <div id="toast-container" role="status" aria-live="polite" aria-atomic="true"></div>

    {{-- ── Idle Session Timeout Warning Modal ─────────────────────────────────── --}}
    <div class="modal fade" id="idleTimeoutModal" tabindex="-1" aria-labelledby="idleTimeoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header border-0 pt-4 px-4 pb-2" style="background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);">
            <div class="d-flex align-items-center gap-3">
              <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                <i class="ph-fill ph-clock-countdown text-white fs-4"></i>
              </span>
              <div>
                <h5 class="modal-title fw-bold mb-0" style="color:#92400e;" id="idleTimeoutModalLabel">Session Expiring Soon</h5>
                <p class="mb-0" style="font-size:0.72rem;color:#b45309;font-weight:500;">Hospital Security • Inactivity Detected</p>
              </div>
            </div>
          </div>
          <div class="modal-body px-4 py-3">
            <p class="text-secondary mb-3" style="font-size:0.875rem;">
              Your HIMS session will automatically sign out in
            </p>
            <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
              <div class="text-center p-3 rounded-4" style="background:#fef3c7;border:2px solid #fde68a;min-width:90px;">
                <div class="fw-bold" style="font-size:2.25rem;color:#b45309;line-height:1;font-variant-numeric:tabular-nums;" id="idle-countdown-seconds">300</div>
                <div style="font-size:0.7rem;color:#92400e;font-weight:600;letter-spacing:0.05em;">SECONDS</div>
              </div>
            </div>
            <p class="text-secondary mb-0" style="font-size:0.8rem;">
              <i class="ph ph-shield-warning me-1 text-warning align-middle"></i>
              To protect sensitive patient financial data, inactive sessions are closed automatically per RA 10173 compliance.
            </p>
          </div>
          <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
            <button type="button" class="btn btn-warning fw-semibold px-4 rounded-3" id="idle-stay-logged-in" style="background:linear-gradient(135deg,#f59e0b,#d97706);border:none;color:#fff;box-shadow:0 2px 8px rgba(245,158,11,0.3);">
              <i class="ph ph-hand-waving me-1"></i>I'm still here
            </button>
            <button type="button" class="btn btn-outline-secondary fw-medium px-3 rounded-3" id="idle-logout-now" style="font-size:0.85rem;">
              <i class="ph ph-sign-out me-1"></i>Sign out now
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Hidden form used by idle-monitor.js to POST /logout on timeout --}}
    <form id="idle-logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
      @csrf
    </form>

    <!-- Global Executive System Alert Modal -->
    <div class="modal fade" id="systemAlertModal" tabindex="-1" aria-labelledby="systemAlertModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header border-0 bg-light-subtle pt-4 px-4 pb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="ph ph-info fs-4" id="systemModalIcon"></i>
              </span>
              <h5 class="modal-title fw-bold text-dark mb-0" id="systemModalTitle">System Notification</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 pt-2">
            <p class="text-secondary fs-sm mb-0" id="systemModalMessage">Notification content...</p>
          </div>
          <div class="modal-footer border-0 bg-light-subtle p-3 px-4">
            <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold rounded-3" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/data/module-registry.js') }}"></script>
    <script src="{{ asset('assets/js/core/app-shell.js') }}?v={{ @filemtime(public_path('assets/js/core/app-shell.js')) }}"></script>
    <script src="{{ asset('assets/js/core/modal-system.js') }}?v={{ @filemtime(public_path('assets/js/core/modal-system.js')) }}"></script>

    <script>
      window.showSystemModal = function(message, title = 'System Notification', iconClass = 'ph-info') {
        const titleEl = document.getElementById('systemModalTitle');
        const msgEl = document.getElementById('systemModalMessage');
        const iconEl = document.getElementById('systemModalIcon');
        const modalEl = document.getElementById('systemAlertModal');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (iconEl) iconEl.className = 'ph ' + iconClass + ' fs-4';

        if (modalEl && window.bootstrap) {
          const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
          modalInstance.show();
        }
      };

      // Override native browser alert() to turn every alert into an executive Bootstrap popup modal
      window.alert = function(message) {
        let title = 'System Action';
        let icon = 'ph-info';
        
        if (typeof message === 'string') {
          const lower = message.toLowerCase();
          if (lower.includes('export') || lower.includes('download') || lower.includes('print') || lower.includes('report') || lower.includes('voucher') || lower.includes('manifest')) {
            title = 'Report Export';
            icon = 'ph-file-arrow-down';
          } else if (lower.includes('success') || lower.includes('verified') || lower.includes('posted') || lower.includes('released') || lower.includes('refreshed')) {
            title = 'Action Completed';
            icon = 'ph-check-circle';
          } else if (lower.includes('warning') || lower.includes('reject') || lower.includes('lock') || lower.includes('error')) {
            title = 'System Alert';
            icon = 'ph-warning';
          }
        }

        window.showSystemModal(message, title, icon);
      };

      // Prevent browser bfcache restoration of authenticated pages on back-forward navigation
      window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
          window.location.reload();
        }
      });

      // Speculative prefetch with hover-intent (250ms debounce) to prevent flooding PHP workers
      document.addEventListener('DOMContentLoaded', function () {
        const prefetched = new Set();
        const prefetchLink = function (url) {
          if (!url || prefetched.has(url) || url.startsWith('#') || url.includes('/logout') || url.includes('javascript:')) return;
          try {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            link.as = 'document';
            document.head.appendChild(link);
            prefetched.add(url);
          } catch (_) {}
        };

        document.querySelectorAll('.sidebar-nav a[href], .dashboard-header a[href], .module-card a[href]').forEach(function (el) {
          const href = el.getAttribute('href');
          if (href && !href.startsWith('#')) {
            let timer = null;
            el.addEventListener('mouseenter', function () {
              timer = setTimeout(function () { prefetchLink(href); }, 250);
            }, { passive: true });
            el.addEventListener('mouseleave', function () {
              if (timer) clearTimeout(timer);
            }, { passive: true });
            el.addEventListener('focus', function () { prefetchLink(href); }, { passive: true });
            el.addEventListener('touchstart', function () { prefetchLink(href); }, { passive: true });
          }
        });
      });
    </script>
    <script src="{{ asset('assets/js/auth/idle-monitor.js') }}"></script>
    @stack('scripts')
  </body>
</html>
