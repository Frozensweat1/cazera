<div>
    @php
        $phone = $branch['phone'] ?? null;
        $wa = $branch['whatsapp'] ?? $phone;
        $waLink = $wa ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $wa) : null;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $branch['name'],
            'description' => $branch['description'],
            'image' => $branch['hero_image'],
            'url' => route('website.branch.show', $branch['slug']),
            'telephone' => $phone,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $branch['location'],
                'addressLocality' => 'Lagos',
                'addressCountry' => 'NG',
            ],
            'openingHours' => $branch['hours'],
            'servesCuisine' => ['Contemporary', 'Hospitality', 'Lounge'],
        ];
    @endphp
    <script type="application/ld+json">@json($schema)</script>

    <section class="relative flex min-h-[78svh] items-end overflow-hidden pb-14 pt-32">
        <img src="{{ $branch['hero_image'] }}" alt="{{ $branch['name'] }}" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/62 to-ink/20"></div>
        <div class="luxury-container relative">
            <x-website.breadcrumbs :items="[['label' => 'Branches', 'url' => route('website.branches')], ['label' => $branch['name']]]" class="px-0 pt-0" />
            <div class="mt-12 max-w-5xl reveal">
                <p class="eyebrow">{{ $branch['location'] }}</p>
                <h1 class="mt-4 font-serif text-6xl font-semibold leading-[0.95] text-ivory md:text-8xl">{{ $branch['name'] }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-parchment/82">{{ $branch['description'] }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @if ($phone)<x-website.button href="tel:{{ $phone }}">Call Branch</x-website.button>@endif
                    @if ($waLink)<x-website.button href="{{ $waLink }}" target="_blank" variant="whatsapp">WhatsApp</x-website.button>@endif
                    @if ($branch['directions_url'])<x-website.button href="{{ $branch['directions_url'] }}" target="_blank" variant="secondary">Directions</x-website.button>@endif
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-container py-16">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="glass-panel rounded-[1.5rem] p-6">
                <p class="eyebrow">Overview</p>
                <p class="mt-4 text-lg leading-8 text-parchment/78">{{ $branch['short_description'] }}</p>
            </div>
            <div class="glass-panel rounded-[1.5rem] p-6">
                <p class="eyebrow">Operating hours</p>
                <p class="mt-4 font-serif text-3xl text-ivory">{{ $branch['hours'] }}</p>
            </div>
            <div class="glass-panel rounded-[1.5rem] p-6">
                <p class="eyebrow">Contact</p>
                <div class="mt-4 space-y-2 text-parchment/78">
                    @if ($phone)<p><a href="tel:{{ $phone }}" class="text-gold">{{ $phone }}</a></p>@endif
                    @if ($branch['email'])<p><a href="mailto:{{ $branch['email'] }}" class="text-gold">{{ $branch['email'] }}</a></p>@endif
                    <p>{{ $branch['location'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-ivory py-20 text-ink">
        <div class="luxury-container">
            <x-website.section-heading eyebrow="Services & menu" title="Explore what this branch serves." subtitle="Browse chef signatures, drinks, experiences and house favorites by category." class="[&_.eyebrow]:text-earth [&_h2]:text-ink [&_p]:text-earth" />
            <div class="mb-8 flex flex-wrap gap-2">
                <button wire:click="$set('category', 'all')" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $category === 'all' ? 'bg-ink text-ivory' : 'bg-ink/8 text-earth hover:bg-ink/12' }}">All</button>
                @foreach ($categories as $cat)
                    <button wire:click="$set('category', '{{ $cat['slug'] }}')" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $category === $cat['slug'] ? 'bg-ink text-ivory' : 'bg-ink/8 text-earth hover:bg-ink/12' }}">{{ $cat['name'] }}</button>
                @endforeach
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($menuItems as $item)
                    <x-website.menu-card :item="$item" class="bg-ink text-ivory" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading eyebrow="Gallery" title="A closer look at the room, plate and mood." />
        <div x-data="{ active: null }" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($gallery as $item)
                <x-website.gallery-card :item="$item" />
            @endforeach
            <template x-if="active">
                <div class="fixed inset-0 z-[70] grid place-items-center bg-ink/90 p-4" @click.self="active = null" @keydown.escape.window="active = null">
                    <button type="button" @click="active = null" class="absolute right-5 top-5 rounded-full bg-ivory px-4 py-2 text-sm font-bold text-ink">Close</button>
                    <img :src="active.image" :alt="active.title" class="max-h-[82vh] rounded-2xl object-contain">
                </div>
            </template>
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr]">
            <div>
                <x-website.section-heading eyebrow="Reviews" title="What guests remember." subtitle="Published reviews and curated testimonials build local trust for this branch." />
                <div class="mt-8 space-y-4">
                    @forelse ($reviews as $review)
                        <article class="rounded-3xl border border-ivory/10 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-bold text-ivory">{{ $review->reviewer_name }}</p>
                                <p class="text-gold">{{ str_repeat('★', $review->rating) }}</p>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $review->review }}</p>
                        </article>
                    @empty
                        <p class="rounded-3xl border border-ivory/10 p-5 text-parchment/70">Reviews for this branch will appear after approval.</p>
                    @endforelse
                </div>
            </div>
            <div class="grid gap-5">
                @foreach ($testimonials->take(2) as $testimonial)
                    <x-website.testimonial-card :testimonial="$testimonial" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container py-16">
        <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <div class="rounded-[2rem] border border-ivory/10 bg-charcoal p-7">
                <p class="eyebrow">Map</p>
                @if ($branch['map_url'])
                    <iframe src="{{ $branch['map_url'] }}" title="{{ $branch['name'] }} map" loading="lazy" class="mt-5 h-80 w-full rounded-3xl border-0"></iframe>
                @else
                    <div class="mt-5 grid h-80 place-items-center rounded-3xl bg-ivory/[0.05] p-8 text-center text-parchment/70">Map embed can be added through branch settings.</div>
                @endif
            </div>
            <div>
                <x-website.section-heading eyebrow="Related branches" title="More places to discover." />
                <div class="grid gap-4">
                    @foreach ($branches as $related)
                        <a href="{{ route('website.branch.show', $related['slug']) }}" class="rounded-3xl border border-ivory/10 p-5 transition hover:bg-ivory/[0.05]">
                            <p class="font-serif text-2xl font-semibold text-ivory">{{ $related['name'] }}</p>
                            <p class="mt-1 text-sm text-parchment/70">{{ $related['location'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-website.cta-section title="Visit {{ $branch['name'] }}." subtitle="Call, WhatsApp or get directions when the mood is right." :image="$branch['image']">
        @if ($phone)<x-website.button href="tel:{{ $phone }}">Call Now</x-website.button>@endif
        @if ($waLink)<x-website.button href="{{ $waLink }}" target="_blank" variant="whatsapp">WhatsApp</x-website.button>@endif
    </x-website.cta-section>
</div>
