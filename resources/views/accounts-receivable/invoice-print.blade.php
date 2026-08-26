<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospital Billing Statement - {{ $invoice->invoice_number }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { font-size: 12px; }
    }
    body { background-color: #f8fafc; color: #1e293b; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .invoice-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  </style>
</head>
<body class="p-4">
  <div class="container" style="max-width: 850px;">
    <!-- Print Button Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
      <button class="btn btn-outline-secondary btn-sm" onclick="window.history.back()">&larr; Back to Invoices</button>
      <button class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print / Save as PDF
      </button>
    </div>

    <!-- Official Billing Statement Card -->
    <div class="invoice-card p-5 border">
      <!-- Hospital Header -->
      <div class="row align-items-center border-bottom pb-4 mb-4">
        <div class="col-8">
          <h3 class="fw-bold text-dark mb-0">ST. JUDE METROPOLITAN MEDICAL CENTER</h3>
          <p class="text-muted small mb-0">1029 Ortigas Center, Pasig City, Metro Manila, Philippines</p>
          <p class="text-muted fs-xs mb-0">BIR VAT Reg. TIN: 004-991-234-000 | CAS Permit: CAS-2026-MED-0991</p>
        </div>
        <div class="col-4 text-end">
          <div class="badge bg-primary fs-6 px-3 py-2 text-uppercase mb-2">Billing Statement</div>
          <div class="font-monospace fw-bold fs-6 text-dark">{{ $invoice->invoice_number }}</div>
          <div class="fs-xs text-muted">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
        </div>
      </div>

      <!-- Patient Information -->
      <div class="row g-3 bg-light rounded-3 p-3 mb-4">
        <div class="col-6">
          <div class="fs-xs text-muted text-uppercase fw-semibold">Patient Full Name</div>
          <div class="fw-bold text-dark fs-6">{{ $invoice->patientAccount?->full_name ?? 'Walk-In Patient' }}</div>
          <div class="fs-xs text-muted font-monospace mt-1">MRN: {{ $invoice->patientAccount?->patient_id_number ?? 'N/A' }}</div>
        </div>
        <div class="col-3">
          <div class="fs-xs text-muted text-uppercase fw-semibold">Admission Type</div>
          <div class="fw-semibold text-dark">{{ $invoice->patientAccount?->admission_type ?? 'Inpatient' }}</div>
          <div class="fs-xs text-muted mt-1">Due Date: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Immediate' }}</div>
        </div>
        <div class="col-3">
          <div class="fs-xs text-muted text-uppercase fw-semibold">HMO Provider / Policy</div>
          <div class="fw-semibold text-primary">{{ $invoice->patientAccount?->hmo_provider ?? 'Self-Pay' }}</div>
          <div class="fs-xs text-muted mt-1">Status: <span class="fw-bold">{{ $invoice->status }}</span></div>
        </div>
      </div>

      <!-- Itemized Hospital Charges -->
      <h6 class="fw-bold text-dark mb-2 text-uppercase fs-xs">Itemized Departmental Charges</h6>
      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 5%;">#</th>
              <th style="width: 20%;">Department</th>
              <th style="width: 45%;">Procedure / Service Particulars</th>
              <th style="width: 10%;" class="text-center">Qty</th>
              <th style="width: 10%;" class="text-end">Unit Price</th>
              <th style="width: 10%;" class="text-end">Gross (₱)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoice->items as $idx => $item)
            <tr>
              <td>{{ $idx + 1 }}</td>
              <td><span class="badge bg-light text-dark border font-monospace">{{ $item->department }}</span></td>
              <td class="fw-medium text-dark">{{ $item->description }}</td>
              <td class="text-center font-monospace">{{ number_format((float) $item->quantity, 0) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $item->unit_price, 2) }}</td>
              <td class="text-end font-monospace fw-semibold">₱{{ number_format((float) $item->gross_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center py-3 text-muted">No itemized charges listed.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Financial Calculation Breakdown -->
      <div class="row justify-content-end mb-4">
        <div class="col-6">
          <div class="table-responsive">
            <table class="table table-sm table-borderless align-middle mb-0">
              <tbody>
                <tr>
                  <td class="text-muted fw-semibold">Gross Hospital Charges:</td>
                  <td class="text-end font-monospace fw-bold text-dark">₱{{ number_format((float) $invoice->total_amount, 2) }}</td>
                </tr>
                @if((float) $invoice->discount_amount > 0)
                <tr>
                  <td class="text-danger">Less: Senior Citizen / PWD &amp; VAT Relief:</td>
                  <td class="text-end font-monospace text-danger">-₱{{ number_format((float) $invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if((float) $invoice->insurance_covered > 0)
                <tr>
                  <td class="text-info">Less: PhilHealth ACR &amp; HMO Coverage:</td>
                  <td class="text-end font-monospace text-info">-₱{{ number_format((float) $invoice->insurance_covered, 2) }}</td>
                </tr>
                @endif
                @if((float) $invoice->paid_amount > 0)
                <tr>
                  <td class="text-success">Less: Cashier Payments Settled:</td>
                  <td class="text-end font-monospace text-success">-₱{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="border-top">
                  <td class="fw-bold fs-6 text-dark">Net Patient Balance Due:</td>
                  <td class="text-end font-monospace fw-bold fs-5 text-danger">₱{{ number_format((float) $invoice->balance_due, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Footer & Signatures -->
      <div class="row pt-5 mt-5 border-top text-center fs-xs text-muted">
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Prepared by: Billing Clerk</div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Verified by: Patient / Representative</div>
        </div>
        <div class="col-4">
          <div class="border-top pt-2 mx-3">Authorized by: Hospital Cashier</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
