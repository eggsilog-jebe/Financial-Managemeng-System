---
name: hims-fms-ui
description: >-
  Use this skill whenever designing, building, or refactoring Blade views, UI components,
  Tailwind CSS v4 layouts, financial data tables, KPI stat cards, Alpine.js modals,
  Chart.js dashboards, or Philippine healthcare (HIMS) and financial management (FMS) interfaces.
---

# Hospital Financial Management System (FMS) — Tailwind CSS v4 UI Engineering Skill

This skill governs all frontend architecture, user experience (UX), and component development for the integrated **Hospital Information Management System (HIMS)** and **Financial Management System (FMS)**.

The frontend stack leverages **Tailwind CSS v4**, **Laravel Blade**, **Alpine.js**, and **Phosphor Icons (`@phosphor-icons/web`)**.

---

## 1. Core Design Philosophy & Visual Standards

### A. Executive Fintech & Healthcare Aesthetic
- **Trust & Precision:** Crisp typography, subtle borders, high-density data tables, and distinct status color coding.
- **Surface Elevation Hierarchy:**
  - Base Background: `bg-slate-50 dark:bg-slate-950`
  - Card / Panel Surface: `bg-white dark:bg-slate-900 ring-1 ring-slate-200/80 dark:ring-slate-800 rounded-2xl shadow-sm`
  - Hover States: `hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors`
- **Monetary & Tabular Integrity:**
  - **Rule:** Every monetary figure, account code, voucher number, and balance **MUST** use `font-mono tabular-nums text-right`.
  - Always prefix Philippine Peso currency amounts with `₱` and format to two decimals:
    ```blade
    ₱{{ number_format((float) $amount, 2) }}
    ```
  - **Zero Raw Math in Blade:** Compute balances, statutory deductions, and tax in Services/DTOs before rendering. Blade templates must only format and display.

---

## 2. Color System & Domain Status Tokens

In Tailwind CSS v4, use the semantic color hierarchy configured in `resources/css/app.css` and the standard Tailwind palette:

### Healthcare Emerald & Slate Neutral Theme
- **Primary Action (Hospital Emerald):** `bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm ring-1 ring-emerald-600/20`
- **Secondary / Ghost Surface:** `bg-white hover:bg-slate-50 text-slate-700 ring-1 ring-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700`
- **Deep Navy Accents:** `text-slate-900 dark:text-white`
- **Muted Helper Text:** `text-slate-500 dark:text-slate-400`

### Financial & Healthcare Status Badges

| Domain / Status | Tailwind CSS v4 Badge Pattern | Example Workflow |
| :--- | :--- | :--- |
| **Settled / Cleared / Posted** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-500/30` | Posted Journal Entries, Settled Invoices, Cleared Checks |
| **Pending / In Review / Draft** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-500/30` | Draft Payment Vouchers, Open Patient Balances, 30-Day Aging |
| **Overdue / Reversed / Voided** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 dark:ring-rose-500/30` | Voided Official Receipts, Overdue 90d+ AR, Reversal Journals |
| **PhilHealth (ACR)** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-500/30` | Primary & Secondary Case Rates (PhilHealth Deductions) |
| **Senior Citizen / PWD** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 ring-1 ring-purple-600/20 dark:bg-purple-950/40 dark:text-purple-300 dark:ring-purple-500/30` | RA 9994 / RA 10754 (20% Discount + 12% VAT Exemption) |
| **Malasakit Center (MAIP/PCSO)** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 ring-1 ring-teal-600/20 dark:bg-teal-950/40 dark:text-teal-300 dark:ring-teal-500/30` | RA 11463 Guarantee Letters (PCSO, DSWD, MAIP, LGU Grants) |
| **Private HMO / Insurance** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-600/20 dark:bg-sky-950/40 dark:text-sky-300 dark:ring-sky-500/30` | Maxicare, Intellicare, Medicard LOA Limits & Guarantees |
| **BIR Form 2307 / Tax** | `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-950/40 dark:text-indigo-300 dark:ring-indigo-500/30` | 10% / 15% Doctor PF Withholding Tax, Vendor EWT Certificates |

---

## 3. Production Component Blueprints

### Blueprint 1: Executive KPI Stat Card
Use in Dashboards, AP/AR Overviews, and Cashier Summary Headers.

```blade
<div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 transition-all hover:shadow-md dark:bg-slate-900 dark:ring-slate-800">
  <div class="flex items-center justify-between">
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Patient Receivables</span>
    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20 dark:bg-emerald-950/50 dark:text-emerald-400">
      <i class="ph-bold ph-receipt text-lg"></i>
    </span>
  </div>
  <div class="mt-4 flex items-baseline justify-between">
    <div class="font-mono text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
      ₱{{ number_format((float) ($totalReceivable ?? 0), 2) }}
    </div>
    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
      <i class="ph-bold ph-trend-up"></i> +4.2%
    </span>
  </div>
  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Active open inpatient & OPD bills</p>
</div>
```

---

### Blueprint 2: High-Density Financial Data Table
Use for Journal Entries, Invoices, Vendor Bills, and Ledger Books.

```blade
<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
  <!-- Table Header Actions & Search -->
  <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
    <div class="relative w-full max-w-sm">
      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
        <i class="ph ph-magnifying-glass text-base"></i>
      </div>
      <input 
        type="search" 
        name="search" 
        value="{{ request('search') }}" 
        placeholder="Search invoice, patient, MRN..." 
        class="w-full rounded-xl border-0 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
      >
    </div>
    <div class="flex items-center gap-2">
      <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
        <i class="ph-bold ph-funnel text-sm"></i> Filter
      </button>
      <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
        <i class="ph-bold ph-download-simple text-sm"></i> Export CSV
      </button>
      <button type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
        <i class="ph-bold ph-plus text-sm"></i> New Record
      </button>
    </div>
  </div>

  <!-- Responsive Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
      <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
        <tr>
          <th scope="col" class="py-3.5 pl-5 pr-3">Ref / Invoice #</th>
          <th scope="col" class="px-3 py-3.5">Patient / Guarantor</th>
          <th scope="col" class="px-3 py-3.5">Department</th>
          <th scope="col" class="px-3 py-3.5">Date</th>
          <th scope="col" class="px-3 py-3.5 text-right font-mono">Gross Total</th>
          <th scope="col" class="px-3 py-3.5 text-right font-mono">Coverage/Deductions</th>
          <th scope="col" class="px-3 py-3.5 text-right font-mono">Balance Due</th>
          <th scope="col" class="px-3 py-3.5 text-center">Status</th>
          <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($invoices as $invoice)
          <tr class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
            <td class="py-4 pl-5 pr-3 font-mono font-semibold text-slate-900 dark:text-white">
              {{ $invoice->invoice_number }}
            </td>
            <td class="px-3 py-4">
              <div class="font-medium text-slate-900 dark:text-white">{{ $invoice->patientAccount->full_name ?? 'N/A' }}</div>
              <div class="font-mono text-xs text-slate-500">MRN: {{ $invoice->patientAccount->patient_mrn ?? 'N/A' }}</div>
            </td>
            <td class="px-3 py-4 text-xs font-medium text-slate-600 dark:text-slate-400">
              {{ $invoice->department ?? 'General' }}
            </td>
            <td class="px-3 py-4 text-xs text-slate-600 dark:text-slate-400">
              {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}
            </td>
            <td class="px-3 py-4 text-right font-mono text-slate-700 dark:text-slate-300">
              ₱{{ number_format((float) $invoice->total_amount, 2) }}
            </td>
            <td class="px-3 py-4 text-right font-mono text-emerald-600 dark:text-emerald-400">
              -₱{{ number_format((float) $invoice->insurance_covered, 2) }}
            </td>
            <td class="px-3 py-4 text-right font-mono font-bold text-slate-900 dark:text-white">
              ₱{{ number_format((float) $invoice->patient_payable, 2) }}
            </td>
            <td class="px-3 py-4 text-center">
              @if($invoice->status === 'SETTLED')
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">
                  Settled
                </span>
              @else
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300">
                  Open Balance
                </span>
              @endif
            </td>
            <td class="py-4 pl-3 pr-5 text-right">
              <a href="{{ route('ar.invoices.show', $invoice->id) }}" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <i class="ph-bold ph-eye"></i> View
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
              <i class="ph ph-receipt text-3xl text-slate-400 mb-2 block"></i>
              No billing records found matching your criteria.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
```

---

### Blueprint 3: Interactive Double-Entry Journal Entry Voucher (Alpine.js)
Guarantees double-entry validation on the client before form submission (`sum(debit) === sum(credit)`).

```blade
<div x-data="{
  lines: [
    { account_id: '', debit: '0.00', credit: '0.00', memo: '' },
    { account_id: '', debit: '0.00', credit: '0.00', memo: '' }
  ],
  addLine() {
    this.lines.push({ account_id: '', debit: '0.00', credit: '0.00', memo: '' });
  },
  removeLine(index) {
    if (this.lines.length > 2) {
      this.lines.splice(index, 1);
    }
  },
  get totalDebit() {
    return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
  },
  get totalCredit() {
    return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
  },
  get isBalanced() {
    return Math.abs(this.totalDebit - this.totalCredit) < 0.0001 && this.totalDebit > 0;
  },
  get difference() {
    return Math.abs(this.totalDebit - this.totalCredit);
  }
}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">

  <div class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800">
    <div>
      <h2 class="text-base font-bold text-slate-900 dark:text-white">Manual Journal Voucher Lines</h2>
      <p class="text-xs text-slate-500">Every journal entry must strictly balance (Debit = Credit).</p>
    </div>
    <button @click="addLine()" type="button" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">
      <i class="ph-bold ph-plus"></i> Add Line
    </button>
  </div>

  <div class="mt-4 space-y-3">
    <template x-for="(line, index) in lines" :key="index">
      <div class="grid grid-cols-12 gap-3 items-center rounded-xl bg-slate-50/60 p-3 ring-1 ring-slate-200/50 dark:bg-slate-800/40 dark:ring-slate-700/50">
        <!-- Account Select -->
        <div class="col-span-12 sm:col-span-5">
          <label class="block text-xs font-medium text-slate-500 mb-1">General Ledger Account</label>
          <select x-model="line.account_id" :name="'lines[' + index + '][account_id]'" required class="w-full rounded-lg border-0 bg-white py-1.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
            <option value="">Select Account...</option>
            @foreach($accounts as $acc)
              <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Line Memo -->
        <div class="col-span-12 sm:col-span-3">
          <label class="block text-xs font-medium text-slate-500 mb-1">Line Memo / Description</label>
          <input type="text" x-model="line.memo" :name="'lines[' + index + '][memo]'" placeholder="Optional description..." class="w-full rounded-lg border-0 bg-white py-1.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
        </div>

        <!-- Debit Input -->
        <div class="col-span-6 sm:col-span-2">
          <label class="block text-xs font-medium text-slate-500 mb-1 text-right">Debit (₱)</label>
          <input type="number" step="0.01" min="0" x-model="line.debit" :name="'lines[' + index + '][debit]'" @focus="if(line.debit === '0.00') line.debit = ''" @blur="if(!line.debit) line.debit = '0.00'" class="w-full rounded-lg border-0 bg-white py-1.5 text-right font-mono text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
        </div>

        <!-- Credit Input -->
        <div class="col-span-5 sm:col-span-2">
          <label class="block text-xs font-medium text-slate-500 mb-1 text-right">Credit (₱)</label>
          <input type="number" step="0.01" min="0" x-model="line.credit" :name="'lines[' + index + '][credit]'" @focus="if(line.credit === '0.00') line.credit = ''" @blur="if(!line.credit) line.credit = '0.00'" class="w-full rounded-lg border-0 bg-white py-1.5 text-right font-mono text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-600">
        </div>
      </div>
    </template>
  </div>

  <!-- Total & Invariance Verification Bar -->
  <div class="mt-6 flex flex-col sm:flex-row items-center justify-between rounded-xl p-4 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
    <div class="flex items-center gap-3">
      <template x-if="isBalanced">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
          <i class="ph-bold ph-check-circle text-base"></i> Balanced Entry
        </span>
      </template>
      <template x-if="!isBalanced">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
          <i class="ph-bold ph-warning-circle text-base"></i> Out of Balance by ₱<span x-text="difference.toFixed(2)"></span>
        </span>
      </template>
    </div>

    <div class="flex items-center gap-6 mt-3 sm:mt-0 font-mono text-sm">
      <div>
        <span class="text-xs text-slate-500 mr-2">Total Debits:</span>
        <span class="font-bold text-slate-900 dark:text-white">₱<span x-text="totalDebit.toFixed(2)"></span></span>
      </div>
      <div>
        <span class="text-xs text-slate-500 mr-2">Total Credits:</span>
        <span class="font-bold text-slate-900 dark:text-white">₱<span x-text="totalCredit.toFixed(2)"></span></span>
      </div>
    </div>
  </div>

  <!-- Submit Action (Disabled if unbalanced) -->
  <div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('accounting.general-ledger.index') }}" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
      Cancel
    </a>
    <button 
      type="submit" 
      :disabled="!isBalanced" 
      class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <i class="ph-bold ph-shield-check text-base"></i> Post Journal Entry
    </button>
  </div>
</div>
```

---

### Blueprint 4: Cashier POS & Collection Desk Interface
For Inpatient/OPD settlement, Official Receipts (OR) with CAS compliant breakdowns.

```blade
<div class="grid grid-cols-1 gap-6 lg:grid-cols-12" x-data="{
  tenderAmount: 0.00,
  patientPayable: {{ (float) ($patientBill->patient_payable ?? 0) }},
  get changeDue() {
    return Math.max(0, this.tenderAmount - this.patientPayable);
  }
}">
  <!-- Left Column: Patient Encounter Summary -->
  <div class="lg:col-span-7 space-y-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
        <div>
          <h3 class="font-bold text-slate-900 dark:text-white">Patient Encounter #{{ $encounter->encounter_number }}</h3>
          <p class="text-xs text-slate-500">Admitted: {{ $encounter->admission_date }} • Room {{ $encounter->room_number }}</p>
        </div>
        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-600/20">
          {{ $encounter->encounter_type }}
        </span>
      </div>

      <!-- Philippine Healthcare Statutory Deductions Matrix -->
      <div class="mt-4 space-y-2 text-sm font-mono">
        <div class="flex justify-between text-slate-600 dark:text-slate-400">
          <span>Gross Hospital Bill</span>
          <span>₱{{ number_format((float) $patientBill->gross_amount, 2) }}</span>
        </div>
        <div class="flex justify-between text-purple-600 dark:text-purple-400">
          <span>Senior Citizen (RA 9994) / PWD (RA 10754) 20% + VAT Relief</span>
          <span>-₱{{ number_format((float) $patientBill->statutory_discount, 2) }}</span>
        </div>
        <div class="flex justify-between text-blue-600 dark:text-blue-400">
          <span>PhilHealth All-Case-Rate (ACR) Claim</span>
          <span>-₱{{ number_format((float) $patientBill->philhealth_benefit, 2) }}</span>
        </div>
        <div class="flex justify-between text-sky-600 dark:text-sky-400">
          <span>Private HMO LOA Guarantee (Maxicare/Intellicare)</span>
          <span>-₱{{ number_format((float) $patientBill->hmo_coverage, 2) }}</span>
        </div>
        <div class="flex justify-between text-teal-600 dark:text-teal-400">
          <span>Malasakit Center (MAIP/PCSO/DSWD GL)</span>
          <span>-₱{{ number_format((float) $patientBill->malasakit_assistance, 2) }}</span>
        </div>
        <div class="border-t border-dashed border-slate-300 pt-2 flex justify-between font-bold text-base text-slate-900 dark:text-white">
          <span>Net Patient Out-Of-Pocket Due</span>
          <span class="text-emerald-600 dark:text-emerald-400">₱{{ number_format((float) $patientBill->patient_payable, 2) }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Cashier Tender & OR Generation -->
  <div class="lg:col-span-5 space-y-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <h3 class="font-bold text-slate-900 dark:text-white mb-3">Tender & Official Receipt (OR)</h3>
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
          <select name="payment_method" class="w-full rounded-xl border-0 bg-slate-50 py-2.5 px-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
            <option value="CASH">Cash Drawer</option>
            <option value="EFT">Online Bank EFT (InstaPay / PESONet)</option>
            <option value="CARD">Credit / Debit Card Terminal</option>
            <option value="CHECK">Manager's Check</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Amount Tendered (₱)</label>
          <input 
            type="number" 
            step="0.01" 
            x-model.number="tenderAmount" 
            class="w-full rounded-xl border-0 bg-slate-50 py-2.5 px-3 text-right font-mono text-lg font-bold text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700" 
            placeholder="0.00"
          >
        </div>

        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60 font-mono">
          <div class="flex justify-between text-xs text-slate-500">
            <span>Change Due:</span>
            <span class="text-sm font-bold text-slate-900 dark:text-white">₱<span x-text="changeDue.toFixed(2)"></span></span>
          </div>
        </div>

        <button 
          type="submit" 
          :disabled="tenderAmount < patientPayable" 
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <i class="ph-bold ph-printer text-base"></i> Issue Official Receipt (OR)
        </button>
      </div>
    </div>
  </div>
</div>
```

---

### Blueprint 5: Accounts Receivable Aging Buckets
Use for managing Patient & HMO overdue debt.

```blade
<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
  <!-- Current (0-30 Days) -->
  <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Current (0-30d)</span>
    <div class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($aging['current'] ?? 0), 2) }}</div>
    <span class="text-xs text-slate-500">Low Risk</span>
  </div>

  <!-- 31-60 Days -->
  <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">31-60 Days</span>
    <div class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($aging['days_31_60'] ?? 0), 2) }}</div>
    <span class="text-xs text-slate-500">HMO Follow-up</span>
  </div>

  <!-- 61-90 Days -->
  <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">61-90 Days</span>
    <div class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($aging['days_61_90'] ?? 0), 2) }}</div>
    <span class="text-xs text-slate-500">Billing Notice Sent</span>
  </div>

  <!-- 91-120 Days -->
  <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <span class="text-xs font-bold uppercase tracking-wider text-orange-600">91-120 Days</span>
    <div class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($aging['days_91_120'] ?? 0), 2) }}</div>
    <span class="text-xs text-slate-500">Critical Stage</span>
  </div>

  <!-- Over 120 Days -->
  <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <span class="text-xs font-bold uppercase tracking-wider text-rose-600">> 120 Days</span>
    <div class="mt-2 font-mono text-lg font-bold text-slate-900 dark:text-white">₱{{ number_format((float) ($aging['over_120'] ?? 0), 2) }}</div>
    <span class="text-xs text-rose-500 font-semibold">Provision for Bad Debt</span>
  </div>
</div>
```

---

### Blueprint 6: Accounts Payable 3-Way Matching Verification Matrix
Compares Purchase Order (PO) ↔ Receiving Report / Delivery Receipt (RR/DR) ↔ Vendor Purchase Bill.

```blade
<div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
  <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
    <h3 class="font-bold text-slate-900 dark:text-white">3-Way Matching Integrity Check</h3>
    @if($matchStatus === 'MATCHED')
      <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-600/20">
        <i class="ph-bold ph-check"></i> 100% Quantities & Prices Matched
      </span>
    @else
      <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-600/20">
        <i class="ph-bold ph-warning"></i> Quantity / Unit Price Discrepancy
      </span>
    @endif
  </div>

  <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 font-mono text-xs">
    <!-- PO Column -->
    <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200/70 dark:bg-slate-800">
      <div class="font-sans font-semibold text-slate-700 dark:text-slate-300">1. Purchase Order (PO)</div>
      <div class="mt-1 text-slate-500">Ref: {{ $po->po_number }}</div>
      <div class="mt-2 font-bold text-slate-900 dark:text-white">₱{{ number_format((float) $po->total_amount, 2) }}</div>
      <div class="text-slate-500">{{ $po->total_qty }} items ordered</div>
    </div>

    <!-- RR Column -->
    <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200/70 dark:bg-slate-800">
      <div class="font-sans font-semibold text-slate-700 dark:text-slate-300">2. Receiving Report (RR)</div>
      <div class="mt-1 text-slate-500">Ref: {{ $rr->rr_number }}</div>
      <div class="mt-2 font-bold text-slate-900 dark:text-white">₱{{ number_format((float) $rr->inspected_amount, 2) }}</div>
      <div class="text-slate-500">{{ $rr->received_qty }} items accepted</div>
    </div>

    <!-- Vendor Bill Column -->
    <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200/70 dark:bg-slate-800">
      <div class="font-sans font-semibold text-slate-700 dark:text-slate-300">3. Vendor Invoice Bill</div>
      <div class="mt-1 text-slate-500">Inv: {{ $bill->invoice_number }}</div>
      <div class="mt-2 font-bold text-slate-900 dark:text-white">₱{{ number_format((float) $bill->billed_amount, 2) }}</div>
      <div class="text-slate-500">{{ $bill->billed_qty }} items billed</div>
    </div>
  </div>
</div>
```

---

### Blueprint 7: Alpine.js Modal & Slide-Over Drawer
High-fidelity modal architecture with zero layout shift and backdrop blur.

```blade
<div x-data="{ open: false }" @keydown.escape.window="open = false">
  <!-- Trigger Button -->
  <button @click="open = true" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
    <i class="ph-bold ph-plus"></i> Settle Patient Billing
  </button>

  <!-- Modal Backdrop & Window -->
  <div x-show="open" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity.duration.300ms class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
      <div 
        x-show="open" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click.outside="open = false" 
        class="mx-auto max-w-xl transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl ring-1 ring-slate-200 transition-all dark:bg-slate-900 dark:ring-slate-800"
      >
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-title">Authorize Payment Disbursement</h3>
            <p class="text-xs text-slate-500">Disbursement Voucher #{{ $voucher->voucher_number ?? 'DV-NEW' }}</p>
          </div>
          <button @click="open = false" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white">
            <i class="ph ph-x text-lg"></i>
          </button>
        </div>

        <form method="POST" action="{{ route('disbursement.vouchers.store') }}" class="mt-4 space-y-4">
          @csrf
          <!-- Form Fields Here -->
          
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <button @click="open = false" type="button" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
              Cancel
            </button>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
              <i class="ph-bold ph-check"></i> Confirm Approval
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
```

---

## 4. Bootstrap 5 to Tailwind CSS v4 Conversion Matrix

When migrating existing views from Bootstrap to Tailwind CSS v4, use this exact 1:1 translation:

| Legacy Bootstrap 5 Pattern | Modern Tailwind CSS v4 Pattern |
| :--- | :--- |
| `container-fluid p-4` | `w-full px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto` |
| `d-flex align-items-center justify-content-between` | `flex items-center justify-between` |
| `card border-0 shadow-sm rounded-3 p-3` | `rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800` |
| `card-header bg-transparent border-bottom p-3` | `border-b border-slate-200 p-4 dark:border-slate-800` |
| `row g-3` | `grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3` |
| `col-md-3`, `col-md-6`, `col-md-12` | `col-span-1`, `col-span-2`, `col-span-full` |
| `btn btn-primary` | `inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700` |
| `btn btn-outline-secondary btn-sm` | `inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50` |
| `table table-hover` | `<table class="w-full text-left text-sm">` with `hover:bg-slate-50/50` on `<tr>` |
| `form-control form-control-sm` | `w-full rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600` |
| `form-select` | `w-full rounded-xl border-0 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600` |
| `badge bg-success-subtle text-success` | `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20` |
| `badge bg-danger-subtle text-danger` | `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20` |
| `badge bg-warning-subtle text-warning` | `inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20` |
| `alert alert-success` | `rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300` |
| `text-muted small` | `text-xs text-slate-500 dark:text-slate-400` |
| `font-monospace text-end` | `font-mono tabular-nums text-right` |

---

## 5. UI Implementation & Verification Checklist

When building or updating any Blade view in FMS:
1. **Asset Compilation:** Ensure `@vite(['resources/css/app.css', 'resources/js/app.js'])` is loaded in `layouts/app.blade.php`.
2. **Numeric Alignment:** Always right-align monetary numbers with `text-right font-mono tabular-nums`.
3. **Currency Denomination:** Always format Philippine currency as `₱{{ number_format((float) $val, 2) }}`.
4. **Interactive Double-Entry Balance Check:** Any custom voucher or journal entry form must compute debit/credit parity in real-time before enabling post buttons.
5. **No Raw Arithmetic in Blade:** Never calculate taxes, discounts, or margins inside `.blade.php`. Always pass pre-computed fields from the Domain Service.
6. **Dark Mode Cohesion:** Ensure every surface uses `dark:bg-slate-900`, `dark:ring-slate-800`, and `dark:text-white` to prevent unstyled white blocks in dark mode.
7. **Build Validation:** Run `npm run build` after creating or editing templates to ensure zero Vite compilation errors.
