<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hospital Billing Statement - {{ $invoice->invoice_number }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  @vite(['resources/css/app.css'])
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background-color: #fff !important; font-size: 11px; }
      .invoice-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 p-4 sm:p-8 font-sans antialiased">
  <div class="max-w-4xl mx-auto">
    <!-- Print Button Toolbar -->
    <div class="flex items-center justify-between mb-6 no-print">
      <button 
        type="button" 
        onclick="window.history.back()" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-arrow-left"></i>
        <span>Back to Invoices</span>
      </button>
      <button 
        type="button" 
        onclick="window.print()" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-printer"></i>
        <span>Print / Save as PDF</span>
      </button>
    </div>

    <!-- Official Billing Statement Card -->
    <div class="invoice-card rounded-2xl bg-white p-6 sm:p-10 shadow-sm ring-1 ring-slate-200/80">
      <!-- Hospital Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-6 mb-6">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
            ST. JUDE METROPOLITAN MEDICAL CENTER
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">1029 Ortigas Center, Pasig City, Metro Manila, Philippines</p>
          <p class="text-[11px] text-slate-400 mt-0.5 font-mono">BIR VAT Reg. TIN: 004-991-234-000 | CAS Permit: CAS-2026-MED-0991</p>
        </div>
        <div class="text-left sm:text-right">
          <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold tracking-wider uppercase bg-blue-50 text-blue-700 ring-1 ring-blue-500/20 mb-2">
            Billing Statement
          </span>
          <div class="font-mono font-bold text-sm text-slate-900">{{ $invoice->invoice_number }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
        </div>
      </div>

      <!-- Patient Information -->
      <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200/60 mb-6 text-xs">
        <div class="sm:col-span-6">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Patient Full Name</span>
          <div class="font-bold text-slate-900 text-sm">{{ $invoice->patientAccount?->full_name ?? 'Walk-In Patient' }}</div>
          <div class="text-xs text-slate-500 font-mono mt-0.5">MRN: {{ $invoice->patientAccount?->patient_id_number ?? 'N/A' }}</div>
        </div>
        <div class="sm:col-span-3">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Admission Type</span>
          <div class="font-semibold text-slate-800">{{ $invoice->patientAccount?->admission_type ?? 'Inpatient' }}</div>
          <div class="text-slate-500 mt-0.5">Due Date: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Immediate' }}</div>
        </div>
        <div class="sm:col-span-3">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">HMO / Policy Provider</span>
          <div class="font-semibold text-blue-600">{{ $invoice->patientAccount?->hmo_provider ?? 'Self-Pay' }}</div>
          <div class="text-slate-500 mt-0.5">Status: <span class="font-bold text-slate-800">{{ $invoice->status }}</span></div>
        </div>
      </div>

      <!-- Itemized Hospital Charges -->
      <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Itemized Departmental Charges</h2>
      <div class="overflow-x-auto rounded-xl border border-slate-200 mb-6">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
            <tr>
              <th class="px-3.5 py-2.5 w-12">#</th>
              <th class="px-3.5 py-2.5">Department</th>
              <th class="px-3.5 py-2.5">Procedure / Service Particulars</th>
              <th class="px-3.5 py-2.5 text-center">Qty</th>
              <th class="px-3.5 py-2.5 text-right">Unit Price</th>
              <th class="px-3.5 py-2.5 text-right">Gross (₱)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            @forelse($invoice->items as $idx => $item)
            <tr>
              <td class="px-3.5 py-2 text-slate-400 font-mono">{{ $idx + 1 }}</td>
              <td class="px-3.5 py-2">
                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-slate-100 text-slate-700">{{ $item->department }}</span>
              </td>
              <td class="px-3.5 py-2 font-medium text-slate-900">{{ $item->description }}</td>
              <td class="px-3.5 py-2 text-center font-mono">{{ number_format((float) $item->quantity, 0) }}</td>
              <td class="px-3.5 py-2 text-right font-mono text-slate-600 tabular-nums">₱{{ number_format((float) $item->unit_price, 2) }}</td>
              <td class="px-3.5 py-2 text-right font-mono font-semibold text-slate-900 tabular-nums">₱{{ number_format((float) $item->gross_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="px-3.5 py-6 text-center text-slate-400">No itemized charges listed.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Financial Calculation Breakdown -->
      <div class="flex justify-end mb-8">
        <div class="w-full sm:w-80 space-y-2 text-xs">
          <div class="flex justify-between py-1 border-b border-slate-100">
            <span class="text-slate-500 font-medium">Gross Hospital Charges:</span>
            <span class="font-mono font-bold text-slate-900 tabular-nums">₱{{ number_format((float) $invoice->total_amount, 2) }}</span>
          </div>
          @if((float) $invoice->discount_amount > 0)
          <div class="flex justify-between py-1 border-b border-slate-100 text-purple-700">
            <span>Less: Senior / PWD &amp; VAT Relief:</span>
            <span class="font-mono font-semibold tabular-nums">-₱{{ number_format((float) $invoice->discount_amount, 2) }}</span>
          </div>
          @endif
          @if((float) $invoice->insurance_covered > 0)
          <div class="flex justify-between py-1 border-b border-slate-100 text-blue-700">
            <span>Less: PhilHealth &amp; HMO Coverage:</span>
            <span class="font-mono font-semibold tabular-nums">-₱{{ number_format((float) $invoice->insurance_covered, 2) }}</span>
          </div>
          @endif
          @if((float) $invoice->paid_amount > 0)
          <div class="flex justify-between py-1 border-b border-slate-100 text-emerald-700">
            <span>Less: Cashier Payments Settled:</span>
            <span class="font-mono font-semibold tabular-nums">-₱{{ number_format((float) $invoice->paid_amount, 2) }}</span>
          </div>
          @endif
          <div class="flex justify-between py-2 border-t-2 border-slate-800 text-sm">
            <span class="font-bold text-slate-900">Net Patient Balance Due:</span>
            <span class="font-mono font-extrabold text-rose-600 tabular-nums">₱{{ number_format((float) $invoice->balance_due, 2) }}</span>
          </div>
        </div>
      </div>

      <!-- Footer & Signatures -->
      <div class="grid grid-cols-3 gap-6 text-center text-xs text-slate-500 pt-6 border-t border-slate-200">
        <div>
          <div class="border-t border-slate-300 pt-2 font-medium">Prepared by: Billing Clerk</div>
        </div>
        <div>
          <div class="border-t border-slate-300 pt-2 font-medium">Verified by: Patient / Representative</div>
        </div>
        <div>
          <div class="border-t border-slate-300 pt-2 font-medium">Authorized by: Hospital Cashier</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
