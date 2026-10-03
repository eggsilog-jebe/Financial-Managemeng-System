@extends('layouts.app')

@section('title', 'Financial KPI Dashboard - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'financial-kpi-dashboard')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Financial Performance &amp; KPI Dashboard
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('reporting.kpi-dashboard.export') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-blue-600"></i>
        <span>Export KPI Deck (CSV)</span>
      </a>
      <a 
        href="{{ route('reporting.executive-reports') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-briefcase"></i>
        <span>Executive Dossier</span>
      </a>
    </div>
  </div>

  <!-- Primary KPI Metric Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Operating Profit Margin" 
      :value="number_format((float) ($operating_margin ?? $operatingProfitMargin ?? 0), 1) . '%'" 
      :isCurrency="false"
      icon="ph-trend-up" 
      color="emerald" 
      subtitle="Target: > 15.0% Operating Surplus"
    />
    <x-stat-card 
      title="Days Sales Outstanding" 
      :value="number_format((float) ($dso ?? 0), 1) . ' Days'" 
      :isCurrency="false"
      icon="ph-calendar-check" 
      color="blue" 
      subtitle="AR Cycle: ₱{{ number_format((float) ($total_ar ?? 0), 2) }}"
    />
    <x-stat-card 
      title="Days Payable Outstanding" 
      :value="number_format((float) ($dpo ?? 0), 1) . ' Days'" 
      :isCurrency="false"
      icon="ph-clock" 
      color="purple" 
      subtitle="AP Cycle: ₱{{ number_format((float) ($total_ap ?? 0), 2) }}"
    />
    <x-stat-card 
      title="Current Working Ratio" 
      :value="number_format((float) ($current_ratio ?? $currentRatio ?? 0), 2) . 'x'" 
      :isCurrency="false"
      icon="ph-shield-check" 
      color="amber" 
      subtitle="Quick Ratio: {{ number_format((float) ($quick_ratio ?? 0), 2) }}x"
    />
  </div>

  <!-- Secondary Metrics Row -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-stat-card 
      title="Total Operating Revenue" 
      :value="$total_revenue ?? $totalRevenue ?? 0" 
      icon="ph-currency-circle-dollar" 
      color="emerald" 
      subtitle="Clinical Services, HMOs & Diagnostics"
    />
    <x-stat-card 
      title="Total Operating Expenses" 
      :value="$total_expense ?? $totalExpense ?? 0" 
      icon="ph-calculator" 
      color="rose" 
      subtitle="Medical Supplies, Payroll & Overhead"
    />
    <x-stat-card 
      title="Liquid Cash Reserves" 
      :value="$total_cash ?? 0" 
      icon="ph-vault" 
      color="purple" 
      subtitle="{{ number_format((float) ($days_cash_on_hand ?? 0), 1) }} Days Cash on Hand (DCOH)"
    />
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- 12-Month Financial Trajectory Grid -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-chart-line-up text-blue-600"></i>
        <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
          12-Month Trajectory: Revenue vs. Expense &amp; Net Surplus Run
        </h6>
      </div>
      <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        Trailing 12 Months
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Fiscal Month</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Revenue Inflow</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Expense Outflow</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Operating Surplus / (Deficit)</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Performance</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($trajectory ?? [] as $t)
          @php
            $isMonthSurplus = ($t['surplus'] >= 0);
          @endphp
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">{{ $t['label'] }}</td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
              ₱{{ number_format((float) $t['revenue'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              ₱{{ number_format((float) $t['expense'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold {{ $isMonthSurplus ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
              {{ $isMonthSurplus ? '+' : '' }}₱{{ number_format((float) $t['surplus'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-center">
              @if($isMonthSurplus)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                  <i class="ph-bold ph-arrow-up-right"></i> Surplus
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                  <i class="ph-bold ph-arrow-down-right"></i> Deficit
                </span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500">
              No monthly trajectory data recorded.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
