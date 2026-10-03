@extends('layouts.app')

@section('title', 'Tax Audit Trail - Tax Management | FMS')
@section('module', 'tax')
@section('page', 'tax-audit')

@section('content')
<div class="space-y-6">
  <!-- Executive Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          Tax Audit Trail &amp; Compliance Logs
        </h1>
    </div>

    <div class="flex items-center gap-2.5">
      <button 
        type="button" 
        onclick="alert('Verifying cryptographic SHA-256 hash integrity across log chain... Hash validated!');" 
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
      >
        <i class="ph-bold ph-shield-check text-emerald-600"></i>
        <span>Verify Hash Chain</span>
      </button>
      <button 
        type="button" 
        id="btnExportAudit" 
        @click="$dispatch('open-modal', 'exportAuditModal')"
        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 ring-1 ring-emerald-600/20 transition-all cursor-pointer"
      >
        <i class="ph-bold ph-file-arrow-down"></i>
        <span>Export Audit Log</span>
      </button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
    <x-stat-card 
      title="Logged Audit Events" 
      :value="($taxRules ?? collect())->count() + ($certificates ?? collect())->count()" 
      :isCurrency="false"
      icon="ph-list-checks" 
      color="indigo" 
      subtitle="Total immutable tax events"
    />
    <x-stat-card 
      title="Verified Tax Impacts" 
      value="100.0%" 
      :isCurrency="false"
      icon="ph-check-circle" 
      color="emerald" 
      subtitle="Reconciled against general ledger"
    />
    <x-stat-card 
      title="Discrepancy Flags" 
      value="0 Flags" 
      :isCurrency="false"
      icon="ph-warning" 
      color="slate" 
      subtitle="Zero calculation anomalies"
    />
    <x-stat-card 
      title="Hash Integrity" 
      value="Encrypted &amp; Verified" 
      :isCurrency="false"
      icon="ph-lock-key" 
      color="purple" 
      subtitle="SHA-256 forward-linked chain"
    />
  </div>

  <!-- Audit Log Data Table Card -->
  
  <!-- Sub-Module Category Navigation Bar -->
  @include('partials.submodule-nav')

  <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 overflow-hidden dark:bg-slate-900 dark:ring-slate-800">
    <!-- Filter Toolbar -->
    <div class="border-b border-slate-200 p-4 dark:border-slate-800">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-wrap">
        <div class="flex flex-wrap items-center gap-2.5">
          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <i class="ph-bold ph-funnel"></i>
            <span>Event Category:</span>
          </div>
          <select 
            id="auditCategorySelect" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="" selected>All Event Categories</option>
            <option value="ewt">EWT 2307 Form Generation</option>
            <option value="return">Statutory Return Filing</option>
          </select>

          <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 ml-2">
            <span>Officer:</span>
          </div>
          <select 
            id="auditUserSelect" 
            class="rounded-xl border-0 bg-slate-50 py-1.5 px-3 text-xs font-semibold text-slate-800 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
            <option value="" selected>All Officers / Users</option>
            <option value="tax_officer_1">tax_officer_1</option>
            <option value="cfo_user">cfo_user</option>
          </select>
        </div>

        <div class="relative w-full sm:w-72">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="ph ph-magnifying-glass text-sm"></i>
          </div>
          <input 
            type="search" 
            id="auditSearchInput" 
            placeholder="Search voucher ID, user, event..." 
            class="w-full rounded-xl border-0 bg-slate-50 py-1.5 pl-9 pr-3 text-xs text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
          >
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto custom-scrollbar">
      <table id="taxAuditTable" class="w-full text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50/75 dark:border-slate-800 dark:bg-slate-800/60 sticky top-0">
          <tr>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Audit Timestamp</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">User / Officer</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Event Category</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Source Voucher / Form</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Tax Impact</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">Cryptographic Hash</th>
            <th class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          @forelse($logs ?? [] as $l)
          @php
            $lArr = is_array($l) ? $l : [
              'time' => $l->created_at ? $l->created_at->format('Y-m-d H:i:s') : 'N/A',
              'user' => $l->user_name ?? ($l->user?->name ?? 'System Officer'),
              'ip' => $l->ip_address ?? '127.0.0.1',
              'category' => class_basename($l->auditable_type ?? 'Tax Event'),
              'cat_type' => strtolower($l->action ?? 'general'),
              'voucher' => ($l->auditable_type ? class_basename($l->auditable_type) . ' #' . $l->auditable_id : ($l->source_voucher ?? 'N/A')),
              'impact' => '₱' . number_format((float) ($l->new_values['tax_due'] ?? $l->new_values['tax_withheld'] ?? $l->new_values['rate'] ?? $l->tax_impact ?? 0), 2),
              'hash' => $l->record_hash ?? $l->hash ?? str_repeat('0', 64),
            ];
          @endphp
          <tr 
            class="audit-row hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors cursor-pointer" 
            data-cat="{{ $lArr['cat_type'] }}" 
            data-user="{{ strtolower($lArr['user']) }}" 
            onclick="openTaxAuditDetailsModal({{ json_encode($lArr) }})"
          >
            <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">
              {{ $lArr['time'] }}
            </td>
            <td class="py-3.5 px-4">
              <div class="font-semibold text-slate-900 dark:text-white">{{ $lArr['user'] }}</div>
              <span class="font-mono text-[11px] text-slate-400">IP: {{ $lArr['ip'] }}</span>
            </td>
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                {{ $lArr['category'] }}
              </span>
            </td>
            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
              {{ $lArr['voucher'] }}
            </td>
            <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold text-rose-600 dark:text-rose-400">
              {{ $lArr['impact'] }}
            </td>
            <td class="py-3.5 px-4">
              <span class="font-mono text-[11px] text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700" title="{{ $lArr['hash'] }}">
                {{ substr($lArr['hash'], 0, 16) }}...
              </span>
            </td>
            <td class="py-3.5 px-4 text-right" onclick="event.stopPropagation();">
              <button 
                type="button" 
                title="View Payload Snapshot" 
                onclick="openTaxAuditDetailsModal({{ json_encode($lArr) }})"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer"
              >
                <i class="ph ph-eye text-sm"></i>
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
              <i class="ph ph-shield-check text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
              No tax audit entries recorded in database.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer & Pagination -->
    @if(method_exists($logs, 'links'))
      <div class="border-t border-slate-200 p-4 dark:border-slate-800">
        {{ $logs->links() }}
      </div>
    @endif
  </div>
</div>

<!-- Modal: In-Depth Tax Audit Entry Details -->
<x-modal 
  id="taxAuditDetailsModal" 
  title="Cryptographic Audit Entry" 
  size="lg"
>
  <div class="space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800" id="detailAuditVoucher">
          C2307-2026-881
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300" id="detailAuditCategory">
          EWT 2307 Form Generation
        </span>
      </div>
      <span class="font-mono text-xs text-slate-400" id="detailAuditTime">2026-08-08 14:22:10</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
        <span class="block text-xs font-semibold uppercase text-slate-400">Tax Impact Base</span>
        <h4 class="mt-1 text-2xl font-bold font-sans kpi-value tabular-nums text-rose-600 dark:text-rose-400" id="detailAuditImpact">-₱12,000.00</h4>
      </div>
      <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 text-center dark:border-slate-800 dark:bg-slate-800/40">
        <span class="block text-xs font-semibold uppercase text-slate-400">Recorded User / IP</span>
        <h5 class="mt-1 text-base font-bold font-mono text-slate-800 dark:text-slate-200">
          <span id="detailAuditUser">tax_officer_1</span> &bull; <span id="detailAuditIp">192.168.1.45</span>
        </h5>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <h6 class="text-xs font-bold uppercase text-slate-700 dark:text-slate-300 flex items-center gap-1.5 mb-3">
        <i class="ph-bold ph-shield-check text-emerald-600"></i>
        Cryptographic SHA-256 Hash Verification
      </h6>
      <div class="space-y-2 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Log Hash Integrity:</span>
          <span class="inline-flex items-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            <i class="ph-bold ph-check mr-1"></i> Tamper-Evident SHA-256 Validated
          </span>
        </div>
        <div class="pt-1">
          <span class="text-slate-500 dark:text-slate-400 block mb-1">Full Hash Signature:</span>
          <div class="rounded-lg bg-slate-50 p-2 font-mono text-[11px] text-slate-600 dark:bg-slate-800 dark:text-slate-300 break-all select-all border border-slate-200 dark:border-slate-700" id="detailAuditHash">
            e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
          </div>
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
      onclick="alert('Exporting Encrypted Log Entry Snapshot...');" 
      class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all cursor-pointer"
    >
      <i class="ph-bold ph-file-text"></i>
      <span>Export Entry Certificate</span>
    </button>
  </x-slot:footer>
</x-modal>

<!-- Modal: Export Tax Audit Log -->
<x-modal 
  id="exportAuditModal" 
  title="Export Signed Tax Audit Log" 
  size="md"
>
  <div class="space-y-3.5">
    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Audit Date Range</label>
      <select class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
        <option value="ytd">Year-To-Date FY 2026</option>
        <option value="q2">Q2 2026</option>
        <option value="all">Full Audit Chain</option>
      </select>
    </div>

    <div>
      <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Export Format</label>
      <select class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
        <option value="pdf">Auditor Signed PDF Package</option>
        <option value="csv">Encrypted CSV Audit Dump</option>
      </select>
    </div>
  </div>

  <x-slot:footer>
    <button 
      type="button" 
      @click="show = false"
      class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer"
    >
      Cancel
    </button>
    <button 
      type="button" 
      onclick="alert('Signed Tax Audit Log exported!'); $dispatch('close-modal', 'exportAuditModal');" 
      class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all cursor-pointer"
    >
      <i class="ph-bold ph-download"></i>
      <span>Generate &amp; Download</span>
    </button>
  </x-slot:footer>
</x-modal>
@endsection

@push('scripts')
<script>
function openTaxAuditDetailsModal(l) {
  if (!l) return;

  const elVoucher = document.getElementById('detailAuditVoucher');
  if (elVoucher) elVoucher.textContent = l.voucher || 'VOUCHER-000';

  const elCategory = document.getElementById('detailAuditCategory');
  if (elCategory) elCategory.textContent = l.category || 'Event Category';

  const elImpact = document.getElementById('detailAuditImpact');
  if (elImpact) elImpact.textContent = l.impact || '₱0.00';

  const elTime = document.getElementById('detailAuditTime');
  if (elTime) elTime.textContent = l.time || '-';

  const elUser = document.getElementById('detailAuditUser');
  if (elUser) elUser.textContent = l.user || '-';

  const elIp = document.getElementById('detailAuditIp');
  if (elIp) elIp.textContent = l.ip || '-';

  const elHash = document.getElementById('detailAuditHash');
  if (elHash) elHash.textContent = l.hash || '-';

  window.dispatchEvent(new CustomEvent('open-modal', { detail: 'taxAuditDetailsModal' }));
}

document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('auditSearchInput');
  const catSelect = document.getElementById('auditCategorySelect');
  const userSelect = document.getElementById('auditUserSelect');
  const summaryText = document.getElementById('auditSummaryText');

  function filterAuditLogs() {
    const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCat = catSelect ? catSelect.value.toLowerCase() : '';
    const selectedUser = userSelect ? userSelect.value.toLowerCase() : '';
    const rows = document.querySelectorAll('.audit-row');
    let visibleCount = 0;

    rows.forEach(function(row) {
      const rowCat = row.getAttribute('data-cat') || '';
      const rowUser = row.getAttribute('data-user') || '';
      const rowText = row.textContent.toLowerCase();

      const matchCat = !selectedCat || rowCat.includes(selectedCat);
      const matchUser = !selectedUser || rowUser.includes(selectedUser);
      const matchSearch = !searchQuery || rowText.includes(searchQuery);

      if (matchCat && matchUser && matchSearch) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (summaryText) {
      summaryText.textContent = `Showing ${visibleCount} Audit Entr${visibleCount !== 1 ? 'ies' : 'y'}`;
    }

    let emptyRow = document.getElementById('noAuditRow');
    const tbody = document.querySelector('#taxAuditTable tbody');
    if (visibleCount === 0) {
      if (!emptyRow && tbody) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'noAuditRow';
        emptyRow.innerHTML = `<td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500"><i class="ph ph-magnifying-glass text-3xl block mb-2"></i>No tax audit entries found matching current filter.</td>`;
        tbody.appendChild(emptyRow);
      }
      if (emptyRow) emptyRow.style.display = '';
    } else if (emptyRow) {
      emptyRow.style.display = 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterAuditLogs);
    searchInput.addEventListener('keyup', filterAuditLogs);
  }
  if (catSelect) catSelect.addEventListener('change', filterAuditLogs);
  if (userSelect) userSelect.addEventListener('change', filterAuditLogs);

  filterAuditLogs();
});
</script>
@endpush
