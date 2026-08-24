<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Receipt - {{ $payment->officialReceipt?->or_number ?? $payment->payment_reference }}</title>
  <style>
    @media print {
      body { margin: 0; padding: 10px; font-size: 12px; }
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
      max-width: 750px;
      margin: 0 auto;
      border: 1px solid #ddd;
      padding: 25px;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .header {
      text-align: center;
      border-bottom: 2px solid #222;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .hospital-title {
      font-size: 20px;
      font-weight: bold;
      text-transform: uppercase;
      margin: 0;
    }
    .hospital-subtitle {
      font-size: 12px;
      color: #555;
      margin: 2px 0;
    }
    .or-title {
      font-size: 16px;
      font-weight: bold;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-top: 10px;
      color: #0b5ed7;
    }
    .meta-table, .items-table, .totals-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .meta-table td {
      padding: 4px 6px;
      font-size: 12px;
    }
    .items-table th, .items-table td {
      border: 1px solid #ccc;
      padding: 6px 8px;
      font-size: 12px;
    }
    .items-table th {
      background: #f2f2f2;
      text-align: left;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; }
    .footer-note {
      font-size: 10px;
      color: #666;
      border-top: 1px dashed #aaa;
      padding-top: 10px;
      margin-top: 20px;
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
  </style>
</head>
<body>

  <div class="no-print" style="text-align: right; max-width: 750px; margin: 0 auto 10px auto;">
    <button class="btn-print" onclick="window.print()">🖨️ Print Official Receipt</button>
  </div>

  <div class="receipt-container">
    <!-- Header -->
    <div class="header">
      <h1 class="hospital-title">St. Jude Metropolitan Medical Center</h1>
      <p class="hospital-subtitle">123 Health Avenue, Medical District, Metro Manila, Philippines</p>
      <p class="hospital-subtitle">VAT Reg. TIN: 000-987-654-00000 | Contact: (02) 8888-9999</p>
      <p class="hospital-subtitle">BIR CAS Accreditation No: CAS-2026-MNL-00991 &bull; ATP No: 2026-001239</p>
      <div class="or-title">OFFICIAL RECEIPT</div>
    </div>

    <!-- Metadata -->
    <table class="meta-table">
      <tr>
        <td style="width: 15%;"><strong>OR Number:</strong></td>
        <td style="width: 35%;" class="font-mono" style="color: #0b5ed7; font-weight: bold;">
          {{ $payment->officialReceipt?->or_number ?? 'OR-' . date('Ymd') . '-0001' }}
        </td>
        <td style="width: 15%;"><strong>Date &amp; Time:</strong></td>
        <td style="width: 35%;">{{ $payment->payment_date->format('M d, Y') }} ({{ date('h:i A') }})</td>
      </tr>
      <tr>
        <td><strong>Received From:</strong></td>
        <td><strong>{{ $payment->patientAccount->full_name }}</strong></td>
        <td><strong>Patient ID:</strong></td>
        <td class="font-mono">{{ $payment->patientAccount->patient_id_number }}</td>
      </tr>
      <tr>
        <td><strong>Invoice Ref:</strong></td>
        <td class="font-mono">{{ $payment->invoice?->invoice_number ?? 'WALK-IN' }}</td>
        <td><strong>Payment Mode:</strong></td>
        <td><strong>{{ $payment->payment_method }}</strong> @if($payment->transaction_channel_ref) (Ref: {{ $payment->transaction_channel_ref }}) @endif</td>
      </tr>
    </table>

    <!-- Line Item Breakdown -->
    <table class="items-table">
      <thead>
        <tr>
          <th>Item Code</th>
          <th>Description &amp; Medical Department</th>
          <th class="text-center" style="width: 60px;">Qty</th>
          <th class="text-right" style="width: 110px;">Unit Price</th>
          <th class="text-right" style="width: 120px;">Gross Amount</th>
        </tr>
      </thead>
      <tbody>
        @if($payment->invoice && $payment->invoice->invoiceItems->count() > 0)
          @foreach($payment->invoice->invoiceItems as $item)
            <tr>
              <td class="font-mono">{{ $item->item_code }}</td>
              <td>{{ $item->description }} ({{ $item->department }})</td>
              <td class="text-center">{{ (float) $item->quantity }}</td>
              <td class="text-right font-mono">₱{{ number_format((float) $item->unit_price, 2) }}</td>
              <td class="text-right font-mono">₱{{ number_format((float) $item->gross_amount, 2) }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td class="font-mono">CLINICAL-COP</td>
            <td>Patient Out-of-Pocket Copay Settlement</td>
            <td class="text-center">1</td>
            <td class="text-right font-mono">₱{{ number_format((float) $payment->amount, 2) }}</td>
            <td class="text-right font-mono">₱{{ number_format((float) $payment->amount, 2) }}</td>
          </tr>
        @endif
      </tbody>
    </table>

    <!-- Statutory Deductions & Net Total Breakdown -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
      <tr>
        <td style="width: 50%; vertical-align: top; padding-right: 15px;">
          <div style="background: #f9f9f9; padding: 10px; border: 1px solid #eee; border-radius: 4px; font-size: 11px;">
            <strong>Philippine Statutory &amp; Tax Summary:</strong><br>
            VATable Sales: ₱0.00<br>
            VAT-Exempt Sales: ₱{{ number_format((float) ($payment->invoice?->total_amount ?? $payment->amount), 2) }}<br>
            Zero-Rated Sales: ₱0.00<br>
            Value-Added Tax (12%): ₱0.00<br>
            Senior/PWD Discount (RA 9994/10754): Applied
          </div>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <table class="totals-table" style="font-size: 12px;">
            <tr>
              <td>Gross Hospital Bill:</td>
              <td class="text-right font-mono">₱{{ number_format((float) ($payment->invoice?->total_amount ?? $payment->amount), 2) }}</td>
            </tr>
            @if($payment->invoice && (float)$payment->invoice->insurance_covered > 0)
              <tr>
                <td>Less: PhilHealth ACR / HMO:</td>
                <td class="text-right font-mono text-danger">-₱{{ number_format((float) $payment->invoice->insurance_covered, 2) }}</td>
              </tr>
            @endif
            <tr style="border-top: 2px solid #222; font-weight: bold; font-size: 14px;">
              <td style="padding-top: 6px;">TOTAL AMOUNT PAID:</td>
              <td class="text-right font-mono" style="color: #0b5ed7; padding-top: 6px;">
                ₱{{ number_format((float) $payment->amount, 2) }}
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Footer -->
    <div class="footer-note">
      <p>This Official Receipt is system-generated and certified under BIR Computerized Accounting System (CAS) Rules.<br>
      Issued by: <strong>Maria Santos (Senior Cashier)</strong> &bull; Terminal ID: <strong>POS-MAIN-01</strong><br>
      <em>"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX. VALID FOR OFFICIAL RECEIPT PURPOSES ONLY."</em></p>
    </div>
  </div>

</body>
</html>
