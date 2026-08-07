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

    <script src="{{ asset('assets/js/data/module-registry.js') }}"></script>
    <script src="{{ asset('assets/js/core/app-shell.js') }}"></script>
    @stack('scripts')
  </body>
</html>
