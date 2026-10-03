@extends('layouts.app')

@section('title', 'Tax Returns & Statutory Filings - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-returns')

@section('content')
<div class="space-y-6" x-data="{
  init() {
    this.$watch('returnDetailsOpen', val => {
      document.body.classList.toggle('overflow-hidden', val);
      document.body.classList.toggle('modal-open', val);
    });
  },
  search: '',
  formFilter: '',
  statusFilter: '',
  returnDetailsOpen: false,
  selectedReturn: {
    code: '',
    title: '',
    period: '',
    due: '',
    payable: '₱0.00',
    ref: '',
    status: 'FILED'
  },
  openDetails(ret) {
    this.selectedReturn = ret;
    this.returnDetailsOpen = true;
  }
}">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Tax Returns &amp; Statutory Filings
        </h1>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
      <button 
        type="button" 
        onclick="alert('Opening BIR Tax Calendar Deadlines...')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 shadow-sm transition-all dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700"
      >
        <i class="ph-bold ph-calendar-check"></i>
        <span>BIR Tax Calendar</span>
      </button>
      <button 
        type="button" 
        id="btnFileReturn"
        @click="$dispatch('open-modal', 'fileReturnModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all"
      >
        <i class="ph-bold ph-file-arrow-up"></i>
        <span>File Statutory Return</span>
      </button>
    </div>
  </div>

  <!-- Alerts -->
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 p-4 text-xs font-medium text-emerald-800 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-check-circle text-lg text-emerald-600"></i>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-xl bg-rose-50 p-4 text-xs font-medium text-rose-800 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-warning-circle text-lg text-rose-600"></i>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl bg-rose-50 p-4 text-xs font-medium text-rose-800 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300">
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
    <x-stat-card 
      title="Filed Returns (This Year)" 
      :value="count($returns ?? [])" 
      :isCurrency="false"
      icon="ph-check-circle" 
      color="emerald" 
      subtitle="Statutory BIR submissions"
    />

    <x-stat-card 
      title="Total Tax Paid (YTD)" 
      :value="(float) (($returns ?? collect())->where('status', 'PAID')->sum('tax_due'))" 
      icon="ph-bank" 
      color="blue" 
      subtitle="Remitted to AGDB depository"
    />

    <x-stat-card 
      title="Pending Tax Payable" 
      :value="(float) (($returns ?? collect())->where('status', 'DRAFT')->sum('tax_due'))" 
      icon="ph-clock" 
      color="rose" 
      subtitle="Scheduled BIR liabilities"
    />

    <x-stat-card 
      title="eFPS Portal Connection" 
      value="Connected" 
      :isCurrency="false"
      icon="ph-globe" 
      color="teal" 
      subtitle="BIR Electronic Filing Service"
    />
  </div>

  <!-- Live Statutory Calculation Engine (BIR Schedules from BirTaxScheduleService) -->
  <div class="rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <i class="ph-bold ph-calculator text-indigo-600 dark:text-indigo-400"></i>
          <span>Live Statutory Tax Computations (Q{{ $quarter ?? 1 }} {{ $year ?? date('Y') }})</span>
        </h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Aggregated in real-time from active general ledger journals and purchase vouchers
        </p>
      </div>
      <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-950/40 dark:text-indigo-300">
        <i class="ph-bold ph-cpu"></i>
        <span>BIR Engine Live Aggregation</span>
      </span>
    </div>

    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- 1601-EQ (EWT) -->
      <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-slate-900 dark:text-white text-xs">BIR Form 1601-EQ (EWT)</span>
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-600/20">
              {{ $ewt1601eq['total_forms'] ?? 0 }} Certificates
            </span>
          </div>
          <div class="text-xs text-slate-500 mb-1">
            Total Tax Base: <strong class="font-mono font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($ewt1601eq['total_tax_base'] ?? 0), 2) }}</strong>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
          <span class="text-slate-500">Expanded Withheld:</span>
          <span class="font-mono font-bold text-rose-600 dark:text-rose-400 text-sm">
            ₱{{ number_format((float) ($ewt1601eq['total_withheld'] ?? 0), 2) }}
          </span>
        </div>
      </div>

      <!-- 1601-C (Payroll) -->
      <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-slate-900 dark:text-white text-xs">BIR Form 1601-C (Payroll)</span>
            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 ring-1 ring-blue-600/20">
              {{ $comp1601c['employee_count'] ?? 0 }} Staff
            </span>
          </div>
          <div class="text-xs text-slate-500 mb-1">
            Gross Compensation: <strong class="font-mono font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($comp1601c['gross_compensation'] ?? 0), 2) }}</strong>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
          <span class="text-slate-500">Compensation Withheld:</span>
          <span class="font-mono font-bold text-rose-600 dark:text-rose-400 text-sm">
            ₱{{ number_format((float) ($comp1601c['tax_withheld'] ?? 0), 2) }}
          </span>
        </div>
      </div>

      <!-- 2550Q (VAT) -->
      <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-slate-900 dark:text-white text-xs">BIR Form 2550Q (VAT)</span>
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-600/20">
              {{ $vat2550q['receipts_count'] ?? 0 }} ORs
            </span>
          </div>
          <div class="text-xs text-slate-500 mb-1">
            Vatable: <strong class="font-mono font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($vat2550q['vatable_sales'] ?? 0), 2) }}</strong>
            <span class="text-slate-400 text-[11px] block mt-0.5">Exempt: ₱{{ number_format((float) ($vat2550q['vat_exempt_sales'] ?? 0), 2) }}</span>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
          <span class="text-slate-500">Output VAT (12%):</span>
          <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">
            ₱{{ number_format((float) ($vat2550q['output_vat_12'] ?? 0), 2) }}
          </span>
        </div>
      </div>
    </div>
  </div>

  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <!-- Data Table Card -->
  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-wrap">
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-1.5 text-xs text-slate-500">
            <i class="ph-bold ph-funnel"></i>
            <span>Form Type:</span>
          </div>
          <select 
            id="returnFormSelect"
            x-model="formFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All BIR Form Types</option>
            <option value="2550">BIR Form 2550Q (Quarterly VAT)</option>
            <option value="1601">BIR Form 1601EQ (Withholding Tax)</option>
            <option value="1702">BIR Form 1702 (Corporate Tax)</option>
          </select>

          <div class="flex items-center gap-1.5 text-xs text-slate-500 ml-2">
            <span>Status:</span>
          </div>
          <select 
            id="returnStatusSelect"
            x-model="statusFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All Statuses</option>
            <option value="paid">Filed &amp; Remitted (Paid)</option>
            <option value="filed">Filed (Pending Remittance)</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative w-full sm:w-72">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="ph ph-magnifying-glass text-sm"></i>
            </div>
            <input 
              type="search" 
              id="returnSearchInput"
              x-model="search"
              placeholder="Search return #, form code, period..." 
              class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
            >
          </div>
          <button 
            type="button" 
            x-show="search || formFilter || statusFilter"
            @click="search = ''; formFilter = ''; statusFilter = '';"
            class="rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-300"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table id="taxReturnTable" class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
        <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
          <tr>
            <th class="py-3 px-4 font-mono">Return Number</th>
            <th class="py-3 px-4">BIR Form Type</th>
            <th class="py-3 px-4">Period Covered</th>
            <th class="py-3 px-4 font-mono">Filing Due Date</th>
            <th class="py-3 px-4 text-right font-mono">Tax Payable (₱)</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($returns ?? [] as $ret)
            @php
              $code = is_array($ret) ? $ret['code'] : ($ret->form_type ?? 'N/A');
              $title = is_array($ret) ? $ret['title'] : ($ret->form_type === '2550Q' ? 'Quarterly Value Added Tax Return' : ($ret->form_type === '1601-EQ' ? 'Quarterly Withholding Tax (EWT)' : 'Monthly Compensation Withholding'));
              $period = is_array($ret) ? $ret['period'] : ($ret->period_covered ?? 'N/A');
              $due = is_array($ret) ? $ret['due'] : ($ret->filing_date ? (is_string($ret->filing_date) ? $ret->filing_date : $ret->filing_date->format('Y-m-d')) : 'N/A');
              $rawTax = (float) (is_array($ret) ? 0 : ($ret->tax_due ?? 0));
              $payable = is_array($ret) ? $ret['payable'] : ('₱' . number_format($rawTax, 2));
              $status = is_array($ret) ? $ret['status'] : ($ret->status ?? 'FILED');
              $retNum = is_array($ret) ? $ret['num'] : ($ret->return_number ?? 'TR-' . $ret->id);
              $ref = is_array($ret) ? $ret['ref'] : ($retNum . ' • eFPS');

              $retData = [
                'num' => $retNum,
                'code' => $code,
                'title' => $title,
                'period' => $period,
                'due' => $due,
                'payable' => $payable,
                'status' => $status,
                'ref' => $ref,
              ];
            @endphp
            <tr 
              class="return-row transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/40 cursor-pointer"
              data-form="{{ strtolower($code) }}"
              data-status="{{ strtolower($status) }}"
              @click="openDetails({{ json_encode($retData) }})"
              x-show="(!search || '{{ strtolower($retNum . ' ' . $code . ' ' . $period) }}'.includes(search.toLowerCase())) && (!formFilter || '{{ strtolower($code) }}'.includes(formFilter.toLowerCase())) && (!statusFilter || '{{ strtolower($status) }}'.includes(statusFilter.toLowerCase()))"
            >
              <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                {{ $retNum }}
              </td>
              <td class="py-3.5 px-4">
                <div class="font-semibold text-slate-900 dark:text-white">{{ $code }}</div>
                <div class="text-xs text-slate-400">{{ $title }}</div>
              </td>
              <td class="py-3.5 px-4 text-xs font-mono text-slate-600 dark:text-slate-400">
                {{ $period }}
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-slate-600 dark:text-slate-400">
                {{ $due }}
              </td>
              <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                {{ $payable }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <x-status-badge :status="$status" />
              </td>
              <td class="py-3.5 px-4 text-right" @click.stop>
                <div class="inline-flex items-center gap-1.5">
                  @if($status !== 'PAID')
                    <form action="{{ route('tax.tax-returns.pay', is_array($ret) ? $ret['id'] : $ret->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm statutory tax remittance payment to BIR?');">
                      @csrf
                      <button 
                        type="submit" 
                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 hover:bg-emerald-100 transition-all dark:bg-emerald-950/40 dark:text-emerald-300"
                        title="Mark Remitted &amp; Paid"
                      >
                        <i class="ph-bold ph-check-circle"></i>
                        <span>Remit</span>
                      </button>
                    </form>
                  @endif
                  <button 
                    type="button" 
                    @click="openDetails({{ json_encode($retData) }})"
                    class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-all dark:bg-slate-800 dark:text-slate-300"
                    title="View Return Details"
                  >
                    <i class="ph-bold ph-eye"></i>
                    <span>Inspect</span>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center text-sm text-slate-400">
                <i class="ph ph-receipt text-3xl mb-2 block mx-auto text-slate-300"></i>
                No tax returns filed in database.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <span id="returnSummaryText">Showing {{ count($returns ?? []) }} Statutory Returns</span>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
          <i class="ph-bold ph-shield-check text-indigo-600"></i>
          <span>BIR CAS Form Filing Compliant</span>
        </span>
      </div>
    </div>
  </div>

  <!-- Slide-Over Drawer: Tax Return Details -->
  <template x-teleport="body">
  <div 
    x-show="returnDetailsOpen" 
    x-cloak 
    class="fixed inset-0 z-50 overflow-hidden" 
    role="dialog" 
    aria-modal="true"
  >
    <div 
      x-show="returnDetailsOpen" 
      x-transition.opacity.duration.300ms 
      class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
      @click="returnDetailsOpen = false"
    ></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
      <div 
        x-show="returnDetailsOpen" 
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.outside="returnDetailsOpen = false" 
        class="w-screen max-w-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800 flex flex-col justify-between"
      >
        <!-- Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400" id="detailReturnCode" x-text="selectedReturn.code"></span>
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1" :class="selectedReturn.status === 'PAID' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20'" id="detailReturnStatus" x-text="selectedReturn.status"></span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="detailReturnTitle" x-text="selectedReturn.title"></h3>
          </div>
          <button 
            type="button" 
            @click="returnDetailsOpen = false" 
            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 text-xs">
          <!-- Metrics -->
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-rose-50 p-4 ring-1 ring-rose-200/80 dark:bg-rose-950/40 dark:ring-rose-800/60 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-1">Total Net Tax Payable</span>
              <div class="font-mono text-lg font-bold text-rose-700 dark:text-rose-300" id="detailReturnPayable" x-text="selectedReturn.payable"></div>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Filing Due Date</span>
              <div class="font-mono text-lg font-bold text-slate-900 dark:text-white" id="detailReturnDue" x-text="selectedReturn.due"></div>
            </div>
          </div>

          <!-- Metadata -->
          <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-file-text text-indigo-600"></i>
              <span>Filing Metadata &amp; Period Scope</span>
            </h4>
            
            <div class="space-y-2 pt-1 divide-y divide-slate-200 dark:divide-slate-700">
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Tax Period Covered:</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white" id="detailReturnPeriod" x-text="selectedReturn.period"></span>
              </div>
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">BIR eFPS Reference:</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" id="detailReturnRef" x-text="selectedReturn.ref"></span>
              </div>
            </div>
          </div>

          <!-- Audit Trail & eFPS Transmission -->
          <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-shield-check text-emerald-600"></i>
              <span>Audit Trail &amp; BIR eFPS Transmission</span>
            </h4>

            <div class="space-y-2 pt-1 text-[11px]">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">Bureau of Internal Revenue Status:</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                  <i class="ph-bold ph-check"></i> Transmitted &amp; Electronic Ack Received
                </span>
              </div>
              <div class="flex items-center justify-between text-slate-400 font-mono">
                <span>CAS Audit Stamp:</span>
                <span x-text="'CAS-RET-' + selectedReturn.num + ' • ' + '{{ date('Y-m-d H:i:s') }} PST'"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <button 
            type="button" 
            @click="returnDetailsOpen = false" 
            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
          >
            Close
          </button>
          <button 
            type="button" 
            onclick="alert('Downloading BIR Filing PDF Brief...');"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20"
          >
            <i class="ph-bold ph-file-pdf"></i>
            <span>Download Return PDF</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  </template>

  <!-- Modal: File Statutory Tax Return -->
  <x-modal 
    id="fileReturnModal" 
    title="Record Statutory Tax Return Filing" 
    subtitle="File BIR Form with CAS audit trail and remittance schedule" 
    icon="ph-file-arrow-up" 
    iconVariant="emerald" 
    size="xl" 
    :showFooter="false"
  >
    <form id="fileReturnForm" action="{{ route('tax.tax-returns.store') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            BIR Form Code <span class="text-rose-500">*</span>
          </label>
          <select 
            name="form_type" 
            id="modalReturnForm" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            required
          >
            <option value="2550Q">BIR Form 2550Q (Quarterly VAT Return)</option>
            <option value="1601-EQ">BIR Form 1601-EQ (Quarterly EWT Return)</option>
            <option value="1601-C">BIR Form 1601-C (Monthly Compensation Withholding)</option>
            <option value="1702-EX">BIR Form 1702-EX (Corporate Income Tax)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Tax Period Covered <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            name="period_covered" 
            id="modalReturnPeriod" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            placeholder="e.g. Q3 2026 (Jul - Sep)" 
            value="Q3 2026" 
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Statutory Due / Filing Date <span class="text-rose-500">*</span>
          </label>
          <input 
            type="date" 
            name="filing_date" 
            id="modalReturnDue" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            value="{{ date('Y-m-d') }}" 
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Total Net Tax Payable (₱) <span class="text-rose-500">*</span>
          </label>
          <input 
            type="number" 
            name="tax_due" 
            id="modalReturnPayable" 
            step="0.01" 
            min="0" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-mono text-right text-rose-600 font-bold ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-rose-400 dark:ring-slate-700" 
            placeholder="0.00" 
            value="0.00" 
            required
          >
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
        <button 
          type="button" 
          @click="$dispatch('close-modal', 'fileReturnModal')" 
          class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20"
        >
          <i class="ph-bold ph-check"></i>
          <span>Post Statutory Return</span>
        </button>
      </div>
    </form>
  </x-modal>

</div>
@endsection
