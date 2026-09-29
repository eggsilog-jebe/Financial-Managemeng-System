@props([
    'status' => 'PENDING',
    'label' => null,
    'icon' => null,
    'variant' => null, // Override auto-detection: emerald, amber, rose, blue, purple, teal, sky, indigo, slate
])

@php
    $normalized = strtoupper(trim((string) $status));
    $displayLabel = $label ?? ucwords(strtolower(str_replace('_', ' ', (string) $status)));

    // Auto-detect variant from status keyword if not explicitly provided
    $resolvedVariant = $variant ?? match (true) {
        in_array($normalized, ['SETTLED', 'POSTED', 'CLEARED', 'PAID', 'APPROVED', 'MATCHED', 'ACTIVE', 'HEALTHY', 'COMPLIANT']) => 'emerald',
        in_array($normalized, ['PENDING', 'DRAFT', 'IN_REVIEW', 'OPEN', 'PARTIAL', 'REMITTANCE DUE', 'MODERATE']) => 'amber',
        in_array($normalized, ['OVERDUE', 'REVERSED', 'VOIDED', 'CRITICAL', 'UNBALANCED', 'DISCREPANCY', 'REJECTED']) => 'rose',
        in_array($normalized, ['PHILHEALTH', 'ACR', 'CLAIM', 'UHC']) => 'blue',
        in_array($normalized, ['SENIOR', 'PWD', 'SENIOR_CITIZEN', 'RA_9994', 'RA_10754']) => 'purple',
        in_array($normalized, ['MALASAKIT', 'MAIP', 'PCSO', 'DSWD', 'GL', 'RA_11463']) => 'teal',
        in_array($normalized, ['HMO', 'INSURANCE', 'LOA']) => 'sky',
        in_array($normalized, ['TAX', 'BIR', 'BIR_2307', 'EWT', 'VAT']) => 'indigo',
        default => 'slate',
    };

    $variantClasses = match ($resolvedVariant) {
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-500/30',
        'amber'   => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-500/30',
        'rose'    => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-300 dark:ring-rose-500/30',
        'blue'    => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-500/30',
        'purple'  => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-950/40 dark:text-purple-300 dark:ring-purple-500/30',
        'teal'    => 'bg-teal-50 text-teal-700 ring-teal-600/20 dark:bg-teal-950/40 dark:text-teal-300 dark:ring-teal-500/30',
        'sky'     => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-950/40 dark:text-sky-300 dark:ring-sky-500/30',
        'indigo'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-950/40 dark:text-indigo-300 dark:ring-indigo-500/30',
        default   => 'bg-slate-100 text-slate-700 ring-slate-300/40 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };

    // Auto-detect default icon if not explicitly provided
    $resolvedIcon = $icon ?? match ($resolvedVariant) {
        'emerald' => 'ph-check-circle',
        'amber'   => 'ph-clock',
        'rose'    => 'ph-warning-circle',
        'blue'    => 'ph-heartbeat',
        'purple'  => 'ph-user-check',
        'teal'    => 'ph-hands-clapping',
        'sky'     => 'ph-shield-plus',
        'indigo'  => 'ph-file-text',
        default   => null,
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ' . $variantClasses]) }}>
    @if($resolvedIcon)
        <i class="ph-bold {{ str_starts_with($resolvedIcon, 'ph-') ? $resolvedIcon : 'ph-' . $resolvedIcon }}"></i>
    @endif
    <span>{{ $displayLabel }}</span>
</span>
