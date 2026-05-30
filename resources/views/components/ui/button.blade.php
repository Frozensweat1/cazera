@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'loadingText' => 'Processing...',
    'target' => null,
    'block' => false,
])

@php

    $variants = [
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'success' => 'btn btn-success',
        'danger' => 'btn btn-danger',
        'warning' => 'btn btn-warning',
        'info' => 'btn btn-info',
        'dark' => 'btn btn-dark',
        'outline-danger' => 'btn btn-outline-danger',
    ];

    $sizes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];

    $sizeClass = $sizes[$size] ?? '';

    $blockClass = $block ? 'w-full justify-center' : '';

    $buttonClasses = "{$variantClass} {$sizeClass} {$blockClass} inline-flex items-center gap-2";

    /*
    |--------------------------------------------------------------------------
    | Resolve Livewire Target
    |--------------------------------------------------------------------------
    */

    $resolvedTarget = $target;

    if (!$resolvedTarget && isset($attributes['wire:click'])) {
        $resolvedTarget = $attributes['wire:click'];
    }

@endphp

<button type="{{ $type }}" @if ($resolvedTarget) wire:target="{{ $resolvedTarget }}" wire:loading.attr="disabled" @endif
    {{ $attributes->merge([
        'class' => $buttonClasses,
    ]) }}>

    @if ($resolvedTarget)
        <!-- LOADING SPINNER -->
        <span wire:loading.flex wire:target="{{ $resolvedTarget }}" class="items-center">

            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">

                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>

                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>

            </svg>

        </span>
    @endif

    <!-- ICON -->
    @if ($icon)
        <span @if ($resolvedTarget) wire:loading.remove wire:target="{{ $resolvedTarget }}" @endif>

            <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-4 h-4" />

        </span>
    @endif

    <!-- NORMAL TEXT -->
    <span @if ($resolvedTarget) wire:loading.remove wire:target="{{ $resolvedTarget }}" @endif>
        {{ $slot }}
    </span>

    <!-- LOADING TEXT -->
    @if ($resolvedTarget)
        <span wire:loading.inline wire:target="{{ $resolvedTarget }}">
            {{ $loadingText }}
        </span>
    @endif

</button>
