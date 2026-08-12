<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Financial Management System (FMS)')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="{{ asset('assets/js/core/theme-boot.js') }}"></script>
    <script src="{{ asset('assets/js/auth/session.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/components/typography-accessibility.css') }}">
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
    <script src="{{ asset('assets/js/core/app-shell.js') }}"></script>

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
    </script>
    @stack('scripts')
  </body>
</html>
