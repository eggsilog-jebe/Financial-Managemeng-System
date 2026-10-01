@props([
    'action' => '',
    'searchName' => 'search',
    'searchValue' => request('search', request('q', '')),
    'placeholder' => 'Search records, references...',
    'hasDateRange' => false,
    'startDateName' => 'start_date',
    'startDateValue' => request('start_date', ''),
    'endDateName' => 'end_date',
    'endDateValue' => request('end_date', ''),
    'exportUrl' => null,
    'exportLabel' => 'Export CSV',
    'resetUrl' => null,
])

<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between w-full']) }}>
    <!-- Left: Search and Filters -->
    <div class="flex flex-1 flex-col gap-2.5 sm:flex-row sm:items-center flex-wrap">
        <!-- Search Input -->
        <div class="relative w-full sm:w-72 md:w-80">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <i class="ph ph-magnifying-glass text-base"></i>
            </div>
            <input 
                type="search" 
                name="{{ $searchName }}" 
                value="{{ $searchValue }}" 
                placeholder="{{ $placeholder }}" 
                style="outline: none !important;"
                class="w-full rounded-xl border-0 bg-slate-50 py-2 pl-10 pr-4 text-xs sm:text-sm text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-emerald-600 focus:outline-none focus:outline-0 outline-none dark:bg-slate-800 dark:text-white dark:ring-slate-700 transition-all"
            >
        </div>

        @if($hasDateRange)
            <div class="flex items-center gap-2">
                <input 
                    type="date" 
                    name="{{ $startDateName }}" 
                    value="{{ $startDateValue }}" 
                    class="rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs sm:text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
                    title="Start Date"
                >
                <span class="text-xs text-slate-400">to</span>
                <input 
                    type="date" 
                    name="{{ $endDateName }}" 
                    value="{{ $endDateValue }}" 
                    class="rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs sm:text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:bg-white focus:ring-2 focus:ring-emerald-600 dark:bg-slate-800 dark:text-white dark:ring-slate-700"
                    title="End Date"
                >
            </div>
        @endif

        {{ $slot }}

        <button 
            type="submit" 
            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition-all"
        >
            <i class="ph-bold ph-funnel text-xs"></i>
            <span>Filter</span>
        </button>

        @if($resetUrl || request()->hasAny([$searchName, $startDateName, $endDateName, 'status', 'type', 'category']))
            <a 
                href="{{ $resetUrl ?? url()->current() }}" 
                class="inline-flex items-center gap-1 rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white transition-colors"
                title="Reset Filters"
            >
                <i class="ph ph-x"></i>
                <span>Reset</span>
            </a>
        @endif
    </div>

    <!-- Right: Export / Secondary actions -->
    @if($exportUrl || isset($actions))
        <div class="flex items-center gap-2 flex-wrap justify-end">
            {{ $actions ?? '' }}

            @if($exportUrl)
                <a 
                    href="{{ $exportUrl }}" 
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 transition-all"
                >
                    <i class="ph-bold ph-download-simple text-sm text-slate-500 dark:text-slate-400"></i>
                    <span>{{ $exportLabel }}</span>
                </a>
            @endif
        </div>
    @endif
</form>
