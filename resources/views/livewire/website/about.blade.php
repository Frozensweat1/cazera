<div>
    @php
        $sections = $page['sections'] ?? [];
        $brand = \App\Support\WebsiteContent::copy('brand.name', 'Cazera');
        $aboutCards = [
            [
                \App\Support\WebsiteContent::copy('about.philosophy_title', data_get($sections, 'philosophy_title', 'Hospitality Philosophy')),
                \App\Support\WebsiteContent::copy('about.philosophy', data_get($sections, 'philosophy', $page['body'] ?? 'Every touchpoint should feel warm, intentional and easy for the guest.')),
            ],
            [
                \App\Support\WebsiteContent::copy('about.mission_title', 'Mission'),
                \App\Support\WebsiteContent::copy('about.mission', data_get($sections, 'mission', 'To make every branch feel welcoming before arrival and unforgettable after the visit.')),
            ],
            [
                \App\Support\WebsiteContent::copy('about.values_title', 'Brand Values'),
                \App\Support\WebsiteContent::copy('about.values', is_array(data_get($sections, 'values')) ? implode(', ', data_get($sections, 'values')) : data_get($sections, 'values', 'Warmth, craft, consistency and elegance.')),
            ],
        ];
        $milestones = \App\Support\WebsiteContent::copy('about.milestones', data_get($sections, 'milestones', [
            ['01', 'Brand foundation'],
            ['02', 'Menu and service modules'],
            ['03', 'Branch expansion'],
            ['04', 'Guest story engine'],
        ]));
    @endphp

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
            @foreach ($aboutCards as [$title, $copy])
                <article>
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-earth">{{ $brand }}</p>
                    <h2 class="mt-3 font-serif text-4xl font-semibold">{{ $title }}</h2>
                    <p class="mt-4 leading-8 text-earth">{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="luxury-container py-20">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('about.milestones_eyebrow', 'Milestones')" :title="\App\Support\WebsiteContent::copy('about.milestones_title', 'From one memorable room to a growing hospitality story.')" />
        <div class="grid gap-5 md:grid-cols-4">
            @foreach ($milestones as $index => $milestone)
                @php
                    $step = is_array($milestone) ? ($milestone[0] ?? str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) : str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                    $title = is_array($milestone) ? ($milestone[1] ?? '') : $milestone;
                @endphp
                <article class="rounded-[1.5rem] border border-ivory/10 p-6">
                    <p class="font-serif text-5xl text-gold">{{ $step }}</p>
                    <h3 class="mt-5 font-serif text-2xl font-semibold text-ivory">{{ $title }}</h3>
                </article>
            @endforeach
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('about.expansion_eyebrow', 'Expansion')" :title="\App\Support\WebsiteContent::copy('about.expansion_title', 'Branches, each with a distinct local invitation.')" :subtitle="\App\Support\WebsiteContent::copy('about.expansion_subtitle', 'Every location carries its own mood, menu, hours, guest stories and direct ways to reach the team.')" />
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
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('about.leadership_eyebrow', 'Leadership')" :title="\App\Support\WebsiteContent::copy('about.leadership_title', 'The people behind the welcome.')" />
        <div class="grid gap-5 md:grid-cols-3">
            @forelse ($leaders as $leader)
                <article class="rounded-[1.5rem] border border-ivory/10 bg-charcoal p-6">
                    <div class="aspect-square overflow-hidden rounded-full bg-gold/15">
                        @if ($leader['image'])
                            <img src="{{ $leader['image'] }}" alt="{{ $leader['name'] }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center font-serif text-5xl text-gold">{{ $leader['initials'] }}</div>
                        @endif
                    </div>
                    <h3 class="mt-5 font-serif text-3xl font-semibold text-ivory">{{ $leader['name'] ?: $leader['role'] }}</h3>
                    <p class="mt-1 text-sm font-bold uppercase tracking-[0.18em] text-gold">{{ $leader['role'] }}</p>
                    <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $leader['copy'] }}</p>
                </article>
            @empty
                @foreach (\App\Support\WebsiteContent::copy('about.leadership_fallbacks', [['Experience Director', 'Shapes the guest journey across branches.'], ['Culinary Lead', 'Guides the menu language and signature moments.'], ['Operations Lead', 'Keeps branch information current and responsive.']]) as [$role, $copy])
                    <article class="rounded-[1.5rem] border border-ivory/10 bg-charcoal p-6">
                        <div class="aspect-square rounded-full bg-gold/15"></div>
                        <h3 class="mt-5 font-serif text-3xl font-semibold text-ivory">{{ $role }}</h3>
                        <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $copy }}</p>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>
</div>
