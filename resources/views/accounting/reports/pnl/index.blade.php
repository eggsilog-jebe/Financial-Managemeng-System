@extends('layouts.app')

@section('title', 'Profit & Loss Statement - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'profit-loss')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Statement of Profit &amp; Loss (Income Statement)
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('reporting.profit-loss.export', ['date_from' => $dateFrom ?? date('Y-01-01'), 'date_to' => $dateTo ?? date('Y-m-d')]) }}" 
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
      title="Gross Revenues" 
      :value="$grossRevenue ?? 0" 
      icon="ph-currency-circle-dollar" 
      color="emerald" 
      subtitle="Before statutory & HMO deductions"
    />
    <x-stat-card 
      title="Discounts & Deductions" 
      :value="-1 * (float) ($salesDiscounts ?? 0)" 
      icon="ph-tag" 
      color="amber" 
      subtitle="RA 9994 / RA 10754 & HMO adjustments"
    />
    <x-stat-card 
      title="Total Expenses" 
      :value="$totalExpense ?? 0" 
      icon="ph-trend-down" 
      color="rose" 
      subtitle="Direct medical & administrative overhead"
    />
    @php
      $isSurplus = (bccomp((string) ($netIncome ?? 0), '0.0000', 4) >= 0);
    @endphp
    <x-stat-card 
      title="Net Operating Surplus" 
      :value="$netIncome ?? 0" 
      icon="ph-scales" 
      color="{{ $isSurplus ? 'emerald' : 'rose' }}" 
      subtitle="Margin: {{ number_format((float) ($profitMargin ?? 0), 1) }}%"
    />
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- Filter Toolbar -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('reporting.profit-loss') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
      <div class="sm:col-span-3">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Reporting Date From:</label>
        <input 
          type="date" 
          name="date_from" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          value="{{ $dateFrom ?? date('Y-01-01') }}"
        >
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Reporting Date To:</label>
        <input 
          type="date" 
          name="date_to" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          value="{{ $dateTo ?? date('Y-m-d') }}"
        >
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Department Breakdown:</label>
        <select 
          name="department" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
        >
          <option value="">All Hospital Departments</option>
          <option value="Emergency" {{ ($department ?? '') === 'Emergency' ? 'selected' : '' }}>Emergency Room (ER)</option>
          <option value="Inpatient" {{ ($department ?? '') === 'Inpatient' ? 'selected' : '' }}>Inpatient Wards &amp; ICU</option>
          <option value="Outpatient" {{ ($department ?? '') === 'Outpatient' ? 'selected' : '' }}>Outpatient Department (OPD)</option>
          <option value="Laboratory" {{ ($department ?? '') === 'Laboratory' ? 'selected' : '' }}>Laboratory &amp; Diagnostics</option>
          <option value="Pharmacy" {{ ($department ?? '') === 'Pharmacy' ? 'selected' : '' }}>Pharmacy &amp; Therapeutics</option>
          <option value="Radiology" {{ ($department ?? '') === 'Radiology' ? 'selected' : '' }}>Radiology &amp; Imaging</option>
        </select>
      </div>

      <div class="sm:col-span-3 flex gap-2">
        <button 
          type="submit" 
          class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
        >
          <i class="ph-bold ph-arrows-clockwise"></i>
          <span>Recompute</span>
        </button>
        <a 
          href="{{ route('reporting.profit-loss') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-x"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Income Statement Sections -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Revenue Section -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 flex flex-col">
      <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 bg-emerald-50/50 dark:bg-emerald-950/20">
        <div class="flex items-center gap-2">
          <i class="ph-bold ph-arrow-circle-down-right text-emerald-600"></i>
          <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
            Operating Revenues
          </h5>
        </div>
        <span class="font-mono tabular-nums text-xs font-bold text-emerald-600 dark:text-emerald-400">
          ₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}
        </span>
      </div>

      <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs">
          <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60">
            <tr>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 w-24">Code</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Revenue Stream</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Department</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($revenues ?? [] as $r)
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                  {{ $r['code'] }}
                </span>
              </td>
              <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">{{ $r['name'] }}</td>
              <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $r['department'] }}</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format((float) $r['balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="py-8 text-center text-slate-400">No revenue journal lines posted in period.</td>
            </tr>
            @endforelse

            @if(isset($allowances) && count($allowances) > 0)
            <tr class="bg-amber-50/50 dark:bg-amber-950/20">
              <td colspan="4" class="py-2.5 px-4 font-bold uppercase text-[11px] text-amber-800 dark:text-amber-300">
                Less: Contractual Allowances &amp; Statutory Discounts
              </td>
            </tr>
            @foreach($allowances as $a)
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                  {{ $a['code'] }}
                </span>
              </td>
              <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">{{ $a['name'] }}</td>
              <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $a['department'] }}</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-amber-600 dark:text-amber-400">
                -₱{{ number_format((float) $a['balance'], 2) }}
              </td>
            </tr>
            @endforeach
            @endif
          </tbody>
          <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-semibold">
            <tr>
              <td colspan="3" class="py-3.5 px-4 uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                Net Operating Revenues
              </td>
              <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                ₱{{ number_format((float) ($totalRevenue ?? 0), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Expenses Section -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 flex flex-col">
      <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 bg-rose-50/50 dark:bg-rose-950/20">
        <div class="flex items-center gap-2">
          <i class="ph-bold ph-arrow-circle-up-right text-rose-600"></i>
          <h5 class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">
            Operating Expenses
          </h5>
        </div>
        <span class="font-mono tabular-nums text-xs font-bold text-rose-600 dark:text-rose-400">
          ₱{{ number_format((float) ($totalExpense ?? 0), 2) }}
        </span>
      </div>

      <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs">
          <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60">
            <tr>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 w-24">Code</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Expense Category</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Department</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($expenses ?? [] as $e)
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                  {{ $e['code'] }}
                </span>
              </td>
              <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">{{ $e['name'] }}</td>
              <td class="py-3 px-4 text-slate-500 dark:text-slate-400">{{ $e['department'] }}</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                ₱{{ number_format((float) $e['balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="py-8 text-center text-slate-400">No operating expenses recorded in period.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-semibold">
            <tr>
              <td colspan="3" class="py-3.5 px-4 uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                Total Operating Expenses
              </td>
              <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-rose-600 dark:text-rose-400 text-sm">
                ₱{{ number_format((float) ($totalExpense ?? 0), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
