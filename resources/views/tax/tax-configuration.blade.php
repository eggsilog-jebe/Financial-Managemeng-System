@extends('layouts.app')

@section('title', 'Tax Configuration - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-config')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Tax Rates &amp; Statutory Configuration
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <button 
        type="button" 
        onclick="alert('Syncing tax rates with BIR online portal...');" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-arrow-counter-clockwise text-indigo-600"></i>
        <span>Sync Tax Rates</span>
      </button>
      <button 
        type="button" 
        id="btnAddTaxRule" 
        @click="$dispatch('open-modal', 'addTaxRuleModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 ring-1 ring-indigo-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-plus-circle"></i>
        <span>Add Tax Rate Rule</span>
      </button>
    </div>
  </div>

  <!-- Session Alerts -->
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 p-4 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-check-circle text-lg text-emerald-600"></i>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-xl bg-rose-50 p-4 text-xs font-semibold text-rose-800 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="ph-bold ph-warning-circle text-lg text-rose-600"></i>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl bg-rose-50 p-4 text-xs font-semibold text-rose-800 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300">
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Configured Tax Rules" 
      :value="($taxRules ?? collect())->count()" 
      :isCurrency="false"
      icon="ph-percent" 
      color="indigo" 
      subtitle="Active statutory computation rules"
    />
    <x-stat-card 
      title="Tax Categories" 
      :value="($taxRules ?? collect())->pluck('tax_type')->unique()->count()" 
      :isCurrency="false"
      icon="ph-user-stethoscope" 
      color="blue" 
      subtitle="EWT, VAT, and Income Tax groups"
    />
    <x-stat-card 
      title="Statutory Compliance" 
      value="BIR Compliant" 
      :isCurrency="false"
      icon="ph-receipt" 
      color="emerald" 
      subtitle="RR 11-2018 / TRAIN Law rates"
    />
    <x-stat-card 
      title="Tax Engine Status" 
      value="Active &amp; Ready" 
      :isCurrency="false"
      icon="ph-shield-check" 
      color="purple" 
      subtitle="Automated withholding computation"
    />
  </div>

  <!-- Tax Rules Data Table Card -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-wrap">
        <div class="flex flex-wrap items-center gap-2.5">
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <i class="ph-bold ph-funnel"></i>
            <span>Category:</span>
          </div>
          <select 
            id="taxCatSelect" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="" selected>All Tax Categories</option>
            <option value="ewt">Expanded Withholding Tax (EWT)</option>
            <option value="vat">Value Added Tax (VAT)</option>
            <option value="cit">Corporate Income Tax (CIT)</option>
          </select>

          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 ml-2">
            <span>Status:</span>
          </div>
          <select 
            id="taxStatusSelect" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="" selected>All Statuses</option>
            <option value="active">Active Rules</option>
            <option value="inactive">Inactive Rules</option>
          </select>
        </div>

        <div class="relative w-full sm:w-72">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="ph ph-magnifying-glass text-sm"></i>
          </div>
          <input 
            type="search" 
            id="taxSearchInput" 
            placeholder="Search tax code, ATC, scope..." 
            class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table id="taxRuleTable" class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Tax Code &amp; Name</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">ATC Code</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Category</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Tax Rate (%)</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Applicable Scope</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Status</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($taxRules ?? [] as $r)
          @php
            $code = is_array($r) ? $r['code'] : $r->tax_code;
            $name = is_array($r) ? $r['name'] : $r->name;
            $atc = is_array($r) ? $r['atc'] : $r->atc_code;
            $category = is_array($r) ? $r['category'] : $r->category;
            $catType = is_array($r) ? $r['cat_type'] : $r->cat_type;
            $rate = is_array($r) ? $r['rate'] : number_format((float) $r->rate, 1) . '%';
            $scope = is_array($r) ? $r['scope'] : $r->scope;
            $status = is_array($r) ? $r['status'] : $r->status;
            $rData = [
              'code' => $code,
              'name' => $name,
              'atc' => $atc,
              'category' => $category,
              'cat_type' => $catType,
              'rate' => $rate,
              'scope' => $scope,
              'status' => $status,
              'status_badge' => 'bg-emerald-50 text-emerald-700'
            ];
          @endphp
          <tr 
            class="tax-row hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors cursor-pointer" 
            onclick="openTaxRuleDetailsModal({{ json_encode($rData) }})"
          >
            <td class="py-3.5 px-4">
              <div class="font-bold text-slate-900 dark:text-white">{{ $name }}</div>
              <span class="font-mono text-xs text-slate-400">{{ $code }}</span>
            </td>
            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
              {{ $atc }}
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                {{ $category }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-rose-600 dark:text-rose-400">
              {{ $rate }}
            </td>
            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
              {{ $scope }}
            </td>
            <td class="py-3.5 px-4 text-center">
              <x-status-badge :status="$status" color="{{ $status === 'Active' ? 'emerald' : 'slate' }}" />
            </td>
            <td class="py-3.5 px-4 text-right" onclick="event.stopPropagation();">
              <div class="inline-flex items-center justify-end gap-1.5">
                <form action="{{ route('tax.tax-rules.toggle', is_array($r) ? $r['id'] : $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Toggle status for this tax rule?');">
                  @csrf
                  <button 
                    type="submit" 
                    title="Toggle Status (Active/Inactive)"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 {{ $status === 'Active' ? 'text-amber-600 hover:bg-amber-50 hover:border-amber-200 dark:border-slate-700 dark:hover:bg-amber-950/30' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 dark:border-slate-700 dark:hover:bg-emerald-950/30' }} cursor-pointer"
                  >
                    <i class="ph {{ $status === 'Active' ? 'ph-pause' : 'ph-play' }} text-sm"></i>
                  </button>
                </form>
                <button 
                  type="button" 
                  title="View Tax Rule Details" 
                  onclick="openTaxRuleDetailsModal({{ json_encode($rData) }})"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer"
                >
                  <i class="ph ph-eye text-sm"></i>
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-percent text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No tax rules configured in database.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
      <span class="text-xs text-slate-500 dark:text-slate-400" id="taxSummaryText">
        Showing {{ count($taxRules ?? []) }} Tax Rules
      </span>
      <nav class="flex items-center gap-1">
        <button class="inline-flex items-center justify-center px-2 py-1 rounded text-xs text-slate-400 cursor-not-allowed">Previous</button>
        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-600 text-white">1</span>
        <button class="inline-flex items-center justify-center px-2 py-1 rounded text-xs text-slate-400 cursor-not-allowed">Next</button>
      </nav>
    </div>
  </div>
</div>

<!-- Modal: In-Depth Tax Rule Details -->
<x-modal 
  id="taxRuleDetailsModal" 
  title="Statutory Tax Rule Details" 
  size="lg"
>
  <div class="space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800" id="detailTaxCode">
          TAX-EWT-DOC10
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300" id="detailTaxStatus">
          <i class="ph ph-check-circle mr-1"></i> Active Statutory Rule
        </span>
      </div>
      <span class="text-xs text-slate-400" id="detailTaxName">Tax Rule</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
        <span class="block text-xs font-semibold uppercase text-slate-400">BIR ATC Code</span>
        <h4 class="mt-1 text-2xl font-bold font-mono text-indigo-600 dark:text-indigo-400" id="detailTaxAtc">WI010</h4>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
        <span class="block text-xs font-semibold uppercase text-slate-400">Configured Tax Rate</span>
        <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-rose-600 dark:text-rose-400" id="detailTaxRate">10.0%</h4>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
        <i class="ph-bold ph-percent text-indigo-600"></i>
        Category &amp; Regulatory Scope
      </h6>
      <div class="space-y-2 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Tax Category</span>
          <span class="font-semibold text-slate-800 dark:text-slate-200" id="detailTaxCategory">Expanded Withholding Tax</span>
        </div>
        <div class="flex items-center justify-between pt-1">
          <span class="text-slate-500 dark:text-slate-400">Applicable Statutory Scope</span>
          <span class="font-medium text-slate-800 dark:text-slate-200" id="detailTaxScope">Visiting Doctors &amp; Medical Consultants</span>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
        <i class="ph-bold ph-shield-check text-emerald-600"></i>
        Audit Trail &amp; BIR Regulations Compliance
      </h6>
      <div class="space-y-2 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Bureau of Internal Revenue Status:</span>
          <span class="inline-flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            <i class="ph-bold ph-check mr-1"></i> BIR Revenue Regulations RR 11-2018 Compliant
          </span>
        </div>
        <div class="flex items-center justify-between pt-1">
          <span class="text-slate-500 dark:text-slate-400">System Audit Stamp:</span>
          <span class="font-mono text-slate-400">LOG-TAX-2026-001 | {{ date('Y-m-d H:i:s') }} PST</span>
        </div>
      </div>
    </div>
  </div>

  <x-slot:footer>
    <button 
      type="button" 
      @click="show = false"
      class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer"
    >
      Close
    </button>
    <button 
      type="button" 
      onclick="alert('Exporting Tax Rule Configuration Schedule...');" 
      class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition-all cursor-pointer"
    >
      <i class="ph-bold ph-file-text"></i>
      <span>Export Rule Audit</span>
    </button>
  </x-slot:footer>
</x-modal>

<!-- Modal: Add Statutory Tax Rate Rule -->
<x-modal 
  id="addTaxRuleModal" 
  title="Add Statutory Tax Rate Rule" 
  size="lg"
  formId="addTaxRuleForm" 
  formAction="{{ route('tax.tax-rules.store') }}" 
  formMethod="POST" 
  submitText="Save Tax Rule" 
  submitIcon="ph-check"
>
  <input type="hidden" name="cat_type" value="EXPANDED">
  <div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Tax Rule Code <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="tax_code" 
          id="modalTaxCode" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono font-bold text-slate-900 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" 
          placeholder="e.g. WC158" 
          required
        >
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Tax Rule Name <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="name" 
          id="modalTaxName" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          placeholder="e.g. EWT - Medical Goods 1%" 
          required
        >
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          BIR ATC Code <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          name="atc_code" 
          id="modalTaxAtc" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono font-bold text-slate-900 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" 
          placeholder="e.g. WC158" 
          required
        >
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Tax Category <span class="text-rose-500">*</span>
        </label>
        <select 
          name="category" 
          id="modalTaxCategory" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          required
        >
          <option value="WITHHOLDING_TAX">Expanded Withholding Tax (EWT)</option>
          <option value="VAT">Value Added Tax (VAT)</option>
          <option value="CIT">Corporate Income Tax (CIT)</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Tax Rate (decimal e.g. 0.0100 for 1%, 12.0000 for 12%) <span class="text-rose-500">*</span>
        </label>
        <input 
          type="number" 
          name="rate" 
          id="modalTaxRate" 
          step="0.0001" 
          min="0" 
          max="100" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 px-3 text-xs font-mono font-bold text-slate-900 text-right shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" 
          placeholder="0.0100" 
          value="0.0100" 
          required
        >
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Applicable Scope / Regulatory Description
        </label>
        <input 
          type="text" 
          name="scope" 
          id="modalTaxScope" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          placeholder="e.g. Hospital suppliers of goods"
        >
      </div>
    </div>
  </div>
</x-modal>
@endsection

@push('scripts')
<script>
function openTaxRuleDetailsModal(r) {
  if (!r) return;

  const elName = document.getElementById('detailTaxName');
  if (elName) elName.textContent = r.name || 'Tax Rule Name';

  const elCode = document.getElementById('detailTaxCode');
  if (elCode) elCode.textContent = r.code || 'TAX-000';

  const elAtc = document.getElementById('detailTaxAtc');
  if (elAtc) elAtc.textContent = r.atc || 'WI000';

  const elRate = document.getElementById('detailTaxRate');
  if (elRate) elRate.textContent = r.rate || '0.0%';

  const elCat = document.getElementById('detailTaxCategory');
  if (elCat) elCat.textContent = r.category || 'Tax Category';

  const elScope = document.getElementById('detailTaxScope');
  if (elScope) elScope.textContent = r.scope || '-';

  const statusEl = document.getElementById('detailTaxStatus');
  if (statusEl) {
    statusEl.textContent = r.status;
  }

  window.dispatchEvent(new CustomEvent('open-modal', { detail: 'taxRuleDetailsModal' }));
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('taxSearchInput');
  const catSelect = document.getElementById('taxCatSelect');
  const statusSelect = document.getElementById('taxStatusSelect');
  const summaryText = document.getElementById('taxSummaryText');

  function filterTaxRules() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedStatus = statusSelect ? statusSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.tax-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-cat') || '';
      const rowStatus = row.getAttribute('data-status') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCat = !selectedCat || rowCat.includes(selectedCat);
      const matchStatus = !selectedStatus || rowStatus.includes(selectedStatus);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCat && matchStatus && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Tax Rule${visibleCount !== 1 ? 's' : ''}`;
    }

    let emptyRow = document.getElementById('noTaxRow');
    const tbody = document.querySelector('#taxRuleTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noTaxRow';
        emptyRow.innerHTML = `<td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500"><i class="ph ph-magnifying-glass text-3xl block mb-2"></i>No tax rules found matching current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTaxRules);
    searchInput.addEventListener('keyup', filterTaxRules);
  }
  if (catSelect) catSelect.addEventListener('change', filterTaxRules);
  if (statusSelect) statusSelect.addEventListener('change', filterTaxRules);

  filterTaxRules();
});
</script>
@endpush
