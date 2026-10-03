@extends('layouts.app')

@section('title', 'Fund Transfers - Cash Management | FMS')
@section('module', 'cash')
@section('page', 'fund-transfers')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Bank Fund Transfers &amp; Sweeps
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
      <button 
        type="button" 
        id="btnNewTransfer" 
        @click="$dispatch('open-modal', 'newTransferModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-arrows-left-right"></i>
        <span>Execute Fund Transfer</span>
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

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <x-stat-card 
      title="Cumulative Volume" 
      :value="$totalTransferVolume ?? 0" 
      icon="ph-arrows-clockwise" 
      color="emerald" 
      subtitle="Total value swept across bank accounts"
    />
    <x-stat-card 
      title="Transfer Transactions" 
      :value="$transfers->total() ?? count($transfers ?? [])" 
      :isCurrency="false" 
      icon="ph-files" 
      color="slate" 
      subtitle="Completed internal treasury transfers"
    />
    <x-stat-card 
      title="Transfer Channels" 
      value="PESONet • InstaPay • Book" 
      :isCurrency="false" 
      icon="ph-globe-hemisphere-west" 
      color="emerald" 
      subtitle="Active banking rails"
    />
  </div>

  <!-- Transfers Ledger Table Card -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <form method="GET" action="{{ route('cash.fund-transfers') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-wrap">
        <div class="flex flex-wrap items-center gap-2.5">
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <i class="ph-bold ph-calendar"></i>
            <span>Dates:</span>
          </div>
          <input 
            type="date" 
            name="date_from" 
            value="{{ request('date_from') }}" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
          <span class="text-xs text-slate-400">to</span>
          <input 
            type="date" 
            name="date_to" 
            value="{{ request('date_to') }}" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
          <button 
            type="submit" 
            class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all cursor-pointer"
          >
            <i class="ph-bold ph-funnel"></i>
            <span>Filter</span>
          </button>
          <a 
            href="{{ route('cash.fund-transfers') }}" 
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-xs font-semibold text-slate-600 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
            title="Reset"
          >
            <i class="ph-bold ph-arrow-counter-clockwise"></i>
          </a>
        </div>

        <div class="relative w-full sm:w-72">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="ph ph-magnifying-glass text-sm"></i>
          </div>
          <input 
            type="search" 
            name="search" 
            value="{{ request('search') }}" 
            placeholder="Search reference #, bank, memo..." 
            class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Transfer Ref #</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Date</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Source (Debit Outflow)</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Destination (Credit Inflow)</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Method</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">General Ledger</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Amount</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($transfers ?? [] as $t)
          <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
            <td class="py-3.5 px-4">
              <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $t->reference_number }}</span>
              @if($t->memo)
                <span class="block text-[11px] text-slate-400 truncate max-w-xs">{{ $t->memo }}</span>
              @endif
            </td>
            <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-400">
              {{ $t->transfer_date ? $t->transfer_date->format('M d, Y') : '-' }}
            </td>
            <td class="py-3.5 px-4">
              <div class="font-semibold text-slate-900 dark:text-white">{{ $t->sourceBank?->name ?? $t->source_account }}</div>
              <span class="font-mono text-xs text-slate-400">{{ $t->sourceBank?->account_number ?? $t->source_number }}</span>
            </td>
            <td class="py-3.5 px-4">
              <div class="font-semibold text-slate-900 dark:text-white">{{ $t->destinationBank?->name ?? $t->destination_account }}</div>
              <span class="font-mono text-xs text-slate-400">{{ $t->destinationBank?->account_number ?? $t->destination_number }}</span>
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                {{ $t->transfer_method }}
              </span>
            </td>
            <td class="py-3.5 px-4">
              @if($t->journalEntry)
                <a 
                  href="{{ route('gl.journal-entries') }}?search={{ $t->journalEntry->reference_number }}" 
                  class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  <i class="ph ph-link"></i>
                  <span>{{ $t->journalEntry->reference_number }}</span>
                </a>
              @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] text-slate-400 bg-slate-50 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                  JE-POSTED
                </span>
              @endif
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
              ₱{{ number_format((float) $t->amount, 2) }}
            </td>
            <td class="py-3.5 px-4 text-center">
              <x-status-badge :status="$t->status" color="emerald" />
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-arrows-left-right text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No fund transfers recorded.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer -->
    <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 dark:border-slate-800">
      <span class="text-xs text-slate-500 dark:text-slate-400">
        Showing {{ $transfers->count() }} of {{ $transfers->total() }} Fund Transfers
      </span>
      <div>
        {{ $transfers->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Modal: Execute Fund Transfer -->
<x-modal 
  id="newTransferModal" 
  title="Execute Inter-Account Fund Transfer" 
  size="md"
  formId="newTransferForm" 
  formAction="{{ route('cash.fund-transfers.store') }}" 
  formMethod="POST" 
  submitText="Authorize &amp; Post Transfer" 
  submitIcon="ph-check"
>
  <div class="space-y-3.5">
    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
        Source Bank Account (From) <span class="text-rose-500">*</span>
      </label>
      <select 
        name="source_bank_account_id" 
        id="sourceBankSelect" 
        class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
        required 
        onchange="validateDifferentBanks()"
      >
        <option value="">-- Select Source Bank Account --</option>
        @foreach($bankAccounts ?? [] as $ba)
          <option value="{{ $ba->id }}" data-balance="{{ (float) $ba->balance }}">
            {{ $ba->bank_name }} - {{ $ba->name }} (₱{{ number_format((float) $ba->balance, 2) }})
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
        Destination Bank Account (To) <span class="text-rose-500">*</span>
      </label>
      <select 
        name="destination_bank_account_id" 
        id="destBankSelect" 
        class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
        required 
        onchange="validateDifferentBanks()"
      >
        <option value="">-- Select Destination Bank Account --</option>
        @foreach($bankAccounts ?? [] as $ba)
          <option value="{{ $ba->id }}">
            {{ $ba->bank_name }} - {{ $ba->name }} ({{ $ba->account_number }})
          </option>
        @endforeach
      </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Transfer Amount (₱) <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 font-mono font-bold text-slate-400 text-xs">₱</span>
          <input 
            type="number" 
            step="0.01" 
            min="0.01" 
            name="amount" 
            class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-7 pr-3 text-xs font-mono font-bold text-slate-900 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white" 
            placeholder="0.00" 
            required
          >
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
          Transfer Date <span class="text-rose-500">*</span>
        </label>
        <input 
          type="date" 
          name="transfer_date" 
          class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
          value="{{ date('Y-m-d') }}" 
          required
        >
      </div>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Transfer Protocol / Channel</label>
      <select 
        name="transfer_method" 
        class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
      >
        <option value="INSTAPAY_PESONET">PESONet / InstaPay Commercial Routing</option>
        <option value="INTERNAL_BOOK_TRANSFER">Internal Bank Intragroup Book Transfer</option>
        <option value="RTGS_DIRECT">RTGS High-Value Treasury Transfer</option>
        <option value="MANAGER_CHECK_DEPOSIT">Manager's Check Inter-Branch Deposit</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Transfer Memo &amp; Justification</label>
      <input 
        type="text" 
        name="memo" 
        class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" 
        placeholder="e.g. Funding payroll account for 15th cutoff"
      >
    </div>
  </div>
</x-modal>

@push('scripts')
<script>
function validateDifferentBanks() {
  const src = document.getElementById('sourceBankSelect')?.value;
  const dst = document.getElementById('destBankSelect')?.value;

  if (src && dst && src === dst) {
    alert('Source and Destination bank accounts must be different!');
    document.getElementById('destBankSelect').value = '';
  }
}
</script>
@endpush
@endsection
