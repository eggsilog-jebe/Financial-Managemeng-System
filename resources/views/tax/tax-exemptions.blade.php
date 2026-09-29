@extends('layouts.app')

@section('title', 'Tax Exemptions & Statutory Relief - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-exemptions')

@section('content')
<div class="space-y-6" x-data="{
  search: '',
  catFilter: '',
  statusFilter: '',
  exemptionDetailsOpen: false,
  selectedExemption: {
    name: '',
    basis: '',
    ref: '',
    cat: '',
    gross: '₱0.00',
    saved: '₱0.00',
    status: 'Enforced'
  },
  openDetails(exemption) {
    this.selectedExemption = exemption;
    this.exemptionDetailsOpen = true;
  }
}">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Tax Exemptions &amp; Special Relief Register
        </h1>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
      <button 
        type="button" 
        onclick="alert('Exporting Exemption Audit Log...')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 shadow-sm transition-all dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700"
      >
        <i class="ph-bold ph-file-arrow-down"></i>
        <span>Exemption Audit PDF</span>
      </button>
      <button 
        type="button" 
        id="btnRegisterExemption"
        @click="$dispatch('open-modal', 'registerExemptionModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-purple-700 ring-1 ring-purple-600/20 transition-all"
      >
        <i class="ph-bold ph-plus-circle"></i>
        <span>Register Exemption Rule</span>
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
    <x-stat-card 
      title="Active Exemption Records" 
      :value="($certificates ?? collect())->count()" 
      :isCurrency="false"
      icon="ph-shield-check" 
      color="purple" 
      subtitle="Statutory legal basis active"
    />

    <x-stat-card 
      title="Total Tax Base Amount" 
      :value="(float) (($certificates ?? collect())->sum(fn($c) => $c->tax_base_amount ?? $c->gross_income ?? 0))" 
      icon="ph-currency-circle-dollar" 
      color="blue" 
      subtitle="Exempted transactions base"
    />

    <x-stat-card 
      title="Total Tax Withheld / Waived" 
      :value="(float) (($certificates ?? collect())->sum('tax_withheld'))" 
      icon="ph-piggy-bank" 
      color="emerald" 
      subtitle="Public relief & tax savings"
    />

    <x-stat-card 
      title="Audit Compliance" 
      value="100% Valid" 
      :isCurrency="false"
      icon="ph-check-square" 
      color="teal" 
      subtitle="BIR verified statutory rules"
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
            <span>Category:</span>
          </div>
          <select 
            id="exemptionCatSelect"
            x-model="catFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All Exemption Categories</option>
            <option value="meds">Essential Medicine (RA 11534)</option>
            <option value="senior">Senior Citizen &amp; PWD (RA 9994)</option>
          </select>

          <div class="flex items-center gap-1.5 text-xs text-slate-500 ml-2">
            <span>Status:</span>
          </div>
          <select 
            id="exemptionStatusSelect"
            x-model="statusFilter"
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="">All Statuses</option>
            <option value="enforced">Active &amp; Enforced</option>
            <option value="review">Under Review</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative w-full sm:w-72">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <i class="ph ph-magnifying-glass text-sm"></i>
            </div>
            <input 
              type="search" 
              id="exemptionSearchInput"
              x-model="search"
              placeholder="Search exemption class, legal basis..." 
              class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
            >
          </div>
          <button 
            type="button" 
            x-show="search || catFilter || statusFilter"
            @click="search = ''; catFilter = ''; statusFilter = '';"
            class="rounded-xl px-2 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-300"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table id="exemptionTable" class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
        <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
          <tr>
            <th class="py-3 px-4">Exemption Class</th>
            <th class="py-3 px-4">Legal Basis / Statutory Authority</th>
            <th class="py-3 px-4 font-mono">Certificate Ref</th>
            <th class="py-3 px-4 text-right font-mono">YTD Exempt Gross (₱)</th>
            <th class="py-3 px-4 text-right font-mono">Tax Saved (₱)</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($exemptions ?? [] as $e)
            @php
              $name = is_array($e) ? $e['name'] : $e->name;
              $basis = is_array($e) ? $e['basis'] : ($e->legal_basis ?? 'N/A');
              $ref = is_array($e) ? $e['ref'] : ($e->reference_number ?? 'N/A');
              $cat = is_array($e) ? ($e['cat'] ?? '') : '';
              $status = is_array($e) ? ($e['status'] ?? 'Enforced') : ($e->status ?? 'Enforced');
              $gross = is_array($e) ? $e['gross'] : ('₱' . number_format($e->exempt_gross, 2));
              $saved = is_array($e) ? $e['saved'] : ('₱' . number_format($e->tax_saved, 2));
              $searchString = strtolower($name . ' ' . $basis . ' ' . $ref);
              $payload = [
                'name' => $name,
                'basis' => $basis,
                'ref' => $ref,
                'cat' => $cat,
                'gross' => $gross,
                'saved' => $saved,
                'status' => $status,
              ];
            @endphp
            <tr 
              class="exemption-row transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/40 cursor-pointer"
              data-cat="{{ $cat }}"
              data-status="{{ strtolower($status) }}"
              @click="openDetails({{ json_encode($payload) }})"
              x-show="(!search || '{{ $searchString }}'.includes(search.toLowerCase())) && (!catFilter || '{{ $cat }}'.includes(catFilter.toLowerCase())) && (!statusFilter || '{{ strtolower($status) }}'.includes(statusFilter.toLowerCase()))"
            >
              <td class="py-3.5 px-4">
                <div class="font-bold text-slate-900 dark:text-white">{{ $name }}</div>
              </td>
              <td class="py-3.5 px-4 text-xs text-slate-500 dark:text-slate-400">
                {{ $basis }}
              </td>
              <td class="py-3.5 px-4 font-mono font-bold text-purple-600 dark:text-purple-400 text-xs">
                {{ $ref }}
              </td>
              <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">
                {{ $gross }}
              </td>
              <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                {{ $saved }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <x-status-badge :status="$status" />
              </td>
              <td class="py-3.5 px-4 text-right" @click.stop>
                <button 
                  type="button" 
                  @click="openDetails({{ json_encode($payload) }})"
                  class="inline-flex items-center gap-1 rounded-lg bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700 ring-1 ring-purple-600/20 hover:bg-purple-100 transition-all dark:bg-purple-950/40 dark:text-purple-300"
                  title="View Exemption Details"
                >
                  <i class="ph-bold ph-eye"></i>
                  <span>Inspect</span>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center text-sm text-slate-400">
                <i class="ph ph-shield-check text-3xl mb-2 block mx-auto text-slate-300"></i>
                No tax exemptions registered in database.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <span id="exemptionSummaryText">Showing {{ count($exemptions ?? []) }} Tax Exemptions</span>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
          <i class="ph-bold ph-shield-check text-purple-600"></i>
          <span>RA 9994 &bull; RA 10754 &bull; RA 11534 Compliant</span>
        </span>
      </div>
    </div>
  </div>

  <!-- Slide-Over Drawer: In-Depth Exemption Details -->
  <div 
    x-show="exemptionDetailsOpen" 
    x-cloak 
    class="fixed inset-0 z-50 overflow-hidden" 
    role="dialog" 
    aria-modal="true"
  >
    <div 
      x-show="exemptionDetailsOpen" 
      x-transition.opacity.duration.300ms 
      class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
    ></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
      <div 
        x-show="exemptionDetailsOpen" 
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.outside="exemptionDetailsOpen = false" 
        class="w-screen max-w-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800 flex flex-col justify-between"
      >
        <!-- Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-mono text-xs font-bold text-purple-600 dark:text-purple-400" id="detailExemptionRef" x-text="selectedExemption.ref"></span>
              <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-600/20" id="detailExemptionStatus" x-text="selectedExemption.status"></span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="detailExemptionName" x-text="selectedExemption.name"></h3>
          </div>
          <button 
            type="button" 
            @click="exemptionDetailsOpen = false" 
            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
          >
            <i class="ph-bold ph-x text-lg"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 text-xs">
          <!-- Metrics -->
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">YTD Exempt Gross</span>
              <div class="font-mono text-lg font-bold text-slate-900 dark:text-white" id="detailExemptionGross" x-text="selectedExemption.gross"></div>
            </div>
            <div class="rounded-2xl bg-emerald-50 p-4 ring-1 ring-emerald-200/80 dark:bg-emerald-950/40 dark:ring-emerald-800/60 text-center">
              <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-1">Total Tax Saved</span>
              <div class="font-mono text-lg font-bold text-emerald-700 dark:text-emerald-300" id="detailExemptionSaved" x-text="selectedExemption.saved"></div>
            </div>
          </div>

          <!-- Legal Basis -->
          <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-scales text-purple-600"></i>
              <span>Legal Basis &amp; Statutory Authority</span>
            </h4>
            
            <div class="space-y-2 pt-1 divide-y divide-slate-200 dark:divide-slate-700">
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Implementing Circular / Basis:</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white" id="detailExemptionBasis" x-text="selectedExemption.basis"></span>
              </div>
              <div class="flex justify-between items-center pt-2">
                <span class="text-slate-500">Scope of Medical Exemption:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200" id="detailExemptionDesc" x-text="'Essential Healthcare Relief • VAT-Exempt'"></span>
              </div>
            </div>
          </div>

          <!-- Audit Stamp -->
          <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/80 dark:bg-slate-800 dark:ring-slate-700 space-y-3">
            <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 uppercase text-[11px] tracking-wider text-slate-500">
              <i class="ph-bold ph-shield-check text-emerald-600"></i>
              <span>Audit Trail &amp; BIR Verification</span>
            </h4>

            <div class="space-y-2 pt-1 text-[11px]">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">Statutory Ruling Status:</span>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">
                  <i class="ph-bold ph-check"></i> Verified by BIR Tax Auditor
                </span>
              </div>
              <div class="flex items-center justify-between text-slate-400 font-mono">
                <span>System Audit Stamp:</span>
                <span x-text="'LOG-EX-2026 • ' + '{{ date('Y-m-d H:i:s') }} PST'"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between">
          <button 
            type="button" 
            @click="exemptionDetailsOpen = false" 
            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
          >
            Close
          </button>
          <button 
            type="button" 
            onclick="alert('Exporting Tax Exemption Ruling Brief...');"
            class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-purple-700 ring-1 ring-purple-600/20"
          >
            <i class="ph-bold ph-file-text"></i>
            <span>Export Exemption Audit</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Register Tax Exemption Rule -->
  <x-modal 
    id="registerExemptionModal" 
    title="Register Statutory Tax Exemption" 
    subtitle="Add exemption authority under NIRC, CREATE Act, or statutory circular" 
    icon="ph-shield-check" 
    iconVariant="purple" 
    size="lg" 
    :showFooter="false"
  >
    <form id="registerExemptionForm" @submit.prevent="
      alert('Statutory exemption rule recorded in CAS masterfile.');
      $dispatch('close-modal', 'registerExemptionModal');
    " class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Exemption Class Title <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            id="modalExemptionName" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            placeholder="e.g. Non-Profit Hospital Income Exemption" 
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Legal Basis / Statutory Law <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            id="modalExemptionBasis" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            placeholder="e.g. NIRC Section 30(E)" 
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            BIR Exemption Certificate Ref <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            id="modalExemptionRef" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-mono text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            placeholder="e.g. BIR-CERT-2026-EX03" 
            required
          >
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            YTD Exempt Gross Base (₱) <span class="text-rose-500">*</span>
          </label>
          <input 
            type="number" 
            id="modalExemptionGross" 
            step="0.01" 
            min="0" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-mono text-right text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-purple-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            value="500000.00" 
            required
          >
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
        <button 
          type="button" 
          @click="$dispatch('close-modal', 'registerExemptionModal')" 
          class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-purple-700 ring-1 ring-purple-600/20"
        >
          <i class="ph-bold ph-check"></i>
          <span>Register Exemption Rule</span>
        </button>
      </div>
    </form>
  </x-modal>

</div>
@endsection
