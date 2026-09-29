@extends('layouts.app')

@section('title', 'Bank Reconciliation Terminal - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'bank-reconciliation')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Bank Statement Reconciliation Workstation
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <a 
        href="{{ route('cash.bank-accounts') }}" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-bank text-emerald-600"></i>
        <span>Bank Accounts</span>
      </a>
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

  <!-- Bank Account & Cutoff Selector -->
  <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <form method="GET" action="{{ route('cash.bank-reconciliation') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
      <div class="sm:col-span-6">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Select Bank Account to Reconcile:
        </label>
        <select 
          name="bank_account_id" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          onchange="this.form.submit()"
        >
          @foreach($bankAccounts as $ba)
            <option value="{{ $ba->id }}" {{ $selectedBankId === $ba->id ? 'selected' : '' }}>
              {{ $ba->bank_name }} - {{ $ba->name }} ({{ $ba->account_number }}) &bull; GL: ₱{{ number_format((float) $ba->balance, 2) }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-4">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Statement Cutoff Date:
        </label>
        <input 
          type="date" 
          name="cutoff_date" 
          value="{{ $cutoffDate }}" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          onchange="this.form.submit()"
        >
      </div>

      <div class="sm:col-span-2">
        <button 
          type="submit" 
          class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition-all cursor-pointer"
        >
          <i class="ph-bold ph-arrows-clockwise"></i>
          <span>Refresh</span>
        </button>
      </div>
    </form>
  </div>

  @php
    $curBank = $workspace['bank_account'] ?? null;
    $bookBal = (float) ($workspace['book_balance'] ?? 0);
    $outChecks = $workspace['outstanding_checks'] ?? collect();
    $totOutChecks = (float) ($workspace['total_outstanding_checks'] ?? 0);
    $depTransit = $workspace['deposits_in_transit'] ?? collect();
    $totDepTransit = (float) ($workspace['total_deposits_in_transit'] ?? 0);
  @endphp

  <!-- Real-Time Reconciliation Calculator Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">GL Ledger Book Balance</span>
      <h4 class="mt-2 text-2xl font-bold font-mono text-slate-900 dark:text-white" id="displayBookBalance">₱{{ number_format($bookBal, 2) }}</h4>
      <input type="hidden" id="rawBookBalance" value="{{ $bookBal }}">
      <span class="mt-1 block text-xs text-slate-400">Hospital general ledger record</span>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Uncleared Deposits</span>
      <h4 class="mt-2 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="displayTransitDeposits">+₱{{ number_format($totDepTransit, 2) }}</h4>
      <input type="hidden" id="rawTransitDeposits" value="{{ $totDepTransit }}">
      <span class="mt-1 block text-xs text-slate-400">Deposits in transit to bank</span>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Uncleared Checks / Outflows</span>
      <h4 class="mt-2 text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" id="displayOutstandingChecks">-₱{{ number_format($totOutChecks, 2) }}</h4>
      <input type="hidden" id="rawOutstandingChecks" value="{{ $totOutChecks }}">
      <span class="mt-1 block text-xs text-slate-400">Outstanding issued checks</span>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
      <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Calculated Variance</span>
      <h4 class="mt-2 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" id="displayVariance">₱0.00</h4>
      <span class="mt-1 block text-xs font-semibold text-emerald-600 dark:text-emerald-400" id="varianceStatusText">Balanced &amp; Ready</span>
    </div>
  </div>

  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <!-- Main Matching Terminal Form -->
  <form method="POST" action="{{ route('cash.bank-reconciliation.post') }}" id="reconciliationForm">
    @csrf
    <input type="hidden" name="bank_account_id" value="{{ $selectedBankId }}">
    <input type="hidden" name="cutoff_date" value="{{ $cutoffDate }}">
    <input type="hidden" name="book_balance" value="{{ $bookBal }}">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Left: Uncleared Checks / Disbursements Register -->
      <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800 flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <i class="ph-bold ph-check-square-offset text-rose-600"></i>
            <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
              Outstanding Checks &amp; Disbursements
            </h6>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
            {{ count($outChecks) }} Items
          </span>
        </div>

        <div class="overflow-x-auto flex-1 max-h-96 overflow-y-auto">
          <table class="w-full text-left text-xs">
            <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
              <tr>
                <th class="py-2.5 px-3 w-10">
                  <input type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" id="toggleAllChecks" onclick="toggleAll('clearedChecksGroup', this.checked)">
                </th>
                <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Check / Voucher #</th>
                <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Payee</th>
                <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Date</th>
                <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              @forelse($outChecks as $chk)
              <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-2.5 px-3">
                  <input 
                    type="checkbox" 
                    name="cleared_check_ids[]" 
                    value="{{ $chk->id }}" 
                    data-amount="{{ (float) $chk->amount }}" 
                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 clearedChecksGroup cursor-pointer" 
                    onchange="recalculateReconciliation()"
                  >
                </td>
                <td class="py-2.5 px-3">
                  <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $chk->check_number }}</span>
                  <span class="block text-[11px] text-slate-400">{{ $chk->disbursementVoucher?->voucher_number ?? 'DV-DIRECT' }}</span>
                </td>
                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">{{ $chk->payee_name }}</td>
                <td class="py-2.5 px-3 font-mono text-slate-500 dark:text-slate-400">
                  {{ $chk->check_date ? $chk->check_date->format('M d, Y') : '-' }}
                </td>
                <td class="py-2.5 px-3 text-right font-mono tabular-nums font-semibold text-rose-600 dark:text-rose-400">
                  ₱{{ number_format((float) $chk->amount, 2) }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">
                  No outstanding checks awaiting clearance.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right: Deposits in Transit & Statement Inputs -->
      <div class="space-y-6">
        <!-- Deposits in Transit Table -->
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
          <div class="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <i class="ph-bold ph-receipt text-emerald-600"></i>
              <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
                Uncleared Deposits in Transit
              </h6>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
              {{ count($depTransit) }} Deposits
            </span>
          </div>

          <div class="overflow-x-auto max-h-52 overflow-y-auto">
            <table class="w-full text-left text-xs">
              <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
                <tr>
                  <th class="py-2.5 px-3 w-10">
                    <input type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" id="toggleAllDeposits" onclick="toggleAll('clearedDepositsGroup', this.checked)">
                  </th>
                  <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Deposit Ref #</th>
                  <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Shift Code</th>
                  <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">Date</th>
                  <th class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($depTransit as $dep)
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                  <td class="py-2.5 px-3">
                    <input 
                      type="checkbox" 
                      name="cleared_deposit_ids[]" 
                      value="{{ $dep->id }}" 
                      data-amount="{{ (float) $dep->total_deposited }}" 
                      class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 clearedDepositsGroup cursor-pointer" 
                      onchange="recalculateReconciliation()"
                    >
                  </td>
                  <td class="py-2.5 px-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                    {{ $dep->deposit_reference }}
                  </td>
                  <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                    {{ $dep->cashierShift?->shift_code ?? 'Manual' }}
                  </td>
                  <td class="py-2.5 px-3 font-mono text-slate-500 dark:text-slate-400">
                    {{ $dep->deposit_date ? $dep->deposit_date->format('M d, Y') : '-' }}
                  </td>
                  <td class="py-2.5 px-3 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                    ₱{{ number_format((float) $dep->total_deposited, 2) }}
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="py-6 text-center text-slate-400 dark:text-slate-500">
                    No deposits in transit.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- Bank Statement Entry Card -->
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
          <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4 dark:border-slate-800">
            <i class="ph-bold ph-bank text-blue-600"></i>
            <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
              Bank Statement Entry &amp; Sign-Off
            </h6>
          </div>

          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Statement Ending Date <span class="text-rose-500">*</span>
                </label>
                <input 
                  type="date" 
                  name="statement_date" 
                  id="inputStatementDate" 
                  value="{{ $cutoffDate }}" 
                  class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
                  required
                >
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Ending Statement Balance (₱) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 font-mono font-bold text-slate-400 text-xs">₱</span>
                  <input 
                    type="number" 
                    step="0.01" 
                    name="statement_balance" 
                    id="inputStatementBalance" 
                    class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-7 pr-3 text-xs font-mono font-bold text-slate-900 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" 
                    value="{{ $bookBal }}" 
                    required 
                    oninput="recalculateReconciliation()"
                  >
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Reconciliation Notes &amp; Audit Comments
              </label>
              <textarea 
                name="notes" 
                rows="2" 
                class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
                placeholder="Audit remarks, statement discrepancies noted..."
              ></textarea>
            </div>

            <div class="flex justify-end pt-2">
              <button 
                type="submit" 
                id="btnPostReconciliation" 
                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
              >
                <i class="ph-bold ph-shield-check text-base"></i>
                <span>Post Bank Reconciliation</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Section: Past Reconciliation History Register -->
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex items-center gap-2 border-b border-slate-200 p-4 dark:border-slate-800">
      <i class="ph-bold ph-clock-counter-clockwise text-blue-600"></i>
      <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
        Bank Reconciliation History Log
      </h6>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Statement Date</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Cutoff Date</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Statement Balance</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Book Balance</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Variance</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Reconciler</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($reconciliations ?? [] as $r)
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
              {{ $r->statement_date ? $r->statement_date->format('M d, Y') : '-' }}
            </td>
            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
              {{ $r->cutoff_date ? $r->cutoff_date->format('M d, Y') : '-' }}
            </td>
            <td class="py-3 px-4 text-right font-mono tabular-nums text-slate-700 dark:text-slate-200">
              ₱{{ number_format((float) $r->statement_balance, 2) }}
            </td>
            <td class="py-3 px-4 text-right font-mono tabular-nums text-slate-700 dark:text-slate-200">
              ₱{{ number_format((float) $r->book_balance, 2) }}
            </td>
            <td class="py-3 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
              ₱{{ number_format((float) $r->variance, 2) }}
            </td>
            <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
              {{ $r->reconciler?->name ?? 'Treasury Staff' }}
            </td>
            <td class="py-3 px-4 text-center">
              <x-status-badge :status="$r->status" color="emerald" />
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-scales text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No historical bank reconciliations logged.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
function toggleAll(className, isChecked) {
  document.querySelectorAll('.' + className).forEach(cb => {
    cb.checked = isChecked;
  });
  recalculateReconciliation();
}

function recalculateReconciliation() {
  const bookBal = parseFloat(document.getElementById('rawBookBalance')?.value || 0);
  const stmtBal = parseFloat(document.getElementById('inputStatementBalance')?.value || 0);
  const rawTransit = parseFloat(document.getElementById('rawTransitDeposits')?.value || 0);
  const rawOutChecks = parseFloat(document.getElementById('rawOutstandingChecks')?.value || 0);

  // Calculate selected cleared checks
  let selectedChecks = 0;
  document.querySelectorAll('.clearedChecksGroup:checked').forEach(cb => {
    selectedChecks += parseFloat(cb.getAttribute('data-amount') || 0);
  });

  // Calculate selected cleared deposits
  let selectedDeposits = 0;
  document.querySelectorAll('.clearedDepositsGroup:checked').forEach(cb => {
    selectedDeposits += parseFloat(cb.getAttribute('data-amount') || 0);
  });

  // Remaining timing differences (uncleared items as of cutoff date)
  const remainingOutChecks = Math.max(0, rawOutChecks - selectedChecks);
  const remainingTransitDeposits = Math.max(0, rawTransit - selectedDeposits);

  // Update dynamic display cards
  const dispTransit = document.getElementById('displayTransitDeposits');
  if (dispTransit) {
    dispTransit.textContent = '+₱' + remainingTransitDeposits.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  const dispChecks = document.getElementById('displayOutstandingChecks');
  if (dispChecks) {
    dispChecks.textContent = '-₱' + remainingOutChecks.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Two-Way Adjusted Bank Balance (GAAP/IFRS)
  const adjustedBankBalance = stmtBal + remainingTransitDeposits - remainingOutChecks;
  const variance = adjustedBankBalance - bookBal;

  const dispVar = document.getElementById('displayVariance');
  const statText = document.getElementById('varianceStatusText');
  const btnPost = document.getElementById('btnPostReconciliation');

  if (dispVar) {
    dispVar.textContent = (variance >= 0 ? '' : '-') + '₱' + Math.abs(variance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (Math.abs(variance) < 0.005) {
      dispVar.className = 'mt-2 text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400';
      if (statText) {
        statText.textContent = 'Balanced with Zero Variance (₱0.00)';
        statText.className = 'mt-1 block text-xs font-semibold text-emerald-600 dark:text-emerald-400';
      }
      if (btnPost) btnPost.disabled = false;
    } else {
      dispVar.className = 'mt-2 text-2xl font-bold font-mono text-rose-600 dark:text-rose-400';
      if (statText) {
        statText.textContent = 'Unresolved Variance: ₱' + Math.abs(variance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        statText.className = 'mt-1 block text-xs font-semibold text-rose-600 dark:text-rose-400';
      }
      if (btnPost) btnPost.disabled = false;
    }
  }
}
document.addEventListener('DOMContentLoaded', recalculateReconciliation);
</script>
@endpush
@endsection
