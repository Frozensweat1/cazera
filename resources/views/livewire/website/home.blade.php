<div>
    @php
        $heroImage = $settings?->hero_background ? asset('storage/' . $settings->hero_background) : ($page['hero_image'] ?? \App\Support\WebsiteContent::image('photo-1514933651103-005eec06c04b', 1900));
        $brandName = $settings?->business_name ?? \App\Support\WebsiteContent::copy('brand.name', 'Cazera');
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $brandName,
            'url' => route('website.home'),
            'logo' => $settings?->logo ? asset('storage/' . $settings->logo) : null,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $settings?->phone,
                'contactType' => 'customer service',
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($schema)</script>

    <section class="relative flex min-h-[92svh] items-end overflow-hidden pb-14 pt-32 md:pb-20">
        <img src="{{ $heroImage }}" alt="{{ $brandName }} hospitality interior" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/68 to-ink/20"></div>
        <div class="absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-ink to-transparent"></div>
        <div class="luxury-container relative">
            <div class="max-w-5xl reveal">
                <p class="eyebrow">{{ \App\Support\WebsiteContent::copy('homepage.hero_eyebrow', $page['eyebrow'] ?? 'Premium hospitality across every branch') }}</p>
                <h1 class="mt-5 font-serif text-6xl font-semibold leading-[0.92] text-ivory md:text-8xl lg:text-9xl">{{ \App\Support\WebsiteContent::copy('homepage.hero_title', $page['title'] ?? $brandName) }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-parchment/82 md:text-xl">{{ \App\Support\WebsiteContent::copy('homepage.hero_subtitle', $page['subtitle'] ?? 'Step into refined dining, atmospheric lounges, signature plates, private celebrations and nights that linger long after the last toast.') }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-website.button href="{{ route('website.branches') }}">Explore Branches</x-website.button>
                    <x-website.button href="{{ route('website.contact') }}" variant="secondary">Contact a Branch</x-website.button>
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-container -mt-8 relative z-10">
        <div class="glass-panel rounded-[2rem] p-5 md:p-7">
            <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <p class="eyebrow">{{ \App\Support\WebsiteContent::copy('homepage.branch_selector_eyebrow', 'Find your atmosphere') }}</p>
                    <h2 class="mt-2 font-serif text-3xl font-semibold text-ivory">{{ \App\Support\WebsiteContent::copy('homepage.branch_selector_title', 'Choose the room, rhythm and location that fit tonight.') }}</h2>
                </div>
                <x-website.button href="{{ route('website.branches') }}" variant="secondary">View All Branches</x-website.button>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($branches as $branch)
                    <a href="{{ route('website.branch.show', $branch['slug']) }}" class="group rounded-3xl border border-ivory/10 bg-ivory/[0.04] p-4 transition hover:bg-ivory/[0.08]">
                        <p class="text-sm font-bold text-gold">{{ $branch['location'] }}</p>
                        <p class="mt-1 font-serif text-2xl font-semibold text-ivory">{{ $branch['name'] }}</p>
                        <p class="mt-2 text-sm text-parchment/68">{{ $branch['hours'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('homepage.featured_branches_eyebrow', 'Featured branches')" :title="\App\Support\WebsiteContent::copy('homepage.featured_branches_title', 'Each branch has its own rhythm.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.featured_branches_subtitle', 'From polished dining rooms to late-evening lounges, every location is shaped for a visit worth planning.')" />
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 stagger">
            @foreach ($branches as $branch)
                <x-website.branch-card :branch="$branch" />
            @endforeach
        </div>
    </section>

    <section class="bg-ivory py-20 text-ink">
        <div class="luxury-container">
            <div class="grid gap-12 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-earth">{{ \App\Support\WebsiteContent::copy('homepage.categories_eyebrow', 'Services & categories') }}</p>
                    <h2 class="mt-3 font-serif text-5xl font-semibold leading-tight">{{ \App\Support\WebsiteContent::copy('homepage.categories_title', 'Designed for dining, hosting and beautiful evenings.') }}</h2>
                    <p class="mt-5 leading-8 text-earth">{{ \App\Support\WebsiteContent::copy('homepage.categories_subtitle', 'Explore the moods of the house, from chef-led plates and crafted drinks to private moments made for the right table.') }}</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($categories as $category)
                        <article class="group overflow-hidden rounded-[1.5rem] bg-ink text-ivory">
                            <div class="aspect-[4/3] overflow-hidden">
                                <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                            </div>
                            <div class="p-5">
                                <h3 class="font-serif text-2xl font-semibold">{{ $category['name'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-parchment/72">{{ $category['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('homepage.menu_eyebrow', 'Menu highlights')" :title="\App\Support\WebsiteContent::copy('homepage.menu_title', 'Signature tastes, beautifully introduced.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.menu_subtitle', 'A curated first look at plates, pours and experiences guests come back for.')" />
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4 stagger">
            @foreach ($menuItems as $item)
                <x-website.menu-card :item="$item" />
            @endforeach
        </div>
    </section>

    <section class="relative overflow-hidden py-24">
        <img src="{{ \App\Support\WebsiteContent::image('photo-1559339352-11d035aa65de', 1800) }}" alt="Chef preparing a premium hospitality plate" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-ink/74"></div>
        <div class="luxury-container relative grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="eyebrow">{{ \App\Support\WebsiteContent::copy('homepage.about_eyebrow', 'About ' . $brandName) }}</p>
                <h2 class="mt-3 font-serif text-5xl font-semibold leading-tight text-ivory md:text-7xl">{{ \App\Support\WebsiteContent::copy('homepage.about_title', 'Hospitality should feel remembered, not merely served.') }}</h2>
            </div>
            <div class="glass-panel rounded-[2rem] p-7">
                <p class="text-lg leading-9 text-parchment/80">{{ \App\Support\WebsiteContent::copy('homepage.about_body', $brandName . ' is built around warm service, intentional rooms and food that feels personal. Every branch carries its own atmosphere, but the promise stays the same: arrive curious, leave already thinking about the next visit.') }}</p>
                <x-website.button href="{{ route('website.about') }}" variant="secondary" class="mt-6">Read Our Story</x-website.button>
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('homepage.testimonials_eyebrow', 'Guest voices')" :title="\App\Support\WebsiteContent::copy('homepage.testimonials_title', 'Trust, told with warmth.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.testimonials_subtitle', 'The best stories are the ones guests tell after the table has been cleared.')" align="center" />
        <div class="grid gap-5 md:grid-cols-3 stagger">
            @foreach ($testimonials as $testimonial)
                <x-website.testimonial-card :testimonial="$testimonial" />
            @endforeach
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('homepage.gallery_eyebrow', 'Gallery preview')" :title="\App\Support\WebsiteContent::copy('homepage.gallery_title', 'A visual invitation.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.gallery_subtitle', 'Ambience, plates, private rooms and evening energy, captured so guests can feel the place before they arrive.')" />
            <div x-data="{ active: null }" class="columns-1 gap-5 sm:columns-2">
                @foreach ($gallery as $item)
                    <x-website.gallery-card :item="$item" class="mb-5 break-inside-avoid" />
                @endforeach
                <template x-if="active">
                    <div class="fixed inset-0 z-[70] grid place-items-center bg-ink/90 p-4" @click.self="active = null" @keydown.escape.window="active = null">
                        <button type="button" @click="active = null" class="absolute right-5 top-5 rounded-full bg-ivory px-4 py-2 text-sm font-bold text-ink">Close</button>
                        <img :src="active.image" :alt="active.title" class="max-h-[82vh] rounded-2xl object-contain">
                    </div>
                </template>
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('homepage.events_eyebrow', 'Promotions & events')" :title="\App\Support\WebsiteContent::copy('homepage.events_title', 'Reasons to come tonight, this week, this season.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.events_subtitle', 'Live music, golden-hour moments, chef specials and seasonal invitations from the branches.')" />
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ($events as $event)
                <a href="{{ route('website.events.show', $event['slug']) }}" class="group overflow-hidden rounded-[1.5rem] border border-ivory/10 bg-charcoal">
                    <div class="aspect-[4/3] overflow-hidden"><img src="{{ $event['image'] }}" alt="{{ $event['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105"></div>
                    <div class="p-5">
                        <p class="eyebrow">{{ $event['tag'] }}</p>
                        <h3 class="mt-2 font-serif text-3xl font-semibold text-ivory">{{ $event['title'] }}</h3>
                        <p class="mt-2 text-sm font-bold text-gold">{{ $event['date'] }}</p>
                        <p class="mt-3 text-sm leading-7 text-parchment/70">{{ $event['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <x-website.cta-section :title="\App\Support\WebsiteContent::copy('homepage.cta_title', 'Make the next visit feel inevitable.')" :subtitle="\App\Support\WebsiteContent::copy('homepage.cta_subtitle', 'Choose a branch, call the team, send a WhatsApp message or ask what is happening tonight.')" :image="\App\Support\WebsiteContent::image('photo-1517248135467-4c7edcad34c4', 1600)">
        <x-website.button href="{{ route('website.contact') }}">Contact Us</x-website.button>
        <x-website.button href="{{ route('website.branches') }}" variant="secondary">Browse Branches</x-website.button>
    </x-website.cta-section>
</div>
