@props(['settings' => null])
@php
    $phone = $settings?->phone ?? null;
    $wa = $settings?->whatsapp ?? $phone;
    $waLink = $wa ? ('https://wa.me/' . preg_replace('/[^0-9]/','',$wa)) : null;
@endphp

<div class="fixed inset-x-0 bottom-0 z-40 block md:hidden">
    <div class="px-3">
        <div class="glass-panel flex items-center justify-between gap-3 rounded-t-3xl bg-ink/92 p-3 shadow-2xl shadow-black/50">
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-ivory">{{ \App\Support\WebsiteContent::copy('mobile_cta.title', 'Ready to visit?') }}</p>
                <p class="truncate text-xs text-parchment/70">{{ \App\Support\WebsiteContent::copy('mobile_cta.subtitle', 'Call or send a WhatsApp message') }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                @if($phone)
                    <a href="tel:{{ $phone }}" class="rounded-full bg-gold px-4 py-2 text-sm font-extrabold text-ink">Call</a>
                @endif
                @if($waLink)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-extrabold text-white">WhatsApp</a>
                @endif
            </div>
        </div>
    </div>
</div>
