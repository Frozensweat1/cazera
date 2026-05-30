<div>
    <section class="relative flex min-h-[72svh] items-end overflow-hidden pb-14 pt-32">
        <img src="{{ $event['image'] }}" alt="{{ $event['title'] }}" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/64 to-ink/18"></div>
        <div class="luxury-container relative">
            <x-website.breadcrumbs :items="[['label' => 'Events', 'url' => route('website.events')], ['label' => $event['title']]]" class="px-0 pt-0" />
            <div class="mt-12 max-w-4xl">
                <p class="eyebrow">{{ $event['tag'] }} / {{ $event['date'] }}</p>
                <h1 class="mt-4 font-serif text-6xl font-semibold leading-tight text-ivory md:text-8xl">{{ $event['title'] }}</h1>
                <p class="mt-5 text-lg leading-8 text-parchment/82">{{ $event['description'] }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-website.button href="{{ route('website.contact') }}">Ask About This Event</x-website.button>
                    <x-website.button href="{{ route('website.branches') }}" variant="secondary">Find a Branch</x-website.button>
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-container py-20">
        <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
            <x-website.section-heading eyebrow="Experience notes" title="Built for atmosphere and easy response." subtitle="Ask about the date, gather your people and let the branch team help shape the evening." />
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['Live hospitality team', 'Branch-specific details', 'Call and WhatsApp CTAs'] as $item)
                    <div class="rounded-3xl border border-ivory/10 p-6 font-bold text-ivory">{{ $item }}</div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="luxury-container pb-20">
        <x-website.section-heading eyebrow="More happenings" title="Keep exploring." />
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ($events as $related)
                <a href="{{ route('website.events.show', $related['slug']) }}" class="rounded-3xl border border-ivory/10 p-5 transition hover:bg-ivory/[0.05]">
                    <p class="eyebrow">{{ $related['tag'] }}</p>
                    <h2 class="mt-2 font-serif text-2xl font-semibold text-ivory">{{ $related['title'] }}</h2>
                    <p class="mt-2 text-sm text-gold">{{ $related['date'] }}</p>
                </a>
            @endforeach
        </div>
    </section>
</div>
