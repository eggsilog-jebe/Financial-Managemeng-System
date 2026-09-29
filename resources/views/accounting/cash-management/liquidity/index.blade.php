@extends('layouts.app')

@section('title', 'Liquidity Management & Ratios - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'liquidity')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Treasury Liquidity &amp; Cash Health
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('cash.liquidity.export') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-emerald-600"></i>
        <span>Export Report (CSV)</span>
      </a>
      <button 
        type="button" 
        onclick="window.print()" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 ring-1 ring-slate-800/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-printer"></i>
        <span>Print Treasury Report</span>
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Total Liquid Reserves" 
      :value="$total_cash ?? 0" 
      icon="ph-vault" 
      color="emerald" 
      subtitle="{{ $active_accounts_count ?? 0 }} Active Depository Accounts"
    />
    <x-stat-card 
      title="Days Cash on Hand (DCOH)" 
      :value="number_format((float) ($days_cash_on_hand ?? 0), 1) . ' Days'" 
      :isCurrency="false"
      icon="ph-hourglass-high" 
      color="blue" 
      subtitle="Daily Burn: ₱{{ number_format((float) ($daily_burn_rate ?? 0), 2) }}/day"
    />
    <x-stat-card 
      title="Treasury Health" 
      :value="$liquidity_status['rating'] ?? 'ADEQUATE'" 
      :isCurrency="false"
      icon="ph-shield-check" 
      color="purple" 
      subtitle="{{ $liquidity_status['desc'] ?? 'Healthy operational runway' }}"
    />
    <x-stat-card 
      title="Safety Floor Violations" 
      :value="($below_minimum_count ?? 0) . ' Accounts'" 
      :isCurrency="false"
      icon="ph-warning" 
      color="{{ ($below_minimum_count ?? 0) > 0 ? 'rose' : 'emerald' }}" 
      subtitle="Below Minimum Operating Reserve"
    />
  </div>

  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <!-- Institutional Distribution & Concentration Table -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-chart-pie-slice text-emerald-600"></i>
        <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
          Institutional Depository Concentration &amp; Reserve Floor Monitors
        </h6>
      </div>
      <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
        {{ count($concentration ?? []) }} Depository Institutions
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Bank &amp; Account Name</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Account Number</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">GL Account Code</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Current Balance</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Minimum Safety Floor</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300" style="width: 200px;">Concentration %</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Safety Status</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Account Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($concentration ?? [] as $c)
          @php
            $isBelow = $c['is_below_min'];
            $pct = $c['percentage'];
          @endphp
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $isBelow ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
            <td class="py-3.5 px-4">
              <div class="font-bold text-slate-900 dark:text-white">{{ $c['name'] }}</div>
              <span class="text-xs text-slate-400">{{ $c['bank_name'] }}</span>
            </td>
            <td class="py-3.5 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">
              {{ $c['account_number'] }}
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                {{ $c['gl_code'] }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold {{ $isBelow ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
              ₱{{ number_format((float) $c['balance'], 2) }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-500 dark:text-slate-400">
              ₱{{ number_format((float) $c['minimum_balance'], 2) }}
            </td>
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-2.5">
                <div class="h-2 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                  <div 
                    class="h-full rounded-full {{ $pct > 50 ? 'bg-amber-500' : 'bg-emerald-500' }}" 
                    style="width: {{ min(100, $pct) }}%;"
                  ></div>
                </div>
                <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 w-12 text-right">
                  {{ number_format((float) $pct, 1) }}%
                </span>
              </div>
            </td>
            <td class="py-3.5 px-4 text-center">
              @if($isBelow)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                  <i class="ph-bold ph-warning-circle"></i> BELOW FLOOR
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                  <i class="ph-bold ph-check-circle"></i> OPTIMAL
                </span>
              @endif
            </td>
            <td class="py-3.5 px-4 text-center">
              @if($c['is_active'])
                <x-status-badge status="Active" color="emerald" />
              @else
                <x-status-badge :status="$c['status']" color="slate" />
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-bank text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No depository accounts found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
      <span class="text-xs text-slate-500 dark:text-slate-400">
        Showing {{ count($concentration ?? []) }} institutional bank accounts
      </span>
    </div>
  </div>
</div>
@endsection
