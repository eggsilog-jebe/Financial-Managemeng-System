<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bank Check - {{ $check->check_number }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; padding: 0; background: #fff; }
      .check-container { border: 2px solid #000 !important; box-shadow: none !important; }
    }
    body { background-color: #f1f5f9; font-family: "Courier New", Courier, monospace; color: #0f172a; padding: 30px; }
    .check-container {
      width: 780px;
      height: 330px;
      background: #f8fafc;
      border: 2px solid #334155;
      border-radius: 6px;
      padding: 24px;
      margin: 0 auto;
      position: relative;
      background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
      background-size: 16px 16px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <!-- Print Actions Toolbar -->
  <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 780px; margin: 0 auto;">
    <button class="btn btn-outline-secondary btn-sm" onclick="window.history.back()">&larr; Back to Register</button>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
      <i class="ph ph-printer me-1"></i> Print Commercial Check
    </button>
  </div>

  <!-- Standard Bank Check Physical Layout -->
  <div class="check-container">
    <!-- Top Row: Bank Info & Check # / Date -->
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h5 class="fw-bold mb-0 text-uppercase font-monospace text-dark">{{ $check->bankAccount?->bank_name ?? 'METROBANK MEDICAL CENTER' }}</h5>
        <div class="fs-xs text-muted">ST. JUDE METROPOLITAN MEDICAL CENTER - DISBURSEMENT</div>
        <div class="fs-xs font-monospace text-muted">Account No: {{ $check->bankAccount?->account_number ?? '1029-9940-11' }}</div>
      </div>
      <div class="text-end">
        <div class="fw-bold font-monospace fs-6 text-danger mb-1">{{ $check->check_number }}</div>
        <div class="border-bottom border-dark pb-1 text-center font-monospace" style="min-width: 160px;">
          DATE: <strong>{{ $check->check_date ? $check->check_date->format('M d, Y') : date('M d, Y') }}</strong>
        </div>
      </div>
    </div>

    <!-- Payee & Numeric Amount Line -->
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 pt-2">
      <div class="d-flex align-items-center flex-grow-1 border-bottom border-dark pb-1">
        <span class="fw-bold text-uppercase fs-xs text-muted me-2" style="font-family: sans-serif;">PAY TO THE ORDER OF:</span>
        <span class="fw-bold text-dark fs-6 font-monospace flex-grow-1">*** {{ $check->payee_name }} ***</span>
      </div>
      <div class="border border-2 border-dark rounded px-3 py-1 bg-white text-end font-monospace fw-bold fs-5 text-dark" style="min-width: 190px;">
        ₱ {{ number_format((float) $check->amount, 2) }}
      </div>
    </div>

    <!-- Amount in English Words Line -->
    <div class="d-flex align-items-center border-bottom border-dark pb-1 mb-4">
      <span class="fw-bold text-uppercase fs-xs text-muted me-2" style="font-family: sans-serif;">PESOS:</span>
      <span class="fw-bold text-dark fs-6 font-monospace flex-grow-1">*** {{ $check->amount_in_words }} ***</span>
    </div>

    <!-- Bottom Row: Signatures & MICR Band -->
    <div class="d-flex justify-content-between align-items-end pt-3">
      <div class="fs-xs text-muted">
        <div>Voucher Ref: <span class="font-monospace fw-bold">{{ $check->disbursementVoucher?->voucher_number ?? 'N/A' }}</span></div>
        <div class="font-monospace text-secondary pt-2">|: {{ $check->check_number }} :| 004991234 |: 1029994011 :|' 01</div>
      </div>
      <div class="d-flex gap-4">
        <div class="text-center" style="width: 170px;">
          <div class="border-bottom border-dark mb-1" style="height: 35px;"></div>
          <span class="fs-xs text-muted text-uppercase d-block" style="font-family: sans-serif;">Authorized Signature</span>
        </div>
        <div class="text-center" style="width: 170px;">
          <div class="border-bottom border-dark mb-1" style="height: 35px;"></div>
          <span class="fs-xs text-muted text-uppercase d-block" style="font-family: sans-serif;">Chief Financial Officer</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
