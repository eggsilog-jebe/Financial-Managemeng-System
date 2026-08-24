<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BIR Form 2307 - Certificate of Creditable Tax Withheld</title>
  <style>
    @media print {
      body { margin: 0; padding: 10px; font-size: 11px; }
      .no-print { display: none; }
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      color: #111;
      line-height: 1.3;
      background: #fdfdfd;
      padding: 20px;
    }
    .form-container {
      max-width: 850px;
      margin: 0 auto;
      border: 2px solid #222;
      padding: 20px;
      background: #fff;
    }
    .form-header {
      text-align: center;
      border-bottom: 2px solid #222;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    .bir-logo-title {
      font-size: 16px;
      font-weight: bold;
      margin: 0;
    }
    .form-number {
      font-size: 20px;
      font-weight: 900;
      color: #b02a37;
    }
    .section-title {
      background: #eee;
      font-weight: bold;
      padding: 4px 8px;
      font-size: 11px;
      text-transform: uppercase;
      border: 1px solid #ccc;
      margin-top: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    table td, table th {
      border: 1px solid #ccc;
      padding: 5px 8px;
      font-size: 11px;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
    .btn-print {
      background: #b02a37;
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

  <div class="no-print" style="text-align: right; max-width: 850px; margin: 0 auto 10px auto;">
    <button class="btn-print" onclick="window.print()">🖨️ Print BIR Form 2307</button>
  </div>

  <div class="form-container">
    <!-- Header -->
    <div class="form-header">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: left;">
          <small>Republika ng Pilipinas</small><br>
          <strong>Kagawaran ng Pananalapi</strong><br>
          <small>Kawanihan ng Rentas Internas</small>
        </div>
        <div>
          <span class="form-number">BIR Form No. 2307</span><br>
          <strong style="font-size: 13px;">Certificate of Creditable Tax Withheld At Source</strong>
        </div>
        <div style="text-align: right;">
          <small>Certificate Series:</small><br>
          <span class="font-mono" style="color: #b02a37;">{{ $cert->certificate_number ?? '2307-2026-0001' }}</span>
        </div>
      </div>
    </div>

    <!-- Part I: Payee Information -->
    <div class="section-title">Part I - Payee Information (Recipient of Income)</div>
    <table>
      <tr>
        <td style="width: 20%;"><strong>Payee TIN:</strong></td>
        <td style="width: 30%;" class="font-mono">{{ $cert->payee_tin ?? '000-123-456-000' }}</td>
        <td style="width: 20%;"><strong>Tax Period Covered:</strong></td>
        <td style="width: 30%;">{{ date('M 01, Y') }} to {{ date('M t, Y') }}</td>
      </tr>
      <tr>
        <td><strong>Payee Name:</strong></td>
        <td colspan="3"><strong>{{ $cert->payee_name ?? $cert->purchaseBill?->vendor?->name ?? 'Zuellig Pharma Corporation' }}</strong></td>
      </tr>
    </table>

    <!-- Part II: Payor Information -->
    <div class="section-title">Part II - Payor Information (Withholding Agent)</div>
    <table>
      <tr>
        <td style="width: 20%;"><strong>Withholding Agent TIN:</strong></td>
        <td style="width: 30%;" class="font-mono">000-987-654-00000</td>
        <td style="width: 20%;"><strong>RDO Code:</strong></td>
        <td style="width: 30%;" class="font-mono">039 (South QC)</td>
      </tr>
      <tr>
        <td><strong>Hospital / Payor Name:</strong></td>
        <td colspan="3"><strong>St. Jude Metropolitan Medical Center</strong></td>
      </tr>
      <tr>
        <td><strong>Registered Address:</strong></td>
        <td colspan="3">123 Health Avenue, Medical District, Metro Manila, Philippines</td>
      </tr>
    </table>

    <!-- Part III: Details of Monthly Income Payments & Tax Withheld -->
    <div class="section-title">Part III - Details of Monthly Income Payments and Tax Withheld</div>
    <table>
      <thead style="background: #f5f5f5;">
        <tr>
          <th>ATC</th>
          <th>Nature of Income Payment</th>
          <th class="text-right" style="width: 140px;">Gross Amount of Payment</th>
          <th class="text-center" style="width: 70px;">Tax Rate</th>
          <th class="text-right" style="width: 140px;">Amount of Tax Withheld</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="font-mono text-center">{{ $cert->atc_code ?? 'WI158' }}</td>
          <td>
            @if(($cert->atc_code ?? 'WI158') === 'WI158')
              Income payments made by top withholding agents to local suppliers of goods (1%)
            @elseif(($cert->atc_code ?? 'WI158') === 'WI160')
              Income payments made by top withholding agents to local suppliers of services (2%)
            @else
              Professional fees paid to medical practitioners / doctor clinics (10%/15%)
            @endif
          </td>
          <td class="text-right font-mono">₱{{ number_format((float) ($cert->gross_amount ?? $cert->purchaseBill?->total_amount ?? 100000.00), 2) }}</td>
          <td class="text-center font-mono">
            {{ (($cert->atc_code ?? 'WI158') === 'WI158') ? '1%' : ((($cert->atc_code ?? 'WI158') === 'WI160') ? '2%' : '10%') }}
          </td>
          <td class="text-right font-mono" style="color: #b02a37;">₱{{ number_format((float) ($cert->tax_withheld ?? 1000.00), 2) }}</td>
        </tr>
      </tbody>
      <tfoot style="background: #f9f9f9; font-weight: bold;">
        <tr>
          <td colspan="2" class="text-right text-uppercase">Total Tax Withheld:</td>
          <td class="text-right font-mono">₱{{ number_format((float) ($cert->gross_amount ?? $cert->purchaseBill?->total_amount ?? 100000.00), 2) }}</td>
          <td></td>
          <td class="text-right font-mono" style="color: #b02a37; font-size: 13px;">₱{{ number_format((float) ($cert->tax_withheld ?? 1000.00), 2) }}</td>
        </tr>
      </tfoot>
    </table>

    <!-- Signature Declaration -->
    <div style="margin-top: 30px; border-top: 1px solid #aaa; padding-top: 15px;">
      <p style="font-size: 10px; color: #555; text-align: justify;">
        We declare under the penalties of perjury that this certificate has been made in good faith, verified by us, and to the best of our knowledge and belief, is true and correct pursuant to the provisions of the National Internal Revenue Code (NIRC), as amended, and the regulations issued under authority thereof.
      </p>
      <div style="display: flex; justify-content: space-between; margin-top: 30px;">
        <div style="text-align: center; width: 40%;">
          <div style="border-bottom: 1px solid #333; height: 30px;"></div>
          <small><strong>Maria Santos, CPA</strong><br>Finance Comptroller / Head of Tax</small>
        </div>
        <div style="text-align: center; width: 40%;">
          <div style="border-bottom: 1px solid #333; height: 30px;"></div>
          <small><strong>Dr. Roberto V. Garcia, MD, MHA</strong><br>Chief Executive Officer / Hospital Director</small>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
