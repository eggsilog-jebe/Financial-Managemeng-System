<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statement of Account - {{ $statement['patient']->full_name }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { font-size: 12px; }
    }
    body { background-color: #f8fafc; color: #1e293b; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .soa-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  </style>
</head>
<body class="p-4">
  <div class="container" style="max-width: 900px;">
    <!-- Print Button Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
      <button class="btn btn-outline-secondary btn-sm" onclick="window.history.back()">&larr; Back to Statements</button>
      <button class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print / Save as PDF
      </button>
    </div>

    <!-- Official Statement Document -->
    <div class="soa-card p-5 border">
      <!-- Header -->
      <div class="row align-items-center border-bottom pb-4 mb-4">
        <div class="col-8">
          <h3 class="fw-bold text-dark mb-0">ST. JUDE METROPOLITAN MEDICAL CENTER</h3>
          <p class="text-muted small mb-0">1029 Ortigas Center, Pasig City, Metro Manila, Philippines</p>
          <p class="text-muted fs-xs mb-0">BIR VAT Reg. TIN: 004-991-234-000 | CAS Permit: CAS-2026-MED-0991</p>
        </div>
        <div class="col-4 text-end">
          <div class="badge bg-danger fs-6 px-3 py-2 text-uppercase mb-2">Statement of Account</div>
          <div class="font-monospace fw-bold fs-6 text-dark">SOA-{{ date('Ymd') }}-{{ $statement['patient']->id }}</div>
          <div class="fs-xs text-muted">Statement Date: {{ date('M d, Y') }}</div>
        </div>
      </div>

      <!-- Debtor Profile -->
      <div class="row g-3 bg-light rounded-3 p-3 mb-4">
        <div class="col-6">
          <div class="fs-xs text-muted text-uppercase fw-semibold">Patient / Debtor Name</div>
          <div class="fw-bold text-dark fs-6">{{ $statement['patient']->full_name }}</div>
          <div class="fs-xs text-muted font-monospace mt-1">MRN: {{ $statement['patient']->patient_id_number }}</div>
        </div>
        <div class="col-3">
          <div class="fs-xs text-muted text-uppercase fw-semibold">Statement Period</div>
          <div class="fw-semibold text-dark">{{ $statement['start_date'] }} to {{ $statement['end_date'] }}</div>
          <div class="fs-xs text-muted mt-1">Type: {{ $statement['patient']->admission_type ?? 'Inpatient' }}</div>
        </div>
        <div class="col-3">
          <div class="fs-xs text-muted text-uppercase fw-semibold">HMO Provider / Guarantees</div>
          <div class="fw-semibold text-primary">{{ $statement['patient']->hmo_provider ?? 'Self-Pay' }}</div>
          <div class="fs-xs text-danger fw-bold mt-1">Due: ₱{{ number_format((float) $statement['ending_balance'], 2) }}</div>
        </div>
      </div>

      <!-- Financial Metrics Summary Box -->
      <div class="row g-2 mb-4 text-center">
        <div class="col-3">
          <div class="border rounded p-2">
            <span class="fs-xs text-muted text-uppercase d-block">Beginning Bal</span>
            <span class="font-monospace fw-bold text-dark">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</span>
          </div>
        </div>
        <div class="col-3">
          <div class="border rounded p-2">
            <span class="fs-xs text-muted text-uppercase d-block">Invoiced Charges</span>
            <span class="font-monospace fw-bold text-primary">+₱{{ number_format((float) $statement['total_debits'], 2) }}</span>
          </div>
        </div>
        <div class="col-3">
          <div class="border rounded p-2">
            <span class="fs-xs text-muted text-uppercase d-block">Payments &amp; Credits</span>
            <span class="font-monospace fw-bold text-success">-₱{{ number_format((float) $statement['total_credits'], 2) }}</span>
          </div>
        </div>
        <div class="col-3">
          <div class="border rounded p-2 bg-danger-subtle">
            <span class="fs-xs text-danger text-uppercase fw-bold d-block">Ending Balance Due</span>
            <span class="font-monospace fw-bold text-danger fs-6">₱{{ number_format((float) $statement['ending_balance'], 2) }}</span>
          </div>
        </div>
      </div>

      <!-- Ledger Movements Table -->
      <h6 class="fw-bold text-dark mb-2 text-uppercase fs-xs">Chronological Transaction History</h6>
      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 12%;">Date</th>
              <th style="width: 12%;">Type</th>
              <th style="width: 18%;">Reference #</th>
              <th style="width: 28%;">Description / Particulars</th>
              <th style="width: 10%;" class="text-end">Charges</th>
              <th style="width: 10%;" class="text-end">Credits</th>
              <th style="width: 10%;" class="text-end">Balance (₱)</th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-light-subtle">
              <td>{{ $statement['start_date'] }}</td>
              <td><span class="badge bg-light text-muted border">FORWARD</span></td>
              <td>—</td>
              <td>Beginning Balance Forwarded</td>
              <td class="text-end font-monospace">—</td>
              <td class="text-end font-monospace">—</td>
              <td class="text-end font-monospace fw-bold">₱{{ number_format((float) $statement['beginning_balance'], 2) }}</td>
            </tr>
            @forelse($statement['movements'] as $m)
            <tr>
              <td>{{ $m['date'] }}</td>
              <td><span class="badge bg-light text-dark border font-monospace">{{ $m['type'] }}</span></td>
              <td class="font-monospace fw-semibold">{{ $m['reference'] }}</td>
              <td>{{ $m['description'] }}</td>
              <td class="text-end font-monospace text-primary">
                {{ (float)$m['debit'] > 0 ? '₱' . number_format((float)$m['debit'], 2) : '—' }}
              </td>
              <td class="text-end font-monospace text-success">
                {{ (float)$m['credit'] > 0 ? '₱' . number_format((float)$m['credit'], 2) : '—' }}
              </td>
              <td class="text-end font-monospace fw-bold text-danger">
                ₱{{ number_format((float)$m['balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-3 text-muted">No transactions in period.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold font-monospace">
            <tr>
              <td colspan="4">TOTALS:</td>
              <td class="text-end text-primary">₱{{ number_format((float) $statement['total_debits'], 2) }}</td>
              <td class="text-end text-success">₱{{ number_format((float) $statement['total_credits'], 2) }}</td>
              <td class="text-end text-danger fs-6">₱{{ number_format((float) $statement['ending_balance'], 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Payment Remittance Notice & Signatures -->
      <div class="p-3 bg-light rounded-3 mb-5 fs-xs text-muted">
        <strong>Payment Remittance Notice:</strong> Please present this statement at the Hospital Cashier Desk or remit via Online Banking/EFT. Checks must be made payable to <em>St. Jude Metropolitan Medical Center</em>.
      </div>

      <div class="row pt-4 text-center fs-xs text-muted">
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Prepared by: Billing Clerk</div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Audited by: AR Accountant</div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Approved by: Finance Manager</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
