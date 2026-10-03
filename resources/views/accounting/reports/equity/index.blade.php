@extends('layouts.app')

@section('title', 'Statement of Changes in Equity - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'equity')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Statement of Changes in Equity (PFRS / IAS 1)
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('reporting.equity.export', ['date_from' => $date_from, 'date_to' => $date_to]) }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-purple-600"></i>
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
      title="Opening Total Equity" 
      :value="$opening_equity ?? 0" 
      icon="ph-clock-counter-clockwise" 
      color="slate" 
      subtitle="As of {{ $date_from }}"
    />
    @php
      $isSurplus = ((float) ($current_period_surplus ?? 0) >= 0);
    @endphp
    <x-stat-card 
      title="Current Period Surplus" 
      :value="$current_period_surplus ?? 0" 
      icon="ph-chart-line-up" 
      color="{{ $isSurplus ? 'emerald' : 'rose' }}" 
      subtitle="Flows directly from P&amp;L"
    />
    <x-stat-card 
      title="Net Capital Movements" 
      :value="$net_equity_movements ?? 0" 
      icon="ph-arrows-left-right" 
      color="blue" 
      subtitle="Additions: ₱{{ number_format((float) ($capital_additions ?? 0), 2) }}"
    />
    <x-stat-card 
      title="Closing Total Equity" 
      :value="$total_closing_equity ?? 0" 
      icon="ph-scales" 
      color="purple" 
      subtitle="Reconciled to Balance Sheet"
    />
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- Filter Toolbar -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('reporting.equity') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            <i class="ph-bold ph-calendar mr-1"></i> Period Start:
          </label>
          <input 
            type="date" 
            name="date_from" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
            value="{{ $date_from }}"
          >
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            Cutoff Date:
          </label>
          <input 
            type="date" 
            name="date_to" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
            value="{{ $date_to }}"
          >
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button 
          type="submit" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
        >
          <i class="ph-bold ph-arrows-clockwise"></i>
          <span>Update Statement</span>
        </button>
        <a 
          href="{{ route('reporting.equity') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-x"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Statement Table Card -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 p-5 dark:border-slate-800">
      <div>
        <h5 class="text-base font-bold text-slate-900 dark:text-white">Statement of Changes in Equity Breakdown</h5>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Reporting Period: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $date_from }}</strong> to <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $date_to }}</strong> (PFRS / IAS 1)
        </p>
      </div>
      <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold font-mono bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
        PFRS COMPLIANT
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 w-28">Account Code</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Equity Component / Fund</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Opening Balance</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Capital Additions</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Drawdowns / Reductions</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Closing Balance</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($accounts ?? [] as $acc)
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                {{ $acc['code'] }}
              </span>
            </td>
            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">{{ $acc['name'] }}</td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
              ₱{{ number_format((float) $acc['opening_balance'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
              +₱{{ number_format((float) $acc['additions'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) $acc['deductions'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-slate-900 dark:text-white">
              ₱{{ number_format((float) $acc['closing_balance'], 2) }}
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="py-8 text-center text-slate-400">No equity accounts found.</td>
          </tr>
          @endforelse

          <!-- Net Operating Surplus Line -->
          <tr class="bg-purple-50/50 dark:bg-purple-950/20">
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200 border border-purple-300 dark:border-purple-700">
                P&amp;L-SURPLUS
              </span>
            </td>
            <td class="py-3.5 px-4">
              <div class="font-bold text-slate-900 dark:text-white">Current Period Net Operating Surplus / (Deficit)</div>
              <span class="text-xs text-slate-400">Accumulated clinical and operating earnings for the period</span>
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-400">₱0.00</td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400">
              +₱{{ number_format((float) ($current_period_surplus ?? 0), 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-400">₱0.00</td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400">
              ₱{{ number_format((float) ($current_period_surplus ?? 0), 2) }}
            </td>
          </tr>
        </tbody>
        <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-semibold">
          <tr>
            <td colspan="2" class="py-3.5 px-4 uppercase text-slate-800 dark:text-slate-200 tracking-wider">
              Total Ending Equity (PFRS Reconciled)
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
              ₱{{ number_format((float) ($opening_equity ?? 0), 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-emerald-600 dark:text-emerald-400">
              +₱{{ number_format((float) bcadd((string) $capital_additions, (string) $current_period_surplus, 4), 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-rose-600 dark:text-rose-400">
              -₱{{ number_format((float) $distributions, 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-purple-600 dark:text-purple-400 text-sm">
              ₱{{ number_format((float) ($total_closing_equity ?? 0), 2) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Card Footer -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200 px-5 py-3 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
      <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
        <i class="ph-bold ph-shield-check text-emerald-600 text-base"></i>
        <span>Reconciles exactly with Balance Sheet Statement Equity total.</span>
      </span>
      <div class="flex items-center gap-2">
        <a 
          href="{{ route('reporting.balance-sheet') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-shield-check text-blue-600"></i>
          <span>View Balance Sheet</span>
        </a>
        <a 
          href="{{ route('reporting.profit-loss') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-chart-line-up text-emerald-600"></i>
          <span>View P&amp;L Statement</span>
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
