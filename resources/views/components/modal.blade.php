@props([
    'id',
    'title' => 'Modal Title',
    'subtitle' => null,
    'icon' => null,
    'iconVariant' => 'emerald', // emerald, blue, amber, rose, purple, teal, slate
    'size' => 'md', // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl
    'maxWidth' => null,
    'formAction' => null,
    'formMethod' => 'POST',
    'formId' => null,
    'formEnctype' => null,
    'submitText' => 'Save Changes',
    'submitIcon' => 'ph-check',
    'submitVariant' => 'emerald', // emerald, amber, rose, blue, slate
    'submitId' => null,
    'cancelText' => 'Cancel',
    'showFooter' => true,
    'scrollable' => true,
])

@php
    $maxWClass = match($maxWidth ?? $size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        '2xl' => 'max-w-5xl',
        '3xl', '4xl', '5xl' => 'max-w-6xl',
        default => 'max-w-xl',
    };

    $normalizedMethod = strtoupper($formMethod);
    $spoofMethod = in_array($normalizedMethod, ['PUT', 'PATCH', 'DELETE']) ? $normalizedMethod : null;
    $htmlMethod = $spoofMethod ? 'POST' : $normalizedMethod;

    $badgeVariantClass = match($iconVariant) {
        'success', 'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-500/20 dark:bg-emerald-950/40 dark:text-emerald-300',
        'warning', 'amber'   => 'bg-amber-50 text-amber-600 ring-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300',
        'danger', 'rose'     => 'bg-rose-50 text-rose-600 ring-rose-500/20 dark:bg-rose-950/40 dark:text-rose-300',
        'info', 'blue'       => 'bg-blue-50 text-blue-600 ring-blue-500/20 dark:bg-blue-950/40 dark:text-blue-300',
        'purple'             => 'bg-purple-50 text-purple-600 ring-purple-500/20 dark:bg-purple-950/40 dark:text-purple-300',
        'teal'               => 'bg-teal-50 text-teal-600 ring-teal-500/20 dark:bg-teal-950/40 dark:text-teal-300',
        default              => 'bg-slate-100 text-slate-700 ring-slate-300/40 dark:bg-slate-800 dark:text-slate-300',
    };

    $submitBtnClass = match($submitVariant) {
        'warning', 'amber' => 'bg-amber-600 hover:bg-amber-700 text-white shadow-sm ring-1 ring-amber-600/20',
        'danger', 'rose'   => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm ring-1 ring-rose-600/20',
        'blue'             => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm ring-1 ring-blue-600/20',
        'slate', 'dark'    => 'bg-slate-800 hover:bg-slate-900 text-white shadow-sm ring-1 ring-slate-700',
        default            => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm ring-1 ring-emerald-600/20',
    };
@endphp

<div 
    x-data="{ open: false }" 
    x-init="
        document.querySelectorAll('[data-bs-target=\'#{{ $id }}\'], [data-target=\'#{{ $id }}\']').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                open = true;
            });
        });
    "
    @open-modal.window="if ($event.detail === '{{ $id }}' || (typeof $event.detail === 'object' && $event.detail.id === '{{ $id }}')) open = true"
    @close-modal.window="if ($event.detail === '{{ $id }}' || (typeof $event.detail === 'object' && $event.detail.id === '{{ $id }}')) open = false"
    @keydown.escape.window="open = false"
    id="{{ $id }}"
>
    @if(isset($trigger))
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @endif

    <!-- Modal Backdrop & Dialog Container -->
    <div 
        x-show="open" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="{{ $id }}Label" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop Blur -->
        <div 
            x-show="open" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
        ></div>

        <!-- Window Centering Wrapper -->
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-10 flex min-h-full items-center justify-center">
            <div 
                x-show="open" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="open = false" 
                class="w-full {{ $maxWClass }} transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl ring-1 ring-slate-200/80 transition-all dark:bg-slate-900 dark:ring-slate-800"
            >
                @if($formAction)
                    <form 
                        action="{{ $formAction }}" 
                        method="{{ $htmlMethod }}" 
                        @if($formId) id="{{ $formId }}" @endif
                        @if($formEnctype) enctype="{{ $formEnctype }}" @endif
                        class="flex flex-col h-full"
                    >
                        @csrf
                        @if($spoofMethod)
                            @method($spoofMethod)
                        @endif
                @endif

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3 overflow-hidden">
                        @if($icon)
                            <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl ring-1 {{ $badgeVariantClass }}">
                                <i class="ph-bold {{ str_starts_with($icon, 'ph-') ? $icon : 'ph-' . $icon }} text-xl"></i>
                            </span>
                        @endif
                        <div class="truncate">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white truncate" id="{{ $id }}Label">
                                {{ $title }}
                            </h3>
                            @if($subtitle)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <button 
                        @click="open = false" 
                        type="button" 
                        class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors"
                        aria-label="Close modal"
                    >
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 {{ $scrollable ? 'max-h-[calc(85vh-160px)] overflow-y-auto custom-scrollbar' : '' }}">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if($showFooter)
                    <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/50">
                        <div class="flex items-center gap-2">
                            @if(isset($footerStart))
                                {{ $footerStart }}
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if(isset($footer))
                                {{ $footer }}
                            @else
                                <button 
                                    @click="open = false" 
                                    type="button" 
                                    class="rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 transition-all"
                                >
                                    {{ $cancelText }}
                                </button>
                                @if($formAction)
                                    <button 
                                        type="submit" 
                                        @if($submitId) id="{{ $submitId }}" @endif 
                                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition-all {{ $submitBtnClass }}"
                                    >
                                        @if($submitIcon)
                                            <i class="ph-bold {{ str_starts_with($submitIcon, 'ph-') ? $submitIcon : 'ph-' . $submitIcon }}"></i>
                                        @endif
                                        <span>{{ $submitText }}</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                @if($formAction)
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
