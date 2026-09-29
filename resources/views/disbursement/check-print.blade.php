<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bank Check - {{ $check->check_number }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  @vite(['resources/css/app.css'])
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; padding: 0; background: #fff !important; }
      .check-container { border: 2px solid #000 !important; box-shadow: none !important; }
    }
  </style>
</head>
<body class="bg-slate-100 text-slate-900 p-6 sm:p-10 font-sans antialiased">
  <!-- Print Actions Toolbar -->
  <div class="flex items-center justify-between mb-6 no-print max-w-[800px] mx-auto">
    <button 
      type="button" 
      onclick="window.history.back()" 
      class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all cursor-pointer"
    >
      <i class="ph-bold ph-arrow-left"></i>
      <span>Back to Register</span>
    </button>
    <button 
      type="button" 
      onclick="window.print()" 
      class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition-all cursor-pointer"
    >
      <i class="ph-bold ph-printer"></i>
      <span>Print Commercial Check</span>
    </button>
  </div>

  <!-- Standard Bank Check Physical Layout -->
  <div class="check-container max-w-[800px] mx-auto bg-slate-50 border-2 border-slate-800 rounded-lg p-6 shadow-md relative" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 16px 16px;">
    <!-- Top Row: Bank Info & Check # / Date -->
    <div class="flex items-start justify-between mb-4">
      <div>
        <h2 class="text-base font-extrabold uppercase font-mono tracking-wider text-slate-900">
          {{ $check->bankAccount?->bank_name ?? 'METROBANK MEDICAL CENTER' }}
        </h2>
        <div class="text-[11px] text-slate-600 font-medium">ST. JUDE METROPOLITAN MEDICAL CENTER - DISBURSEMENT</div>
        <div class="text-[11px] font-mono text-slate-500">Account No: {{ $check->bankAccount?->account_number ?? '1029-9940-11' }}</div>
      </div>
      <div class="text-right">
        <div class="font-mono font-bold text-sm text-rose-600 mb-1">{{ $check->check_number }}</div>
        <div class="border-b border-slate-800 pb-1 text-center font-mono min-w-[170px] text-xs">
          DATE: <strong>{{ $check->check_date ? $check->check_date->format('M d, Y') : date('M d, Y') }}</strong>
        </div>
      </div>
    </div>

    <!-- Payee & Numeric Amount Line -->
    <div class="flex items-center justify-between gap-4 mb-4 pt-2">
      <div class="flex items-center flex-grow border-b border-slate-800 pb-1">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mr-2 whitespace-nowrap">PAY TO THE ORDER OF:</span>
        <span class="font-mono font-bold text-slate-900 text-sm flex-grow">*** {{ $check->payee_name }} ***</span>
      </div>
      <div class="border-2 border-slate-800 rounded-md px-3 py-1 bg-white text-right font-mono font-bold text-base text-slate-900 min-w-[190px] tabular-nums">
        ₱ {{ number_format((float) $check->amount, 2) }}
      </div>
    </div>

    <!-- Amount in English Words Line -->
    <div class="flex items-center border-b border-slate-800 pb-1 mb-6">
      <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mr-2 whitespace-nowrap">PESOS:</span>
      <span class="font-mono font-semibold text-slate-900 text-xs flex-grow">*** {{ $check->amount_in_words }} ***</span>
    </div>

    <!-- Bottom Row: Signatures & MICR Band -->
    <div class="flex items-end justify-between pt-4">
      <div class="text-xs text-slate-500">
        <div>Voucher Ref: <span class="font-mono font-bold text-slate-800">{{ $check->disbursementVoucher?->voucher_number ?? 'N/A' }}</span></div>
        <div class="font-mono text-slate-400 text-xs pt-2">|: {{ $check->check_number }} :| 004991234 |: 1029994011 :|' 01</div>
      </div>
      <div class="flex gap-6">
        <div class="text-center w-40">
          <div class="border-b border-slate-800 mb-1 h-8"></div>
          <span class="text-[10px] text-slate-500 font-semibold uppercase block">Authorized Signature</span>
        </div>
        <div class="text-center w-40">
          <div class="border-b border-slate-800 mb-1 h-8"></div>
          <span class="text-[10px] text-slate-500 font-semibold uppercase block">Chief Financial Officer</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
