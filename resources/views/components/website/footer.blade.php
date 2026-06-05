@props(['settings' => null])
@php
    $name = $settings?->business_name ?? \App\Support\WebsiteContent::copy('brand.name', 'Cazera');
    $footerEyebrow = \App\Support\WebsiteContent::copy('footer.eyebrow', 'Hospitality showcase');
    $footerDescription = \App\Support\WebsiteContent::copy('footer.description', $settings?->tagline ?? 'Cinematic hospitality experiences across dining, lounges, events and memorable nights out.');
    $links = [
        ['About', route('website.about')],
        ['Branches', route('website.branches')],
        ['Gallery', route('website.gallery')],
        ['Reviews', route('website.reviews')],
        ['Events', route('website.events')],
        ['Careers', route('website.careers')],
        ['Contact', route('website.contact')],
    ];
@endphp

<footer class="border-t border-ivory/10 bg-ink pb-24 pt-16 text-ivory/72 md:pb-8">
    <div class="luxury-container">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr_0.8fr]">
            <div>
                <p class="eyebrow">{{ $footerEyebrow }}</p>
                <h2 class="mt-3 font-serif text-4xl font-semibold text-ivory">{{ $name }}</h2>
                <p class="mt-4 max-w-xl leading-7 text-parchment/75">{{ $footerDescription }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'tiktok_url' => 'TikTok', 'x_url' => 'X'] as $field => $label)
                        @if ($settings?->{$field})
                            <a href="{{ $settings->{$field} }}" target="_blank" rel="noopener" aria-label="{{ $label }}"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-ivory/12 text-ivory/78 transition hover:border-gold/60 hover:text-gold">
                                @switch($field)
                                    @case('facebook_url')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.5V6.75c0-.5.4-.9.9-.9H17V2.25h-3.05A4.95 4.95 0 0 0 9 7.2v1.3H6.5V12H9v9.75h4V12h3.1l.55-3.5H13Z"/></svg>
                                        @break
                                    @case('instagram_url')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.4" fill="currentColor"/></svg>
                                        @break
                                    @case('youtube_url')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a3 3 0 0 0-2.1-2.12C17.64 4.58 12 4.58 12 4.58s-5.64 0-7.5.5A3 3 0 0 0 2.4 7.2 31.24 31.24 0 0 0 1.9 12a31.24 31.24 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.12c1.86.5 7.5.5 7.5.5s5.64 0 7.5-.5a3 3 0 0 0 2.1-2.12 31.24 31.24 0 0 0 .5-4.8 31.24 31.24 0 0 0-.5-4.8ZM10 15.5v-7l6 3.5-6 3.5Z"/></svg>
                                        @break
                                    @case('tiktok_url')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 3c.35 2.25 1.62 3.6 3.9 3.75v3.45a7.15 7.15 0 0 1-3.85-1.16v6.58A6.08 6.08 0 1 1 10.58 9.6c.37 0 .73.03 1.08.1v3.65a2.44 2.44 0 1 0 1.62 2.3V3h3.32Z"/></svg>
                                        @break
                                    @default
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.53 3h3.15l-6.88 7.86L21.9 21h-6.34l-4.96-6.49L4.92 21H1.75l7.36-8.41L1.35 3h6.5l4.49 5.94L17.53 3Zm-1.1 16.22h1.75L6.9 4.69H5.02l11.41 14.53Z"/></svg>
                                @endswitch
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="font-serif text-2xl font-semibold text-ivory">Explore</h3>
                <nav class="mt-5 grid grid-cols-2 gap-3 text-sm" aria-label="Footer navigation">
                    @foreach ($links as [$label, $url])
                        <a href="{{ $url }}" class="transition hover:text-gold">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>

            <div>
                <h3 class="font-serif text-2xl font-semibold text-ivory">Contact</h3>
                <div class="mt-5 space-y-3 text-sm leading-6">
                    @if ($settings?->address)
                        <p>{{ $settings->address }}</p>
                    @endif
                    @if ($settings?->phone)
                        <p><a href="tel:{{ $settings->phone }}" class="text-gold hover:text-parchment">{{ $settings->phone }}</a></p>
                    @endif
                    @if ($settings?->email)
                        <p><a href="mailto:{{ $settings->email }}" class="text-gold hover:text-parchment">{{ $settings->email }}</a></p>
                    @endif
                    @if ($settings?->google_map_url)
                        <p><a href="{{ $settings->google_map_url }}" target="_blank" rel="noopener" class="font-bold text-ivory hover:text-gold">Open map</a></p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-4 border-t border-ivory/10 pt-6 text-xs text-ivory/50 md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ date('Y') }} {{ $name }}. All rights reserved.</p>
            <p>{{ \App\Support\WebsiteContent::copy('footer.bottom_note', 'Built for discovery, direct calls, WhatsApp conversations and memorable visits.') }}</p>
        </div>
    </div>
</footer>
