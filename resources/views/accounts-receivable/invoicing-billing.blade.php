@extends('layouts.app')

@section('title', 'Invoicing & Patient Billing - Accounts Receivable | FMS')
@section('module', 'ar')
@section('page', 'invoicing')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Patient Billing &amp; Invoicing Hub
        </h1>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap">
      <button 
        type="button" 
        @click="$dispatch('open-modal', 'createInvoiceModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all"
      >
        <i class="ph-bold ph-plus-circle"></i>
        <span>New Patient Invoice</span>
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

  <!-- Executive Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
    <x-stat-card 
      title="Total Billed Encounters" 
      :value="$totalBilled ?? 0" 
      icon="ph-receipt" 
      color="slate" 
      subtitle="Cumulative clinical gross billings"
    />

    <x-stat-card 
      title="Pending Patient Copay (AR)" 
      :value="$totalPending ?? 0" 
      icon="ph-hand-coins" 
      color="rose" 
      subtitle="Out-of-pocket balances due at cashier"
      badge="Open"
    />

    <x-stat-card 
      title="Settled / Paid Invoices" 
      :value="$totalPaid ?? 0" 
      icon="ph-check-circle" 
      color="emerald" 
      subtitle="Fully discharged &amp; reconciled accounts"
      badge="Settled"
    />
  </div>

  <!-- Table Card -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <form method="GET" action="{{ route('ar.invoices.index') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2.5 flex-wrap">
          <div class="flex items-center gap-1.5 text-xs text-slate-500">
            <i class="ph-bold ph-funnel"></i>
            <span>Status:</span>
          </div>
          <select 
            name="status" 
            onchange="this.form.submit()" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Unpaid / Open Balance</option>
            <option value="PARTIAL" {{ request('status') === 'PARTIAL' ? 'selected' : '' }}>Partial Settlement</option>
            <option value="SETTLED" {{ request('status') === 'SETTLED' ? 'selected' : '' }}>Settled</option>
            <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Paid</option>
          </select>
        </div>

        <div class="relative w-full sm:w-72">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="ph ph-magnifying-glass text-sm"></i>
          </div>
          <input 
            type="search" 
            name="search" 
            value="{{ request('search') }}" 
            placeholder="Search invoice #, MRN, patient..." 
            class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
        </div>
      </form>
    </div>

    <!-- Responsive Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
        <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
          <tr>
            <th scope="col" class="py-3.5 pl-5 pr-3">Invoice #</th>
            <th scope="col" class="px-3 py-3.5">Patient / Guarantor</th>
            <th scope="col" class="px-3 py-3.5">Department</th>
            <th scope="col" class="px-3 py-3.5">Date</th>
            <th scope="col" class="px-3 py-3.5 text-right font-mono">Gross Total</th>
            <th scope="col" class="px-3 py-3.5 text-right font-mono">Coverage/Deductions</th>
            <th scope="col" class="px-3 py-3.5 text-right font-mono">Patient Payable</th>
            <th scope="col" class="px-3 py-3.5 text-center">Status</th>
            <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          @forelse($invoices as $inv)
            @php
              $gross = (float) $inv->total_amount;
              $coverage = (float) ($inv->insurance_covered ?? (($inv->philhealth_benefit ?? 0) + ($inv->hmo_coverage ?? 0) + ($inv->statutory_discount ?? 0)));
              $payable = (float) $inv->patient_payable;
              $effectiveDiscount = strtoupper((string) ($inv->patientAccount?->effective_discount_category ?? $inv->patientAccount?->discount_category ?? 'NONE'));
            @endphp
            <tr class="transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
              <td class="py-3.5 pl-5 pr-3 font-mono font-bold text-slate-900 dark:text-white text-xs sm:text-sm">
                {{ $inv->invoice_number }}
              </td>
              <td class="px-3 py-3.5">
                <div class="font-semibold text-slate-900 dark:text-white text-xs sm:text-sm flex items-center gap-1.5 flex-wrap">
                  <span>{{ $inv->patientAccount?->full_name ?? 'Walk-In Patient' }}</span>
                  @if($effectiveDiscount === 'PWD')
                    <span class="inline-flex items-center gap-1 rounded bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-700 ring-1 ring-teal-600/20" data-statutory="{{ json_encode(['statutory_category' => 'PWD']) }}">
                      <i class="ph-bold ph-wheelchair"></i> PWD 20%
                    </span>
                  @elseif($effectiveDiscount === 'SENIOR_CITIZEN' || $effectiveDiscount === 'SENIOR')
                    <span class="inline-flex items-center gap-1 rounded bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 ring-1 ring-purple-600/20" data-statutory="{{ json_encode(['statutory_category' => 'SENIOR_CITIZEN']) }}">
                      <i class="ph-bold ph-identification-card"></i> Senior 20%
                    </span>
                  @endif
                </div>
                <div class="text-[11px] font-mono text-slate-500">MRN: {{ $inv->patientAccount?->patient_id_number ?: 'N/A' }}</div>
              </td>
              <td class="px-3 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                {{ $inv->department ?? 'Clinical Services' }}
              </td>
              <td class="px-3 py-3.5 text-xs text-slate-600 dark:text-slate-400 font-mono">
                {{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}
              </td>
              <td class="px-3 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                ₱{{ number_format($gross, 2) }}
              </td>
              <td class="px-3 py-3.5 text-right font-mono text-emerald-600 dark:text-emerald-400">
                -₱{{ number_format($coverage, 2) }}
              </td>
              <td class="px-3 py-3.5 text-right font-mono font-bold tabular-nums text-slate-900 dark:text-white">
                ₱{{ number_format($payable, 2) }}
              </td>
              <td class="px-3 py-3.5 text-center">
                <x-status-badge :status="$inv->status" />
              </td>
              <td class="py-3.5 pl-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <a 
                    href="{{ route('ar.invoices.print', $inv->id) }}" 
                    target="_blank"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white transition-colors"
                    title="Print Billing Invoice"
                  >
                    <i class="ph-bold ph-printer text-sm"></i>
                  </a>
                  @if($payable > 0 && $inv->status !== 'SETTLED' && $inv->status !== 'PAID')
                    <a 
                      href="{{ route('collection.cashier-desk', ['search' => $inv->invoice_number]) }}" 
                      class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all"
                      title="Collect Payment at Cashier Desk"
                    >
                      <i class="ph-bold ph-hand-coins"></i>
                      <span>Collect</span>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="py-12 text-center text-sm text-slate-400">
                <i class="ph ph-receipt text-3xl mb-2 block mx-auto text-slate-300 dark:text-slate-600"></i>
                No patient billing records found matching your query.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="border-t border-slate-200 p-4 dark:border-slate-800">
      {{ $invoices->links() }}
    </div>
  </div>
</div>

<!-- Modal: New Patient Invoice (Alpine.js Waterfall Calculator) -->
<x-modal 
  id="createInvoiceModal" 
  title="Generate Clinical Billing Invoice" 
  subtitle="Calculate statutory deductions, PhilHealth case rates &amp; generate GL receivables" 
  icon="ph-receipt" 
  iconVariant="emerald" 
  size="3xl" 
  formAction="{{ route('ar.invoices.store') }}" 
  formMethod="POST" 
  formId="newInvoiceForm"
  submitText="Post Patient Invoice"
  submitIcon="ph-check"
>
  <div 
    x-data="{
      items: [
        { item_code: 'BED-01', description: 'Room & Board (Inpatient Regular)', quantity: 1, unit_price: 2500.00, is_vatable: false, is_senior_pwd_eligible: true },
        { item_code: 'LAB-01', description: 'Complete Blood Count (CBC) with Platelet', quantity: 1, unit_price: 650.00, is_vatable: false, is_senior_pwd_eligible: true }
      ],
      discountType: 'NONE',
      philhealthBenefit: 0.00,
      hmoCoverage: 0.00,
      addItem() {
        this.items.push({ item_code: '', description: '', quantity: 1, unit_price: 0.00, is_vatable: false, is_senior_pwd_eligible: true });
      },
      removeItem(index) {
        if (this.items.length > 1) {
          this.items.splice(index, 1);
        }
      },
      get grossTotal() {
        return this.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0)), 0);
      },
      get statutoryDiscount() {
        if (this.discountType === 'SENIOR_CITIZEN' || this.discountType === 'PWD') {
          return this.grossTotal * 0.20;
        }
        return 0.00;
      },
      get netPayable() {
        const net = this.grossTotal - this.statutoryDiscount - (parseFloat(this.philhealthBenefit) || 0) - (parseFloat(this.hmoCoverage) || 0);
        return Math.max(0, net);
      }
    }" 
    class="space-y-4"
  >
    <!-- Header Details -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200 dark:ring-slate-700">
      <div class="sm:col-span-6">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Select Patient Account <span class="text-rose-500">*</span></label>
        <select name="patient_account_id" required class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
          <option value="">-- Choose Patient --</option>
          @foreach($patients as $p)
            <option value="{{ $p->id }}">{{ $p->full_name }} (MRN: {{ $p->patient_id_number }})</option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Invoice Date <span class="text-rose-500">*</span></label>
        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
      </div>

      <div class="sm:col-span-3">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Due Date</label>
        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
      </div>
    </div>

    <!-- Line Items Repeater -->
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200 dark:ring-slate-700 space-y-3">
      <div class="flex items-center justify-between border-b border-slate-200 pb-2 dark:border-slate-700">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
          <i class="ph-bold ph-list-numbers text-emerald-600"></i>
          <span>Clinical Charge Items</span>
        </h4>
        <button 
          type="button" 
          @click="addItem()" 
          class="inline-flex items-center gap-1 rounded-lg bg-slate-200/80 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200"
        >
          <i class="ph-bold ph-plus"></i> Add Item
        </button>
      </div>

      <template x-for="(item, index) in items" :key="index">
        <div class="grid grid-cols-12 gap-2.5 items-center p-2 rounded-lg bg-white dark:bg-slate-900 ring-1 ring-slate-200 dark:ring-slate-700">
          <div class="col-span-3">
            <input type="text" :name="'items[' + index + '][item_code]'" x-model="item.item_code" placeholder="Code (e.g. BED-01)" required class="w-full rounded-lg border-0 bg-slate-50 py-1.5 px-2 text-xs font-mono text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
          </div>
          <div class="col-span-4">
            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="Description of service/drug" required class="w-full rounded-lg border-0 bg-slate-50 py-1.5 px-2 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
          </div>
          <div class="col-span-2">
            <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" placeholder="Qty" required class="w-full rounded-lg border-0 bg-slate-50 py-1.5 px-2 text-xs text-right font-mono text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
          </div>
          <div class="col-span-2">
            <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" placeholder="Price" required class="w-full rounded-lg border-0 bg-slate-50 py-1.5 px-2 text-xs text-right font-mono text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
          </div>
          <div class="col-span-1 text-center">
            <button type="button" @click="removeItem(index)" class="p-1 text-rose-500 hover:text-rose-700">
              <i class="ph-bold ph-trash text-sm"></i>
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- Deductions Waterfall Panel -->
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 ring-1 ring-slate-200 dark:ring-slate-700">
      <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-1.5">
        <i class="ph-bold ph-scales text-purple-600"></i>
        <span>Third-Party Deductions &amp; Statutory Matrix</span>
      </h4>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Statutory Exemption</label>
          <select name="discount_type" x-model="discountType" class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
            <option value="NONE">None (Regular)</option>
            <option value="SENIOR_CITIZEN">Senior Citizen (RA 9994 20%)</option>
            <option value="PWD">PWD (RA 10754 20%)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">PhilHealth Case Rate (₱)</label>
          <input type="number" step="0.01" name="philhealth_primary_case_rate_amount" x-model.number="philhealthBenefit" placeholder="0.00" class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs font-mono text-right text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">HMO Approved Limit (₱)</label>
          <input type="number" step="0.01" name="hmo_approved_limit" x-model.number="hmoCoverage" placeholder="0.00" class="w-full rounded-xl border-0 bg-white py-2 px-3 text-xs font-mono text-right text-slate-900 ring-1 ring-inset ring-slate-300 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
        </div>
      </div>

      <!-- Real-Time Waterfall Calculation Summary Bar -->
      <div class="mt-4 p-3.5 rounded-xl bg-white dark:bg-slate-900 ring-1 ring-slate-200 dark:ring-slate-700 font-mono text-xs space-y-1.5">
        <div class="flex justify-between text-slate-600 dark:text-slate-400">
          <span>Gross Clinical Total:</span>
          <span>₱<span x-text="grossTotal.toFixed(2)"></span></span>
        </div>
        <div class="flex justify-between text-purple-600 dark:text-purple-400" x-show="statutoryDiscount > 0">
          <span>Statutory 20% Discount (RA 9994/10754):</span>
          <span>-₱<span x-text="statutoryDiscount.toFixed(2)"></span></span>
        </div>
        <div class="flex justify-between text-blue-600 dark:text-blue-400" x-show="philhealthBenefit > 0">
          <span>PhilHealth Case Rate Deduction:</span>
          <span>-₱<span x-text="(parseFloat(philhealthBenefit) || 0).toFixed(2)"></span></span>
        </div>
        <div class="flex justify-between text-sky-600 dark:text-sky-400" x-show="hmoCoverage > 0">
          <span>Private HMO Guarantee Deduction:</span>
          <span>-₱<span x-text="(parseFloat(hmoCoverage) || 0).toFixed(2)"></span></span>
        </div>
        <div class="border-t border-dashed border-slate-200 dark:border-slate-700 pt-2 flex justify-between font-bold text-sm text-slate-900 dark:text-white">
          <span>Net Patient Out-Of-Pocket Due:</span>
          <span class="text-emerald-600 dark:text-emerald-400">₱<span x-text="netPayable.toFixed(2)"></span></span>
        </div>
      </div>
    </div>
  </div>
</x-modal>
@endsection
