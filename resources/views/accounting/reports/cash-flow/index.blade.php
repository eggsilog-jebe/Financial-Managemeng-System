@extends('layouts.app')

@section('title', 'Statement of Cash Flows - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'cash-flow-statement')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Statement of Cash Flows
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('reporting.cash-flow-statement.export', ['date_from' => $date_from ?? date('Y-01-01'), 'date_to' => $date_to ?? date('Y-m-d')]) }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-emerald-600"></i>
        <span>Export Statement (CSV)</span>
      </a>
      <button 
        type="button" 
        onclick="window.print()" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 ring-1 ring-slate-800/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-printer"></i>
        <span>Print Statement</span>
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Operating Cash Flow" 
      :value="$net_operating_cash ?? 0" 
      icon="ph-hand-coins" 
      color="emerald" 
      subtitle="Cash generated from clinical operations"
    />
    <x-stat-card 
      title="Investing (CapEx)" 
      :value="$net_investing_cash ?? 0" 
      icon="ph-buildings" 
      color="amber" 
      subtitle="Medical diagnostic & facility CapEx"
    />
    <x-stat-card 
      title="Financing Activities" 
      :value="$net_financing_cash ?? 0" 
      icon="ph-bank" 
      color="blue" 
      subtitle="Capital reserves & debt facilities"
    />
    <x-stat-card 
      title="Ending Cash Pool" 
      :value="$closing_cash ?? 0" 
      icon="ph-vault" 
      color="purple" 
      subtitle="Reconciled depository liquid balance"
    />
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- Filter Toolbar -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('reporting.cash-flow-statement') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            <i class="ph-bold ph-calendar mr-1"></i> Period From:
          </label>
          <input 
            type="date" 
            name="date_from" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
            value="{{ $date_from ?? date('Y-01-01') }}"
          >
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            To:
          </label>
          <input 
            type="date" 
            name="date_to" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
            value="{{ $date_to ?? date('Y-m-d') }}"
          >
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button 
          type="submit" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
        >
          <i class="ph-bold ph-arrows-clockwise"></i>
          <span>Recompute</span>
        </button>
        <a 
          href="{{ route('reporting.cash-flow-statement') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-x"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- PAS 7 Formal Statement Table -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex items-center gap-2 border-b border-slate-200 p-4 dark:border-slate-800">
      <i class="ph-bold ph-list-numbers text-emerald-600"></i>
      <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
        PAS 7 Statement of Cash Flows Breakdown
      </h6>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-5 font-semibold text-slate-700 dark:text-slate-300">Cash Flow Line Item &amp; Classification</th>
            <th class="py-3 px-5 font-semibold text-slate-700 dark:text-slate-300 text-right w-64">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          <!-- 1. Operating Activities -->
          <tr class="bg-slate-50/80 dark:bg-slate-800/50 font-bold">
            <td colspan="2" class="py-2.5 px-5 text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <i class="ph-bold ph-caret-right text-emerald-600"></i>
              <span>1. CASH FLOWS FROM OPERATING ACTIVITIES</span>
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Cash received from Patient Copays, HMO Settlements &amp; Clinical Fees
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
              ₱{{ number_format((float) ($operating_receipts ?? 0), 2) }}
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Cash paid to Medical Suppliers, Pharmaceuticals &amp; Consumables
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) ($supplier_disbursements ?? 0), 2) }}
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Cash paid to Healthcare Personnel, Doctors &amp; Hospital Staff Payroll
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) ($payroll_disbursements ?? 0), 2) }}
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Cash paid for Direct Clinical Operations &amp; Facility Utilities
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) ($direct_opex_cash ?? 0), 2) }}
            </td>
          </tr>
          <tr class="bg-emerald-50/60 dark:bg-emerald-950/20 font-bold border-y border-emerald-100 dark:border-emerald-900/40">
            <td class="py-3 px-5 pl-9 text-emerald-800 dark:text-emerald-300 uppercase">
              Net Cash Provided by (Used in) Operating Activities
            </td>
            <td class="py-3 px-5 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400 text-sm">
              ₱{{ number_format((float) ($net_operating_cash ?? 0), 2) }}
            </td>
          </tr>

          <!-- 2. Investing Activities -->
          <tr class="bg-slate-50/80 dark:bg-slate-800/50 font-bold">
            <td colspan="2" class="py-2.5 px-5 text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <i class="ph-bold ph-caret-right text-amber-600"></i>
              <span>2. CASH FLOWS FROM INVESTING ACTIVITIES</span>
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Acquisition of Medical Diagnostic Equipment &amp; Hospital Infrastructure (CapEx)
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) ($capex_outflows ?? 0), 2) }}
            </td>
          </tr>
          <tr class="bg-slate-50 dark:bg-slate-800/60 font-bold border-y border-slate-200 dark:border-slate-700">
            <td class="py-3 px-5 pl-9 text-slate-800 dark:text-slate-200 uppercase">
              Net Cash Provided by (Used in) Investing Activities
            </td>
            <td class="py-3 px-5 text-right font-mono tabular-nums font-bold text-slate-900 dark:text-white">
              ₱{{ number_format((float) ($net_investing_cash ?? 0), 2) }}
            </td>
          </tr>

          <!-- 3. Financing Activities -->
          <tr class="bg-slate-50/80 dark:bg-slate-800/50 font-bold">
            <td colspan="2" class="py-2.5 px-5 text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
              <i class="ph-bold ph-caret-right text-blue-600"></i>
              <span>3. CASH FLOWS FROM FINANCING ACTIVITIES</span>
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-700 dark:text-slate-300">
              Proceeds from Institutional Capital Reserves &amp; Credit Facilities
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums text-slate-700 dark:text-slate-300">
              ₱{{ number_format((float) ($net_financing_cash ?? 0), 2) }}
            </td>
          </tr>
          <tr class="bg-slate-50 dark:bg-slate-800/60 font-bold border-y border-slate-200 dark:border-slate-700">
            <td class="py-3 px-5 pl-9 text-slate-800 dark:text-slate-200 uppercase">
              Net Cash Provided by (Used in) Financing Activities
            </td>
            <td class="py-3 px-5 text-right font-mono tabular-nums font-bold text-slate-900 dark:text-white">
              ₱{{ number_format((float) ($net_financing_cash ?? 0), 2) }}
            </td>
          </tr>

          <!-- Summary Net Cash Movement -->
          <tr class="bg-blue-50/60 dark:bg-blue-950/20 font-bold border-y border-blue-100 dark:border-blue-900/40">
            <td class="py-3.5 px-5 text-blue-900 dark:text-blue-300 uppercase tracking-wide">
              NET INCREASE / (DECREASE) IN CASH &amp; CASH EQUIVALENTS
            </td>
            <td class="py-3.5 px-5 text-right font-mono tabular-nums font-bold text-blue-600 dark:text-blue-400 text-base">
              ₱{{ number_format((float) ($net_cash_flow ?? 0), 2) }}
            </td>
          </tr>
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-2.5 px-5 pl-9 text-slate-500 dark:text-slate-400">
              Cash and Cash Equivalents at Beginning of Period
            </td>
            <td class="py-2.5 px-5 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
              ₱{{ number_format((float) ($opening_cash ?? 0), 2) }}
            </td>
          </tr>
          <tr class="bg-emerald-500/10 dark:bg-emerald-500/20 font-bold border-t-2 border-emerald-500">
            <td class="py-4 px-5 text-emerald-800 dark:text-emerald-300 uppercase tracking-wide text-sm">
              CASH AND CASH EQUIVALENTS AT END OF PERIOD (RECONCILED)
            </td>
            <td class="py-4 px-5 text-right font-mono tabular-nums font-extrabold text-emerald-600 dark:text-emerald-400 text-lg">
              ₱{{ number_format((float) ($closing_cash ?? 0), 2) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
