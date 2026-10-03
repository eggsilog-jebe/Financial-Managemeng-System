@extends('layouts.app')

@section('title', 'Executive Reports Dossier - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'executive-reports')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Executive Board Report &amp; Financial Dossier
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <button 
        type="button" 
        onclick="window.print()" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-printer text-blue-600"></i>
        <span>Print Complete Dossier</span>
      </button>
      <a 
        href="{{ route('reporting.balance-sheet.export') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-download-simple"></i>
        <span>Export Balance Sheet</span>
      </a>
    </div>
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- Cutoff Date Selector -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('reporting.executive-reports') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
          <i class="ph-bold ph-calendar mr-1"></i> Dossier Cutoff Date:
        </label>
        <input 
          type="date" 
          name="cutoff_date" 
          class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          value="{{ $as_of_date ?? date('Y-m-d') }}" 
          onchange="this.form.submit()"
        >
      </div>

      <span class="font-mono text-xs text-slate-400">
        Generated on {{ $generated_at ?? date('Y-m-d H:i:s') }} PST
      </span>
    </form>
  </div>

  <!-- Branded Printable Dossier Container -->
  <div class="rounded-3xl bg-white p-6 sm:p-10 shadow-lg ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 space-y-8">
    <!-- Dossier Header -->
    <div class="border-b border-slate-200 pb-6 text-center dark:border-slate-800">
      <div class="flex items-center justify-center gap-2.5 mb-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500/30 text-xl">
          <i class="ph-bold ph-chart-line-up"></i>
        </div>
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Financial Management System
        </h2>
      </div>
      <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 mt-2 font-mono">
        {{ $dossier_title ?? 'Executive Financial & Operational Dossier' }} &bull; FY {{ $fiscal_year ?? date('Y') }}
      </div>
      <div class="text-xs text-slate-400 mt-1.5 font-mono">
        Period Covered: {{ $period_covered ?? date('Y-m-d') }}
      </div>
    </div>

    <!-- Section 1: Executive KPI Scorecard -->
    <div>
      <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 pb-2 mb-4 dark:border-slate-800 flex items-center gap-2">
        <i class="ph-bold ph-chart-polar text-blue-600"></i>
        <span>1. Executive Financial Scorecard</span>
      </h5>
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
          <span class="block text-xs font-semibold uppercase text-slate-400">Operating Margin</span>
          <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-emerald-600 dark:text-emerald-400">
            {{ number_format((float) ($kpis['operating_margin'] ?? 0), 1) }}%
          </h4>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
          <span class="block text-xs font-semibold uppercase text-slate-400">Days Sales Outstanding</span>
          <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-blue-600 dark:text-blue-400">
            {{ number_format((float) ($kpis['dso'] ?? 0), 1) }} Days
          </h4>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
          <span class="block text-xs font-semibold uppercase text-slate-400">Days Cash on Hand</span>
          <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-purple-600 dark:text-purple-400">
            {{ number_format((float) ($kpis['days_cash_on_hand'] ?? 0), 1) }} Days
          </h4>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
          <span class="block text-xs font-semibold uppercase text-slate-400">Current Working Ratio</span>
          <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-slate-900 dark:text-white">
            {{ number_format((float) ($kpis['current_ratio'] ?? 0), 2) }}x
          </h4>
        </div>
      </div>
    </div>

    <!-- Section 2: Statement of Financial Position Summary -->
    <div>
      <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 pb-2 mb-4 dark:border-slate-800 flex items-center gap-2">
        <i class="ph-bold ph-scales text-blue-600"></i>
        <span>2. Condensed Statement of Financial Position</span>
      </h5>
      <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Balance Sheet Classification</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right w-64">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4 font-bold text-emerald-600 dark:text-emerald-400">TOTAL HOSPITAL ASSETS (Cash, AR, Equipment)</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format((float) ($balance_sheet['total_assets'] ?? 0), 2) }}
              </td>
            </tr>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4 font-bold text-rose-600 dark:text-rose-400">TOTAL LIABILITIES (Accounts Payable, Accruals)</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-rose-600 dark:text-rose-400">
                ₱{{ number_format((float) ($balance_sheet['total_liabilities'] ?? 0), 2) }}
              </td>
            </tr>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4 font-bold text-blue-600 dark:text-blue-400">TOTAL NET EQUITY (Capital Reserves + Current Surplus)</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-blue-600 dark:text-blue-400">
                ₱{{ number_format((float) ($balance_sheet['total_equity'] ?? 0), 2) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-bold">
            <tr>
              <td class="py-3 px-4 uppercase text-slate-800 dark:text-slate-200">TOTAL LIABILITIES &amp; NET EQUITY</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums text-slate-900 dark:text-white text-sm">
                ₱{{ number_format((float) ($balance_sheet['total_liab_and_equity'] ?? 0), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Section 3: Statement of Comprehensive Income Summary -->
    <div>
      <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 border-b border-slate-100 pb-2 mb-4 dark:border-slate-800 flex items-center gap-2">
        <i class="ph-bold ph-receipt text-blue-600"></i>
        <span>3. Condensed Statement of Comprehensive Income</span>
      </h5>
      <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Revenue &amp; Expense Summary</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right w-64">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-2.5 px-4 text-slate-700 dark:text-slate-300">Gross Operating Revenues (Inpatient, Outpatient, Diagnostics, Pharmacy)</td>
              <td class="py-2.5 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format((float) ($profit_and_loss['gross_revenue'] ?? 0), 2) }}
              </td>
            </tr>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-2.5 px-4 text-slate-700 dark:text-slate-300">Less: Statutory &amp; Institutional Discounts (5010)</td>
              <td class="py-2.5 px-4 text-right font-mono tabular-nums font-semibold text-amber-600 dark:text-amber-400">
                -₱{{ number_format((float) ($profit_and_loss['sales_discounts'] ?? 0), 2) }}
              </td>
            </tr>
            <tr class="bg-slate-50/60 dark:bg-slate-800/40 font-semibold border-y border-slate-100 dark:border-slate-800">
              <td class="py-2.5 px-4 text-slate-800 dark:text-slate-200">Net Operating Revenues</td>
              <td class="py-2.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format((float) ($profit_and_loss['net_revenue'] ?? 0), 2) }}
              </td>
            </tr>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-2.5 px-4 text-slate-700 dark:text-slate-300">Less: Operating Expenses (Supplies, Salaries, Facilities, Overhead)</td>
              <td class="py-2.5 px-4 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                -₱{{ number_format((float) ($profit_and_loss['total_expenses'] ?? 0), 2) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="border-t-2 border-slate-300 bg-blue-50/80 dark:border-slate-700 dark:bg-blue-950/40 font-bold">
            <tr>
              <td class="py-3 px-4 uppercase text-blue-900 dark:text-blue-300">NET OPERATING SURPLUS / (DEFICIT)</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums text-blue-600 dark:text-blue-400 text-sm">
                ₱{{ number_format((float) ($profit_and_loss['net_income'] ?? 0), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
