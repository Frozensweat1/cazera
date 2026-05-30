@props([
    'danger' => false,
    'icon' => null,
    'href' => null,
])

@php
    $icon = $icon ?: ($danger ? 'trash' : null);

    $baseStyle = 'transition: background-color 150ms ease, color 150ms ease, transform 150ms ease;';
    $style = $danger
        ? $baseStyle . ' color: #dc2626 !important;'
        : $baseStyle . ' color: #374151 !important;';

    $hoverIn = $danger
        ? "this.style.backgroundColor='#fef2f2'; this.style.color='#b91c1c'; this.style.transform='translateX(2px)'"
        : "this.style.backgroundColor='#f8fafc'; this.style.color='#111827'; this.style.transform='translateX(2px)'";

    $hoverOut = $danger
        ? "this.style.backgroundColor='transparent'; this.style.color='#dc2626'; this.style.transform='translateX(0)'"
        : "this.style.backgroundColor='transparent'; this.style.color='#374151'; this.style.transform='translateX(0)'";

    $iconStyle = $danger
        ? 'color: #dc2626 !important;'
        : 'color: #6b7280 !important;';

@endphp

@php
    $mergedAttributes = $attributes->merge([
        'class' => $danger
            ? 'group flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold !text-red-600 hover:!bg-red-50 hover:!text-red-700 focus:outline-none focus:!bg-red-50 focus:!text-red-700 dark:hover:!bg-red-950/40'
            : 'group flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-gray-950 focus:outline-none focus:bg-gray-50 focus:text-gray-950 dark:text-gray-200 dark:hover:bg-white/10 dark:hover:text-white',
        'style' => $style,
        'onmouseenter' => $hoverIn,
        'onmouseleave' => $hoverOut,
        'role' => 'menuitem',
        'x-on:click' => 'open = false',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $mergedAttributes }}>
        @if ($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" @class([
                'h-4 w-4 shrink-0 transition-colors',
                '!text-red-600 group-hover:!text-red-700' => $danger,
                'text-gray-500 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white' => ! $danger,
            ]) style="{{ $iconStyle }}" />
        @endif

        <span class="truncate">
            {{ $slot }}
        </span>
    </a>
@else
    <button type="button" {{ $mergedAttributes }}>
        @if ($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" @class([
                'h-4 w-4 shrink-0 transition-colors',
                '!text-red-600 group-hover:!text-red-700' => $danger,
                'text-gray-500 group-hover:text-gray-900 dark:text-gray-300 dark:group-hover:text-white' => ! $danger,
            ]) style="{{ $iconStyle }}" />
        @endif

        <span class="truncate">
            {{ $slot }}
        </span>
    </button>
@endif
