<div>
    <x-website.breadcrumbs :items="[['label' => 'Promotions & Events']]" />

    <section class="luxury-container pb-20 pt-10">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('events.eyebrow', $page['eyebrow'] ?? 'Promotions & events')" :title="\App\Support\WebsiteContent::copy('events.title', $page['title'] ?? 'What is happening at Cazera.')" :subtitle="\App\Support\WebsiteContent::copy('events.subtitle', $page['subtitle'] ?? 'Upcoming events, seasonal campaigns, happy hour moments and live music nights, presented as visual invitations.')" />
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($events as $event)
                <a href="{{ route('website.events.show', $event['slug']) }}" class="group overflow-hidden rounded-[1.75rem] border border-ivory/10 bg-charcoal">
                    <div class="relative aspect-[4/3] overflow-hidden image-veil">
                        <img src="{{ $event['image'] }}" alt="{{ $event['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        <span class="absolute left-5 top-5 z-10 rounded-full bg-gold px-3 py-1 text-xs font-extrabold uppercase tracking-[0.15em] text-ink">{{ $event['tag'] }}</span>
                    </div>
                    <div class="p-6">
                        <p class="text-sm font-bold text-gold">{{ $event['date'] }}</p>
                        <h2 class="mt-2 font-serif text-3xl font-semibold text-ivory">{{ $event['title'] }}</h2>
                        <p class="mt-3 text-sm leading-7 text-parchment/72">{{ $event['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>
