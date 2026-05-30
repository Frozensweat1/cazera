@props(['item'])

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-[1.5rem] border border-ivory/10 bg-ivory/[0.045]']) }}>
    <a href="{{ route('website.menu.show', $item['slug']) }}" class="block focus:outline-none focus:ring-2 focus:ring-gold/70">
    <div class="aspect-[5/4] overflow-hidden">
        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
    </div>
    <div class="p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-gold">{{ $item['category_name'] ?? 'Signature' }}</p>
                <h3 class="mt-2 font-serif text-2xl font-semibold text-ivory">{{ $item['title'] }}</h3>
            </div>
            @if ($item['price'])
                <p class="shrink-0 rounded-full bg-gold/12 px-3 py-1 text-sm font-extrabold text-gold">GHS {{ $item['price'] }}</p>
            @endif
        </div>
        <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $item['description'] }}</p>
    </div>
    </a>
</article>
