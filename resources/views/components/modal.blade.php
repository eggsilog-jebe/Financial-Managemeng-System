@props([
    'id',
    'title' => 'Modal Title',
    'subtitle' => null,
    'icon' => null,
    'iconVariant' => 'primary',
    'size' => 'md',
    'scrollable' => true,
    'centered' => true,
    'staticBackdrop' => false,
    'formAction' => null,
    'formMethod' => 'POST',
    'formId' => null,
    'formEnctype' => null,
    'submitText' => 'Save Changes',
    'submitIcon' => 'ph-check',
    'submitVariant' => 'btn-primary',
    'submitId' => null,
    'cancelText' => 'Cancel',
    'showFooter' => true,
])

@php
    $sizeClass = match($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };

    $dialogClasses = trim(implode(' ', array_filter([
        'modal-dialog',
        $sizeClass,
        $centered ? 'modal-dialog-centered' : null,
        $scrollable ? 'modal-dialog-scrollable' : null,
    ])));

    $normalizedMethod = strtoupper($formMethod);
    $spoofMethod = in_array($normalizedMethod, ['PUT', 'PATCH', 'DELETE']) ? $normalizedMethod : null;
    $htmlMethod = $spoofMethod ? 'POST' : $normalizedMethod;

    $badgeVariantClass = match($iconVariant) {
        'success' => 'bg-success-subtle text-success border-success-subtle',
        'warning' => 'bg-warning-subtle text-warning border-warning-subtle',
        'danger'  => 'bg-danger-subtle text-danger border-danger-subtle',
        'info'    => 'bg-info-subtle text-info border-info-subtle',
        'teal'    => 'bg-teal-subtle text-teal border-teal-subtle',
        'secondary' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        default   => 'bg-primary-subtle text-primary border-primary-subtle',
    };
@endphp

<div 
    class="modal fade" 
    id="{{ $id }}" 
    tabindex="-1" 
    aria-labelledby="{{ $id }}Label" 
    aria-hidden="true"
    @if($staticBackdrop) data-bs-backdrop="static" data-bs-keyboard="false" @endif
    {{ $attributes }}
>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content border-0 shadow-lg hims-modal__content">
            @if($formAction)
                <form 
                    action="{{ $formAction }}" 
                    method="{{ $htmlMethod }}" 
                    @if($formId) id="{{ $formId }}" @endif
                    @if($formEnctype) enctype="{{ $formEnctype }}" @endif
                    class="hims-modal__form d-flex flex-column h-100"
                >
                    @csrf
                    @if($spoofMethod)
                        @method($spoofMethod)
                    @endif
            @endif

            <!-- Modal Header -->
            <div class="modal-header hims-modal__header bg-light-subtle border-bottom px-4 py-3 align-items-center">
                <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden">
                    @if($icon)
                        <span class="hims-modal__icon-badge p-2 rounded-3 border d-inline-flex align-items-center justify-content-center flex-shrink-0 {{ $badgeVariantClass }}">
                            <i class="ph {{ $icon }} fs-4"></i>
                        </span>
                    @endif
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-5 lh-sm text-truncate" id="{{ $id }}Label">
                            {{ $title }}
                        </h5>
                        @if($subtitle)
                            <p class="text-muted fs-xs mb-0 mt-1 lh-sm text-truncate">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body hims-modal__body p-4">
                {{ $slot }}
            </div>

            <!-- Modal Footer -->
            @if($showFooter)
                <div class="modal-footer hims-modal__footer bg-light-subtle border-top px-4 py-3 d-flex align-items-center justify-content-between">
                    <div class="hims-modal__footer-start d-flex align-items-center gap-2">
                        @if(isset($footerStart))
                            {{ $footerStart }}
                        @endif
                    </div>
                    <div class="hims-modal__footer-actions d-flex align-items-center gap-2">
                        @if(isset($footer))
                            {{ $footer }}
                        @else
                            <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">
                                {{ $cancelText }}
                            </button>
                            @if($formAction)
                                <button type="submit" @if($submitId) id="{{ $submitId }}" @endif class="btn btn-sm {{ $submitVariant }} px-3 d-inline-flex align-items-center gap-1 hims-btn-submit">
                                    @if($submitIcon)
                                        <i class="ph {{ $submitIcon }}"></i>
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
