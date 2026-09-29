@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        {{-- Mobile Simple Pagination --}}
        <div class="flex flex-1 items-center justify-between sm:hidden gap-2">
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

            <span class="font-mono text-xs text-slate-500 dark:text-slate-400">
                Page <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginator->currentPage() }}</span> / {{ $paginator->lastPage() }}
            </span>

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
        </div>

        {{-- Desktop Full Pagination with Results Counter --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span>
                    {!! __('records') !!}
                </p>
            </div>

            <div>
                <nav class="inline-flex items-center gap-1 rounded-xl p-0.5" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                            <i class="ph-bold ph-caret-left text-sm"></i>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 hover:text-slate-900 transition-colors dark:text-slate-400 dark:ring-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="{{ __('pagination.previous') }}">
                            <i class="ph-bold ph-caret-left text-sm"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="inline-flex h-8 w-8 items-center justify-center text-xs font-bold text-slate-400">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex h-8 min-w-[2rem] px-2 items-center justify-center rounded-lg bg-emerald-600 text-xs font-mono font-bold text-white shadow-sm ring-1 ring-emerald-600/30">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex h-8 min-w-[2rem] px-2 items-center justify-center rounded-lg text-xs font-mono font-medium text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-colors dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 hover:text-slate-900 transition-colors dark:text-slate-400 dark:ring-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="{{ __('pagination.next') }}">
                            <i class="ph-bold ph-caret-right text-sm"></i>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                            <i class="ph-bold ph-caret-right text-sm"></i>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>
@endif
