@props(['settings' => null])
@php
    $wa = $settings?->whatsapp ?? $settings?->phone ?? null;
    $waLink = $wa ? ('https://wa.me/' . preg_replace('/[^0-9]/','',$wa)) : null;
@endphp

@if($waLink)
    <a href="{{ $waLink }}" aria-label="Message us on WhatsApp" target="_blank" rel="noopener" class="fixed bottom-8 right-5 z-50 hidden h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl shadow-emerald-950/40 transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-emerald-200 md:inline-flex">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11.9 11.9 0 0012 0C5.37 0 .01 5.37 0 12.01 0 14.11.5 16.09 1.5 17.79L0 24l6.41-1.69A11.94 11.94 0 0012 24c6.63 0 12-5.37 12-11.99 0-3.2-1.24-6.18-3.5-8.51zM12 21.5c-1.72 0-3.37-.46-4.82-1.33l-.34-.2-3.82 1.01 1.02-3.73-.21-.36A9.53 9.53 0 012.5 12c0-5.25 4.25-9.5 9.5-9.5s9.5 4.25 9.5 9.5-4.25 9.5-9.5 9.5z"/></svg>
    </a>
@endif
