@extends('layouts.app')

@section('title', 'Cash Flow Forecasting - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'cash-flow-forecast')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Hospital Cash Flow Forecasting Engine
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('cash.cash-flow-forecast.export', ['horizon' => $horizon_days ?? 30]) }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-blue-600"></i>
        <span>Export Schedule (CSV)</span>
      </a>
      <a 
        href="{{ route('cash.liquidity') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-gauge"></i>
        <span>Liquidity Ratios</span>
      </a>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Available Liquid Cash" 
      :value="$available_cash ?? 0" 
      icon="ph-vault" 
      color="blue" 
      subtitle="Across {{ count($bank_accounts ?? []) }} active bank accounts"
    />
    <x-stat-card 
      title="Projected Inflows" 
      :value="$total_projected_inflows ?? 0" 
      icon="ph-arrow-down-left" 
      color="emerald" 
      subtitle="Patient: ₱{{ number_format((float) ($patient_inflows ?? 0), 2) }} | HMO: ₱{{ number_format((float) ($hmo_inflows ?? 0), 2) }}"
    />
    <x-stat-card 
      title="Committed Outflows" 
      :value="$total_committed_outflows ?? 0" 
      icon="ph-arrow-up-right" 
      color="rose" 
      subtitle="AP: ₱{{ number_format((float) ($ap_outflows ?? 0), 2) }} | Payroll: ₱{{ number_format((float) ($payroll_outflows ?? 0), 2) }}"
    />
    @php
      $isPositive = (bccomp((string) ($net_operating_position ?? 0), '0.0000', 4) >= 0);
    @endphp
    <x-stat-card 
      title="Projected Ending Cash" 
      :value="$projected_ending_cash ?? 0" 
      icon="ph-trend-up" 
      color="{{ $isPositive ? 'emerald' : 'rose' }}" 
      subtitle="Net flow: {{ $isPositive ? '+' : '' }}₱{{ number_format((float) ($net_operating_position ?? 0), 2) }}"
    />
  </div>

  <!-- Chronological Cash Events Schedule Grid -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar Header -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <form method="GET" action="{{ route('cash.cash-flow-forecast') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2.5 flex-wrap">
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <i class="ph-bold ph-calendar-blank"></i>
            <span>Forecasting Horizon:</span>
          </div>
          <select 
            name="horizon" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
            onchange="this.form.submit()"
          >
            <option value="15" {{ request('horizon') == 15 ? 'selected' : '' }}>Next 15 Days (Bi-Weekly Cash Runway)</option>
            <option value="30" {{ request('horizon', 30) == 30 ? 'selected' : '' }}>Next 30 Days (Monthly Operational Forecast)</option>
            <option value="60" {{ request('horizon') == 60 ? 'selected' : '' }}>Next 60 Days (Two-Month Horizon)</option>
            <option value="90" {{ request('horizon') == 90 ? 'selected' : '' }}>Next 90 Days (Quarterly Liquidity Outlook)</option>
          </select>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-xs text-slate-400">
            Active window: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ request('horizon', 30) }} Days</span>
          </span>
          <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            {{ count($events ?? []) }} Scheduled Events
          </span>
        </div>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Event Type</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Category</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Reference #</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Counterparty / Entity</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Expected Due Date</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Projected Amount</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($events ?? [] as $evt)
          @php
            $isInflow = ($evt['type'] === 'INFLOW');
          @endphp
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3 px-4">
              @if($isInflow)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                  <i class="ph-bold ph-arrow-down-left"></i> INFLOW
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                  <i class="ph-bold ph-arrow-up-right"></i> OUTFLOW
                </span>
              @endif
            </td>
            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
              {{ $evt['category'] }}
            </td>
            <td class="py-3 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">
              {{ $evt['reference'] }}
            </td>
            <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
              {{ $evt['counterparty'] }}
            </td>
            <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400">
              {{ date('M d, Y', strtotime($evt['due_date'])) }}
            </td>
            <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold {{ $isInflow ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
              {{ $isInflow ? '+' : '-' }}₱{{ number_format((float) $evt['amount'], 2) }}
            </td>
            <td class="py-3 px-4 text-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                {{ $evt['status'] }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-calendar-blank text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No scheduled cash events within the selected horizon.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
      <span class="text-xs text-slate-500 dark:text-slate-400">
        Showing {{ count($events ?? []) }} chronological cash forecasting line items
      </span>
    </div>
  </div>
</div>
@endsection
