<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Hospital Financial Management System (FMS)</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f4f9;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .login-card {
      max-width: 900px;
      width: 100%;
      background: #fff;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.08);
      border: 0;
    }
    .login-sidebar {
      background: linear-gradient(135deg, #0d6efd, #0a58ca);
      color: #fff;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .demo-btn {
      transition: all 0.2s ease;
      text-align: left;
      border: 1px solid #e9ecef;
      background: #f8f9fa;
    }
    .demo-btn:hover {
      background: #e7f1ff;
      border-color: #0d6efd;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="row g-0">
      <!-- Left Hero Panel -->
      <div class="col-lg-5 login-sidebar d-none d-lg-flex">
        <div>
          <div class="d-flex align-items-center gap-2 mb-4">
            <span class="p-2 rounded-3 bg-white text-primary d-inline-flex"><i class="ph ph-bank fs-3"></i></span>
            <span class="fs-4 fw-bold">IHIMS FMS</span>
          </div>
          <h2 class="fw-bold mb-3">Hospital Financial Management System</h2>
          <p class="opacity-75 fs-sm">
            Transaction Core engine engineered for Philippine statutory compliance, BIR CAS audit standards, PhilHealth ACR, and GAAP double-entry invariance.
          </p>
        </div>

        <div class="pt-4 border-top border-white-50">
          <small class="opacity-75 d-block">Philippine Healthcare Compliance</small>
          <span class="badge bg-white text-primary mt-1">BIR Form 2307 &bull; 1601-EQ &bull; RA 9994</span>
        </div>
      </div>

      <!-- Right Form Panel -->
      <div class="col-lg-7 p-4 p-md-5">
        <div class="mb-4">
          <h3 class="fw-bold text-dark mb-1">Sign In to Workspace</h3>
          <p class="text-muted fs-sm">Enter your credentials or click a demo role below.</p>
        </div>

        @if($errors->any())
          <div class="alert alert-danger rounded-3 py-2 px-3 fs-sm border-0 mb-4">
            <i class="ph ph-warning-circle me-1 align-middle"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="mb-4">
          @csrf
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Email Address</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ph ph-envelope"></i></span>
              <input type="email" name="email" value="{{ old('email', 'cfo@hospital.local') }}" class="form-control bg-light border-start-0" required autofocus>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="ph ph-lock"></i></span>
              <input type="password" name="password" value="password" class="form-control bg-light border-start-0" required>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input type="checkbox" name="remember" class="form-check-input" id="rememberMe" checked>
              <label class="form-check-label fs-xs text-muted" for="rememberMe">Remember Session</label>
            </div>
            <span class="fs-xs text-muted">Default Password: <strong>password</strong></span>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3">
            <i class="ph ph-sign-in me-1"></i> Sign In to Transaction Core
          </button>
        </form>

        <!-- Quick 1-Click Role Switchers -->
        <div class="pt-3 border-top">
          <p class="fs-xs text-muted fw-semibold text-uppercase mb-2"><i class="ph ph-lightning text-warning me-1"></i>Instant Demo Role Switchers (1-Click Login):</p>
          <div class="row g-2">
            <div class="col-6">
              <a href="{{ route('login.quick', 'cfo') }}" class="btn demo-btn w-100 p-2 rounded-3 text-decoration-none">
                <strong class="d-block text-dark fs-xs"><i class="ph ph-shield-check text-primary me-1"></i>CFO Executive</strong>
                <span class="fs-xs text-muted" style="font-size: 10px;">Full access &amp; locks</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'accountant') }}" class="btn demo-btn w-100 p-2 rounded-3 text-decoration-none">
                <strong class="d-block text-dark fs-xs"><i class="ph ph-book-open text-success me-1"></i>Staff Accountant</strong>
                <span class="fs-xs text-muted" style="font-size: 10px;">GL, AP/AR, Reports</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'cashier') }}" class="btn demo-btn w-100 p-2 rounded-3 text-decoration-none">
                <strong class="d-block text-dark fs-xs"><i class="ph ph-hand-coins text-warning me-1"></i>Cashier Supervisor</strong>
                <span class="fs-xs text-muted" style="font-size: 10px;">POS Desk &amp; Receipts</span>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('login.quick', 'auditor') }}" class="btn demo-btn w-100 p-2 rounded-3 text-decoration-none">
                <strong class="d-block text-dark fs-xs"><i class="ph ph-file-search text-info me-1"></i>BIR CAS Auditor</strong>
                <span class="fs-xs text-muted" style="font-size: 10px;">Read-only Audit Log</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
