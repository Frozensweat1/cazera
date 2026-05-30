@props(['branch'])
@php
    $phone = $branch['phone'] ?? null;
    $wa = $branch['whatsapp'] ?? $phone;
    $waLink = $wa ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $wa) : null;
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-[1.75rem] border border-ivory/10 bg-charcoal/78']) }}>
    <a href="{{ route('website.branch.show', $branch['slug']) }}" class="block focus:outline-none focus:ring-2 focus:ring-gold/70">
        <div class="relative aspect-[4/3] overflow-hidden image-veil">
            <img src="{{ $branch['image'] }}" alt="{{ $branch['name'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
            <div class="absolute left-5 top-5 z-10 flex flex-wrap gap-2">
                @foreach (array_slice($branch['tags'] ?? [], 0, 3) as $tag)
                    <span class="rounded-full bg-ink/55 px-3 py-1 text-xs font-bold text-ivory backdrop-blur">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </a>
    <div class="space-y-5 p-5">
        <div>
            <p class="eyebrow">{{ $branch['location'] }}</p>
            <h3 class="mt-2 font-serif text-3xl font-semibold text-ivory">{{ $branch['name'] }}</h3>
            <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $branch['short_description'] ?? $branch['description'] }}</p>
            <p class="mt-3 text-sm font-bold text-gold">{{ $branch['hours'] }}</p>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
            <x-website.button href="{{ route('website.branch.show', $branch['slug']) }}" class="px-4 py-2.5">View Branch</x-website.button>
            @if ($phone)
                <x-website.button href="tel:{{ $phone }}" variant="secondary" class="px-4 py-2.5">Call</x-website.button>
            @endif
            @if ($waLink)
                <x-website.button href="{{ $waLink }}" target="_blank" variant="whatsapp" class="px-4 py-2.5">WhatsApp</x-website.button>
            @endif
            @if ($branch['directions_url'] ?? null)
                <x-website.button href="{{ $branch['directions_url'] }}" target="_blank" variant="ghost" class="px-4 py-2.5">Directions</x-website.button>
            @endif
        </div>
    </div>
</article>
