@props([
    'headers' => [],
    'title' => null,
    'subtitle' => null,
    'emptyMessage' => 'No records found matching your criteria.',
    'emptyIcon' => 'ph-receipt',
    'hasRecords' => true,
    'pagination' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-900 dark:ring-slate-800']) }}>
    @if(isset($header) || $title)
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            @if(isset($actions))
                <div class="flex items-center gap-2 flex-wrap">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
            @if(!empty($headers))
                <thead class="border-b border-slate-200 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400 sticky top-0 z-10 backdrop-blur-sm">
                    <tr>
                        @foreach($headers as $h)
                            @php
                                $isString = is_string($h);
                                $label = $isString ? $h : ($h['label'] ?? '');
                                $align = $isString ? 'left' : ($h['align'] ?? 'left');
                                $width = $isString ? '' : ($h['width'] ?? '');
                                $alignClass = match($align) {
                                    'right' => 'text-right font-mono',
                                    'center' => 'text-center',
                                    default => 'text-left',
                                };
                            @endphp
                            <th scope="col" class="py-3.5 px-4 {{ $alignClass }}" @if($width) style="width: {{ $width }}" @endif>
                                {{ $label }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @if($hasRecords)
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($headers) ?: 10 }}" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                            <i class="ph {{ $emptyIcon }} text-3xl text-slate-400 dark:text-slate-500 mb-2 block mx-auto"></i>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($pagination || isset($footer))
        <div class="border-t border-slate-200 bg-slate-50/50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/50">
            @if(isset($footer))
                {{ $footer }}
            @elseif($pagination)
                <div class="flex items-center justify-between">
                    {{ $pagination }}
                </div>
            @endif
        </div>
    @endif
</div>
