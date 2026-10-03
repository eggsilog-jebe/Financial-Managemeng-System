@extends('layouts.app')

@section('title', 'Withholding Tax Certificates (BIR Form 2307 & 2306) - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'withholding-tax')

@section('content')
<div class="space-y-6" x-data="{
  init() {
    this.$watch('certDetailsOpen', val => {
      document.body.classList.toggle('overflow-hidden', val);
      document.body.classList.toggle('modal-open', val);
    });
  },
  search: '',
  formFilter: '',
  payeeFilter: '',
  certDetailsOpen: false,
  selectedCert: {
    num: '',
    payee: '',
    role: '',
    payee_type: '',
    tin: '',
    atc: '',
    gross: '₱0.00',
    tax: '₱0.00',
    form: 'BIR Form 2307',
    form_type: '2307'
  },
  openDetails(cert) {
    this.selectedCert = cert;
    this.certDetailsOpen = true;
  }
}">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Withholding Tax Certificates (BIR Form 2307 &amp; 2306)
        </h1>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
      <button 
        type="button" 
        onclick="alert('Exporting BIR DAT E-Submission file...')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 shadow-sm transition-all dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700"
      >
        <i class="ph-bold ph-file-arrow-down"></i>
        <span>Export BIR E-Submission</span>
      </button>
      <button 
        type="button" 
        id="btnIssueCert"
        @click="$dispatch('open-modal', 'issueCertModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all"
      >
        <i class="ph-bold ph-plus-circle"></i>
        <span>Issue 2307 Certificate</span>
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
    <x-stat-card 
      title="Form 2307 Issued (Month)" 
      :value="count($certificates ?? [])" 
      :isCurrency="false"
      icon="ph-file-text" 
      color="indigo" 
      subtitle="Generated tax certificates"
    />

    <x-stat-card 
      title="Total Tax Withheld (EWT)" 
      :value="(float) (($certificates ?? collect())->sum('tax_withheld'))" 
      icon="ph-scissors" 
      color="rose" 
      subtitle="Expanded withholding tax"
    />

    <x-stat-card 
      title="Total Gross Income Base" 
      :value="(float) (($certificates ?? collect())->sum(fn($c) => $c->tax_base_amount ?? $c->gross_income ?? 0))" 
      icon="ph-currency-circle-dollar" 
      color="emerald" 
      subtitle="Vatable & EWT base payments"
    />

    <x-stat-card 
      title="Remittance Status" 
      value="Due Aug 10" 
      :isCurrency="false"
      icon="ph-clock" 
      color="amber" 
      subtitle="BIR Form 1601-EQ eFPS Filing"
    />
  </div>

  <!-- Main Table Card -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

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
            id="certFormSelect"
            x-model="formFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All Form Types</option>
            <option value="2307">BIR Form 2307</option>
            <option value="2306">BIR Form 2306</option>
          </select>

          <div class="flex items-center gap-1.5 text-xs text-slate-500 ml-2">
            <span>Payee Category:</span>
          </div>
          <select 
            id="certPayeeTypeSelect"
            x-model="payeeFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All Payee Types</option>
            <option value="doctor">Medical Consultants</option>
            <option value="supplier">Suppliers &amp; Vendors</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative w-full sm:w-72">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="ph ph-magnifying-glass text-sm"></i>
            </div>
            <input 
              type="search" 
              id="certSearchInput"
              x-model="search"
              placeholder="Search cert #, payee, TIN..." 
              class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
            >
          </div>
          <button 
            type="button" 
            x-show="search || formFilter || payeeFilter"
            @click="search = ''; formFilter = ''; payeeFilter = '';"
            class="rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-300"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table id="certTable" class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
        <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
          <tr>
            <th class="py-3 px-4 font-mono">Cert Number</th>
            <th class="py-3 px-4">Payee / Doctor Name</th>
            <th class="py-3 px-4 font-mono">TIN Number</th>
            <th class="py-3 px-4 text-center font-mono">ATC Code</th>
            <th class="py-3 px-4 text-right font-mono">Gross Income (₱)</th>
            <th class="py-3 px-4 text-right font-mono">Tax Withheld (₱)</th>
            <th class="py-3 px-4 text-center">Form Type</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($certificates ?? [] as $c)
            @php
              $num = is_array($c) ? $c['num'] : ($c->certificate_number ?? $c->cert_number ?? 'N/A');
              $payee = is_array($c) ? $c['payee'] : ($c->payee_name ?? 'N/A');
              $role = is_array($c) ? $c['role'] : ($c->payee_role ?? ($c->doctor_id ? 'Doctor' : 'Vendor'));
              $payeeType = is_array($c) ? $c['payee_type'] : ($c->doctor_id ? 'doctor' : 'supplier');
              $tin = is_array($c) ? $c['tin'] : ($c->payee_tin ?? $c->tin ?? 'N/A');
              $atc = is_array($c) ? $c['atc'] : ($c->atc_code ?? 'N/A');
              $rawGross = (float) ($c->tax_base_amount ?? $c->gross_income ?? 0);
              $rawTax = (float) ($c->tax_withheld ?? 0);
              $gross = is_array($c) ? $c['gross'] : ('₱' . number_format($rawGross, 2));
              $tax = is_array($c) ? $c['tax'] : ('₱' . number_format($rawTax, 2));
              $form = is_array($c) ? $c['form'] : ('BIR Form ' . ($c->form_type ?? '2307'));
              $formType = is_array($c) ? $c['form_type'] : ($c->form_type ?? '2307');
              
              $cData = [
                'num' => $num,
                'payee' => $payee,
                'role' => $role,
                'payee_type' => $payeeType,
                'tin' => $tin,
                'atc' => $atc,
                'gross' => $gross,
                'tax' => $tax,
                'form' => $form,
                'form_type' => $formType
              ];
            @endphp
            <tr 
              class="cert-row transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/40 cursor-pointer" 
              data-form="{{ strtolower($formType) }}"
              data-payee="{{ strtolower($payeeType) }}"
              @click="openDetails({{ json_encode($cData) }})"
              x-show="(!search || '{{ strtolower($num . ' ' . $payee . ' ' . $tin . ' ' . $atc) }}'.includes(search.toLowerCase())) && (!formFilter || '{{ strtolower($formType) }}'.includes(formFilter.toLowerCase())) && (!payeeFilter || '{{ strtolower($payeeType) }}' === payeeFilter.toLowerCase())"
            >
              <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                {{ $num }}
              </td>
              <td class="py-3.5 px-4">
                <div class="font-semibold text-slate-900 dark:text-white">{{ $payee }}</div>
                <div class="text-xs text-slate-400">{{ $role }}</div>
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-slate-600 dark:text-slate-400">
                {{ $tin }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-semibold text-slate-700 ring-1 ring-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                  {{ $atc }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">
                ₱{{ number_format($rawGross, 2) }}
              </td>
              <td class="py-3.5 px-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                ₱{{ number_format($rawTax, 2) }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <x-status-badge status="BIR_2307" :label="$form" />
              </td>
              <td class="py-3.5 px-4 text-right" @click.stop>
                <button 
                  type="button" 
                  @click="openDetails({{ json_encode($cData) }})"
                  class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-600/20 hover:bg-indigo-100 transition-all dark:bg-indigo-950/40 dark:text-indigo-300"
                  title="View Certificate Details"
                >
                  <i class="ph-bold ph-eye"></i>
                  <span>Inspect</span>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-12 text-center text-sm text-slate-400">
                <i class="ph ph-receipt text-3xl mb-2 block mx-auto text-slate-300"></i>
                No tax certificates issued in database.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer & Pagination -->
    @if(method_exists($certificates, 'links'))
      <div class="border-t border-slate-200 p-4 dark:border-slate-800">
        {{ $certificates->links() }}
      </div>
    @endif

    <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <span id="certSummaryText">Showing {{ method_exists($certificates, 'total') ? $certificates->total() : count($certificates ?? []) }} Tax Certificates</span>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
          <i class="ph-bold ph-shield-check text-emerald-600"></i>
          <span>BIR CAS Rule 2021 Compliant</span>
        </span>
      </div>
    </div>
  </div>

  <!-- Slide-Over Drawer: In-Depth Certificate Details -->
  <template x-teleport="body">
  <div 
    x-show="certDetailsOpen" 
    x-cloak 
    class="fixed inset-0 z-50 overflow-hidden" 
    role="dialog" 
    aria-modal="true"
  >
    <div 
      x-show="certDetailsOpen" 
      x-transition.opacity.duration.300ms 
      class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
      @click="certDetailsOpen = false"
    ></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
      <div 
        x-show="certDetailsOpen" 
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.outside="certDetailsOpen = false" 
        class="w-screen max-w-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800 flex flex-col justify-between"
      >
        <!-- Drawer Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400" id="detailCertNum" x-text="selectedCert.num"></span>
              <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-600/20" id="detailCertForm" x-text="selectedCert.form"></span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="detailCertPayee" x-text="selectedCert.payee"></h3>
          </div>
          <button 
            type="button" 
            @click="certDetailsOpen = false" 
            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

        <!-- Drawer Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 text-xs">
          <!-- Gross vs Tax Metrics -->
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Gross Income Base</span>
              <div class="font-mono text-lg font-bold text-slate-900 dark:text-white" id="detailCertGross" x-text="selectedCert.gross"></div>
            </div>
            <div class="rounded-2xl bg-rose-50 p-4 ring-1 ring-rose-200/80 dark:bg-rose-950/40 dark:ring-rose-800/60 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-1">Creditable Tax Withheld</span>
              <div class="font-mono text-lg font-bold text-rose-700 dark:text-rose-300" id="detailCertTax" x-text="selectedCert.tax"></div>
            </div>
          </div>

          <!-- Taxpayer & ATC Details -->
          <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-identification-card text-indigo-600"></i>
              <span>Taxpayer &amp; ATC Details</span>
            </h4>
            
            <div class="space-y-2 pt-1 divide-y divide-slate-200 dark:divide-slate-700">
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Taxpayer Identification Number (TIN):</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white" id="detailCertTin" x-text="selectedCert.tin"></span>
              </div>
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Alphanumeric Tax Code (ATC):</span>
                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" id="detailCertAtc" x-text="selectedCert.atc"></span>
              </div>
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Payee Professional Category:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200" id="detailCertRole" x-text="selectedCert.role"></span>
              </div>
            </div>
          </div>

          <!-- Audit Trail & Digital Stamp -->
          <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-shield-check text-emerald-600"></i>
              <span>Audit Trail &amp; BIR Verification</span>
            </h4>

            <div class="space-y-2 pt-1 text-[11px]">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">Electronic Stamp:</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                  <i class="ph-bold ph-check"></i> Certified by Hospital Tax Officer
                </span>
              </div>
              <div class="flex items-center justify-between text-slate-400 font-mono">
                <span>System Verification:</span>
                <span x-text="'CERT-' + selectedCert.num + ' • ' + '{{ date('Y-m-d H:i:s') }} PST'"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Drawer Footer -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <button 
            type="button" 
            @click="certDetailsOpen = false" 
            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
          >
            Close
          </button>
          <button 
            type="button" 
            onclick="alert('Exporting Official BIR Form 2307 PDF...');"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20"
          >
            <i class="ph-bold ph-printer"></i>
            <span>Print 2307 PDF</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  </template>

  <!-- Modal: Issue BIR Form 2307 Withholding Certificate -->
  <x-modal 
    id="issueCertModal" 
    title="Issue BIR Form 2307 Withholding Certificate" 
    subtitle="Creditable withholding tax at source certificate generation" 
    icon="ph-receipt" 
    iconVariant="emerald" 
    size="xl" 
    :showFooter="false"
  >
    <div 
      x-data="{
        payee: '',
        tin: '105-882-991-000',
        atc: 'WI010 (10%)',
        gross: 150000.00,
        get rate() {
          if (this.atc.includes('15%')) return 0.15;
          if (this.atc.includes('10%')) return 0.10;
          if (this.atc.includes('5%')) return 0.05;
          if (this.atc.includes('2%')) return 0.02;
          return 0.01;
        },
        get calculatedTax() {
          return (parseFloat(this.gross) || 0) * this.rate;
        }
      }"
    >
      <form id="issueCertForm" @submit.prevent="
        alert('BIR Form 2307 Certificate successfully issued and recorded in CAS audit trail.');
        $dispatch('close-modal', 'issueCertModal');
      " class="space-y-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Payee Legal Name (Doctor / Supplier) <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              id="modalCertPayee" 
              x-model="payee"
              class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
              placeholder="e.g. Dr. Alejandro Santos" 
              required
            >
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Taxpayer Identification Number (TIN) <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              id="modalCertTin" 
              x-model="tin"
              class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-mono text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
              placeholder="000-000-000-000" 
              required
            >
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Alphanumeric Tax Code (ATC) <span class="text-rose-500">*</span>
            </label>
            <select 
              id="modalCertAtc" 
              x-model="atc"
              class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
              required
            >
              <option value="WI010 (10%)">WI010 - Medical Professional Fees (10%)</option>
              <option value="WI011 (15%)">WI011 - Medical Professional Fees (15%)</option>
              <option value="WC158 (1%)">WC158 - Purchase of Medical Goods (1%)</option>
              <option value="WI160 (2%)">WI160 - Purchase of Hospital Services (2%)</option>
              <option value="WC100 (5%)">WC100 - Subcontractor Clinical Services (5%)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Gross Income Payment (₱) <span class="text-rose-500">*</span>
            </label>
            <input 
              type="number" 
              id="modalCertGross" 
              x-model.number="gross"
              step="0.01" 
              min="0" 
              class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-mono text-right text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
              required
            >
          </div>
        </div>

        <!-- Live Calculation Box -->
        <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-slate-800/60 dark:ring-slate-700 flex items-center justify-between">
          <div>
            <span class="text-xs text-slate-500 block">Computed Creditable EWT Amount:</span>
            <span class="text-[11px] text-slate-400 font-mono" x-text="'Rate: ' + (rate * 100) + '% applied on ₱' + (parseFloat(gross) || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})"></span>
          </div>
          <div class="text-right">
            <span class="font-mono text-lg font-bold text-rose-600 dark:text-rose-400" x-text="'₱' + calculatedTax.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
          <button 
            type="button" 
            @click="$dispatch('close-modal', 'issueCertModal')" 
            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20"
          >
            <i class="ph-bold ph-printer"></i>
            <span>Generate &amp; Sign 2307 PDF</span>
          </button>
        </div>
      </form>
    </div>
  </x-modal>

</div>
@endsection
