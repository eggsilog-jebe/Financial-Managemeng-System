@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed dark:bg-slate-800 dark:text-slate-600">
                <i class="ph-bold ph-caret-left text-sm"></i>
                <span>{!! __('pagination.previous') !!}</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                <i class="ph-bold ph-caret-left text-sm"></i>
                <span>{!! __('pagination.previous') !!}</span>
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700">
                <span>{!! __('pagination.next') !!}</span>
                <i class="ph-bold ph-caret-right text-sm"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed dark:bg-slate-800 dark:text-slate-600">
                <span>{!! __('pagination.next') !!}</span>
                <i class="ph-bold ph-caret-right text-sm"></i>
            </span>
        @endif
    </nav>
@endif
