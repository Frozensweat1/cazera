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
                            <a href="{{ $settings->{$field} }}" target="_blank" rel="noopener" class="rounded-full border border-ivory/12 px-4 py-2 text-sm font-semibold text-ivory/78 transition hover:border-gold/60 hover:text-gold">{{ $label }}</a>
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
