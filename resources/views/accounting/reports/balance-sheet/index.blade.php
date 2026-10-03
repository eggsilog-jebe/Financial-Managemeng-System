@extends('layouts.app')

@section('title', 'Balance Sheet Statement - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'balance-sheet')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Statement of Financial Position (Balance Sheet)
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('reporting.balance-sheet.export', ['as_of_date' => $asOfDate ?? date('Y-m-d')]) }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-file-csv text-blue-600"></i>
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
      title="Total Assets (Debit)" 
      :value="$totalAssets ?? 0" 
      icon="ph-trend-up" 
      color="emerald" 
      subtitle="Combined current & non-current assets"
    />
    <x-stat-card 
      title="Total Liabilities (Credit)" 
      :value="$totalLiabilities ?? 0" 
      icon="ph-bank" 
      color="rose" 
      subtitle="Current & non-current obligations"
    />
    <x-stat-card 
      title="Adjusted Net Equity" 
      :value="$totalEquity ?? 0" 
      icon="ph-scales" 
      color="blue" 
      subtitle="Surplus: ₱{{ number_format((float) ($netSurplus ?? 0), 2) }}"
    />
    <x-stat-card 
      title="Accounting Invariance" 
      :value="($isBalanced ?? true) ? 'BALANCED' : 'UNBALANCED'" 
      :isCurrency="false"
      icon="ph-shield-check" 
      color="{{ ($isBalanced ?? true) ? 'emerald' : 'rose' }}" 
      subtitle="A = L + E (Variance: ₱{{ number_format((float) ($variance ?? 0), 2) }})"
    />
  </div>

    <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

<!-- Filter Toolbar -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('reporting.balance-sheet') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            <i class="ph-bold ph-calendar mr-1"></i> As-Of Cutoff Date:
          </label>
          <input 
            type="date" 
            name="as_of_date" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
            value="{{ $asOfDate ?? date('Y-m-d') }}"
          >
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            Comparative:
          </label>
          <select 
            name="comparison" 
            class="rounded-xl border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
          >
            <option value="none" {{ ($comparison ?? '') === 'none' ? 'selected' : '' }}>Standard Single Period</option>
            <option value="prior_year" {{ ($comparison ?? '') === 'prior_year' ? 'selected' : '' }}>Compare Prior Year (1 Year Prior)</option>
            <option value="prior_quarter" {{ ($comparison ?? '') === 'prior_quarter' ? 'selected' : '' }}>Compare Prior Quarter (3 Months Prior)</option>
          </select>
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
          href="{{ route('reporting.balance-sheet') }}" 
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 cursor-pointer"
        >
          <i class="ph-bold ph-x"></i>
          <span>Reset</span>
        </a>
      </div>
    </form>
  </div>

  <!-- Dual Column Balance Sheet Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Assets Column -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 flex flex-col">
      <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 bg-emerald-50/50 dark:bg-emerald-950/20">
        <div class="flex items-center gap-2">
          <i class="ph-bold ph-vault text-emerald-600"></i>
          <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
            Assets (Debit Accounts)
          </h5>
        </div>
        <span class="font-mono tabular-nums text-xs font-bold text-emerald-600 dark:text-emerald-400">
          ₱{{ number_format((float) ($totalAssets ?? 0), 2) }}
        </span>
      </div>

      <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs">
          <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60">
            <tr>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 w-24">Code</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Account Description</th>
              <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($assets ?? [] as $a)
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                  {{ $a['code'] }}
                </span>
              </td>
              <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">{{ $a['name'] }}</td>
              <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                ₱{{ number_format((float) $a['balance'], 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="py-8 text-center text-slate-400">No asset ledger accounts recorded.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-semibold">
            <tr>
              <td colspan="2" class="py-3.5 px-4 uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                Total Assets
              </td>
              <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                ₱{{ number_format((float) ($totalAssets ?? 0), 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Liabilities & Equity Column -->
    <div class="space-y-6">
      <!-- Liabilities Card -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 bg-rose-50/50 dark:bg-rose-950/20">
          <div class="flex items-center gap-2">
            <i class="ph-bold ph-bank text-rose-600"></i>
            <h5 class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">
              Liabilities (Credit Accounts)
            </h5>
          </div>
          <span class="font-mono tabular-nums text-xs font-bold text-rose-600 dark:text-rose-400">
            ₱{{ number_format((float) ($totalLiabilities ?? 0), 2) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60">
              <tr>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 w-24">Code</th>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Account Description</th>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              @forelse($liabilities ?? [] as $l)
              <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                    {{ $l['code'] }}
                  </span>
                </td>
                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">{{ $l['name'] }}</td>
                <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                  ₱{{ number_format((float) $l['balance'], 2) }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="py-6 text-center text-slate-400">No liability ledger accounts recorded.</td>
              </tr>
              @endforelse
            </tbody>
            <tfoot class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/60 font-semibold">
              <tr>
                <td colspan="2" class="py-3 px-4 uppercase text-slate-700 dark:text-slate-300">
                  Total Liabilities
                </td>
                <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-rose-600 dark:text-rose-400">
                  ₱{{ number_format((float) ($totalLiabilities ?? 0), 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Equity Card -->
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800 bg-blue-50/50 dark:bg-blue-950/20">
          <div class="flex items-center gap-2">
            <i class="ph-bold ph-scales text-blue-600"></i>
            <h5 class="text-xs font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300">
              Equity &amp; Accumulated Surplus
            </h5>
          </div>
          <span class="font-mono tabular-nums text-xs font-bold text-blue-600 dark:text-blue-400">
            ₱{{ number_format((float) ($totalEquity ?? 0), 2) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60">
              <tr>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 w-24">Code</th>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">Account Description</th>
                <th class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Balance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              @forelse($equity ?? [] as $e)
              <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                    {{ $e['code'] }}
                  </span>
                </td>
                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">{{ $e['name'] }}</td>
                <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-blue-600 dark:text-blue-400">
                  ₱{{ number_format((float) $e['balance'], 2) }}
                </td>
              </tr>
              @empty
              @endforelse
              <tr class="bg-amber-50/50 dark:bg-amber-950/20">
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                    SURPLUS
                  </span>
                </td>
                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">Current Year Operating Surplus / (Deficit)</td>
                <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-blue-600 dark:text-blue-400">
                  ₱{{ number_format((float) ($netSurplus ?? 0), 2) }}
                </td>
              </tr>
            </tbody>
            <tfoot class="border-t-2 border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 font-semibold">
              <tr>
                <td colspan="2" class="py-3.5 px-4 uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                  Total Liabilities &amp; Equity
                </td>
                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-blue-600 dark:text-blue-400 text-sm">
                  ₱{{ number_format((float) ($totalLiabAndEq ?? 0), 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
