<div>
    <x-website.breadcrumbs :items="[['label' => 'About']]" />

    <section class="luxury-container pb-20 pt-10">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('about.eyebrow', $page['eyebrow'] ?? 'Our story')" :title="\App\Support\WebsiteContent::copy('about.title', $page['title'] ?? 'A hospitality brand built around atmosphere, care and return visits.')" :subtitle="\App\Support\WebsiteContent::copy('about.subtitle', $page['subtitle'] ?? 'Cazera brings together generous service, expressive rooms and memorable food for guests who value the feeling of a place as much as the plate.')" />
            <div class="relative overflow-hidden rounded-[2rem]">
                <img src="{{ $page['hero_image'] ?? \App\Support\WebsiteContent::image('photo-1552566626-52f8b828add9', 1400) }}" alt="Premium restaurant interior" class="aspect-[4/3] w-full object-cover">
            </div>
        </div>
    </section>

    <section class="bg-ivory py-20 text-ink">
        <div class="luxury-container grid gap-12 lg:grid-cols-3">
            @foreach ([
                ['Hospitality Philosophy', 'Every touchpoint should lower friction and raise desire: clear information, beautiful imagery, human language and fast contact.'],
                ['Mission', 'To make every branch feel welcoming before arrival and unforgettable after the visit.'],
                ['Brand Values', 'Warmth, taste, restraint, responsiveness and a deep respect for the guest’s time.'],
            ] as [$title, $copy])
                <article>
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-earth">{{ \App\Support\WebsiteContent::copy('brand.name', 'Cazera') }}</p>
                    <h2 class="mt-3 font-serif text-4xl font-semibold">{{ $title }}</h2>
                    <p class="mt-4 leading-8 text-earth">{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading eyebrow="Milestones" title="From one memorable room to a growing hospitality story." />
        <div class="grid gap-5 md:grid-cols-4">
            @foreach ([['01', 'Brand foundation'], ['02', 'Menu and service modules'], ['03', 'Branch expansion'], ['04', 'Guest story engine']] as [$step, $title])
                <article class="rounded-[1.5rem] border border-ivory/10 p-6">
                    <p class="font-serif text-5xl text-gold">{{ $step }}</p>
                    <h3 class="mt-5 font-serif text-2xl font-semibold text-ivory">{{ $title }}</h3>
                </article>
            @endforeach
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <x-website.section-heading eyebrow="Expansion" title="Branches, each with a distinct local invitation." subtitle="Every location carries its own mood, menu, hours, guest stories and direct ways to reach the team." />
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($branches as $branch)
                    <a href="{{ route('website.branch.show', $branch['slug']) }}" class="rounded-3xl border border-ivory/10 p-5 transition hover:bg-ivory/[0.05]">
                        <p class="font-serif text-2xl font-semibold text-ivory">{{ $branch['name'] }}</p>
                        <p class="mt-2 text-sm text-parchment/70">{{ $branch['location'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading eyebrow="Leadership" title="The people behind the welcome." />
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ([['Experience Director', 'Shapes the guest journey across branches.'], ['Culinary Lead', 'Guides the menu language and signature moments.'], ['Operations Lead', 'Keeps branch information current and responsive.']] as [$role, $copy])
                <article class="rounded-[1.5rem] border border-ivory/10 bg-charcoal p-6">
                    <div class="aspect-square rounded-full bg-gold/15"></div>
                    <h3 class="mt-5 font-serif text-3xl font-semibold text-ivory">{{ $role }}</h3>
                    <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </section>
</div>
