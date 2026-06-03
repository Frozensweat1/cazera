@props(['label' => 'Export PDF', 'href' => null])

<div {{ $attributes->merge(['class' => 'report-print-hidden flex justify-end']) }}>
    @if ($href)
        <a href="{{ $href }}"
            target="_blank"
            rel="noopener"
            class="report-export-button">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <path d="M14 3H7.75A2.75 2.75 0 0 0 5 5.75v12.5A2.75 2.75 0 0 0 7.75 21h8.5A2.75 2.75 0 0 0 19 18.25V8l-5-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M14 3v4.25A.75.75 0 0 0 14.75 8H19" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M12 11v5m0 0 2.25-2.25M12 16l-2.25-2.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>{{ $label }}</span>
        </a>
    @else
        <button type="button"
            x-data
            x-on:click="window.print()"
            class="report-export-button">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <path d="M14 3H7.75A2.75 2.75 0 0 0 5 5.75v12.5A2.75 2.75 0 0 0 7.75 21h8.5A2.75 2.75 0 0 0 19 18.25V8l-5-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M14 3v4.25A.75.75 0 0 0 14.75 8H19" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M12 11v5m0 0 2.25-2.25M12 16l-2.25-2.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>{{ $label }}</span>
        </button>
    @endif
</div>
