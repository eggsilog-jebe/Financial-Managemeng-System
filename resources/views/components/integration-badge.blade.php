@props([
    'type' => 'internal', // 'external', 'internal', 'standalone'
    'systems' => [],           // External IHIMS systems (e.g. BDMS, SPRS, HRMS, SWS, PSM)
    'internalModules' => [],   // Internal FMS modules/submodules (e.g. Invoices, Bank Accounts, GL)
    'tables' => [],            // MySQL database tables involved (e.g. payments, journal_entries)
    'glImpact' => null,        // Double-entry accounting triggers (e.g. DR 1020 / CR 1011)
    'description' => ''        // Concise architectural memo
])

@php
    $configs = [
        'external' => [
            'label' => 'External IHIMS Integrated',
            'bg' => 'background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe;',
            'dot' => 'background-color: #9333ea;',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>',
            'tag_bg' => 'background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;',
        ],
        'internal' => [
            'label' => 'Internal FMS Connected',
            'bg' => 'background-color: #ecfdf5; color: #065f46; border-color: #a7f3d0;',
            'dot' => 'background-color: #10b981;',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>',
            'tag_bg' => 'background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;',
        ],
        'standalone' => [
            'label' => 'Standalone Config',
            'bg' => 'background-color: #f8fafc; color: #334155; border-color: #cbd5e1;',
            'dot' => 'background-color: #64748b;',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>',
            'tag_bg' => 'background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
        ],
    ];

    $cfg = $configs[$type] ?? $configs['internal'];
@endphp

<div x-data="{ open: false }" class="position-relative d-inline-block text-start">
    <!-- Trigger Button -->
    <button 
        @mouseenter="open = true" 
        @mouseleave="open = false" 
        @click="open = !open"
        type="button" 
        class="btn btn-sm d-inline-flex align-items-center gap-2 rounded-pill px-3 py-1 border shadow-sm fs-xs fw-semibold"
        style="{{ $cfg['bg'] }}; cursor: help;"
        title="Click or hover to see how this page connects to other hospital systems"
    >
        <span class="rounded-circle d-inline-block" style="width: 7px; height: 7px; {{ $cfg['dot'] }}"></span>
        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $cfg['icon'] !!}
        </svg>
        <span>{{ $cfg['label'] }}</span>
    </button>

    <!-- Comprehensive Popover -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 transform -translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 transform -translate-y-1 scale-95"
        @mouseenter="open = true"
        @mouseleave="open = false"
        class="position-absolute end-0 z-3 mt-2 p-3 bg-white rounded-3 shadow-lg border text-start"
        style="width: 350px; font-size: 12px; line-height: 1.5; color: #475569; display: none;"
    >
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
            <span class="fw-bold text-dark"><i class="ph ph-cpu me-1 text-primary"></i>System &amp; Data Connections</span>
            <span class="badge px-2 py-0.5 rounded text-uppercase font-monospace" style="{{ $cfg['bg'] }}">{{ $type }}</span>
        </div>
        
        <!-- Summary Memo -->
        @if(!empty($description))
            <div class="bg-light p-2 rounded mb-2 text-dark" style="font-size: 11px; line-height: 1.4;">
                <i class="ph ph-info me-1 text-primary"></i> {{ $description }}
            </div>
        @endif

        <!-- External IHIMS Integrations -->
        @if(!empty($systems))
            <div class="mb-2">
                <span class="fw-semibold text-dark d-block mb-1" style="font-size: 11px;">
                    {{ $type === 'external' ? 'External Hospital Systems Connected:' : 'Connected Data Sources:' }}
                </span>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($systems as $sys)
                        <span class="px-2 py-0.5 rounded font-monospace fw-medium" style="font-size: 10px; background-color: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;">{{ $sys }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Internal FMS Connected Submodules -->
        @if(!empty($internalModules))
            <div class="mb-2">
                <span class="fw-semibold text-dark d-block mb-1" style="font-size: 11px;">Internal Finance Modules Connected:</span>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($internalModules as $mod)
                        <span class="px-2 py-0.5 rounded fw-medium" style="font-size: 10px; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">{{ $mod }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Database Tables Involved -->
        @if(!empty($tables))
            <div class="mb-2">
                <span class="fw-semibold text-dark d-block mb-1" style="font-size: 11px;">Active Database Tables:</span>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($tables as $tbl)
                        <span class="px-1.5 py-0.5 rounded font-monospace" style="font-size: 10px; background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">{{ $tbl }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- General Ledger Impact -->
        @if(!empty($glImpact))
            <div class="pt-2 border-top" style="font-size: 11px;">
                <span class="fw-semibold text-dark d-block mb-1"><i class="ph ph-scales me-1 text-success"></i> Automatic General Ledger (GL) Impact:</span>
                <div class="p-2 rounded font-monospace text-dark" style="font-size: 10.5px; background-color: #f8fafc; border: 1px solid #e2e8f0; word-break: break-word;">
                    {{ $glImpact }}
                </div>
            </div>
        @endif
    </div>
</div>
