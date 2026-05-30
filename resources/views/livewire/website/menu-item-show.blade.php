<div>
    <x-website.breadcrumbs :items="[['label' => 'Menu & Services', 'url' => route('website.branches')], ['label' => $item['title']]]" />

    <section class="luxury-container pb-20 pt-10">
        <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <div class="overflow-hidden rounded-[2rem] border border-ivory/10 bg-charcoal">
                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="aspect-[4/3] w-full object-cover">
            </div>
            <div>
                <p class="eyebrow">{{ $item['category_name'] ?? 'Signature' }} @if($item['module']) / {{ $item['module'] }} @endif</p>
                <h1 class="mt-4 font-serif text-5xl font-semibold leading-tight text-ivory md:text-7xl">{{ $item['title'] }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-parchment/78">{{ $item['description'] }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @if ($item['price'])
                        <span class="rounded-full bg-gold/12 px-5 py-3 text-sm font-extrabold text-gold">GHS {{ $item['price'] }}</span>
                    @endif
                    @if ($item['branch'])
                        <span class="rounded-full border border-ivory/12 px-5 py-3 text-sm font-bold text-ivory">{{ $item['branch'] }}</span>
                    @endif
                    @if ($item['branch_slug'])
                        <x-website.button href="{{ route('website.branch.show', $item['branch_slug']) }}">View Branch</x-website.button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="bg-ivory py-20 text-ink">
        <div class="luxury-container">
            <x-website.section-heading eyebrow="Explore More" title="Other menu items and services across branches." subtitle="A rotating selection from different modules and branches." />
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($relatedItems as $related)
                    <x-website.menu-card :item="$related" class="bg-ink text-ivory" />
                @endforeach
            </div>
        </div>
    </section>
</div>
