<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Receipt - {{ $payment->officialReceipt?->or_number ?? $payment->payment_reference }}</title>
  <style>
    @media print {
      body { margin: 0; padding: 10px; font-size: 11px; }
      .no-print { display: none; }
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      color: #111;
      line-height: 1.4;
      background: #fdfdfd;
      padding: 30px;
    }
    .receipt-container {
      max-width: 780px;
      margin: 0 auto;
      border: 1px solid #ddd;
      padding: 25px;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .header {
      text-align: center;
      border-bottom: 2px solid #222;
      padding-bottom: 12px;
      margin-bottom: 15px;
    }
    .hospital-title {
      font-size: 19px;
      font-weight: bold;
      text-transform: uppercase;
      margin: 0;
      letter-spacing: 0.5px;
    }
    .hospital-subtitle {
      font-size: 11px;
      color: #444;
      margin: 2px 0;
    }
    .eopt-banner {
      background: #f0f7ff;
      border: 1px solid #b8daff;
      color: #004085;
      padding: 6px 10px;
      font-size: 10px;
      font-weight: bold;
      text-align: center;
      margin-top: 8px;
      border-radius: 3px;
    }
    .or-title {
      font-size: 16px;
      font-weight: bold;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-top: 8px;
      color: #0b5ed7;
    }
    .meta-table, .items-table, .totals-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .meta-table td {
      padding: 4px 6px;
      font-size: 11.5px;
    }
    .items-table th, .items-table td {
      border: 1px solid #ccc;
      padding: 6px 8px;
      font-size: 11.5px;
    }
    .items-table th {
      background: #f2f2f2;
      text-align: left;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; }
    .footer-note {
      font-size: 9.5px;
      color: #555;
      border-top: 1px dashed #aaa;
      padding-top: 10px;
      margin-top: 15px;
      text-align: center;
    }
    .btn-print {
      background: #0b5ed7;
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .badge-paid {
      display: inline-block;
      padding: 2px 8px;
      background: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
      border-radius: 3px;
      font-weight: bold;
      font-size: 11px;
    }
  </style>
</head>
<body>

  <div class="no-print" style="text-align: right; max-width: 780px; margin: 0 auto 10px auto;">
    <button class="btn-print" onclick="window.print()">🖨️ Print Official Receipt (BIR EOPT)</button>
  </div>

  <div class="receipt-container">
    <!-- Header -->
    <div class="header">
      <h1 class="hospital-title">St. Jude Metropolitan Medical Center, Inc.</h1>
      <p class="hospital-subtitle">123 Health Avenue, Medical District, Metro Manila, Philippines 1200</p>
      <p class="hospital-subtitle">VAT Reg. TIN: 000-987-654-00000-NV | Contact: (02) 8888-9999 / fms@stjude.health</p>
      <p class="hospital-subtitle">BIR CAS Accreditation No: CAS-2026-MNL-00991 &bull; ATP No: BIR-ATP-2026-088192</p>
      <p class="hospital-subtitle">POS Machine Accreditation No: POS-NCR-2026-00412 &bull; Terminal: {{ $payment->cashierShift?->terminal_name ?? 'POS-MAIN-01' }}</p>
      
      <div class="eopt-banner">
        OFFICIAL RECEIPT / COLLECTION RECEIPT &bull; Supplementary Document to VAT/Non-VAT Sales Invoice (RA 11976 / BIR EOPT Act)
      </div>

      <div class="or-title">OFFICIAL RECEIPT</div>
    </div>

    <!-- Metadata -->
    <table class="meta-table">
      <tr>
        <td style="width: 16%;"><strong>OR Number:</strong></td>
        <td style="width: 34%;" class="font-mono" style="color: #0b5ed7; font-weight: bold;">
          {{ $payment->officialReceipt?->or_number ?? 'OR-' . date('Ymd') . '-0001' }}
        </td>
        <td style="width: 16%;"><strong>Date &amp; Time:</strong></td>
        <td style="width: 34%;">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : date('M d, Y') }} ({{ date('h:i A') }})</td>
      </tr>
      <tr>
        <td><strong>Received From:</strong></td>
        <td><strong>{{ $payment->officialReceipt?->payor_name ?: ($payment->patientAccount?->full_name ?? 'Walk-In Patient') }}</strong></td>
        <td><strong>Patient MRN:</strong></td>
        <td class="font-mono">{{ $payment->patientAccount?->patient_id_number ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td><strong>Payor TIN:</strong></td>
        <td class="font-mono">{{ $payment->officialReceipt?->payor_tin ?? '000-000-000-000' }}</td>
        <td><strong>Admission Type:</strong></td>
        <td>{{ $payment->patientAccount?->admission_type ?? 'Outpatient' }}</td>
      </tr>
      <tr>
        <td><strong>Invoice Reference:</strong></td>
        <td class="font-mono">{{ $payment->invoice?->invoice_number ?? 'WALK-IN-COP' }}</td>
        <td><strong>Payment Channel:</strong></td>
        <td>
          <strong>{{ $payment->payment_method }}</strong>
          @if($payment->transaction_channel_ref)
            <span class="font-mono fs-xs">({{ $payment->transaction_channel_ref }})</span>
          @endif
        </td>
      </tr>
    </table>

    <!-- Line Item Breakdown of Medical Services -->
    <table class="items-table">
      <thead>
        <tr>
          <th style="width: 100px;">Item Code</th>
          <th>Description of Medical Services / Supplies</th>
          <th class="text-center" style="width: 50px;">Qty</th>
          <th class="text-right" style="width: 110px;">Unit Price (₱)</th>
          <th class="text-right" style="width: 120px;">Gross Total (₱)</th>
        </tr>
      </thead>
      <tbody>
        @php
          $items = $payment->invoice?->invoiceItems ?? ($payment->invoice?->items ?? collect());
        @endphp
        @if($items->count() > 0)
          @foreach($items as $item)
            <tr>
              <td class="font-mono">{{ $item->item_code ?? 'SRV-001' }}</td>
              <td>{{ $item->description }} @if($item->department) ({{ $item->department }}) @endif</td>
              <td class="text-center">{{ (float) ($item->quantity ?? 1) }}</td>
              <td class="text-right font-mono">₱{{ number_format((float) ($item->unit_price ?? $item->subtotal), 2) }}</td>
              <td class="text-right font-mono">₱{{ number_format((float) ($item->subtotal ?? $item->gross_amount ?? $item->unit_price), 2) }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td class="font-mono">CLINICAL-COP</td>
            <td>Hospital Inpatient/Outpatient Copay &bull; Professional Medical Settlement</td>
            <td class="text-center">1</td>
            <td class="text-right font-mono">₱{{ number_format((float) $payment->amount, 2) }}</td>
            <td class="text-right font-mono">₱{{ number_format((float) $payment->amount, 2) }}</td>
          </tr>
        @endif
      </tbody>
    </table>

    <!-- Statutory Deductions & Net Total Breakdown -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
      <tr>
        <td style="width: 52%; vertical-align: top; padding-right: 15px;">
          <div style="background: #f9fbfd; padding: 10px; border: 1px solid #e1e8ed; border-radius: 4px; font-size: 11px;">
            <strong style="color: #0b5ed7;">Philippine EOPT Tax &amp; Discount Summary:</strong><br>
            VATable Sales (12%): ₱0.00<br>
            VAT-Exempt Sales (Medical Services - Sec 109): ₱{{ number_format((float) ($payment->invoice?->total_amount ?? $payment->amount), 2) }}<br>
            Zero-Rated Sales: ₱0.00<br>
            Output Value-Added Tax (12%): ₱0.00<br>
            Senior Citizen / PWD 20% Discount (RA 9994 / 10754): Applied &bull; Exempt<br>
            PhilHealth / HMO Statutory Coverage: Deducted
          </div>
        </td>
        <td style="width: 48%; vertical-align: top;">
          <table class="totals-table" style="font-size: 11.5px;">
            <tr>
              <td>Total Gross Hospital Bill:</td>
              <td class="text-right font-mono">₱{{ number_format((float) ($payment->invoice?->total_amount ?? $payment->amount), 2) }}</td>
            </tr>
            @if($payment->invoice && (float)$payment->invoice->insurance_covered > 0)
              <tr>
                <td>Less: PhilHealth Benefit / HMO:</td>
                <td class="text-right font-mono text-danger">-₱{{ number_format((float) $payment->invoice->insurance_covered, 2) }}</td>
              </tr>
            @endif
            <tr style="border-top: 2px solid #222; font-weight: bold; font-size: 13px;">
              <td style="padding-top: 5px;">TOTAL AMOUNT PAID:</td>
              <td class="text-right font-mono" style="color: #0b5ed7; padding-top: 5px;">
                ₱{{ number_format((float) $payment->amount, 2) }}
              </td>
            </tr>
            <tr>
              <td style="padding-top: 4px;">Remaining Patient Balance:</td>
              <td class="text-right font-mono fw-bold" style="padding-top: 4px;">
                @if($payment->invoice && (float)$payment->invoice->patient_payable <= 0.0001)
                  <span class="badge-paid">CLEARED (₱0.00)</span>
                @else
                  ₱{{ number_format((float) ($payment->invoice?->patient_payable ?? 0), 2) }}
                @endif
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Footer Signatures & Audit Info -->
    <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 11px;">
      <div style="display: flex; justify-content: space-between;">
        <div>
          <strong>Issued by Cashier:</strong> {{ $payment->cashierShift?->cashier?->name ?? 'Maria Santos (Senior Cashier)' }}<br>
          <strong>Shift Code:</strong> {{ $payment->cashierShift?->shift_code ?? 'SHIFT-20260825-001' }}
        </div>
        <div style="text-align: right;">
          <strong>Account Settlement Status:</strong> 
          <span style="color: #0f5132; font-weight: bold;">
            {{ ($payment->invoice && (float)$payment->invoice->patient_payable <= 0.0001) ? 'FULL SETTLEMENT & CLEARANCE' : 'PARTIAL COPAY SETTLEMENT' }}
          </span>
        </div>
      </div>
    </div>

    <!-- EOPT Certification Notice -->
    <div class="footer-note">
      <p>This Official / Collection Receipt is system-generated pursuant to BIR Revenue Regulations No. 7-2024 and the Ease of Paying Taxes (EOPT) Act (RA 11976).<br>
      <em>"THIS DOCUMENT IS A VALID PROOF OF PAYMENT AND COLLECTION AS A SUPPLEMENTARY RECEIPT UNDER PHILIPPINE TAX LAWS."</em></p>
    </div>
  </div>

</body>
</html>
