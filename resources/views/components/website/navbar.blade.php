@props(['settings' => null])
@php
    $links = [
        ['label' => 'Home', 'route' => 'website.home'],
        ['label' => 'Branches', 'route' => 'website.branches'],
        ['label' => 'Gallery', 'route' => 'website.gallery'],
        ['label' => 'Reviews', 'route' => 'website.reviews'],
        ['label' => 'Events', 'route' => 'website.events'],
        ['label' => 'Careers', 'route' => 'website.careers'],
        ['label' => 'Contact', 'route' => 'website.contact'],
    ];
    $phone = $settings?->phone;
    $brandName = $settings?->business_name ?? \App\Support\WebsiteContent::copy('brand.name', config('app.name', 'Cazera'));
    $tagline = $settings?->tagline ?? \App\Support\WebsiteContent::copy('brand.tagline', 'Hospitality, staged beautifully');
@endphp

<header x-data="{ open: false, compact: false }" x-init="compact = window.scrollY > 24; window.addEventListener('scroll', () => compact = window.scrollY > 24)" class="fixed inset-x-0 top-0 z-50">
    <div class="luxury-container pt-3">
        <div class="glass-panel rounded-full px-4 py-3 transition-all duration-300 md:px-6" :class="compact ? 'bg-ink/85 shadow-2xl shadow-black/30' : 'bg-ink/35'">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('website.home') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ $brandName }} home">
                    @if ($settings?->logo)
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $brandName }}" class="h-10 w-10 rounded-full border border-ivory/15 bg-ivory/5 object-contain p-1.5">
                    @else
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-gold/40 bg-gold/15 font-serif text-lg font-bold text-gold">C</span>
                    @endif
                    <span class="min-w-0">
                        <span class="block truncate font-serif text-xl font-semibold leading-none text-ivory">{{ $brandName }}</span>
                        <span class="hidden text-[0.65rem] font-bold uppercase tracking-[0.22em] text-parchment/70 sm:block">{{ $tagline }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
                    @foreach ($links as $link)
                        <a href="{{ route($link['route']) }}" class="rounded-full px-4 py-2 text-sm font-semibold text-ivory/76 transition hover:bg-ivory/8 hover:text-ivory focus:outline-none focus:ring-2 focus:ring-gold/70">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    @if ($phone)
                        <a href="tel:{{ $phone }}" class="hidden rounded-full bg-gold px-5 py-2.5 text-sm font-extrabold text-ink transition hover:bg-parchment focus:outline-none focus:ring-2 focus:ring-gold/70 sm:inline-flex">Call Now</a>
                    @endif
                    <button type="button" @click="open = ! open" class="inline-grid h-11 w-11 place-items-center rounded-full border border-ivory/15 text-ivory transition hover:bg-ivory/10 focus:outline-none focus:ring-2 focus:ring-gold/70 lg:hidden" :aria-expanded="open.toString()" aria-controls="mobile-menu">
                        <span class="sr-only">Toggle menu</span>
                        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg x-cloak x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" x-cloak x-show="open" x-transition.origin.top class="mt-3 overflow-hidden rounded-3xl border border-ivory/10 bg-ink/95 p-3 shadow-2xl shadow-black/40 lg:hidden">
            <nav class="grid gap-1" aria-label="Mobile navigation">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="rounded-2xl px-4 py-3 text-sm font-semibold text-ivory/82 hover:bg-ivory/8 hover:text-ivory">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('website.about') }}" class="rounded-2xl px-4 py-3 text-sm font-semibold text-ivory/82 hover:bg-ivory/8 hover:text-ivory">About</a>
            </nav>
        </div>
    </div>
</header>
