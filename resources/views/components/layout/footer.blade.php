@props(['settings' => \App\Support\WebsiteContent::settings()])

@php
    $brandName = $settings?->business_name ?: config('app.name', 'Cazera');
    $year = now()->year;
@endphp

<footer class="mt-auto p-6 pt-0 text-center text-xs text-slate-500 dark:text-white-dark sm:text-sm ltr:sm:text-left rtl:sm:text-right">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; <span id="footer-year">{{ $year }}</span> {{ $brandName }}. All rights reserved.</p>
        <p>
            Built by
            <span class="font-semibold text-slate-700 dark:text-white">FrozenBytes</span>
        </p>
    </div>
</footer>
